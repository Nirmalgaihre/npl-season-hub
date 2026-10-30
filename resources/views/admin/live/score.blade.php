@extends('layouts.admin')

@section('title', 'Live Scoring')

@section('content')
<div class="min-h-screen bg-slate-100/70 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-8">

        {{-- Header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 rounded-full bg-orange-100/80 px-3 py-1 text-xs font-black uppercase tracking-widest text-orange-600">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-orange-500"></span>
                    NPL Season Hub &bull; Match Operations
                </div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Live Scoring</h1>
                <p class="text-sm font-medium text-slate-500">
                    {{ $fixture->homeTeam->name ?? 'Home' }}
                    <span class="font-normal text-slate-300">vs</span>
                    {{ $fixture->awayTeam->name ?? 'Away' }}
                </p>
            </div>

            <a href="{{ route('live.show', $fixture) }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-900 px-5 py-3.5 text-sm font-bold text-white shadow-xl transition hover:bg-indigo-800 active:scale-95">
                Open public scoreboard
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </header>

        {{-- Alerts --}}
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-800">
                <p class="font-black">Please fix the following validation errors:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Match Banner --}}
        <section class="rounded-3xl bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 p-6 text-white shadow-2xl sm:p-8">
            <div class="grid gap-6 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-300">Home Team</span>
                    <h2 class="mt-1 text-2xl font-black sm:text-3xl">{{ $fixture->homeTeam->name ?? 'Home team' }}</h2>
                </div>

                <div class="flex justify-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-xs font-black">VS</span>
                </div>

                <div class="sm:text-right">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-300">Away Team</span>
                    <h2 class="mt-1 text-2xl font-black sm:text-3xl">{{ $fixture->awayTeam->name ?? 'Away team' }}</h2>
                </div>
            </div>

            <div class="mt-6 border-t border-white/10 pt-4 text-sm text-indigo-200">
                Venue: {{ $fixture->venue ?: 'Venue not set' }}
            </div>
        </section>

        {{-- Toss and Start Innings --}}
        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Toss --}}
            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl sm:p-7">
                <h2 class="text-lg font-black text-slate-900">Toss & Match Setup</h2>
                <p class="mt-1 text-xs text-slate-500">Record the toss before the first innings.</p>

                <form method="POST" action="{{ route('admin.live.toss', $fixture) }}" class="mt-5 space-y-4">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Toss Winner</label>
                            <select name="toss_winner_team_id" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800">
                                <option value="">Choose team</option>
                                <option value="{{ $fixture->home_team_id }}" @selected(old('toss_winner_team_id', $fixture->toss_winner_team_id) == $fixture->home_team_id)>
                                    {{ $fixture->homeTeam->name ?? 'Home' }}
                                </option>
                                <option value="{{ $fixture->away_team_id }}" @selected(old('toss_winner_team_id', $fixture->toss_winner_team_id) == $fixture->away_team_id)>
                                    {{ $fixture->awayTeam->name ?? 'Away' }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Decision</label>
                            <select name="toss_decision" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800">
                                <option value="bat" @selected(old('toss_decision', $fixture->toss_decision) === 'bat')>Bat First</option>
                                <option value="field" @selected(old('toss_decision', $fixture->toss_decision) === 'field')>Field First</option>
                            </select>
                        </div>
                    </div>

                    <button class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-extrabold text-white hover:bg-slate-800">
                        Save Toss Decision
                    </button>
                </form>

                @if($fixture->tossWinner)
                    <div class="mt-4 rounded-xl bg-indigo-50 p-3 text-xs font-semibold text-indigo-900">
                        Toss: <strong>{{ $fixture->tossWinner->name }}</strong>
                        elected to <strong>{{ $fixture->toss_decision }}</strong>.
                    </div>
                @endif
            </section>

            {{-- Start Innings --}}
            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl sm:p-7">
                <h2 class="text-lg font-black text-slate-900">Start Innings</h2>
                <p class="mt-1 text-xs text-slate-500">Select the batting team, two batters and an opening bowler.</p>

                <form method="POST" action="{{ route('admin.live.start', $fixture) }}" id="start-innings-form" class="mt-5 space-y-4">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Innings</label>
                            <select name="innings_number" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800">
                                <option value="1" @selected(old('innings_number', 1) == 1)>1st Innings</option>
                                <option value="2" @selected(old('innings_number') == 2)>2nd Innings</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Batting Team</label>
                            <select name="batting_team_id" id="batting_team_id" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-3 text-sm font-semibold text-slate-800">
                                <option value="{{ $fixture->home_team_id }}"
                                    @selected(old('batting_team_id', $fixture->home_team_id) == $fixture->home_team_id)>
                                    {{ $fixture->homeTeam->name ?? 'Home' }}
                                </option>
                                <option value="{{ $fixture->away_team_id }}"
                                    @selected(old('batting_team_id') == $fixture->away_team_id)>
                                    {{ $fixture->awayTeam->name ?? 'Away' }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        {{-- Striker --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Striker</label>
                            <select name="striker" id="striker" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-2.5 text-sm font-semibold text-slate-800">
                                <option value="">Select striker</option>
                                @foreach($players as $player)
                                    <option value="{{ $player->name }}"
                                            data-team-id="{{ $player->team_id }}"
                                            @selected(old('striker') === $player->name)>
                                        {{ $player->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Non-Striker --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Non-Striker</label>
                            <select name="non_striker" id="non_striker" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-2.5 text-sm font-semibold text-slate-800">
                                <option value="">Select non-striker</option>
                                @foreach($players as $player)
                                    <option value="{{ $player->name }}"
                                            data-team-id="{{ $player->team_id }}"
                                            @selected(old('non_striker') === $player->name)>
                                        {{ $player->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Opening Bowler --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Opening Bowler</label>
                            <select name="current_bowler" id="current_bowler" required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 py-2.5 text-sm font-semibold text-slate-800">
                                <option value="">Select opening bowler</option>
                                @foreach($players as $player)
                                    <option value="{{ $player->name }}"
                                            data-team-id="{{ $player->team_id }}"
                                            @selected(old('current_bowler') === $player->name)>
                                        {{ $player->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button class="w-full rounded-xl bg-gradient-to-r from-orange-500 to-rose-600 px-4 py-3 text-sm font-black text-white hover:opacity-95">
                        Start Innings
                    </button>
                </form>

                <p class="mt-3 text-xs font-medium text-amber-700">
                    Innings with recorded deliveries cannot be restarted.
                </p>
            </section>
        </div>

        {{-- Innings Overview --}}
        <section class="grid gap-4 sm:grid-cols-2">
            @forelse($innings as $inning)
                <article class="rounded-3xl border bg-white p-6 {{ $inning->is_current ? 'border-orange-500 ring-2 ring-orange-400/20 shadow-xl' : 'border-slate-200 shadow-sm' }}">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400">
                            Innings {{ $inning->innings_number }}
                        </span>
                        @if($inning->is_current)
                            <span class="rounded-full bg-emerald-500 px-3 py-1 text-[11px] font-black text-white">LIVE</span>
                        @endif
                    </div>

                    <h3 class="mt-2 text-lg font-black text-slate-800">
                        {{ $inning->battingTeam->name ?? 'Batting team' }}
                    </h3>

                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-4xl font-black text-indigo-950">{{ $inning->total_runs }}</span>
                        <span class="text-2xl font-black text-slate-400">/{{ $inning->wickets }}</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-500">
                        <span>Overs: <strong class="text-slate-900">{{ $inning->oversText() }}</strong></span>
                        <span>Extras: <strong class="text-slate-900">{{ $inning->extras }}</strong></span>
                        <span>Run Rate: <strong class="text-slate-900">{{ number_format($inning->runRate(), 2) }}</strong></span>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-400 sm:col-span-2">
                    No innings started yet.
                </div>
            @endforelse
        </section>

        @php($active = $innings->firstWhere('is_current', true))

        {{-- Active Scoring Console --}}
        @if($active)
            <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl">
                <div class="bg-indigo-950 p-6 text-white sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-widest text-orange-400">Active Live Scoring</span>
                            <h2 class="mt-1 text-2xl font-black">{{ $active->battingTeam->name ?? 'Batting team' }}</h2>
                        </div>
                        @if($active->free_hit_next)
                            <span class="animate-pulse rounded-xl bg-orange-500 px-4 py-2 text-xs font-black uppercase">FREE HIT DELIVERY</span>
                        @endif
                    </div>

                    <div class="mt-6 flex flex-wrap items-end gap-8 border-t border-white/10 pt-6">
                        <div>
                            <span class="block text-xs font-bold uppercase text-indigo-300">Score</span>
                            <span class="text-5xl font-black">{{ $active->total_runs }}/{{ $active->wickets }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase text-indigo-300">Overs</span>
                            <span class="text-2xl font-black">{{ $active->oversText() }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase text-indigo-300">Run Rate</span>
                            <span class="text-2xl font-black">{{ number_format($active->runRate(), 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[1.2fr_.8fr]">
                    {{-- Scoring Controls --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-base font-black text-slate-900">Tap to Score Runs</h3>
                            <p class="text-xs text-slate-500">Each action records a delivery.</p>

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

                                        <button class="flex h-16 w-full flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-xl font-black text-indigo-950 transition hover:border-orange-500 hover:bg-orange-500 hover:text-white">
                                            {{ $run }}
                                            <span class="text-[9px] font-bold">RUNS</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>

                        {{-- Quick Extras --}}
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

                                        <button class="w-full rounded-2xl border border-orange-200 bg-orange-50 px-3 py-3.5 text-xs font-black text-orange-900 hover:bg-orange-100">
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>

                        {{-- Detailed Delivery --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h4 class="text-sm font-black text-slate-900">Detailed Delivery Form</h4>
                            <p class="text-xs text-slate-500">Custom extras, runs and bowler changes.</p>

                            <form method="POST" action="{{ route('admin.live.ball', $fixture) }}" class="mt-4 space-y-4">
                                @csrf
                                <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                <input type="hidden" name="striker" value="{{ $active->striker }}">
                                <input type="hidden" name="non_striker" value="{{ $active->non_striker }}">

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Bowler</label>
                                        <input name="bowler" value="{{ $active->current_bowler }}" required
                                               class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs font-semibold">
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Batter Runs</label>
                                        <select name="batter_runs" class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs">
                                            @foreach([0,1,2,3,4,5,6] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Extra Type</label>
                                        <select name="extra_type" class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs">
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
                                        <input name="extra_runs" type="number" min="0" max="7" value="0" required
                                               class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs">
                                    </div>
                                </div>

                                <button class="w-full rounded-xl bg-indigo-900 px-4 py-3 text-xs font-black uppercase text-white hover:bg-indigo-800">
                                    Record Custom Delivery
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Wicket and Lineup --}}
                    <div class="space-y-6">
                        <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5">
                            <h3 class="text-sm font-black uppercase tracking-wider text-rose-900">Record Wicket</h3>

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
                                    <select name="wicket_type" required class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs">
                                        @foreach(['bowled','caught','lbw','run out','stumped','hit wicket','retired hurt','retired out','obstructing the field','hit the ball twice'] as $type)
                                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-700">Dismissed Batter</label>
                                    <select name="dismissed_batter" required class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs">
                                        <option value="{{ $active->striker }}">{{ $active->striker }} (Striker)</option>
                                        <option value="{{ $active->non_striker }}">{{ $active->non_striker }} (Non-Striker)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-700">Incoming Batter</label>
                                    <select name="incoming_batter" id="incoming_batter" class="w-full rounded-xl border-slate-200 bg-white py-2.5 text-xs">
                                        <option value="">Select incoming batter (optional)</option>
                                        @foreach($players as $player)
                                            <option value="{{ $player->name }}"
                                                    data-team-id="{{ $player->team_id }}"
                                                    @selected(old('incoming_batter') === $player->name)>
                                                {{ $player->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button class="w-full rounded-xl bg-rose-600 px-4 py-3 text-xs font-black uppercase text-white hover:bg-rose-700">
                                    Confirm & Fall of Wicket
                                </button>
                            </form>
                        </div>

                        {{-- Current Status --}}
                        <div>
                            <h3 class="text-sm font-black text-slate-900">Current On-Field Status</h3>
                            <div class="mt-3 space-y-2.5">
                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-3.5 text-sm">
                                    <span class="font-extrabold text-slate-800">{{ $active->striker ?: 'Not set' }}</span>
                                    <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-[10px] font-black text-emerald-800">STRIKER</span>
                                </div>

                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-3.5 text-sm">
                                    <span class="font-extrabold text-slate-700">{{ $active->non_striker ?: 'Not set' }}</span>
                                    <span class="rounded-lg bg-slate-200 px-2.5 py-1 text-[10px] font-black text-slate-600">NON-STRIKER</span>
                                </div>

                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-3.5 text-sm">
                                    <span class="font-medium text-slate-500">Bowler</span>
                                    <span class="font-extrabold text-slate-900">{{ $active->current_bowler ?: 'Not set' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <h2 class="text-base font-black text-slate-900">Ready to start scoring?</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Save the toss setup and initialize an innings above to activate the scoring board.
                </p>
            </section>
        @endif

        {{-- Recent Deliveries --}}
        @if($active)
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-7">
                <h2 class="text-lg font-black text-slate-900">Recent Deliveries</h2>
                <p class="text-xs text-slate-500">Undo is available for the latest recorded delivery.</p>

                <div class="mt-5 divide-y divide-slate-100">
                    @forelse($active->deliveries->sortByDesc('ball_number')->take(12) as $ball)
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3.5">
                            <div>
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $ball->striker }} <span class="font-normal text-slate-400">vs</span> {{ $ball->bowler }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $ball->extra_type === 'none' ? 'Bat runs' : ucfirst(str_replace('_', ' ', $ball->extra_type)) }}
                                    &bull; <strong class="text-indigo-900">{{ $ball->totalRuns() }} run(s)</strong>
                                    @if($ball->is_wicket)
                                        <span class="font-black uppercase text-rose-700">Wicket: {{ $ball->wicket_type }}</span>
                                    @endif
                                </p>
                            </div>

                            @if($loop->first)
                                <form method="POST" action="{{ route('admin.live.undo', [$fixture, $ball]) }}"
                                      onsubmit="return confirm('Undo the latest delivery? This will alter the score.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-black text-rose-700 hover:bg-rose-100">
                                        Undo Latest
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-400">No deliveries recorded in this innings yet.</p>
                    @endforelse
                </div>
            </section>
        @endif

    </div>
</div>

{{-- Player dropdown filtering --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const battingTeam = document.getElementById('batting_team_id');
    const striker = document.getElementById('striker');
    const nonStriker = document.getElementById('non_striker');
    const bowler = document.getElementById('current_bowler');
    const incomingBatter = document.getElementById('incoming_batter');

    const homeTeamId = @json((string) $fixture->home_team_id);
    const awayTeamId = @json((string) $fixture->away_team_id);

    if (!battingTeam || !striker || !nonStriker || !bowler) {
        return;
    }

    /*
     * Keep the original option list intact.
     * Hide and disable players who do not meet the filter.
     */
    function filterPlayers(select, teamId, excludedNames = []) {
        const previousValue = select.value;

        Array.from(select.options).forEach(function (option, index) {
            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matchesTeam =
                String(option.dataset.teamId ?? '') === String(teamId);

            const notExcluded =
                !excludedNames.includes(option.value);

            const allowed = matchesTeam && notExcluded;

            option.hidden = !allowed;
            option.disabled = !allowed;
        });

        const selectedOption = select.selectedOptions[0];

        if (
            !selectedOption ||
            selectedOption.disabled ||
            selectedOption.hidden
        ) {
            select.value = '';
        } else {
            select.value = previousValue;
        }
    }

    function updateOpeningPlayers() {
        const battingTeamId = battingTeam.value;

        const bowlingTeamId =
            String(battingTeamId) === String(homeTeamId)
                ? awayTeamId
                : homeTeamId;

        filterPlayers(striker, battingTeamId);
        filterPlayers(nonStriker, battingTeamId, [striker.value].filter(Boolean));
        filterPlayers(bowler, bowlingTeamId);

        updateIncomingBatter();
    }

    function updateIncomingBatter() {
        if (!incomingBatter) {
            return;
        }

        const activeBattingTeamId = @json(isset($active) ? (string) $active->batting_team_id : null);

        if (!activeBattingTeamId) {
            return;
        }

        filterPlayers(
            incomingBatter,
            activeBattingTeamId,
            [
                @json(isset($active) ? $active->striker : ''),
                @json(isset($active) ? $active->non_striker : '')
            ].filter(Boolean)
        );
    }

    striker.addEventListener('change', function () {
        filterPlayers(
            nonStriker,
            battingTeam.value,
            [striker.value].filter(Boolean)
        );
    });

    nonStriker.addEventListener('change', function () {
        filterPlayers(
            striker,
            battingTeam.value,
            [nonStriker.value].filter(Boolean)
        );
    });

    battingTeam.addEventListener('change', updateOpeningPlayers);

    updateOpeningPlayers();
    updateIncomingBatter();
});
</script>
@endsection
