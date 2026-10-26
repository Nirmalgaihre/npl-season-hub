
@extends('layouts.app')

@section('title', 'Live Score | ' . ($fixture->homeTeam->name ?? 'Home') . ' vs ' . ($fixture->awayTeam->name ?? 'Away'))

@section('content')
<meta http-equiv="refresh" content="10">

<div class="min-h-screen bg-[#F6F8FB] px-4 py-6 sm:px-6">
    <main class="mx-auto max-w-5xl space-y-5">

        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] text-[#F06445]">NPL Season Hub</p>
                <h1 class="mt-2 text-2xl font-black text-[#202766]">Match centre</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $fixture->match_type ?? 'Cricket match' }} · {{ $fixture->venue ?: 'Venue not set' }}
                </p>
            </div>
            @if($current && ($fixture->status ?? '') === 'live')
                <span class="rounded-full bg-red-50 px-3 py-2 text-xs font-black uppercase text-red-700">● Live</span>
            @else
                <span class="rounded-full bg-slate-200 px-3 py-2 text-xs font-bold text-slate-600">
                    {{ $current ? 'Score available' : 'Awaiting score' }}
                </span>
            @endif
        </header>

        <section class="overflow-hidden rounded-3xl bg-[#202766] text-white shadow-lg">
            <div class="border-b border-white/10 px-5 py-4 sm:px-8">
                <p class="text-sm font-bold text-indigo-100">
                    Match {{ $fixture->match_number ?? $fixture->id }}
                </p>
                <p class="mt-1 text-sm text-indigo-200">{{ $fixture->venue ?: 'Venue not set' }}</p>
            </div>

            <div class="grid gap-6 p-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:p-8">
                <div>
                    <p class="font-bold">{{ $current?->battingTeam?->name ?? $fixture->homeTeam->name ?? 'Home team' }}</p>
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

                <div class="hidden text-xs font-black tracking-[.25em] text-white/40 sm:block">VS</div>

                <div class="sm:text-right">
                    <p class="font-bold">{{ $current?->bowlingTeam?->name ?? $fixture->awayTeam->name ?? 'Away team' }}</p>
                    @php($otherInnings = $current ? $innings->firstWhere('innings_number', $current->innings_number === 1 ? 2 : 1) : null)
                    <p class="mt-2 text-4xl font-black sm:text-5xl">{{ $otherInnings ? $otherInnings->total_runs . '/' . $otherInnings->wickets : '—' }}</p>
                    <p class="mt-2 text-sm text-indigo-100">{{ $otherInnings ? $otherInnings->oversText() . ' overs' : 'Other innings' }}</p>
                </div>
            </div>

            @if($fixture->tossWinner)
                <div class="border-t border-white/10 bg-white/5 px-5 py-3 text-sm text-indigo-100 sm:px-8">
                    Toss: <strong class="text-white">{{ $fixture->tossWinner->name }}</strong>
                    chose to <strong class="text-white">{{ $fixture->toss_decision }}</strong>.
                </div>
            @endif

            @if($current && $current->free_hit_next)
                <div class="border-t border-orange-300/30 bg-orange-500 px-5 py-3 text-sm font-black text-white sm:px-8">
                    FREE HIT — next delivery
                </div>
            @endif

            @if($current && $current->innings_number === 2)
                @php($firstInnings = $innings->firstWhere('innings_number', 1))
                @if($firstInnings)
                    <div class="border-t border-white/10 px-5 py-3 text-sm text-indigo-100 sm:px-8">
                        Target: <strong class="text-white">{{ $firstInnings->total_runs + 1 }}</strong>
                        · Runs required: <strong class="text-white">{{ max(0, $firstInnings->total_runs + 1 - $current->total_runs) }}</strong>
                    </div>
                @endif
            @endif
        </section>

        @if($current)
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
                                    @if($batter['name'] === $current->striker) <span class="text-[#F06445]">*</span> @endif
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $batter['fours'] }} fours · {{ $batter['sixes'] }} sixes
                                    @if($batter['out']) · {{ $batter['dismissal'] ?: 'out' }} @endif
                                </p>
                            </div>
                            <p class="text-lg font-black text-slate-900">
                                {{ $batter['runs'] }}
                                <span class="text-sm font-semibold text-slate-400">({{ $batter['balls'] }})</span>
                            </p>
                        </div>
                    @endforeach

                    <div class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-600">
                        Extras: <strong>{{ $current->extras }}</strong>
                        <span class="mx-1">·</span>
                        Total: <strong>{{ $current->total_runs }}/{{ $current->wickets }}</strong>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-black text-[#202766]">Bowling</h2>
                        <span class="text-xs font-bold text-slate-400">O · R · W</span>
                    </div>

                    @forelse($bowlers as $bowler)
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-3 first:mt-3">
                            <div>
                                <p class="font-bold text-slate-800">{{ $bowler['name'] }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ intdiv($bowler['legal_balls'], 6) }}.{{ $bowler['legal_balls'] % 6 }} overs
                                </p>
                            </div>
                            <p class="text-lg font-black text-slate-900">
                                {{ $bowler['wickets'] }}/{{ $bowler['runs'] }}
                            </p>
                        </div>
                    @empty
                        <p class="mt-4 text-sm text-slate-500">Bowling figures appear after the first delivery.</p>
                    @endforelse
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-black text-[#202766]">Recent deliveries</h2>
                    <span class="text-xs text-slate-400">Refreshes every 10 seconds</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse($current->deliveries->sortByDesc('ball_number')->take(12)->reverse() as $ball)
                        <div class="min-w-14 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center">
                            <p class="text-xs text-slate-400">{{ $ball->ball_number }}</p>
                            <p class="mt-1 font-black text-[#202766]">
                                @if($ball->is_wicket)
                                    W
                                @elseif($ball->extra_type === 'wide')
                                    WD
                                @elseif($ball->extra_type === 'no_ball')
                                    NB
                                @elseif($ball->extra_type === 'bye')
                                    B
                                @elseif($ball->extra_type === 'leg_bye')
                                    LB
                                @else
                                    {{ $ball->batter_runs }}
                                @endif
                            </p>
                            <p class="text-xs text-slate-500">{{ $ball->totalRuns() }} run(s)</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No deliveries recorded yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-black text-[#202766]">Innings summary</h2>
                <div class="mt-3 divide-y divide-slate-100">
                    @foreach($innings as $inning)
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    {{ $inning->battingTeam->name ?? 'Batting team' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    Innings {{ $inning->innings_number }} · {{ $inning->oversText() }} overs · Extras {{ $inning->extras }}
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
                <h2 class="font-black text-slate-900">Score not entered yet</h2>
                <p class="mt-2 text-sm text-slate-500">The scorecard will appear when the scorer starts an innings.</p>
            </section>
        @endif

        <p class="text-xs leading-5 text-slate-500">
            Scores reflect deliveries entered by the match scorer. This page refreshes every 10 seconds.
        </p>
    </main>
</div>
@endsection
