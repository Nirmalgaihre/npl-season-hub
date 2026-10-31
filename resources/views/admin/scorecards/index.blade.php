
@extends('layouts.admin')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Safe defaults for optional view data
    |--------------------------------------------------------------------------
    */
    $seasons = $seasons ?? collect();
    $teams = $teams ?? collect();
    $savedFixtures = $savedFixtures ?? [];

    $savedFixtures = collect($savedFixtures)
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="sc-page mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">

    {{-- Page heading --}}
    <div class="sc-heading mb-7">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-red-700">
                    <span class="h-2 w-2 rounded-full bg-red-600"></span>
                    NPL Management
                </div>

                <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Manual Scorecards
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">
                    Manage match scorecards, review saved innings and enter match scores.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Fixtures found
                    </p>
                    <p class="mt-1 text-2xl font-black text-slate-900">
                        {{ $fixtures->total() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div role="status" class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.72a.75.75 0 00-1.06-1.06L9 10.94 7.78 9.72a.75.75 0 00-1.06 1.06l1.75 1.75a.75.75 0 001.06 0l4.25-4.25z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-bold">Changes saved</p>
                <p class="mt-1 text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="font-bold">Please check the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filters --}}
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-4 sm:px-6">
            <h2 class="font-bold text-slate-900">Find a fixture</h2>
            <p class="text-sm text-slate-500">
                Narrow the list by season, team or match number.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('admin.scorecards.index') }}"
            class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 xl:grid-cols-4"
        >
            <label class="block text-sm font-semibold text-slate-700">
                Season

                <select
                    name="season_id"
                    class="sc-control mt-2"
                >
                    <option value="">All seasons</option>

                    @foreach ($seasons as $season)
                        <option
                            value="{{ $season->id }}"
                            @selected((string) request('season_id') === (string) $season->id)
                        >
                            {{ $season->name ?? $season->title ?? ('Season ' . $season->id) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-semibold text-slate-700">
                Team

                <select
                    name="team_id"
                    class="sc-control mt-2"
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

            <label class="block text-sm font-semibold text-slate-700">
                Match number

                <input
                    type="number"
                    min="1"
                    name="match_number"
                    value="{{ request('match_number') }}"
                    class="sc-control mt-2"
                    placeholder="e.g. 12"
                >
            </label>

            <div class="flex flex-wrap items-end gap-2">
                <button
                    type="submit"
                    class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-red-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-100 sm:flex-none"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                    </svg>
                    Apply filters
                </button>

                <a
                    href="{{ route('admin.scorecards.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </section>

    {{-- List header --}}
    <div class="mb-4 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Fixtures</h2>
            <p class="mt-1 text-sm text-slate-500">
                @if ($fixtures->total() > 0)
                    Showing {{ $fixtures->firstItem() }}–{{ $fixtures->lastItem() }}
                    of {{ $fixtures->total() }} fixtures
                @else
                    No fixtures match your current filters.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-500">
            <span class="inline-flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Scorecard saved
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                Not entered
            </span>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th scope="col" class="px-5 py-4">Match</th>
                        <th scope="col" class="px-5 py-4">Season</th>
                        <th scope="col" class="px-5 py-4">Scheduled</th>
                        <th scope="col" class="px-5 py-4">Score</th>
                        <th scope="col" class="px-5 py-4">Result</th>
                        <th scope="col" class="px-5 py-4">Status</th>
                        <th scope="col" class="px-5 py-4 text-right">Action</th>
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

                            $homeTeam = $fixture->homeTeam;
                            $awayTeam = $fixture->awayTeam;

                            $homeName = $homeTeam->short_name
                                ?? $homeTeam->name
                                ?? 'Home';

                            $awayName = $awayTeam->short_name
                                ?? $awayTeam->name
                                ?? 'Away';

                            $hasScores = $fixture->home_runs !== null
                                || $fixture->away_runs !== null;
                        @endphp

                        <tr class="align-top transition hover:bg-slate-50/80">
                            <td class="px-5 py-5">
                                <p class="font-bold text-slate-900">
                                    Match #{{ $fixture->match_number ?? $fixture->id }}
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $homeTeam->name ?? 'Home team' }}
                                    <span class="mx-1 text-slate-400">vs</span>
                                    {{ $awayTeam->name ?? 'Away team' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $fixture->match_type ?? 'Match' }}
                                </p>
                            </td>

                            <td class="px-5 py-5 text-sm text-slate-600">
                                {{ $fixture->season->name ?? $fixture->season->title ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-5 text-sm text-slate-600">
                                @if ($fixture->scheduled_at)
                                    <p class="font-semibold text-slate-700">
                                        {{ \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('d M Y') }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('h:i A') }}
                                    </p>
                                @else
                                    <span class="text-slate-400">Not scheduled</span>
                                @endif
                            </td>

                            <td class="px-5 py-5">
                                @if ($hasScores)
                                    <div class="space-y-3">
                                        <div>
                                            <p class="font-bold text-slate-900">
                                                {{ $homeName }}
                                                <span class="ml-1 tabular-nums">
                                                    {{ $fixture->home_runs ?? '—' }}
                                                    @if ($fixture->home_wickets !== null)
                                                        /{{ $fixture->home_wickets }}
                                                    @endif
                                                </span>
                                            </p>

                                            @if ($fixture->home_overs !== null)
                                                <p class="mt-0.5 text-xs text-slate-500">
                                                    {{ $fixture->home_overs }} overs
                                                </p>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="font-bold text-slate-900">
                                                {{ $awayName }}
                                                <span class="ml-1 tabular-nums">
                                                    {{ $fixture->away_runs ?? '—' }}
                                                    @if ($fixture->away_wickets !== null)
                                                        /{{ $fixture->away_wickets }}
                                                    @endif
                                                </span>
                                            </p>

                                            @if ($fixture->away_overs !== null)
                                                <p class="mt-0.5 text-xs text-slate-500">
                                                    {{ $fixture->away_overs }} overs
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-400">No score yet</span>
                                @endif
                            </td>

                            <td class="max-w-xs px-5 py-5 text-sm">
                                @if ($fixture->winner_team_id)
                                    <p class="font-semibold text-emerald-700">
                                        {{ $fixture->result_summary ?: 'Result recorded' }}
                                    </p>
                                @elseif ($hasScorecard)
                                    <span class="text-slate-600">
                                        {{ $fixture->result_summary ?: 'Scorecard saved' }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Pending</span>
                                @endif
                            </td>

                            <td class="px-5 py-5">
                                @if ($hasScorecard)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Saved
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                        Not entered
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-5 text-right">
                                <a
                                    href="{{ route('admin.scorecards.edit', ['fixture' => $fixture->id]) }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-100"
                                >
                                    {{ $hasScorecard ? 'Edit scorecard' : 'Enter scorecard' }}

                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M5 12h14"/>
                                        <path d="m12 5 7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <circle cx="11" cy="11" r="7"/>
                                        <path d="m16 16 4 4"/>
                                    </svg>
                                </div>
                                <h3 class="mt-4 font-bold text-slate-900">No fixtures found</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Try another season, team or match number.
                                </p>
                                <a
                                    href="{{ route('admin.scorecards.index') }}"
                                    class="mt-4 inline-flex text-sm font-bold text-red-700 hover:text-red-800"
                                >
                                    Clear filters
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Responsive fixture cards for mobile and tablet --}}
    <div class="space-y-4 lg:hidden">
        @forelse ($fixtures as $fixture)
            @php
                $hasScorecard = in_array(
                    (int) $fixture->id,
                    $savedFixtures,
                    true
                );

                $homeTeam = $fixture->homeTeam;
                $awayTeam = $fixture->awayTeam;

                $homeName = $homeTeam->short_name
                    ?? $homeTeam->name
                    ?? 'Home';

                $awayName = $awayTeam->short_name
                    ?? $awayTeam->name
                    ?? 'Away';

                $hasScores = $fixture->home_runs !== null
                    || $fixture->away_runs !== null;
            @endphp

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wider text-red-700">
                            Match #{{ $fixture->match_number ?? $fixture->id }}
                        </p>

                        <h3 class="mt-1 font-bold leading-6 text-slate-900">
                            {{ $homeTeam->name ?? 'Home team' }}
                            <span class="text-slate-400">vs</span>
                            {{ $awayTeam->name ?? 'Away team' }}
                        </h3>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $fixture->season->name ?? $fixture->season->title ?? 'Season not assigned' }}
                        </p>
                    </div>

                    @if ($hasScorecard)
                        <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                            Saved
                        </span>
                    @else
                        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                            Pending
                        </span>
                    @endif
                </div>

                <div class="space-y-4 p-4 sm:p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs font-semibold text-slate-500">
                                {{ $homeName }}
                            </p>
                            <p class="mt-1 text-xl font-black tabular-nums text-slate-900">
                                @if ($hasScores)
                                    {{ $fixture->home_runs ?? '—' }}
                                    @if ($fixture->home_wickets !== null)
                                        <span class="text-base font-semibold">/{{ $fixture->home_wickets }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </p>
                            @if ($fixture->home_overs !== null)
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $fixture->home_overs }} overs
                                </p>
                            @endif
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs font-semibold text-slate-500">
                                {{ $awayName }}
                            </p>
                            <p class="mt-1 text-xl font-black tabular-nums text-slate-900">
                                @if ($hasScores)
                                    {{ $fixture->away_runs ?? '—' }}
                                    @if ($fixture->away_wickets !== null)
                                        <span class="text-base font-semibold">/{{ $fixture->away_wickets }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </p>
                            @if ($fixture->away_overs !== null)
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $fixture->away_overs }} overs
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                        <div class="text-slate-500">
                            @if ($fixture->scheduled_at)
                                <p class="font-semibold text-slate-700">
                                    {{ \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('d M Y, h:i A') }}
                                </p>
                            @else
                                <p>Not scheduled</p>
                            @endif

                            <p class="mt-1">
                                {{ $fixture->result_summary ?: ($fixture->winner_team_id ? 'Result recorded' : 'Result pending') }}
                            </p>
                        </div>

                        <span class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-500">
                            {{ $fixture->match_type ?? 'Match' }}
                        </span>
                    </div>

                    <a
                        href="{{ route('admin.scorecards.edit', ['fixture' => $fixture->id]) }}"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-100"
                    >
                        {{ $hasScorecard ? 'Edit scorecard' : 'Enter scorecard' }}

                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-12 text-center">
                <h3 class="font-bold text-slate-900">No fixtures found</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Try changing your filters.
                </p>
                <a
                    href="{{ route('admin.scorecards.index') }}"
                    class="mt-4 inline-flex font-bold text-red-700 hover:text-red-800"
                >
                    Clear filters
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($fixtures->hasPages())
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white px-4 py-4 sm:px-5">
            {{ $fixtures->links() }}
        </div>
    @endif

</div>

@push('styles')
<style>
    .sc-page {
        color: #0f172a;
    }

    .sc-control {
        display: block;
        width: 100%;
        min-height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background-color: #fff;
        padding: 10px 12px;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .sc-control:focus {
        border-color: #b91c1c;
        box-shadow: 0 0 0 3px rgba(185, 28, 28, .10);
    }

    .sc-control::placeholder {
        color: #94a3b8;
    }
</style>
@endpush
@endsection
