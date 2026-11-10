<?php $__env->startSection('title', 'Live Match Manager'); ?>
<?php $__env->startSection('page_heading', 'Live Match Manager'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                Live Match Manager
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Select a fixture to manage its live score.
            </p>
        </div>

        <a href="<?php echo e(route('admin.fixtures.index')); ?>"
           class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Manage Fixtures
        </a>
    </div>

    
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold text-slate-900">Fixtures</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Choose a match to open its scoring controls.
                </p>
            </div>

            <span class="text-sm text-slate-500">
                <?php echo e($fixtures->total()); ?> fixtures
            </span>
        </div>

        <?php if($fixtures->count()): ?>
            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $fixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex flex-col gap-4 px-5 py-5 transition hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    Fixture #<?php echo e($fixture->id); ?>

                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <span class="font-semibold text-slate-900">
                                    <?php echo e($fixture->homeTeam?->name ?? 'Home team'); ?>

                                </span>

                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    VS
                                </span>

                                <span class="font-semibold text-slate-900">
                                    <?php echo e($fixture->awayTeam?->name ?? 'Away team'); ?>

                                </span>
                            </div>

                            <?php if($fixture->created_at): ?>
                                <p class="mt-2 text-xs text-slate-500">
                                    Added <?php echo e($fixture->created_at->format('d M Y')); ?>

                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="<?php echo e(route('admin.live.admin', $fixture)); ?>"
                               class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                               style="background-color: #202766;">
                                Manage Score
                            </a>

                            <a href="<?php echo e(route('live.show', $fixture)); ?>"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Public Scoreboard
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                <?php echo e($fixtures->links()); ?>

            </div>
        <?php else: ?>
            <div class="px-6 py-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6 text-slate-500"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M8 12h8m-4-4v8m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h3 class="mt-4 text-base font-semibold text-slate-900">
                    No fixtures found
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Create a fixture before starting live scoring.
                </p>

                <a href="<?php echo e(route('admin.fixtures.index')); ?>"
                   class="mt-5 inline-flex rounded-lg px-4 py-2.5 text-sm font-semibold text-white"
                   style="background-color: #202766;">
                    Open Fixture Manager
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/live/index.blade.php ENDPATH**/ ?>