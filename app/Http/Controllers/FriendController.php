<?php

namespace App\Http\Controllers;

use App\Models\FriendGift;
use App\Models\Friendship;
use App\Models\GameNotification;
use App\Models\SchoolMember;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FriendController extends Controller
{
    /** How many unanswered outgoing requests a player may have at once. */
    const MAX_PENDING_OUTGOING = 10;

    public function index()
    {
        abort_unless(Schema::hasTable('friendships'), 503, 'Friends is almost ready — the friendships migration has not run yet.');

        $user = auth()->user();
        $code = Schema::hasColumn('users', 'friend_code') ? $user->ensureFriendCode() : null;
        if (User::usernamesEnabled()) $user->ensureUsername();

        $userCols = 'id,name,profile_photo' . (User::usernamesEnabled() ? ',username' : '');

        $rows = Friendship::where(fn ($q) => $q->where('requester_id', $user->id)->orWhere('addressee_id', $user->id))
            ->with(["requester:{$userCols}", "addressee:{$userCols}",
                    'requester.progress:id,user_id,level,life_chapter,credit_score',
                    'addressee.progress:id,user_id,level,life_chapter,credit_score'])
            ->latest()
            ->get();

        $friends  = $rows->where('status', 'accepted');
        $incoming = $rows->where('status', 'pending')->where('addressee_id', $user->id);
        $outgoing = $rows->where('status', 'pending')->where('requester_id', $user->id);

        // Classmates from the same active school — one-tap friend suggestions
        $knownIds   = $rows->flatMap(fn ($f) => [$f->requester_id, $f->addressee_id])->unique()->all();
        $classmates = collect();
        $myMembership = SchoolMember::where('user_id', $user->id)->where('status', 'active')->first();
        if ($myMembership) {
            $classmates = SchoolMember::where('school_subscription_id', $myMembership->school_subscription_id)
                ->where('status', 'active')
                ->where('user_id', '!=', $user->id)
                ->whereNotIn('user_id', $knownIds)
                ->with("user:{$userCols}")
                ->take(8)
                ->get()
                ->pluck('user')
                ->filter();
        }

        // Friend loans (built on top of friendships) — hidden until its migration runs
        $loansEnabled = Schema::hasTable('friend_loans');
        $myLoans      = collect();
        if ($loansEnabled) {
            $myLoans = \App\Models\FriendLoan::where(fn ($q) => $q->where('lender_id', $user->id)->orWhere('borrower_id', $user->id))
                ->whereNotIn('status', ['declined', 'expired'])
                ->with(["lender:{$userCols}", "borrower:{$userCols}"])
                ->latest()
                ->get();
        }

        $progress = $user->getOrCreateProgress();

        $gate            = app(PlanGate::class);
        $giftsEnabled    = Schema::hasTable('friend_gifts');
        $sendMoneyAccess = $gate->limit($user, 'send_money_access') > 0;

        return view('friends.index', compact(
            'user', 'code', 'friends', 'incoming', 'outgoing', 'classmates',
            'loansEnabled', 'myLoans', 'progress', 'giftsEnabled', 'sendMoneyAccess'
        ));
    }

    /** Send a request by friend code or exact display name. */
    public function store(Request $request)
    {
        abort_unless(Schema::hasTable('friendships'), 503);

        $user = auth()->user();
        $data = $request->validate(['q' => 'nullable|string|max:120', 'user_id' => 'nullable|integer']);

        $target = null;
        if (!empty($data['user_id'])) {
            $target = User::find((int) $data['user_id']);
        } elseif (!empty($data['q'])) {
            $q = trim($data['q']);
            if (Schema::hasColumn('users', 'friend_code')) {
                $target = User::where('friend_code', strtoupper($q))->first();
            }
            if (!$target && User::usernamesEnabled()) {
                $target = User::where('username', strtolower(ltrim($q, '@')))->first();
            }
            $target ??= User::where('name', $q)->where('is_active', true)->first();
        }

        if (!$target) {
            return back()->with('error', 'No player found with that username, name or friend code. Usernames look like @amina_k, codes like PQ-AB12CD.');
        }
        if ($target->id === $user->id) {
            return back()->with('error', "That's your own code — share it with a friend instead 😄");
        }

        $pendingOut = Friendship::where('requester_id', $user->id)->where('status', 'pending')->count();
        if ($pendingOut >= self::MAX_PENDING_OUTGOING) {
            return back()->with('error', 'You have too many unanswered requests — wait for some replies first.');
        }

        $existing = Friendship::between($user->id, $target->id);
        if ($existing) {
            if ($existing->status === 'accepted') return back()->with('error', "You're already friends with {$target->name}.");
            if ($existing->status === 'pending')  return back()->with('error', 'A request between you two is already waiting for an answer.');
            // Declined before — allow a fresh start, re-sent by whoever asks now
            $existing->update(['requester_id' => $user->id, 'addressee_id' => $target->id, 'status' => 'pending', 'responded_at' => null]);
        } else {
            Friendship::create(['requester_id' => $user->id, 'addressee_id' => $target->id, 'status' => 'pending']);
        }

        GameNotification::create([
            'user_id' => $target->id,
            'type'    => 'friend_request',
            'title'   => '👋 ' . $user->name . ' wants to be friends',
            'body'    => 'Accept on the Friends page to unlock lending, chama invites and more together.',
            'icon'    => '👋',
            'data'    => ['from' => $user->id, 'url' => '/friends'],
        ]);

        return back()->with('success', "Friend request sent to {$target->name}!");
    }

    public function accept(Friendship $friendship)
    {
        $user = auth()->user();
        abort_unless($friendship->addressee_id === $user->id && $friendship->status === 'pending', 403);

        $friendship->update(['status' => 'accepted', 'responded_at' => now()]);

        GameNotification::create([
            'user_id' => $friendship->requester_id,
            'type'    => 'friend_accepted',
            'title'   => '🤝 ' . $user->name . ' accepted your friend request',
            'body'    => 'You can now lend to each other and invite each other to chamas.',
            'icon'    => '🤝',
            'data'    => ['friend' => $user->id, 'url' => '/friends'],
        ]);

        return back()->with('success', "You and {$friendship->requester->name} are now friends!");
    }

    public function decline(Friendship $friendship)
    {
        $user = auth()->user();
        abort_unless($friendship->addressee_id === $user->id && $friendship->status === 'pending', 403);

        $friendship->update(['status' => 'declined', 'responded_at' => now()]);

        return back()->with('success', 'Request declined.');
    }

    /** Unfriend, or cancel an outgoing request. */
    public function destroy(Friendship $friendship)
    {
        $user = auth()->user();
        abort_unless(in_array($user->id, [$friendship->requester_id, $friendship->addressee_id], true), 403);

        // An open loan between you two keeps the friendship record alive
        if (Schema::hasTable('friend_loans')) {
            $openLoan = \App\Models\FriendLoan::whereIn('status', ['requested', 'offered', 'countered', 'active'])
                ->where(function ($q) use ($friendship) {
                    $q->whereIn('lender_id', [$friendship->requester_id, $friendship->addressee_id])
                      ->whereIn('borrower_id', [$friendship->requester_id, $friendship->addressee_id]);
                })
                ->exists();
            if ($openLoan) {
                return back()->with('error', 'You have an open loan together — settle it before unfriending.');
            }
        }

        $friendship->delete();

        return back()->with('success', 'Removed.');
    }

    /** Instant, no-interest gift to a friend — a plain top-up, not the structured loan flow. */
    public function sendGift(Request $request)
    {
        abort_unless(Schema::hasTable('friend_gifts'), 503);
        $user = auth()->user();

        $gate = app(PlanGate::class);
        if ($gate->limit($user, 'send_money_access') < 1) {
            return back()->with('error', $gate->deny('send_money_access', 0)['error']);
        }

        $data = $request->validate([
            'friend_id' => 'required|integer|exists:users,id',
            'amount'    => 'required|integer|min:10|max:50000',
            'message'   => 'nullable|string|max:120',
        ]);

        $friend = User::findOrFail((int) $data['friend_id']);
        if ($friend->id === $user->id) {
            return back()->with('error', "You can't send money to yourself.");
        }
        if (!Friendship::areFriends($user->id, $friend->id)) {
            return back()->with('error', 'You can only send money to accepted friends.');
        }

        $senderLevel = (int) ($user->getOrCreateProgress()->level ?? 1);
        if ($senderLevel < FriendGift::MIN_LEVEL) {
            return back()->with('error', 'Reach level ' . FriendGift::MIN_LEVEL . ' to send money to friends.');
        }

        if (FriendGift::sentTodayCount($user->id) >= FriendGift::DAILY_LIMIT) {
            return back()->with('error', "You've sent " . FriendGift::DAILY_LIMIT . ' gifts today already — try again tomorrow.');
        }

        $senderProgress = $user->getOrCreateProgress();
        $amount         = (int) $data['amount'];
        $maxGift        = (int) floor(($senderProgress->balance ?? 0) * FriendGift::MAX_GIFT_SHARE);

        if ($amount > $maxGift) {
            return back()->with('error', 'You can send at most 20% of your cash in one gift (Ksh ' . number_format(max($maxGift, 0)) . ' right now).');
        }
        if (($senderProgress->balance ?? 0) < $amount) {
            return back()->with('error', 'You need Ksh ' . number_format($amount) . ' to send this — you have Ksh ' . number_format($senderProgress->balance ?? 0) . '.');
        }

        $recipientProgress = $friend->getOrCreateProgress();

        DB::transaction(function () use ($user, $friend, $senderProgress, $recipientProgress, $amount, $data) {
            $senderProgress->balance -= $amount;
            $senderProgress->recalculateNetWorth();
            $senderProgress->save();

            $recipientProgress->balance += $amount;
            $recipientProgress->recalculateNetWorth();
            $recipientProgress->save();

            FriendGift::create([
                'sender_id'    => $user->id,
                'recipient_id' => $friend->id,
                'amount'       => $amount,
                'message'      => $data['message'] ?? null,
            ]);
        });

        GameNotification::create([
            'user_id' => $friend->id,
            'type'    => 'friend_gift',
            'title'   => '💰 ' . $user->name . ' sent you Ksh ' . number_format($amount),
            'body'    => trim($data['message'] ?? '') !== '' ? '"' . trim($data['message']) . '"' : 'It just landed in your wallet.',
            'icon'    => '💰',
            'data'    => ['url' => '/friends'],
        ]);

        return back()->with('success', "Sent Ksh " . number_format($amount) . " to {$friend->name}!");
    }
}
