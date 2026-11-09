<?php $__env->startSection('title', 'Point Table Management | Admin'); ?>

<?php $__env->startSection('content'); ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    .pa-display { font-family: 'Anton', Impact, sans-serif; }
    .pa-body    { font-family: 'Archivo', system-ui, sans-serif; }
    .pa-tabular { font-variant-numeric: tabular-nums; }
</style>

<div class="pa-body min-h-screen bg-[#F5F1EB] text-[#0B1B3A]">

    <section class="bg-[#0B1B3A] text-white">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
            <p class="text-[10px] font-extrabold uppercase tracking-[0.28em] text-[#F06445]">Admin</p>
            <h1 class="pa-display mt-2 text-4xl leading-none sm:text-5xl">POINT TABLE</h1>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <?php if($seasons->count() > 1): ?>
                    <form method="GET" action="<?php echo e(route('admin.pointtable.index')); ?>">
                        <select name="season" onchange="this.form.submit()"
                                class="rounded-lg border border-white/20 bg-white/10 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                            <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s->id); ?>" <?php if($s->id === $selectedSeason?->id): echo 'selected'; endif; ?> class="text-[#0B1B3A]">
                                    <?php echo e($s->name ?? 'Season ' . $s->id); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                <?php endif; ?>
                <span class="text-xs font-semibold text-white/60">
                    NR points: <?php echo e($noResultPoints); ?>

                </span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">

        <?php if(session('success')): ?>
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-800">
                <ul class="list-inside list-disc space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <li><?php echo e($e); ?></li> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.pointtable.update')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <input type="hidden" name="season_id" value="<?php echo e($selectedSeason?->id); ?>">

            <div class="overflow-hidden rounded-2xl bg-white shadow ring-1 ring-black/5">
                <div class="hidden grid-cols-[60px_1fr_repeat(6,60px)_200px] items-center border-b-2 border-black/10 bg-[#FAF6F0] px-5 py-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-500 lg:grid">
                    <span>#</span>
                    <span>Team</span>
                    <span class="text-center">P</span>
                    <span class="text-center">W</span>
                    <span class="text-center">L</span>
                    <span class="text-center">T</span>
                    <span class="text-center">NR</span>
                    <span class="text-center">Pts</span>
                    <span>Penalty · Notes</span>
                </div>

                <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $team = $row['team']; $adj = $adjustments->get($team->id); ?>
                    <div class="grid grid-cols-1 items-center gap-3 border-b border-black/5 px-5 py-4 last:border-0 lg:grid-cols-[60px_1fr_repeat(6,60px)_200px] lg:gap-0">

                        <span class="pa-tabular pa-display inline-flex h-8 w-8 items-center justify-center rounded-md bg-[#0B1B3A]/5 text-base text-[#0B1B3A]">
                            <?php echo e($index + 1); ?>

                        </span>

                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-50 p-1.5">
                                <?php if($team->logo): ?>
                                    <img src="<?php echo e(asset('storage/' . $team->logo)); ?>" alt="" class="h-full w-full object-contain">
                                <?php else: ?>
                                    <span class="text-[10px] font-black"><?php echo e(strtoupper(substr($team->short_name ?? $team->name, 0, 3))); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm font-extrabold"><?php echo e($team->name); ?></p>
                        </div>

                        <span class="pa-tabular hidden text-center text-sm font-bold lg:block"><?php echo e($row['played']); ?></span>
                        <span class="pa-tabular hidden text-center text-sm font-bold text-emerald-700 lg:block"><?php echo e($row['won']); ?></span>
                        <span class="pa-tabular hidden text-center text-sm font-bold text-rose-600 lg:block"><?php echo e($row['lost']); ?></span>
                        <span class="pa-tabular hidden text-center text-sm font-bold lg:block"><?php echo e($row['tied']); ?></span>
                        <span class="pa-tabular hidden text-center text-sm font-bold lg:block"><?php echo e($row['no_result']); ?></span>
                        <span class="pa-tabular hidden text-center text-base font-black text-[#0B1B3A] lg:block"><?php echo e($row['points']); ?></span>

                        <div class="grid grid-cols-[80px_1fr] gap-2 lg:block">
                            <input type="hidden" name="adjustments[<?php echo e($loop->index); ?>][team_id]" value="<?php echo e($team->id); ?>">
                            <input type="number" min="0" max="100"
                                   name="adjustments[<?php echo e($loop->index); ?>][penalty_points]"
                                   value="<?php echo e($adj?->penalty_points ?? 0); ?>"
                                   class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm font-bold tabular-nums">
                            <input type="text"
                                   name="adjustments[<?php echo e($loop->index); ?>][notes]"
                                   value="<?php echo e($adj?->notes); ?>"
                                   placeholder="Reason"
                                   class="mt-1 w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs lg:mt-1">
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-[#F06445] px-6 py-3 text-sm font-extrabold uppercase tracking-wide text-white transition hover:bg-[#0B1B3A]">
                    Save penalties
                </button>
            </div>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/pointtable/index.blade.php ENDPATH**/ ?>