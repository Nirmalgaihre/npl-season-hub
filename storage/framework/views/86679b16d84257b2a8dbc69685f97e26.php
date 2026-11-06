<?php $__env->startSection('content'); ?>
<?php
    $seasons = $seasons ?? collect();
    $teams = $teams ?? collect();
    $savedFixtures = $savedFixtures ?? [];

    $savedFixtures = collect($savedFixtures)
        ->map(fn ($id) => (int) $id)
        ->all();
?>

<div class="min-h-screen bg-slate-50/80">
    <div class="mx-auto max-w-[1600px] px-4 py-8 sm:px-6 lg:px-8">

        
        <div class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-4 inline-flex items-center gap-2.5 rounded-full border border-amber-200/70 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-800 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-600"></span>
                    </span>
                    NPL Operations Hub
                </div>

                <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Manual Scorecards
                </h1>
                <p class="mt-2 max-w-2xl text-[15px] leading-relaxed text-slate-500">
                    Manage match scorecards, audit saved innings, and enter real-time results with precision.
                </p>
            </div>

            
            <div class="flex items-center gap-5 rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-[0_4px_20px_-4px_rgba(15,23,42,0.08)]">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 text-amber-400 shadow-inner">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Fixtures</p>
                    <p class="text-2xl font-black tabular-nums text-slate-900"><?php echo e($fixtures->total()); ?></p>
                </div>
            </div>
        </div>

        
        <?php if(session('success')): ?>
            <div role="status" class="mb-6 flex items-start gap-3.5 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 px-5 py-4 text-emerald-900 shadow-sm">
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-4.5 w-4.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.72a.75.75 0 00-1.06-1.06L9 10.94 7.78 9.72a.75.75 0 00-1.06 1.06l1.75 1.75a.75.75 0 001.06 0l4.25-4.25z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Success</p>
                    <p class="mt-0.5 text-sm text-emerald-800/90"><?php echo e(session('success')); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div role="alert" class="mb-6 rounded-2xl border border-rose-200/80 bg-rose-50/90 px-5 py-4 text-sm text-rose-900 shadow-sm">
                <p class="font-bold">Action Failed</p>
                <p class="mt-0.5 text-rose-800/90"><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div role="alert" class="mb-6 rounded-2xl border border-rose-200/80 bg-rose-50/90 px-5 py-4 text-rose-900 shadow-sm">
                <p class="font-bold">Please check the highlighted fields:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-rose-800/90">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <section class="mb-8 overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.07)]">
            <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white px-6 py-4">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-900">Filter Fixtures</h2>
                    <p class="text-xs text-slate-500">Refine by season, team, or match number</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    Advanced
                </span>
            </div>

            <form method="GET" action="<?php echo e(route('admin.scorecards.index')); ?>"
                  class="grid gap-5 p-6 sm:grid-cols-2 xl:grid-cols-4">

                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Season</label>
                    <select name="season_id" class="sc-control">
                        <option value="">All Seasons</option>
                        <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($season->id); ?>"
                                <?php if((string) request('season_id') === (string) $season->id): echo 'selected'; endif; ?>>
                                <?php echo e($season->name ?? $season->title ?? ('Season ' . $season->id)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Team</label>
                    <select name="team_id" class="sc-control">
                        <option value="">All Teams</option>
                        <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($team->id); ?>"
                                <?php if((string) request('team_id') === (string) $team->id): echo 'selected'; endif; ?>>
                                <?php echo e($team->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Match Number</label>
                    <input type="number" min="1" name="match_number"
                           value="<?php echo e(request('match_number')); ?>"
                           class="sc-control" placeholder="e.g. 12">
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 text-sm font-bold text-white shadow-md transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300/40">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/>
                        </svg>
                        Apply Filters
                    </button>

                    <a href="<?php echo e(route('admin.scorecards.index')); ?>"
                       class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        
        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Match Schedules & Scorecards</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    <?php if($fixtures->total() > 0): ?>
                        Showing <span class="font-semibold text-slate-700"><?php echo e($fixtures->firstItem()); ?>–<?php echo e($fixtures->lastItem()); ?></span>
                        of <span class="font-semibold text-slate-700"><?php echo e($fixtures->total()); ?></span> fixtures
                    <?php else: ?>
                        No matches match your current filters
                    <?php endif; ?>
                </p>
            </div>

            <div class="flex items-center gap-5 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 shadow-sm">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span>
                    Saved Scorecard
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-slate-300 ring-2 ring-slate-100"></span>
                    Pending Entry
                </span>
            </div>
        </div>

        
        <div class="hidden overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.07)] lg:block">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50/90 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th scope="col" class="px-6 py-4">Match Details</th>
                            <th scope="col" class="px-6 py-4">Season</th>
                            <th scope="col" class="px-6 py-4">Schedule</th>
                            <th scope="col" class="px-6 py-4">Current Score</th>
                            <th scope="col" class="px-6 py-4">Result Status</th>
                            <th scope="col" class="px-6 py-4">Scorecard</th>
                            <th scope="col" class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php $__empty_1 = true; $__currentLoopData = $fixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $hasScorecard = in_array((int) $fixture->id, $savedFixtures, true);
                                $homeTeam = $fixture->homeTeam;
                                $awayTeam = $fixture->awayTeam;
                                $homeName = $homeTeam->short_name ?? $homeTeam->name ?? 'Home';
                                $awayName = $awayTeam->short_name ?? $awayTeam->name ?? 'Away';
                                $hasScores = $fixture->home_runs !== null || $fixture->away_runs !== null;
                            ?>

                            <tr class="group transition-colors hover:bg-slate-50/70">
                                
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-white">
                                            #<?php echo e($fixture->match_number ?? $fixture->id); ?>

                                        </span>
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                            <?php echo e($fixture->match_type ?? 'T20'); ?>

                                        </span>
                                    </div>
                                    <p class="mt-2 font-bold text-slate-900">
                                        <?php echo e($homeTeam->name ?? 'Home Team'); ?>

                                        <span class="mx-1.5 font-normal text-slate-400">vs</span>
                                        <?php echo e($awayTeam->name ?? 'Away Team'); ?>

                                    </p>
                                </td>

                                
                                <td class="px-6 py-5 font-medium text-slate-600">
                                    <?php echo e($fixture->season->name ?? $fixture->season->title ?? '—'); ?>

                                </td>

                                
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <?php if($fixture->scheduled_at): ?>
                                        <p class="font-semibold text-slate-800">
                                            <?php echo e(\Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('d M Y')); ?>

                                        </p>
                                        <p class="text-xs text-slate-400">
                                            <?php echo e(\Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('h:i A')); ?>

                                        </p>
                                    <?php else: ?>
                                        <span class="italic text-slate-400">Unscheduled</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-5">
                                    <?php if($hasScores): ?>
                                        <div class="space-y-1.5 min-w-[160px]">
                                            <div class="flex items-center justify-between gap-6">
                                                <span class="font-medium text-slate-600"><?php echo e($homeName); ?></span>
                                                <span class="font-bold tabular-nums text-slate-900">
                                                    <?php echo e($fixture->home_runs ?? '—'); ?>

                                                    <?php if($fixture->home_wickets !== null): ?>/<?php echo e($fixture->home_wickets); ?><?php endif; ?>
                                                    <?php if($fixture->home_overs !== null): ?>
                                                        <span class="ml-1 text-xs font-normal text-slate-400">(<?php echo e($fixture->home_overs); ?>)</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between gap-6">
                                                <span class="font-medium text-slate-600"><?php echo e($awayName); ?></span>
                                                <span class="font-bold tabular-nums text-slate-900">
                                                    <?php echo e($fixture->away_runs ?? '—'); ?>

                                                    <?php if($fixture->away_wickets !== null): ?>/<?php echo e($fixture->away_wickets); ?><?php endif; ?>
                                                    <?php if($fixture->away_overs !== null): ?>
                                                        <span class="ml-1 text-xs font-normal text-slate-400">(<?php echo e($fixture->away_overs); ?>)</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="italic text-slate-400">No runs recorded</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-5 max-w-[200px]">
                                    <?php if($fixture->winner_team_id): ?>
                                        <span class="font-semibold text-emerald-700">
                                            <?php echo e($fixture->result_summary ?: 'Result Recorded'); ?>

                                        </span>
                                    <?php elseif($hasScorecard): ?>
                                        <span class="text-slate-600">
                                            <?php echo e($fixture->result_summary ?: 'Scorecard Saved'); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400">Awaiting outcome</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <?php if($hasScorecard): ?>
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Saved
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-5 text-right whitespace-nowrap">
                                    <a href="<?php echo e(route('admin.scorecards.edit', ['fixture' => $fixture->id])); ?>"
                                       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300/40 group-hover:shadow-md">
                                        <?php echo e($hasScorecard ? 'Edit Scorecard' : 'Enter Scorecard'); ?>

                                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/>
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold text-slate-900">No match records found</h3>
                                    <p class="mt-1.5 text-sm text-slate-500">Try adjusting or clearing your filters.</p>
                                    <a href="<?php echo e(route('admin.scorecards.index')); ?>"
                                       class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-slate-900 hover:underline">
                                        Clear all filters
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="space-y-4 lg:hidden">
            <?php $__empty_1 = true; $__currentLoopData = $fixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $hasScorecard = in_array((int) $fixture->id, $savedFixtures, true);
                    $homeTeam = $fixture->homeTeam;
                    $awayTeam = $fixture->awayTeam;
                    $homeName = $homeTeam->short_name ?? $homeTeam->name ?? 'Home';
                    $awayName = $awayTeam->short_name ?? $awayTeam->name ?? 'Away';
                    $hasScores = $fixture->home_runs !== null || $fixture->away_runs !== null;
                ?>

                <div class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-[0_4px_20px_-4px_rgba(15,23,42,0.06)]">
                    
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-white">
                                    #<?php echo e($fixture->match_number ?? $fixture->id); ?>

                                </span>
                                <span class="text-[11px] font-semibold uppercase text-slate-400">
                                    <?php echo e($fixture->match_type ?? 'T20'); ?>

                                </span>
                            </div>
                            <h3 class="mt-2 text-[15px] font-bold leading-snug text-slate-900">
                                <?php echo e($homeTeam->name ?? 'Home Team'); ?>

                                <span class="font-normal text-slate-400">vs</span>
                                <?php echo e($awayTeam->name ?? 'Away Team'); ?>

                            </h3>
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold
                            <?php echo e($hasScorecard
                                ? 'border border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border border-slate-200 bg-slate-100 text-slate-600'); ?>">
                            <?php echo e($hasScorecard ? 'Saved' : 'Pending'); ?>

                        </span>
                    </div>

                    
                    <div class="grid grid-cols-2 gap-3 px-5 py-4">
                        <div class="rounded-xl bg-slate-50 px-3.5 py-3">
                            <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($homeName); ?></p>
                            <p class="mt-1 text-xl font-black tabular-nums text-slate-900">
                                <?php echo e($fixture->home_runs ?? '—'); ?>

                                <?php if($fixture->home_wickets !== null): ?>
                                    <span class="text-sm font-semibold text-slate-500">/<?php echo e($fixture->home_wickets); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3.5 py-3">
                            <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($awayName); ?></p>
                            <p class="mt-1 text-xl font-black tabular-nums text-slate-900">
                                <?php echo e($fixture->away_runs ?? '—'); ?>

                                <?php if($fixture->away_wickets !== null): ?>
                                    <span class="text-sm font-semibold text-slate-500">/<?php echo e($fixture->away_wickets); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    
                    <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3.5 text-xs text-slate-500">
                        <span>
                            <?php echo e($fixture->scheduled_at
                                ? \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('d M Y · h:i A')
                                : 'Unscheduled'); ?>

                        </span>
                    </div>

                    <div class="px-5 pb-5">
                        <a href="<?php echo e(route('admin.scorecards.edit', ['fixture' => $fixture->id])); ?>"
                           class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800">
                            <?php echo e($hasScorecard ? 'Edit Scorecard' : 'Enter Scorecard'); ?>

                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-slate-200/70 bg-white px-6 py-14 text-center shadow-sm">
                    <p class="text-slate-500">No match records found.</p>
                    <a href="<?php echo e(route('admin.scorecards.index')); ?>" class="mt-3 inline-block text-sm font-bold text-slate-900 hover:underline">
                        Clear filters
                    </a>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($fixtures->hasPages()): ?>
            <div class="mt-8 rounded-2xl border border-slate-200/70 bg-white px-5 py-4 shadow-sm">
                <?php echo e($fixtures->links()); ?>

            </div>
        <?php endif; ?>

    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .sc-control {
        display: block;
        width: 100%;
        min-height: 44px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background-color: #fff;
        padding: 10px 14px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .sc-control:hover {
        border-color: #cbd5e1;
    }

    .sc-control:focus {
        border-color: #0f172a;
        box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
    }

    .sc-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/scorecards/index.blade.php ENDPATH**/ ?>