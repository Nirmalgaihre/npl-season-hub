@extends('layouts.app')

@section('title', 'Point Table | Nepal Premier League')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    .pt-display { font-family: 'Anton', Impact, sans-serif; letter-spacing: -0.01em; }
    .pt-body    { font-family: 'Archivo', system-ui, sans-serif; }
    .pt-tabular { font-variant-numeric: tabular-nums; }

    .pt-hero {
        background:
            radial-gradient(circle at 90% 10%, rgba(240,100,69,0.20) 0%, transparent 50%),
            linear-gradient(150deg, #0B1B3A 0%, #050E22 100%);
    }
    .pt-row { transition: background-color .15s ease; }
    .pt-row:hover { background-color: rgba(240,100,69,0.04); }
    .pt-row.is-top-4 { background-color: rgba(240,100,69,0.03); }
    .pt-row.is-top-4:hover { background-color: rgba(240,100,69,0.07); }
    .pt-row::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
        background: var(--edge-color, transparent);
    }
</style>

<div class="pt-body min-h-screen bg-[#F5F1EB] text-[#0B1B3A]">

    {{-- HERO --}}
    <section class="pt-hero relative overflow-hidden text-white">
        <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:px-10">

            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-white/55">
                <a href="{{ route('home') }}" class="hover:text-[#F06445]">Home</a>
                <span class="text-white/25">/</span>
                <span class="text-white/90">Point Table</span>
            </nav>

            <div class="mt-8 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.3em] text-[#F06445]">
                        Nepal Premier League
                    </p>
                    <h1 class="pt-display mt-3 text-5xl leading-none text-white sm:text-6xl lg:text-7xl">
                        STANDINGS
                    </h1>
                    @if ($selectedSeason)
                        <p class="mt-3 text-sm font-semibold text-white/60">
                            {{ $selectedSeason->name ?? 'Season' }}
                            · {{ $completedMatches }} {{ \Illuminate\Support\Str::plural('match', $completedMatches) }} played
                        </p>
                    @endif
                </div>

                {{-- Season switcher --}}
                @if ($seasons->count() > 1)
                    <form method="GET" action="{{ route('pointtable.index') }}" class="shrink-0">
                        <select name="season"
                                onchange="this.form.submit()"
                                class="w-full min-w-[200px] rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur focus:border-[#F06445] focus:outline-none">
                            @foreach ($seasons as $s)
                                <option value="{{ $s->id }}" @selected($s->id === $selectedSeason?->id) class="text-[#0B1B3A]">
                                    {{ $s->name ?? 'Season ' . $s->id }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>

        <svg class="absolute bottom-0 left-0 right-0 z-[2] h-6 w-full" viewBox="0 0 1200 24" preserveAspectRatio="none">
            <polygon points="0,24 1200,24 1200,0" fill="#F06445" opacity="0.9"/>
        </svg>
    </section>

    {{-- TABLE --}}
    <section class="mx-auto -mt-3 max-w-7xl px-5 py-10 sm:px-8 lg:px-10">

        @if ($standings->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <p class="pt-display text-xl text-slate-400">NO STANDINGS YET</p>
                <p class="mt-2 text-sm text-slate-500">Standings will appear once fixtures have been played and verified.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl bg-white shadow-[0_24px_60px_-24px_rgba(11,27,58,0.25)] ring-1 ring-black/5">

                {{-- Table header --}}
                <div class="hidden grid-cols-[60px_1fr_repeat(6,60px)] items-center border-b-2 border-[#0B1B3A]/10 bg-[#FAF6F0] px-5 py-3 text-[10px] font-extrabold uppercase tracking-[0.2em] text-slate-500 lg:grid">
                    <span>#</span>
                    <span>Team</span>
                    <span class="text-center">P</span>
                    <span class="text-center">W</span>
                    <span class="text-center">L</span>
                    <span class="text-center">T</span>
                    <span class="text-center">NR</span>
                    <span class="text-center">NRR</span>
                </div>

                {{-- Rows --}}
                <div>
                    @foreach ($standings as $index => $row)
                        @php
                            $rank = $index + 1;
                            $isQualifying = $rank <= 4;
                            $nrr = $row['nrr'];
                            $nrrStr = $nrr === null ? '—' : sprintf('%+.3f', $nrr);
                            $nrrClass = $nrr === null ? 'text-slate-400'
                                : ($nrr > 0 ? 'text-emerald-700' : ($nrr < 0 ? 'text-rose-600' : 'text-slate-500'));
                            $edge = $isQualifying ? '#F06445' : 'transparent';
                            $team = $row['team'];
                        @endphp

                        <div class="pt-row {{ $isQualifying ? 'is-top-4' : '' }} relative grid grid-cols-[44px_1fr_repeat(2,44px)] items-center gap-2 border-b border-black/[0.05] px-3 py-4 last:border-0 lg:grid-cols-[60px_1fr_repeat(6,60px)] lg:gap-0 lg:px-5"
                             style="--edge-color: {{ $edge }}">

                            {{-- Rank --}}
                            <div class="flex items-center">
                                <span class="pt-tabular pt-display inline-flex h-8 w-8 items-center justify-center rounded-md text-base
                                             {{ $isQualifying ? 'bg-[#F06445] text-white' : 'bg-[#0B1B3A]/5 text-[#0B1B3A]' }}">
                                    {{ $rank }}
                                </span>
                            </div>

                            {{-- Team --}}
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-50 p-1.5 ring-1 ring-black/5">
                                    @if ($team->logo)
                                        <img src="{{ asset('storage/' . $team->logo) }}"
                                             alt="{{ $team->name }}"
                                             class="h-full w-full object-contain">
                                    @else
                                        <span class="text-[10px] font-black text-[#0B1B3A]">
                                            {{ strtoupper(substr($team->short_name ?? $team->name, 0, 3)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('teams.show', $team) }}"
                                       class="truncate text-sm font-extrabold text-[#0B1B3A] hover:text-[#F06445]">
                                        {{ $team->name }}
                                    </a>
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 lg:hidden">
                                        {{ $row['points'] }} PTS · NRR {{ $nrrStr }}
                                    </p>
                                </div>
                            </div>

                            {{-- Compact mobile stats --}}
                            <div class="pt-tabular pt-display text-center text-base text-[#0B1B3A] lg:hidden">
                                {{ $row['points'] }}
                            </div>
                            <div class="text-center text-[10px] font-bold uppercase tracking-wide text-slate-400 lg:hidden">
                                PTS
                            </div>

                            {{-- Desktop stats --}}
                            <span class="pt-tabular hidden text-center text-sm font-bold text-slate-700 lg:block">{{ $row['played'] }}</span>
                            <span class="pt-tabular hidden text-center text-sm font-bold text-emerald-700 lg:block">{{ $row['won'] }}</span>
                            <span class="pt-tabular hidden text-center text-sm font-bold text-rose-600 lg:block">{{ $row['lost'] }}</span>
                            <span class="pt-tabular hidden text-center text-sm font-bold text-slate-600 lg:block">{{ $row['tied'] }}</span>
                            <span class="pt-tabular hidden text-center text-sm font-bold text-slate-500 lg:block">{{ $row['no_result'] }}</span>
                            <span class="pt-tabular hidden text-center text-sm font-bold {{ $nrrClass }} lg:block">{{ $nrrStr }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-6 flex flex-wrap items-center gap-6 text-xs font-semibold text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-1 rounded-full bg-[#F06445]"></span>
                    Playoff qualification
                </div>
                <div>P = Played · W = Won · L = Lost · T = Tied · NR = No Result · NRR = Net Run Rate</div>
            </div>
        @endif
    </section>
</div>
@endsection