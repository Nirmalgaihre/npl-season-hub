<?php $__env->startSection('title', 'Edit Season | NPL Season Hub'); ?>

<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-4xl">
    <a href="<?php echo e(route('admin.seasons.index')); ?>"
       class="text-sm font-semibold text-slate-500 hover:text-red-600">
        ← Back to seasons
    </a>

    <h1 class="editorial-title mt-5 text-4xl font-black">Edit season</h1>
    <p class="mb-8 mt-2 text-slate-500">
        Update <?php echo e($season->name); ?>.
    </p>

    <form method="POST" action="<?php echo e(route('admin.seasons.update', $season)); ?>"
          class="rounded-xl border border-slate-200 bg-white p-6 md:p-8">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <?php echo $__env->make('admin.seasons._form', [
            'season' => $season,
            'buttonText' => 'Save changes'
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/seasons/edit.blade.php ENDPATH**/ ?>