<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\UserProgress;
use App\Models\PlayerAsset;
use App\Models\PlayerBill;
use App\Services\PlanGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    /**
     * POST /ai/chat
     * Accepts a player message, calls OpenRouter, returns AI reply as JSON.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $userId   = auth()->id();
        $cacheKey = "ai_chat_limit_{$userId}";
        $limit    = (int) Setting::get('ai_daily_limit', 15);

        $currentCount = (int) Cache::get($cacheKey, 0);
        if ($currentCount >= $limit) {
            return response()->json([
                'error'         => 'Daily limit reached. Come back tomorrow!',
                'limit_reached' => true,
            ], 429);
        }

        // Plan gate: free accounts get a smaller daily pesAI allowance (0 = unlimited)
        $user      = Auth::user();
        $gate      = app(PlanGate::class);
        $planLimit = $gate->limit($user, 'ai_per_day');
        if (!$gate->allows($user, 'ai_per_day', $currentCount)) {
            // `limit_reached` keeps the existing chat UI rendering `error` correctly
            return response()->json($gate->deny('ai_per_day', $planLimit) + ['limit_reached' => true], 422);
        }

        $ctx      = $this->buildPlayerContext();
        $history  = session('ai_chat_history', []);

        $messages = [['role' => 'system', 'content' => $this->buildSystemPrompt($ctx)]];
        foreach (array_slice($history, -8) as $turn) {
            $messages[] = $turn;
        }
        $messages[] = ['role' => 'user', 'content' => $request->message];

        $result = $this->callOpenRouter($messages);

        // Persist conversation in session (keep last 10 turns = 20 entries)
        $history[] = ['role' => 'user',      'content' => $request->message];
        $history[] = ['role' => 'assistant', 'content' => $result['reply']];
        session(['ai_chat_history' => array_slice($history, -20)]);

        // Increment daily counter, set 24-hour TTL on first use
        if ($currentCount === 0) {
            Cache::put($cacheKey, 1, now()->endOfDay());
        } else {
            Cache::increment($cacheKey);
        }

        return response()->json([
            'reply'         => $result['reply'],
            'tokens_used'   => $result['tokens'],
            'ai_quota_left' => $planLimit === 0 ? null : max(0, $planLimit - ($currentCount + 1)),
        ]);
    }

    /**
     * POST /ai/clear
     * Wipes conversation history from the session.
     */
    public function clear(Request $request)
    {
        session()->forget('ai_chat_history');
        return response()->json(['ok' => true]);
    }

    /**
     * GET /ai/context
     * Returns the current player context (for debugging / chat header).
     */
    public function context(Request $request)
    {
        return response()->json($this->buildPlayerContext());
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function buildPlayerContext(): array
    {
        $user     = Auth::user();
        $progress = UserProgress::where('user_id', $user->id)->first();

        $assets = PlayerAsset::with('asset')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->map(fn($pa) => [
                'name'          => $pa->asset->name,
                'category'      => $pa->asset->category,
                'monthly_income'=> $pa->asset->monthly_income * $pa->quantity,
                'monthly_cost'  => $pa->asset->monthly_cost  * $pa->quantity,
                'current_value' => $pa->current_value,
            ]);

        $bills = PlayerBill::with('bill')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->map(fn($pb) => $pb->bill->name . ' Ksh ' . number_format($pb->bill->amount));

        return [
            'name'                => $user->name,
            'age_group'           => $user->age_group ?? '18-25',
            'life_chapter'        => $progress?->life_chapter ?? 'student',
            'balance'             => $progress?->balance ?? 0,
            'salary'              => $progress?->career_income_rate ?? 0,
            'career_title'        => $progress?->career_title ?? 'Player',
            'credit_score'        => $progress?->credit_score ?? 600,
            'level'               => $progress?->level ?? 1,
            'assets'              => $assets->toArray(),
            'bills'               => $bills->toArray(),
            'total_monthly_income'=> $assets->sum('monthly_income'),
            'total_monthly_costs' => $assets->sum('monthly_cost') + ($bills->count() * 3000),
        ];
    }

    private function buildSystemPrompt(array $ctx): string
    {
        $assetList = empty($ctx['assets'])
            ? 'No assets yet'
            : collect($ctx['assets'])
                ->map(fn($a) => "{$a['name']} (+Ksh " . number_format($a['monthly_income']) . "/mo)")
                ->implode(', ');

        $billList = empty($ctx['bills']) ? 'None' : implode(', ', $ctx['bills']);

        $ageGuide = match($ctx['age_group']) {
            '8-12'  => "IMPORTANT — you are talking to a CHILD aged 8–12. Use very simple words and short sentences a child can understand. Use fun emojis 😊🌟. Compare money to things kids relate to: pocket money, sweets, school supplies, piggy banks. NEVER use financial jargon. Be playful, encouraging and patient. Example analogies: 'saving is like putting seeds in the ground so they grow into a big tree 🌳'.",
            '13-17' => "IMPORTANT — you are talking to a TEENAGER aged 13–17. Use casual, relatable teen language — cool but not cringe. Reference: school fees, M-Pesa, side hustles (selling snacks at school, washing cars), saving for a phone or sneakers, holiday jobs. Be honest and straight — teens respect that. Avoid being preachy.",
            '18-25' => "IMPORTANT — you are talking to a YOUNG ADULT aged 18–25. Career-focused and practical. Reference: first salary, SACCO membership, boda-boda hustle, chama groups, NSE shares, side gigs, avoiding lifestyle inflation. Speak like a sharp friend who knows money.",
            '26+'   => "IMPORTANT — you are talking to a MATURE ADULT aged 26+. Use a professional, strategic tone. Focus on: wealth building, family financial planning, business ownership, diversified investment portfolios, credit score optimization, retirement planning. Be data-driven and long-term focused.",
            default => "Be practical, encouraging, and grounded in Kenyan financial reality.",
        };

        return "You are pesAI, the AI financial mentor inside PesaQuest — a Kenyan financial literacy game. You have a warm, sharp Kenyan voice. Mix English and Swahili naturally ('Poa sana!', 'Sawa kabisa', 'Uchumi wa kweli'). You are encouraging but brutally honest when needed.

{$ageGuide}

PLAYER PROFILE:
- Name: {$ctx['name']}, Age group: {$ctx['age_group']}, Life chapter: {$ctx['life_chapter']}
- Career: {$ctx['career_title']}, Monthly salary: Ksh " . number_format($ctx['salary']) . "
- Game balance: Ksh " . number_format($ctx['balance']) . "
- Credit score: {$ctx['credit_score']}/850, Level: {$ctx['level']}

PORTFOLIO:
- Assets: {$assetList}
- Monthly asset income: Ksh " . number_format($ctx['total_monthly_income']) . "
- Active bills: {$billList}

KENYAN FINANCIAL RESOURCES (share a relevant URL only when the player asks about the specific institution or topic — max 1 link per reply):
- CBK (rates, T-bills, forex): https://www.centralbank.go.ke
- CMA (stocks, bonds, regulations): https://www.cma.or.ke
- NSE (share prices, listings): https://www.nse.co.ke
- Licensed SACCOs (SASRA): https://www.sasra.go.ke
- M-Pesa / Safaricom: https://www.safaricom.co.ke/personal/m-pesa
- HELB (student loans): https://www.helb.co.ke
- KRA (taxes, PIN): https://www.kra.go.ke
- CRB credit check: https://www.creditinfo.co.ke

RULES:
- Keep replies under 5 sentences. Go up to 8 only when explaining a financial product step-by-step.
- Always ground advice in the player's actual game data above.
- Use real Kenyan context: M-Pesa, SACCOs, NSE, Gikomba, matatus, Safaricom, CBK T-bills, chamas.
- Never make up numbers — only use data provided above.
- End each response with one short actionable tip or a question to keep the conversation alive.
- When a player's situation suggests a specific action (e.g. low credit score, zero savings, no assets), proactively mention it with a concrete suggestion.
- Share a resource link only when genuinely relevant — don't force it.
- Adapt your vocabulary and tone strictly to the age group instructions above.";
    }

    private function callOpenRouter(array $messages): array
    {
        $apiKey = Setting::get('openrouter_api_key', '');
        $model  = Setting::get('ai_model', 'meta-llama/llama-3.1-8b-instruct:free');

        if (empty($apiKey)) {
            return [
                'reply'  => 'pesAI is not available yet — the admin needs to configure the AI settings.',
                'tokens' => 0,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'HTTP-Referer'  => config('app.url', 'http://localhost'),
                'X-Title'       => 'PesaQuest - pesAI',
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => 450,
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                return ['reply' => 'pesAI is resting — please try again in a moment.', 'tokens' => 0];
            }

            $data = $response->json();
            return [
                'reply'  => $data['choices'][0]['message']['content'] ?? 'Sijui — please try again.',
                'tokens' => $data['usage']['total_tokens'] ?? 0,
            ];
        } catch (\Throwable $e) {
            return ['reply' => 'pesAI is resting — please try again in a moment.', 'tokens' => 0];
        }
    }
}
