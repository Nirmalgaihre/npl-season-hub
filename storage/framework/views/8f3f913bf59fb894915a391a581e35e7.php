<?php $__env->startSection('title', ($playerName ?? 'Player') . ' | NPL Player Profile'); ?>

<?php $__env->startSection('meta_description', 'Explore NPL player profiles, batting and bowling statistics, and match-by-match performance on NPL Season Hub.'); ?>

<?php $__env->startSection('content'); ?>
<?php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $playerName = trim($playerName ?? 'Player');
    $performances = collect($performances ?? []);
    $battingStats = $battingStats ?? [];
    $bowlingStats = $bowlingStats ?? [];

    $registeredPlayer = $player ?? DB::table('players')
        ->whereRaw('LOWER(name) = ?', [mb_strtolower($playerName)])
        ->first();

    $registeredTeamId = data_get($registeredPlayer, 'team_id');
    $registeredTeam = $registeredTeamId
        ? DB::table('teams')->where('id', $registeredTeamId)->first()
        : null;

    $imageUrl = function ($path, $folder = null) {
        if (!is_string($path) || trim($path) === '' || trim($path) === '—') {
            return null;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['https://', 'http://', 'data:', '/'])) {
            return $path;
        }

        $path = preg_replace('#^storage/#', '', $path);

        if ($folder && !Str::startsWith($path, $folder . '/')) {
            $path = $folder . '/' . ltrim($path, '/');
        }

        return Storage::disk('public')->url($path);
    };

    $teamName = data_get($registeredTeam, 'name')
        ?: data_get($registeredPlayer, 'team.name')
        ?: 'Team not listed';

    $teamShortName = data_get($registeredTeam, 'short_name') ?: $teamName;

    $teamLogo = $registeredTeam
        ? $imageUrl(data_get($registeredTeam, 'logo') ?: data_get($registeredTeam, 'logo_path'), 'teams')
        : null;

    $playerPhoto = $imageUrl(
        data_get($registeredPlayer, 'photo_path')
        ?: data_get($registeredPlayer, 'photo')
        ?: data_get($registeredPlayer, 'image')
    );

    $playerRole = data_get($registeredPlayer, 'role')
        ?: data_get($registeredPlayer, 'playing_role')
        ?: 'Cricketer';

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
        ['Runs', $value($battingStats, 'runs'), 'Total runs scored', 'activity'],
        ['Highest score', $value($battingStats, 'highest_score'), 'Best individual innings', 'trophy'],
        ['Average', $number($value($battingStats, 'average', '—')), 'Runs per dismissal', 'chart'],
        ['Strike rate', $number($value($battingStats, 'strike_rate', '—')), 'Runs per 100 balls', 'gauge'],
        ['Fours', $value($battingStats, 'fours'), 'Boundaries hit', 'target'],
        ['Sixes', $value($battingStats, 'sixes'), 'Maximums hit', 'zap'],
        ['Innings', $value($battingStats, 'innings'), 'Batting innings', 'layers'],
        ['Not outs', $value($battingStats, 'not_outs'), 'Innings not dismissed', 'shield'],
    ];

    $bowling = [
        ['Wickets', $value($bowlingStats, 'wickets'), 'Total wickets taken'],
        ['Economy', $number($value($bowlingStats, 'economy', '—')), 'Runs conceded per over'],
        ['Overs', $value($bowlingStats, 'overs'), 'Overs bowled'],
        ['Runs conceded', $value($bowlingStats, 'runs'), 'Bowling runs conceded'],
        ['Maidens', $value($bowlingStats, 'maidens'), 'Maiden overs'],
        ['Innings', $value($bowlingStats, 'innings'), 'Bowling innings'],
    ];

    $matchRows = $performances->map(function ($performance) use (
        $registeredTeamId, $imageUrl, $date, $value
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

        return [
            'fixture_id' => data_get($fixture, 'id'),
            'match_number' => data_get($fixture, 'match_number'),
            'date' => $date(data_get($fixture, 'scheduled_at')),
            'my_team' => $myTeamName,
            'my_team_logo' => $imageUrl(
                data_get($myTeam, 'logo') ?: data_get($myTeam, 'logo_path'), 'teams'
            ),
            'opponent' => $opponentName,
            'opponent_logo' => $imageUrl(
                data_get($opponent, 'logo') ?: data_get($opponent, 'logo_path'), 'teams'
            ),
            'runs' => $value($batter, 'runs'),
            'balls' => $value($batter, 'balls'),
            'fours' => $value($batter, 'fours'),
            'sixes' => $value($batter, 'sixes'),
            'wickets' => $value($bowler, 'wickets'),
            'bowling_runs' => $value($bowler, 'runs'),
            'overs' => $value($bowler, 'overs'),
            'economy' => $value($bowler, 'economy', '—'),
            'dismissal' => data_get($batter, 'dismissal'),
        ];
    });

    $matchCount = $matchRows->count();

    $highlights = [
        [
            'label' => 'Total runs',
            'number' => $value($battingStats, 'runs'),
            'note' => 'Career batting',
            'symbol' => 'R',
        ],
        [
            'label' => 'Highest score',
            'number' => $value($battingStats, 'highest_score'),
            'note' => 'Best innings',
            'symbol' => 'H',
        ],
        [
            'label' => 'Wickets',
            'number' => $value($bowlingStats, 'wickets'),
            'note' => 'Career bowling',
            'symbol' => 'W',
        ],
        [
            'label' => 'Strike rate',
            'number' => $number($value($battingStats, 'strike_rate', '—')),
            'note' => 'Runs per 100 balls',
            'symbol' => 'SR',
        ],
    ];
