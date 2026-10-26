@extends('layouts.admin')

@section('title', $season->name . ' | NPL Season Hub')

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('admin.seasons.index') }}"
       class="text-sm font-semibold text-slate-500 hover:text-red-600">
        ← Back to seasons
    </a>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 md:p-10">
        <p class="text-sm font-bold uppercase tracking-widest text-red-600">
            Season {{ $season->season_number }}
        </p>

        <h1 class="editorial-title mt-3 text-4xl font-black md:text-5xl">
            {{ $season->name }}
        </h1>

        <div class="mt-6">
            <span class="rounded-full bg-slate-100 px-3 py-2 text-sm font-semibold">
                {{ ucfirst($season->status) }}
            </span>
        </div>

        <dl class="mt-8 grid gap-6 border-t border-slate-200 pt-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm text-slate-500">Start date</dt>
                <dd class="mt-1 font-semibold">
                    {{ $season->start_date?->format('d F Y') ?? 'Not set' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-slate-500">End date</dt>
                <dd class="mt-1 font-semibold">
                    {{ $season->end_date?->format('d F Y') ?? 'Not set' }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm text-slate-500">URL slug</dt>
                <dd class="mt-1 break-all font-semibold">{{ $season->slug }}</dd>
            </div>
        </dl>

        @if($season->description)
            <div class="mt-8 border-t border-slate-200 pt-6">
                <h2 class="text-lg font-bold">Description</h2>
                <p class="mt-3 whitespace-pre-line leading-7 text-slate-700">{{ $season->description }}</p>
            </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('admin.seasons.edit', $season) }}"
               class="inline-flex rounded-lg bg-red-600 px-5 py-3 font-bold text-white hover:bg-red-700">
                Edit season
            </a>
        </div>
    </div>
</div>
@endsection