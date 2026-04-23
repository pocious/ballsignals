<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'plan'  => 'required|in:weekly,monthly,two_months',
        ]);

        SubscriptionRequest::create([
            'name'   => $request->name,
            'email'  => $request->email,
            'phone'  => $request->phone,
            'plan'   => $request->plan,
            'status' => 'pending',
        ]);

        return redirect()->route('premium')->with('sub_success', true);
    }
}
