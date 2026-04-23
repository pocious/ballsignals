<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $query = SubscriptionRequest::latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        $counts = [
            'all'      => SubscriptionRequest::count(),
            'pending'  => SubscriptionRequest::where('status', 'pending')->count(),
            'approved' => SubscriptionRequest::where('status', 'approved')->count(),
            'rejected' => SubscriptionRequest::where('status', 'rejected')->count(),
            'expired'  => SubscriptionRequest::where('status', 'expired')->count(),
        ];

        return view('admin.subscription-requests.index', compact('requests', 'status', 'counts'));
    }

    public function show(SubscriptionRequest $subscriptionRequest): View
    {
        return view('admin.subscription-requests.show', compact('subscriptionRequest'));
    }

    public function approve(SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $plan = SubscriptionRequest::$plans[$subscriptionRequest->plan] ?? null;
        $days = $plan['days'] ?? 30;

        $subscriptionRequest->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'expires_at'  => now()->addDays($days),
        ]);

        return back()->with('success', "Subscription approved. Access granted for {$days} days.");
    }

    public function reject(SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $subscriptionRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Subscription request rejected.');
    }

    public function destroy(SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $subscriptionRequest->delete();

        return redirect()->route('admin.subscription-requests.index')
            ->with('success', 'Subscription request deleted.');
    }
}
