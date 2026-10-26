@extends('layouts.app')

@section('title', $team->name . ' | NPL Season Hub')

@section('content')
@php
    $teamFixtures = $fixtures ?? collect();

    $upcomingFixtures = $teamFixtures
        ->where('status', 'scheduled')
        ->sortBy(fn ($match) => $match->scheduled_at?->timestamp ?? PHP_INT_MAX);

    $liveFixtures = $teamFixtures->where('status', 'live');

    $completedFixtures = $teamFixtures
        ->where('status', 'completed')
        ->sortByDesc(fn ($match) => $match->scheduled_at?->timestamp ?? 0);

    $statusStyles = [
        'live' => 'bg-red-50 text-red-700',
        'scheduled' => 'bg-blue-50 text-blue-700',
        'completed' => 'bg-emerald-50 text-emerald-700',
        'postponed' => 'bg-orange-50 text-orange-700',
        'abandoned' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<div class="min-h-screen bg-[#F6F8FB] text-[#202766]">

    {{-- Page heading --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-5 py-7 sm:px-8 lg:px-10">

            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-[#202766]">Home</a>
                <span>/</span>
                <a href="{{ route('teams.index') }}" class="hover:text-[#202766]">Teams</a>
                <span>/</span>
                <span class="font-semibold text-[#202766]">{{ $team->name }}</span>
            </nav>

            <div class="mt-7 grid gap-6 md:grid-cols-[150px_1fr] md:items-center">

                {{-- Team logo --}}
                <div class="flex h-32 w-32 items-center justify-center border border-slate-200 bg-[#F6F8FB] p-3 sm:h-36 sm:w-36">
                    @if ($team->logo)
                        <img
                            src="{{ asset('storage/' . $team->logo) }}"
                            alt="{{ $team->name }} logo"
                            class="h-full w-full object-contain"
                        >
                    @else
                        <span class="text-3xl font-extrabold text-[#202766]">
                            {{ strtoupper(substr($team->short_name ?: $team->name, 0, 3)) }}
                        </span>
                    @endif
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#F06445]">
                        NPL Team Profile
                    </p>

                    <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-[#202766] sm:text-4xl">
                        {{ $team->name }}
                    </h1>

                    @if ($team->short_name)
                        <p class="mt-2 text-sm font-bold uppercase tracking-wider text-slate-500">
                            {{ $team->short_name }}
                        </p>
                    @endif

                    @if ($team->home_venue)
                        <p class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                            <span class="font-bold text-[#202766]">Home venue:</span>
                            {{ $team->home_venue }}
                        </p>
                    @endif

                    @if ($team->website_url)
                        <a
                            href="{{ $team->website_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-[#202766] hover:text-[#F06445]"
                        >
                            Official website <span aria-hidden="true">↗</span>
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <main class="mx-auto max-w-7xl px-5 py-9 sm:px-8 lg:px-10">

        {{-- Team summary --}}
        <section class="grid gap-5 sm:grid-cols-3">

            <div class="border border-slate-200 bg-white p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Total fixtures
                </p>
                <p class="mt-3 text-3xl font-extrabold text-[#202766]">
                    {{ $teamFixtures->count() }}
                </p>
                <p class="mt-2 text-sm text-slate-500">Across assigned seasons</p>
            </div>

            <div class="border border-slate-200 bg-white p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Upcoming matches
                </p>
                <p class="mt-3 text-3xl font-extrabold text-[#202766]">
                    {{ $upcomingFixtures->count() }}
                </p>
                <p class="mt-2 text-sm text-slate-500">Scheduled fixtures</p>
            </div>

            <div class="border border-slate-200 bg-white p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Completed matches
                </p>
                <p class="mt-3 text-3xl font-extrabold text-[#202766]">
                    {{ $completedFixtures->count() }}
                </p>
                <p class="mt-2 text-sm text-slate-500">Matches marked completed</p>
            </div>

        </section>

        {{-- Live matches --}}
        @if ($liveFixtures->isNotEmpty())
            <section class="mt-10">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-3 w-3 animate-pulse rounded-full bg-red-600"></span>
                    <h2 class="text-2xl font-extrabold text-[#202766]">Live matches</h2>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ($liveFixtures as $match)
                        @include('teams.partials.match-card', ['match' => $match, 'team' => $team])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Upcoming fixtures --}}
        <section class="mt-10">
            <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#F06445]">
                        What's next?
                    </p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#202766]">
                        Upcoming matches
                    </h2>
                </div>

                <a href="{{ route('fixtures.index') }}"
                   class="text-sm font-bold text-[#202766] hover:text-[#F06445]">
                    All fixtures →
                </a>
            </div>

            @if ($upcomingFixtures->isNotEmpty())
                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ($upcomingFixtures as $match)
                        @include('teams.partials.match-card', ['match' => $match, 'team' => $team])
                    @endforeach
                </div>
            @else
                <div class="border border-slate-200 bg-white px-6 py-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center bg-[#F6F8FB] text-xl text-[#202766]">
                        ◷
                    </div>
                    <h3 class="mt-4 font-bold text-[#202766]">No upcoming matches</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        There are no scheduled fixtures for this team at the moment.
                    </p>
                </div>
            @endif
        </section>

        {{-- Completed matches --}}
        <section class="mt-10">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#F06445]">
                    Previous games
                </p>
                <h2 class="mt-2 text-2xl font-extrabold text-[#202766]">
                    Recent results
                </h2>
            </div>

            @if ($completedFixtures->isNotEmpty())
                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ($completedFixtures as $match)
                        @include('teams.partials.match-card', ['match' => $match, 'team' => $team])
                    @endforeach
                </div>
            @else
                <div class="border border-slate-200 bg-white px-6 py-10 text-center">
                    <h3 class="font-bold text-[#202766]">No completed matches yet</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Official results will appear here after matches are completed.
                    </p>
                </div>
            @endif
        </section>

        {{-- About the team --}}
        @if ($team->description)
            <section class="mt-10 border border-slate-200 bg-white p-6 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#F06445]">
                    Team overview
                </p>
                <h2 class="mt-2 text-2xl font-extrabold text-[#202766]">
                    About {{ $team->name }}
                </h2>
                <p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">
                    {{ $team->description }}
                </p>
            </section>
        @endif

        {{-- Seasons --}}
        <section class="mt-10">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#F06445]">
                    Competition history
                </p>
                <h2 class="mt-2 text-2xl font-extrabold text-[#202766]">
                    Participating seasons
                </h2>
            </div>

            @if ($team->seasons->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($team->seasons as $season)
                        <a href="{{ route('seasons.show', $season) }}"
                           class="group border border-slate-200 bg-white p-5 transition hover:border-[#202766]">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#F06445]">
                                Season {{ $season->season_number }}
                            </p>
                            <h3 class="mt-2 font-extrabold text-[#202766] group-hover:text-[#F06445]">
                                {{ $season->name }}
                            </h3>
                            <span class="mt-4 inline-block text-sm font-bold text-[#202766]">
                                View season →
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="border border-slate-200 bg-white p-6 text-sm text-slate-500">
                    No seasons have been assigned to this team yet.
                </div>
            @endif
        </section>

        <div class="mt-9 border-t border-slate-200 pt-6">
            <a href="{{ route('teams.index') }}"
               class="inline-flex items-center justify-center gap-2 bg-[#202766] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#171d50]">
                ← All teams
            </a>
        </div>

    </main>
</div>
@endsection