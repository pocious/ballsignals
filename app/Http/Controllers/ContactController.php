<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('subscribe');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:150',
        ]);

        $alreadySubscribed = ContactMessage::where('email', $request->email)->exists();

        if (!$alreadySubscribed) {
            ContactMessage::create([
                'name'    => $request->name,
                'email'   => $request->email,
                'subject' => 'Daily Tips Subscription',
                'message' => 'Subscribed for daily tips.',
            ]);
        }

        return redirect()->route('subscribe')->with('success', 'You are subscribed! You will receive today\'s tips and daily updates from us.');
    }

    public function showContact(): View
    {
        return view('contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return redirect()->route('contact')->with('success', 'Message sent! We will get back to you within 24 hours.');
    }
}
