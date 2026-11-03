@extends('layouts.app')

@section('title', 'Point Table Management | Admin')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    .pa-display { font-family: 'Anton', Impact, sans-serif; }
    .pa-body    { font-family: 'Archivo', system-ui, sans-serif; }
    .pa-tabular { font-variant-numeric: tabular-nums; }
</style>

<div class="pa-body min-h-screen bg-[#F5F1EB] text-[#0B1B3A]">

    <section class="bg-[#0B1B3A] text-white">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
            <p class="text-[10px] font-extrabold uppercase tracking-[0.28em] text-[#F06445]">Admin</p>
            <h1 class="pa-display mt-2 text-4xl leading-none sm:text-5xl">POINT TABLE</h1>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                @if ($seasons->count() > 1)
                    <form method="GET" action="{{ route('admin.pointtable.index') }}">
                        <select name="season" onchange="this.form.submit()"
                                class="rounded-lg border border-white/20 bg-white/10 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                            @foreach ($seasons as $s)
                                <option value="{{ $s->id }}" @selected($s->id === $selectedSeason?->id) class="text-[#0B1B3A]">
                                    {{ $s->name ?? 'Season ' . $s->id }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <span class="text-xs font-semibold text-white/60">
                    NR points: {{ $noResultPoints }}
                </span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pointtable.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="season_id" value="{{ $selectedSeason?->id }}">

            <div class="overflow-hidden rounded-2xl bg-white shadow ring-1 ring-black/5">
                <div class="hidden grid-cols-[60px_1fr_repeat(6,60px)_200px] items-center border-b-2 border-black/10 bg-[#FAF6F0] px-5 py-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-500 lg:grid">
                    <span>#</span>
                    <span>Team</span>
                    <span class="text-center">P</span>
                    <span class="text-center">W</span>
                    <span class="text-center">L</span>
                    <span class="text-center">T</span>
                    <span class="text-center">NR</span>
                    <span class="text-center">Pts</span>
                    <span>Penalty · Notes</span>
                </div>

                @foreach ($standings as $index => $row)
                    @php $team = $row['team']; $adj = $adjustments->get($team->id); @endphp
                    <div class="grid grid-cols-1 items-center gap-3 border-b border-black/5 px-5 py-4 last:border-0 lg:grid-cols-[60px_1fr_repeat(6,60px)_200px] lg:gap-0">

                        <span class="pa-tabular pa-display inline-flex h-8 w-8 items-center justify-center rounded-md bg-[#0B1B3A]/5 text-base text-[#0B1B3A]">
                            {{ $index + 1 }}
                        </span>

                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-50 p-1.5">
                                @if ($team->logo)
                                    <img src="{{ asset('storage/' . $team->logo) }}" alt="" class="h-full w-full object-contain">
                                @else
                                    <span class="text-[10px] font-black">{{ strtoupper(substr($team->short_name ?? $team->name, 0, 3)) }}</span>
                                @endif
                            </div>
                            <p class="text-sm font-extrabold">{{ $team->name }}</p>
                        </div>

                        <span class="pa-tabular hidden text-center text-sm font-bold lg:block">{{ $row['played'] }}</span>
                        <span class="pa-tabular hidden text-center text-sm font-bold text-emerald-700 lg:block">{{ $row['won'] }}</span>
                        <span class="pa-tabular hidden text-center text-sm font-bold text-rose-600 lg:block">{{ $row['lost'] }}</span>
                        <span class="pa-tabular hidden text-center text-sm font-bold lg:block">{{ $row['tied'] }}</span>
                        <span class="pa-tabular hidden text-center text-sm font-bold lg:block">{{ $row['no_result'] }}</span>
                        <span class="pa-tabular hidden text-center text-base font-black text-[#0B1B3A] lg:block">{{ $row['points'] }}</span>

                        <div class="grid grid-cols-[80px_1fr] gap-2 lg:block">
                            <input type="hidden" name="adjustments[{{ $loop->index }}][team_id]" value="{{ $team->id }}">
                            <input type="number" min="0" max="100"
                                   name="adjustments[{{ $loop->index }}][penalty_points]"
                                   value="{{ $adj?->penalty_points ?? 0 }}"
                                   class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm font-bold tabular-nums">
                            <input type="text"
                                   name="adjustments[{{ $loop->index }}][notes]"
                                   value="{{ $adj?->notes }}"
                                   placeholder="Reason"
                                   class="mt-1 w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs lg:mt-1">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-[#F06445] px-6 py-3 text-sm font-extrabold uppercase tracking-wide text-white transition hover:bg-[#0B1B3A]">
                    Save penalties
                </button>
            </div>
        </form>
    </section>
</div>
@endsection