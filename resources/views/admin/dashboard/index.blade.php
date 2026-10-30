
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page_heading', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-500">NPL Season Hub / Admin</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">
                Dashboard Overview
            </h1>
            <p class="mt-2 text-sm text-gray-500">
                Monitor fixtures and match activity from one place.
            </p>
        </div>

        <a href="{{ route('admin.fixtures.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
            <i class="fa-solid fa-calendar-plus"></i>
            Manage Fixtures
        </a>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-500">Total Fixtures</div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-700">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900">
                {{ number_format($stats['total_fixtures']) }}
            </div>
            <p class="mt-1 text-xs text-gray-500">All recorded fixtures</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-500">Upcoming</div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i class="fa-regular fa-clock text-lg"></i>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900">
                {{ number_format($stats['upcoming_fixtures']) }}
            </div>
            <p class="mt-1 text-xs text-gray-500">Scheduled fixtures</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-500">Live Matches</div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-600"></span>
                    </span>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900">
                {{ number_format($stats['live_fixtures']) }}
            </div>
            <p class="mt-1 text-xs text-gray-500">Currently marked live</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-500">Completed</div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-green-600">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900">
                {{ number_format($stats['completed_fixtures']) }}
            </div>
            <p class="mt-1 text-xs text-gray-500">Finished fixtures</p>
        </div>

    </div>

    {{-- Quick actions --}}
    <div>
        <h2 class="text-lg font-bold text-gray-900">Quick Actions</h2>
        <p class="mt-1 text-sm text-gray-500">
            Jump directly to common administration tasks.
        </p>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            <a href="{{ route('admin.fixtures.index') }}"
               class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-red-200 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 transition group-hover:bg-red-100">
                        <i class="fa-solid fa-list-check text-xl"></i>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Manage Fixtures</h3>
                        <p class="mt-1 text-sm text-gray-500">View and update match schedules.</p>
                    </div>
                    <i class="fa-solid fa-arrow-right ml-auto text-gray-400 transition group-hover:translate-x-1 group-hover:text-red-600"></i>
                </div>
            </a>

            <a href="{{ route('admin.fixtures.create') }}"
               class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-red-200 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Create Fixture</h3>
                        <p class="mt-1 text-sm text-gray-500">Add a new match to the season.</p>
                    </div>
                    <i class="fa-solid fa-arrow-right ml-auto text-gray-400 transition group-hover:translate-x-1 group-hover:text-blue-600"></i>
                </div>
            </a>

            <a href="{{ route('admin.fixtures.index') }}"
               class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-red-200 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-600">
                        <i class="fa-solid fa-chart-line text-xl"></i>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Season Activity</h3>
                        <p class="mt-1 text-sm text-gray-500">Review the current fixture list.</p>
                    </div>
                    <i class="fa-solid fa-arrow-right ml-auto text-gray-400 transition group-hover:translate-x-1 group-hover:text-green-600"></i>
                </div>
            </a>

        </div>
    </div>

    {{-- Recent fixtures --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-gray-900">Recent Fixtures</h2>
                <p class="mt-1 text-sm text-gray-500">
                    The latest fixtures added to the system.
                </p>
            </div>

            <a href="{{ route('admin.fixtures.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-700">
                View all fixtures
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        @if($recentFixtures->isEmpty())
            <div class="px-5 py-12 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                    <i class="fa-solid fa-calendar-xmark text-xl"></i>
                </span>
                <h3 class="mt-4 font-semibold text-gray-900">No fixtures yet</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Create a fixture to start building your season schedule.
                </p>
                <a href="{{ route('admin.fixtures.create') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    <i class="fa-solid fa-plus"></i>
                    Create Fixture
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Match
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Date
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Status
                            </th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($recentFixtures as $fixture)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ $fixture->homeTeam?->name ?? 'Team ' . ($fixture->home_team_id ?? 'TBC') }}
                                        <span class="mx-1 text-gray-400">vs</span>
                                        {{ $fixture->awayTeam?->name ?? 'Team ' . ($fixture->away_team_id ?? 'TBC') }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Fixture #{{ $fixture->id }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                    @if($fixture->match_date)
                                        {{ \Illuminate\Support\Carbon::parse($fixture->match_date)->format('d M Y') }}
                                    @else
                                        <span class="text-gray-400">Not scheduled</span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $status = strtolower($fixture->status ?? 'upcoming');

                                        $statusClasses = match ($status) {
                                            'live' => 'bg-red-50 text-red-700 ring-red-600/20',
                                            'completed' => 'bg-green-50 text-green-700 ring-green-600/20',
                                            default => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                        };
                                    @endphp

                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a href="{{ route('admin.fixtures.edit', $fixture->id) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
