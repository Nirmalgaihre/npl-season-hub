@extends('layouts.app')

@section('title', 'Live Score | ' . ($fixture->homeTeam->name ?? 'Home') . ' vs ' . ($fixture->awayTeam->name ?? 'Away'))

@section('content')
<meta http-equiv="refresh" content="10">

@php
    /*
    |--------------------------------------------------------------------------
    | Build over-by-over delivery groups
    |--------------------------------------------------------------------------
    | Wides and no-balls do not count as legal balls.
    | The database's counts_as_legal_ball value is preferred when available.
    | Otherwise, wide/no-ball deliveries are treated as illegal.
    */

    $overGroups = [];
    $legalBallsSeen = 0;

    $orderedDeliveries = $current
        ? $current->deliveries->sortBy('id')->values()
        : collect();

    foreach ($orderedDeliveries as $delivery) {
        $overIndex = intdiv($legalBallsSeen, 6);
        $ballPosition = ($legalBallsSeen % 6) + 1;

        $extraType = $delivery->extra_type ?? 'none';

        if ($delivery->getAttribute('counts_as_legal_ball') !== null) {
            $isLegalDelivery = (bool) $delivery->getAttribute('counts_as_legal_ball');
        } else {
            $isLegalDelivery = !in_array($extraType, ['wide', 'no_ball'], true);
        }

        if (!isset($overGroups[$overIndex])) {
            $overGroups[$overIndex] = [
                'balls' => [],
                'runs' => 0,
                'wickets' => 0,
                'legal_balls' => 0,
            ];
        }

        $overGroups[$overIndex]['balls'][] = [
            'delivery' => $delivery,
            'position' => $ballPosition,
            'is_legal' => $isLegalDelivery,
        ];

        $overGroups[$overIndex]['runs'] += (int) $delivery->totalRuns();

        if ($delivery->is_wicket) {
            $overGroups[$overIndex]['wickets']++;
        }

        if ($isLegalDelivery) {
            $overGroups[$overIndex]['legal_balls']++;
            $legalBallsSeen++;
        }
    }

    ksort($overGroups);
@endphp

