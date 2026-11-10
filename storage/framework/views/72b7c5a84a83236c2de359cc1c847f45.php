<?php $__env->startSection('title', 'Players'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#f06445]">
                Cricket management
            </p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#1d0647]">
                Players
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Manage player profiles, teams and photos.
            </p>
        </div>

        <a href="<?php echo e(route('admin.players.create')); ?>"
           class="inline-flex items-center justify-center bg-[#1d0647] px-5 py-3 text-sm font-bold text-white hover:bg-[#2d0b68]">
            + Add player
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Filter Form with Team and Role Filter -->
    <form method="GET" action="<?php echo e(route('admin.players.index')); ?>"
          class="grid gap-3 border border-slate-200 bg-white p-4 md:grid-cols-[1fr_180px_180px_auto]">
        <input
            type="search"
            name="search"
            value="<?php echo e(request('search')); ?>"
            placeholder="Search player name or nationality..."
            class="w-full border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#1d0647]"
        >

        <!-- Filter by Team -->
        <select name="team_id"
                class="w-full border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#1d0647]">
            <option value="">All teams</option>
            <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($team->id); ?>" <?php if((string) request('team_id') === (string) $team->id): echo 'selected'; endif; ?>>
                    <?php echo e($team->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <!-- Filter by Role -->
        <select name="role"
                class="w-full border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#1d0647]">
            <option value="">All roles</option>
            <option value="batsman" <?php if(request('role') === 'batsman'): echo 'selected'; endif; ?>>Batsman</option>
            <option value="bowler" <?php if(request('role') === 'bowler'): echo 'selected'; endif; ?>>Bowler</option>
            <option value="all_rounder" <?php if(request('role') === 'all_rounder'): echo 'selected'; endif; ?>>All Rounder</option>
            <option value="wicket_keeper" <?php if(request('role') === 'wicket_keeper'): echo 'selected'; endif; ?>>Wicket Keeper</option>
        </select>

        <button class="bg-[#1d0647] px-5 py-3 text-sm font-bold text-white">
            Filter
        </button>
    </form>

    <div class="overflow-hidden border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Player</th>
                        <th class="px-5 py-4">Team</th>
                        <th class="px-5 py-4">Role</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="<?php echo e($player->photo_url); ?>"
                                        alt="<?php echo e($player->name); ?>"
                                        class="h-12 w-12 rounded-full border border-slate-200 object-cover"
                                    >
                                    <div>
                                        <p class="font-bold text-[#1d0647]"><?php echo e($player->name); ?></p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            <?php echo e($player->nationality ?: 'Nationality not set'); ?>

                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <?php echo e($player->team?->name ?? 'Unassigned'); ?>

                            </td>

                            <td class="px-5 py-4">
                                <?php echo e($player->role
                                    ? ucwords(str_replace('_', ' ', $player->role))
                                    : 'Batsman'); ?> <!-- Default display fallback -->
                            </td>

                            <td class="px-5 py-4">
                                <?php if($player->is_active): ?>
                                    <span class="bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="<?php echo e(route('admin.players.edit', $player)); ?>"
                                       class="font-bold text-[#1d0647] hover:underline">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="<?php echo e(route('admin.players.destroy', $player)); ?>"
                                          onsubmit="return confirm('Delete this player profile?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                                class="font-bold text-red-600 hover:underline">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="font-bold text-slate-700">No players found</p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Add a player or change your search filters.
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-5 py-4">
            <?php echo e($players->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/players/index.blade.php ENDPATH**/ ?>