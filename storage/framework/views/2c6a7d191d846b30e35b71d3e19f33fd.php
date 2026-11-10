<?php $__env->startSection('title', 'Write Article | NPL Season Hub'); ?>

<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <a href="<?php echo e(route('admin.articles.index')); ?>" class="text-sm font-semibold text-red-700">← All articles</a>
    <h1 class="mb-2 mt-4 text-3xl font-black text-slate-900">Write an article</h1>
    <p class="mb-7 text-sm text-slate-600">Write accurate, original NPL news and match coverage.</p>

    <?php if($errors->any()): ?>
        <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.articles.store')); ?>" method="POST" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
        <?php echo $__env->make('admin.articles._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/articles/create.blade.php ENDPATH**/ ?>