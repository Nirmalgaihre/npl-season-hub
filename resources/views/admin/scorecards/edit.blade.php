
@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('admin.scorecards.index') }}"
       class="font-semibold text-red-700 hover:underline">
        ← Back to scorecards
    </a>

    <div class="mb-8 mt-4">
        <p class="text-sm font-bold uppercase tracking-widest text-red-700">
            Manual scorecard entry
        </p>
        <h1 class="mt-2 text-3xl font-black text-slate-900">
            Match #{{ $fixture->match_number }}
        </h1>
        <p class="mt-2 text-slate-500">
            {{ $fixture->homeTeam->name }} vs {{ $fixture->awayTeam->name }}
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="font-bold">Please check these fields:</p>
            <ul class="mt-2 list-inside list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.scorecards.update', $fixture) }}"
          class="space-y-8">
        @csrf
        @method('PUT')

        @foreach ([1, 2] as $number)
            @php
                $saved = $innings[$number];
                $old = old("innings.$number", [
                    'batting_team_id' => $saved?->batting_team_id
                        ?? ($number === 1 ? $fixture->home_team_id : $fixture->away_team_id),
                    'total_runs' => $saved?->total_runs ?? 0,
                    'wickets' => $saved?->wickets ?? 0,
                    'overs' => $saved?->overs ?? 0,
                    'extras' => $saved?->extras ?? 0,
                    'batters' => $saved?->batters ?? [],
                    'bowlers' => $saved?->bowlers ?? [],
                ]);

                $batters = array_values($old['batters'] ?? []);
                $bowlers = array_values($old['bowlers'] ?? []);

                while (count($batters) < 11) {
                    $batters[] = [
                        'name' => '',
                        'dismissal' => 'not out',
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                    ];
                }

                while (count($bowlers) < 5) {
                    $bowlers[] = [
                        'name' => '',
                        'overs' => 0,
                        'maidens' => 0,
                        'runs' => 0,
                        'wickets' => 0,
                    ];
                }
            @endphp

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="bg-slate-900 px-5 py-4 text-white">
                    <h2 class="text-xl font-black">Innings {{ $number }}</h2>
                    <p class="mt-1 text-sm text-slate-300">
                        Enter the batting and bowling figures from the official scorecard.
                    </p>
                </div>

                <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="text-sm font-semibold text-slate-700">
                        Batting team
                        <select name="innings[{{ $number }}][batting_team_id]"
                                class="mt-2 w-full rounded-xl border-slate-300">
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}"
                                    @selected((string) $old['batting_team_id'] === (string) $team->id)>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    @foreach ([
                        'total_runs' => 'Total runs',
                        'wickets' => 'Wickets',
                        'overs' => 'Overs (e.g. 19.4)',
                        'extras' => 'Extras',
                    ] as $field => $label)
                        <label class="text-sm font-semibold text-slate-700">
                            {{ $label }}
                            <input type="number"
                                   name="innings[{{ $number }}][{{ $field }}]"
                                   value="{{ $old[$field] }}"
                                   min="0"
                                   step="{{ $field === 'overs' ? '0.1' : '1' }}"
                                   required
                                   class="mt-2 w-full rounded-xl border-slate-300">
                        </label>
                    @endforeach
                </div>

                <div class="border-t border-slate-200 p-5">
                    <h3 class="mb-4 text-lg font-extrabold text-slate-900">Batting</h3>
                    <p class="mb-4 text-sm text-slate-500">
                        Leave unused rows blank. Dismissal example: c Fielder b Bowler.
                    </p>

                    <div class="space-y-4">
                        @foreach ($batters as $i => $batter)
                            <div class="grid gap-3 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-6">
                                <input name="innings[{{ $number }}][batters][{{ $i }}][name]"
                                       value="{{ $batter['name'] ?? '' }}"
                                       placeholder="Batter name"
                                       class="rounded-lg border-slate-300 lg:col-span-2">

                                <input name="innings[{{ $number }}][batters][{{ $i }}][dismissal]"
                                       value="{{ $batter['dismissal'] ?? 'not out' }}"
                                       placeholder="Dismissal / not out"
                                       class="rounded-lg border-slate-300">

                                @foreach (['runs', 'balls', 'fours', 'sixes'] as $field)
                                    <label class="text-xs font-semibold text-slate-500">
                                        {{ ucfirst($field) }}
                                        <input type="number" min="0"
                                               name="innings[{{ $number }}][batters][{{ $i }}][{{ $field }}]"
                                               value="{{ $batter[$field] ?? 0 }}"
                                               class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-slate-200 p-5">
                    <h3 class="mb-4 text-lg font-extrabold text-slate-900">Bowling</h3>

                    <div class="space-y-4">
                        @foreach ($bowlers as $i => $bowler)
                            <div class="grid gap-3 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-5">
                                <input name="innings[{{ $number }}][bowlers][{{ $i }}][name]"
                                       value="{{ $bowler['name'] ?? '' }}"
                                       placeholder="Bowler name"
                                       class="rounded-lg border-slate-300">

                                @foreach (['overs', 'maidens', 'runs', 'wickets'] as $field)
                                    <label class="text-xs font-semibold text-slate-500">
                                        {{ ucfirst($field) }}
                                        <input type="number" min="0"
                                               step="{{ $field === 'overs' ? '0.1' : '1' }}"
                                               name="innings[{{ $number }}][bowlers][{{ $i }}][{{ $field }}]"
                                               value="{{ $bowler[$field] ?? 0 }}"
                                               class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.scorecards.index') }}"
               class="rounded-xl border border-slate-300 px-5 py-3 text-center font-semibold text-slate-700">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-xl bg-red-700 px-6 py-3 font-bold text-white hover:bg-red-800">
                Save both innings
            </button>
        </div>
    </form>
</div>
@endsection
