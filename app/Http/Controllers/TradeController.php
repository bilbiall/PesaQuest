<?php

namespace App\Http\Controllers;

use App\Models\GameNotification;
use App\Models\PlayerAsset;
use App\Models\TradeListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    public function index()
    {
        $listings = TradeListing::active()
            ->with(['seller', 'playerAsset.asset'])
            ->where('seller_id', '!=', auth()->id())
            ->latest()
            ->paginate(20);

        $myListings = TradeListing::active()
            ->mine()
            ->with(['playerAsset.asset'])
            ->latest()
            ->get();

        return view('trade.index', compact('listings', 'myListings'));
    }

    public function list(Request $request, PlayerAsset $playerAsset)
    {
        abort_if($playerAsset->user_id !== auth()->id(), 403);
        abort_if($playerAsset->status !== 'active', 422, 'Asset is not available for listing.');

        $alreadyListed = TradeListing::active()
            ->where('player_asset_id', $playerAsset->id)
            ->exists();
        abort_if($alreadyListed, 422, 'This asset is already listed.');

        $request->validate([
            'asking_price' => 'required|integer|min:1|max:999999999',
        ]);

        TradeListing::create([
            'seller_id'       => auth()->id(),
            'player_asset_id' => $playerAsset->id,
            'asking_price'    => $request->asking_price,
        ]);

        return response()->json(['message' => 'Listed successfully!']);
    }

    public function buy(Request $request, TradeListing $listing)
    {
        abort_if($listing->status !== 'active', 422, 'This listing is no longer available.');
        abort_if($listing->seller_id === auth()->id(), 422, 'You cannot buy your own listing.');

        $buyer    = auth()->user();
        $progress = $buyer->getOrCreateProgress();

        abort_if($progress->balance < $listing->asking_price, 422, 'Insufficient balance.');

        DB::transaction(function () use ($listing, $buyer, $progress) {
            $sellerProgress = $listing->seller->getOrCreateProgress();

            // Transfer balance
            $progress->balance         = $progress->balance - $listing->asking_price;
            $sellerProgress->balance   = $sellerProgress->balance + $listing->asking_price;
            $progress->save();
            $sellerProgress->save();

            // Transfer asset ownership
            $listing->playerAsset->user_id = $buyer->id;
            $listing->playerAsset->save();

            // Close listing
            $listing->status  = 'sold';
            $listing->buyer_id = $buyer->id;
            $listing->sold_at  = now();
            $listing->save();

            // Notify seller
            GameNotification::create([
                'user_id' => $listing->seller_id,
                'type'    => 'success',
                'title'   => '🎉 Asset Sold!',
                'body'    => "{$buyer->name} bought your {$listing->playerAsset->asset->name} for Ksh " . number_format($listing->asking_price) . ".",
                'icon'    => '🤝',
                'data'    => ['listing_id' => $listing->id, 'amount' => $listing->asking_price],
            ]);

            // Notify buyer
            GameNotification::create([
                'user_id' => $buyer->id,
                'type'    => 'success',
                'title'   => '✅ Purchase Complete!',
                'body'    => "You bought {$listing->playerAsset->asset->name} for Ksh " . number_format($listing->asking_price) . ". It's now in your portfolio.",
                'icon'    => '🛒',
                'data'    => ['listing_id' => $listing->id],
            ]);
        });

        return response()->json(['message' => 'Purchase successful!']);
    }

    public function cancel(TradeListing $listing)
    {
        abort_if($listing->seller_id !== auth()->id(), 403);
        abort_if($listing->status !== 'active', 422, 'Listing is not active.');

        $listing->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Listing cancelled.']);
    }
}
