 

<?php $__env->startSection('title', 'Fixtures & Results | NPL Admin'); ?>
<?php $__env->startSection('page_heading', 'Fixtures & Results'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-100/70">
    <div class="mx-auto max-w-[1500px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        
        <div class="flex flex-col justify-between gap-4 rounded-2xl bg-slate-950 p-6 text-white sm:flex-row sm:items-center">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-red-400">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    NPL Administration
                </div>

                <h1 class="text-2xl font-black sm:text-3xl">Fixtures & Results</h1>

                <p class="mt-2 text-sm text-slate-400">
                    Manage matches, check scores and publish verified results.
                </p>
            </div>

            <a href="<?php echo e(route('admin.fixtures.create')); ?>"
               class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/>
                </svg>
                Create Fixture
            </a>
        </div>

        
        <?php if(session('success')): ?>
            <div role="status" class="flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <p><strong>Success:</strong> <?php echo e(session('success')); ?></p>
                <button type="button" onclick="this.parentElement.remove()" class="text-lg leading-none">&times;</button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div role="alert" class="flex items-start justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p><strong>Error:</strong> <?php echo e(session('error')); ?></p>
                <button type="button" onclick="this.parentElement.remove()" class="text-lg leading-none">&times;</button>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-bold">Please fix the following errors:</p>
                <ul class="mt-2 list-inside list-disc">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <a href="<?php echo e(route('admin.fixtures.index')); ?>"
               class="rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-400">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500 sm:text-sm">All Matches</span>
                    <span class="rounded-lg bg-slate-100 p-2 text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="17" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900"><?php echo e(number_format($stats['total'] ?? 0)); ?></p>
                <p class="mt-1 text-xs text-slate-400">Total fixtures</p>
            </a>

            <a href="<?php echo e(route('admin.fixtures.index', ['status' => 'scheduled'])); ?>"
               class="rounded-xl border border-slate-200 bg-white p-4 transition hover:border-blue-300">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500 sm:text-sm">Scheduled</span>
                    <span class="rounded-lg bg-blue-50 p-2 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900"><?php echo e(number_format($stats['scheduled'] ?? 0)); ?></p>
                <p class="mt-1 text-xs text-slate-400">Upcoming matches</p>
            </a>

            <a href="<?php echo e(route('admin.fixtures.index', ['status' => 'live'])); ?>"
               class="rounded-xl border border-slate-200 bg-white p-4 transition hover:border-red-300">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500 sm:text-sm">Live Now</span>
                    <span class="rounded-lg bg-red-50 p-2 text-red-600">
                        <span class="block h-5 w-5 rounded-full border-2 border-current p-1">
                            <span class="block h-full w-full rounded-full bg-current"></span>
                        </span>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-red-600"><?php echo e(number_format($stats['live'] ?? 0)); ?></p>
                <p class="mt-1 text-xs text-slate-400">Currently in progress</p>
            </a>

            <a href="<?php echo e(route('admin.fixtures.index', ['status' => 'completed'])); ?>"
               class="rounded-xl border border-slate-200 bg-white p-4 transition hover:border-emerald-300">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500 sm:text-sm">Completed</span>
                    <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900"><?php echo e(number_format($stats['completed'] ?? 0)); ?></p>
                <p class="mt-1 text-xs text-slate-400">Finished matches</p>
            </a>
        </div>

        
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex flex-col justify-between gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="font-bold text-slate-900">Find a Match</h2>
                    <p class="mt-1 text-xs text-slate-500">Search by team, venue, season or status.</p>
                </div>

                <span class="text-sm text-slate-500">
                    <strong class="text-slate-900"><?php echo e(number_format($fixtures->total())); ?></strong>
                    matches found
                </span>
            </div>

            <form method="GET" action="<?php echo e(route('admin.fixtures.index')); ?>" class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-12">
                <div class="relative sm:col-span-2 lg:col-span-5">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                    </svg>

                    <input type="search"
                           name="search"
                           value="<?php echo e(request('search')); ?>"
                           placeholder="Search team or venue..."
                           class="fixture-control pl-9">
                </div>

                <div class="lg:col-span-3">
                    <select name="season_id" class="fixture-control">
                        <option value="">All Seasons</option>
                        <?php $__currentLoopData = ($seasons ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($season->id); ?>"
                                <?php if((string) request('season_id') === (string) $season->id): echo 'selected'; endif; ?>>
                                <?php echo e($season->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <select name="status" class="fixture-control">
                        <option value="">All Statuses</option>
                        <?php $__currentLoopData = ['scheduled', 'live', 'completed', 'abandoned', 'postponed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>>
                                <?php echo e(ucfirst($status)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit"
                            class="flex-1 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">
                        Search
                    </button>

                    <a href="<?php echo e(route('admin.fixtures.index')); ?>"
                       class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </form>

            <?php
                $quickStatuses = [
                    '' => 'All',
                    'scheduled' => 'Scheduled',
                    'live' => 'Live',
                    'completed' => 'Completed',
                    'abandoned' => 'Abandoned',
                    'postponed' => 'Postponed',
                ];
            ?>

            <div class="flex gap-2 overflow-x-auto border-t border-slate-100 px-4 py-3">
                <?php $__currentLoopData = $quickStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $quickParams = array_filter([
                            'search' => request('search'),
                            'season_id' => request('season_id'),
                            'status' => $value,
                        ], fn ($item) => $item !== null && $item !== '');
                    ?>

                    <a href="<?php echo e(route('admin.fixtures.index', $quickParams)); ?>"
                       class="shrink-0 rounded-lg border px-3.5 py-2 text-xs font-bold transition
                       <?php echo e((string) request('status', '') === (string) $value
                            ? 'border-slate-900 bg-slate-900 text-white'
                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'); ?>">
                        <?php if($value === 'live'): ?>
                            <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-red-500"></span>
                        <?php endif; ?>
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex flex-col justify-between gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="font-bold text-slate-900">Match Centre</h2>
                    <p class="mt-1 text-xs text-slate-500">Scores, schedules and match management.</p>
                </div>

                <p class="text-xs text-slate-500">
                    Showing <?php echo e($fixtures->firstItem() ?? 0); ?>–<?php echo e($fixtures->lastItem() ?? 0); ?>

                    of <?php echo e($fixtures->total()); ?>

                </p>
            </div>

            <div class="divide-y divide-slate-200">
                <?php $__empty_1 = true; $__currentLoopData = $fixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $statusStyles = [
                            'scheduled' => 'bg-blue-50 text-blue-700',
                            'live' => 'bg-red-50 text-red-700',
                            'completed' => 'bg-emerald-50 text-emerald-700',
                            'abandoned' => 'bg-amber-50 text-amber-800',
                            'postponed' => 'bg-amber-50 text-amber-800',
                        ];

                        $statusClass = $statusStyles[$fixture->status] ?? 'bg-slate-100 text-slate-700';

                        $fixtureScorecards = collect($manualScorecards ?? [])->get($fixture->id, collect());

                        $homeManual = $fixtureScorecards->first(
                            fn ($scorecard) => (int) $scorecard->batting_team_id === (int) $fixture->home_team_id
                        );

                        $awayManual = $fixtureScorecards->first(
                            fn ($scorecard) => (int) $scorecard->batting_team_id === (int) $fixture->away_team_id
                        );

                        $homeRuns = $homeManual?->total_runs ?? $fixture->home_runs;
                        $homeWickets = $homeManual?->wickets ?? $fixture->home_wickets;
                        $homeOvers = $homeManual?->overs ?? $fixture->home_overs;

                        $awayRuns = $awayManual?->total_runs ?? $fixture->away_runs;
                        $awayWickets = $awayManual?->wickets ?? $fixture->away_wickets;
                        $awayOvers = $awayManual?->overs ?? $fixture->away_overs;

                        $hasBothTeamScores = $homeRuns !== null && $awayRuns !== null;

                        $canVerifyResult = $fixture->status === 'completed'
                            && !$fixture->is_result_verified
                            && $hasBothTeamScores;

                        $homeTeam = $fixture->homeTeam;
                        $awayTeam = $fixture->awayTeam;

                        $homeName = $homeTeam?->short_name ?: $homeTeam?->name ?: 'Home Team';
                        $awayName = $awayTeam?->short_name ?: $awayTeam?->name ?: 'Away Team';

                        /*
                         * Resolve the logo against storage/app/public.
                         *
                         * Supported database values:
                         * logo.png
                         * teams/logo.png
                         * storage/teams/logo.png
                         * public/teams/logo.png
                         *
                         * The actual file must exist before its URL is used.
                         */
                        $resolveTeamLogo = function ($team) {
                            if (!$team || !is_string($team->logo) || trim($team->logo) === '') {
                                return null;
                            }

                            $logo = trim(str_replace('\\', '/', $team->logo));

                            // Do not treat external URLs as local storage paths.
                            if (preg_match('/^(https?:)?\/\//i', $logo)) {
                                return null;
                            }

                            // Reject path traversal and unsafe path characters.
                            if (
                                str_contains($logo, '..') ||
                                str_contains($logo, "\0") ||
                                str_starts_with($logo, '/')
                            ) {
                                return null;
                            }

                            // Normalize common stored path prefixes.
                            $logo = preg_replace('#^public/#i', '', $logo);
                            $logo = preg_replace('#^storage/#i', '', $logo);
                            $logo = ltrim($logo, '/');

                            // If only the filename is stored, use the teams directory.
                            if (!str_contains($logo, '/')) {
                                $logo = 'teams/' . $logo;
                            }

                            // Only use files that actually exist on the public disk.
                            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($logo)) {
                                return null;
                            }

                            return asset('storage/' . $logo);
                        };

                        $homeLogo = $resolveTeamLogo($homeTeam);
                        $awayLogo = $resolveTeamLogo($awayTeam);

                        $homeInitials = strtoupper(substr(trim($homeName), 0, 2));
                        $awayInitials = strtoupper(substr(trim($awayName), 0, 2));

                        $winnerName = null;
                        $resultLabel = null;

                        if ($fixture->status === 'completed' && $hasBothTeamScores) {
                            if ((float) $homeRuns > (float) $awayRuns) {
                                $winnerName = $homeName;
                                $resultLabel = $homeName . ' won';
                            } elseif ((float) $awayRuns > (float) $homeRuns) {
                                $winnerName = $awayName;
                                $resultLabel = $awayName . ' won';
                            } else {
                                $resultLabel = 'Match tied';
                            }
                        }
                    ?>

                    <article class="p-4 transition hover:bg-slate-50 sm:p-5">
                        
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-slate-900 px-2.5 py-1 text-xs font-bold text-white">
                                    Match <?php echo e($fixture->match_number ?? '—'); ?>

                                </span>

                                <span class="rounded-md px-2.5 py-1 text-xs font-bold <?php echo e($statusClass); ?>">
                                    <?php echo e(ucfirst($fixture->status ?? 'Unknown')); ?>

                                </span>

                                <?php if($fixture->is_result_verified): ?>
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                        </svg>
                                        Verified
                                    </span>
                                <?php elseif($fixture->status === 'completed'): ?>
                                    <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">
                                        Needs verification
                                    </span>
                                <?php endif; ?>
                            </div>

                            <span class="text-xs text-slate-500">
                                <?php echo e($fixture->season?->name ?? 'NPL Season'); ?>

                            </span>
                        </div>

                        
                        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:gap-5 sm:p-5">

                            
                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-1 sm:h-12 sm:w-12">
                                    <?php if($homeLogo): ?>
                                        <img
                                            src="<?php echo e($homeLogo); ?>"
                                            alt="<?php echo e($homeName); ?> logo"
                                            class="h-full w-full object-contain"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.classList.add('hidden');this.nextElementSibling.classList.remove('hidden');"
                                        >
                                        <span class="hidden text-xs font-black text-slate-500">
                                            <?php echo e($homeInitials); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-black text-slate-500">
                                            <?php echo e($homeInitials); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900 sm:text-base">
                                        <?php echo e($homeName); ?>

                                    </p>

                                    <?php if($homeRuns !== null): ?>
                                        <p class="mt-1 text-lg font-black tabular-nums text-slate-900 sm:text-2xl">
                                            <?php echo e($homeRuns); ?><span class="text-slate-400">/<?php echo e($homeWickets ?? 0); ?></span>
                                        </p>
                                        <p class="text-xs text-slate-500"><?php echo e($homeOvers ?? 0); ?> overs</p>
                                    <?php else: ?>
                                        <p class="mt-1 text-xs text-slate-400">Yet to bat</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="text-center">
                                <span class="text-xs font-black tracking-widest text-slate-400">VS</span>
                            </div>

                            
                            <div class="flex min-w-0 items-center justify-end gap-2 text-right sm:gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900 sm:text-base">
                                        <?php echo e($awayName); ?>

                                    </p>

                                    <?php if($awayRuns !== null): ?>
                                        <p class="mt-1 text-lg font-black tabular-nums text-slate-900 sm:text-2xl">
                                            <?php echo e($awayRuns); ?><span class="text-slate-400">/<?php echo e($awayWickets ?? 0); ?></span>
                                        </p>
                                        <p class="text-xs text-slate-500"><?php echo e($awayOvers ?? 0); ?> overs</p>
                                    <?php else: ?>
                                        <p class="mt-1 text-xs text-slate-400">Yet to bat</p>
                                    <?php endif; ?>
                                </div>

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-1 sm:h-12 sm:w-12">
                                    <?php if($awayLogo): ?>
                                        <img
                                            src="<?php echo e($awayLogo); ?>"
                                            alt="<?php echo e($awayName); ?> logo"
                                            class="h-full w-full object-contain"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.classList.add('hidden');this.nextElementSibling.classList.remove('hidden');"
                                        >
                                        <span class="hidden text-xs font-black text-slate-500">
                                            <?php echo e($awayInitials); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-black text-slate-500">
                                            <?php echo e($awayInitials); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <?php if($resultLabel): ?>
                            <div class="mt-3 flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                </svg>
                                <?php echo e($resultLabel); ?>

                                <?php if(!$fixture->is_result_verified): ?>
                                    <span class="font-normal text-emerald-700">· Pending verification</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        
                        <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M16 3v4M8 3v4M3 11h18"/>
                                </svg>
                                <?php echo e($fixture->scheduled_at ? $fixture->scheduled_at->format('d M Y, h:i A') : 'Date not set'); ?>

                            </span>

                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5 7-11a7 7 0 10-14 0c0 6 7 11 7 11z"/>
                                    <circle cx="12" cy="10" r="2"/>
                                </svg>
                                <?php echo e($fixture->venue ?: 'Venue TBD'); ?>

                            </span>
                        </div>

                        
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                            <a href="<?php echo e(route('admin.fixtures.edit', $fixture)); ?>"
                               class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-slate-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16 4 4 4M4 20l4-.8L19 8a2.12 2.12 0 00-3-3L5 16l-1 4z"/>
                                </svg>
                                Edit Match
                            </a>

                            <a href="<?php echo e(route('fixtures.show', $fixture)); ?>"
                               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                View Match
                            </a>

                            <?php if($canVerifyResult): ?>
                                <form method="POST"
                                      action="<?php echo e(route('admin.fixtures.verify', $fixture)); ?>"
                                      onsubmit="return confirm('Verify this completed match result?')">
                                    <?php echo csrf_field(); ?>

                                    <button type="submit"
                                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                        </svg>
                                        Verify Result
                                    </button>
                                </form>
                            <?php endif; ?>

                            <div class="flex-1"></div>

                            <form method="POST"
                                  action="<?php echo e(route('admin.fixtures.destroy', $fixture)); ?>"
                                  onsubmit="return confirm('Permanently delete this fixture? This action cannot be undone.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>

                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="4" width="18" height="17" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 font-bold text-slate-900">No matches found</h3>
                        <p class="mt-1 text-sm text-slate-500">Try different filters or create a new fixture.</p>

                        <div class="mt-5 flex justify-center gap-2">
                            <a href="<?php echo e(route('admin.fixtures.index')); ?>"
                               class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Clear Filters
                            </a>

                            <a href="<?php echo e(route('admin.fixtures.create')); ?>"
                               class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                                Create Fixture
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($fixtures->hasPages() || $fixtures->total() > 0): ?>
                <div class="flex flex-col justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center">
                    <p class="text-xs text-slate-500">
                        Showing
                        <strong class="text-slate-800"><?php echo e($fixtures->firstItem() ?? 0); ?></strong>
                        to
                        <strong class="text-slate-800"><?php echo e($fixtures->lastItem() ?? 0); ?></strong>
                        of
                        <strong class="text-slate-800"><?php echo e($fixtures->total()); ?></strong>
                        matches
                    </p>

                    <div>
                        <?php echo e($fixtures->withQueryString()->links()); ?>

                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .fixture-control {
        display: block;
        width: 100%;
        min-height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fff;
        padding: 9px 12px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 500;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .fixture-control:hover {
        border-color: #cbd5e1;
    }

    .fixture-control:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, .10);
    }

    .fixture-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/fixtures/index.blade.php ENDPATH**/ ?>