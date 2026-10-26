 @extends('layouts.app')

@section('title', 'Admin Login | NPL Season Hub')

@section('content')
<div class="flex min-h-[75vh] items-center justify-center py-8">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl md:grid-cols-2">

        {{-- Branding panel --}}
        <div class="relative flex flex-col justify-between overflow-hidden bg-slate-950 p-8 text-white md:p-12">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full border border-white/10"></div>
            <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full border border-white/10"></div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-3 rounded-lg border border-white/10 bg-white/5 px-3 py-2">
                    <span class="text-sm font-black tracking-wide">NPL</span>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-300">Season Hub</span>
                </div>

                <div class="mt-12 flex min-h-40 items-center justify-center rounded-xl bg-white p-5">
                    <img
                        src="https://upload.wikimedia.org/wikipedia/en/4/4b/Nepal_Premier_League_official_logo.png"
                        alt="Nepal Premier League official logo"
                        class="max-h-40 max-w-full object-contain"
                        referrerpolicy="no-referrer"
                    >
                </div>

                <p class="mt-10 text-xs font-bold uppercase tracking-[0.25em] text-red-400">
                    Editorial control room
                </p>

                <h1 class="mt-4 text-4xl font-black leading-tight tracking-tight md:text-5xl">
                    Every season.<br>
                    Every story.
                </h1>

                <p class="mt-5 max-w-sm leading-7 text-slate-300">
                    Manage cricket coverage, publish articles and organize the season archive from one secure workspace.
                </p>
            </div>

            <p class="relative z-10 mt-12 text-xs text-slate-400">
                NPL Season Hub · Administrator access
            </p>
        </div>

        {{-- Login form --}}
        <div class="p-7 sm:p-10 md:p-12">
            <div class="mb-8">
                <p class="text-sm font-bold uppercase tracking-widest text-red-600">
                    Welcome back
                </p>

                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
                    Admin login
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Sign in with your administrator username and password.
                </p>
            </div>

            @if (session('success'))
                <div role="status" class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="username" class="mb-2 block text-sm font-semibold text-slate-800">
                        Username
                    </label>

                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        required
                        autofocus
                        maxlength="50"
                        placeholder="Enter administrator username"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-red-600 focus:ring-4 focus:ring-red-100"
                    >
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="password" class="block text-sm font-semibold text-slate-800">
                            Password
                        </label>
                    </div>

                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="Enter your password"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3.5 pr-20 text-sm outline-none transition placeholder:text-slate-400 focus:border-red-600 focus:ring-4 focus:ring-red-100"
                        >

                        <button
                            type="button"
                            id="toggle-password"
                            aria-controls="password"
                            aria-pressed="false"
                            class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-slate-500 hover:text-red-600"
                        >
                            Show
                        </button>
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        class="h-4 w-4 rounded border-slate-300 accent-red-600"
                    >
                    Remember me on this device
                </label>

                <button
                    type="submit"
                    class="flex w-full items-center justify-center rounded-lg bg-red-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200"
                >
                    Sign in to dashboard
                    <span class="ml-2" aria-hidden="true">→</span>
                </button>
            </form>

            <div class="mt-8 border-t border-slate-200 pt-5">
                <a href="{{ route('seasons.index') }}"
                   class="text-sm font-semibold text-slate-500 transition hover:text-red-600">
                    ← Return to public website
                </a>

                <p class="mt-5 text-xs leading-5 text-slate-400">
                    Authorized personnel only. Never share your administrator credentials.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('toggle-password')?.addEventListener('click', function () {
        const password = document.getElementById('password');
        const isVisible = password.type === 'text';

        password.type = isVisible ? 'password' : 'text';
        this.textContent = isVisible ? 'Show' : 'Hide';
        this.setAttribute('aria-pressed', String(!isVisible));
    });
</script>
@endsection