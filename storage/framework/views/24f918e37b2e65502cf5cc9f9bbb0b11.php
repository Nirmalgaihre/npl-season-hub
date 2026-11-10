<?php $__env->startSection('title', 'Articles | NPL Season Hub'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-red-700">Content management</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">News & Articles</h1>
                <p class="mt-2 text-sm text-slate-600">Manage your NPL news coverage and match reports.</p>
            </div>
            <a href="<?php echo e(route('admin.articles.create')); ?>" class="rounded-lg bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800">+ Write article</a>
        </div>

        <div class="my-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <?php $__currentLoopData = [
                ['All articles', \App\Models\Article::count()],
                ['Published', \App\Models\Article::where('status', 'published')->count()],
                ['Drafts', \App\Models\Article::where('status', 'draft')->count()],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm text-slate-500"><?php echo e($stat[0]); ?></p>
                    <p class="mt-2 text-3xl font-black text-slate-900"><?php echo e($stat[1]); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <form class="mb-5 flex flex-wrap gap-3 rounded-xl border border-slate-200 bg-white p-4" method="GET">
            <input name="search" value="<?php echo e(request('search')); ?>" placeholder="Search article title..." class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2">
                <option value="">All statuses</option>
                <option value="draft" <?php if(request('status') === 'draft'): echo 'selected'; endif; ?>>Draft</option>
                <option value="published" <?php if(request('status') === 'published'): echo 'selected'; endif; ?>>Published</option>
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white">Filter</button>
            <a href="<?php echo e(route('admin.articles.index')); ?>" class="rounded-lg border border-slate-300 px-4 py-2">Reset</a>
        </form>

        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>
        <?php if(session('success')): ?>
            <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Article</th>
                        <th class="px-5 py-4">Category</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Updated</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900"><?php echo e($article->title); ?></p>
                                <p class="mt-1 text-xs text-slate-500">By <?php echo e($article->author?->name ?? 'Former author'); ?></p>
                            </td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e($article->category?->name ?? 'Uncategorized'); ?></td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold <?php echo e($article->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'); ?>">
                                    <?php echo e(ucfirst($article->status)); ?>

                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500"><?php echo e($article->updated_at->format('d M Y')); ?></td>
                            <td class="px-5 py-4 text-right">
                                <a class="font-semibold text-red-700 hover:underline" href="<?php echo e(route('admin.articles.edit', $article)); ?>">Edit</a>
                                <form class="ml-3 inline" method="POST" action="<?php echo e(route('admin.articles.destroy', $article)); ?>" onsubmit="return confirm('Delete this article permanently?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="font-semibold text-slate-600 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="px-5 py-16 text-center">
                            <p class="text-lg font-bold text-slate-800">No articles yet</p>
                            <p class="mt-2 text-slate-500">Your real articles will appear here once you create them.</p>
                            <a href="<?php echo e(route('admin.articles.create')); ?>" class="mt-4 inline-block font-semibold text-red-700">Write your first article →</a>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="border-t border-slate-200 p-4"><?php echo e($articles->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/articles/index.blade.php ENDPATH**/ ?>