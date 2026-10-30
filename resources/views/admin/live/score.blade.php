@extends('layouts.admin')

@section('title', 'Live Scoring')

@section('content')
@php
    $players = $players ?? collect();
    $innings = $innings ?? collect();

    $currentInnings = $innings->last();

    $currentDeliveries = $currentInnings
        ? $currentInnings->deliveries
        : collect();

    /*
     * Keep player choices tied to the current innings.
     * Supports the common team_id field, a loaded team relation,
     * or a many-to-many pivot containing team_id.
     */
    $battingTeamId = $currentInnings->batting_team_id
        ?? ($currentInnings->battingTeam->id ?? null);

    $bowlingTeamId = $currentInnings->bowling_team_id
        ?? ($currentInnings->bowlingTeam->id ?? null);

    $playerTeamId = static function ($player) {
        return $player->team_id
            ?? ($player->team->id ?? null)
            ?? ($player->pivot->team_id ?? null);
    };

    $battingPlayers = $players->filter(function ($player) use ($playerTeamId, $battingTeamId) {
        return $battingTeamId !== null
            && (string) $playerTeamId($player) === (string) $battingTeamId;
    })->values();

    $bowlingPlayers = $players->filter(function ($player) use ($playerTeamId, $bowlingTeamId) {
        return $bowlingTeamId !== null
            && (string) $playerTeamId($player) === (string) $bowlingTeamId;
    })->values();

    $totalRuns = $currentInnings
        ? (int) ($currentInnings->total_runs ?? $currentInnings->runs ?? 0)
        : 0;

    $totalWickets = $currentInnings
        ? (int) ($currentInnings->wickets ?? 0)
        : 0;

    $inningsNumber = $currentInnings
        ? (int) ($currentInnings->innings_number ?? 1)
        : 1;
@endphp

