@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-red-700">NPL Management</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Fixtures & Results</h1>
            <p class="mt-2 text-slate-500">Manage match schedules and publish verified results.</p>
        </div>
        <a href="{{ route('admin.fixtures.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800">
            + Create fixture
        </a>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['All fixtures', $stats['total']],
            ['Scheduled', $stats['scheduled']],
            ['Live', $stats['live']],
            ['Completed', $stats['completed']],
        ] as [$label, $count])
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $count }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.fixtures.index') }}"
          class="mt-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4">
        <input type="search" name="search" value="{{ request('search') }}"
               placeholder="Search teams or venue"
               class="rounded-xl border-slate-300 md:col-span-2">

        <select name="season_id" class="rounded-xl border-slate-300">
            <option value="">All seasons</option>
            @foreach ($seasons as $season)
                <option value="{{ $season->id }}" @selected((string) request('season_id') === (string) $season->id)>
                    {{ $season->name }}
                </option>
            @endforeach
        </select>

        <select name="status" class="rounded-xl border-slate-300">
            <option value="">All statuses</option>
            @foreach (['scheduled', 'live', 'completed', 'abandoned', 'postponed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>

        <div class="flex gap-2 md:col-span-4">
            <button class="rounded-xl bg-slate-900 px-5 py-2.5 font-semibold text-white">Apply filters</button>
            <a href="{{ route('admin.fixtures.index') }}"
               class="rounded-xl border border-slate-300 px-5 py-2.5 font-semibold text-slate-700">
                Reset
            </a>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Match</th>
                        <th class="px-5 py-4">Season</th>
                        <th class="px-5 py-4">Schedule</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Result verification</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($fixtures as $fixture)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900">
                                    #{{ $fixture->match_number }} ·
                                    {{ $fixture->homeTeam->short_name ?: $fixture->homeTeam->name }}
                                    <span class="text-slate-400">vs</span>
                                    {{ $fixture->awayTeam->short_name ?: $fixture->awayTeam->name }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ $fixture->venue ?: 'Venue not set' }}</p>

                                @if ($fixture->is_result_verified)
                                    <p class="mt-2 font-semibold text-slate-700">
                                        {{ $fixture->home_runs }}/{{ $fixture->home_wickets }}
                                        ({{ $fixture->home_overs }} ov)
                                        <span class="text-slate-400">—</span>
                                        {{ $fixture->away_runs }}/{{ $fixture->away_wickets }}
                                        ({{ $fixture->away_overs }} ov)
                                    </p>
                                @elseif ($fixture->status === 'completed')
                                    <p class="mt-2 text-xs font-semibold text-amber-700">Score not verified</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $fixture->season->name }}</td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $fixture->scheduled_at?->format('d M Y, h:i A') ?? 'Not scheduled' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                                    {{ $fixture->status === 'live' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $fixture->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $fixture->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ in_array($fixture->status, ['abandoned', 'postponed']) ? 'bg-amber-100 text-amber-800' : '' }}">
                                    {{ ucfirst($fixture->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($fixture->is_result_verified)
                                    <span class="font-semibold text-emerald-700">Verified</span>
                                @else
                                    <span class="font-semibold text-amber-700">Unverified</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('fixtures.show', $fixture) }}"
                                       class="font-semibold text-slate-600 hover:text-red-700">View</a>
                                    <a href="{{ route('admin.fixtures.edit', $fixture) }}"
                                       class="font-semibold text-red-700 hover:text-red-900">Edit</a>
                                    <form method="POST" action="{{ route('admin.fixtures.destroy', $fixture) }}"
                                          onsubmit="return confirm('Delete this fixture? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="font-bold text-slate-900">No fixtures found</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Create a fixture or adjust your filters. No placeholder matches are inserted.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-5 py-4">
            {{ $fixtures->links() }}
        </div>
    </div>
</div>
@endsection