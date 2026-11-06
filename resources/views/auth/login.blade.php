
@extends('layouts.app')

@section('title', 'Admin Login | NPL Season Hub')

@section('content')
<div class="min-h-[80vh] bg-gray-50 px-4 py-8 sm:px-6 lg:py-12">
    <div class="mx-auto grid min-h-[620px] max-w-5xl overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm lg:grid-cols-2">

        {{-- LEFT SIDE: NPL SEASON 3 --}}
        <div class="relative min-h-[300px] overflow-hidden bg-slate-900 lg:min-h-full">

            <img
                src="{{ asset('images/npl-season-3.jpg') }}"
                alt="Nepal Premier League Season 3 cricket stadium"
                class="absolute inset-0 h-full w-full object-cover"
                onerror="this.style.display='none'"
            >

            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-black/20"></div>

            <div class="relative flex h-full min-h-[300px] flex-col justify-between p-7 text-white sm:p-10 lg:min-h-[620px]">

                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded bg-white p-1">
                            <img
                                src="https://upload.wikimedia.org/wikipedia/en/4/4b/Nepal_Premier_League_official_logo.png"
                                alt="NPL logo"
                                class="max-h-full max-w-full object-contain"
                            >
                        </div>

                        <div>
                            <p class="text-base font-bold tracking-wide">
                                NPL SEASON HUB
                            </p>
                            <p class="mt-1 text-xs text-gray-200">
                                Nepal Premier League · 2026
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-12">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-red-400">
                        Season III · 2026
                    </p>

                    <h1 class="text-3xl font-bold leading-tight sm:text-4xl">
                        Nepal Premier League
                    </h1>

                    <p class="mt-4 max-w-md text-sm leading-6 text-gray-200">
                        Your workspace for managing fixtures, scorecards,
                        player information, match results and tournament coverage.
                    </p>

                    <div class="mt-7 h-1 w-14 rounded bg-red-600"></div>
                </div>

                <p class="mt-10 text-xs text-gray-300">
                    NPL Season Hub · Administrator Portal
                </p>
            </div>
        </div>

        {{-- RIGHT SIDE: LOGIN FORM --}}
        <div class="flex items-center justify-center px-6 py-10 sm:px-10 lg:px-12">

            <div class="w-full max-w-sm">

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">
                        Admin Login
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Enter your administrator credentials to continue.
                    </p>
                </div>

                {{-- Success message --}}
                @if (session('success'))
                    <div
                        role="status"
                        class="mb-5 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Error message --}}
                @if ($errors->any())
                    <div
                        role="alert"
                        class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    >
                        {{ $errors->first() }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('login.store') }}"
                    class="space-y-5"
                >
                    @csrf

                    {{-- Username --}}
                    <div>
                        <label
                            for="username"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Username
                        </label>

                        <input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username') }}"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            maxlength="50"
                            required
                            autofocus
                            placeholder="Enter your username"
                            class="block w-full rounded border border-gray-300 px-3.5 py-3 text-sm text-gray-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-100"
                        >
                    </div>

                    {{-- Password --}}
                    <div>
                        <label
                            for="password"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Password
                        </label>

                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                autocomplete="current-password"
                                maxlength="4096"
                                required
                                placeholder="Enter your password"
                                class="block w-full rounded border border-gray-300 px-3.5 py-3 pr-16 text-sm text-gray-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-100"
                            >

                            <button
                                type="button"
                                id="toggle-password"
                                aria-controls="password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-gray-500 hover:text-red-600"
                            >
                                Show
                            </button>
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                @checked(old('remember'))
                                class="h-4 w-4 rounded border-gray-300 accent-red-600"
                            >
                            Remember me
                        </label>
                    </div>

                    {{-- Google reCAPTCHA --}}
                    <div>
                        @if (config('services.recaptcha.site_key'))
                            <div
                                class="g-recaptcha"
                                data-sitekey="{{ config('services.recaptcha.site_key') }}"
                            ></div>
                        @else
                            <p class="text-sm text-red-600">
                                Security verification is not configured.
                            </p>
                        @endif

                        @error('g-recaptcha-response')
                            <p class="mt-2 text-sm text-red-600" role="alert">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        @disabled(!config('services.recaptcha.site_key'))
                        class="flex w-full items-center justify-center rounded bg-red-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Sign In
                    </button>
                </form>

                <div class="mt-7 border-t border-gray-200 pt-5">
                    <a
                        href="{{ route('seasons.index') }}"
                        class="text-sm text-gray-600 transition hover:text-red-700"
                    >
                        &larr; Back to website
                    </a>

                    <p class="mt-4 text-xs leading-5 text-gray-400">
                        Restricted access. Only authorized administrators may sign in.
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Google reCAPTCHA API --}}
<script
    src="https://www.google.com/recaptcha/api.js"
    async
    defer
></script>

{{-- Password visibility --}}
<script>
    document.getElementById('toggle-password')?.addEventListener('click', function () {
        const password = document.getElementById('password');
        const visible = password.type === 'password';

        password.type = visible ? 'text' : 'password';
        this.textContent = visible ? 'Hide' : 'Show';
        this.setAttribute('aria-pressed', String(visible));
    });
</script>
@endsection
