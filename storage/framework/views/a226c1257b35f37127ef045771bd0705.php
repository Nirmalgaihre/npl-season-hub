<?php $__env->startSection('title', 'Points Table | NPL Season Hub'); ?>

<?php $__env->startSection('content'); ?>
<?php
$standingsCollection = collect($standings ?? []);
$seasons = collect($seasons ?? []);

$logoUrl = function ($team) {
if (!$team) {
return null;
}

$path = $team->logo_path
?? $team->logo
?? $team->logo_url
?? null;

if (!$path) {
return null;
}

if (
str_starts_with($path, 'https://')
|| str_starts_with($path, 'http://')
|| str_starts_with($path, '/')
) {
return $path;
}

return asset('storage/' . ltrim($path, '/'));
};

$teamCount = $totalTeams ?? $standingsCollection->count();
$completedCount = $completedMatches ?? 0;
$upcomingCount = $upcomingMatches ?? 0;

// Each completed match appears in two teams' played totals.
$calculatedFixtures = (int) floor(
$standingsCollection->sum(function ($row) {
return (int) ($row['played'] ?? $row['gp'] ?? 0);
}) / 2
) + (int) $upcomingCount;

$fixtureTotal = isset($fixtureCount)
? (int) $fixtureCount
: $calculatedFixtures;
?>

<style>
.npl-standings {
    --npl-navy: #101d36;
    --npl-blue: #173d73;
    --npl-red: #d7283d;
    --npl-text: #202b3c;
    --npl-muted: #6c7788;
    --npl-border: #e1e5eb;
    --npl-soft: #f5f7fa;
    --npl-green: #18794e;

    padding: 28px 0 52px;
    color: var(--npl-text);
    background: #fff;
}

.npl-standings *,
.npl-standings *::before,
.npl-standings *::after {
    box-sizing: border-box;
}

.npl-standings .npl-wrap {
    width: min(1200px, calc(100% - 36px));
    margin: 0 auto;
}

/* Tournament heading */

.npl-standings .npl-heading {
    padding: 0 0 23px;
    border-bottom: 1px solid var(--npl-border);
}

.npl-standings .npl-heading-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
}

.npl-standings .npl-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.npl-standings .npl-brand-mark {
    display: flex;
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    align-items: center;
    justify-content: center;
    border-left: 4px solid var(--npl-red);
    background: var(--npl-navy);
    color: #fff;
    font-size: 15px;
    font-weight: 900;
    letter-spacing: -.8px;
}

.npl-standings .npl-brand-name {
    color: var(--npl-navy);
    font-size: 15px;
    font-weight: 900;
    letter-spacing: .025em;
}

.npl-standings .npl-brand-subtitle {
    margin-top: 3px;
    color: var(--npl-muted);
    font-size: 11px;
}

.npl-standings .npl-season-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 11px;
    border: 1px solid var(--npl-border);
    color: var(--npl-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
}

.npl-standings .npl-season-tag::before {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--npl-red);
    content: "";
}

.npl-standings .npl-title-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
}

.npl-standings .npl-kicker {
    margin: 0 0 7px;
    color: var(--npl-red);
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.npl-standings .npl-page-title {
    margin: 0;
    color: var(--npl-navy);
    font-size: clamp(30px, 4vw, 43px);
    font-weight: 900;
    line-height: 1.12;
    letter-spacing: -.045em;
}

.npl-standings .npl-description {
    max-width: 580px;
    margin: 10px 0 0;
    color: var(--npl-muted);
    font-size: 13px;
    line-height: 1.7;
}

.npl-standings .npl-current-season {
    margin-top: 8px;
    color: var(--npl-blue);
    font-size: 12px;
    font-weight: 800;
}

/* Compact tournament summary */

.npl-standings .npl-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin: 24px 0 34px;
    border: 1px solid var(--npl-border);
    background: #fff;
}

.npl-standings .npl-summary-item {
    min-width: 0;
    padding: 17px 21px;
    border-right: 1px solid var(--npl-border);
}

.npl-standings .npl-summary-item:last-child {
    border-right: 0;
}

.npl-standings .npl-summary-label {
    display: block;
    color: var(--npl-muted);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .085em;
    text-transform: uppercase;
}

