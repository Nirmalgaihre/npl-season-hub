
@extends('layouts.admin')

@section('title', 'Fixtures & Results')
@section('page_heading', 'Fixtures & Results')

@section('content')
<div class="mx-auto max-w-[1600px] space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <i class="fa-solid fa-house text-gray-400"></i>
                <span>Workspace</span>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-red-700">Tournament</span>
            </div>

            <h2 class="mt-3 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                Fixtures &amp; Results
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                Manage match schedules, track match status and verify official results.
            </p>
        </div>

        <a href="{{ route('admin.fixtures.create') }}"
           class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
            <i class="fa-solid fa-plus"></i>
            Create fixture
        </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div role="status"
             class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <p class="flex-1">{{ session('success') }}</p>
            <button type="button"
                    onclick="this.parentElement.remove()"
                    class="text-lg leading-none text-emerald-700 hover:text-emerald-900"
                    aria-label="Dismiss notification">&times;</button>
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div role="alert"
             class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <p class="flex-1">{{ session('error') }}</p>
            <button type="button"
                    onclick="this.parentElement.remove()"
                    class="text-lg leading-none"
                    aria-label="Dismiss notification">&times;</button>
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="mb-2 font-semibold">Please check the following:</p>
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">All fixtures</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900">
                        {{ number_format($stats['total']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Total matches recorded</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Scheduled</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900">
                        {{ number_format($stats['scheduled']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Upcoming matches</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <i class="fa-regular fa-clock text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Live matches</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900">
                        {{ number_format($stats['live']) }}
                    </p>
                    <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                        Currently marked live
                    </p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-red-50 text-red-600">
                    <i class="fa-solid fa-tower-broadcast text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900">
                        {{ number_format($stats['completed']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Matches marked completed</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <i class="fa-solid fa-check-double text-lg"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- FILTER PANEL --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                <i class="fa-solid fa-filter"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Find fixtures</h3>
                <p class="mt-0.5 text-xs text-gray-500">Filter matches by team, venue, season or status.</p>
            </div>
        </div>

        <form method="GET"
              action="{{ route('admin.fixtures.index') }}"
              class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">

            <div class="xl:col-span-2">
                <label for="fixture-search" class="mb-2 block text-xs font-semibold text-gray-700">
                    Search
                </label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                    <input id="fixture-search"
                           type="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Team name, short name or venue"
                           class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-500 focus:ring-2 focus:ring-gray-100">
                </div>
            </div>

            <div>
                <label for="fixture-season" class="mb-2 block text-xs font-semibold text-gray-700">
                    Season
                </label>
                <select id="fixture-season"
                        name="season_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-gray-500 focus:ring-2 focus:ring-gray-100">
                    <option value="">All seasons</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}"
                            @selected((string) request('season_id') === (string) $season->id)>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fixture-status" class="mb-2 block text-xs font-semibold text-gray-700">
                    Match status
                </label>
                <select id="fixture-status"
                        name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-gray-500 focus:ring-2 focus:ring-gray-100">
                    <option value="">All statuses</option>
                    @foreach(['scheduled', 'live', 'completed', 'abandoned', 'postponed'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-2 md:col-span-2 xl:col-span-4">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    Apply filters
                </button>

                <a href="{{ route('admin.fixtures.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    Reset
                </a>

                <span class="ml-auto text-xs text-gray-400">
                    {{ $fixtures->total() }} result(s)
                </span>
            </div>
        </form>
    </section>

    {{-- FIXTURES TABLE --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex flex-col justify-between gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Match schedule</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Review fixtures, scores and result verification.
                </p>
            </div>

            <span class="inline-flex w-fit items-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600">
                <i class="fa-solid fa-list text-gray-400"></i>
                {{ $fixtures->firstItem() ?? 0 }}–{{ $fixtures->lastItem() ?? 0 }}
                of {{ $fixtures->total() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3.5">Match</th>
                        <th class="px-5 py-3.5">Season</th>
                        <th class="px-5 py-3.5">Schedule</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Verification</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($fixtures as $fixture)
                        @php
                            $statusStyles = [
                                'scheduled' => 'bg-blue-50 text-blue-700 ring-blue-600/15',
                                'live' => 'bg-red-50 text-red-700 ring-red-600/15',
                                'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15',
                                'abandoned' => 'bg-amber-50 text-amber-800 ring-amber-600/15',
                                'postponed' => 'bg-amber-50 text-amber-800 ring-amber-600/15',
                            ];

                            $statusClass = $statusStyles[$fixture->status]
                                ?? 'bg-gray-100 text-gray-700 ring-gray-500/15';
                        @endphp

                        <tr class="transition hover:bg-gray-50/80">
                            {{-- MATCH --}}
                            <td class="px-5 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-xs font-bold text-gray-600">
                                        {{ $fixture->match_number }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900">
                                            {{ $fixture->homeTeam->short_name ?: $fixture->homeTeam->name }}
                                        </p>

                                        <p class="my-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                                            vs
                                        </p>

                                        <p class="font-semibold text-gray-900">
                                            {{ $fixture->awayTeam->short_name ?: $fixture->awayTeam->name }}
                                        </p>

                                        <p class="mt-2 flex items-center gap-1.5 text-xs text-gray-500">
                                            <i class="fa-solid fa-location-dot text-gray-400"></i>
                                            {{ $fixture->venue ?: 'Venue not set' }}
                                        </p>

                                        @if($fixture->is_result_verified)
                                            <div class="mt-3 inline-flex flex-wrap items-center gap-2 rounded-md bg-gray-50 px-2.5 py-2">
                                                <span class="text-xs font-bold text-gray-900">
                                                    {{ $fixture->home_runs }}/{{ $fixture->home_wickets }}
                                                </span>
                                                <span class="text-[10px] text-gray-500">
                                                    {{ $fixture->home_overs }} ov
                                                </span>

                                                <span class="mx-1 text-gray-300">|</span>

                                                <span class="text-xs font-bold text-gray-900">
                                                    {{ $fixture->away_runs }}/{{ $fixture->away_wickets }}
                                                </span>
                                                <span class="text-[10px] text-gray-500">
                                                    {{ $fixture->away_overs }} ov
                                                </span>
                                            </div>
                                        @elseif($fixture->status === 'completed')
                                            <p class="mt-2 text-xs font-medium text-amber-700">
                                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                                Score not verified
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- SEASON --}}
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 text-xs font-medium text-gray-700">
                                    <i class="fa-regular fa-calendar text-gray-400"></i>
                                    {{ $fixture->season->name ?? 'Season unavailable' }}
                                </span>
                            </td>

                            {{-- SCHEDULE --}}
                            <td class="px-5 py-4">
                                @if($fixture->scheduled_at)
                                    <p class="whitespace-nowrap text-xs font-semibold text-gray-800">
                                        {{ $fixture->scheduled_at->format('d M Y') }}
                                    </p>
                                    <p class="mt-1 whitespace-nowrap text-xs text-gray-500">
                                        <i class="fa-regular fa-clock mr-1 text-gray-400"></i>
                                        {{ $fixture->scheduled_at->format('h:i A') }}
                                    </p>
                                @else
                                    <span class="text-xs text-gray-400">Not scheduled</span>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-md px-2.5 py-1.5 text-[11px] font-semibold ring-1 ring-inset {{ $statusClass }}">
                                    @if($fixture->status === 'live')
                                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-600"></span>
                                    @elseif($fixture->status === 'completed')
                                        <i class="fa-solid fa-check text-[9px]"></i>
                                    @else
                                        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                                    @endif

                                    {{ ucfirst($fixture->status) }}
                                </span>
                            </td>

                            {{-- VERIFICATION --}}
                            <td class="px-5 py-4">
                                @if($fixture->is_result_verified)
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-semibold text-emerald-700">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-amber-700">
                                        <i class="fa-regular fa-clock"></i>
                                        Unverified
                                    </span>
                                @endif
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('fixtures.show', $fixture) }}"
                                       class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2.5 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
                                       title="View match">
                                        <i class="fa-regular fa-eye"></i>
                                        View
                                    </a>

                                    <a href="{{ route('admin.fixtures.edit', $fixture) }}"
                                       class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2.5 py-2 text-xs font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50"
                                       title="Edit fixture">
                                        <i class="fa-solid fa-pen"></i>
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.fixtures.destroy', $fixture) }}"
                                          onsubmit="return confirm('Delete this fixture? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-white px-2.5 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50"
                                                title="Delete fixture">
                                            <i class="fa-regular fa-trash-can"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-gray-100 text-gray-400">
                                    <i class="fa-regular fa-calendar-xmark text-xl"></i>
                                </div>

                                <h4 class="mt-4 text-sm font-semibold text-gray-900">
                                    No fixtures found
                                </h4>

                                <p class="mx-auto mt-2 max-w-sm text-xs leading-5 text-gray-500">
                                    There are no matches matching these filters. Try changing the search criteria or create a new fixture.
                                </p>

                                <div class="mt-5 flex flex-wrap justify-center gap-2">
                                    <a href="{{ route('admin.fixtures.index') }}"
                                       class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        Clear filters
                                    </a>

                                    <a href="{{ route('admin.fixtures.create') }}"
                                       class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                        <i class="fa-solid fa-plus mr-1"></i>
                                        Create fixture
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($fixtures->hasPages() || $fixtures->total() > 0)
            <div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-gray-500">
                    Showing
                    <span class="font-semibold text-gray-800">{{ $fixtures->firstItem() ?? 0 }}</span>
                    to
                    <span class="font-semibold text-gray-800">{{ $fixtures->lastItem() ?? 0 }}</span>
                    of
                    <span class="font-semibold text-gray-800">{{ $fixtures->total() }}</span>
                    fixtures
                </p>

                <div class="text-sm">
                    {{ $fixtures->links() }}
                </div>
            </div>
        @endif
    </section>

</div>
@endsection
