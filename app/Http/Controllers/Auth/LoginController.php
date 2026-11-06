<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class LoginController extends Controller
{
    private const MAX_ACCOUNT_ATTEMPTS = 5;
    private const MAX_IP_ATTEMPTS = 20;

    private const ACCOUNT_DECAY_SECONDS = 60;
    private const IP_DECAY_SECONDS = 600;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:4096'],
            'g-recaptcha-response' => ['required', 'string', 'max:10000'],
        ], [
            'g-recaptcha-response.required' =>
                'Please complete the Google reCAPTCHA verification.',
        ]);

        $username = Str::lower(trim($validated['username']));
        $ip = (string) $request->ip();

        // Hash identifiers to avoid storing raw usernames/IPs in cache keys.
        $accountKey = 'admin-login:account:' . hash(
            'sha256',
            $username . '|' . $ip
        );

        $ipKey = 'admin-login:ip:' . hash('sha256', $ip);

        // Block excessive attempts before contacting Google or checking passwords.
        if (
            RateLimiter::tooManyAttempts(
                $accountKey,
                self::MAX_ACCOUNT_ATTEMPTS
            ) ||
            RateLimiter::tooManyAttempts(
                $ipKey,
                self::MAX_IP_ATTEMPTS
            )
        ) {
            $wait = max(
                RateLimiter::availableIn($accountKey),
                RateLimiter::availableIn($ipKey)
            );

            return $this->loginError(
                'Too many login attempts. Please try again in '
                . max(1, (int) ceil($wait / 60))
                . ' minute(s).'
            );
        }

        // Verify CAPTCHA with Google's server.
        $secret = config('services.recaptcha.secret_key');

        if (! is_string($secret) || trim($secret) === '') {
            report(new \RuntimeException(
                'Google reCAPTCHA secret key is not configured.'
            ));

            return $this->loginError(
                'Login verification is temporarily unavailable. Please try again later.'
            );
        }

        try {
            $response = Http::asForm()
                ->connectTimeout(3)
                ->timeout(5)
                ->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret' => $secret,
                        'response' => $validated['g-recaptcha-response'],
                        'remoteip' => $ip,
                    ]
                );

            if (
                ! $response->successful() ||
                $response->json('success') !== true
            ) {
                RateLimiter::hit($ipKey, self::IP_DECAY_SECONDS);

                return $this->loginError(
                    'Google reCAPTCHA verification failed. Please try again.'
                );
            }
        } catch (Throwable $exception) {
            report($exception);

            // Fail closed: never bypass CAPTCHA when Google cannot be reached.
            return $this->loginError(
                'Login verification is temporarily unavailable. Please try again later.'
            );
        }

        // Attempt authentication only after successful CAPTCHA verification.
        if (! Auth::attempt([
            'username' => $validated['username'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            RateLimiter::hit(
                $accountKey,
                self::ACCOUNT_DECAY_SECONDS
            );

            RateLimiter::hit(
                $ipKey,
                self::IP_DECAY_SECONDS
            );

            return $this->loginError('Invalid username or password.');
        }

        // Rotate the session ID after successful authentication.
        $request->session()->regenerate();

        // Preserve the existing admin authorization requirement.
        if (! $request->user()->can('manage-seasons')) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Do not reveal whether the account has another role.
            return $this->loginError(
                'This account does not have administrator access.'
            );
        }

        // Clear failed attempts after successful authorized login.
        RateLimiter::clear($accountKey);
        RateLimiter::clear($ipKey);

        return redirect()
            ->intended(route('admin.seasons.index'))
            ->with('success', 'Welcome back! You are now logged in.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    private function loginError(string $message): RedirectResponse
    {
        return back()
            ->withErrors(['username' => $message])
            ->withInput(['username' => request()->input('username')]);
    }
}