.npl-standings .npl-summary-value {
    display: block;
    margin-top: 7px;
    color: var(--npl-navy);
    font-size: 29px;
    font-weight: 900;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}

/* Standings section heading */

.npl-standings .npl-section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 15px;
}

.npl-standings .npl-section-title {
    margin: 0;
    color: var(--npl-navy);
    font-size: 23px;
    font-weight: 900;
    letter-spacing: -.025em;
}

.npl-standings .npl-section-description {
    margin: 5px 0 0;
    color: var(--npl-muted);
    font-size: 12px;
    line-height: 1.6;
}

.npl-standings .npl-season-filter {
    display: flex;
    align-items: center;
    gap: 9px;
}

.npl-standings .npl-season-filter label {
    color: var(--npl-muted);
    font-size: 11px;
    font-weight: 800;
}

.npl-standings .npl-season-filter select {
    min-width: 170px;
    max-width: 240px;
    min-height: 39px;
    padding: 8px 30px 8px 11px;
    border: 1px solid #d7dde6;
    border-radius: 3px;
    outline: none;
    background-color: #fff;
    color: var(--npl-navy);
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
}

.npl-standings .npl-season-filter select:focus {
    border-color: var(--npl-blue);
    box-shadow: 0 0 0 2px rgb(23 61 115 / 10%);
}

/* Standings table */

.npl-standings .npl-table-frame {
    overflow: hidden;
    border: 1px solid var(--npl-border);
    background: #fff;
}

.npl-standings .npl-table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.npl-standings .npl-table {
    width: 100%;
    min-width: 750px;
    border-collapse: collapse;
    text-align: center;
}

.npl-standings .npl-table thead {
    background: var(--npl-navy);
}

.npl-standings .npl-table thead th {
    height: 43px;
    padding: 11px 12px;
    border: 0;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .055em;
    text-transform: uppercase;
    white-space: nowrap;
}

.npl-standings .npl-table thead th:first-child {
    width: 54px;
}

.npl-standings .npl-table thead th:nth-child(2) {
    text-align: left;
}

.npl-standings .npl-table tbody tr {
    border-bottom: 1px solid #e9edf2;
    background: #fff;
    transition: background-color .15s ease;
}

.npl-standings .npl-table tbody tr:last-child {
    border-bottom: 0;
}

.npl-standings .npl-table tbody tr:hover {
    background: #f7f9fc;
}

.npl-standings .npl-table tbody td {
    height: 68px;
    padding: 12px;
    color: var(--npl-text);
    font-size: 13px;
    font-variant-numeric: tabular-nums;
    vertical-align: middle;
}

.npl-standings .npl-table .npl-position {
    width: 54px;
    color: #657184;
    font-size: 13px;
    font-weight: 800;
}

.npl-standings .npl-position-number {
    display: inline-flex;
    width: 25px;
    height: 25px;
    align-items: center;
    justify-content: center;
}

.npl-standings .npl-position-first .npl-position-number {
    background: #f2e7c9;
    color: #765514;
}

.npl-standings .npl-team-column {
    min-width: 225px;
    text-align: left;
}

.npl-standings .npl-team {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 12px;
}

.npl-standings .npl-team-logo {
    display: flex;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid #e7eaf0;
    border-radius: 50%;
    background: #fff;
    color: var(--npl-blue);
    font-size: 11px;
    font-weight: 900;
}

.npl-standings .npl-team-logo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.npl-standings .npl-team-name {
    color: var(--npl-navy);
    font-size: 13px;
    font-weight: 800;
    line-height: 1.4;
}

.npl-standings .npl-team-caption {
    margin-top: 3px;
    color: #8993a2;
    font-size: 10px;
}

.npl-standings .npl-number {
    color: #3c4758;
    font-size: 13px;
    font-weight: 600;
}

.npl-standings .npl-nrr {
    min-width: 90px;
    font-weight: 800;
    white-space: nowrap;
}

.npl-standings .npl-nrr-positive {
    color: var(--npl-green);
}

.npl-standings .npl-nrr-negative {
    color: var(--npl-red);
}

.npl-standings .npl-points-column {
    min-width: 105px;
    border-left: 1px solid #edf0f4;
    background: #fafbfd;
}

.npl-standings .npl-table thead .npl-points-column {
    border-left: 1px solid rgb(255 255 255 / 16%);
    background: var(--npl-blue);
}

