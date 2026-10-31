
{{-- resources/views/pointtable/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Points Table | NPL Season Hub')

@section('meta_description', 'View the Nepal Premier League points table, team standings, match records, points and net run rate.')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $logoUrl = function ($team) {
        $path = $team?->logo;

        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['https://', 'http://', 'data:image/'])) {
            return $path;
        }

        return Storage::url(ltrim($path, '/'));
    };

    $formatNrr = function ($nrr) {
        if ($nrr === null) {
            return '—';
        }

        return ($nrr > 0 ? '+' : '') . number_format($nrr, 3);
    };
@endphp

<div class="min-h-screen bg-[#F5F7FB]">
    {{-- HEADER --}}
    <section class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-orange-700">
                        <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                        Nepal Premier League
                    </div>

                    <h1 class="text-3xl font-black tracking-tight text-gray-950 sm:text-4xl">
                        Points <span class="text-orange-600">Table</span>
                    </h1>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-gray-500 sm:text-base">
                        Follow team standings, match results, points and net run rate throughout the season.
                    </p>
                </div>

                @if ($seasons->isNotEmpty())
                    <form action="{{ route('pointtable.index') }}" method="GET" class="w-full md:w-auto">
                        <label for="season" class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">
                            Select season
                        </label>

                        <div class="flex gap-2">
                            <select
                                id="season"
                                name="season"
                                class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 md:min-w-[220px]"
                            >
                                @foreach ($seasons as $season)
                                    <option
                                        value="{{ $season->id }}"
                                        @selected($selectedSeason && $selectedSeason->id === $season->id)
                                    >
                                        {{ $season->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button
                                type="submit"
                                class="rounded-xl bg-gray-950 px-4 py-3 text-sm font-bold text-white transition hover:bg-orange-600"
                            >
                                View
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (!$selectedSeason)
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <h2 class="text-lg font-bold text-gray-900">No season available</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Add a season and assign teams and fixtures to see the points table.
                </p>
            </div>
        @else
            {{-- SUMMARY CARDS --}}
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Teams</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <circle cx="9" cy="8" r="3"></circle>
                                <path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5v1"></path>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-gray-950">{{ $totalTeams }}</p>
                    <p class="mt-1 text-xs text-gray-400">Teams in this season</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Completed matches</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path d="m5 12 4 4L19 6"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-gray-950">{{ $completedMatches }}</p>
                    <p class="mt-1 text-xs text-gray-400">Matches with completed status</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Upcoming / in progress</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M12 7v5l3 2"></path>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-gray-950">{{ $upcomingMatches }}</p>
                    <p class="mt-1 text-xs text-gray-400">Not yet recorded as a result</p>
                </div>
            </div>

            {{-- TABLE HEADING --}}
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-gray-950">
                        {{ $selectedSeason->name }} Standings
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Sorted by points, then net run rate.
                    </p>
                </div>

                <span class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-500">
                    Updated from saved match results
                </span>
            </div>

            {{-- POINTS TABLE --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                @if ($standings->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path d="M4 19V5m0 14h16M8 15v-3m4 3V7m4 8V9"></path>
                            </svg>
                        </div>

                        <h3 class="mt-4 text-lg font-bold text-gray-900">
                            No teams found
                        </h3>

                        <p class="mt-2 text-sm text-gray-500">
                            Assign teams to this season or add fixtures containing the participating teams.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[850px] border-collapse text-left">
                            <thead>
                                <tr class="bg-gray-50 text-[10px] font-extrabold uppercase tracking-wider text-gray-500">
                                    <th class="px-4 py-4 text-center">Pos</th>
                                    <th class="px-4 py-4">Team</th>
                                    <th class="px-3 py-4 text-center">P</th>
                                    <th class="px-3 py-4 text-center">W</th>
                                    <th class="px-3 py-4 text-center">L</th>
                                    <th class="px-3 py-4 text-center">T</th>
                                    <th class="px-3 py-4 text-center">NR</th>
                                    <th class="px-3 py-4 text-center">Pts</th>
                                    <th class="px-4 py-4 text-right">NRR</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @foreach ($standings as $index => $row)
                                    @php
                                        $team = $row['team'];
                                        $logo = $logoUrl($team);
                                        $position = $index + 1;
                                    @endphp

                                    <tr class="transition hover:bg-orange-50/40">
                                        <td class="px-4 py-4 text-center">
                                            @if ($position === 1)
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-sm font-black text-amber-700">
                                                    1
                                                </span>
                                            @elseif ($position === 2)
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-sm font-black text-gray-600">
                                                    2
                                                </span>
                                            @elseif ($position === 3)
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 text-sm font-black text-orange-700">
                                                    3
                                                </span>
                                            @else
                                                <span class="text-sm font-bold text-gray-500">
                                                    {{ $position }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4">
                                            <div class="flex min-w-[220px] items-center gap-3">
                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-100 bg-gray-50">
                                                    @if ($logo)
                                                        <img
                                                            src="{{ $logo }}"
                                                            alt="{{ $team->name }} logo"
                                                            class="h-full w-full object-contain p-1.5"
                                                            loading="lazy"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                        >
                                                        <span class="hidden h-full w-full items-center justify-center text-xs font-black text-gray-500">
                                                            {{ strtoupper(substr($team->short_name ?: $team->name, 0, 3)) }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs font-black text-gray-500">
                                                            {{ strtoupper(substr($team->short_name ?: $team->name, 0, 3)) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="min-w-0">
                                                    <p class="text-sm font-extrabold text-gray-900">
                                                        {{ $team->name }}
                                                    </p>

                                                    @if ($team->short_name)
                                                        <p class="mt-0.5 text-xs font-medium text-gray-400">
                                                            {{ $team->short_name }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-3 py-4 text-center text-sm font-semibold text-gray-700">
                                            {{ $row['played'] }}
                                        </td>

                                        <td class="px-3 py-4 text-center text-sm font-bold text-emerald-700">
                                            {{ $row['won'] }}
                                        </td>

                                        <td class="px-3 py-4 text-center text-sm font-semibold text-gray-600">
                                            {{ $row['lost'] }}
                                        </td>

                                        <td class="px-3 py-4 text-center text-sm font-semibold text-gray-600">
                                            {{ $row['tied'] }}
                                        </td>

                                        <td class="px-3 py-4 text-center text-sm font-semibold text-gray-600">
                                            {{ $row['no_result'] }}
                                        </td>

                                        <td class="px-3 py-4 text-center">
                                            <span class="inline-flex min-w-9 items-center justify-center rounded-lg bg-gray-950 px-2.5 py-2 text-sm font-black text-white">
                                                {{ $row['points'] }}
                                            </span>

                                            @if ($row['penalty_points'] > 0)
                                                <p class="mt-1 text-[10px] font-semibold text-red-600">
                                                    −{{ $row['penalty_points'] }} penalty
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            @if ($row['nrr_available'])
                                                <span class="text-sm font-extrabold {{ $row['nrr'] > 0 ? 'text-emerald-700' : ($row['nrr'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                                    {{ $formatNrr($row['nrr']) }}
                                                </span>
                                            @else
                                                <span class="text-sm font-medium text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-100 bg-gray-50/70 px-4 py-3 sm:px-6">
                        <p class="text-xs leading-5 text-gray-500">
                            P = Played, W = Won, L = Lost, T = Tied, NR = No Result,
                            Pts = Points, NRR = Net Run Rate. Standard scoring used here:
                            2 points for a win and 1 point for a tie or no result.
                        </p>
                    </div>
                @endif
            </div>

            {{-- POINTS SYSTEM --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                <h3 class="text-sm font-extrabold text-gray-900">How points are calculated</h3>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-500">Win</p>
                        <p class="mt-1 text-xl font-black text-emerald-700">2 points</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-500">Tie / No result</p>
                        <p class="mt-1 text-xl font-black text-gray-900">1 point each</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-500">Loss</p>
                        <p class="mt-1 text-xl font-black text-gray-900">0 points</p>
                    </div>
                </div>

                <p class="mt-4 text-xs leading-5 text-gray-400">
                    Confirm these rules against the official competition regulations.
                    NRR is shown only when both teams have usable run and overs data.
                </p>
            </div>
        @endif
    </main>
</div>
@endsection
