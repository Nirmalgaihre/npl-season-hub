<?php $__env->startSection('title', 'Edit Player'); ?>

<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-3xl">
    <a href="<?php echo e(route('admin.players.index')); ?>"
       class="text-sm font-bold text-[#1d0647] hover:underline">
        ← Back to players
    </a>

    <h1 class="mt-5 text-3xl font-extrabold text-[#1d0647]">Edit player</h1>
    <p class="mt-2 text-sm text-slate-500">
        Update <?php echo e($player->name); ?>'s profile and photo.
    </p>

    <?php echo $__env->make('admin.players._form', [
        'action' => route('admin.players.update', $player),
        'method' => 'PUT',
        'player' => $player,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/players/edit.blade.php ENDPATH**/ ?>