.npl-standings .npl-points-total {
    display: block;
    color: var(--npl-navy);
    font-size: 21px;
    font-weight: 900;
    line-height: 1.1;
}

.npl-standings .npl-adjustments {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    margin-top: 5px;
}

.npl-standings .npl-adjustment {
    font-size: 9px;
    font-weight: 800;
    line-height: 1.3;
    white-space: nowrap;
}

.npl-standings .npl-manual-positive {
    color: var(--npl-green);
}

.npl-standings .npl-manual-negative {
    color: #a76509;
}

.npl-standings .npl-penalty {
    color: var(--npl-red);
}

/* Table notes and empty state */

.npl-standings .npl-table-notes {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 9px 20px;
    padding: 13px 15px;
    border-top: 1px solid var(--npl-border);
    background: #f8f9fb;
    color: var(--npl-muted);
    font-size: 10px;
    line-height: 1.7;
}

.npl-standings .npl-table-notes strong {
    color: var(--npl-navy);
    font-weight: 800;
}

.npl-standings .npl-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 18px;
    margin-top: 15px;
    color: var(--npl-muted);
    font-size: 10px;
}

.npl-standings .npl-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.npl-standings .npl-legend-mark {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

.npl-standings .npl-empty {
    padding: 48px 20px;
    text-align: center;
}

.npl-standings .npl-empty-mark {
    display: flex;
    width: 52px;
    height: 52px;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    border: 1px solid var(--npl-border);
    color: var(--npl-blue);
    font-size: 23px;
    font-weight: 900;
}

.npl-standings .npl-empty-title {
    margin: 0;
    color: var(--npl-navy);
    font-size: 18px;
    font-weight: 900;
}

.npl-standings .npl-empty-description {
    max-width: 430px;
    margin: 9px auto 0;
    color: var(--npl-muted);
    font-size: 12px;
    line-height: 1.7;
}

@media (min-width: 900px) {
    .npl-standings .npl-table .npl-points-column {
        position: relative;
    }
}

@media (max-width: 700px) {
    .npl-standings {
        padding: 20px 0 38px;
    }

    .npl-standings .npl-wrap {
        width: calc(100% - 24px);
    }

    .npl-standings .npl-heading {
        padding-bottom: 19px;
    }

    .npl-standings .npl-heading-top {
        margin-bottom: 19px;
    }

    .npl-standings .npl-brand-mark {
        width: 40px;
        height: 40px;
        flex-basis: 40px;
    }

    .npl-standings .npl-brand-name {
        font-size: 13px;
    }

    .npl-standings .npl-brand-subtitle {
        font-size: 10px;
    }

    .npl-standings .npl-season-tag {
        padding: 7px 8px;
        font-size: 9px;
    }

    .npl-standings .npl-title-row {
        align-items: flex-start;
    }

    .npl-standings .npl-page-title {
        font-size: 32px;
    }

    .npl-standings .npl-description {
        font-size: 12px;
    }

    .npl-standings .npl-summary {
        margin: 17px 0 27px;
    }

    .npl-standings .npl-summary-item {
        padding: 14px 12px;
    }

    .npl-standings .npl-summary-label {
        font-size: 9px;
    }

    .npl-standings .npl-summary-value {
        font-size: 25px;
    }

    .npl-standings .npl-section-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 13px;
    }

    .npl-standings .npl-section-title {
        font-size: 21px;
    }

    .npl-standings .npl-season-filter {
        width: 100%;
        justify-content: space-between;
    }

    .npl-standings .npl-season-filter select {
        min-width: 0;
        max-width: 70%;
    }

    .npl-standings .npl-table thead th {
        padding-right: 10px;
        padding-left: 10px;
    }

    .npl-standings .npl-table tbody td {
        padding-right: 10px;
        padding-left: 10px;
    }

    .npl-standings .npl-table-notes {
        padding: 12px;
    }
}

@media (max-width: 390px) {
    .npl-standings .npl-wrap {
        width: calc(100% - 18px);
    }

    .npl-standings .npl-brand {
        gap: 8px;
    }

    .npl-standings .npl-season-tag {
        gap: 5px;
        font-size: 8px;
    }

    .npl-standings .npl-page-title {
        font-size: 29px;
    }

    .npl-standings .npl-summary-item {
        padding: 13px 9px;
    }

    .npl-standings .npl-summary-value {
        font-size: 23px;
    }
}
</style>

