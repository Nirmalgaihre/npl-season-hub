
@extends('layouts.admin')

@section('title', 'Manage Seasons | NPL Season Hub')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Page heading --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-red-700">
                    NPL Season Hub / Administration
                </p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                    Manage Seasons
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Create and manage Nepal Premier League seasons.
                </p>
            </div>

            <a href="{{ route('admin.seasons.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-red-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800">
                + Add Season
            </a>
        </div>

        {{-- Search and filters --}}
        <form method="GET"
              action="{{ route('admin.seasons.index') }}"
              class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="search" class="mb-2 block text-sm font-medium text-slate-700">
                        Search seasons
                    </label>
                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name or slug"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">
                        Status
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
                        <option value="">All statuses</option>
                        <option value="upcoming" @selected(request('status') === 'upcoming')>Upcoming</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Apply filters
                    </button>

                    <a href="{{ route('admin.seasons.index') }}"
                       class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Seasons table --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-900">All Seasons</h2>
                <span class="text-sm text-slate-500">
                    {{ $seasons->total() }} total
                </span>
            </div>

            @if ($seasons->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Season</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Dates</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach ($seasons as $season)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-900">
                                            {{ $season->name }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            Season {{ $season->season_number }} · {{ $season->slug }}
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                        {{ $season->start_date?->format('d M Y') ?? 'Not set' }}
                                        <span class="text-slate-400">—</span>
                                        {{ $season->end_date?->format('d M Y') ?? 'Not set' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @php
                                            $statusClasses = [
                                                'upcoming' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                                'active' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                'completed' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
                                            ];
                                            $statusClass = $statusClasses[$season->status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                        @endphp

                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset {{ $statusClass }}">
                                            {{ $season->status }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                        <a href="{{ route('admin.seasons.show', $season) }}"
                                           class="mr-3 font-semibold text-slate-700 hover:text-red-700">
                                            View
                                        </a>

                                        <a href="{{ route('admin.seasons.edit', $season) }}"
                                           class="font-semibold text-red-700 hover:text-red-900">
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.seasons.destroy', $season) }}"
                                            method="POST"
                                            class="ml-3 inline"
                                            onsubmit="return confirm('Are you sure you want to delete this season?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="font-semibold text-red-700 hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $seasons->withQueryString()->links() }}
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-2xl text-red-700">
                        🏏
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">
                        No seasons found
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        No seasons match your filters. Create a season to get started.
                    </p>
                    <a href="{{ route('admin.seasons.create') }}"
                       class="mt-5 inline-flex rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800">
                        Add your first season
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
