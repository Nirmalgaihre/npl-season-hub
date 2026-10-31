@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-widest text-red-700">
            NPL Management
        </p>

        <h1 class="mt-2 text-3xl font-black text-slate-900">
            Manual Scorecards
        </h1>

        <p class="mt-2 text-slate-500">
            Filter fixtures and enter or update the scorecard for either team.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="font-bold">Please check your filters:</p>
            <ul class="mt-2 list-inside list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin.scorecards.index') }}"
        class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4"
    >
        <label class="text-sm font-semibold text-slate-700">
            Season

            <select
                name="season_id"
                class="mt-2 w-full rounded-xl border-slate-300"
            >
                <option value="">All seasons</option>

                @foreach ($seasons as $season)
                    <option
                        value="{{ $season->id }}"
                        @selected((string) request('season_id') === (string) $season->id)
                    >
                        {{ $season->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-sm font-semibold text-slate-700">
            Team

            <select
                name="team_id"
                class="mt-2 w-full rounded-xl border-slate-300"
            >
                <option value="">All teams</option>

                @foreach ($teams as $team)
                    <option
                        value="{{ $team->id }}"
                        @selected((string) request('team_id') === (string) $team->id)
                    >
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-sm font-semibold text-slate-700">
            Match number

            <input
                type="number"
                min="1"
                name="match_number"
                value="{{ request('match_number') }}"
                class="mt-2 w-full rounded-xl border-slate-300"
                placeholder="e.g. 12"
            >
        </label>

        <div class="flex items-end gap-2">
            <button
                type="submit"
                class="rounded-xl bg-red-700 px-5 py-3 font-bold text-white hover:bg-red-800"
            >
                Filter
            </button>

            <a
                href="{{ route('admin.scorecards.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50"
            >
                Reset
            </a>
        </div>
    </form>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            Showing {{ $fixtures->firstItem() ?? 0 }}–{{ $fixtures->lastItem() ?? 0 }}
            of {{ $fixtures->total() }} fixtures
        </p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Match</th>
                    <th class="px-5 py-4">Season</th>
                    <th class="px-5 py-4">Scheduled</th>
                    <th class="px-5 py-4">Score</th>
                    <th class="px-5 py-4">Result</th>
                    <th class="px-5 py-4">Scorecard</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($fixtures as $fixture)
                    @php
                        $hasScorecard = in_array(
                            (int) $fixture->id,
                            $savedFixtures,
                            true
                        );

                        $homeName = $fixture->homeTeam->short_name
                            ?? $fixture->homeTeam->name
                            ?? 'Home';

                        $awayName = $fixture->awayTeam->short_name
                            ?? $fixture->awayTeam->name
                            ?? 'Away';

                        $hasScores = $fixture->home_runs !== null
                            || $fixture->away_runs !== null;
                    @endphp

                    <tr class="align-top hover:bg-slate-50">
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-900">
                                Match #{{ $fixture->match_number ?? $fixture->id }}
                            </p>

                            <p class="mt-1 whitespace-nowrap text-slate-600">
                                {{ $fixture->homeTeam->name ?? 'Home team' }}
                                <span class="text-slate-400">vs</span>
                                {{ $fixture->awayTeam->name ?? 'Away team' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $fixture->match_type ?? 'Match' }}
                            </p>
                        </td>

                        <td class="px-5 py-4 text-slate-600">
                            {{ $fixture->season->name ?? 'Season' }}
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                            @if ($fixture->scheduled_at)
                                {{ \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('d M Y, h:i A') }}
                            @else
                                <span class="text-slate-400">Not scheduled</span>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            @if ($hasScores)
                                <div class="space-y-2">
                                    <p class="font-bold text-slate-900">
                                        {{ $homeName }}
                                        {{ $fixture->home_runs ?? '—' }}
                                        @if ($fixture->home_wickets !== null)
                                            /{{ $fixture->home_wickets }}
                                        @endif
                                    </p>

                                    @if ($fixture->home_overs !== null)
                                        <p class="-mt-1 text-xs text-slate-500">
                                            {{ $fixture->home_overs }} overs
                                        </p>
                                    @endif

                                    <p class="font-bold text-slate-900">
                                        {{ $awayName }}
                                        {{ $fixture->away_runs ?? '—' }}
                                        @if ($fixture->away_wickets !== null)
                                            /{{ $fixture->away_wickets }}
                                        @endif
                                    </p>

                                    @if ($fixture->away_overs !== null)
                                        <p class="-mt-1 text-xs text-slate-500">
                                            {{ $fixture->away_overs }} overs
                                        </p>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400">No score yet</span>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            @if ($fixture->winner_team_id)
                                <p class="font-semibold text-emerald-700">
                                    {{ $fixture->result_summary ?: 'Result recorded' }}
                                </p>
                            @elseif ($hasScorecard)
                                <span class="text-slate-600">
                                    {{ $fixture->result_summary ?: 'Scorecard saved' }}
                                </span>
                            @else
                                <span class="text-slate-500">Pending</span>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            @if ($hasScorecard)
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                    Saved
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                    Not entered
                                </span>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <a
                                href="{{ route('admin.scorecards.edit', $fixture) }}"
                                class="inline-flex rounded-xl bg-red-700 px-4 py-2.5 font-semibold text-white hover:bg-red-800"
                            >
                                {{ $hasScorecard ? 'Edit scorecard' : 'Enter scorecard' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <p class="font-semibold text-slate-700">
                                No fixtures found
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Try changing the season, team, or match number filters.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $fixtures->links() }}
    </div>
</div>
@endsection