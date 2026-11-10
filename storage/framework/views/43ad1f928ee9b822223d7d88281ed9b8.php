<?php
    $home = $match->homeTeam;
    $away = $match->awayTeam;
    $verified = (bool) $match->is_result_verified;

    $statusStyles = [
        'live' => 'bg-red-50 text-red-700',
        'scheduled' => 'bg-blue-50 text-blue-700',
        'completed' => 'bg-emerald-50 text-emerald-700',
        'postponed' => 'bg-orange-50 text-orange-700',
        'abandoned' => 'bg-slate-100 text-slate-600',
    ];
?>

<article class="overflow-hidden border border-slate-200 bg-white transition hover:border-[#202766]/40">

    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#F06445]">
                <?php echo e($match->season->name); ?>

            </p>
            <p class="mt-1 text-xs text-slate-500">
                Match <?php echo e($match->match_number); ?>

                · <?php echo e(ucfirst($match->match_type)); ?>

            </p>
        </div>

        <span class="shrink-0 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider <?php echo e($statusStyles[$match->status] ?? 'bg-slate-100 text-slate-600'); ?>">
            <?php echo e($match->status === 'scheduled' ? 'Not Started Yet' : ucfirst($match->status)); ?>

        </span>
    </div>

    <div class="p-5">

        
        <div class="flex items-center gap-3">
            <?php if($home->logo): ?>
                <img src="<?php echo e(asset('storage/' . $home->logo)); ?>"
                     alt="<?php echo e($home->name); ?>"
                     class="h-10 w-10 shrink-0 object-contain">
            <?php else: ?>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#F6F8FB] text-xs font-extrabold text-[#202766]">
                    <?php echo e(strtoupper(substr($home->short_name ?: $home->name, 0, 3))); ?>

                </div>
            <?php endif; ?>

            <div class="min-w-0 flex-1">
                <p class="break-words text-sm font-bold text-[#202766]">
                    <?php echo e($home->name); ?>

                </p>
            </div>

            <?php if($verified): ?>
                <p class="shrink-0 text-base font-extrabold text-[#202766]">
                    <?php echo e($match->home_runs ?? 0); ?>/<?php echo e($match->home_wickets ?? 0); ?>

                </p>
            <?php endif; ?>
        </div>

        <div class="my-4 border-t border-dashed border-slate-200"></div>

        
        <div class="flex items-center gap-3">
            <?php if($away->logo): ?>
                <img src="<?php echo e(asset('storage/' . $away->logo)); ?>"
                     alt="<?php echo e($away->name); ?>"
                     class="h-10 w-10 shrink-0 object-contain">
            <?php else: ?>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#F6F8FB] text-xs font-extrabold text-[#202766]">
                    <?php echo e(strtoupper(substr($away->short_name ?: $away->name, 0, 3))); ?>

                </div>
            <?php endif; ?>

            <div class="min-w-0 flex-1">
                <p class="break-words text-sm font-bold text-[#202766]">
                    <?php echo e($away->name); ?>

                </p>
            </div>

            <?php if($verified): ?>
                <p class="shrink-0 text-base font-extrabold text-[#202766]">
                    <?php echo e($match->away_runs ?? 0); ?>/<?php echo e($match->away_wickets ?? 0); ?>

                </p>
            <?php endif; ?>
        </div>

        
        <?php if($verified && $match->winner): ?>
            <div class="mt-5 border-l-4 border-[#F06445] bg-[#F6F8FB] px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Winner
                </p>
                <p class="mt-1 text-sm font-extrabold text-[#202766]">
                    <?php echo e($match->winner->name); ?>

                </p>
                <?php if($match->result_summary): ?>
                    <p class="mt-1 text-xs leading-5 text-slate-600">
                        <?php echo e($match->result_summary); ?>

                    </p>
                <?php endif; ?>
            </div>
        <?php elseif($match->status === 'scheduled'): ?>
            <p class="mt-5 text-sm text-slate-500">
                <?php echo e($match->scheduled_at?->format('D, d M Y · h:i A') ?? 'Date and time to be announced'); ?>

            </p>
        <?php elseif($match->status === 'live' && !$verified): ?>
            <p class="mt-5 text-sm font-semibold text-red-600">
                Match in progress · Official score unavailable
            </p>
        <?php elseif($match->status === 'completed' && !$verified): ?>
            <p class="mt-5 text-sm text-slate-500">
                Result awaiting verification
            </p>
        <?php else: ?>
            <p class="mt-5 text-sm text-slate-500">
                <?php echo e($match->scheduled_at?->format('D, d M Y · h:i A') ?? 'Schedule information unavailable'); ?>

            </p>
        <?php endif; ?>

        <div class="mt-5 border-t border-slate-100 pt-4">
            <a href="<?php echo e(route('fixtures.show', $match)); ?>"
               class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] hover:text-[#F06445]">
                View match details <span aria-hidden="true">→</span>
            </a>
        </div>

    </div>
</article><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/teams/partials/match-card.blade.php ENDPATH**/ ?>