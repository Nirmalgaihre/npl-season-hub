@php
    $statuses = [
        'scheduled' => ['label' => 'Scheduled', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'live' => ['label' => 'Live', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'completed' => ['label' => 'Completed', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200'],
        'abandoned' => ['label' => 'Abandoned', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200'],
        'postponed' => ['label' => 'Postponed', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200'],
    ];

    $matchTypes = [
        'league' => 'League Match',
        'qualifier' => 'Qualifier',
        'eliminator' => 'Eliminator',
        'final' => 'Final',
        'other' => 'Other',
    ];

    $selectedHome = old('home_team_id', $fixture->home_team_id);
    $selectedAway = old('away_team_id', $fixture->away_team_id);
@endphp

<div class="mx-auto max-w-5xl space-y-8 font-sans">
    {{-- Validation Errors Banner --}}
    @if ($errors->any())
        <div class="relative overflow-hidden rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="rounded-xl bg-red-500/10 p-2.5 text-red-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-red-950">Please address the following issues</h3>
                    <ul class="mt-2 grid grid-cols-1 gap-1 text-sm text-red-700 sm:grid-cols-2">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Section 1: Match Information --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs transition-all duration-200 hover:shadow-md">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 sm:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 ring-1 ring-red-500/10">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Match Details</h2>
                    <p class="text-xs font-medium text-slate-500">Configure core match setup, schedule, and competing teams</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <div class="grid gap-6 md:grid-cols-2">
                {{-- Season --}}
                <div class="space-y-1.5">
                    <label for="season_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Season <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="season_id" name="season_id" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 ease-in-out focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            <option value="">Select season</option>
                            @foreach ($seasons as $season)
                                <option value="{{ $season->id }}"
                                    @selected((string) old('season_id', $fixture->season_id) === (string) $season->id)>
                                    {{ $season->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Match Number --}}
                <div class="space-y-1.5">
                    <label for="match_number" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Match Number <span class="text-red-500">*</span>
                    </label>
                    <input id="match_number" type="number" min="1" name="match_number"
                        value="{{ old('match_number', $fixture->match_number) }}" required
                        placeholder="e.g. 12"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 ease-in-out placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                </div>

                {{-- Teams Card Container --}}
                <div class="md:col-span-2 rounded-2xl border border-slate-100 bg-slate-50/60 p-4 sm:p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="home_team_id" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700">
                                <span class="inline-block h-2 w-2 rounded-full bg-red-600"></span> Home Team <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="home_team_id" name="home_team_id" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-xs transition duration-150 focus:border-red-600 focus:outline-none focus:ring-4 focus:ring-red-600/10">
                                    <option value="">Select home team</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected((string) $selectedHome === (string) $team->id)>
                                            {{ $team->name }}{{ $team->short_name ? ' ('.$team->short_name.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="away_team_id" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700">
                                <span class="inline-block h-2 w-2 rounded-full bg-blue-600"></span> Away Team <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="away_team_id" name="away_team_id" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-xs transition duration-150 focus:border-red-600 focus:outline-none focus:ring-4 focus:ring-red-600/10">
                                    <option value="">Select away team</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected((string) $selectedAway === (string) $team->id)>
                                            {{ $team->name }}{{ $team->short_name ? ' ('.$team->short_name.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Match Type --}}
                <div class="space-y-1.5">
                    <label for="match_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Match Type <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="match_type" name="match_type" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            @foreach ($matchTypes as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('match_type', $fixture->match_type ?? 'league') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="space-y-1.5">
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Match Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="status" name="status" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            @foreach ($statuses as $value => $data)
                                <option value="{{ $value }}"
                                    @selected(old('status', $fixture->status ?? 'scheduled') === $value)>
                                    {{ $data['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Scheduled At --}}
                <div class="space-y-1.5">
                    <label for="scheduled_at" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Date and Time
                    </label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at"
                        value="{{ old('scheduled_at', $fixture->scheduled_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                </div>

                {{-- Venue --}}
                <div class="space-y-1.5">
                    <label for="venue" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Venue
                    </label>
                    <div class="relative">
                        <input id="venue" name="venue" maxlength="180"
                            value="{{ old('venue', $fixture->venue) }}"
                            placeholder="e.g. Lord's Cricket Ground"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/30 pl-10 pr-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2: Innings Scores & Outcomes --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs transition-all duration-200 hover:shadow-md">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 sm:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-500/10">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 003-3V8.25a3 3 0 00-3-3h-9a3 3 0 00-3 3v7.5a3 3 0 003 3m9 0v-11.25a3 3 0 00-3-3h-9a3 3 0 00-3 3v11.25" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Innings & Results</h2>
                    <p class="text-xs font-medium text-slate-500">Record scores, match outcomes, and performance details</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <div class="grid gap-6 md:grid-cols-2">
                {{-- Home Team Innings --}}
                <div class="rounded-2xl border border-slate-200/60 bg-gradient-to-b from-slate-50/80 to-slate-50/30 p-5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                        <h3 class="font-bold text-slate-900">Home Team Innings</h3>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div>
                            <label for="home_runs" class="mb-1 block text-xs font-bold text-slate-600">Runs</label>
                            <input id="home_runs" type="number" min="0" max="999" name="home_runs"
                                value="{{ old('home_runs', $fixture->home_runs) }}"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                        <div>
                            <label for="home_wickets" class="mb-1 block text-xs font-bold text-slate-600">Wickets</label>
                            <input id="home_wickets" type="number" min="0" max="10" name="home_wickets"
                                value="{{ old('home_wickets', $fixture->home_wickets) }}"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                        <div>
                            <label for="home_overs" class="mb-1 block text-xs font-bold text-slate-600">Overs</label>
                            <input id="home_overs" type="number" min="0" max="50" step="0.1" name="home_overs"
                                value="{{ old('home_overs', $fixture->home_overs) }}"
                                placeholder="20.0"
                                class="w-full rounded-xl border border-slate-200 bg-white text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                    </div>
                </div>

                {{-- Away Team Innings --}}
                <div class="rounded-2xl border border-slate-200/60 bg-gradient-to-b from-slate-50/80 to-slate-50/30 p-5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                        <h3 class="font-bold text-slate-900">Away Team Innings</h3>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div>
                            <label for="away_runs" class="mb-1 block text-xs font-bold text-slate-600">Runs</label>
                            <input id="away_runs" type="number" min="0" max="999" name="away_runs"
                                value="{{ old('away_runs', $fixture->away_runs) }}"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                        <div>
                            <label for="away_wickets" class="mb-1 block text-xs font-bold text-slate-600">Wickets</label>
                            <input id="away_wickets" type="number" min="0" max="10" name="away_wickets"
                                value="{{ old('away_wickets', $fixture->away_wickets) }}"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                        <div>
                            <label for="away_overs" class="mb-1 block text-xs font-bold text-slate-600">Overs</label>
                            <input id="away_overs" type="number" min="0" max="50" step="0.1" name="away_overs"
                                value="{{ old('away_overs', $fixture->away_overs) }}"
                                placeholder="20.0"
                                class="w-full rounded-xl border border-slate-200 bg-white text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                    </div>
                </div>

                {{-- Winner --}}
                <div class="space-y-1.5">
                    <label for="winner_team_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Winner
                    </label>
                    <div class="relative">
                        <select id="winner_team_id" name="winner_team_id"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            <option value="">Not set / No winner</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}"
                                    @selected((string) old('winner_team_id', $fixture->winner_team_id) === (string) $team->id)>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Player of the Match --}}
                <div class="space-y-1.5">
                    <label for="player_of_match" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Player of the Match
                    </label>
                    <div class="relative">
                        <input id="player_of_match" name="player_of_match" maxlength="150"
                            value="{{ old('player_of_match', $fixture->player_of_match) }}"
                            placeholder="e.g. Virat Kohli"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/30 pl-10 pr-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 003-3V8.25a3 3 0 00-3-3h-9a3 3 0 00-3 3v7.5a3 3 0 003 3m9 0v-11.25a3 3 0 00-3-3h-9a3 3 0 00-3 3v11.25" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Result Summary --}}
                <div class="md:col-span-2 space-y-1.5">
                    <label for="result_summary" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Result Summary
                    </label>
                    <textarea id="result_summary" name="result_summary" rows="3" maxlength="2000"
                        placeholder="e.g. India won by 6 wickets with 3 balls remaining..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/30 p-4 text-sm font-medium text-slate-800 transition duration-150 placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">{{ old('result_summary', $fixture->result_summary) }}</textarea>
                </div>

                {{-- Verification Checkbox Card --}}
                <div class="md:col-span-2">
                    <label class="group relative flex cursor-pointer items-start gap-4 rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/60 to-orange-50/30 p-4 transition duration-150 hover:bg-amber-50">
                        <div class="flex items-center pt-0.5">
                            <input type="checkbox" name="is_result_verified" value="1"
                                @checked(old('is_result_verified', $fixture->is_result_verified))
                                class="h-5 w-5 rounded-md border-amber-300 text-red-600 focus:ring-red-600 focus:ring-offset-0">
                        </div>
                        <div>
                            <span class="flex items-center gap-2 text-sm font-bold text-amber-950">
                                Verify official result
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">Important</span>
                            </span>
                            <span class="mt-0.5 block text-xs leading-relaxed text-amber-900/80">
                                Only check this after confirming the final score. Unverified scores remain hidden from public pages.
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </section>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('admin.fixtures.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-2xs transition duration-150 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-100">
            Cancel
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-red-600 to-red-700 px-7 py-3 text-sm font-bold text-white shadow-md transition duration-150 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-4 focus:ring-red-600/30 active:scale-[0.99]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            {{ $fixture->exists ? 'Save Changes' : 'Create Fixture' }}
        </button>
    </div>
</div>