<div class="min-h-screen bg-[#F6F8FB] px-4 py-6 sm:px-6">
    <main class="mx-auto max-w-5xl space-y-5">

        {{-- Match header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] text-[#F06445]">
                    NPL Season Hub
                </p>

                <h1 class="mt-2 text-2xl font-black text-[#202766]">
                    Match centre
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $fixture->match_type ?? 'Cricket match' }}
                    ·
                    {{ $fixture->venue ?: 'Venue not set' }}
                </p>
            </div>

            @if($current && ($fixture->status ?? '') === 'live')
                <span class="rounded-full bg-red-50 px-3 py-2 text-xs font-black uppercase text-red-700">
                    <span class="mr-1 inline-block h-2 w-2 rounded-full bg-red-600"></span>
                    Live
                </span>
            @else
                <span class="rounded-full bg-slate-200 px-3 py-2 text-xs font-bold text-slate-600">
                    {{ $current ? 'Score available' : 'Awaiting score' }}
                </span>
            @endif
        </header>

        {{-- Main scoreboard --}}
        <section class="overflow-hidden rounded-3xl bg-[#202766] text-white shadow-lg">

            <div class="border-b border-white/10 px-5 py-4 sm:px-8">
                <p class="text-sm font-bold text-indigo-100">
                    Match {{ $fixture->match_number ?? $fixture->id }}
                </p>

                <p class="mt-1 text-sm text-indigo-200">
                    {{ $fixture->venue ?: 'Venue not set' }}
                </p>
            </div>

            <div class="grid gap-6 p-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:p-8">

                <div>
                    <p class="font-bold">
                        {{ $current?->battingTeam?->name ?? $fixture->homeTeam->name ?? 'Home team' }}
                    </p>

                    <p class="mt-2 text-4xl font-black sm:text-5xl">
                        {{ $current ? $current->total_runs . '/' . $current->wickets : '—' }}
                    </p>

                    <p class="mt-2 text-sm text-indigo-100">
                        {{ $current ? $current->oversText() . ' overs' : 'Yet to bat' }}
                    </p>

                    @if($current)
                        <p class="mt-1 text-sm text-indigo-100">
                            Run rate: {{ number_format($current->runRate(), 2) }}
                        </p>
                    @endif
                </div>

                <div class="hidden text-xs font-black tracking-[.25em] text-white/40 sm:block">
                    VS
                </div>

                <div class="sm:text-right">
                    <p class="font-bold">
                        {{ $current?->bowlingTeam?->name ?? $fixture->awayTeam->name ?? 'Away team' }}
                    </p>

                    @php
                        $otherInnings = $current
                            ? $innings->firstWhere(
                                'innings_number',
                                $current->innings_number === 1 ? 2 : 1
                            )
                            : null;
                    @endphp

                    <p class="mt-2 text-4xl font-black sm:text-5xl">
                        {{ $otherInnings ? $otherInnings->total_runs . '/' . $otherInnings->wickets : '—' }}
                    </p>

                    <p class="mt-2 text-sm text-indigo-100">
                        {{ $otherInnings ? $otherInnings->oversText() . ' overs' : 'Other innings' }}
                    </p>
                </div>
            </div>

            @if($fixture->tossWinner)
                <div class="border-t border-white/10 bg-white/5 px-5 py-3 text-sm text-indigo-100 sm:px-8">
                    Toss:
                    <strong class="text-white">{{ $fixture->tossWinner->name }}</strong>
                    chose to
                    <strong class="text-white">{{ $fixture->toss_decision }}</strong>.
                </div>
            @endif

            @if($current && $current->free_hit_next)
                <div class="border-t border-orange-300/30 bg-orange-500 px-5 py-3 text-sm font-black text-white sm:px-8">
                    FREE HIT — next delivery
                </div>
            @endif

            @if($current && $current->innings_number === 2)
                @php
                    $firstInnings = $innings->firstWhere('innings_number', 1);
                @endphp

                @if($firstInnings)
                    <div class="border-t border-white/10 px-5 py-3 text-sm text-indigo-100 sm:px-8">
                        Target:
                        <strong class="text-white">{{ $firstInnings->total_runs + 1 }}</strong>

                        <span class="mx-1">·</span>

                        Runs required:
                        <strong class="text-white">
                            {{ max(0, $firstInnings->total_runs + 1 - $current->total_runs) }}
                        </strong>
                    </div>
                @endif
            @endif

        </section>

        @if($current)

            {{-- Batting and bowling cards --}}
            <div class="grid gap-5 md:grid-cols-2">

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-black text-[#202766]">Batting</h2>
                        <span class="text-xs font-bold text-slate-400">R (B)</span>
                    </div>

                    @foreach($batters as $batter)
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-3 first:mt-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    {{ $batter['name'] }}

                                    @if($batter['name'] === $current->striker)
                                        <span class="text-[#F06445]">*</span>
                                    @endif
                                </p>

                                <p class="text-xs text-slate-500">
                                    {{ $batter['fours'] }} fours
                                    ·
                                    {{ $batter['sixes'] }} sixes

                                    @if($batter['out'])
                                        · {{ $batter['dismissal'] ?: 'out' }}
                                    @endif
                                </p>
                            </div>

                            <p class="text-lg font-black text-slate-900">
                                {{ $batter['runs'] }}
                                <span class="text-sm font-semibold text-slate-400">
                                    ({{ $batter['balls'] }})
                                </span>
                            </p>
                        </div>
                    @endforeach

                    <div class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-600">
                        Extras: <strong>{{ $current->extras }}</strong>
                        <span class="mx-1">·</span>
                        Total:
                        <strong>{{ $current->total_runs }}/{{ $current->wickets }}</strong>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-black text-[#202766]">Bowling</h2>
                        <span class="text-xs font-bold text-slate-400">O · W / R</span>
                    </div>

                    @forelse($bowlers as $bowler)
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-3 first:mt-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    {{ $bowler['name'] }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    {{ intdiv($bowler['legal_balls'], 6) }}.{{ $bowler['legal_balls'] % 6 }}
                                    overs
                                </p>
                            </div>

                            <p class="text-lg font-black text-slate-900">
                                {{ $bowler['wickets'] }}/{{ $bowler['runs'] }}
                            </p>
                        </div>
                    @empty
                        <p class="mt-4 text-sm text-slate-500">
                            Bowling figures appear after the first delivery.
                        </p>
                    @endforelse
                </section>

            </div>

            {{-- NEW: Over-by-over scoreboard --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-black text-[#202766]">
                            Over-by-over
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Every delivery grouped by legal-ball count
                        </p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                        Auto-refresh · 10 sec
                    </span>
                </div>

                @if(count($overGroups))
                    <div class="divide-y divide-slate-100">

                        @foreach($overGroups as $overNumber => $overData)
                            @php
                                $isCurrentOver = $loop->last;
                                $overLabel = $overNumber + 1;
                            @endphp

                            <article class="p-5 sm:p-6">

                                {{-- Over heading and summary --}}
                                <div class="flex flex-wrap items-start justify-between gap-3">

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-black text-[#202766]">
                                                Over {{ $overLabel }}
                                            </h3>

                                            @if($isCurrentOver && $overData['legal_balls'] < 6)
                                                <span class="rounded-full bg-orange-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-orange-800">
                                                    In progress
                                                </span>
                                            @elseif($overData['legal_balls'] >= 6)
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700">
                                                    Complete
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $overData['legal_balls'] }} legal
                                            {{ $overData['legal_balls'] === 1 ? 'ball' : 'balls' }}

                                            @if($overData['wickets'] > 0)
                                                · {{ $overData['wickets'] }}
                                                {{ $overData['wickets'] === 1 ? 'wicket' : 'wickets' }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-2xl font-black text-[#202766]">
                                            {{ $overData['runs'] }}
                                        </p>

                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                            Over runs
                                        </p>
                                    </div>
                                </div>

                                {{-- Delivery balls --}}
                                <div class="mt-4 flex flex-wrap gap-2">

                                    @foreach($overData['balls'] as $ballItem)
                                        @php
                                            $ball = $ballItem['delivery'];
                                            $extraType = $ball->extra_type ?? 'none';

                                            if ($ball->is_wicket) {
                                                $ballLabel = 'W';
                                                $ballStyle = 'border-rose-200 bg-rose-50 text-rose-700';
                                            } elseif ($extraType === 'wide') {
                                                $ballLabel = 'Wd';
                                                $ballStyle = 'border-orange-200 bg-orange-50 text-orange-800';
                                            } elseif ($extraType === 'no_ball') {
                                                $ballLabel = 'Nb';
                                                $ballStyle = 'border-orange-200 bg-orange-50 text-orange-800';
                                            } elseif ($extraType === 'bye') {
                                                $ballLabel = 'B';
                                                $ballStyle = 'border-violet-200 bg-violet-50 text-violet-800';
                                            } elseif ($extraType === 'leg_bye') {
                                                $ballLabel = 'Lb';
                                                $ballStyle = 'border-violet-200 bg-violet-50 text-violet-800';
                                            } elseif ($extraType === 'penalty') {
                                                $ballLabel = 'P';
                                                $ballStyle = 'border-amber-200 bg-amber-50 text-amber-800';
                                            } elseif ((int) $ball->batter_runs === 4) {
                                                $ballLabel = '4';
                                                $ballStyle = 'border-blue-200 bg-blue-50 text-blue-700';
                                            } elseif ((int) $ball->batter_runs === 6) {
                                                $ballLabel = '6';
                                                $ballStyle = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                            } else {
                                                $ballLabel = (string) $ball->batter_runs;
                                                $ballStyle = 'border-slate-200 bg-slate-50 text-[#202766]';
                                            }

                                            /*
                                             * Illegal deliveries display the next legal-ball
                                             * position, e.g. 2Wd, without advancing the over.
                                             */
                                            $deliveryPosition = $ballItem['position'];

                                            if (!$ballItem['is_legal']) {
                                                $deliveryPositionLabel = $deliveryPosition . (
                                                    $extraType === 'wide' ? 'Wd' : 'Nb'
                                                );
                                            } else {
                                                $deliveryPositionLabel = (string) $deliveryPosition;
                                            }
                                        @endphp

                                        <div
                                            class="flex min-w-[58px] flex-col items-center justify-center rounded-xl border px-3 py-2.5 {{ $ballStyle }}"
                                            title="{{ $ball->striker ?? 'Batter' }} vs {{ $ball->bowler ?? 'Bowler' }} — {{ $ball->totalRuns() }} run(s)"
                                        >
                                            <span class="text-[10px] font-semibold opacity-60">
                                                {{ $deliveryPositionLabel }}
                                            </span>

                                            <span class="mt-0.5 text-lg font-black leading-tight">
                                                {{ $ballLabel }}
                                            </span>

                                            <span class="mt-1 text-[10px] font-semibold">
                                                {{ $ball->totalRuns() }} {{ $ball->totalRuns() === 1 ? 'run' : 'runs' }}
                                            </span>
                                        </div>
                                    @endforeach

                                </div>

                                {{-- Over summary --}}
                                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                    <span>
                                        Runs:
                                        <strong class="text-slate-800">{{ $overData['runs'] }}</strong>
                                    </span>

                                    <span>
                                        Wickets:
                                        <strong class="text-slate-800">{{ $overData['wickets'] }}</strong>
                                    </span>

                                    <span>
                                        Legal balls:
                                        <strong class="text-slate-800">{{ $overData['legal_balls'] }}/6</strong>
                                    </span>
                                </div>

                            </article>
                        @endforeach

                    </div>
                @else
                    <div class="px-5 py-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-[#202766]">
                            <span class="text-xl font-black">0</span>
                        </div>

                        <h3 class="mt-3 font-black text-slate-800">
                            No deliveries yet
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            The over-by-over scoreboard will appear when the scorer records the first delivery.
                        </p>
                    </div>
                @endif

                {{-- Ball legend --}}
                <div class="flex flex-wrap gap-x-4 gap-y-2 border-t border-slate-100 bg-slate-50 px-5 py-3 text-[11px] font-semibold text-slate-500 sm:px-6">
                    <span><strong class="text-blue-700">4</strong> Boundary</span>
                    <span><strong class="text-emerald-700">6</strong> Six</span>
                    <span><strong class="text-rose-700">W</strong> Wicket</span>
                    <span><strong class="text-orange-800">Wd</strong> Wide</span>
                    <span><strong class="text-orange-800">Nb</strong> No-ball</span>
                    <span><strong class="text-violet-800">B/Lb</strong> Byes / Leg-byes</span>
                </div>

            </section>

            {{-- Innings summary --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-black text-[#202766]">
                    Innings summary
                </h2>

                <div class="mt-3 divide-y divide-slate-100">
                    @foreach($innings as $inning)
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    {{ $inning->battingTeam->name ?? 'Batting team' }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    Innings {{ $inning->innings_number }}
                                    · {{ $inning->oversText() }} overs
                                    · Extras {{ $inning->extras }}
                                </p>
                            </div>

                            <p class="text-xl font-black text-[#202766]">
                                {{ $inning->total_runs }}/{{ $inning->wickets }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

        @else

            <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <h2 class="font-black text-slate-900">
                    Score not entered yet
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    The scorecard will appear when the scorer starts an innings.
                </p>
            </section>

        @endif

        <p class="text-xs leading-5 text-slate-500">
            Scores reflect deliveries entered by the match scorer.
            This page refreshes every 10 seconds.
        </p>

    </main>
</div>
@endsection