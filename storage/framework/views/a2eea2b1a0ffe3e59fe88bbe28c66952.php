<?php $__env->startSection('title', 'Fixtures & Results | Nepal Premier League'); ?>

<?php $__env->startSection('meta_description', 'Official Nepal Premier League fixtures, live scores, match results and schedules.'); ?>

<?php $__env->startSection('content'); ?>

<?php
    use App\Models\ManualScorecard;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $timezone = config('app.timezone', 'Asia/Kathmandu');
    $fixtures = $fixtures ?? collect();

    $fixtureCollection = method_exists($fixtures, 'getCollection')
        ? $fixtures->getCollection()
        : collect($fixtures);

    $fixtureIds = $fixtureCollection->pluck('id')->filter()->unique()->values();

    $manualScorecards = ManualScorecard::query()
        ->whereIn('fixture_id', $fixtureIds)
        ->orderBy('fixture_id')
        ->orderBy('innings_number')
        ->get()
        ->groupBy('fixture_id');

    $imageUrl = function ($path) {
        if (blank($path)) return null;
        $path = trim((string) $path);
        if (Str::startsWith($path, ['https://', 'http://', 'data:image/'])) return $path;
        return Storage::url(ltrim($path, '/'));
    };

    $teamName = fn ($team, $fallback = 'Team') =>
        $team?->name ?: $team?->short_name ?: $fallback;

    $teamLogo = function ($team) use ($imageUrl) {
        return $imageUrl(
            $team?->logo ?? $team?->logo_path ?? $team?->image ?? $team?->image_path ?? null
        );
    };

    $formatOvers = function ($inning) {
        if (!$inning) return null;
        $legalBalls = $inning->legal_balls ?? null;
        if ($legalBalls !== null && is_numeric($legalBalls)) {
            return intdiv((int) $legalBalls, 6) . '.' . ((int) $legalBalls % 6);
        }
        $overs = $inning->overs ?? null;
        return $overs !== null && $overs !== '' ? (string) $overs : null;
    };

    $formatTime = function ($date) use ($timezone) {
        if (!$date) return null;
        try {
            return Carbon::parse($date, $timezone)->timezone($timezone)->format('H:i');
        } catch (\Throwable $e) { return null; }
    };

    $formatMatchDay = function ($date) use ($timezone) {
        if (!$date) return 'TBA';
        try {
            $d = Carbon::parse($date, $timezone)->timezone($timezone);
            if ($d->isToday()) return 'Today';
            if ($d->isTomorrow()) return 'Tomorrow';
            if ($d->isYesterday()) return 'Yesterday';
            return $d->format('D, M j');
        } catch (\Throwable $e) { return 'TBA'; }
    };

    $totalMatches = method_exists($fixtures, 'total') ? $fixtures->total() : $fixtureCollection->count();
    $hasPagination = method_exists($fixtures, 'links') && method_exists($fixtures, 'currentPage');
    
    $nplLogo = 'https://blog.nirmalgaihre.com.np/images/npl.png';
    $seasonName = $fixtureCollection->first()?->season?->name ?? 'NPL 2025';
?>

