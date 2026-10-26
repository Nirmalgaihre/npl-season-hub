@extends('layouts.admin')

@section('title', 'Live Scoring')

@section('content')
<div class="min-h-screen bg-slate-100/70 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-8">

        <!-- Top Header & Actions -->
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 rounded-full bg-orange-100/80 px-3 py-1 text-xs font-black uppercase tracking-widest text-orange-600">
                    <span class="h-2 w-2 rounded-full bg-orange-500 animate-pulse"></span>
                    NPL Season Hub &bull; Match Operations
                </div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Live Scoring</h1>
                <p class="text-sm font-medium text-slate-500">
                    {{ $fixture->homeTeam->name ?? 'Home' }} <span class="text-slate-300 font-normal">vs</span> {{ $fixture->awayTeam->name ?? 'Away' }}
                </p>
            </div>
            
            <a href="{{ route('live.show', $fixture) }}" target="_blank"
               class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-900 px-5 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-900/10 transition-all duration-200 hover:bg-indigo-800 hover:shadow-indigo-900/20 active:scale-95">
                <span>Open public scoreboard</span>
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>
        </header>

        <!-- Alerts -->
        @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm font-bold text-emerald-800 shadow-sm backdrop-blur-sm">
                <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50/85 p-5 text-sm text-rose-800 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-2 font-black text-rose-900">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please fix the following validation errors:</span>
                </div>
                <ul class="mt-2.5 list-inside list-disc space-y-1 font-medium text-rose-700">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <!-- Match Hero Banner -->
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 p-6 text-white shadow-2xl sm:p-8">
            <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 grid gap-6 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                <div>
                    <span class="inline-block text-xs font-bold uppercase tracking-widest text-indigo-300">Home Team</span>
                    <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">{{ $fixture->homeTeam->name ?? 'Home team' }}</h2>
                </div>
                <div class="flex justify-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-xs font-black tracking-widest text-white/70 shadow-inner backdrop-blur-md">VS</span>
                </div>
                <div class="sm:text-right">
                    <span class="inline-block text-xs font-bold uppercase tracking-widest text-indigo-300">Away Team</span>
                    <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">{{ $fixture->awayTeam->name ?? 'Away team' }}</h2>
                </div>
            </div>
            <div class="relative z-10 mt-6 flex items-center gap-2 border-t border-white/10 pt-4 text-xs font-medium text-indigo-200 sm:text-sm">
                <svg class="h-4 w-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>{{ $fixture->venue ?: 'Venue not set' }}</span>
            </div>
        </section>

        <!-- Toss & Match Setup Grid -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Toss -->
            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-7">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 font-bold">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Toss & Match Setup</h2>
                        <p class="text-xs text-slate-500">Record the toss before the first innings.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.live.toss', $fixture) }}" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Toss Winner</label>
                            <select name="toss_winner_team_id" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800 focus:border-indigo-600 focus:bg-white focus:ring-indigo-600">
                                <option value="">Choose team</option>
                                <option value="{{ $fixture->home_team_id }}" @selected(old('toss_winner_team_id', $fixture->toss_winner_team_id) == $fixture->home_team_id)>{{ $fixture->homeTeam->name ?? 'Home' }}</option>
                                <option value="{{ $fixture->away_team_id }}" @selected(old('toss_winner_team_id', $fixture->toss_winner_team_id) == $fixture->away_team_id)>{{ $fixture->awayTeam->name ?? 'Away' }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Decision</label>
                            <select name="toss_decision" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800 focus:border-indigo-600 focus:bg-white focus:ring-indigo-600">
                                <option value="bat" @selected(old('toss_decision', $fixture->toss_decision) === 'bat')>Bat First</option>
                                <option value="field" @selected(old('toss_decision', $fixture->toss_decision) === 'field')>Field First</option>
                            </select>
                        </div>
                    </div>
                    <button class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-slate-900/10 transition-all hover:bg-slate-800 active:scale-95">
                        Save Toss Decision
                    </button>
                </form>

                @if($fixture->tossWinner)
                    <div class="mt-4 flex items-center gap-2 rounded-xl bg-indigo-50/60 p-3 text-xs font-semibold text-indigo-900">
                        <svg class="h-4 w-4 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Toss: <strong>{{ $fixture->tossWinner->name }}</strong> elected to <strong>{{ $fixture->toss_decision }}</strong>.</span>
                    </div>
                @endif
            </section>

            <!-- Start Innings -->
            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-7">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 font-bold">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Start Innings</h2>
                        <p class="text-xs text-slate-500">Configure opening batters and bowler.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.live.start', $fixture) }}" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Innings</label>
                            <select name="innings_number" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800 focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                                <option value="1">1st Innings</option>
                                <option value="2">2nd Innings</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Batting Team</label>
                            <select name="batting_team_id" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800 focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                                <option value="{{ $fixture->home_team_id }}">{{ $fixture->homeTeam->name ?? 'Home' }}</option>
                                <option value="{{ $fixture->away_team_id }}">{{ $fixture->awayTeam->name ?? 'Away' }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Striker</label>
                            <input name="striker" required maxlength="100" class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-2.5 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white" placeholder="Batter name">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Non-Striker</label>
                            <input name="non_striker" required maxlength="100" class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-2.5 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white" placeholder="Batter name">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Opening Bowler</label>
                            <input name="current_bowler" required maxlength="100" class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-2.5 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white" placeholder="Bowler name">
                        </div>
                    </div>
                    <button class="w-full rounded-xl bg-gradient-to-r from-orange-500 to-rose-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/20 transition-all hover:opacity-95 active:scale-95">
                        Start Innings
                    </button>
                </form>
                <p class="mt-3 text-[11px] font-medium text-amber-700/90 flex items-center gap-1.5">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Innings with recorded deliveries cannot be restarted.</span>
                </p>
            </section>
        </div>

        <!-- Score Overview Cards -->
        <section class="grid gap-4 sm:grid-cols-2">
            @forelse($innings as $inning)
                <article class="relative overflow-hidden rounded-3xl border {{ $inning->is_current ? 'border-orange-500 ring-2 ring-orange-400/20 shadow-xl' : 'border-slate-200/80 shadow-sm' }} bg-white p-6 transition-all">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400">Innings {{ $inning->innings_number }}</span>
                        @if($inning->is_current)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-3 py-1 text-[11px] font-black tracking-wider text-white shadow-sm shadow-emerald-500/30">
                                <span class="h-1.5 w-1.5 rounded-full bg-white animate-ping"></span>
                                LIVE
                            </span>
                        @endif
                    </div>
                    <h3 class="mt-2 text-lg font-black text-slate-800">{{ $inning->battingTeam->name ?? 'Batting team' }}</h3>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-4xl font-black tracking-tight text-indigo-950">{{ $inning->total_runs }}</span>
                        <span class="text-2xl font-black text-slate-400">/{{ $inning->wickets }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-500">
                        <span>Overs: <strong class="text-slate-900">{{ $inning->oversText() }}</strong></span>
                        <span>Extras: <strong class="text-slate-900">{{ $inning->extras }}</strong></span>
                        <span>Run Rate: <strong class="text-slate-900">{{ number_format($inning->runRate(), 2) }}</strong></span>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm font-medium text-slate-400 sm:col-span-2">
                    No innings started yet.
                </div>
            @endforelse
        </section>

        {{-- Active scoring console --}}
        @php($active = $innings->firstWhere('is_current', true))
        @if($active)
            <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl">
                <!-- Console Top Bar -->
                <div class="bg-indigo-950 p-6 text-white sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-widest text-orange-400">Active Live Scoring</span>
                            <h2 class="mt-1 text-2xl font-black tracking-tight">{{ $active->battingTeam->name ?? 'Batting team' }}</h2>
                        </div>
                        @if($active->free_hit_next)
                            <span class="rounded-xl bg-orange-500 px-4 py-2 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-orange-500/30 animate-pulse">
                                FREE HIT DELIVERY
                            </span>
                        @endif
                    </div>
                    <div class="mt-6 flex flex-wrap items-end gap-x-8 gap-y-3 border-t border-white/10 pt-6">
                        <div>
                            <span class="block text-xs font-bold text-indigo-300 uppercase tracking-wider">Score</span>
                            <span class="text-5xl font-black tracking-tight">{{ $active->total_runs }}/{{ $active->wickets }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-indigo-300 uppercase tracking-wider">Overs</span>
                            <span class="text-2xl font-black">{{ $active->oversText() }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-indigo-300 uppercase tracking-wider">Run Rate</span>
                            <span class="text-2xl font-black">{{ number_format($active->runRate(), 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[1.2fr_.8fr]">
                    <!-- Left Column: Quick Scoring & Extra pads -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-base font-black text-slate-900">Tap to Score Runs</h3>
                            <p class="text-xs text-slate-500">Each action instantly records a delivery and updates match statistics.</p>

                            <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-7">
                                @foreach([0,1,2,3,4,5,6] as $run)
                                    <form method="POST" action="{{ route('admin.live.ball', $fixture) }}">
                                        @csrf
                                        <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                        <input type="hidden" name="striker" value="{{ $active->striker }}">
                                        <input type="hidden" name="non_striker" value="{{ $active->non_striker }}">
                                        <input type="hidden" name="bowler" value="{{ $active->current_bowler }}">
                                        <input type="hidden" name="batter_runs" value="{{ $run }}">
                                        <input type="hidden" name="extra_type" value="none">
                                        <input type="hidden" name="extra_runs" value="0">
                                        <button class="group flex h-16 w-full flex-col items-center justify-center rounded-2xl border border-slate-200/80 bg-slate-50/50 text-xl font-black text-indigo-950 shadow-sm transition-all hover:border-orange-500 hover:bg-orange-500 hover:text-white active:scale-95">
                                            <span>{{ $run }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 group-hover:text-orange-100">RUNS</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="text-base font-black text-slate-900">Quick Extras</h3>
                            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach([
                                    ['wide', 'Wide (+1)', 0, 1],
                                    ['no_ball', 'No-Ball (+1)', 0, 1],
                                    ['bye', 'Bye', 0, 1],
                                    ['leg_bye', 'Leg-Bye', 0, 1],
                                ] as [$type, $label, $batRuns, $extraRuns])
                                    <form method="POST" action="{{ route('admin.live.ball', $fixture) }}">
                                        @csrf
                                        <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                        <input type="hidden" name="striker" value="{{ $active->striker }}">
                                        <input type="hidden" name="non_striker" value="{{ $active->non_striker }}">
                                        <input type="hidden" name="bowler" value="{{ $active->current_bowler }}">
                                        <input type="hidden" name="batter_runs" value="{{ $batRuns }}">
                                        <input type="hidden" name="extra_type" value="{{ $type }}">
                                        <input type="hidden" name="extra_runs" value="{{ $extraRuns }}">
                                        <button class="w-full rounded-2xl border border-orange-200/80 bg-orange-50/60 px-3 py-3.5 text-xs font-black text-orange-900 transition-all hover:bg-orange-100 active:scale-95">
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>

                        <!-- Detailed Delivery Form -->
                        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-5">
                            <h4 class="text-sm font-black text-slate-900">Detailed Delivery Form</h4>
                            <p class="text-[11px] text-slate-500">Use for custom extras, multi-run no-balls, or changing bowlers.</p>

                            <form method="POST" action="{{ route('admin.live.ball', $fixture) }}" class="mt-4 space-y-4">
                                @csrf
                                <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                <input type="hidden" name="striker" value="{{ $active->striker }}">
                                <input type="hidden" name="non_striker" value="{{ $active->non_striker }}">

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Bowler (Change at over end)</label>
                                        <input name="bowler" value="{{ $active->current_bowler }}" required class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-800">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Batter Runs</label>
                                        <select name="batter_runs" class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-800">
                                            @foreach([0,1,2,3,4,5,6] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Extra Type</label>
                                        <select name="extra_type" class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-800">
                                            <option value="none">None</option>
                                            <option value="wide">Wide</option>
                                            <option value="no_ball">No-ball</option>
                                            <option value="bye">Bye</option>
                                            <option value="leg_bye">Leg-bye</option>
                                            <option value="penalty">Penalty</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Extra Runs</label>
                                        <input name="extra_runs" type="number" min="0" max="7" value="0" required class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-800">
                                    </div>
                                </div>
                                <button class="w-full rounded-xl bg-indigo-900 px-4 py-3 text-xs font-black uppercase tracking-wider text-white shadow-md transition-all hover:bg-indigo-800 active:scale-95">
                                    Record Custom Delivery
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Wickets & Current Lineup Panel -->
                    <div class="space-y-6">
                        <!-- Wicket Panel -->
                        <div class="rounded-2xl border border-rose-200/80 bg-rose-50/40 p-5">
                            <div class="flex items-center gap-2 text-rose-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                <h3 class="text-sm font-black uppercase tracking-wider text-rose-900">Record Wicket</h3>
                            </div>

                            <form method="POST" action="{{ route('admin.live.ball', $fixture) }}" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                <input type="hidden" name="striker" value="{{ $active->striker }}">
                                <input type="hidden" name="non_striker" value="{{ $active->non_striker }}">
                                <input type="hidden" name="bowler" value="{{ $active->current_bowler }}">
                                <input type="hidden" name="batter_runs" value="0">
                                <input type="hidden" name="extra_type" value="none">
                                <input type="hidden" name="extra_runs" value="0">
                                <input type="hidden" name="is_wicket" value="1">

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-700">Dismissal Type</label>
                                    <select name="wicket_type" required class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-800">
                                        @foreach(['bowled','caught','lbw','run out','stumped','hit wicket','retired hurt','retired out','obstructing the field','hit the ball twice'] as $type)
                                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-700">Dismissed Batter</label>
                                    <select name="dismissed_batter" required class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-800">
                                        <option value="{{ $active->striker }}">{{ $active->striker }} (Striker)</option>
                                        <option value="{{ $active->non_striker }}">{{ $active->non_striker }} (Non-Striker)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-700">Incoming Batter</label>
                                    <input name="incoming_batter" maxlength="100" class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-800 placeholder:text-slate-400" placeholder="Next batter name">
                                </div>
                                <button class="w-full rounded-xl bg-rose-600 px-4 py-3 text-xs font-black uppercase tracking-wider text-white shadow-md shadow-rose-600/20 transition-all hover:bg-rose-700 active:scale-95">
                                    Confirm & Fall of Wicket
                                </button>
                            </form>
                        </div>

                        <!-- Current Batsmen & Bowler Status -->
                        <div>
                            <h3 class="text-sm font-black text-slate-900">Current On-Field Status</h3>
                            <div class="mt-3 space-y-2.5">
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200/60 text-sm">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        <span class="font-extrabold text-slate-800">{{ $active->striker ?: 'Not set' }}</span>
                                    </div>
                                    <span class="rounded-lg bg-emerald-100/70 px-2.5 py-1 text-[10px] font-black text-emerald-800">STRIKER</span>
                                </div>
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200/60 text-sm">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                        <span class="font-extrabold text-slate-700">{{ $active->non_striker ?: 'Not set' }}</span>
                                    </div>
                                    <span class="rounded-lg bg-slate-200/60 px-2.5 py-1 text-[10px] font-black text-slate-600">NON-STRIKER</span>
                                </div>
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3.5 border border-slate-200/60 text-sm">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                                        <span class="text-slate-500 font-medium">Bowler</span>
                                    </div>
                                    <span class="font-extrabold text-slate-900">{{ $active->current_bowler ?: 'Not set' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                </div>
                <h2 class="mt-4 text-base font-black text-slate-900">Ready to start scoring?</h2>
                <p class="mt-1 text-xs text-slate-500">Save the toss setup and initialize your first innings above to activate the control board.</p>
            </section>
        @endif

        {{-- Recent Deliveries / Undo --}}
        @if($active)
            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Recent Deliveries</h2>
                        <p class="text-xs text-slate-500">Undo is available exclusively for the latest recorded ball.</p>
                    </div>
                </div>
                
                <div class="mt-5 divide-y divide-slate-100">
                    @forelse($active->deliveries->sortByDesc('ball_number')->take(12) as $ball)
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                            <div class="space-y-0.5">
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $ball->striker }} <span class="text-slate-400 font-normal">vs</span> {{ $ball->bowler }}
                                </p>
                                <p class="text-xs text-slate-500 flex items-center gap-2">
                                    <span class="font-medium text-slate-600">{{ $ball->extra_type === 'none' ? 'Bat runs' : ucfirst(str_replace('_', ' ', $ball->extra_type)) }}</span>
                                    <span>&bull;</span>
                                    <strong class="text-indigo-900">{{ $ball->totalRuns() }} run(s)</strong>
                                    @if($ball->is_wicket)
                                        <span class="rounded-md bg-rose-100 px-1.5 py-0.5 text-[10px] font-black text-rose-800 uppercase">WICKET: {{ $ball->wicket_type }}</span>
                                    @endif
                                </p>
                            </div>
                            @if($loop->first)
                                <form method="POST" action="{{ route('admin.live.undo', [$fixture, $ball]) }}"
                                      onsubmit="return confirm('Undo the latest delivery? This will officially alter the score.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/50 px-3 py-2 text-xs font-black text-rose-700 transition-all hover:bg-rose-100 active:scale-95">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        Undo Latest
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs font-medium text-slate-400">No deliveries recorded in this innings yet.</p>
                    @endforelse
                </div>
            </section>
        @endif

    </div>
</div>
@endsection