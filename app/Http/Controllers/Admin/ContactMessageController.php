<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DailyTipsNotification;
use App\Models\BettingTip;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::latest()->paginate(20);
        return view('admin.contacts.index', compact('messages'));
    }

    public function show(ContactMessage $contact): View
    {
        $contact->update(['is_read' => true]);
        return view('admin.contacts.show', compact('contact'));
    }

    public function destroy(ContactMessage $contact): RedirectResponse
    {
        $contact->delete();
        return redirect()->route('admin.contacts.index')->with('success', 'Message deleted.');
    }

    public function sendDailyTips(): RedirectResponse
    {
        $tips = BettingTip::football()
            ->whereDate('match_time', today())
            ->where('is_premium', false)
            ->orderBy('match_time', 'asc')
            ->get();

        $subscribers = ContactMessage::select('email', 'name')
            ->distinct('email')
            ->get();

        if ($subscribers->isEmpty()) {
            return back()->with('error', 'No subscribers to send to yet.');
        }

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->send(new DailyTipsNotification($tips));
        }

        return back()->with('success', "Today's tips sent to {$subscribers->count()} subscriber(s)!");
    }
}
