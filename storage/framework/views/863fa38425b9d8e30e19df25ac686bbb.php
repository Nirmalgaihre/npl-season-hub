
<?php
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

    /*
    |--------------------------------------------------------------------------
    | Manual scoreboard integration
    |--------------------------------------------------------------------------
    | The controller must pass $manualScorecards to this view.
    | Scores are mapped using batting_team_id, not innings_number.
    */

    $manualScorecards = $manualScorecards ?? collect();

    $manualHomeInnings = $manualScorecards->first(
        fn ($scorecard) =>
            (int) $scorecard->batting_team_id === (int) $selectedHome
    );

    $manualAwayInnings = $manualScorecards->first(
        fn ($scorecard) =>
            (int) $scorecard->batting_team_id === (int) $selectedAway
    );

    $homeRunsValue = old(
        'home_runs',
        $manualHomeInnings?->total_runs ?? $fixture->home_runs
    );

    $homeWicketsValue = old(
        'home_wickets',
        $manualHomeInnings?->wickets ?? $fixture->home_wickets
    );

    $homeOversValue = old(
        'home_overs',
        $manualHomeInnings?->overs ?? $fixture->home_overs
    );

    $awayRunsValue = old(
        'away_runs',
        $manualAwayInnings?->total_runs ?? $fixture->away_runs
    );

    $awayWicketsValue = old(
        'away_wickets',
        $manualAwayInnings?->wickets ?? $fixture->away_wickets
    );

    $awayOversValue = old(
        'away_overs',
        $manualAwayInnings?->overs ?? $fixture->away_overs
    );
?>

