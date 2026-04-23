<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    public function index(): View
    {
        $subscribers = ContactMessage::select('email', 'name')
            ->selectRaw('COUNT(*) as message_count')
            ->selectRaw('MIN(created_at) as first_contact')
            ->selectRaw('MAX(created_at) as last_contact')
            ->groupBy('email', 'name')
            ->orderByDesc('first_contact')
            ->paginate(20);

        $total  = ContactMessage::distinct('email')->count('email');
        $recent = ContactMessage::whereDate('created_at', today())->distinct('email')->count('email');

        return view('admin.subscribers.index', compact('subscribers', 'total', 'recent'));
    }

    public function destroy(string $email): RedirectResponse
    {
        ContactMessage::where('email', $email)->delete();
        return redirect()->route('admin.subscribers.index')
            ->with('success', "Subscriber {$email} removed.");
    }
}
