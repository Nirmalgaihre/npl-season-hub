<?php $__env->startSection('title', 'Live Score | ' . ($fixture->homeTeam->name ?? 'Home') . ' vs ' . ($fixture->awayTeam->name ?? 'Away')); ?>

<?php $__env->startSection('content'); ?>
<meta http-equiv="refresh" content="10">

<?php
    /*
    |--------------------------------------------------------------------------
    | Build over-by-over delivery groups
    |--------------------------------------------------------------------------
    | Wides and no-balls do not count as legal balls.
    | The database's counts_as_legal_ball value is preferred when available.
    | Otherwise, wide/no-ball deliveries are treated as illegal.
    */

    $overGroups = [];
    $legalBallsSeen = 0;

    $orderedDeliveries = $current
        ? $current->deliveries->sortBy('id')->values()
        : collect();

    foreach ($orderedDeliveries as $delivery) {
        $overIndex = intdiv($legalBallsSeen, 6);
        $ballPosition = ($legalBallsSeen % 6) + 1;

        $extraType = $delivery->extra_type ?? 'none';

        if ($delivery->getAttribute('counts_as_legal_ball') !== null) {
            $isLegalDelivery = (bool) $delivery->getAttribute('counts_as_legal_ball');
        } else {
            $isLegalDelivery = !in_array($extraType, ['wide', 'no_ball'], true);
        }

        if (!isset($overGroups[$overIndex])) {
            $overGroups[$overIndex] = [
                'balls' => [],
                'runs' => 0,
                'wickets' => 0,
                'legal_balls' => 0,
            ];
        }

        $overGroups[$overIndex]['balls'][] = [
            'delivery' => $delivery,
            'position' => $ballPosition,
            'is_legal' => $isLegalDelivery,
        ];

        $overGroups[$overIndex]['runs'] += (int) $delivery->totalRuns();

        if ($delivery->is_wicket) {
            $overGroups[$overIndex]['wickets']++;
        }

        if ($isLegalDelivery) {
            $overGroups[$overIndex]['legal_balls']++;
            $legalBallsSeen++;
        }
    }

    ksort($overGroups);
?>

