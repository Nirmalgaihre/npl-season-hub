<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
    <div class="mb-7">
        <a href="<?php echo e(route('admin.fixtures.index')); ?>"
           class="text-sm font-semibold text-red-700 hover:underline">
            ← Back to fixtures
        </a>
        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900">
            Edit Match #<?php echo e($fixture->match_number); ?>

        </h1>
        <p class="mt-2 text-slate-500">
            Update the schedule, status or official match result.
        </p>
    </div>

    <form action="<?php echo e(route('admin.fixtures.update', $fixture)); ?>" method="POST" class="space-y-6">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <?php echo $__env->make('admin.fixtures._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/fixtures/edit.blade.php ENDPATH**/ ?>