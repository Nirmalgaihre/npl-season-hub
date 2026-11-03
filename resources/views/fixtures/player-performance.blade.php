
@extends('layouts.app')

@section('title', ($playerName ?? 'Player') . ' | NPL Player Profile')

@section('meta_description', 'Player profile, batting statistics, bowling statistics and match history on NPL Season Hub.')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $playerName = trim($playerName ?? 'Player');
    $performances = collect($performances ?? []);
    $battingStats = $battingStats ?? [];
    $bowlingStats = $bowlingStats ?? [];

    /*
    |--------------------------------------------------------------------------
    | Find the registered player and their team
    |--------------------------------------------------------------------------
    */
    $registeredPlayer = $player ?? DB::table('players')
        ->whereRaw('LOWER(name) = ?', [mb_strtolower($playerName)])
        ->first();

    $registeredTeam = null;
    $registeredTeamId = data_get($registeredPlayer, 'team_id');

    if ($registeredTeamId) {
        $registeredTeam = DB::table('teams')
            ->where('id', $registeredTeamId)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve logos stored in storage/app/public/teams
    |--------------------------------------------------------------------------
    */
    $imageUrl = function ($path, $folder = null) {
        if (!$path || !is_string($path)) {
            return null;
        }

        $path = trim($path);

        if ($path === '' || $path === '—') {
            return null;
        }

        if (Str::startsWith($path, ['https://', 'http://', 'data:', '/'])) {
            return $path;
        }

        $path = preg_replace('#^storage/#', '', $path);

        if ($folder && !Str::startsWith($path, $folder . '/')) {
            $path = $folder . '/' . ltrim($path, '/');
        }

        return Storage::disk('public')->url($path);
    };

    $teamLogo = $registeredTeam
        ? $imageUrl(data_get($registeredTeam, 'logo') ?: data_get($registeredTeam, 'logo_path'), 'teams')
        : null;

    $playerPhotoPath = data_get($registeredPlayer, 'photo_path')
        ?: data_get($registeredPlayer, 'photo')
        ?: data_get($registeredPlayer, 'image');

    $playerPhoto = $imageUrl($playerPhotoPath);

    $playerRole = data_get($registeredPlayer, 'role')
        ?: data_get($registeredPlayer, 'playing_role')
        ?: 'Cricketer';

    $teamName = data_get($registeredTeam, 'name')
        ?: data_get($registeredPlayer, 'team.name')
        ?: 'Team not listed';

    $teamShortName = data_get($registeredTeam, 'short_name')
        ?: $teamName;

    $initials = collect(preg_split('/\s+/', $playerName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');

    $value = function ($data, $key, $default = 0) {
        $result = data_get($data, $key);

        return $result === null || $result === '' ? $default : $result;
    };

    $number = function ($value, $decimals = 2) {
        if ($value === null || $value === '' || $value === '—') {
            return '—';
        }

        return is_numeric($value)
            ? number_format((float) $value, $decimals)
            : $value;
    };

    $date = function ($date) {
        if (!$date) {
            return 'Date TBA';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d M Y');
        } catch (\Throwable $e) {
            return 'Date TBA';
        }
    };

    $batting = [
        ['Runs', $value($battingStats, 'runs'), 'Total runs'],
        ['Highest score', $value($battingStats, 'highest_score'), 'Best innings'],
        ['Average', $number($value($battingStats, 'average', '—')), 'Runs per dismissal'],
        ['Strike rate', $number($value($battingStats, 'strike_rate', '—')), 'Runs per 100 balls'],
        ['Fours', $value($battingStats, 'fours'), 'Boundaries'],
        ['Sixes', $value($battingStats, 'sixes'), 'Maximums'],
        ['Innings', $value($battingStats, 'innings'), 'Batting innings'],
        ['Not outs', $value($battingStats, 'not_outs'), 'Not dismissed'],
    ];

    $bowling = [
        ['Wickets', $value($bowlingStats, 'wickets'), 'Wickets taken'],
        ['Economy', $number($value($bowlingStats, 'economy', '—')), 'Runs per over'],
        ['Overs', $value($bowlingStats, 'overs'), 'Overs bowled'],
        ['Runs conceded', $value($bowlingStats, 'runs'), 'Bowling runs'],
        ['Maidens', $value($bowlingStats, 'maidens'), 'Maiden overs'],
        ['Innings', $value($bowlingStats, 'innings'), 'Bowling innings'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Build match-history entries using actual scorecard teams
    |--------------------------------------------------------------------------
    */
    $matchRows = $performances->map(function ($performance) use (
        $registeredTeamId,
        $imageUrl,
        $date,
        $value
    ) {
        $fixture = data_get($performance, 'fixture');
        $scorecard = data_get($performance, 'scorecard');

        $batter = data_get($performance, 'batter');
        $bowler = data_get($performance, 'bowler');

        $battingTeam = data_get($performance, 'batting_team');
        $bowlingTeam = data_get($performance, 'bowling_team');

        $battingTeamId = data_get($scorecard, 'batting_team_id')
            ?? data_get($performance, 'batting_team_id');

        $bowlingTeamId = data_get($scorecard, 'bowling_team_id')
            ?? data_get($performance, 'bowling_team_id');

        /*
         * Prefer the registered player's team to determine the opponent.
         * Fall back to the innings role when the team ID is unavailable.
         */
        if ($registeredTeamId && (string) $registeredTeamId === (string) $battingTeamId) {
            $myTeam = $battingTeam;
            $opponent = $bowlingTeam;
        } elseif ($registeredTeamId && (string) $registeredTeamId === (string) $bowlingTeamId) {
            $myTeam = $bowlingTeam;
            $opponent = $battingTeam;
        } elseif ($batter) {
            $myTeam = $battingTeam;
            $opponent = $bowlingTeam;
        } else {
            $myTeam = $bowlingTeam;
            $opponent = $battingTeam;
        }

        $opponentName = data_get($opponent, 'name')
            ?: data_get($opponent, 'short_name')
            ?: 'Opponent unavailable';

        $myTeamName = data_get($myTeam, 'name')
            ?: data_get($myTeam, 'short_name')
            ?: 'Team unavailable';

        $opponentLogo = $imageUrl(
            data_get($opponent, 'logo') ?: data_get($opponent, 'logo_path'),
            'teams'
        );

        $myTeamLogo = $imageUrl(
            data_get($myTeam, 'logo') ?: data_get($myTeam, 'logo_path'),
            'teams'
        );

        $matchId = data_get($fixture, 'id');
        $matchNumber = data_get($fixture, 'match_number');

        $dismissal = data_get($batter, 'dismissal');

        return [
            'fixture' => $fixture,
            'fixture_id' => $matchId,
            'match_number' => $matchNumber,
            'date' => $date(data_get($fixture, 'scheduled_at')),
            'my_team' => $myTeamName,
            'my_team_logo' => $myTeamLogo,
            'opponent' => $opponentName,
            'opponent_logo' => $opponentLogo,
            'runs' => $value($batter, 'runs'),
            'balls' => $value($batter, 'balls'),
            'fours' => $value($batter, 'fours'),
            'sixes' => $value($batter, 'sixes'),
            'wickets' => $value($bowler, 'wickets'),
            'bowling_runs' => $value($bowler, 'runs'),
            'overs' => $value($bowler, 'overs'),
            'economy' => $value($bowler, 'economy', '—'),
            'dismissal' => $dismissal,
        ];
    });

    $hasPerformanceData = $matchRows->isNotEmpty();
@endphp

<div class="min-h-screen bg-slate-50 pb-14 text-slate-900">

    {{-- Top navigation --}}
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 transition hover:text-rose-600">
                    <span class="text-lg">←</span>
                    Back
                </a>

                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-slate-600">
                    NPL Season Hub
                </span>
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">

        {{-- Player profile hero --}}
        <section class="relative overflow-hidden rounded-3xl bg-[#101b32] shadow-xl">
            <div class="absolute -right-16 -top-24 h-72 w-72 rounded-full border-[35px] border-white/5"></div>
            <div class="absolute -bottom-28 right-1/3 h-64 w-64 rounded-full bg-rose-600/10 blur-3xl"></div>

            <div class="relative grid md:grid-cols-[1fr_300px]">

                <div class="flex min-w-0 flex-col justify-between p-6 sm:p-9 lg:p-11">

                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-rose-600 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-white">
                            Player profile
                        </span>

                        <span class="text-xs font-semibold text-slate-400">
                            Nepal Premier League
                        </span>
                    </div>

                    <div class="py-9">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-400">
                            {{ $teamName }}
                        </p>

                        <h1 class="mt-3 break-words text-3xl font-black tracking-tight text-white sm:text-5xl">
                            {{ $playerName }}
                        </h1>

                        <span class="mt-5 inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-sm font-bold text-white">
                            <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                            {{ $playerRole }}
                        </span>

                        <p class="mt-5 max-w-xl text-sm leading-6 text-slate-300">
                            Player statistics, match-by-match performance and team information.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-white/10 pt-5">

                        {{-- Actual registered team logo --}}
                        @if ($teamLogo)
                            <img
                                src="{{ $teamLogo }}"
                                alt="{{ $teamName }} logo"
                                class="h-12 w-12 rounded-xl border border-white/15 bg-white p-1.5 object-contain"
                                onerror="this.style.display='none'"
                            >
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-sm font-black text-white">
                                {{ Str::upper(Str::substr($teamShortName, 0, 3)) }}
                            </div>
                        @endif

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                Registered team
                            </p>
                            <p class="mt-1 font-extrabold text-white">
                                {{ $teamName }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Player portrait --}}
                <div class="relative flex min-h-[270px] items-center justify-center overflow-hidden bg-gradient-to-br from-slate-200 to-slate-400 md:min-h-[390px]">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(255,255,255,0.8),_transparent_70%)]"></div>

                    @if ($playerPhoto)
                        <img
                            src="{{ $playerPhoto }}"
                            alt="{{ $playerName }}"
                            class="relative z-10 h-full max-h-[420px] min-h-[270px] w-full object-contain object-bottom"
                            onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';"
                        >
                    @endif

                    <div
                        class="relative z-10 {{ $playerPhoto ? 'hidden' : 'flex' }} h-36 w-36 items-center justify-center rounded-full border-8 border-white/70 bg-[#101b32] text-5xl font-black text-white shadow-2xl"
                    >
                        {{ $initials ?: 'P' }}
                    </div>

                    @if ($teamLogo)
                        <div class="absolute bottom-4 right-4 z-20 rounded-2xl border border-slate-200 bg-white p-2 shadow-lg">
                            <img
                                src="{{ $teamLogo }}"
                                alt="{{ $teamName }} logo"
                                class="h-12 w-12 object-contain sm:h-14 sm:w-14"
                                onerror="this.style.display='none'"
                            >
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Highlight stats --}}
        <section class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            @foreach ([
                ['Total runs', $value($battingStats, 'runs'), 'Batting'],
                ['Highest score', $value($battingStats, 'highest_score'), 'Best innings'],
                ['Wickets', $value($bowlingStats, 'wickets'), 'Bowling'],
                ['Strike rate', $number($value($battingStats, 'strike_rate', '—')), 'Runs per 100 balls'],
            ] as $stat)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                        {{ $stat[0] }}
                    </p>
                    <p class="mt-3 break-words text-3xl font-black tracking-tight text-[#101b32]">
                        {{ $stat[1] }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">{{ $stat[2] }}</p>
                </div>
            @endforeach
        </section>

        {{-- Batting --}}
        <section class="mt-10">
            <div class="mb-5 flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-rose-600">01 / Batting</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Batting statistics</h2>
                    <p class="mt-1 text-sm text-slate-500">Recorded batting performance.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                @foreach ($batting as $stat)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                        <p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p>
                        <p class="mt-3 break-words text-2xl font-black text-slate-900">{{ $stat[1] }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-400">{{ $stat[2] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Bowling --}}
        <section class="mt-10">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-rose-600">02 / Bowling</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Bowling statistics</h2>
                <p class="mt-1 text-sm text-slate-500">Recorded bowling performance.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                @foreach ($bowling as $stat)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                        <p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p>
                        <p class="mt-3 break-words text-2xl font-black text-slate-900">{{ $stat[1] }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-400">{{ $stat[2] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Match history --}}
        <section class="mt-10">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-rose-600">03 / Match centre</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Match history</h2>
                    <p class="mt-1 text-sm text-slate-500">The teams involved and the player's recorded performance.</p>
                </div>

                <span class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-slate-700">
                    {{ $matchRows->count() }} {{ Str::plural('record', $matchRows->count()) }}
                </span>
            </div>

            @if ($matchRows->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($matchRows as $match)
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">

                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3 sm:px-5">
                                <div>
                                    <p class="text-sm font-extrabold text-slate-900">
                                        Match {{ $match['match_number'] ?? $match['fixture_id'] ?? '—' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $match['date'] }}</p>
                                </div>

                                @if ($match['fixture_id'] && \Illuminate\Support\Facades\Route::has('fixtures.show'))
                                    <a
                                        href="{{ route('fixtures.show', $match['fixture_id']) }}"
                                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600"
                                    >
                                        View match <span>→</span>
                                    </a>
                                @endif
                            </div>

                            <div class="grid gap-4 p-4 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:p-5">

                                {{-- Player's team --}}
                                <div class="flex min-w-0 items-center gap-3 rounded-xl bg-slate-50 p-3">
                                    @if ($match['my_team_logo'])
                                        <img
                                            src="{{ $match['my_team_logo'] }}"
                                            alt="{{ $match['my_team'] }} logo"
                                            class="h-12 w-12 shrink-0 rounded-xl border border-slate-200 bg-white p-1 object-contain"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#101b32] text-xs font-black text-white">
                                            {{ Str::upper(Str::substr($match['my_team'], 0, 3)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Player's team</p>
                                        <p class="mt-1 break-words text-sm font-extrabold text-slate-900">
                                            {{ $match['my_team'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center">
                                    <span class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-black tracking-widest text-slate-400">
                                        VS
                                    </span>
                                </div>

                                {{-- Opponent --}}
                                <div class="flex min-w-0 items-center gap-3 rounded-xl border border-rose-100 bg-rose-50/50 p-3">
                                    @if ($match['opponent_logo'])
                                        <img
                                            src="{{ $match['opponent_logo'] }}"
                                            alt="{{ $match['opponent'] }} logo"
                                            class="h-12 w-12 shrink-0 rounded-xl border border-slate-200 bg-white p-1 object-contain"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-xs font-black text-rose-600 ring-1 ring-rose-100">
                                            {{ Str::upper(Str::substr($match['opponent'], 0, 3)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-rose-500">Opponent · VS</p>
                                        <p class="mt-1 break-words text-sm font-extrabold text-slate-900">
                                            {{ $match['opponent'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-px border-t border-slate-100 bg-slate-100 sm:grid-cols-4">
                                <div class="bg-white p-4">
                                    <p class="text-xs font-semibold text-slate-500">Batting</p>
                                    <p class="mt-2 text-xl font-black text-slate-900">
                                        {{ $match['runs'] }}
                                        <span class="text-sm font-semibold text-slate-400">({{ $match['balls'] }})</span>
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $match['fours'] }} fours · {{ $match['sixes'] }} sixes
                                    </p>
                                </div>

                                <div class="bg-white p-4">
                                    <p class="text-xs font-semibold text-slate-500">Bowling</p>
                                    <p class="mt-2 text-xl font-black text-slate-900">
                                        {{ $match['wickets'] }}/{{ $match['bowling_runs'] }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $match['overs'] }} overs
                                    </p>
                                </div>

                                <div class="bg-white p-4">
                                    <p class="text-xs font-semibold text-slate-500">Economy</p>
                                    <p class="mt-2 text-xl font-black text-slate-900">
                                        {{ $number($match['economy']) }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">Runs per over</p>
                                </div>

                                <div class="bg-white p-4">
                                    <p class="text-xs font-semibold text-slate-500">Dismissal</p>
                                    <p class="mt-2 break-words text-sm font-bold text-slate-800">
                                        {{ $match['dismissal'] ?: '—' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">Batting record</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-500">
                        🏏
                    </div>

                    <h3 class="mt-4 text-lg font-extrabold text-slate-900">
                        No match records yet
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        This player's match history will appear here when a matching batting or bowling entry is saved in the scorecards.
                    </p>

                    @if ($teamLogo)
                        <div class="mt-5 flex justify-center">
                            <img
                                src="{{ $teamLogo }}"
                                alt="{{ $teamName }} logo"
                                class="h-16 w-16 rounded-xl border border-slate-200 bg-white p-2 object-contain"
                            >
                        </div>
                        <p class="mt-2 text-sm font-bold text-slate-600">{{ $teamName }}</p>
                    @endif
                </div>
            @endif
        </section>

        {{-- Footer --}}
        <div class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
            <

            <a
                href="{{ route('teams.index') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-[#101b32] px-4 py-3 text-sm font-bold text-white transition hover:bg-rose-600"
            >
                ← All teams
            </a>
        </div>

    </main>
</div>
@endsection