<div class="min-h-screen bg-[#F6F8FB] px-4 py-6 sm:px-6">
    <main class="mx-auto max-w-5xl space-y-5">

        
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] text-[#F06445]">
                    NPL Season Hub
                </p>

                <h1 class="mt-2 text-2xl font-black text-[#202766]">
                    Match centre
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    <?php echo e($fixture->match_type ?? 'Cricket match'); ?>

                    ·
                    <?php echo e($fixture->venue ?: 'Venue not set'); ?>

                </p>
            </div>

            <?php if($current && ($fixture->status ?? '') === 'live'): ?>
                <span class="rounded-full bg-red-50 px-3 py-2 text-xs font-black uppercase text-red-700">
                    <span class="mr-1 inline-block h-2 w-2 rounded-full bg-red-600"></span>
                    Live
                </span>
            <?php else: ?>
                <span class="rounded-full bg-slate-200 px-3 py-2 text-xs font-bold text-slate-600">
                    <?php echo e($current ? 'Score available' : 'Awaiting score'); ?>

                </span>
            <?php endif; ?>
        </header>

        
        <section class="overflow-hidden rounded-3xl bg-[#202766] text-white shadow-lg">

            <div class="border-b border-white/10 px-5 py-4 sm:px-8">
                <p class="text-sm font-bold text-indigo-100">
                    Match <?php echo e($fixture->match_number ?? $fixture->id); ?>

                </p>

                <p class="mt-1 text-sm text-indigo-200">
                    <?php echo e($fixture->venue ?: 'Venue not set'); ?>

                </p>
            </div>

            <div class="grid gap-6 p-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:p-8">

                <div>
                    <p class="font-bold">
                        <?php echo e($current?->battingTeam?->name ?? $fixture->homeTeam->name ?? 'Home team'); ?>

                    </p>

                    <p class="mt-2 text-4xl font-black sm:text-5xl">
                        <?php echo e($current ? $current->total_runs . '/' . $current->wickets : '—'); ?>

                    </p>

                    <p class="mt-2 text-sm text-indigo-100">
                        <?php echo e($current ? $current->oversText() . ' overs' : 'Yet to bat'); ?>

                    </p>

                    <?php if($current): ?>
                        <p class="mt-1 text-sm text-indigo-100">
                            Run rate: <?php echo e(number_format($current->runRate(), 2)); ?>

                        </p>
                    <?php endif; ?>
                </div>

                <div class="hidden text-xs font-black tracking-[.25em] text-white/40 sm:block">
                    VS
                </div>

                <div class="sm:text-right">
                    <p class="font-bold">
                        <?php echo e($current?->bowlingTeam?->name ?? $fixture->awayTeam->name ?? 'Away team'); ?>

                    </p>

                    <?php
                        $otherInnings = $current
                            ? $innings->firstWhere(
                                'innings_number',
                                $current->innings_number === 1 ? 2 : 1
                            )
                            : null;
                    ?>

                    <p class="mt-2 text-4xl font-black sm:text-5xl">
                        <?php echo e($otherInnings ? $otherInnings->total_runs . '/' . $otherInnings->wickets : '—'); ?>

                    </p>

                    <p class="mt-2 text-sm text-indigo-100">
                        <?php echo e($otherInnings ? $otherInnings->oversText() . ' overs' : 'Other innings'); ?>

                    </p>
                </div>
            </div>

            <?php if($fixture->tossWinner): ?>
                <div class="border-t border-white/10 bg-white/5 px-5 py-3 text-sm text-indigo-100 sm:px-8">
                    Toss:
                    <strong class="text-white"><?php echo e($fixture->tossWinner->name); ?></strong>
                    chose to
                    <strong class="text-white"><?php echo e($fixture->toss_decision); ?></strong>.
                </div>
            <?php endif; ?>

            <?php if($current && $current->free_hit_next): ?>
                <div class="border-t border-orange-300/30 bg-orange-500 px-5 py-3 text-sm font-black text-white sm:px-8">
                    FREE HIT — next delivery
                </div>
            <?php endif; ?>

            <?php if($current && $current->innings_number === 2): ?>
                <?php
                    $firstInnings = $innings->firstWhere('innings_number', 1);
                ?>

                <?php if($firstInnings): ?>
                    <div class="border-t border-white/10 px-5 py-3 text-sm text-indigo-100 sm:px-8">
                        Target:
                        <strong class="text-white"><?php echo e($firstInnings->total_runs + 1); ?></strong>

                        <span class="mx-1">·</span>

                        Runs required:
                        <strong class="text-white">
                            <?php echo e(max(0, $firstInnings->total_runs + 1 - $current->total_runs)); ?>

                        </strong>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </section>

        <?php if($current): ?>

            
            <div class="grid gap-5 md:grid-cols-2">

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-black text-[#202766]">Batting</h2>
                        <span class="text-xs font-bold text-slate-400">R (B)</span>
                    </div>

                    <?php $__currentLoopData = $batters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-3 first:mt-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    <?php echo e($batter['name']); ?>


                                    <?php if($batter['name'] === $current->striker): ?>
                                        <span class="text-[#F06445]">*</span>
                                    <?php endif; ?>
                                </p>

                                <p class="text-xs text-slate-500">
                                    <?php echo e($batter['fours']); ?> fours
                                    ·
                                    <?php echo e($batter['sixes']); ?> sixes

                                    <?php if($batter['out']): ?>
                                        · <?php echo e($batter['dismissal'] ?: 'out'); ?>

                                    <?php endif; ?>
                                </p>
                            </div>

                            <p class="text-lg font-black text-slate-900">
                                <?php echo e($batter['runs']); ?>

                                <span class="text-sm font-semibold text-slate-400">
                                    (<?php echo e($batter['balls']); ?>)
                                </span>
                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-600">
                        Extras: <strong><?php echo e($current->extras); ?></strong>
                        <span class="mx-1">·</span>
                        Total:
                        <strong><?php echo e($current->total_runs); ?>/<?php echo e($current->wickets); ?></strong>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-black text-[#202766]">Bowling</h2>
                        <span class="text-xs font-bold text-slate-400">O · W / R</span>
                    </div>

                    <?php $__empty_1 = true; $__currentLoopData = $bowlers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bowler): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-3 first:mt-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    <?php echo e($bowler['name']); ?>

                                </p>

                                <p class="text-xs text-slate-500">
                                    <?php echo e(intdiv($bowler['legal_balls'], 6)); ?>.<?php echo e($bowler['legal_balls'] % 6); ?>

                                    overs
                                </p>
                            </div>

                            <p class="text-lg font-black text-slate-900">
                                <?php echo e($bowler['wickets']); ?>/<?php echo e($bowler['runs']); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="mt-4 text-sm text-slate-500">
                            Bowling figures appear after the first delivery.
                        </p>
                    <?php endif; ?>
                </section>

            </div>

            
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-black text-[#202766]">
                            Over-by-over
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Every delivery grouped by legal-ball count
                        </p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                        Auto-refresh · 10 sec
                    </span>
                </div>

                <?php if(count($overGroups)): ?>
                    <div class="divide-y divide-slate-100">

                        <?php $__currentLoopData = $overGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $overNumber => $overData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isCurrentOver = $loop->last;
                                $overLabel = $overNumber + 1;
                            ?>

                            <article class="p-5 sm:p-6">

                                
                                <div class="flex flex-wrap items-start justify-between gap-3">

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-black text-[#202766]">
                                                Over <?php echo e($overLabel); ?>

                                            </h3>

                                            <?php if($isCurrentOver && $overData['legal_balls'] < 6): ?>
                                                <span class="rounded-full bg-orange-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-orange-800">
                                                    In progress
                                                </span>
                                            <?php elseif($overData['legal_balls'] >= 6): ?>
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700">
                                                    Complete
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="mt-1 text-xs text-slate-500">
                                            <?php echo e($overData['legal_balls']); ?> legal
                                            <?php echo e($overData['legal_balls'] === 1 ? 'ball' : 'balls'); ?>


                                            <?php if($overData['wickets'] > 0): ?>
                                                · <?php echo e($overData['wickets']); ?>

                                                <?php echo e($overData['wickets'] === 1 ? 'wicket' : 'wickets'); ?>

                                            <?php endif; ?>
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-2xl font-black text-[#202766]">
                                            <?php echo e($overData['runs']); ?>

                                        </p>

                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                            Over runs
                                        </p>
                                    </div>
                                </div>

                                
                                <div class="mt-4 flex flex-wrap gap-2">

                                    <?php $__currentLoopData = $overData['balls']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ballItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $ball = $ballItem['delivery'];
                                            $extraType = $ball->extra_type ?? 'none';

                                            if ($ball->is_wicket) {
                                                $ballLabel = 'W';
                                                $ballStyle = 'border-rose-200 bg-rose-50 text-rose-700';
                                            } elseif ($extraType === 'wide') {
                                                $ballLabel = 'Wd';
                                                $ballStyle = 'border-orange-200 bg-orange-50 text-orange-800';
                                            } elseif ($extraType === 'no_ball') {
                                                $ballLabel = 'Nb';
                                                $ballStyle = 'border-orange-200 bg-orange-50 text-orange-800';
                                            } elseif ($extraType === 'bye') {
                                                $ballLabel = 'B';
                                                $ballStyle = 'border-violet-200 bg-violet-50 text-violet-800';
                                            } elseif ($extraType === 'leg_bye') {
                                                $ballLabel = 'Lb';
                                                $ballStyle = 'border-violet-200 bg-violet-50 text-violet-800';
                                            } elseif ($extraType === 'penalty') {
                                                $ballLabel = 'P';
                                                $ballStyle = 'border-amber-200 bg-amber-50 text-amber-800';
                                            } elseif ((int) $ball->batter_runs === 4) {
                                                $ballLabel = '4';
                                                $ballStyle = 'border-blue-200 bg-blue-50 text-blue-700';
                                            } elseif ((int) $ball->batter_runs === 6) {
                                                $ballLabel = '6';
                                                $ballStyle = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                            } else {
                                                $ballLabel = (string) $ball->batter_runs;
                                                $ballStyle = 'border-slate-200 bg-slate-50 text-[#202766]';
                                            }

                                            /*
                                             * Illegal deliveries display the next legal-ball
                                             * position, e.g. 2Wd, without advancing the over.
                                             */
                                            $deliveryPosition = $ballItem['position'];

                                            if (!$ballItem['is_legal']) {
                                                $deliveryPositionLabel = $deliveryPosition . (
                                                    $extraType === 'wide' ? 'Wd' : 'Nb'
                                                );
                                            } else {
                                                $deliveryPositionLabel = (string) $deliveryPosition;
                                            }
                                        ?>

                                        <div
                                            class="flex min-w-[58px] flex-col items-center justify-center rounded-xl border px-3 py-2.5 <?php echo e($ballStyle); ?>"
                                            title="<?php echo e($ball->striker ?? 'Batter'); ?> vs <?php echo e($ball->bowler ?? 'Bowler'); ?> — <?php echo e($ball->totalRuns()); ?> run(s)"
                                        >
                                            <span class="text-[10px] font-semibold opacity-60">
                                                <?php echo e($deliveryPositionLabel); ?>

                                            </span>

                                            <span class="mt-0.5 text-lg font-black leading-tight">
                                                <?php echo e($ballLabel); ?>

                                            </span>

                                            <span class="mt-1 text-[10px] font-semibold">
                                                <?php echo e($ball->totalRuns()); ?> <?php echo e($ball->totalRuns() === 1 ? 'run' : 'runs'); ?>

                                            </span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </div>

                                
                                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                    <span>
                                        Runs:
                                        <strong class="text-slate-800"><?php echo e($overData['runs']); ?></strong>
                                    </span>

                                    <span>
                                        Wickets:
                                        <strong class="text-slate-800"><?php echo e($overData['wickets']); ?></strong>
                                    </span>

                                    <span>
                                        Legal balls:
                                        <strong class="text-slate-800"><?php echo e($overData['legal_balls']); ?>/6</strong>
                                    </span>
                                </div>

                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </div>
                <?php else: ?>
                    <div class="px-5 py-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-[#202766]">
                            <span class="text-xl font-black">0</span>
                        </div>

                        <h3 class="mt-3 font-black text-slate-800">
                            No deliveries yet
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            The over-by-over scoreboard will appear when the scorer records the first delivery.
                        </p>
                    </div>
                <?php endif; ?>

                
                <div class="flex flex-wrap gap-x-4 gap-y-2 border-t border-slate-100 bg-slate-50 px-5 py-3 text-[11px] font-semibold text-slate-500 sm:px-6">
                    <span><strong class="text-blue-700">4</strong> Boundary</span>
                    <span><strong class="text-emerald-700">6</strong> Six</span>
                    <span><strong class="text-rose-700">W</strong> Wicket</span>
                    <span><strong class="text-orange-800">Wd</strong> Wide</span>
                    <span><strong class="text-orange-800">Nb</strong> No-ball</span>
                    <span><strong class="text-violet-800">B/Lb</strong> Byes / Leg-byes</span>
                </div>

            </section>

            
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-black text-[#202766]">
                    Innings summary
                </h2>

                <div class="mt-3 divide-y divide-slate-100">
                    <?php $__currentLoopData = $innings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    <?php echo e($inning->battingTeam->name ?? 'Batting team'); ?>

                                </p>

                                <p class="text-xs text-slate-500">
                                    Innings <?php echo e($inning->innings_number); ?>

                                    · <?php echo e($inning->oversText()); ?> overs
                                    · Extras <?php echo e($inning->extras); ?>

                                </p>
                            </div>

                            <p class="text-xl font-black text-[#202766]">
                                <?php echo e($inning->total_runs); ?>/<?php echo e($inning->wickets); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

        <?php else: ?>

            <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <h2 class="font-black text-slate-900">
                    Score not entered yet
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    The scorecard will appear when the scorer starts an innings.
                </p>
            </section>

        <?php endif; ?>

        <p class="text-xs leading-5 text-slate-500">
            Scores reflect deliveries entered by the match scorer.
            This page refreshes every 10 seconds.
        </p>

    </main>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/live/show.blade.php ENDPATH**/ ?>