<div class="npl-standings">
    <div class="npl-wrap">

        
        <header class="npl-heading">
            <div class="npl-heading-top">
                <div class="npl-brand">
                    <div class="npl-brand-mark" aria-label="NPL">
                        NPL
                    </div>

                    <div>
                        <div class="npl-brand-name">NPL SEASON HUB</div>
                        <div class="npl-brand-subtitle">
                            Nepal Premier League coverage
                        </div>
                    </div>
                </div>

                <div class="npl-season-tag">
                    <?php echo e($selectedSeason->name ?? 'Tournament standings'); ?>

                </div>
            </div>

            <div class="npl-title-row">
                <div>
                    <p class="npl-kicker">Tournament standings</p>

                    <h1 class="npl-page-title">Points Table</h1>

                    <p class="npl-description">
                        Team rankings, match records, net run rate and total
                        points for the selected season.
                    </p>

                    <?php if($selectedSeason): ?>
                    <div class="npl-current-season">
                        <?php echo e($selectedSeason->name); ?>

                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        
        <section class="npl-summary" aria-label="Tournament statistics">
            <div class="npl-summary-item">
                <span class="npl-summary-label">Teams</span>
                <span class="npl-summary-value"><?php echo e($teamCount); ?></span>
            </div>

            <div class="npl-summary-item">
                <span class="npl-summary-label">Completed matches</span>
                <span class="npl-summary-value"><?php echo e($completedCount); ?></span>
            </div>

            <div class="npl-summary-item">
                <span class="npl-summary-label">Upcoming matches</span>
                <span class="npl-summary-value"><?php echo e($upcomingCount); ?></span>
            </div>

            <div class="npl-summary-item">
                <span class="npl-summary-label">Total fixtures</span>
                <span class="npl-summary-value"><?php echo e($fixtureTotal); ?></span>
            </div>
        </section>

        
        <div class="npl-section-heading">
            <div>
                <h2 class="npl-section-title">Team Standings</h2>
                <p class="npl-section-description">
                    Teams are listed in the order supplied by the standings calculator.
                </p>
            </div>

            <?php if($seasons->isNotEmpty()): ?>
            <form method="GET" action="<?php echo e(route('pointtable.index')); ?>" class="npl-season-filter">
                <label for="season">Season</label>

                <select id="season" name="season" onchange="this.form.submit()">
                    <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seasonOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($seasonOption->id); ?>" <?php if( $selectedSeason && (int) $selectedSeason->id ===
                        (int) $seasonOption->id
                        ): echo 'selected'; endif; ?>
                        >
                        <?php echo e($seasonOption->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
            <?php endif; ?>
        </div>

        
        <section class="npl-table-frame" aria-label="Team points table">
            <?php if($standingsCollection->isNotEmpty()): ?>
            <div class="npl-table-scroll">
                <table class="npl-table">
                    <thead>
                        <tr>
                            <th scope="col">Pos</th>
                            <th scope="col">Team</th>
                            <th scope="col">P</th>
                            <th scope="col">W</th>
                            <th scope="col">L</th>
                            <th scope="col">T</th>
                            <th scope="col">NR</th>
                            <th scope="col">NRR</th>
                            <th scope="col" class="npl-points-column">Pts</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $standingsCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                        $team = $row['team'] ?? null;
                        $logo = $logoUrl($team);
                        $position = $index + 1;

                        $played = (int) (
                        $row['played'] ?? $row['gp'] ?? 0
                        );

                        $won = (int) (
                        $row['won'] ?? $row['w'] ?? 0
                        );

                        $lost = (int) (
                        $row['lost'] ?? $row['l'] ?? 0
                        );

                        $tied = (int) (
                        $row['tied'] ?? $row['t'] ?? 0
                        );

                        $noResult = (int) (
                        $row['no_result'] ?? $row['nr'] ?? 0
                        );

                        $points = $row['points']
                        ?? $row['pts']
                        ?? 0;

                        $manualPoints = (int) (
                        $row['manual_points'] ?? 0
                        );

                        $penaltyPoints = (int) (
                        $row['penalty_points']
                        ?? $row['penalty']
                        ?? 0
                        );

                        $nrr = $row['nrr'] ?? null;

                        if (is_numeric($nrr)) {
                        $nrr = (float) $nrr;

                        $nrrDisplay = ($nrr > 0 ? '+' : '')
                        . number_format($nrr, 3);

                        $nrrClass = $nrr > 0
                        ? 'npl-nrr-positive'
                        : ($nrr < 0 ? 'npl-nrr-negative' : '' ); } else { $nrrDisplay=$nrr ?: '—' ; $nrrClass='' ; }
                            $teamName=$team->name ?? 'Unknown Team';
                            $teamInitials = strtoupper(
                            substr($teamName, 0, 2)
                            );
                            ?>

                            <tr>
                                <td class="npl-position <?php echo e($position === 1 ? 'npl-position-first' : ''); ?>">
                                    <span class="npl-position-number">
                                        <?php echo e($position); ?>

                                    </span>
                                </td>

                                <td class="npl-team-column">
                                    <div class="npl-team">
                                        <div class="npl-team-logo">
                                            <?php if($logo): ?>
                                            <img src="<?php echo e($logo); ?>" alt="<?php echo e($teamName); ?> logo" loading="lazy"
                                                onerror="this.style.display='none'; this.parentElement.textContent='<?php echo e($teamInitials); ?>';">
                                            <?php else: ?>
                                            <?php echo e($teamInitials); ?>

                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            <div class="npl-team-name">
                                                <?php echo e($teamName); ?>

                                            </div>

                                            <div class="npl-team-caption">
                                                <?php echo e($played); ?> <?php echo e($played === 1 ? 'match' : 'matches'); ?> played
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="npl-number"><?php echo e($played); ?></td>
                                <td class="npl-number"><?php echo e($won); ?></td>
                                <td class="npl-number"><?php echo e($lost); ?></td>
                                <td class="npl-number"><?php echo e($tied); ?></td>
                                <td class="npl-number"><?php echo e($noResult); ?></td>

                                <td class="npl-number npl-nrr <?php echo e($nrrClass); ?>">
                                    <?php echo e($nrrDisplay); ?>

                                </td>

                                <td class="npl-points-column">
                                    <span class="npl-points-total">
                                        <?php echo e($points); ?>

                                    </span>

                                    <?php if($manualPoints !== 0 || $penaltyPoints > 0): ?>
                                    <div class="npl-adjustments">
                                        <?php if($manualPoints > 0): ?>
                                        <span class="npl-adjustment npl-manual-positive">
                                            +<?php echo e($manualPoints); ?> manual
                                        </span>
                                        <?php elseif($manualPoints < 0): ?> <span class="npl-adjustment npl-manual-negative">
                                            <?php echo e($manualPoints); ?> manual
                                            </span>
                                            <?php endif; ?>

                                            <?php if($penaltyPoints > 0): ?>
                                            <span class="npl-adjustment npl-penalty">
                                                −<?php echo e($penaltyPoints); ?> penalty
                                            </span>
                                            <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="npl-table-notes">
                <span>
                    <strong>P</strong> Played ·
                    <strong>W</strong> Won ·
                    <strong>L</strong> Lost ·
                    <strong>T</strong> Tied ·
                    <strong>NR</strong> No Result
                </span>

                <span>
                    <strong>NRR</strong> Net Run Rate ·
                    <strong>Pts</strong> Total Points
                </span>
            </div>
            <?php else: ?>
            <div class="npl-empty">
                <div class="npl-empty-mark" aria-hidden="true">—</div>

                <h3 class="npl-empty-title">
                    No standings available
                </h3>

                <p class="npl-empty-description">
                    There are no standings to display for this season yet.
                    Team rankings will appear here when the required
                    tournament data is available.
                </p>
            </div>
            <?php endif; ?>
        </section>

        
        <div class="npl-legend" aria-label="Points adjustment legend">
            <span class="npl-legend-item">
                <span class="npl-legend-mark" style="background:#18794e"></span>
                Manual points added
            </span>

            <span class="npl-legend-item">
                <span class="npl-legend-mark" style="background:#a76509"></span>
                Manual points deducted
            </span>

            <span class="npl-legend-item">
                <span class="npl-legend-mark" style="background:#d7283d"></span>
                Penalty points
            </span>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/pointtable/index.blade.php ENDPATH**/ ?>