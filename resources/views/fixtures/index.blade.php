@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <section class="bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-400">Nepal Premier League</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Fixtures & Results</h1>
            <p class="mt-4 max-w-2xl text-slate-300">
                Match schedules and confirmed results from the selected NPL season.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('fixtures.index') }}"
              class="mb-8 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row">
            <select name="season" class="min-w-0 flex-1 rounded-xl border-slate-300">
                <option value="">All seasons</option>
                @foreach ($seasons as $season)
                    <option value="{{ $season->id }}" @selected((string) request('season') === (string) $season->id)>
                        {{ $season->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="min-w-0 flex-1 rounded-xl border-slate-300">
                <option value="">All matches</option>
                <option value="upcoming" @selected(request('status') === 'upcoming')>Upcoming</option>
                <option value="live" @selected(request('status') === 'live')>Live</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed results</option>
            </select>

            <button class="rounded-xl bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800">
                Filter matches
            </button>
        </form>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($fixtures as $fixture)
                <a href="{{ route('fixtures.show', $fixture) }}"
                   class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-red-300 hover:shadow-md sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $fixture->season->name }} · Match {{ $fixture->match_number }}
                        </span>

                        <span class="rounded-full px-3 py-1 text-xs font-bold
                            {{ $fixture->status === 'live' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $fixture->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $fixture->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ in_array($fixture->status, ['abandoned', 'postponed']) ? 'bg-amber-100 text-amber-800' : '' }}">
                            {{ $fixture->status === 'live' ? 'LIVE' : ucfirst($fixture->status) }}
                        </span>
                    </div>

                    <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                        <div class="min-w-0 text-center">
                            <p class="break-words text-lg font-black text-slate-900">
                                {{ $fixture->homeTeam->short_name ?: $fixture->homeTeam->name }}
                            </p>
                            @if ($fixture->is_result_verified)
                                <p class="mt-2 text-xl font-bold text-slate-900">
                                    {{ $fixture->home_runs }}/{{ $fixture->home_wickets }}
                                </p>
                                <p class="text-xs text-slate-500">{{ $fixture->home_overs }} overs</p>
                            @endif
                        </div>

                        <span class="text-xs font-black uppercase tracking-widest text-slate-400">VS</span>

                        <div class="min-w-0 text-center">
                            <p class="break-words text-lg font-black text-slate-900">
                                {{ $fixture->awayTeam->short_name ?: $fixture->awayTeam->name }}
                            </p>
                            @if ($fixture->is_result_verified)
                                <p class="mt-2 text-xl font-bold text-slate-900">
                                    {{ $fixture->away_runs }}/{{ $fixture->away_wickets }}
                                </p>
                                <p class="text-xs text-slate-500">{{ $fixture->away_overs }} overs</p>
                            @endif
                        </div>
                    </div>

                    @if ($fixture->is_result_verified && $fixture->result_summary)
                        <p class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm font-medium text-emerald-800">
                            {{ $fixture->result_summary }}
                        </p>
                    @endif

                    <div class="mt-5 border-t border-slate-100 pt-4 text-sm text-slate-500">
                        <p>{{ $fixture->scheduled_at?->format('D, d M Y · h:i A') ?? 'Date to be announced' }}</p>
                        <p class="mt-1">{{ $fixture->venue ?: 'Venue to be announced' }}</p>
                        <p class="mt-4 font-semibold text-red-700 group-hover:text-red-900">
                            Match details →
                        </p>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center lg:col-span-2">
                    <h2 class="text-xl font-bold text-slate-900">No matches available</h2>
                    <p class="mt-2 text-slate-500">
                        Fixtures will appear here when they are added by the administrator.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $fixtures->links() }}</div>
    </div>
</div>
@endsection