<?php $__env->startSection('title', $season->name . ' | NPL Season Hub'); ?>

<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-4xl">
    <a href="<?php echo e(route('admin.seasons.index')); ?>"
       class="text-sm font-semibold text-slate-500 hover:text-red-600">
        ← Back to seasons
    </a>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 md:p-10">
        <p class="text-sm font-bold uppercase tracking-widest text-red-600">
            Season <?php echo e($season->season_number); ?>

        </p>

        <h1 class="editorial-title mt-3 text-4xl font-black md:text-5xl">
            <?php echo e($season->name); ?>

        </h1>

        <div class="mt-6">
            <span class="rounded-full bg-slate-100 px-3 py-2 text-sm font-semibold">
                <?php echo e(ucfirst($season->status)); ?>

            </span>
        </div>

        <dl class="mt-8 grid gap-6 border-t border-slate-200 pt-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm text-slate-500">Start date</dt>
                <dd class="mt-1 font-semibold">
                    <?php echo e($season->start_date?->format('d F Y') ?? 'Not set'); ?>

                </dd>
            </div>

            <div>
                <dt class="text-sm text-slate-500">End date</dt>
                <dd class="mt-1 font-semibold">
                    <?php echo e($season->end_date?->format('d F Y') ?? 'Not set'); ?>

                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm text-slate-500">URL slug</dt>
                <dd class="mt-1 break-all font-semibold"><?php echo e($season->slug); ?></dd>
            </div>
        </dl>

        <?php if($season->description): ?>
            <div class="mt-8 border-t border-slate-200 pt-6">
                <h2 class="text-lg font-bold">Description</h2>
                <p class="mt-3 whitespace-pre-line leading-7 text-slate-700"><?php echo e($season->description); ?></p>
            </div>
        <?php endif; ?>

        <div class="mt-8">
            <a href="<?php echo e(route('admin.seasons.edit', $season)); ?>"
               class="inline-flex rounded-lg bg-red-600 px-5 py-3 font-bold text-white hover:bg-red-700">
                Edit season
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/seasons/show.blade.php ENDPATH**/ ?>