<div class="bg-[#f8f9fa] text-[#202124] font-sans min-h-screen">

    
    <section class="relative overflow-hidden bg-npl-navy text-white">
        
        <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
        
        
        <div class="absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-npl-red/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 h-[400px] w-[400px] rounded-full bg-npl-gold/10 blur-3xl pointer-events-none"></div>

        <div class="npl-container relative py-10 sm:py-14 lg:py-16">
            <div class="flex flex-col items-start gap-8 lg:flex-row lg:items-center lg:justify-between">

                
                <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center sm:gap-8">
                    
                    <div class="relative shrink-0">
                        <div class="absolute inset-0 rounded-2xl bg-white/20 blur-md transform scale-105"></div>
                        <div class="relative flex h-28 w-28 items-center justify-center rounded-2xl bg-white p-3 shadow-2xl ring-1 ring-white/10 sm:h-32 sm:w-32">
                            <img src="<?php echo e($nplLogo); ?>" alt="Nepal Premier League" class="h-full w-full object-contain">
                        </div>
                    </div>

                    
                    <div class="min-w-0">
                        <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 backdrop-blur-sm">
                            <span class="h-1.5 w-1.5 rounded-full bg-npl-red animate-pulse"></span>
                            <span class="text-[10px] font-extrabold uppercase tracking-[2px] text-white">
                                Official Match Centre
                            </span>
                        </div>
                        
                        <h1 class="npl-display text-4xl font-black uppercase leading-[0.95] tracking-wide text-white sm:text-5xl lg:text-6xl">
                            Fixtures <span class="text-npl-gold">&amp; Results</span>
                        </h1>
                        
                        <p class="mt-4 max-w-xl text-sm leading-6 text-white/70 sm:text-base">
                            Complete match schedule, live scores and results from the <span class="font-bold text-white"><?php echo e($seasonName); ?></span> season. All times shown in Nepal Standard Time (NPT).
                        </p>
                    </div>
                </div>

                
                <div class="flex w-full gap-3 sm:w-auto">
                    <div class="flex-1 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-md sm:min-w-[140px]">
                        <p class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-widest text-white/50">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Season
                        </p>
                        <p class="npl-display mt-2 text-2xl font-black leading-none text-white">
                            <?php echo e($seasonName); ?>

                        </p>
                    </div>
                    <div class="flex-1 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-md sm:min-w-[140px]">
                        <p class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-widest text-white/50">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Total Matches
                        </p>
                        <p class="npl-display mt-2 text-2xl font-black leading-none text-white">
                            <?php echo e(number_format($totalMatches)); ?>

                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    
    <div class="max-w-[1000px] mx-auto px-4 py-8 -mt-4 sm:-mt-6 relative z-10">
        
        
        <div class="rounded-2xl bg-white shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-gray-200 p-4 sm:p-6 mb-8">
            
            
            <div class="mb-6 flex items-end justify-between border-b border-gray-100 pb-3">
                <div class="flex gap-6 text-[15px] font-medium text-gray-500">
                    <span class="text-[#202124] border-b-2 border-[#d93025] pb-3 -mb-[13px] font-bold">Matches</span>
                    <a href="<?php echo e(route('pointtable.index') ?? '#'); ?>" class="hover:text-[#202124] transition-colors">Standings</a>
                    <a href="#" class="hover:text-[#202124] transition-colors">Stats</a>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                
                <?php $__empty_1 = true; $__currentLoopData = $fixtureCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fixture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $home = $fixture->homeTeam ?? null;
                        $away = $fixture->awayTeam ?? null;
                        
                        $homeName = $teamName($home, 'Home');
                        $awayName = $teamName($away, 'Away');
                        $homeLogo = $teamLogo($home);
                        $awayLogo = $teamLogo($away);

                        $innings = $manualScorecards->get($fixture->id, collect())->sortBy('innings_number')->values();
                        $homeInning = $innings->filter(fn ($i) => (int) $i->batting_team_id === (int) $fixture->home_team_id)->last();
                        $awayInning = $innings->filter(fn ($i) => (int) $i->batting_team_id === (int) $fixture->away_team_id)->last();

                        $homeRuns = $homeInning?->total_runs ?? $homeInning?->runs;
                        $awayRuns = $awayInning?->total_runs ?? $awayInning?->runs;
                        
                        $homeScore = $homeInning && $homeRuns !== null ? $homeRuns . '/' . ($homeInning->wickets ?? 0) : null;
                        $awayScore = $awayInning && $awayRuns !== null ? $awayRuns . '/' . ($awayInning->wickets ?? 0) : null;
                        
                        $homeOvers = $formatOvers($homeInning);
                        $awayOvers = $formatOvers($awayInning);

                        $matchDate = $fixture->scheduled_at ?? $fixture->match_date ?? $fixture->date ?? null;
                        $matchDay = $formatMatchDay($matchDate);
                        $matchTime = $formatTime($matchDate);

                        $rawStatus = strtolower(trim((string) ($fixture->status ?? 'scheduled')));
                        $isLive = str_contains($rawStatus, 'live') || str_contains($rawStatus, 'progress') || $rawStatus === 'in_play';
                        $isResult = str_contains($rawStatus, 'complete') || str_contains($rawStatus, 'finished') || str_contains($rawStatus, 'result');
                        
                        $matchNumber = $fixture->match_number ?? $fixture->match_no ?? null;
                        $resultText = $fixture->result_summary ?? $fixture->result ?? '';
                        
                        // Determine Winner for Bold Styling
                        $homeWon = false;
                        $awayWon = false;
                        if ($isResult && $resultText) {
                            $lowerResult = strtolower($resultText);
                            if (str_contains($lowerResult, strtolower($homeName)) || str_contains($lowerResult, strtolower($home->short_name ?? ''))) {
                                $homeWon = true;
                            } elseif (str_contains($lowerResult, strtolower($awayName)) || str_contains($lowerResult, strtolower($away->short_name ?? ''))) {
                                $awayWon = true;
                            }
                        }

                        $fixtureUrl = route('fixtures.show', ['fixture' => $fixture->id]);
                    ?>

                    <a href="<?php echo e($fixtureUrl); ?>" class="group block border border-transparent hover:bg-[#f8f9fa] hover:border-gray-200 rounded-xl p-4 -m-2 transition-all duration-200">
                        
                        
                        <div class="flex justify-between items-center mb-3 text-[13px]">
                            <span class="text-[#70757a]">
                                T20 <?php echo e($matchNumber ? $matchNumber : $fixture->id); ?> of <?php echo e($totalMatches); ?>

                            </span>
                            
                            <?php if($isLive): ?>
                                <span class="font-bold text-[#d93025] animate-pulse flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#d93025]"></span> Live
                                </span>
                            <?php elseif($isResult): ?>
                                <span class="text-[#70757a]">Result</span>
                            <?php else: ?>
                                <span class="text-[#70757a]"><?php echo e($matchDay); ?></span>
                            <?php endif; ?>
                        </div>

                        
                        <div class="space-y-3">
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center shrink-0">
                                        <?php if($homeLogo): ?>
                                            <img src="<?php echo e($homeLogo); ?>" alt="<?php echo e($homeName); ?>" class="w-full h-full object-contain">
                                        <?php else: ?>
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'text-[15px] transition-colors group-hover:text-blue-600',
                                        'font-bold text-[#202124]' => $homeWon,
                                        'font-normal text-[#202124]' => !$homeWon && ($homeScore !== null || !$isResult),
                                        'font-normal text-[#70757a]' => !$homeWon && $homeScore === null && $isResult,
                                    ]); ?>">
                                        <?php echo e($homeName); ?>

                                    </span>
                                </div>
                                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'text-[15px] flex items-center gap-1.5',
                                    'font-bold text-[#202124]' => $homeWon,
                                    'font-normal text-[#5f6368]' => !$homeWon && ($homeScore !== null || !$isResult),
                                    'font-normal text-[#70757a]' => !$homeWon && $homeScore === null && $isResult,
                                ]); ?>">
                                    <?php if(!$homeScore): ?>
                                        Yet to bat
                                    <?php else: ?>
                                        <?php echo e($homeScore); ?> 
                                        <?php if($homeOvers): ?> <span class="text-[12px] text-[#70757a]">(<?php echo e($homeOvers); ?>)</span> <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center shrink-0">
                                        <?php if($awayLogo): ?>
                                            <img src="<?php echo e($awayLogo); ?>" alt="<?php echo e($awayName); ?>" class="w-full h-full object-contain">
                                        <?php else: ?>
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'text-[15px] transition-colors group-hover:text-blue-600',
                                        'font-bold text-[#202124]' => $awayWon,
                                        'font-normal text-[#202124]' => !$awayWon && ($awayScore !== null || !$isResult),
                                        'font-normal text-[#70757a]' => !$awayWon && $awayScore === null && $isResult,
                                    ]); ?>">
                                        <?php echo e($awayName); ?>

                                    </span>
                                </div>
                                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'text-[15px] flex items-center gap-1.5',
                                    'font-bold text-[#202124]' => $awayWon,
                                    'font-normal text-[#5f6368]' => !$awayWon && ($awayScore !== null || !$isResult),
                                    'font-normal text-[#70757a]' => !$awayWon && $awayScore === null && $isResult,
                                ]); ?>">
                                    <?php if(!$awayScore): ?>
                                        Yet to bat
                                    <?php else: ?>
                                        <?php echo e($awayScore); ?> 
                                        <?php if($awayOvers): ?> <span class="text-[12px] text-[#70757a]">(<?php echo e($awayOvers); ?>)</span> <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <div class="mt-3 text-[13px]">
                            <?php if($isResult && $resultText): ?>
                                <span class="text-[#1e8e3e] font-medium"><?php echo e($resultText); ?></span>
                            <?php elseif($isLive): ?>
                                <span class="text-[#d93025] font-medium">Match in progress</span>
                            <?php else: ?>
                                <span class="text-[#70757a]">Starts at <?php echo e($matchTime ?: 'TBA'); ?> NPT</span>
                            <?php endif; ?>
                        </div>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-full py-12 text-center text-[#70757a]">
                        No matches found.
                    </div>
                <?php endif; ?>
                
            </div>

            
            <?php if($hasPagination && $fixtures->hasPages()): ?>
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <?php echo e($fixtures->withQueryString()->onEachSide(1)->links()); ?>

                </div>
            <?php endif; ?>

        </div>
    </div>
</div>


<style>
    .pagination { display: flex; gap: 4px; list-style: none; padding: 0; margin: 0; justify-content: center; }
    .page-item .page-link, .page-item span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px;
        font-size: 13px; color: #1a73e8; background: #fff; border: 1px solid #dadce0; border-radius: 4px;
    }
    .page-item.active span { background: #e8f0fe; color: #1967d2; border-color: #1967d2; font-weight: 500; }
    .page-item.disabled span { color: #bdc1c6; }
    .page-item a:hover { background: #f8f9fa; }
</style>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/fixtures/index.blade.php ENDPATH**/ ?>