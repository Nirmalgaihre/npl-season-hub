<?php $__env->startSection('title', 'NPL Admin Dashboard'); ?>
<?php $__env->startSection('page_heading', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen space-y-7 bg-white pb-10">

    
    <section class="relative overflow-hidden rounded-3xl bg-slate-950 px-6 py-8 text-white shadow-xl sm:px-9">
        <div class="pointer-events-none absolute -right-12 -top-20 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-center">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-slate-200">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                    NPL SEASON HUB
                </span>

                <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                    Admin <span class="text-red-400">Dashboard</span>
                </h2>

                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                    Monitor tournament activity, manage fixtures, review results,
                    and keep your match information up to date.
                </p>

                <p class="mt-4 text-sm text-slate-400">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <?php echo e(now()->format('l, d F Y')); ?>

                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="<?php echo e(route('admin.fixtures.index')); ?>"
                   class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 hover:bg-slate-100">
                    <i class="fas fa-calendar-alt mr-2"></i>Manage Fixtures
                </a>

                <a href="<?php echo e(route('admin.fixtures.create')); ?>"
                   class="rounded-xl bg-red-500 px-5 py-3 text-sm font-bold text-white hover:bg-red-600">
                    <i class="fas fa-plus mr-2"></i>Add Fixture
                </a>
            </div>
        </div>
    </section>

    
    <section>
        <div class="mb-4">
            <h3 class="text-xl font-black text-slate-900">Tournament Overview</h3>
            <p class="mt-1 text-sm text-slate-500">Live database statistics</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <?php
                $cards = [
                    ['label' => 'Total Fixtures', 'key' => 'total_fixtures', 'description' => 'All recorded fixtures', 'icon' => 'fa-calendar-alt', 'bg' => 'bg-blue-50', 'border' => 'border-blue-100', 'iconBg' => 'bg-blue-600', 'text' => 'text-blue-800'],
                    ['label' => 'Upcoming Matches', 'key' => 'upcoming_fixtures', 'description' => 'Scheduled matches', 'icon' => 'fa-clock', 'bg' => 'bg-amber-50', 'border' => 'border-amber-100', 'iconBg' => 'bg-amber-500', 'text' => 'text-amber-800'],
                    ['label' => 'Live Matches', 'key' => 'live_fixtures', 'description' => 'Currently marked live', 'icon' => 'fa-broadcast-tower', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'iconBg' => 'bg-emerald-600', 'text' => 'text-emerald-800'],
                    ['label' => 'Completed Matches', 'key' => 'completed_fixtures', 'description' => 'Marked completed', 'icon' => 'fa-check-circle', 'bg' => 'bg-purple-50', 'border' => 'border-purple-100', 'iconBg' => 'bg-purple-600', 'text' => 'text-purple-800'],
                    ['label' => 'Registered Teams', 'key' => 'total_teams', 'description' => 'Teams in database', 'icon' => 'fa-shield-alt', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-100', 'iconBg' => 'bg-cyan-600', 'text' => 'text-cyan-800'],
                    ['label' => 'Total Seasons', 'key' => 'total_seasons', 'description' => 'Registered seasons', 'icon' => 'fa-trophy', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-100', 'iconBg' => 'bg-indigo-600', 'text' => 'text-indigo-800'],
                    ['label' => 'Verified Results', 'key' => 'verified_results', 'description' => 'Confirmed completed results', 'icon' => 'fa-user-check', 'bg' => 'bg-green-50', 'border' => 'border-green-100', 'iconBg' => 'bg-green-600', 'text' => 'text-green-800'],
                    ['label' => 'Pending Verification', 'key' => 'pending_verification', 'description' => 'Completed results to review', 'icon' => 'fa-exclamation-triangle', 'bg' => 'bg-rose-50', 'border' => 'border-rose-100', 'iconBg' => 'bg-rose-600', 'text' => 'text-rose-800'],
                    ['label' => 'Abandoned Matches', 'key' => 'abandoned_fixtures', 'description' => 'Marked abandoned', 'icon' => 'fa-ban', 'bg' => 'bg-orange-50', 'border' => 'border-orange-100', 'iconBg' => 'bg-orange-600', 'text' => 'text-orange-800'],
                    ['label' => 'Postponed Matches', 'key' => 'postponed_fixtures', 'description' => 'Awaiting rescheduling', 'icon' => 'fa-calendar-times', 'bg' => 'bg-slate-100', 'border' => 'border-slate-200', 'iconBg' => 'bg-slate-600', 'text' => 'text-slate-800'],
                ];
            ?>

            <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="group relative overflow-hidden rounded-2xl border <?php echo e($card['border']); ?> <?php echo e($card['bg']); ?> p-5 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold <?php echo e($card['text']); ?>"><?php echo e($card['label']); ?></p>
                            <p class="mt-3 text-4xl font-black text-slate-900">
                                <?php echo e(number_format($stats[$card['key']] ?? 0)); ?>

                            </p>
                            <p class="mt-2 text-xs text-slate-600"><?php echo e($card['description']); ?></p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl <?php echo e($card['iconBg']); ?> text-xl text-white shadow-sm">
                            <i class="fas <?php echo e($card['icon']); ?>"></i>
                        </span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>

    
    <?php if($liveFixtures->isNotEmpty()): ?>
        <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
            <div class="border-b border-emerald-100 bg-emerald-50 px-5 py-4">
                <h3 class="text-lg font-black text-slate-900">
                    <span class="mr-2 inline-block h-2.5 w-2.5 animate-pulse rounded-full bg-red-500"></span>
                    Live Matches
                </h3>
                <p class="mt-1 text-sm text-slate-600">Fixtures currently marked live</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                <?php $__currentLoopData = $liveFixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-extrabold text-red-700">LIVE</span>
                            <span class="text-xs text-slate-500">
                                <?php echo e($fixture->scheduled_at ? \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('M d, h:i A') : 'Time not set'); ?>

                            </span>
                        </div>

                        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                            <?php $__currentLoopData = [$fixture->homeTeam, $fixture->awayTeam]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="min-w-0 text-center">
                                    <?php if($team && !empty($teamLogoUrls[$team->id] ?? null)): ?>
                                        <img src="<?php echo e($teamLogoUrls[$team->id]); ?>"
                                             alt="<?php echo e($team->name); ?>"
                                             class="mx-auto mb-2 h-16 w-16 rounded-full border border-slate-200 bg-white p-1 object-contain"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="mx-auto mb-2 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                            <i class="fas fa-shield-alt text-2xl"></i>
                                        </div>
                                    <?php endif; ?>

                                    <p class="break-words text-sm font-extrabold text-slate-900">
                                        <?php echo e($team?->name ?? 'Team not assigned'); ?>

                                    </p>
                                </div>

                                <?php if($loop->first): ?>
                                    <span class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-black text-white">VS</span>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <a href="<?php echo e(route('admin.fixtures.edit', $fixture)); ?>"
                           class="mt-4 block rounded-lg bg-emerald-600 px-4 py-2.5 text-center text-xs font-bold text-white hover:bg-emerald-700">
                            <i class="fas fa-edit mr-1"></i> Manage Fixture
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>

    
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 bg-amber-50 px-5 py-5">
                <div>
                    <h3 class="text-lg font-black text-slate-900">
                        <i class="fas fa-calendar-day mr-2 text-amber-500"></i>Upcoming Matches
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">Next scheduled fixtures</p>
                </div>
                <a href="<?php echo e(route('admin.fixtures.index')); ?>" class="text-sm font-bold text-blue-700">View all →</a>
            </div>

            <div class="space-y-3 p-4 sm:p-5">
                <?php $__empty_1 = true; $__currentLoopData = $upcomingFixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-xl border border-slate-200 p-4 transition hover:border-amber-300 hover:bg-amber-50/30">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Scheduled</span>
                            <span class="text-right text-xs text-slate-500">
                                <?php echo e($fixture->scheduled_at ? \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('D, M d · h:i A') : 'Date not set'); ?>

                            </span>
                        </div>

                        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                            <?php $__currentLoopData = [$fixture->homeTeam, $fixture->awayTeam]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="min-w-0 text-center">
                                    <?php if($team && !empty($teamLogoUrls[$team->id] ?? null)): ?>
                                        <img src="<?php echo e($teamLogoUrls[$team->id]); ?>"
                                             alt="<?php echo e($team->name); ?>"
                                             class="mx-auto mb-2 h-14 w-14 rounded-full border border-slate-200 bg-white p-1 object-contain"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                            <i class="fas fa-shield-alt text-xl"></i>
                                        </div>
                                    <?php endif; ?>

                                    <p class="break-words text-sm font-extrabold text-slate-900">
                                        <?php echo e($team?->name ?? 'Team not assigned'); ?>

                                    </p>
                                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        <?php echo e($loop->first ? 'Home' : 'Away'); ?>

                                    </p>
                                </div>

                                <?php if($loop->first): ?>
                                    <span class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-black text-white">VS</span>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <?php if($fixture->season?->name): ?>
                            <p class="mt-3 text-center text-xs text-slate-500"><?php echo e($fixture->season->name); ?></p>
                        <?php endif; ?>

                        <a href="<?php echo e(route('admin.fixtures.edit', $fixture)); ?>"
                           class="mt-4 block rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                            Edit Fixture
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="py-12 text-center">
                        <i class="fas fa-calendar-check text-3xl text-amber-400"></i>
                        <p class="mt-3 font-bold text-slate-900">No upcoming matches</p>
                        <p class="mt-1 text-sm text-slate-500">Create a fixture to see it here.</p>
                        <a href="<?php echo e(route('admin.fixtures.create')); ?>" class="mt-4 inline-block rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Add Fixture</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-purple-50 px-5 py-5">
                <h3 class="text-lg font-black text-slate-900">
                    <i class="fas fa-history mr-2 text-purple-600"></i>Recent Matches
                </h3>
                <p class="mt-1 text-sm text-slate-500">Latest completed fixtures</p>
            </div>

            <div class="space-y-3 p-4 sm:p-5">
                <?php $__empty_1 = true; $__currentLoopData = $recentMatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-xl border border-slate-200 p-4 hover:border-purple-200 hover:bg-purple-50/30">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-800">Completed</span>
                            <span class="text-xs text-slate-500">
                                <?php echo e($fixture->scheduled_at ? \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('M d, Y') : 'Match #' . $fixture->id); ?>

                            </span>
                        </div>

                        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                            <?php $__currentLoopData = [$fixture->homeTeam, $fixture->awayTeam]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="min-w-0 text-center">
                                    <?php if($team && !empty($teamLogoUrls[$team->id] ?? null)): ?>
                                        <img src="<?php echo e($teamLogoUrls[$team->id]); ?>"
                                             alt="<?php echo e($team->name); ?>"
                                             class="mx-auto mb-2 h-12 w-12 rounded-full border border-slate-200 bg-white p-1 object-contain"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                    <?php endif; ?>

                                    <p class="break-words text-xs font-extrabold text-slate-900">
                                        <?php echo e($team?->name ?? 'Team not assigned'); ?>

                                    </p>
                                </div>

                                <?php if($loop->first): ?>
                                    <span class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-black text-white">VS</span>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <span class="text-xs text-slate-500">Result verification</span>

                            <?php if($fixture->is_result_verified): ?>
                                <span class="text-xs font-bold text-emerald-700">
                                    <i class="fas fa-check-circle mr-1"></i>Verified
                                </span>
                            <?php else: ?>
                                <span class="text-xs font-bold text-amber-700">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Pending
                                </span>
                            <?php endif; ?>
                        </div>

                        <a href="<?php echo e(route('admin.fixtures.edit', $fixture)); ?>"
                           class="mt-3 block rounded-lg bg-slate-100 px-3 py-2 text-center text-xs font-bold text-slate-700 hover:bg-purple-100 hover:text-purple-800">
                            Review Match
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="py-12 text-center">
                        <i class="fas fa-history text-3xl text-purple-400"></i>
                        <p class="mt-3 font-bold text-slate-900">No completed matches yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-5 py-5">
            <h3 class="text-lg font-black text-slate-900">
                <i class="fas fa-list-alt mr-2 text-blue-600"></i>Recent Fixture Activity
            </h3>
            <p class="mt-1 text-sm text-slate-500">Latest fixture records</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-white">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Match</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Season</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Scheduled</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                        <th class="px-5 py-4 text-right text-xs font-bold uppercase text-slate-500">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $recentFixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <?php $__currentLoopData = [$fixture->homeTeam, $fixture->awayTeam]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($team && !empty($teamLogoUrls[$team->id] ?? null)): ?>
                                            <img src="<?php echo e($teamLogoUrls[$team->id]); ?>"
                                                 alt="<?php echo e($team->name); ?>"
                                                 class="h-9 w-9 rounded-full border border-slate-200 bg-white p-1 object-contain"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                                <i class="fas fa-shield-alt"></i>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <div class="min-w-0">
                                        <p class="max-w-xs truncate text-sm font-bold text-slate-900">
                                            <?php echo e($fixture->homeTeam?->name ?? 'Home Team'); ?>

                                            <span class="text-slate-400">vs</span>
                                            <?php echo e($fixture->awayTeam?->name ?? 'Away Team'); ?>

                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">Fixture #<?php echo e($fixture->id); ?></p>
                                    </div>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                <?php echo e($fixture->season?->name ?? '—'); ?>

                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                <?php echo e($fixture->scheduled_at ? \Illuminate\Support\Carbon::parse($fixture->scheduled_at)->format('M d, Y · h:i A') : 'Not set'); ?>

                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <?php
                                    $statusClasses = [
                                        'scheduled' => 'bg-amber-100 text-amber-800',
                                        'live' => 'bg-emerald-100 text-emerald-800',
                                        'completed' => 'bg-purple-100 text-purple-800',
                                        'abandoned' => 'bg-orange-100 text-orange-800',
                                        'postponed' => 'bg-slate-200 text-slate-700',
                                    ];
                                ?>

                                <span class="rounded-full px-3 py-1 text-xs font-bold <?php echo e($statusClasses[$fixture->status] ?? 'bg-gray-100 text-gray-700'); ?>">
                                    <?php echo e(ucfirst($fixture->status ?? 'Unknown')); ?>

                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <a href="<?php echo e(route('admin.fixtures.edit', $fixture)); ?>"
                                   class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-blue-50">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                No fixtures found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/admin/dashboard/index.blade.php ENDPATH**/ ?>