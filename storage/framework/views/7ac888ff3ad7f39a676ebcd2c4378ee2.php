<?php $__env->startSection('title', 'Manage Teams | NPL Season Hub'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-red-700">NPL administration</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Teams</h1>
                <p class="mt-2 text-sm text-slate-600">Manage franchise profiles and season participation.</p>
            </div>
            <a href="<?php echo e(route('admin.teams.create')); ?>"
                class="rounded-lg bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800">
                + Add team
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="my-5 rounded-lg bg-green-50 p-4 text-sm text-green-800"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="my-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Total teams</p>
                <p class="mt-2 text-3xl font-black"><?php echo e(\App\Models\Team::count()); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Active teams</p>
                <p class="mt-2 text-3xl font-black"><?php echo e(\App\Models\Team::where('is_active', true)->count()); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Inactive teams</p>
                <p class="mt-2 text-3xl font-black"><?php echo e(\App\Models\Team::where('is_active', false)->count()); ?></p>
            </div>
        </div>

        <form method="GET" class="mb-5 flex flex-wrap gap-3 rounded-xl border border-slate-200 bg-white p-4">
            <input name="search" value="<?php echo e(request('search')); ?>" placeholder="Search team name..."
                class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2.5">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2.5">
                <option value="">All statuses</option>
                <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Active</option>
                <option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2.5 font-semibold text-white">Filter</button>
            <a href="<?php echo e(route('admin.teams.index')); ?>" class="rounded-lg border border-slate-300 px-4 py-2.5">Reset</a>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Team</th>
                        <th class="px-5 py-4">Home venue</th>
                        <th class="px-5 py-4">Seasons</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if($team->logo): ?>
                                        <img src="<?php echo e(asset('storage/'.$team->logo)); ?>" alt="" class="h-12 w-12 rounded-lg border border-slate-200 object-contain p-1">
                                    <?php else: ?>
                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 font-black text-red-700">
                                            <?php echo e(strtoupper(substr($team->short_name ?: $team->name, 0, 2))); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-bold text-slate-900"><?php echo e($team->name); ?></p>
                                        <p class="mt-1 text-xs text-slate-500"><?php echo e($team->short_name ?: $team->slug); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e($team->home_venue ?: 'Not specified'); ?></td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <?php $__empty_2 = true; $__currentLoopData = $team->seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs"><?php echo e($season->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                        <span class="text-xs text-slate-400">None assigned</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold <?php echo e($team->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-600'); ?>">
                                    <?php echo e($team->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <a href="<?php echo e(route('admin.teams.edit', $team)); ?>" class="font-semibold text-red-700 hover:underline">Edit</a>
                                <form method="POST" action="<?php echo e(route('admin.teams.destroy', $team)); ?>" class="ml-3 inline"
                                    onsubmit="return confirm('Delete this team?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="font-semibold text-slate-600 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <p class="text-lg font-bold text-slate-900">No teams added yet</p>
                                <p class="mt-2 text-sm text-slate-500">Add verified team information to get started.</p>
                                <a href="<?php echo e(route('admin.teams.create')); ?>" class="mt-4 inline-block font-semibold text-red-700">Add a team →</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="border-t border-slate-200 p-4"><?php echo e($teams->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/teams/index.blade.php ENDPATH**/ ?>