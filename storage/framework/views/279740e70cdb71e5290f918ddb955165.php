<?php $__env->startSection('title', 'Live Match Manager'); ?>
<?php $__env->startSection('page_heading', 'Live Match Manager'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <section class="rounded-2xl p-6 text-white shadow-sm sm:p-8"
             style="background:linear-gradient(120deg,#171D50,#202766);">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-[.2em] text-orange-300">
                    Match operations
                </p>
                <h2 class="text-2xl font-extrabold sm:text-3xl">
                    Live scoring control centre
                </h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-slate-300">
                    Select a fixture to enter or manage its innings and match score.
                </p>
            </div>

            <a href="<?php echo e(route('admin.fixtures.index')); ?>"
               class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white"
               style="background:#F06445;">
                View fixtures &rarr;
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900">Available fixtures</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Select a match to open its scoring screen.
                </p>
            </div>

            <span class="text-xs font-semibold text-slate-500">
                <?php echo e($fixtures->total()); ?> fixture(s)
            </span>
        </div>

        <?php if($fixtures->isEmpty()): ?>
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">
                    &#127951;
                </div>
                <h4 class="mt-4 font-bold text-slate-900">No fixtures available</h4>
                <p class="mt-2 text-sm text-slate-500">
                    Create a fixture before starting live scoring.
                </p>
                <a href="<?php echo e(route('admin.fixtures.index')); ?>"
                   class="mt-5 inline-flex rounded-xl px-4 py-3 text-sm font-bold text-white"
                   style="background:#202766;">
                    Manage fixtures
                </a>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $fixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="flex flex-col gap-4 px-5 py-5 transition hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between">

                        <div class="min-w-0 flex-1">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-600">
                                    Fixture #<?php echo e($fixture->id); ?>

                                </span>

                                <?php if(isset($fixture->status) && strtolower((string) $fixture->status) === 'live'): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-red-50 px-2.5 py-1 text-[10px] font-extrabold uppercase text-red-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Live
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <h4 class="text-base font-extrabold text-slate-900">
                                    <?php echo e($fixture->homeTeam?->name ?? 'Home team unavailable'); ?>

                                </h4>

                                <span class="text-xs font-black text-slate-400">VS</span>

                                <h4 class="text-base font-extrabold text-slate-900">
                                    <?php echo e($fixture->awayTeam?->name ?? 'Away team unavailable'); ?>

                                </h4>
                            </div>

                            <p class="mt-2 text-xs text-slate-500">
                                Select Manage Score to open this fixture's scoring page.
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-2">
                            <a href="<?php echo e(route('admin.live.score', $fixture)); ?>"
                               class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-xs font-extrabold text-white transition hover:opacity-90"
                               style="background:#202766;">
                                &#9998; Manage Score
                            </a>

                            <?php if(\Illuminate\Support\Facades\Route::has('live.show')): ?>
                                <a href="<?php echo e(route('live.show', $fixture)); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                    Public view &#8599;
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="border-t border-slate-100 px-5 py-4">
                <?php echo e($fixtures->links()); ?>

            </div>
        <?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/live/index.blade.php ENDPATH**/ ?>