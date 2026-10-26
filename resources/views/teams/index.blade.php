
@extends('layouts.app')

@section('title', 'NPL Teams | NPL Season Hub')

@section('content')
<div class="min-h-screen bg-white">
    <section class="border-b border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-700">Nepal Premier League</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">NPL Teams</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                Explore team profiles and their season participation.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if ($teams->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($teams as $team)
                    <a href="{{ route('teams.show', $team) }}"
                        class="group flex gap-4 rounded-xl border border-slate-200 bg-white p-5 transition hover:-translate-y-1 hover:border-red-200 hover:shadow-lg">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-50">
                            @if ($team->logo)
                                <img src="{{ asset('storage/'.$team->logo) }}" alt="{{ $team->name }} logo" loading="lazy" class="h-full w-full object-contain p-2">
                            @else
                                <span class="text-xl font-black text-red-700">{{ strtoupper(substr($team->short_name ?: $team->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-bold leading-snug text-slate-950 group-hover:text-red-700">{{ $team->name }}</h2>
                            @if ($team->short_name)
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $team->short_name }}</p>
                            @endif
                            <p class="mt-3 text-sm text-slate-600">{{ $team->home_venue ?: 'Venue not specified' }}</p>
                            <p class="mt-3 text-sm font-semibold text-red-700">View team profile →</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $teams->links() }}</div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 px-5 py-16 text-center">
                <h2 class="text-lg font-bold text-slate-900">Team profiles coming soon</h2>
                <p class="mt-2 text-sm text-slate-500">There are no active teams published yet.</p>
            </div>
        @endif
    </section>
</div>
@endsection