?>

<div class="min-h-screen overflow-hidden bg-slate-50 pb-12 text-slate-900">

    
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
            <a href="<?php echo e(url()->previous()); ?>"
               class="inline-flex min-h-10 items-center gap-2 rounded-xl px-3 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-rose-600">
                <span aria-hidden="true" class="text-lg">←</span>
                <span>Back</span>
            </a>

            <a href="<?php echo e(url('/')); ?>"
               class="flex min-w-0 items-center gap-2.5"
               aria-label="NPL Season Hub home">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-xs font-black text-white shadow-sm">
                    NPL
                </span>
                <span class="hidden text-sm font-black tracking-tight text-slate-900 sm:inline">
                    NPL Season Hub
                </span>
            </a>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 pt-5 sm:px-6 sm:pt-8 lg:px-8">

        
        <section class="relative isolate overflow-hidden rounded-3xl bg-[#101b32] shadow-xl shadow-slate-900/10">
            <div aria-hidden="true" class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full border-[35px] border-white/[0.04]"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 left-1/3 h-72 w-72 rounded-full bg-rose-600/20 blur-3xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.07]"
                 style="background-image:radial-gradient(#ffffff 0.7px,transparent 0.7px);background-size:18px 18px"></div>

            <div class="relative grid md:grid-cols-[minmax(0,1fr)_300px] lg:grid-cols-[minmax(0,1fr)_350px]">

                <div class="flex min-w-0 flex-col p-5 sm:p-8 lg:p-10">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-600 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.15em] text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                            Player profile
                        </span>
                        <span class="rounded-full border border-white/10 bg-white/[0.06] px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-300">
                            Nepal Premier League
                        </span>
                    </div>

                    <div class="py-8 sm:py-10">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-400">
                            <?php echo e($teamName); ?>

                        </p>

                        <h1 class="mt-3 max-w-3xl break-words text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">
                            <?php echo e($playerName); ?>

                        </h1>

                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.07] px-3 py-2 text-sm font-bold text-white">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-rose-600/20 text-xs text-rose-300">✦</span>
                                <?php echo e($playerRole); ?>

                            </span>
                            <span class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.07] px-3 py-2 text-xs font-semibold text-slate-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                Player statistics
                            </span>
                        </div>

                        <p class="mt-5 max-w-lg text-sm leading-6 text-slate-300">
                            Explore batting numbers, bowling figures and recorded match performances.
                        </p>
                    </div>

                    <div class="mt-auto flex min-w-0 items-center gap-3 border-t border-white/10 pt-5">
                        <?php if($teamLogo): ?>
                            <img src="<?php echo e($teamLogo); ?>"
                                 alt="<?php echo e($teamName); ?> logo"
                                 class="h-12 w-12 shrink-0 rounded-xl border border-white/15 bg-white p-1.5 object-contain"
                                 onerror="this.style.display='none'">
                        <?php else: ?>
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-xs font-black text-white">
                                <?php echo e(Str::upper(Str::substr($teamShortName, 0, 3))); ?>

                            </div>
                        <?php endif; ?>

                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">Registered team</p>
                            <p class="mt-1 break-words text-sm font-extrabold text-white"><?php echo e($teamName); ?></p>
                        </div>
                    </div>
                </div>

                
                <div class="relative flex min-h-[230px] items-center justify-center overflow-hidden bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 sm:min-h-[300px] md:min-h-[390px]">
                    <div aria-hidden="true" class="absolute inset-0"
                         style="background:radial-gradient(circle at 50% 35%,rgba(255,255,255,.9),transparent 60%)"></div>
                    <div aria-hidden="true" class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-slate-900/10 to-transparent"></div>

                    <?php if($playerPhoto): ?>
                        <img src="<?php echo e($playerPhoto); ?>"
                             alt="<?php echo e($playerName); ?>"
                             class="relative z-10 h-64 w-full object-contain object-bottom sm:h-80 md:h-full md:max-h-[440px]"
                             onerror="this.onerror=null;this.style.display='none';document.getElementById('player-fallback').classList.remove('hidden');">
                    <?php endif; ?>

                    <div id="player-fallback"
                         class="<?php echo e($playerPhoto ? 'hidden' : 'flex'); ?> relative z-10 h-32 w-32 items-center justify-center rounded-full border-8 border-white/70 bg-[#101b32] text-4xl font-black text-white shadow-2xl sm:h-40 sm:w-40 sm:text-5xl">
                        <?php echo e($initials ?: 'P'); ?>

                    </div>

                    <?php if($teamLogo): ?>
                        <div class="absolute bottom-4 right-4 z-20 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/70 bg-white p-2 shadow-lg sm:bottom-5 sm:right-5 sm:h-[76px] sm:w-[76px]">
                            <img src="<?php echo e($teamLogo); ?>"
                                 alt="<?php echo e($teamName); ?> logo"
                                 class="max-h-full max-w-full object-contain"
                                 onerror="this.parentElement.style.display='none'">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        
        <section class="mt-5 sm:mt-6">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.17em] text-rose-600">At a glance</p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900 sm:text-xl">Career highlights</h2>
                </div>
                <span class="text-xs font-medium text-slate-400">Recorded statistics</span>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                <?php $__currentLoopData = $highlights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-rose-200 hover:shadow-md sm:p-5">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold text-slate-500 sm:text-sm"><?php echo e($stat['label']); ?></p>
                            <span class="flex h-8 min-w-8 items-center justify-center rounded-lg bg-rose-50 px-1.5 text-[10px] font-black text-rose-600">
                                <?php echo e($stat['symbol']); ?>

                            </span>
                        </div>
                        <p class="mt-4 break-words text-3xl font-black tracking-tight text-[#101b32] sm:text-4xl">
                            <?php echo e($stat['number']); ?>

                        </p>
                        <p class="mt-1.5 text-[11px] font-medium text-slate-400 sm:text-xs"><?php echo e($stat['note']); ?></p>
                        <div class="absolute bottom-0 left-0 h-0.5 w-0 bg-rose-600 transition-all duration-300 group-hover:w-full"></div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section id="batting" class="mt-9 scroll-mt-24 sm:mt-12">
            <div class="mb-4 flex items-center gap-3 sm:mb-5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-lg text-rose-600">
                    <span aria-hidden="true">🏏</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-rose-600">01 / Batting</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-900 sm:text-2xl">Batting statistics</h2>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">Scoring, consistency and boundaries.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                <?php $__currentLoopData = $batting; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-rose-200 hover:shadow-md sm:p-5">
                        <p class="text-xs font-semibold text-slate-500 sm:text-sm"><?php echo e($stat[0]); ?></p>
                        <p class="mt-3 break-words text-2xl font-black tracking-tight text-slate-900 sm:text-3xl"><?php echo e($stat[1]); ?></p>
                        <p class="mt-1.5 text-[11px] leading-5 text-slate-400 sm:text-xs"><?php echo e($stat[2]); ?></p>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section id="bowling" class="mt-9 scroll-mt-24 sm:mt-12">
            <div class="mb-4 flex items-center gap-3 sm:mb-5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-lg text-[#101b32]">
                    <span aria-hidden="true">🎯</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-rose-600">02 / Bowling</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-900 sm:text-2xl">Bowling statistics</h2>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">Wickets, economy and bowling workload.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                <?php $__currentLoopData = $bowling; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-rose-200 hover:shadow-md sm:p-5">
                        <p class="text-xs font-semibold text-slate-500 sm:text-sm"><?php echo e($stat[0]); ?></p>
                        <p class="mt-3 break-words text-2xl font-black tracking-tight text-slate-900 sm:text-3xl"><?php echo e($stat[1]); ?></p>
                        <p class="mt-1.5 text-[11px] leading-5 text-slate-400 sm:text-xs"><?php echo e($stat[2]); ?></p>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section id="matches" class="mt-9 scroll-mt-24 sm:mt-12">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-lg text-rose-600">
                        <span aria-hidden="true">📋</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-rose-600">03 / Match centre</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-900 sm:text-2xl">Match history</h2>
                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">Recorded performance by match.</p>
                    </div>
                </div>

                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    <?php echo e($matchCount); ?> <?php echo e(Str::plural('record', $matchCount)); ?>

                </span>
            </div>

            <?php if($matchRows->isNotEmpty()): ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $matchRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-slate-300 hover:shadow-md">

                            
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-[#101b32] text-white">
                                        <span class="text-[8px] font-bold uppercase text-slate-300">Match</span>
                                        <span class="text-sm font-black"><?php echo e($match['match_number'] ?? $match['fixture_id'] ?? '—'); ?></span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">Match <?php echo e($match['match_number'] ?? $match['fixture_id'] ?? '—'); ?></p>
                                        <p class="mt-0.5 text-xs text-slate-500"><?php echo e($match['date']); ?></p>
                                    </div>
                                </div>

                                <?php if($match['fixture_id'] && \Illuminate\Support\Facades\Route::has('fixtures.show')): ?>
                                    <a href="<?php echo e(route('fixtures.show', $match['fixture_id'])); ?>"
                                       class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                                        View match <span aria-hidden="true">→</span>
                                    </a>
                                <?php endif; ?>
                            </div>

                            
                            <div class="grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-center sm:gap-4 sm:p-5">
                                <div class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                    <?php if($match['my_team_logo']): ?>
                                        <img src="<?php echo e($match['my_team_logo']); ?>"
                                             alt="<?php echo e($match['my_team']); ?> logo"
                                             class="h-11 w-11 shrink-0 rounded-xl border border-slate-200 bg-white p-1 object-contain"
                                             loading="lazy"
                                             onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#101b32] text-[10px] font-black text-white">
                                            <?php echo e(Str::upper(Str::substr($match['my_team'], 0, 3))); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Player's team</p>
                                        <p class="mt-1 break-words text-sm font-extrabold leading-5 text-slate-900"><?php echo e($match['my_team']); ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center">
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-black tracking-[0.2em] text-slate-400">VS</span>
                                </div>

                                <div class="flex min-w-0 items-center gap-3 rounded-xl border border-rose-100 bg-rose-50/60 p-3">
                                    <?php if($match['opponent_logo']): ?>
                                        <img src="<?php echo e($match['opponent_logo']); ?>"
                                             alt="<?php echo e($match['opponent']); ?> logo"
                                             class="h-11 w-11 shrink-0 rounded-xl border border-slate-200 bg-white p-1 object-contain"
                                             loading="lazy"
                                             onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-100 bg-white text-[10px] font-black text-rose-600">
                                            <?php echo e(Str::upper(Str::substr($match['opponent'], 0, 3))); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-rose-500">Opponent</p>
                                        <p class="mt-1 break-words text-sm font-extrabold leading-5 text-slate-900"><?php echo e($match['opponent']); ?></p>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="grid grid-cols-2 gap-px border-t border-slate-100 bg-slate-100 sm:grid-cols-4">
                                <div class="bg-white p-4 sm:p-5">
                                    <p class="text-xs font-semibold text-slate-500">Batting</p>
                                    <p class="mt-2 text-2xl font-black tracking-tight text-[#101b32]">
                                        <?php echo e($match['runs']); ?>

                                        <span class="text-sm font-semibold text-slate-400">(<?php echo e($match['balls']); ?>)</span>
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-400"><?php echo e($match['fours']); ?> fours · <?php echo e($match['sixes']); ?> sixes</p>
                                </div>

                                <div class="bg-white p-4 sm:p-5">
                                    <p class="text-xs font-semibold text-slate-500">Bowling</p>
                                    <p class="mt-2 text-2xl font-black tracking-tight text-[#101b32]">
                                        <?php echo e($match['wickets']); ?><span class="mx-1 text-slate-300">/</span><?php echo e($match['bowling_runs']); ?>

                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-400"><?php echo e($match['overs']); ?> overs</p>
                                </div>

                                <div class="bg-white p-4 sm:p-5">
                                    <p class="text-xs font-semibold text-slate-500">Economy</p>
                                    <p class="mt-2 text-2xl font-black tracking-tight text-[#101b32]"><?php echo e($number($match['economy'])); ?></p>
                                    <p class="mt-1 text-[11px] text-slate-400">Runs per over</p>
                                </div>

                                <div class="bg-white p-4 sm:p-5">
                                    <p class="text-xs font-semibold text-slate-500">Dismissal</p>
                                    <p class="mt-2 break-words text-sm font-bold leading-5 text-slate-800"><?php echo e($match['dismissal'] ?: '—'); ?></p>
                                    <p class="mt-1 text-[11px] text-slate-400">Batting record</p>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-8">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-3xl">
                        🏏
                    </div>
                    <h3 class="mt-4 text-lg font-black text-slate-900">No match records yet</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Match-by-match performance will appear here when a matching batting or bowling entry is available in the saved scorecards.
                    </p>
                    <?php if($teamLogo): ?>
                        <div class="mt-5 flex justify-center">
                            <img src="<?php echo e($teamLogo); ?>"
                                 alt="<?php echo e($teamName); ?> logo"
                                 class="h-14 w-14 rounded-xl border border-slate-200 bg-white p-2 object-contain">
                        </div>
                        <p class="mt-2 text-sm font-bold text-slate-600"><?php echo e($teamName); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        
        <footer class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-black text-slate-900">NPL Season Hub</p>
                <p class="mt-1 text-xs text-slate-500">Player statistics and match performance.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <?php if(\Illuminate\Support\Facades\Route::has('teams.index')): ?>
                    <a href="<?php echo e(route('teams.index')); ?>"
                       class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#101b32] px-4 py-3 text-sm font-bold text-white transition hover:bg-rose-600">
                        <span aria-hidden="true">←</span> All teams
                    </a>
                <?php endif; ?>

                <a href="<?php echo e(url('/')); ?>"
                   class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                    Home
                </a>
            </div>
        </footer>

    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/fixtures/player-performance.blade.php ENDPATH**/ ?>