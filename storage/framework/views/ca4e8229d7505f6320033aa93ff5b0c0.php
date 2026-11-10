<?php
    $season = $season ?? null;
?>

<?php if($errors->any()): ?>
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-bold">Please fix the following errors:</p>
        <ul class="mt-2 list-inside list-disc">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-2 block text-sm font-semibold">Season name *</label>
        <input id="name" name="name" required maxlength="100"
               value="<?php echo e(old('name', $season?->name)); ?>"
               placeholder="e.g. Nepal Premier League Season 3"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="season_number" class="mb-2 block text-sm font-semibold">Season number *</label>
        <input id="season_number" name="season_number" type="number"
               min="1" max="65535" required
               value="<?php echo e(old('season_number', $season?->season_number)); ?>"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
        <?php $__errorArgs = ['season_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="slug" class="mb-2 block text-sm font-semibold">URL slug</label>
        <input id="slug" name="slug" maxlength="120"
               value="<?php echo e(old('slug', $season?->slug)); ?>"
               placeholder="Leave blank to generate automatically"
               pattern="[a-z0-9]+(-[a-z0-9]+)*"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
        <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <p class="mt-1 text-xs text-slate-500">Use lowercase letters, numbers and hyphens.</p>
    </div>

    <div>
        <label for="status" class="mb-2 block text-sm font-semibold">Status *</label>
        <select id="status" name="status" required
                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600">
            <?php $__currentLoopData = ['upcoming' => 'Upcoming', 'active' => 'Active', 'completed' => 'Completed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>"
                    <?php if(old('status', $season?->status ?? 'upcoming') === $value): echo 'selected'; endif; ?>>
                    <?php echo e($label); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="start_date" class="mb-2 block text-sm font-semibold">Start date</label>
        <input id="start_date" name="start_date" type="date"
               value="<?php echo e(old('start_date', $season?->start_date?->format('Y-m-d'))); ?>"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3">
        <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="end_date" class="mb-2 block text-sm font-semibold">End date</label>
        <input id="end_date" name="end_date" type="date"
               value="<?php echo e(old('end_date', $season?->end_date?->format('Y-m-d'))); ?>"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3">
        <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-2 block text-sm font-semibold">Description</label>
        <textarea id="description" name="description" rows="5" maxlength="5000"
                  placeholder="Enter verified information about this season."
                  class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3"><?php echo e(old('description', $season?->description)); ?></textarea>
        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>

<div class="mt-8 flex flex-wrap gap-3">
    <button type="submit"
            class="rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700">
        <?php echo e($buttonText ?? 'Save season'); ?>

    </button>

    <a href="<?php echo e(route('admin.seasons.index')); ?>"
       class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold hover:bg-slate-100">
        Cancel
    </a>
</div><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/seasons/_form.blade.php ENDPATH**/ ?>