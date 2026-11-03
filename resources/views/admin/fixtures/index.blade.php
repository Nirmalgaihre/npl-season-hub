{{-- resources/views/admin/fixtures/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Fixtures & Results | NPL Admin')
@section('page_heading', 'Fixtures & Results')

@section('content')
<div class="min-h-screen bg-slate-50/80">
    <div class="mx-auto max-w-[1600px] px-4 py-8 sm:px-6 lg:px-8">

        {{-- ───────────────────────────────────────────────
             HEADER
        ─────────────────────────────────────────────── --}}
        <div class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-4 inline-flex items-center gap-2.5 rounded-full border border-amber-200/70 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-800 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-600"></span>
                    </span>
                    NPL Operations Hub
                </div>

                <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Fixtures &amp; Results
                </h1>
                <p class="mt-2 max-w-2xl text-[15px] leading-relaxed text-slate-500">
                    Manage tournament schedules, live scores, and official result verification.
                </p>
            </div>

            <a href="{{ route('admin.fixtures.create') }}"
               class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-slate-900 px-6 py-3.5 text-sm font-bold text-white shadow-md transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300/40">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Create Fixture
            </a>
        </div>

        {{-- ───────────────────────────────────────────────
             FLASH MESSAGES
        ─────────────────────────────────────────────── --}}
        @if(session('success'))
            <div role="status" class="mb-6 flex items-start gap-3.5 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 px-5 py-4 text-emerald-900 shadow-sm">
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-4.5 w-4.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.72a.75.75 0 00-1.06-1.06L9 10.94 7.78 9.72a.75.75 0 00-1.06 1.06l1.75 1.75a.75.75 0 001.06 0l4.25-4.25z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold">Success</p>
                    <p class="mt-0.5 text-sm text-emerald-800/90">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.closest('[role=status]').remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div role="alert" class="mb-6 flex items-start gap-3.5 rounded-2xl border border-rose-200/80 bg-rose-50/90 px-5 py-4 text-rose-900 shadow-sm">
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-100">
                    <svg class="h-4.5 w-4.5 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold">Action Failed</p>
                    <p class="mt-0.5 text-sm text-rose-800/90">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-rose-500 hover:text-rose-700 text-lg leading-none">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div role="alert" class="mb-6 rounded-2xl border border-rose-200/80 bg-rose-50/90 px-5 py-4 text-rose-900 shadow-sm">
                <p class="font-bold">Please resolve the following errors:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-rose-800/90">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────
             STAT CARDS
        ─────────────────────────────────────────────── --}}
        <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            {{-- All Matches --}}
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_4px_20px_-4px_rgba(15,23,42,0.06)] transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">All Matches</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black tabular-nums text-slate-900">{{ number_format($stats['total'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-400">Total fixtures listed</p>
            </div>

            {{-- Scheduled --}}
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_4px_20px_-4px_rgba(15,23,42,0.06)] transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Scheduled</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black tabular-nums text-slate-900">{{ number_format($stats['scheduled'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-400">Upcoming games</p>
            </div>

            {{-- Live Now --}}
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_4px_20px_-4px_rgba(15,23,42,0.06)] transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Live Now</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                        <svg class="h-5 w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.652a3.75 3.75 0 010-5.304m5.304 0a3.75 3.75 0 010 5.304m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.981 0 13.79M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black tabular-nums text-rose-600">{{ number_format($stats['live'] ?? 0) }}</p>
                <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-500 opacity-75"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                    </span>
                    Happening right now
                </p>
            </div>

            {{-- Completed --}}
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_4px_20px_-4px_rgba(15,23,42,0.06)] transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Completed</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black tabular-nums text-slate-900">{{ number_format($stats['completed'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-400">Finished matches</p>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────
             SEARCH & FILTERS
        ─────────────────────────────────────────────── --}}
        <section class="mb-8 overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.07)]">
            <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white px-6 py-4">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-900">Search &amp; Filter</h2>
                    <p class="text-xs text-slate-500">Find fixtures by team, venue, season or status</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    Advanced
                </span>
            </div>

            <form method="GET" action="{{ route('admin.fixtures.index') }}" class="p-6 space-y-5">
                <div class="grid gap-4 md:grid-cols-12">
                    <div class="relative md:col-span-6">
                        <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/>
                        </svg>
                        <input type="search" name="search" value="{{ request('search') }}"
                               placeholder="Search team name, short code or venue..."
                               class="sc-control pl-10">
                    </div>

                    <div class="md:col-span-3">
                        <select name="season_id" class="sc-control">
                            <option value="">All Seasons</option>
                            @foreach(($seasons ?? collect()) as $season)
                                <option value="{{ $season->id }}" @selected((string) request('season_id') === (string) $season->id)>
                                    {{ $season->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <select name="status" class="sc-control">
                            <option value="">All Statuses</option>
                            @foreach(['scheduled', 'live', 'completed', 'abandoned', 'postponed'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300/40">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/>
                            </svg>
                            Search
                        </button>
                        <a href="{{ route('admin.fixtures.index') }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                            Reset
                        </a>
                    </div>
                    <span class="text-xs font-medium text-slate-500">
                        Found <strong class="text-slate-900">{{ number_format($fixtures->total()) }}</strong> matches
                    </span>
                </div>
            </form>

            {{-- Quick Status Pills --}}
            @php
                $quickStatuses = [
                    '' => 'All Matches',
                    'scheduled' => 'Scheduled',
                    'live' => 'Live Now',
                    'completed' => 'Completed'
                ];
            @endphp
            <div class="flex gap-2 overflow-x-auto border-t border-slate-100 bg-slate-50/50 px-6 py-3.5">
                @foreach($quickStatuses as $value => $label)
                    @php
                        $quickParams = array_filter([
                            'search' => request('search'),
                            'season_id' => request('season_id'),
                            'status' => $value
                        ], fn ($item) => $item !== null && $item !== '');
                    @endphp
                    <a href="{{ route('admin.fixtures.index', $quickParams) }}"
                       class="shrink-0 rounded-xl px-3.5 py-1.5 text-xs font-bold transition
                              {{ (string) request('status', '') === (string) $value
                                  ? 'bg-slate-900 text-white shadow-sm'
                                  : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                        {{ $label }}
                        @if($value === 'live')
                            <span class="ml-1.5 inline-block h-1.5 w-1.5 rounded-full
                                  {{ (string) request('status', '') === 'live' ? 'bg-white' : 'bg-rose-500' }}"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ───────────────────────────────────────────────
             FIXTURES LIST
        ─────────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.07)]">
            <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white px-6 py-4">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-900">Match Fixtures &amp; Scoreboard</h2>
                    <p class="text-xs text-slate-500">
                        Showing <span class="font-semibold text-slate-700">{{ $fixtures->firstItem() ?? 0 }}–{{ $fixtures->lastItem() ?? 0 }}</span>
                        of <span class="font-semibold text-slate-700">{{ $fixtures->total() }}</span>
                    </p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($fixtures as $fixture)
                    @php
                        $statusStyles = [
                            'scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'live'      => 'bg-rose-50 text-rose-700 border-rose-200',
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'abandoned' => 'bg-amber-50 text-amber-800 border-amber-200',
                            'postponed' => 'bg-amber-50 text-amber-800 border-amber-200',
                        ];
                        $statusClass = $statusStyles[$fixture->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';

                        $fixtureScorecards = collect($manualScorecards ?? [])->get($fixture->id, collect());
                        $homeManual = $fixtureScorecards->first(fn ($s) => (int) $s->batting_team_id === (int) $fixture->home_team_id);
                        $awayManual = $fixtureScorecards->first(fn ($s) => (int) $s->batting_team_id === (int) $fixture->away_team_id);

                        $homeRuns    = $homeManual?->total_runs ?? $fixture->home_runs;
                        $homeWickets = $homeManual?->wickets    ?? $fixture->home_wickets;
                        $homeOvers   = $homeManual?->overs      ?? $fixture->home_overs;

                        $awayRuns    = $awayManual?->total_runs ?? $fixture->away_runs;
                        $awayWickets = $awayManual?->wickets    ?? $fixture->away_wickets;
                        $awayOvers   = $awayManual?->overs      ?? $fixture->away_overs;

                        $hasBothTeamScores = $homeRuns !== null && $awayRuns !== null;
                        $canVerifyResult   = $fixture->status === 'completed' && !$fixture->is_result_verified && $hasBothTeamScores;

                        $homeTeam = $fixture->homeTeam;
                        $awayTeam = $fixture->awayTeam;

                        $homeName = $homeTeam?->short_name ?: $homeTeam?->name ?: 'Home Team';
                        $awayName = $awayTeam?->short_name ?: $awayTeam?->name ?: 'Away Team';

                        $homeLogo = $homeTeam?->logo ? asset('storage/teams/' . $homeTeam->logo) : null;
                        $awayLogo = $awayTeam?->logo ? asset('storage/teams/' . $awayTeam->logo) : null;
                    @endphp

                    <article class="p-5 sm:p-6 transition-colors hover:bg-slate-50/60">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                            {{-- Match Info --}}
                            <div class="min-w-0 flex-1 space-y-4">
                                {{-- Badges --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-lg bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-white">
                                        Match {{ $fixture->match_number ?? '—' }}
                                    </span>

                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                        @if($fixture->status === 'live')
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-500 opacity-75"></span>
                                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                                            </span>
                                        @endif
                                        {{ $fixture->status ?? 'Unknown' }}
                                    </span>

                                    @if($fixture->is_result_verified)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.72a.75.75 0 00-1.06-1.06L9 10.94 7.78 9.72a.75.75 0 00-1.06 1.06l1.75 1.75a.75.75 0 001.06 0l4.25-4.25z" clip-rule="evenodd"/>
                                            </svg>
                                            Verified
                                        </span>
                                    @elseif($fixture->status === 'completed')
                                        <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                            </svg>
                                            Needs Verification
                                        </span>
                                    @endif
                                </div>

                                {{-- Scoreboard --}}
                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-4 sm:max-w-2xl">
                                    {{-- Home --}}
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                                            @if($homeLogo)
                                                <img src="{{ $homeLogo }}" alt="{{ $homeName }}" class="h-full w-full object-contain">
                                            @else
                                                <span class="text-xs font-black text-slate-500">{{ strtoupper(substr($homeName, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-slate-900 sm:text-base">{{ $homeName }}</p>
                                            <p class="mt-0.5 text-xs font-bold text-slate-700">
                                                @if($homeRuns !== null)
                                                    {{ $homeRuns }}/{{ $homeWickets ?? 0 }}
                                                    <span class="font-normal text-slate-400">({{ $homeOvers ?? 0 }} ov)</span>
                                                @else
                                                    <span class="italic font-normal text-slate-400">Yet to bat</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    {{-- VS --}}
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-[10px] font-black uppercase text-slate-400 shadow-sm">
                                        VS
                                    </div>

                                    {{-- Away --}}
                                    <div class="flex min-w-0 items-center justify-end gap-3 text-right">
                                        <div class="min-w-0 order-2 sm:order-1">
                                            <p class="truncate text-sm font-black text-slate-900 sm:text-base">{{ $awayName }}</p>
                                            <p class="mt-0.5 text-xs font-bold text-slate-700">
                                                @if($awayRuns !== null)
                                                    {{ $awayRuns }}/{{ $awayWickets ?? 0 }}
                                                    <span class="font-normal text-slate-400">({{ $awayOvers ?? 0 }} ov)</span>
                                                @else
                                                    <span class="italic font-normal text-slate-400">Yet to bat</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-sm order-1 sm:order-2">
                                            @if($awayLogo)
                                                <img src="{{ $awayLogo }}" alt="{{ $awayName }}" class="h-full w-full object-contain">
                                            @else
                                                <span class="text-xs font-black text-slate-500">{{ strtoupper(substr($awayName, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Meta --}}
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                        </svg>
                                        {{ $fixture->scheduled_at ? $fixture->scheduled_at->format('d M Y, h:i A') : 'Date not set' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                        </svg>
                                        {{ $fixture->venue ?: 'Venue TBD' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-4.5A3.375 3.375 0 0012.75 9.75h-1.5A3.375 3.375 0 007.5 13.125V18.75"/>
                                        </svg>
                                        {{ $fixture->season?->name ?? 'NPL Season' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 flex-wrap items-center gap-2 border-t border-slate-100 pt-4 lg:border-t-0 lg:pt-0">
                                <a href="{{ route('fixtures.show', $fixture) }}"
                                   class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50"
                                   title="View Public Match Page">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </a>

                                <a href="{{ route('admin.fixtures.edit', $fixture) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                    </svg>
                                    Edit Match
                                </a>

                                @if($canVerifyResult)
                                    <form method="POST" action="{{ route('admin.fixtures.verify', $fixture) }}"
                                          onsubmit="return confirm('Verify this completed match result?')">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700"
                                                title="Verify Result">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.72a.75.75 0 00-1.06-1.06L9 10.94 7.78 9.72a.75.75 0 00-1.06 1.06l1.75 1.75a.75.75 0 001.06 0l4.25-4.25z" clip-rule="evenodd"/>
                                            </svg>
                                            Verify
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.fixtures.destroy', $fixture) }}"
                                      onsubmit="return confirm('Permanently delete this fixture?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100"
                                            title="Delete Fixture">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-20 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-bold text-slate-900">No matches found</h3>
                        <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">
                            Try adjusting your search or clear the filters to see all fixtures.
                        </p>
                        <div class="mt-6 flex justify-center gap-3">
                            <a href="{{ route('admin.fixtures.index') }}"
                               class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                Clear Filters
                            </a>
                            <a href="{{ route('admin.fixtures.create') }}"
                               class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-slate-800">
                                Create Fixture
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($fixtures->hasPages() || $fixtures->total() > 0)
                <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">
                        Showing
                        <span class="font-semibold text-slate-800">{{ $fixtures->firstItem() ?? 0 }}</span>
                        to
                        <span class="font-semibold text-slate-800">{{ $fixtures->lastItem() ?? 0 }}</span>
                        of
                        <span class="font-semibold text-slate-800">{{ $fixtures->total() }}</span>
                        matches
                    </p>
                    <div class="text-sm">
                        {{ $fixtures->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </section>

    </div>
</div>

@push('styles')
<style>
    .sc-control {
        display: block;
        width: 100%;
        min-height: 44px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background-color: #fff;
        padding: 10px 14px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .sc-control:hover {
        border-color: #cbd5e1;
    }

    .sc-control:focus {
        border-color: #0f172a;
        box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
    }

    .sc-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
</style>
@endpush
@endsection