
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
            Enter or update match scorecards without recording every ball.
        </p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-4">Match</th>
                    <th class="px-5 py-4">Season</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($fixtures as $fixture)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-900">
                                Match #{{ $fixture->match_number }}
                            </p>
                            <p class="mt-1 text-slate-500">
                                {{ $fixture->homeTeam->name }}
                                <span class="text-slate-400">vs</span>
                                {{ $fixture->awayTeam->name }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            {{ $fixture->season->name ?? 'Season' }}
                        </td>
                        <td class="px-5 py-4">
                            @if (in_array($fixture->id, $savedFixtures))
                                <span class="font-semibold text-emerald-700">Scorecard saved</span>
                            @else
                                <span class="text-slate-500">Not entered</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.scorecards.edit', $fixture) }}"
                               class="inline-flex rounded-xl bg-red-700 px-4 py-2.5 font-semibold text-white hover:bg-red-800">
                                Enter / Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                            No fixtures found. Create a fixture first.
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
