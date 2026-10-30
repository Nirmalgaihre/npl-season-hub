@extends('layouts.admin')

@section('title', 'Live Scoring')

@section('content')
@php
    $active = $innings->firstWhere('is_current', true);
    $players = $players ?? collect();

    $playerOptions = $players->map(function ($player) {
        return [
            'id' => $player->id,
            'team_id' => $player->team_id,
            'name' => $player->name,
        ];
    })->values();

    $homeId = (string) $fixture->home_team_id;
    $awayId = (string) $fixture->away_team_id;
@endphp

<div class="min-h-screen bg-slate-100/70 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-8">

        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-orange-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-orange-700">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-orange-500"></span>
                    NPL Season Hub · Match Operations
                </div>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Live Scoring
                </h1>
                <p class="mt-1 text-sm font-medium text-slate-500">
                    {{ $fixture->homeTeam->name ?? 'Home' }} vs
                    {{ $fixture->awayTeam->name ?? 'Away' }}
                </p>
            </div>

            <a href="{{ route('live.show', $fixture) }}" target="_blank"
               class="rounded-2xl bg-indigo-950 px-5 py-3 text-center text-sm font-bold text-white hover:bg-indigo-800">
                Open Public Scoreboard ↗
            </a>
        </header>

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-800">
                <p class="font-black">Please fix the following errors:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-3xl bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 p-6 text-white shadow-xl sm:p-8">
            <div class="grid gap-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-300">Home Team</p>
                    <h2 class="mt-1 text-2xl font-black">{{ $fixture->homeTeam->name ?? 'Home team' }}</h2>
                </div>
                <div class="text-center text-sm font-black text-indigo-300">VS</div>
                <div class="sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-300">Away Team</p>
                    <h2 class="mt-1 text-2xl font-black">{{ $fixture->awayTeam->name ?? 'Away team' }}</h2>
                </div>
            </div>
            <p class="mt-5 border-t border-white/10 pt-4 text-sm text-indigo-200">
                Venue: {{ $fixture->venue ?: 'Venue not set' }}
            </p>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Toss & Match Setup</h2>
                <p class="mt-1 text-xs text-slate-500">Save the toss details before scoring.</p>

                <form method="POST" action="{{ route('admin.live.toss', $fixture) }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Toss Winner</label>
                        <select name="toss_winner_team_id" required class="field">
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
                        <label class="mb-1 block text-xs font-bold text-slate-600">Decision</label>
                        <select name="toss_decision" required class="field">
                            <option value="bat" @selected(old('toss_decision', $fixture->toss_decision) === 'bat')>Bat First</option>
                            <option value="field" @selected(old('toss_decision', $fixture->toss_decision) === 'field')>Field First</option>
                        </select>
                    </div>

                    <button class="primary-button w-full" type="submit">Save Toss Decision</button>
                </form>

                @if($fixture->tossWinner)
                    <p class="mt-4 rounded-xl bg-indigo-50 p-3 text-xs text-indigo-900">
                        Toss: <strong>{{ $fixture->tossWinner->name }}</strong>
                        elected to <strong>{{ $fixture->toss_decision }}</strong>.
                    </p>
                @endif
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Start Innings</h2>
                <p class="mt-1 text-xs text-slate-500">Select the opening batters and bowling player.</p>

                <form method="POST" action="{{ route('admin.live.start', $fixture) }}" class="mt-5 space-y-4" id="start-innings-form">
                    @csrf

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Innings</label>
                        <select name="innings_number" required class="field">
                            <option value="1">1st Innings</option>
                            <option value="2">2nd Innings</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Batting Team</label>
                        <select name="batting_team_id" id="opening-batting-team" required class="field">
                            <option value="{{ $fixture->home_team_id }}">{{ $fixture->homeTeam->name ?? 'Home' }}</option>
                            <option value="{{ $fixture->away_team_id }}">{{ $fixture->awayTeam->name ?? 'Away' }}</option>
                        </select>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">Striker</label>
                            <select name="striker" id="opening-striker" required class="field player-select" data-team-source="opening-batting-team" data-role="batting">
                                <option value="">Choose striker</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">Non-Striker</label>
                            <select name="non_striker" id="opening-non-striker" required class="field player-select" data-team-source="opening-batting-team" data-role="batting">
                                <option value="">Choose non-striker</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Opening Bowler</label>
                        <select name="current_bowler" id="opening-bowler" required class="field player-select" data-team-source="opening-batting-team" data-role="bowling">
                            <option value="">Choose opening bowler</option>
                        </select>
                    </div>

                    <button type="submit" class="primary-button w-full">Start Innings</button>
                </form>

                <p class="mt-3 text-xs text-amber-700">
                    An innings with recorded deliveries cannot be restarted.
                </p>
            </section>
        </div>

        <section class="grid gap-4 sm:grid-cols-2">
            @forelse($innings as $inning)
                <article class="rounded-3xl border {{ $inning->is_current ? 'border-orange-400 ring-2 ring-orange-100' : 'border-slate-200' }} bg-white p-6">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400">
                            Innings {{ $inning->innings_number }}
                        </span>
                        @if($inning->is_current)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">LIVE</span>
                        @endif
                    </div>
                    <h3 class="mt-2 text-lg font-black text-slate-800">
                        {{ $inning->battingTeam->name ?? 'Batting team' }}
                    </h3>
                    <p class="mt-2 text-4xl font-black text-indigo-950">
                        {{ $inning->total_runs }}/{{ $inning->wickets }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-4 border-t border-slate-100 pt-4 text-xs text-slate-500">
                        <span>Overs: <strong>{{ $inning->oversText() }}</strong></span>
                        <span>Extras: <strong>{{ $inning->extras }}</strong></span>
                        <span>Run rate: <strong>{{ number_format($inning->runRate(), 2) }}</strong></span>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 sm:col-span-2">
                    No innings started yet.
                </div>
            @endforelse
        </section>

        @if($active)
            @php
                $activeBattingTeamId = (string) $active->batting_team_id;
                $activeBowlingTeamId = (string) $active->bowling_team_id;
                $currentDeliveries = $active->deliveries->sortBy('id');
                $latestDelivery = $currentDeliveries->last();
                $overComplete = ((int) $active->legal_balls > 0 && (int) $active->legal_balls % 6 === 0);
            @endphp

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
                <div class="bg-indigo-950 p-6 text-white sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-orange-400">Active Live Scoring</p>
                            <h2 class="mt-1 text-2xl font-black">{{ $active->battingTeam->name ?? 'Batting team' }}</h2>
                            <p class="mt-1 text-xs text-indigo-300">
                                Bowling: {{ $active->bowlingTeam->name ?? 'Bowling team' }}
                            </p>
                        </div>
                        @if($active->free_hit_next)
                            <span class="rounded-xl bg-orange-500 px-3 py-2 text-xs font-black">FREE HIT</span>
                        @endif
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-4 border-t border-white/10 pt-6">
                        <div>
                            <p class="text-xs font-bold uppercase text-indigo-300">Score</p>
                            <p class="text-3xl font-black sm:text-5xl">{{ $active->total_runs }}/{{ $active->wickets }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-indigo-300">Overs</p>
                            <p class="text-2xl font-black sm:text-3xl">{{ $active->oversText() }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-indigo-300">Run Rate</p>
                            <p class="text-2xl font-black sm:text-3xl">{{ number_format($active->runRate(), 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-8 p-5 sm:p-8 lg:grid-cols-[1.2fr_.8fr]">

                    <div class="space-y-6">

                        <section class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5">
                            <h3 class="text-base font-black text-slate-900">Choose Players for Next Delivery</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Select the batters and bowler before using quick runs or extras.
                            </p>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-600">Striker</label>
                                    <select id="live-striker" class="field live-player" data-team-id="{{ $activeBattingTeamId }}" data-placeholder="Choose striker">
                                        <option value="{{ $active->striker }}">{{ $active->striker }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-600">Non-Striker</label>
                                    <select id="live-non-striker" class="field live-player" data-team-id="{{ $activeBattingTeamId }}" data-placeholder="Choose non-striker">
                                        <option value="{{ $active->non_striker }}">{{ $active->non_striker }}</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-bold text-slate-600">Current / Next Bowler</label>
                                    <select id="live-bowler" class="field live-player" data-team-id="{{ $activeBowlingTeamId }}" data-placeholder="Choose bowler">
                                        <option value="{{ $active->current_bowler }}">{{ $active->current_bowler }}</option>
                                    </select>
                                    @if($overComplete)
                                        <p class="mt-2 text-xs font-bold text-orange-700">
                                            Over complete — select the next bowler before recording another ball.
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <p id="player-selection-warning" class="mt-3 hidden text-xs font-bold text-rose-600">
                                Select two different batters and a bowler.
                            </p>
                        </section>

                        <section>
                            <h3 class="text-base font-black text-slate-900">Tap to Score Runs</h3>
                            <p class="mt-1 text-xs text-slate-500">Quick scoring uses the players selected above.</p>

                            <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-7">
                                @foreach([0, 1, 2, 3, 4, 5, 6] as $run)
                                    <form method="POST" action="{{ route('admin.live.ball', $fixture) }}" data-live-delivery-form>
                                        @csrf
                                        <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                        <input type="hidden" name="striker" value="{{ $active->striker }}" data-player-field="striker">
                                        <input type="hidden" name="non_striker" value="{{ $active->non_striker }}" data-player-field="non_striker">
                                        <input type="hidden" name="bowler" value="{{ $active->current_bowler }}" data-player-field="bowler">
                                        <input type="hidden" name="batter_runs" value="{{ $run }}">
                                        <input type="hidden" name="extra_type" value="none">
                                        <input type="hidden" name="extra_runs" value="0">

                                        <button type="submit" class="run-button">
                                            <span>{{ $run }}</span>
                                            <span class="text-[9px] font-bold">RUNS</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </section>

                        <section>
                            <h3 class="text-base font-black text-slate-900">Quick Extras</h3>
                            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach([
                                    ['wide', 'Wide (+1)', 0, 1],
                                    ['no_ball', 'No-ball (+1)', 0, 1],
                                    ['bye', 'Bye (+1)', 0, 1],
                                    ['leg_bye', 'Leg-bye (+1)', 0, 1],
                                ] as [$type, $label, $batRuns, $extraRuns])
                                    <form method="POST" action="{{ route('admin.live.ball', $fixture) }}" data-live-delivery-form>
                                        @csrf
                                        <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                        <input type="hidden" name="striker" value="{{ $active->striker }}" data-player-field="striker">
                                        <input type="hidden" name="non_striker" value="{{ $active->non_striker }}" data-player-field="non_striker">
                                        <input type="hidden" name="bowler" value="{{ $active->current_bowler }}" data-player-field="bowler">
                                        <input type="hidden" name="batter_runs" value="{{ $batRuns }}">
                                        <input type="hidden" name="extra_type" value="{{ $type }}">
                                        <input type="hidden" name="extra_runs" value="{{ $extraRuns }}">

                                        <button type="submit" class="w-full rounded-2xl border border-orange-200 bg-orange-50 px-3 py-3 text-xs font-black text-orange-900 hover:bg-orange-100">
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h3 class="text-sm font-black text-slate-900">Detailed Delivery Form</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Select a bowler for this delivery. You can also choose the next bowler at the end of the over.
                            </p>

                            <form method="POST" action="{{ route('admin.live.ball', $fixture) }}" class="mt-4 space-y-4" data-live-delivery-form id="detailed-delivery-form">
                                @csrf
                                <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                <input type="hidden" name="striker" value="{{ $active->striker }}" data-player-field="striker">
                                <input type="hidden" name="non_striker" value="{{ $active->non_striker }}" data-player-field="non_striker">

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-600">Bowler for This Delivery</label>
                                    <select name="bowler" id="detailed-bowler" required class="field live-player" data-team-id="{{ $activeBowlingTeamId }}" data-placeholder="Choose bowler">
                                        <option value="{{ $active->current_bowler }}">{{ $active->current_bowler }}</option>
                                    </select>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Batter Runs</label>
                                        <select name="batter_runs" class="field">
                                            @foreach([0, 1, 2, 3, 4, 5, 6] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Extra Type</label>
                                        <select name="extra_type" id="detail-extra-type" class="field">
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
                                        <input name="extra_runs" type="number" min="0" max="7" value="0" required class="field">
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Delivery Note (Optional)</label>
                                        <input name="note" maxlength="255" class="field" placeholder="Optional note">
                                    </div>
                                </div>

                                <button type="submit" class="primary-button w-full">Record Custom Delivery</button>
                            </form>
                        </section>
                    </div>

                    <div class="space-y-6">

                        <section class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5">
                            <h3 class="text-sm font-black uppercase tracking-wider text-rose-900">Record Wicket</h3>

                            <form method="POST" action="{{ route('admin.live.ball', $fixture) }}" class="mt-4 space-y-3" data-live-delivery-form id="wicket-form">
                                @csrf
                                <input type="hidden" name="innings_id" value="{{ $active->id }}">
                                <input type="hidden" name="striker" value="{{ $active->striker }}" data-player-field="striker">
                                <input type="hidden" name="non_striker" value="{{ $active->non_striker }}" data-player-field="non_striker">
                                <input type="hidden" name="bowler" value="{{ $active->current_bowler }}" data-player-field="bowler">
                                <input type="hidden" name="batter_runs" value="0">
                                <input type="hidden" name="extra_type" value="none">
                                <input type="hidden" name="extra_runs" value="0">
                                <input type="hidden" name="is_wicket" value="1">

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-600">Dismissal Type</label>
                                    <select name="wicket_type" required class="field">
                                        @foreach([
                                            'bowled', 'caught', 'lbw', 'run out', 'stumped',
                                            'hit wicket', 'retired hurt', 'retired out',
                                            'obstructing the field', 'hit the ball twice',
                                            'handled the ball'
                                        ] as $type)
                                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-600">Dismissed Batter</label>
                                    <select name="dismissed_batter" id="dismissed-batter" required class="field">
                                        <option value="{{ $active->striker }}">{{ $active->striker }} (Striker)</option>
                                        <option value="{{ $active->non_striker }}">{{ $active->non_striker }} (Non-Striker)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-600">Incoming Batter</label>
                                    <select name="incoming_batter" id="incoming-batter" class="field">
                                        <option value="">Choose incoming batter</option>
                                    </select>
                                </div>

                                <button type="submit" class="w-full rounded-xl bg-rose-600 px-4 py-3 text-xs font-black uppercase text-white hover:bg-rose-700">
                                    Confirm & Fall of Wicket
                                </button>
                            </form>
                        </section>

                        <section>
                            <h3 class="text-sm font-black text-slate-900">Current On-Field Status</h3>
                            <div class="mt-3 space-y-2">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-[10px] font-black uppercase text-emerald-700">Striker</p>
                                    <p id="status-striker" class="mt-1 font-bold text-slate-900">{{ $active->striker ?: 'Not set' }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-[10px] font-black uppercase text-slate-500">Non-Striker</p>
                                    <p id="status-non-striker" class="mt-1 font-bold text-slate-900">{{ $active->non_striker ?: 'Not set' }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-[10px] font-black uppercase text-indigo-700">Current Bowler</p>
                                    <p id="status-bowler" class="mt-1 font-bold text-slate-900">{{ $active->current_bowler ?: 'Not set' }}</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        @else
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <h2 class="text-base font-black text-slate-900">Ready to start scoring?</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Choose the batting team, two opening batters, and an opening bowler above.
                </p>
            </section>
        @endif

        @if($active)
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Recent Deliveries</h2>
                    <p class="mt-1 text-xs text-slate-500">Only the latest delivery can be undone.</p>
                </div>

                <div class="mt-5 divide-y divide-slate-100">
                    @forelse($active->deliveries->sortByDesc('id')->take(12) as $ball)
                        <div class="flex flex-wrap items-center justify-between gap-3 py-4">
                            <div>
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $ball->striker ?? 'Unknown batter' }}
                                    <span class="font-normal text-slate-400">vs</span>
                                    {{ $ball->bowler ?? 'Unknown bowler' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ ($ball->extra_type ?? 'none') === 'none'
                                        ? 'Bat runs'
                                        : ucfirst(str_replace('_', ' ', $ball->extra_type)) }}
                                    · <strong class="text-indigo-900">{{ $ball->totalRuns() }} run(s)</strong>
                                    @if($ball->is_wicket)
                                        · <strong class="text-rose-700">Wicket: {{ $ball->wicket_type }}</strong>
                                    @endif
                                </p>
                            </div>

                            @if($latestDelivery && (int) $ball->id === (int) $latestDelivery->id)
                                <form method="POST"
                                      action="{{ route('admin.live.undo', ['fixture' => $fixture->id, 'delivery' => $latestDelivery->id]) }}"
                                      onsubmit="return confirm('Undo the latest delivery? This will alter the recorded score.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-black text-rose-700 hover:bg-rose-100">
                                        Undo Latest
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-400">No deliveries recorded yet.</p>
                    @endforelse
                </div>
            </section>
        @endif

    </div>
</div>

<style>
    .field {
        display: block;
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: .75rem;
        background: #fff;
        padding: .7rem .8rem;
        font-size: .875rem;
        font-weight: 600;
        color: #0f172a;
    }

    .field:focus {
        outline: 2px solid transparent;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgb(79 70 229 / 12%);
    }

    .primary-button {
        border-radius: .75rem;
        background: #312e81;
        padding: .85rem 1rem;
        color: white;
        font-size: .875rem;
        font-weight: 800;
        transition: background .15s ease;
    }

    .primary-button:hover {
        background: #3730a3;
    }

    .run-button {
        display: flex;
        height: 4rem;
        width: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #f8fafc;
        color: #1e1b4b;
        font-size: 1.25rem;
        font-weight: 900;
        transition: all .15s ease;
    }

    .run-button:hover {
        border-color: #f97316;
        background: #f97316;
        color: #fff;
    }

    .run-button:disabled,
    .primary-button:disabled {
        cursor: wait;
        opacity: .6;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const players = @json($playerOptions);
    const homeId = @json($homeId);
    const awayId = @json($awayId);

    const openingTeam = document.getElementById('opening-batting-team');
    const openingStriker = document.getElementById('opening-striker');
    const openingNonStriker = document.getElementById('opening-non-striker');
    const openingBowler = document.getElementById('opening-bowler');

    const liveStriker = document.getElementById('live-striker');
    const liveNonStriker = document.getElementById('live-non-striker');
    const liveBowler = document.getElementById('live-bowler');
    const detailedBowler = document.getElementById('detailed-bowler');

    const incomingBatter = document.getElementById('incoming-batter');
    const dismissedBatter = document.getElementById('dismissed-batter');

    function teamPlayers(teamId) {
        return players.filter(function (player) {
            return String(player.team_id) === String(teamId);
        });
    }

    function otherTeamId(teamId) {
        return String(teamId) === homeId ? awayId : homeId;
    }

    function fillSelect(select, items, placeholder, selectedValue) {
        if (!select) return;

        const previous = selectedValue !== undefined
            ? String(selectedValue)
            : String(select.value || '');

        select.innerHTML = '';

        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = placeholder;
        select.appendChild(emptyOption);

        items.forEach(function (player) {
            const option = document.createElement('option');
            option.value = player.name;
            option.textContent = player.name;
            select.appendChild(option);
        });

        if (items.some(function (player) {
            return player.name === previous;
        })) {
            select.value = previous;
        } else if (items.length === 1) {
            select.value = items[0].name;
        }
    }

    function fillOpeningSelections() {
        const battingId = openingTeam.value;
        const battingPlayers = teamPlayers(battingId);
        const bowlingPlayers = teamPlayers(otherTeamId(battingId));

        fillSelect(openingStriker, battingPlayers, 'Choose striker');
        fillSelect(openingNonStriker, battingPlayers, 'Choose non-striker');
        fillSelect(openingBowler, bowlingPlayers, 'Choose opening bowler');

        if (openingStriker.value && openingNonStriker.value === openingStriker.value) {
            openingNonStriker.value = '';
        }
    }

    function fillActiveSelections() {
        if (!liveStriker) return;

        const battingId = liveStriker.dataset.teamId;
        const bowlingId = liveBowler.dataset.teamId;

        const battingPlayers = teamPlayers(battingId);
        const bowlingPlayers = teamPlayers(bowlingId);

        fillSelect(
            liveStriker,
            battingPlayers,
            'Choose striker',
            @json($active->striker)
        );

        fillSelect(
            liveNonStriker,
            battingPlayers,
            'Choose non-striker',
            @json($active->non_striker)
        );

        fillSelect(
            liveBowler,
            bowlingPlayers,
            'Choose current / next bowler',
            @json($active->current_bowler)
        );

        fillSelect(
            detailedBowler,
            bowlingPlayers,
            'Choose bowler for this delivery',
            @json($active->current_bowler)
        );

        fillSelect(incomingBatter, battingPlayers, 'Choose incoming batter');

        updateSelectedPlayers();
    }

    function updateSelectedPlayers() {
        if (!liveStriker) return;

        const striker = liveStriker.value;
        const nonStriker = liveNonStriker.value;
        const bowler = liveBowler.value;

        document.querySelectorAll('[data-player-field="striker"]').forEach(function (input) {
            input.value = striker;
        });

        document.querySelectorAll('[data-player-field="non_striker"]').forEach(function (input) {
            input.value = nonStriker;
        });

        document.querySelectorAll('[data-player-field="bowler"]').forEach(function (input) {
            input.value = bowler;
        });

        document.getElementById('status-striker').textContent = striker || 'Not set';
        document.getElementById('status-non-striker').textContent = nonStriker || 'Not set';
        document.getElementById('status-bowler').textContent = bowler || 'Not set';

        const warning = document.getElementById('player-selection-warning');

        if (warning) {
            warning.classList.toggle(
                'hidden',
                Boolean(striker && nonStriker && bowler && striker !== nonStriker)
            );
        }
    }

    if (openingTeam) {
        openingTeam.addEventListener('change', fillOpeningSelections);
        fillOpeningSelections();
    }

    if (liveStriker) {
        fillActiveSelections();

        [liveStriker, liveNonStriker, liveBowler].forEach(function (select) {
            select.addEventListener('change', updateSelectedPlayers);
        });

        if (detailedBowler) {
            detailedBowler.addEventListener('change', function () {
                liveBowler.value = detailedBowler.value;
                updateSelectedPlayers();
            });
        }
    }

    if (dismissedBatter) {
        dismissedBatter.addEventListener('change', function () {
            // Incoming batter selection remains separate from the dismissed player.
        });
    }

    document.querySelectorAll('form[data-live-delivery-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            if (!form.checkValidity()) {
                return;
            }

            if (liveStriker) {
                const striker = liveStriker.value;
                const nonStriker = liveNonStriker.value;
                const bowler = form.querySelector('[name="bowler"]')?.value
                    || liveBowler.value;

                if (!striker || !nonStriker || !bowler || striker === nonStriker) {
                    event.preventDefault();
                    alert('Choose two different batters and a bowler before recording this delivery.');
                    return;
                }

                form.querySelector('[name="striker"]').value = striker;
                form.querySelector('[name="non_striker"]').value = nonStriker;
                form.querySelector('[name="bowler"]').value = bowler;
            }

            form.dataset.submitting = 'true';

            const button = form.querySelector('button[type="submit"]');

            if (button) {
                button.disabled = true;
                button.dataset.originalText = button.textContent;
                button.textContent = 'Saving delivery…';
            }
        });
    });

    const wicketForm = document.getElementById('wicket-form');

    if (wicketForm && liveStriker) {
        wicketForm.addEventListener('submit', function (event) {
            const dismissed = dismissedBatter.value;
            const incoming = incomingBatter.value;

            if (dismissed && incoming && dismissed === incoming) {
                event.preventDefault();
                alert('The incoming batter must be different from the dismissed batter.');
            }
        });
    }
});
</script>
@endsection
