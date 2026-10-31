
{{-- resources/views/admin/pointtable/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Points Table Management')
@section('page_heading', 'Points Table Management')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-orange-600">
                Tournament Management
            </p>

            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">
                Points Table
            </h2>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
                View automatically calculated standings and manage team penalty points.
            </p>
        </div>

        @if ($selectedSeason)
            <a
                href="{{ route('pointtable.index', ['season' => $selectedSeason->id]) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-600"
            >
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                View Public Table
            </a>
        @endif
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            <i class="fa-solid fa-circle-check mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Please check the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!$selectedSeason)
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <i class="fa-solid fa-calendar-xmark text-3xl text-gray-300"></i>
            <h3 class="mt-4 font-bold text-gray-900">No season available</h3>
            <p class="mt-2 text-sm text-gray-500">
                Create a season before managing the points table.
            </p>
        </div>
    @else

        {{-- SEASON FILTER AND SUMMARY --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Selected season
                </p>

                <form action="{{ route('admin.pointtable.index') }}" method="GET" class="mt-3">
                    <select
                        name="season"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-sm font-semibold text-gray-900 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                    >
                        @foreach ($seasons as $seasonOption)
                            <option
                                value="{{ $seasonOption->id }}"
                                @selected((int) $seasonOption->id === (int) $selectedSeason->id)
                            >
                                {{ $seasonOption->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Participating teams
                </p>
                <p class="mt-3 text-3xl font-black text-gray-900">
                    {{ $teams->count() }}
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    Teams registered for this season
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Total fixtures
                </p>
                <p class="mt-3 text-3xl font-black text-gray-900">
                    {{ $fixtureCount }}
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    Includes scheduled and completed fixtures
                </p>
            </div>
        </div>

        {{-- AUTOMATIC STANDINGS --}}
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">

            <div class="flex flex-col justify-between gap-3 border-b border-gray-200 px-5 py-5 sm:flex-row sm:items-center">
                <div>
                    <h3 class="font-bold text-gray-900">
                        {{ $selectedSeason->name }} — Standings
                    </h3>
                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Calculated from saved match results and saved penalty adjustments.
                    </p>
                </div>

                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    Auto-calculated
                </span>
            </div>

            @if ($standings->isEmpty())
                <div class="px-6 py-14 text-center">
                    <i class="fa-solid fa-ranking-star text-3xl text-gray-300"></i>
                    <h3 class="mt-3 font-bold text-gray-900">No teams found</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Assign teams to this season or create fixtures involving those teams.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left">
                        <thead class="bg-gray-50">
                            <tr class="text-[10px] font-extrabold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-4">Pos</th>
                                <th class="px-5 py-4">Team</th>
                                <th class="px-4 py-4 text-center">GP</th>
                                <th class="px-4 py-4 text-center">W</th>
                                <th class="px-4 py-4 text-center">T</th>
                                <th class="px-4 py-4 text-center">L</th>
                                <th class="px-4 py-4 text-center">NR</th>
                                <th class="px-5 py-4 text-center">PTS</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($standings as $index => $row)
                                @php
                                    $team = $row['team'];
                                @endphp

                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg
                                            {{ $index === 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}
                                            text-xs font-black">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($team->logo)
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::url($team->logo) }}"
                                                    alt="{{ $team->name }}"
                                                    class="h-10 w-10 rounded-lg border border-gray-100 bg-white object-contain p-1"
                                                    loading="lazy"
                                                >
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-black text-gray-500">
                                                    {{ strtoupper(substr($team->short_name ?: $team->name, 0, 3)) }}
                                                </div>
                                            @endif

                                            <div>
                                                <p class="text-sm font-bold text-gray-900">
                                                    {{ $team->name }}
                                                </p>
                                                <p class="mt-0.5 text-xs text-gray-400">
                                                    {{ $team->short_name ?: 'Team' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-center text-sm font-semibold text-gray-700">
                                        {{ $row['gp'] }}
                                    </td>

                                    <td class="px-4 py-4 text-center text-sm font-bold text-emerald-700">
                                        {{ $row['w'] }}
                                    </td>

                                    <td class="px-4 py-4 text-center text-sm text-gray-700">
                                        {{ $row['t'] }}
                                    </td>

                                    <td class="px-4 py-4 text-center text-sm text-gray-700">
                                        {{ $row['l'] }}
                                    </td>

                                    <td class="px-4 py-4 text-center text-sm text-gray-700">
                                        {{ $row['nr'] }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex min-w-10 items-center justify-center rounded-lg bg-orange-50 px-3 py-2 text-sm font-black text-orange-700">
                                            {{ $row['pts'] }}
                                        </span>

                                        @if ($row['penalty'] > 0)
                                            <p class="mt-1 text-[10px] font-medium text-red-600">
                                                −{{ $row['penalty'] }} penalty
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-xs leading-5 text-gray-500">
                        GP = Games Played · W = Wins · T = Ties · L = Losses ·
                        NR = No Result · PTS = Points after penalties
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Default scoring: win 2 points, tie/no result 1 point, loss 0 points.
                    </p>
                </div>
            @endif
        </section>

        {{-- MANUAL PENALTY ADJUSTMENTS --}}
        <form
            action="{{ route('admin.pointtable.update') }}"
            method="POST"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white"
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="season_id"
                value="{{ $selectedSeason->id }}"
            >

            <div class="flex flex-col justify-between gap-3 border-b border-gray-200 px-5 py-5 sm:flex-row sm:items-center">
                <div>
                    <h3 class="font-bold text-gray-900">
                        Team point adjustments
                    </h3>
                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Enter a deduction and its reason. These penalties are subtracted from calculated points.
                    </p>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-orange-700"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Adjustments
                </button>
            </div>

            @if ($teams->isEmpty())
                <div class="px-6 py-14 text-center">
                    <i class="fa-solid fa-people-group text-3xl text-gray-300"></i>
                    <h3 class="mt-3 font-bold text-gray-900">No teams for this season</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Assign teams to this season or create fixtures involving the teams.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left">
                        <thead class="bg-gray-50">
                            <tr class="text-[10px] font-extrabold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-4">Team</th>
                                <th class="px-4 py-4">Penalty points</th>
                                <th class="px-5 py-4">Reason / notes</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($teams as $index => $team)
                                @php
                                    $adjustment = $adjustments->get($team->id);

                                    $penaltyValue = old(
                                        "adjustments.$index.penalty_points",
                                        $adjustment?->penalty_points ?? 0
                                    );

                                    $notesValue = old(
                                        "adjustments.$index.notes",
                                        $adjustment?->notes ?? ''
                                    );
                                @endphp

                                <tr class="hover:bg-gray-50/70">
                                    <td class="px-5 py-4">
                                        <input
                                            type="hidden"
                                            name="adjustments[{{ $index }}][team_id]"
                                            value="{{ $team->id }}"
                                        >

                                        <div class="flex items-center gap-3">
                                            @if ($team->logo)
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::url($team->logo) }}"
                                                    alt="{{ $team->name }}"
                                                    class="h-10 w-10 rounded-lg border border-gray-100 bg-white object-contain p-1"
                                                    loading="lazy"
                                                >
                                            @else
                                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-xs font-black text-gray-500">
                                                    {{ strtoupper(substr($team->short_name ?: $team->name, 0, 3)) }}
                                                </div>
                                            @endif

                                            <div>
                                                <p class="text-sm font-bold text-gray-900">
                                                    {{ $team->name }}
                                                </p>
                                                <p class="mt-0.5 text-xs text-gray-400">
                                                    {{ $team->short_name ?: 'Team' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="1"
                                            name="adjustments[{{ $index }}][penalty_points]"
                                            value="{{ $penaltyValue }}"
                                            required
                                            class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm font-bold text-gray-900 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                                        >
                                        <p class="mt-1 text-[10px] text-gray-400">
                                            Points deducted
                                        </p>
                                    </td>

                                    <td class="px-5 py-4">
                                        <input
                                            type="text"
                                            maxlength="1000"
                                            name="adjustments[{{ $index }}][notes]"
                                            value="{{ $notesValue }}"
                                            placeholder="Reason for adjustment (optional)"
                                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-800 outline-none placeholder:text-gray-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 sm:flex-row sm:items-center">
                    <p class="text-xs leading-5 text-gray-500">
                        Match statistics are calculated from saved results. This form only manages deductions.
                    </p>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>
                </div>
            @endif
        </form>

    @endif
</div>
@endsection
