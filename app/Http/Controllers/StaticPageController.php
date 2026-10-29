<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'max:0'],
        ]);

        $recipient = config('mail.from.address');

        if (!$recipient) {
            return back()->withInput()->withErrors([
                'contact' => 'Contact email is not configured yet.',
            ]);
        }

        try {
            Mail::raw(
                "New NPL Season Hub contact message\n\n"
                . "Name: {$validated['name']}\n"
                . "Email: {$validated['email']}\n"
                . "Subject: {$validated['subject']}\n\n"
                . "Message:\n{$validated['message']}",
                function ($mail) use ($recipient, $validated) {
                    $mail->to($recipient)
                        ->subject('NPL Season Hub: ' . $validated['subject'])
                        ->replyTo($validated['email'], $validated['name']);
                }
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'contact' => 'Message could not be sent. Please try again later.',
            ]);
        }

        return back()->with('success', 'Thank you! Your message has been sent.');
    }
}
