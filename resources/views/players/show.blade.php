@extends('layouts.app')

@section('title', $player->name . ' | ' . ($team->name ?? 'NPL') . ' | Nepal Premier League')

@section('meta_description', $player->name . ' — batting, bowling and recent form for ' . ($team->name ?? 'NPL') . ' in the Nepal Premier League.')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    .npl-display { font-family: 'Anton', Impact, 'Arial Narrow', sans-serif; letter-spacing: -0.01em; }
    .npl-body    { font-family: 'Archivo', system-ui, -apple-system, sans-serif; }
    .npl-tabular { font-variant-numeric: tabular-nums; }

    .npl-hero-band {
        position: relative;
        background:
            radial-gradient(circle at 85% 15%, rgba(240,100,69,0.22) 0%, transparent 45%),
            radial-gradient(circle at 10% 90%, rgba(232,184,75,0.10) 0%, transparent 40%),
            linear-gradient(160deg, #0B1B3A 0%, #091533 40%, #050E22 100%);
    }
    .npl-hero-band::after {
        content: ''; position: absolute; inset: 0; pointer-events: none; z-index: 1;
        background: repeating-linear-gradient(115deg, transparent 0, transparent 60px, rgba(255,255,255,0.025) 61px, transparent 62px);
    }
    .npl-ghost-number {
        font-family: 'Anton', sans-serif;
        color: rgba(255,255,255,0.05);
        font-size: 24rem;
        line-height: 0.7;
        letter-spacing: -0.04em;
    }
    .npl-stat-cell { position: relative; }
    .npl-stat-cell::before {
        content: ''; position: absolute; left: 0; top: 15%; bottom: 15%; width: 2px;
        background: linear-gradient(to bottom, transparent, #F06445, transparent);
    }
    .npl-stat-cell:first-child::before { display: none; }

    .npl-form-pill { transition: transform .2s ease, box-shadow .2s ease; }
    .npl-form-pill:hover { transform: translateY(-2px); box-shadow: 0 10px 24px -8px rgba(11,27,58,.35); }

    .npl-log-row { position: relative; transition: background-color .15s ease; }
    .npl-log-row:hover { background-color: rgba(240,100,69,0.04); }
    .npl-log-row::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
        background: var(--edge-color, transparent);
    }

    .npl-jersey-ribbon {
        background: linear-gradient(90deg, #F06445 0%, #FF815D 100%);
    }

    /* Performance section */
    .npl-perf-row { transition: background-color .15s ease; }
    .npl-perf-row:hover { background-color: #FAF6F0; }
</style>

<div class="npl-body min-h-screen bg-[#F5F1EB] text-[#0B1B3A]">

    {{-- ═══════════════════════════════════════════════════════════════════
         HERO
    ═════════════════════════════════════════════════════════════════════ --}}
    <section class="npl-hero-band relative overflow-hidden text-white">

        <div class="relative z-20 border-b border-white/10">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8 lg:px-10">

                <nav class="hidden items-center gap-2 text-xs font-semibold text-white/55 sm:flex">
                    <a href="{{ route('teams.index') }}" class="transition hover:text-[#F06445]">Teams</a>
                    <span class="text-white/25">/</span>
                    @if ($team)
                        <a href="{{ route('teams.show', $team) }}" class="transition hover:text-[#F06445]">{{ $team->name }}</a>
                        <span class="text-white/25">/</span>
                    @endif
                    <span class="text-white/90">{{ $player->name }}</span>
                </nav>

                <a href="{{ $team ? route('teams.show', $team) . '#squad' : route('teams.index') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white/80 transition hover:border-[#F06445] hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3.5 w-3.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Squad
                </a>
            </div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-5 pb-16 pt-10 sm:px-8 sm:pt-14 lg:px-10 lg:pt-16">

            @if (!empty($player->jersey_number))
                <div class="pointer-events-none absolute right-4 top-1/2 hidden -translate-y-1/2 select-none lg:block">
                    <span class="npl-ghost-number">{{ $player->jersey_number }}</span>
                </div>
            @endif

            <div class="relative grid gap-10 lg:grid-cols-[1fr_minmax(0,420px)] lg:items-end">

                <div class="min-w-0">
                    @if ($team)
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5">
                            @if (!empty($team->logo))
                                <img src="{{ asset('storage/' . $team->logo) }}" alt="" class="h-4 w-4 rounded-sm object-contain">
                            @endif
                            <span class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-white/85">
                                {{ $team->name }}
                            </span>
                        </div>
                    @endif

                    @php
                        $nameParts = preg_split('/\s+/', trim($player->name));
                        $lastName  = count($nameParts) > 1 ? array_pop($nameParts) : '';
                        $firstName = implode(' ', $nameParts);
                    @endphp

                    <h1 class="npl-display mt-5 text-white">
                        @if ($firstName)
                            <span class="block text-3xl leading-none text-white/85 sm:text-4xl lg:text-5xl">{{ strtoupper($firstName) }}</span>
                        @endif
                        <span class="mt-1 block text-6xl leading-[0.85] sm:text-7xl lg:text-[7.5rem]">{{ strtoupper($lastName ?: $firstName) }}</span>
                    </h1>

                    <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3">
                        @if (!empty($player->role))
                            <div>
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.24em] text-white/40">Role</p>
                                <p class="mt-1 text-sm font-bold text-white">{{ $player->role }}</p>
                            </div>
                        @endif
                        @if (!empty($player->nationality))
                            <div>
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.24em] text-white/40">Country</p>
                                <p class="mt-1 text-sm font-bold text-white">{{ $player->nationality }}</p>
                            </div>
                        @endif
                        @if (!empty($team?->short_name))
                            <div>
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.24em] text-white/40">Franchise</p>
                                <p class="mt-1 text-sm font-bold text-white">{{ $team->short_name }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="relative lg:justify-self-end">
                    <div class="relative h-64 w-64 sm:h-80 sm:w-80">
                        <div class="npl-jersey-ribbon absolute -bottom-2 -right-2 h-[70%] w-[70%] rounded-tl-3xl opacity-90"></div>

                        <div class="relative flex h-full w-full items-end justify-center overflow-hidden rounded-3xl bg-gradient-to-b from-[#203D88] via-[#14285F] to-[#091538] ring-1 ring-white/20">
                            @if (!empty($player->photo_path))
                                <img src="{{ asset('storage/' . $player->photo_path) }}"
                                     alt="{{ $player->name }}"
                                     class="h-[105%] w-auto max-w-none translate-y-2 object-contain object-bottom drop-shadow-2xl">
                            @else
                                <span class="npl-display mb-12 text-7xl text-white/90">
                                    {{ strtoupper(substr($player->name, 0, 2)) }}
                                </span>
                            @endif

                            @if (!empty($player->jersey_number))
                                <div class="absolute left-3 top-3 flex h-12 w-12 items-center justify-center rounded-xl bg-[#0B1B3A] npl-display text-xl text-white ring-2 ring-white/20">
                                    {{ $player->jersey_number }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <svg class="absolute bottom-0 left-0 right-0 z-[2] h-8 w-full" viewBox="0 0 1200 30" preserveAspectRatio="none" aria-hidden="true">
            <polygon points="0,30 1200,30 1200,0" fill="#F06445" opacity="0.9"/>
        </svg>
    </section>

    {{-- HERO STATS --}}
    <section class="relative z-20 mx-auto -mt-4 max-w-7xl px-5 sm:px-8 lg:px-10">
        <div class="overflow-hidden rounded-2xl bg-white shadow-[0_24px_60px_-24px_rgba(11,27,58,0.35)] ring-1 ring-black/5">
            <div class="grid grid-cols-2 divide-slate-100 sm:grid-cols-5 sm:divide-x">
                @php
                    $heroStats = [
                        ['label' => 'Matches',    'value' => max($totalInnings, $totalBowlInnings), 'accent' => false],
                        ['label' => 'Runs',       'value' => $totalRuns,        'accent' => false],
                        ['label' => 'Bat avg',    'value' => $battingAvg ?? '—','accent' => false],
                        ['label' => 'Strike rate','value' => $strikeRate ?? '—','accent' => true],
                        ['label' => 'Wickets',    'value' => $totalWickets,     'accent' => false],
                    ];
                @endphp
                @foreach ($heroStats as $stat)
                    <div class="npl-stat-cell px-5 py-6 text-center sm:text-left">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-slate-400">{{ $stat['label'] }}</p>
                        <p class="npl-tabular npl-display mt-2 text-4xl leading-none sm:text-5xl {{ $stat['accent'] ? 'text-[#F06445]' : 'text-[#0B1B3A]' }}">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- RECENT FORM --}}
    @if ($battingRows->isNotEmpty())
        <section class="mx-auto mt-14 max-w-7xl px-5 sm:px-8 lg:px-10">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-1 bg-[#F06445]"></div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.28em] text-[#F06445]">Recent form</p>
                        <h2 class="npl-display mt-1 text-3xl leading-none text-[#0B1B3A] sm:text-4xl">LAST 5 INNINGS</h2>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#E8B84B]"></span>50+</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-600"></span>25+</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#0B1B3A]"></span>10+</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-rose-600"></span>&lt;10</span>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                @foreach ($battingRows->take(5) as $row)
                    @php
                        $runs = (int) ($row->runs ?? 0);
                        $isNotOut = empty($row->is_out);
                        $balls = (int) ($row->balls ?? 0);
                        $tone = $runs >= 50 ? ['bg-[#E8B84B]', 'text-[#0B1B3A]', 'ring-[#E8B84B]/30']
                              : ($runs >= 25 ? ['bg-emerald-600', 'text-white', 'ring-emerald-600/25']
                              : ($runs >= 10 ? ['bg-[#0B1B3A]', 'text-white', 'ring-[#0B1B3A]/20']
                              : ['bg-rose-600', 'text-white', 'ring-rose-600/25']));
                    @endphp
                    <div class="npl-form-pill flex w-[calc(20%-0.75rem)] min-w-[110px] flex-col rounded-2xl ring-4 p-4 {{ $tone[2] }} {{ $tone[0] }} {{ $tone[1] }}">
                        <div class="flex items-baseline justify-between">
                            <span class="text-[9px] font-extrabold uppercase tracking-[0.2em] opacity-70">Inns</span>
                            @if ($isNotOut)
                                <span class="text-[9px] font-extrabold uppercase tracking-wide opacity-90">Not out</span>
                            @endif
                        </div>
                        <p class="npl-tabular npl-display mt-1 text-4xl leading-none">
                            {{ $runs }}@if ($isNotOut)<span class="align-top text-lg">*</span>@endif
                        </p>
                        <p class="mt-2 truncate text-[10px] font-semibold uppercase tracking-wide opacity-75">{{ $balls }} balls</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- BATTING + BOWLING --}}
    <div class="mx-auto mt-14 max-w-7xl px-5 sm:px-8 lg:px-10">
        <div class="grid gap-10 lg:grid-cols-2">

            {{-- BATTING --}}
            <section>
                <div class="flex items-end justify-between border-b border-slate-200 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-1 bg-[#F06445]"></div>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.24em] text-slate-400">Career</p>
                            <h2 class="npl-display text-3xl leading-none text-[#0B1B3A]">BATTING</h2>
                        </div>
                    </div>
                    <p class="npl-tabular npl-display text-4xl text-[#F06445]">{{ $totalRuns }}</p>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                    @foreach ([
                        ['Innings', $totalInnings],
                        ['Balls', $totalBalls],
                        ['Highest', $highestScore],
                        ['Fours', $totalFours],
                        ['Sixes', $totalSixes],
                        ['50s', $battingRows->filter(fn($r) => (int)($r->runs ?? 0) >= 50)->count()],
                    ] as [$label, $value])
                        <div class="rounded-lg bg-white p-3 ring-1 ring-black/[0.05]">
                            <p class="text-[9px] font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ $label }}</p>
                            <p class="npl-tabular mt-1 text-lg font-black text-[#0B1B3A]">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                @if ($battingRows->isNotEmpty())
                    <ul class="mt-6 overflow-hidden rounded-xl bg-white ring-1 ring-black/[0.05]">
                        @foreach ($battingRows->take(8) as $row)
                            @php
                                $runs = (int) ($row->runs ?? 0);
                                $balls = (int) ($row->balls ?? 0);
                                $sr = $balls > 0 ? round(($runs / $balls) * 100, 1) : null;
                                $isNotOut = empty($row->is_out);
                                $edgeColor = $runs >= 50 ? '#E8B84B' : ($runs >= 25 ? '#10b981' : ($runs >= 10 ? '#0B1B3A' : '#e11d48'));
                            @endphp
                            <li class="npl-log-row border-b border-black/[0.05] last:border-0" style="--edge-color: {{ $edgeColor }}">
                                <div class="flex items-center gap-4 py-3 pl-4 pr-3">
                                    <div class="w-16 shrink-0">
                                        <p class="npl-tabular npl-display text-2xl leading-none text-[#0B1B3A]">
                                            {{ $runs }}@if ($isNotOut)<span class="align-top text-xs text-slate-500">*</span>@endif
                                        </p>
                                        <p class="npl-tabular text-[10px] font-semibold text-slate-400">{{ $balls }}b · SR {{ $sr ?? '—' }}</p>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-[#0B1B3A]">Innings</p>
                                        <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                            {{ (int)($row->fours ?? 0) }}×4 · {{ (int)($row->sixes ?? 0) }}×6
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="mt-6 rounded-xl border-2 border-dashed border-slate-200 bg-white px-5 py-10 text-center">
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-400">No batting records</p>
                    </div>
                @endif
            </section>

            {{-- BOWLING --}}
            <section>
                <div class="flex items-end justify-between border-b border-slate-200 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-1 bg-[#0B1B3A]"></div>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.24em] text-slate-400">Career</p>
                            <h2 class="npl-display text-3xl leading-none text-[#0B1B3A]">BOWLING</h2>
                        </div>
                    </div>
                    <p class="npl-tabular npl-display text-4xl text-[#0B1B3A]">{{ $totalWickets }}</p>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                    @foreach ([
                        ['Innings', $totalBowlInnings],
                        ['Overs', rtrim(rtrim(number_format($totalOvers, 1), '0'), '.') ?: '0'],
                        ['Economy', $economy ?? '—'],
                        ['Runs', $totalRunsConceded],
                        ['Best', $bestFigures ? ($bestFigures->wickets ?? 0) . '/' . ($bestFigures->runs_conceded ?? 0) : '—'],
                        ['Maidens', 0],
                    ] as [$label, $value])
                        <div class="rounded-lg bg-white p-3 ring-1 ring-black/[0.05]">
                            <p class="text-[9px] font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ $label }}</p>
                            <p class="npl-tabular mt-1 text-lg font-black text-[#0B1B3A]">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                @if ($bowlingRows->isNotEmpty())
                    <ul class="mt-6 overflow-hidden rounded-xl bg-white ring-1 ring-black/[0.05]">
                        @foreach ($bowlingRows->take(8) as $row)
                            @php
                                $wkts = (int) ($row->wickets ?? 0);
                                $overs = (float) ($row->overs ?? 0);
                                $runs = (int) ($row->runs_conceded ?? 0);
                                $eco = $overs > 0 ? round($runs / $overs, 2) : null;
                                $edgeColor = $wkts >= 3 ? '#e11d48' : ($wkts >= 2 ? '#F06445' : ($wkts >= 1 ? '#0B1B3A' : '#94a3b8'));
                            @endphp
                            <li class="npl-log-row border-b border-black/[0.05] last:border-0" style="--edge-color: {{ $edgeColor }}">
                                <div class="flex items-center gap-4 py-3 pl-4 pr-3">
                                    <div class="w-16 shrink-0">
                                        <p class="npl-tabular npl-display text-2xl leading-none text-[#0B1B3A]">{{ $wkts }}-{{ $runs }}</p>
                                        <p class="npl-tabular text-[10px] font-semibold text-slate-400">{{ $overs }} ov · {{ $eco ?? '—' }}</p>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-[#0B1B3A]">Bowling spell</p>
                                        <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                            {{ $wkts >= 3 ? 'Match-turning spell' : 'Spell' }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="mt-6 rounded-xl border-2 border-dashed border-slate-200 bg-white px-5 py-10 text-center">
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-400">No bowling records</p>
                    </div>
                @endif
            </section>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         NEW: MATCH-BY-MATCH PERFORMANCE
         Fed by PublicMatchController@playerPerformance
    ═════════════════════════════════════════════════════════════════════ --}}
    @php
        $perfRows   = $performances ?? collect();
        $perfBat    = $battingStats ?? [];
        $perfBowl   = $bowlingStats ?? [];
        $hasPerf    = $perfRows instanceof \Illuminate\Support\Collection
                        ? $perfRows->isNotEmpty()
                        : !empty($perfRows);
    @endphp

    <section class="mx-auto mt-14 max-w-7xl px-5 pb-16 sm:px-8 lg:px-10">

        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-3">
            <div class="flex items-center gap-3">
                <div class="h-8 w-1 bg-[#0B1B3A]"></div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.24em] text-slate-400">Match-by-match</p>
                    <h2 class="npl-display text-3xl leading-none text-[#0B1B3A]">PERFORMANCE</h2>
                </div>
            </div>
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-600">
                {{ $hasPerf ? $perfRows->count() : 0 }} innings on record
            </span>
        </div>

        @if (!$hasPerf)

            <div class="mt-6 rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                <p class="text-sm font-black uppercase tracking-[0.14em] text-slate-400">No match-by-match records yet</p>
                <p class="mt-2 text-xs text-slate-500">Performance data will appear once published match scorecards include this player.</p>
            </div>

        @else

            {{-- Aggregated tiles --}}
            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                <div class="rounded-xl border border-black/[0.05] bg-white p-3.5">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">Bat inns</p>
                    <p class="npl-tabular mt-1.5 text-xl font-black text-[#0B1B3A]">{{ $perfBat['innings'] ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-black/[0.05] bg-white p-3.5">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">Runs</p>
                    <p class="npl-tabular mt-1.5 text-xl font-black text-emerald-700">{{ $perfBat['runs'] ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-black/[0.05] bg-white p-3.5">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">HS</p>
                    <p class="npl-tabular mt-1.5 text-xl font-black text-[#0B1B3A]">{{ $perfBat['highest_score'] ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-black/[0.05] bg-white p-3.5">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">Bat avg</p>
                    <p class="npl-tabular mt-1.5 text-xl font-black text-[#0B1B3A]">{{ $perfBat['average'] ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-black/[0.05] bg-white p-3.5">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">SR</p>
                    <p class="npl-tabular mt-1.5 text-xl font-black text-[#F06445]">{{ $perfBat['strike_rate'] ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-black/[0.05] bg-white p-3.5">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">Wickets</p>
                    <p class="npl-tabular mt-1.5 text-xl font-black text-rose-700">{{ $perfBowl['wickets'] ?? 0 }}</p>
                </div>
            </div>

            {{-- Match log --}}
            <div class="mt-6 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
                <div class="hidden grid-cols-[1fr_80px_80px_80px_90px_80px] items-center border-b border-black/[0.06] bg-[#FAF6F0] px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-[0.16em] text-slate-500 sm:grid">
                    <span>Fixture</span>
                    <span class="text-center">Bat</span>
                    <span class="text-center">Balls</span>
                    <span class="text-center">SR</span>
                    <span class="text-center">Bowl</span>
                    <span class="text-center">Econ</span>
                </div>

                <ul class="divide-y divide-black/[0.05]">
                    @foreach ($perfRows as $row)
                        @php
                            $batter  = $row['batter']  ?? null;
                            $bowler  = $row['bowler']  ?? null;
                            $fixture = $row['fixture'] ?? null;
                            $bowlTeam = $row['bowling_team'] ?? null;

                            $batRuns  = (int) ($batter['runs']  ?? 0);
                            $batBalls = (int) ($batter['balls'] ?? 0);
                            $batSr    = $batBalls > 0 ? round(($batRuns / $batBalls) * 100, 1) : null;
                            $batNotOut = $batter && (
                                !empty($batter['is_not_out'])
                                || str_contains(mb_strtolower((string)($batter['dismissal'] ?? '')), 'not out')
                            );

                            $bowlW = (int) ($bowler['wickets'] ?? 0);
                            $bowlR = (int) ($bowler['runs']    ?? 0);
                            $bowlOversText = (string) ($bowler['overs'] ?? '');
                            $bowlEcon = null;
                            if ($bowler) {
                                if (isset($bowler['economy'])) {
                                    $bowlEcon = (float) $bowler['economy'];
                                } else {
                                    $p = explode('.', $bowlOversText);
                                    $b = (max(0,(int)($p[0] ?? 0)) * 6) + min(5, max(0,(int)($p[1] ?? 0)));
                                    $bowlEcon = $b > 0 ? $bowlR / ($b / 6) : 0;
                                }
                            }

                            $vsName = $bowlTeam?->short_name ?: ($bowlTeam?->name ?: '—');
                            $matchDate = $fixture?->scheduled_at?->format('d M Y');
                            $fixtureUrl = $fixture ? route('fixtures.show', ['fixture' => $fixture->id]) : '#';
                        @endphp

                        <li class="npl-perf-row">
                            <a href="{{ $fixtureUrl }}" class="block px-4 py-3">
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_80px_80px_80px_90px_80px] sm:items-center sm:gap-0">

                                    {{-- Fixture label --}}
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold text-[#0B1B3A]">vs {{ $vsName }}</p>
                                        <p class="mt-0.5 truncate text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                            {{ $matchDate ?? 'Date TBA' }}
                                            @if ($fixture?->match_number) · Match {{ $fixture->match_number }} @endif
                                        </p>
                                    </div>

                                    {{-- Mobile inline --}}
                                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-[11px] font-semibold text-slate-500 sm:hidden">
                                        @if ($batter)
                                            <span><span class="npl-tabular text-[#0B1B3A]">{{ $batRuns }}</span>@if ($batNotOut)<span class="text-emerald-600">*</span>@endif ({{ $batBalls }}b)</span>
                                        @endif
                                        @if ($bowler)
                                            <span><span class="npl-tabular text-rose-700">{{ $bowlW }}-{{ $bowlR }}</span> ({{ $bowlOversText }})</span>
                                        @endif
                                    </div>

                                    {{-- Desktop cells --}}
                                    <span class="npl-tabular hidden text-center text-sm font-black text-[#0B1B3A] sm:block">
                                        @if ($batter){{ $batRuns }}@if ($batNotOut)<span class="text-emerald-600 text-xs align-top">*</span>@endif @else<span class="text-slate-300">—</span>@endif
                                    </span>
                                    <span class="npl-tabular hidden text-center text-sm text-slate-600 sm:block">
                                        {{ $batter ? $batBalls : '—' }}
                                    </span>
                                    <span class="npl-tabular hidden text-center text-sm text-slate-600 sm:block">
                                        {{ $batSr !== null ? $batSr : '—' }}
                                    </span>
                                    <span class="npl-tabular hidden text-center text-sm font-black text-rose-700 sm:block">
                                        @if ($bowler){{ $bowlW }}-{{ $bowlR }} @else<span class="text-slate-300">—</span>@endif
                                    </span>
                                    <span class="npl-tabular hidden text-center text-sm text-slate-600 sm:block">
                                        {{ $bowlEcon !== null ? number_format($bowlEcon, 2) : '—' }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="mt-4 text-[11px] font-medium text-slate-500">
                * = not out. Performance data aggregated from published match scorecards.
            </p>

        @endif
    </section>

    {{-- Footer note --}}
    <div class="mx-auto max-w-7xl px-5 pb-16 sm:px-8 lg:px-10">
        <div class="flex items-start gap-3 border-t border-slate-200 pt-6">
            <div class="h-2 w-2 shrink-0 translate-y-2 rounded-full bg-[#F06445]"></div>
            <p class="text-xs leading-6 text-slate-500">
                Statistics compiled from verified NPL scorecards. Figures update as fixtures are completed and verified by the league.
            </p>
        </div>
    </div>
</div>
@endsection