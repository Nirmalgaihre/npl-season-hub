<?php $__env->startSection('title', 'Manage Sponsors | NPL Season Hub'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-red-700">
                    NPL Season Hub / Administration
                </p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                    Sponsors &amp; Partners
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Manage sponsors separately for each NPL season.
                </p>
            </div>

            <a href="<?php echo e(route('home')); ?>#official-partners"
               target="_blank"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                Preview Homepage ↗
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-bold">Please correct the following:</p>
                <ul class="mt-2 list-inside list-disc">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="<?php echo e(route('admin.sponsors.index')); ?>"
                  class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="season_id" class="mb-2 block text-sm font-semibold text-slate-700">
                        Select season
                    </label>
                    <select id="season_id" name="season_id" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm focus:border-red-600 focus:outline-none">
                        <?php $__empty_1 = true; $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <option value="<?php echo e($item->id); ?>"
                                <?php if((int) ($selectedSeason?->id) === (int) $item->id): echo 'selected'; endif; ?>>
                                <?php echo e($item->name); ?> — Season <?php echo e($item->season_number); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option value="">No seasons available</option>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit"
                        class="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                    Load Season
                </button>
            </form>
        </div>

        <?php if(!$selectedSeason): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                Create a season first, then return here to add its sponsors.
            </div>
        <?php else: ?>

            <?php
                $isEditing = (bool) $editingSponsor;
                $formAction = $isEditing
                    ? route('admin.sponsors.update', $editingSponsor)
                    : route('admin.sponsors.store');
            ?>

            <div class="mb-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900">
                        <?php echo e($isEditing ? 'Edit Sponsor' : 'Add Sponsor'); ?>

                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        <?php echo e($selectedSeason->name); ?> · Season <?php echo e($selectedSeason->season_number); ?>

                    </p>
                </div>

                <form action="<?php echo e($formAction); ?>" method="POST"
                      enctype="multipart/form-data" class="space-y-5 p-5">
                    <?php echo csrf_field(); ?>
                    <?php if($isEditing): ?>
                        <?php echo method_field('PUT'); ?>
                    <?php endif; ?>

                    <input type="hidden" name="season_id" value="<?php echo e($selectedSeason->id); ?>">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">
                                Sponsor / Partner Name *
                            </label>
                            <input id="name" name="name" required maxlength="150"
                                   value="<?php echo e(old('name', $editingSponsor?->name)); ?>"
                                   placeholder="e.g. Siddhartha Bank"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm focus:border-red-600 focus:outline-none">
                        </div>

                        <div>
                            <label for="category" class="mb-2 block text-sm font-semibold text-slate-700">
                                Sponsor Category *
                            </label>
                            <select id="category" name="category" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm focus:border-red-600 focus:outline-none">
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category); ?>"
                                        <?php if(old('category', $editingSponsor?->category ?? 'Official Partner') === $category): echo 'selected'; endif; ?>>
                                        <?php echo e($category); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label for="logo" class="mb-2 block text-sm font-semibold text-slate-700">
                                Logo (JPG, PNG or WebP; max 3 MB)
                            </label>
                            <input id="logo" name="logo" type="file"
                                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm">
                            <?php if($editingSponsor?->logo_path): ?>
                                <div class="mt-3 flex items-center gap-3">
                                    <img src="<?php echo e(asset('storage/' . $editingSponsor->logo_path)); ?>"
                                         alt="<?php echo e($editingSponsor->name); ?>"
                                         class="h-14 w-24 rounded border border-slate-200 bg-white object-contain p-1">
                                    <span class="text-xs text-slate-500">Current logo</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="website_url" class="mb-2 block text-sm font-semibold text-slate-700">
                                Website URL
                            </label>
                            <input id="website_url" name="website_url" type="url" maxlength="2048"
                                   value="<?php echo e(old('website_url', $editingSponsor?->website_url)); ?>"
                                   placeholder="https://example.com"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm focus:border-red-600 focus:outline-none">
                        </div>

                        <div>
                            <label for="sort_order" class="mb-2 block text-sm font-semibold text-slate-700">
                                Display Order
                            </label>
                            <input id="sort_order" name="sort_order" type="number" min="0" max="100000"
                                   required value="<?php echo e(old('sort_order', $editingSponsor?->sort_order ?? 0)); ?>"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm focus:border-red-600 focus:outline-none">
                            <p class="mt-1 text-xs text-slate-500">Smaller numbers appear first.</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Visibility
                            </label>
                            <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                                <input type="checkbox" name="is_active" value="1"
                                       <?php if(old('is_active', $editingSponsor?->is_active ?? true)): echo 'checked'; endif; ?>
                                       class="h-4 w-4 rounded border-slate-300 text-red-700 focus:ring-red-600">
                                <span class="text-sm text-slate-700">Show on homepage</span>
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">
                                Short Description
                            </label>
                            <textarea id="description" name="description" rows="3" maxlength="5000"
                                      placeholder="Optional description of this sponsor or its role."
                                      class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm focus:border-red-600 focus:outline-none"><?php echo e(old('description', $editingSponsor?->description)); ?></textarea>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">
                        <button type="submit"
                                class="rounded-lg bg-red-700 px-5 py-3 text-sm font-bold text-white hover:bg-red-800">
                            <?php echo e($isEditing ? 'Save Changes' : 'Add Sponsor'); ?>

                        </button>

                        <?php if($isEditing): ?>
                            <a href="<?php echo e(route('admin.sponsors.index', ['season_id' => $selectedSeason->id])); ?>"
                               class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Cancel Editing
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Season Sponsors</h2>
                        <p class="mt-1 text-sm text-slate-500">Only sponsors assigned to this season appear here.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                        <?php echo e($sponsors->count()); ?> records
                    </span>
                </div>

                <?php if($sponsors->isEmpty()): ?>
                    <div class="px-6 py-12 text-center">
                        <div class="text-3xl">🏏</div>
                        <h3 class="mt-3 font-bold text-slate-900">No sponsors added yet</h3>
                        <p class="mt-1 text-sm text-slate-500">Add the first sponsor using the form above.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Sponsor</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__currentLoopData = $sponsors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sponsor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <?php if($sponsor->logo_path): ?>
                                                    <img src="<?php echo e(asset('storage/' . $sponsor->logo_path)); ?>"
                                                         alt="<?php echo e($sponsor->name); ?>"
                                                         class="h-12 w-16 rounded border border-slate-200 bg-white object-contain p-1">
                                                <?php else: ?>
                                                    <div class="flex h-12 w-16 items-center justify-center rounded bg-slate-100 text-xs font-bold text-slate-500">
                                                        <?php echo e(strtoupper(substr($sponsor->name, 0, 3))); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="font-semibold text-slate-900"><?php echo e($sponsor->name); ?></div>
                                                    <?php if($sponsor->website_url): ?>
                                                        <a href="<?php echo e($sponsor->website_url); ?>" target="_blank" rel="noopener noreferrer"
                                                           class="text-xs text-blue-700 hover:underline">Visit website ↗</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-slate-600"><?php echo e($sponsor->category); ?></td>
                                        <td class="px-4 py-4 text-sm text-slate-600"><?php echo e($sponsor->sort_order); ?></td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($sponsor->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600'); ?>">
                                                <?php echo e($sponsor->is_active ? 'Active' : 'Hidden'); ?>

                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                            <a href="<?php echo e(route('admin.sponsors.index', ['season_id' => $selectedSeason->id, 'edit' => $sponsor->id])); ?>"
                                               class="font-semibold text-red-700 hover:text-red-900">Edit</a>

                                            <form action="<?php echo e(route('admin.sponsors.destroy', $sponsor)); ?>"
                                                  method="POST" class="ml-3 inline"
                                                  onsubmit="return confirm('Delete this sponsor permanently?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="font-semibold text-red-700 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/sponsors/index.blade.php ENDPATH**/ ?>