<div class="mx-auto max-w-5xl space-y-8 font-sans">

    
    <?php if($errors->any()): ?>
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
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                <?php echo e($error); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
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

                
                <div class="space-y-1.5">
                    <label for="season_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Season <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="season_id" name="season_id" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 ease-in-out focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            <option value="">Select season</option>
                            <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($season->id); ?>"
                                    <?php if((string) old('season_id', $fixture->season_id) === (string) $season->id): echo 'selected'; endif; ?>>
                                    <?php echo e($season->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-1.5">
                    <label for="match_number" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Match Number <span class="text-red-500">*</span>
                    </label>
                    <input id="match_number" type="number" min="1" name="match_number"
                        value="<?php echo e(old('match_number', $fixture->match_number)); ?>" required
                        placeholder="e.g. 12"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 ease-in-out placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                </div>

                
                <div class="md:col-span-2 rounded-2xl border border-slate-100 bg-slate-50/60 p-4 sm:p-5">
                    <div class="grid gap-4 sm:grid-cols-2">

                        <div class="space-y-1.5">
                            <label for="home_team_id" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700">
                                <span class="inline-block h-2 w-2 rounded-full bg-red-600"></span>
                                Home Team <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="home_team_id" name="home_team_id" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-xs transition duration-150 focus:border-red-600 focus:outline-none focus:ring-4 focus:ring-red-600/10">
                                    <option value="">Select home team</option>
                                    <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($team->id); ?>" <?php if((string) $selectedHome === (string) $team->id): echo 'selected'; endif; ?>>
                                            <?php echo e($team->name); ?><?php echo e($team->short_name ? ' ('.$team->short_name.')' : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="away_team_id" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700">
                                <span class="inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                                Away Team <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="away_team_id" name="away_team_id" required
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-xs transition duration-150 focus:border-red-600 focus:outline-none focus:ring-4 focus:ring-red-600/10">
                                    <option value="">Select away team</option>
                                    <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($team->id); ?>" <?php if((string) $selectedAway === (string) $team->id): echo 'selected'; endif; ?>>
                                            <?php echo e($team->name); ?><?php echo e($team->short_name ? ' ('.$team->short_name.')' : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                
                <div class="space-y-1.5">
                    <label for="match_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Match Type <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="match_type" name="match_type" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            <?php $__currentLoopData = $matchTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>"
                                    <?php if(old('match_type', $fixture->match_type ?? 'league') === $value): echo 'selected'; endif; ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-1.5">
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Match Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="status" name="status" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>"
                                    <?php if(old('status', $fixture->status ?? 'scheduled') === $value): echo 'selected'; endif; ?>>
                                    <?php echo e($data['label']); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-1.5">
                    <label for="scheduled_at" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Date and Time
                    </label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at"
                        value="<?php echo e(old('scheduled_at', $fixture->scheduled_at?->format('Y-m-d\TH:i'))); ?>"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                </div>

                
                <div class="space-y-1.5">
                    <label for="venue" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Venue
                    </label>
                    <div class="relative">
                        <input id="venue" name="venue" maxlength="180"
                            value="<?php echo e(old('venue', $fixture->venue)); ?>"
                            placeholder="e.g. Lord's Cricket Ground"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/30 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-800 transition duration-150 placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
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

            
            <?php if($manualHomeInnings || $manualAwayInnings): ?>
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m6.023-4.306A11.954 11.954 0 0112 2.25a11.954 11.954 0 01-9.273 3.194A12.003 12.003 0 0012 21.75a12.003 12.003 0 009.273-16.806z" />
                    </svg>
                    <div>
                        <p class="font-bold">Manual scoreboard data loaded</p>
                        <p class="mt-0.5 text-xs text-emerald-700">
                            Available innings have been matched to their batting teams. Saving this form updates the fixture fields, not the manual scorecard records.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid gap-6 md:grid-cols-2">

                
                <div class="rounded-2xl border border-slate-200/60 bg-gradient-to-b from-slate-50/80 to-slate-50/30 p-5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                        <h3 class="font-bold text-slate-900">Home Team Innings</h3>
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        <?php echo e($teams->firstWhere('id', (int) $selectedHome)?->name ?? 'Home team'); ?>

                        <?php if($manualHomeInnings): ?>
                            <span class="font-semibold text-emerald-700">· Manual scorecard</span>
                        <?php endif; ?>
                    </p>

                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div>
                            <label for="home_runs" class="mb-1 block text-xs font-bold text-slate-600">Runs</label>
                            <input id="home_runs" type="number" min="0" max="999" name="home_runs"
                                value="<?php echo e($homeRunsValue); ?>"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>

                        <div>
                            <label for="home_wickets" class="mb-1 block text-xs font-bold text-slate-600">Wickets</label>
                            <input id="home_wickets" type="number" min="0" max="10" name="home_wickets"
                                value="<?php echo e($homeWicketsValue); ?>"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>

                        <div>
                            <label for="home_overs" class="mb-1 block text-xs font-bold text-slate-600">Overs</label>
                            <input id="home_overs" type="number" min="0" max="50" step="0.1" name="home_overs"
                                value="<?php echo e($homeOversValue); ?>"
                                placeholder="20.0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                    </div>
                </div>

                
                <div class="rounded-2xl border border-slate-200/60 bg-gradient-to-b from-slate-50/80 to-slate-50/30 p-5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                        <h3 class="font-bold text-slate-900">Away Team Innings</h3>
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        <?php echo e($teams->firstWhere('id', (int) $selectedAway)?->name ?? 'Away team'); ?>

                        <?php if($manualAwayInnings): ?>
                            <span class="font-semibold text-emerald-700">· Manual scorecard</span>
                        <?php endif; ?>
                    </p>

                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div>
                            <label for="away_runs" class="mb-1 block text-xs font-bold text-slate-600">Runs</label>
                            <input id="away_runs" type="number" min="0" max="999" name="away_runs"
                                value="<?php echo e($awayRunsValue); ?>"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>

                        <div>
                            <label for="away_wickets" class="mb-1 block text-xs font-bold text-slate-600">Wickets</label>
                            <input id="away_wickets" type="number" min="0" max="10" name="away_wickets"
                                value="<?php echo e($awayWicketsValue); ?>"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>

                        <div>
                            <label for="away_overs" class="mb-1 block text-xs font-bold text-slate-600">Overs</label>
                            <input id="away_overs" type="number" min="0" max="50" step="0.1" name="away_overs"
                                value="<?php echo e($awayOversValue); ?>"
                                placeholder="20.0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-center font-semibold text-slate-900 shadow-2xs focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20">
                        </div>
                    </div>
                </div>

                
                <div class="space-y-1.5">
                    <label for="winner_team_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Winner
                    </label>
                    <div class="relative">
                        <select id="winner_team_id" name="winner_team_id"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm font-medium text-slate-800 transition duration-150 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                            <option value="">Not set / No winner</option>
                            <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($team->id); ?>"
                                    <?php if((string) old('winner_team_id', $fixture->winner_team_id) === (string) $team->id): echo 'selected'; endif; ?>>
                                    <?php echo e($team->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-1.5">
                    <label for="player_of_match" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Player of the Match
                    </label>
                    <div class="relative">
                        <input id="player_of_match" name="player_of_match" maxlength="150"
                            value="<?php echo e(old('player_of_match', $fixture->player_of_match)); ?>"
                            placeholder="e.g. Virat Kohli"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/30 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-800 transition duration-150 placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 003-3V8.25a3 3 0 00-3-3h-9a3 3 0 00-3 3v7.5a3 3 0 003 3m9 0v-11.25a3 3 0 00-3-3h-9a3 3 0 00-3 3v11.25" />
                            </svg>
                        </div>
                    </div>
                </div>

                
                <div class="md:col-span-2 space-y-1.5">
                    <label for="result_summary" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Result Summary
                    </label>
                    <textarea id="result_summary" name="result_summary" rows="3" maxlength="2000"
                        placeholder="e.g. India won by 6 wickets with 3 balls remaining..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/30 p-4 text-sm font-medium text-slate-800 transition duration-150 placeholder:text-slate-400 focus:border-red-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-600/10"><?php echo e(old('result_summary', $fixture->result_summary)); ?></textarea>
                </div>

                
                <div class="md:col-span-2">
                    <label class="group relative flex cursor-pointer items-start gap-4 rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/60 to-orange-50/30 p-4 transition duration-150 hover:bg-amber-50">
                        <div class="flex items-center pt-0.5">
                            <input type="checkbox" name="is_result_verified" value="1"
                                <?php if(old('is_result_verified', $fixture->is_result_verified)): echo 'checked'; endif; ?>
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

    
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="<?php echo e(route('admin.fixtures.index')); ?>"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-2xs transition duration-150 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-100">
            Cancel
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-red-600 to-red-700 px-7 py-3 text-sm font-bold text-white shadow-md transition duration-150 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-4 focus:ring-red-600/30 active:scale-[0.99]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            <?php echo e($fixture->exists ? 'Save Changes' : 'Create Fixture'); ?>

        </button>
    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const homeTeam = document.getElementById('home_team_id');
    const awayTeam = document.getElementById('away_team_id');
    const homeRuns = document.getElementById('home_runs');
    const awayRuns = document.getElementById('away_runs');
    const awayWickets = document.getElementById('away_wickets');
    const winner = document.getElementById('winner_team_id');
    const summary = document.getElementById('result_summary');
    const status = document.getElementById('status');

    if (
        !homeTeam || !awayTeam ||
        !homeRuns || !awayRuns ||
        !awayWickets || !winner || !summary || !status
    ) {
        return;
    }

    let summaryWasEdited = summary.value.trim() !== '';

    function getTeamName(select) {
        return select.options[select.selectedIndex]?.text
            .replace(/\s*\([^)]*\)\s*$/, '')
            .trim() || 'Team';
    }

    function calculateResult() {
        // Never announce a winner before the match is completed.
        if (status.value !== 'completed') {
            winner.value = '';

            if (!summaryWasEdited) {
                summary.value = '';
            }

            return;
        }

        const home = Number(homeRuns.value);
        const away = Number(awayRuns.value);
        const wickets = Number(awayWickets.value);

        const valid =
            homeTeam.value &&
            awayTeam.value &&
            homeTeam.value !== awayTeam.value &&
            homeRuns.value.trim() !== '' &&
            awayRuns.value.trim() !== '' &&
            awayWickets.value.trim() !== '' &&
            Number.isFinite(home) &&
            Number.isFinite(away) &&
            Number.isFinite(wickets) &&
            home >= 0 &&
            away >= 0 &&
            wickets >= 0 &&
            wickets <= 10;

        if (!valid) {
            winner.value = '';

            if (!summaryWasEdited) {
                summary.value = '';
            }

            return;
        }

        const homeName = getTeamName(homeTeam);
        const awayName = getTeamName(awayTeam);

        let winnerId = '';
        let result = '';

        if (home === away) {
            result = 'Match tied.';
        } else if (home > away) {
            winnerId = homeTeam.value;
            result = `${homeName} won by ${home - away} runs.`;
        } else {
            winnerId = awayTeam.value;
            result = `${awayName} won by ${10 - wickets} wickets.`;
        }

        winner.value = winnerId;

        if (!summaryWasEdited) {
            summary.value = result;
        }
    }

    summary.addEventListener('input', function () {
        summaryWasEdited = summary.value.trim() !== '';

        if (!summaryWasEdited) {
            calculateResult();
        }
    });

    [
        homeTeam,
        awayTeam,
        homeRuns,
        awayRuns,
        awayWickets,
        status
    ].forEach(function (element) {
        element.addEventListener('input', calculateResult);
        element.addEventListener('change', calculateResult);
    });

    calculateResult();
});
</script>

<?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/fixtures/_form.blade.php ENDPATH**/ ?>