<div class="min-h-screen bg-slate-100 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <header class="flex flex-col justify-between gap-4 rounded-2xl bg-[#202766] p-6 text-white shadow-lg sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-300">
                    NPL Season Hub
                </p>

                <h1 class="mt-2 text-2xl font-bold sm:text-3xl">
                    Live Scoring
                </h1>

                <p class="mt-2 text-sm text-white/70">
                    Manage innings, deliveries, extras, wickets and bowling changes.
                </p>
            </div>

            <a
                href="{{ url()->previous() }}"
                class="inline-flex items-center justify-center rounded-xl border border-white/20 px-4 py-2.5 text-sm font-semibold transition hover:bg-white/10"
            >
                Back
            </a>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="mb-2 font-bold">Please correct these errors:</p>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Match information --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-[#171D50] px-5 py-4 text-white">
                <h2 class="font-bold">Match Centre</h2>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-3 sm:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Home team
                    </p>
                    <p class="mt-2 text-lg font-bold text-slate-900">
                        {{ $fixture->homeTeam->name ?? 'Home Team' }}
                    </p>
                </div>

                <div class="text-center">
                    <span class="inline-flex rounded-full bg-orange-50 px-4 py-2 text-sm font-bold text-orange-700">
                        VS
                    </span>
                    <p class="mt-2 text-xs text-slate-500">
                        Fixture #{{ $fixture->id }}
                    </p>
                </div>

                <div class="sm:text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Away team
                    </p>
                    <p class="mt-2 text-lg font-bold text-slate-900">
                        {{ $fixture->awayTeam->name ?? 'Away Team' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Toss setup --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900">Toss Setup</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Select the toss winner and their decision.
                </p>
            </div>

            <form
                action="{{ route('admin.live.toss', $fixture) }}"
                method="POST"
                class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end"
            >
                @csrf

                <div>
                    <label for="toss_winner_id" class="mb-2 block text-sm font-semibold text-slate-700">
                        Toss winner
                    </label>

                    <select
                        id="toss_winner_id"
                        name="toss_winner_id"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                    >
                        <option value="">Choose team</option>
                        <option
                            value="{{ $fixture->home_team_id }}"
                            @selected(old('toss_winner_id', $fixture->toss_winner_id ?? '') == $fixture->home_team_id)
                        >
                            {{ $fixture->homeTeam->name ?? 'Home Team' }}
                        </option>
                        <option
                            value="{{ $fixture->away_team_id }}"
                            @selected(old('toss_winner_id', $fixture->toss_winner_id ?? '') == $fixture->away_team_id)
                        >
                            {{ $fixture->awayTeam->name ?? 'Away Team' }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="toss_decision" class="mb-2 block text-sm font-semibold text-slate-700">
                        Decision
                    </label>

                    <select
                        id="toss_decision"
                        name="toss_decision"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                    >
                        <option value="bat" @selected(old('toss_decision', $fixture->toss_decision ?? '') === 'bat')>
                            Bat first
                        </option>
                        <option value="bowl" @selected(old('toss_decision', $fixture->toss_decision ?? '') === 'bowl')>
                            Bowl first
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-[#202766] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#171D50]"
                >
                    Save Toss
                </button>
            </form>
        </section>

        {{-- Start innings --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900">Innings Controls</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Choose the batting and bowling teams to start an innings.
                </p>
            </div>

            <form
                action="{{ route('admin.live.start', $fixture) }}"
                method="POST"
                class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end"
            >
                @csrf

                <div>
                    <label for="batting_team_id" class="mb-2 block text-sm font-semibold text-slate-700">
                        Batting team
                    </label>

                    <select
                        id="batting_team_id"
                        name="batting_team_id"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                    >
                        <option value="">Choose batting team</option>
                        <option value="{{ $fixture->home_team_id }}">
                            {{ $fixture->homeTeam->name ?? 'Home Team' }}
                        </option>
                        <option value="{{ $fixture->away_team_id }}">
                            {{ $fixture->awayTeam->name ?? 'Away Team' }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="bowling_team_id" class="mb-2 block text-sm font-semibold text-slate-700">
                        Bowling team
                    </label>

                    <select
                        id="bowling_team_id"
                        name="bowling_team_id"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                    >
                        <option value="">Choose bowling team</option>
                        <option value="{{ $fixture->home_team_id }}">
                            {{ $fixture->homeTeam->name ?? 'Home Team' }}
                        </option>
                        <option value="{{ $fixture->away_team_id }}">
                            {{ $fixture->awayTeam->name ?? 'Away Team' }}
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-orange-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-700"
                >
                    Start Innings
                </button>
            </form>
        </section>

        {{-- Current score --}}
        <section class="overflow-hidden rounded-2xl bg-[#171D50] text-white shadow-lg">
            <div class="flex flex-col justify-between gap-3 border-b border-white/10 px-5 py-4 sm:flex-row sm:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-orange-300">
                        Current innings
                    </p>
                    <h2 class="mt-1 text-lg font-bold">
                        {{ $currentInnings->battingTeam->name ?? 'No innings started' }}
                    </h2>
                </div>

                @if ($currentInnings)
                    <span class="w-fit rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">
                        Innings {{ $inningsNumber }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-4">
                <div class="rounded-xl bg-white/5 p-4">
                    <p class="text-xs text-white/60">Runs</p>
                    <p class="mt-2 text-3xl font-extrabold">
                        {{ $totalRuns }}
                    </p>
                </div>

                <div class="rounded-xl bg-white/5 p-4">
                    <p class="text-xs text-white/60">Wickets</p>
                    <p class="mt-2 text-3xl font-extrabold">
                        {{ $totalWickets }}
                    </p>
                </div>

                <div class="rounded-xl bg-white/5 p-4">
                    <p class="text-xs text-white/60">Overs</p>
                    <p class="mt-2 text-3xl font-extrabold">
                        {{ $currentInnings->overs ?? '0.0' }}
                    </p>
                </div>

                <div class="rounded-xl bg-white/5 p-4">
                    <p class="text-xs text-white/60">Deliveries</p>
                    <p class="mt-2 text-3xl font-extrabold">
                        {{ $currentDeliveries->count() }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Innings list --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-900">Innings Summary</h2>

            @forelse ($innings as $inning)
                <div class="mb-3 flex flex-col justify-between gap-3 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center">
                    <div>
                        <p class="font-bold text-slate-900">
                            Innings {{ $inning->innings_number ?? $loop->iteration }}
                            — {{ $inning->battingTeam->name ?? 'Batting team' }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Bowling: {{ $inning->bowlingTeam->name ?? 'Bowling team' }}
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        <p class="text-xl font-extrabold text-[#202766]">
                            {{ $inning->total_runs ?? $inning->runs ?? 0 }}/{{ $inning->wickets ?? 0 }}
                        </p>
                        <p class="text-xs text-slate-500">
                            Overs: {{ $inning->overs ?? '0.0' }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                    No innings have been started for this fixture.
                </p>
            @endforelse
        </section>

        {{-- Detailed Delivery Form --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-900">Detailed Delivery Form</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Record batter runs, extras, wickets and bowling changes.
                </p>
            </div>

            @if (!$currentInnings)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Start an innings before recording deliveries.
                </div>
            @else
                <form
                    id="detailedDeliveryForm"
                    action="{{ route('admin.live.ball', $fixture) }}"
                    method="POST"
                    class="space-y-6"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="innings_id"
                        value="{{ $currentInnings->id }}"
                    >

                    {{-- Players --}}
                    <div>
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                            Choose players
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="striker" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Striker
                                </label>
                                <select
                                    name="striker"
                                    id="striker"
                                    required
                                    class="player-select w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                                    <option value="">Choose striker</option>
                                    @foreach ($battingPlayers as $player)
                                        <option value="{{ $player->name }}" data-player-id="{{ $player->id ?? $player->name }}" @selected(old('striker') == $player->name)>
                                            {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="non_striker" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Non-striker
                                </label>
                                <select
                                    name="non_striker"
                                    id="non_striker"
                                    required
                                    class="player-select w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                                    <option value="">Choose non-striker</option>
                                    @foreach ($battingPlayers as $player)
                                        <option value="{{ $player->name }}" data-player-id="{{ $player->id ?? $player->name }}" @selected(old('non_striker') == $player->name)>
                                            {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="bowler" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Current bowler
                                </label>
                                <select
                                    name="bowler"
                                    id="bowler"
                                    required
                                    class="player-select w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                                    <option value="">Choose bowler</option>
                                    @foreach ($bowlingPlayers as $player)
                                        <option value="{{ $player->name }}" @selected(old('bowler') == $player->name)>
                                            {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-500">
                                    Choose the incoming bowler when a new over begins.
                                </p>
                            </div>

                            <div class="rounded-xl border border-orange-100 bg-orange-50 p-4">
                                <p class="text-sm font-bold text-orange-900">Bowler change</p>
                                <p class="mt-1 text-xs leading-5 text-orange-800">
                                    Select the bowler who will deliver the next ball. Do not submit an extra delivery just to change the bowler.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    {{-- Batter runs --}}
                    <div>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">
                                Batter runs
                            </h3>
                            <span class="text-sm font-bold text-[#202766]">
                                Selected: <span id="selectedRuns">0</span>
                            </span>
                        </div>

                        <input
                            type="hidden"
                            name="runs"
                            id="runs"
                            value="{{ old('runs', 0) }}"
                        >

                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                            @foreach ([0, 1, 2, 3, 4, 5, 6] as $run)
                                <button
                                    type="button"
                                    data-run="{{ $run }}"
                                    class="run-button rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold transition hover:border-orange-400 hover:bg-orange-50"
                                >
                                    {{ $run }}
                                    @if ($run === 4)
                                        <span class="block text-xs font-medium">Four</span>
                                    @elseif ($run === 6)
                                        <span class="block text-xs font-medium">Six</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    {{-- Extras --}}
                    <div>
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                            Custom extras
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="extra_type" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Extra type
                                </label>
                                <select
                                    name="extra_type"
                                    id="extra_type"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                                    <option value="none">No extras</option>
                                    <option value="wide">Wide</option>
                                    <option value="no_ball">No-ball</option>
                                    <option value="bye">Bye</option>
                                    <option value="leg_bye">Leg-bye</option>
                                    <option value="penalty">Penalty</option>
                                </select>
                            </div>

                            <div>
                                <label for="extra_runs" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Extra runs
                                </label>
                                <input
                                    type="number"
                                    name="extra_runs"
                                    id="extra_runs"
                                    min="0"
                                    max="100"
                                    value="{{ old('extra_runs', 0) }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                            </div>
                        </div>

                        <p id="extrasHelp" class="mt-3 rounded-xl bg-slate-50 p-3 text-sm text-slate-600">
                            No extras selected.
                        </p>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    {{-- Wickets --}}
                    <div>
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                            Wicket details
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="wicket_type" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Wicket type
                                </label>
                                <select
                                    name="wicket_type"
                                    id="wicket_type"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                                    <option value="none">No wicket</option>
                                    <option value="bowled">Bowled</option>
                                    <option value="caught">Caught</option>
                                    <option value="lbw">LBW</option>
                                    <option value="run_out">Run out</option>
                                    <option value="stumped">Stumped</option>
                                    <option value="hit_wicket">Hit wicket</option>
                                    <option value="retired_out">Retired out</option>
                                    <option value="obstructing_field">Obstructing the field</option>
                                </select>
                            </div>

                            <div id="dismissedWrap" class="hidden">
                                <label for="dismissed_batter" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Dismissed batter
                                </label>
                                <select
                                    name="dismissed_batter"
                                    id="dismissed_batter"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                                    <option value="">Choose dismissed batter</option>
                                    @foreach ($battingPlayers as $player)
                                        <option value="{{ $player->name }}" data-player-id="{{ $player->id ?? $player->name }}">
                                            {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="incomingWrap" class="hidden sm:col-span-2">
                                <label for="incoming_batter" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Incoming batter
                                </label>
                                <select
                                    name="incoming_batter"
                                    id="incoming_batter"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                                >
                                    <option value="">Choose incoming batter</option>
                                    @foreach ($battingPlayers as $player)
                                        <option value="{{ $player->name }}" data-player-id="{{ $player->id ?? $player->name }}">
                                            {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery preview --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Delivery summary
                        </p>
                        <p id="deliverySummary" class="mt-2 font-semibold text-slate-900">
                            0 runs · No extras · No wicket
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Confirm the selected details before saving.
                        </p>
                    </div>

                    {{-- Submit --}}
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            type="submit"
                            id="saveDeliveryButton"
                            class="flex-1 rounded-xl bg-orange-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-300 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <span id="saveDeliveryButtonText">Save Delivery</span>
                        </button>

                        <button
                            type="reset"
                            id="resetDelivery"
                            class="rounded-xl border border-slate-300 px-5 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Reset Form
                        </button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Recent deliveries --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Recent Deliveries</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Deliveries recorded in the current innings.
                    </p>
                </div>
            </div>

            @if ($currentInnings && $currentDeliveries->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">#</th>
                                <th class="px-3 py-3">Batter</th>
                                <th class="px-3 py-3">Bowler</th>
                                <th class="px-3 py-3">Runs</th>
                                <th class="px-3 py-3">Extras</th>
                                <th class="px-3 py-3">Wicket</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach ($currentDeliveries->sortByDesc('id')->take(10) as $delivery)
                                <tr>
                                    <td class="px-3 py-3 font-semibold text-slate-700">
                                        {{ $delivery->ball_number ?? $delivery->id }}
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ $delivery->striker ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ $delivery->bowler ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 font-bold text-slate-900">
                                        {{ $delivery->runs ?? $delivery->batter_runs ?? 0 }}
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ $delivery->extra_type ?? 'none' }}
                                        ({{ $delivery->extra_runs ?? 0 }})
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ $delivery->wicket_type ?? 'none' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                    No deliveries have been recorded in this innings yet.
                </p>
            @endif

            {{-- Undo latest delivery --}}
                        {{-- Undo latest delivery --}}
            @if ($currentInnings && $currentDeliveries->isNotEmpty())
                @php
                    $latestDelivery = $currentDeliveries->last();
                @endphp

                <form
                    action="{{ route('admin.live.undo', [
                        'fixture' => $fixture->id,
                        'delivery' => $latestDelivery->id,
                    ]) }}"
                    method="POST"
                    class="mt-5 border-t border-slate-100 pt-5"
                    onsubmit="return confirm('Undo the latest delivery? Make sure this is the delivery you want to remove.');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="rounded-xl border border-red-200 px-4 py-3 text-sm font-bold text-red-700 transition hover:bg-red-50"
                    >
                        Undo Latest Delivery
                    </button>
                </form>
            @endif
        </section>

        {{-- Footer --}}
        <footer class="pb-4 text-center text-xs text-slate-400">
            NPL Season Hub · Live Scoring Administration
        </footer>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('detailedDeliveryForm');

    if (!form) return;

    const runsInput = document.getElementById('runs');
    const selectedRuns = document.getElementById('selectedRuns');
    const runButtons = form.querySelectorAll('.run-button');

    const extraType = document.getElementById('extra_type');
    const extraRuns = document.getElementById('extra_runs');
    const extrasHelp = document.getElementById('extrasHelp');

    const wicketType = document.getElementById('wicket_type');
    const dismissedWrap = document.getElementById('dismissedWrap');
    const incomingWrap = document.getElementById('incomingWrap');
    const dismissedBatter = document.getElementById('dismissed_batter');
    const incomingBatter = document.getElementById('incoming_batter');

    const summary = document.getElementById('deliverySummary');
    const strikerSelect = document.getElementById('striker');
    const nonStrikerSelect = document.getElementById('non_striker');

    function updateBatterOptions() {
        if (!strikerSelect || !nonStrikerSelect) return;

        const strikerValue = strikerSelect.value;
        const nonStrikerValue = nonStrikerSelect.value;

        Array.from(nonStrikerSelect.options).forEach(function (option) {
            option.disabled = option.value !== '' && option.value === strikerValue;
        });

        Array.from(strikerSelect.options).forEach(function (option) {
            option.disabled = option.value !== '' && option.value === nonStrikerValue;
        });

        if (strikerValue && strikerValue === nonStrikerValue) {
            nonStrikerSelect.value = '';
        }
    }

    function updateRunButtons() {
        runButtons.forEach(function (button) {
            const active = Number(button.dataset.run) === Number(runsInput.value);

            button.classList.toggle('border-orange-500', active);
            button.classList.toggle('bg-orange-50', active);
            button.classList.toggle('text-orange-700', active);
            button.classList.toggle('border-slate-200', !active);
        });

        selectedRuns.textContent = runsInput.value;
    }

    function updateExtras() {
        const type = extraType.value;
        const count = Number(extraRuns.value || 0);

        const descriptions = {
            none: 'No extras selected.',
            wide: 'Wide selected. A wide is an extra and does not count as a legal delivery.',
            no_ball: 'No-ball selected. A no-ball is an extra and does not count as a legal delivery.',
            bye: 'Bye selected. These runs are extras, not batter runs.',
            leg_bye: 'Leg-bye selected. These runs are extras, not batter runs.',
            penalty: 'Penalty selected. Confirm how penalty runs are handled by your scoring controller.'
        };

        extrasHelp.textContent = descriptions[type] || '';

        if (type !== 'none' && count === 0) {
            extrasHelp.textContent += ' Enter the number of extras for this delivery.';
        }

        updateSummary();
    }

    function updateWicketFields() {
        const hasWicket = wicketType.value !== 'none';

        dismissedWrap.classList.toggle('hidden', !hasWicket);
        incomingWrap.classList.toggle('hidden', !hasWicket);

        dismissedBatter.required = hasWicket;

        updateSummary();
    }

    function updateSummary() {
        const runs = Number(runsInput.value || 0);
        const extras = extraType.value;
        const extraCount = Number(extraRuns.value || 0);
        const wicket = wicketType.value;

        const extrasLabel = extras === 'none'
            ? 'No extras'
            : extras.replaceAll('_', ' ') + ' (' + extraCount + ')';

        const wicketLabel = wicket === 'none'
            ? 'No wicket'
            : wicket.replaceAll('_', ' ');

        summary.textContent =
            runs + ' batter runs · ' + extrasLabel + ' · ' + wicketLabel;
    }

    runButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            runsInput.value = button.dataset.run;
            updateRunButtons();
            updateSummary();
        });
    });

    extraType.addEventListener('change', updateExtras);
    extraRuns.addEventListener('input', updateExtras);
    wicketType.addEventListener('change', updateWicketFields);
    strikerSelect.addEventListener('change', updateBatterOptions);
    nonStrikerSelect.addEventListener('change', updateBatterOptions);

    form.addEventListener('submit', function (event) {
        const striker = strikerSelect.value;
        const nonStriker = nonStrikerSelect.value;

        if (striker && nonStriker && striker === nonStriker) {
            event.preventDefault();
            alert('Striker and non-striker must be different players.');
            return;
        }

        if (wicketType.value !== 'none' && !dismissedBatter.value) {
            event.preventDefault();
            alert('Please select the dismissed batter.');
            return;
        }

        if (wicketType.value !== 'none' &&
            dismissedBatter.value &&
            incomingBatter.value &&
            dismissedBatter.value === incomingBatter.value) {
            event.preventDefault();
            alert('The incoming batter must be different from the dismissed batter.');
            return;
        }

        // Let Laravel receive the POST once, then prevent accidental double submissions.
        const saveButton = document.getElementById('saveDeliveryButton');
        const saveButtonText = document.getElementById('saveDeliveryButtonText');

        if (saveButton && !saveButton.disabled) {
            saveButton.disabled = true;
            if (saveButtonText) {
                saveButtonText.textContent = 'Saving Delivery...';
            }
        }
    });

    form.addEventListener('reset', function () {
        setTimeout(function () {
            runsInput.value = '0';
            extraType.value = 'none';
            extraRuns.value = '0';
            wicketType.value = 'none';

            updateBatterOptions();
            updateRunButtons();
            updateExtras();
            updateWicketFields();

            const saveButton = document.getElementById('saveDeliveryButton');
            const saveButtonText = document.getElementById('saveDeliveryButtonText');
            if (saveButton) saveButton.disabled = false;
            if (saveButtonText) saveButtonText.textContent = 'Save Delivery';
        }, 0);
    });

    updateBatterOptions();
    updateRunButtons();
    updateExtras();
    updateWicketFields();
});
</script>
@endsection
