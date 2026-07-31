<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Services\PlanGate;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $contact = [
            'email'    => Setting::get('contact_email', ''),
            'whatsapp' => Setting::get('contact_whatsapp', ''),
            'phone'    => Setting::get('contact_phone', ''),
        ];

        return view('landing', compact('contact'));
    }

    public function pricing()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('months')->get();

        // Perk cards are generated live from the exact same PlanGate limits
        // enforced in code — so this page can never advertise a perk that
        // isn't actually true, or omit one that's real.
        $gate       = app(PlanGate::class);
        $perks      = $gate->pricingPerkCards();
        $trialDays  = $gate->trialDays();

        return view('pricing', compact('plans', 'perks', 'trialDays'));
    }
}
