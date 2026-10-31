
@extends('layouts.admin')

@section('title', 'Edit Scorecard')

@section('content')
@php
    $teamIds = [
        (int) $fixture->home_team_id,
        (int) $fixture->away_team_id,
    ];
@endphp

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="mb-1">Edit Match Scorecard</h3>
            <div class="text-muted">
                {{ $fixture->match_number ? 'Match '.$fixture->match_number.' · ' : '' }}
                Select a team to show its players.
            </div>
        </div>

        <a href="{{ route('admin.scorecards.index') }}"
           class="btn btn-outline-secondary">
            Back to Scorecards
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-muted small">HOME TEAM</div>
                    <h5 class="mb-0">
                        {{ $teams->firstWhere('id', $fixture->home_team_id)->name ?? 'Home Team' }}
                    </h5>
                </div>

                <span class="badge bg-secondary fs-6">VS</span>

                <div class="text-end">
                    <div class="text-muted small">AWAY TEAM</div>
                    <h5 class="mb-0">
                        {{ $teams->firstWhere('id', $fixture->away_team_id)->name ?? 'Away Team' }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('admin.scorecards.update', $fixture->id) }}"
          id="scorecard-form">
        @csrf
        @method('PUT')

        @foreach($scorecards as $inningIndex => $inning)
            @php
                $battingTeamId = (int) ($inning->batting_team_id ?? $fixture->home_team_id);
                $bowlingTeamId = (int) ($inning->bowling_team_id ?? $fixture->away_team_id);

                $batters = is_array($inning->batters ?? null)
                    ? $inning->batters
                    : [];

                $bowlers = is_array($inning->bowlers ?? null)
                    ? $inning->bowlers
                    : [];

                if (empty($batters)) {
                    $batters = [[
                        'name' => '',
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'strike_rate' => 0,
                    ]];
                }

                if (empty($bowlers)) {
                    $bowlers = [[
                        'name' => '',
                        'overs' => 0,
                        'maidens' => 0,
                        'runs' => 0,
                        'wickets' => 0,
                        'economy' => 0,
                    ]];
                }
            @endphp

            <section class="card border-0 shadow-sm mb-4 innings-section"
                     data-innings-index="{{ $inningIndex }}">

                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        Innings {{ $inning->innings_number ?? ($inningIndex + 1) }}
                    </h5>

                    <input type="hidden"
                           name="innings[{{ $inningIndex }}][innings_number]"
                           value="{{ $inning->innings_number ?? ($inningIndex + 1) }}">
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batting Team</label>

                            <select name="innings[{{ $inningIndex }}][batting_team_id]"
                                    class="form-select batting-team team-select"
                                    required>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}"
                                        @selected($battingTeamId === (int) $team->id)>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bowling Team</label>

                            <select name="innings[{{ $inningIndex }}][bowling_team_id]"
                                    class="form-select bowling-team team-select"
                                    required>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}"
                                        @selected($bowlingTeamId === (int) $team->id)>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Total Runs</label>
                            <input type="number" min="0"
                                   class="form-control"
                                   name="innings[{{ $inningIndex }}][total_runs]"
                                   value="{{ $inning->total_runs ?? 0 }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Wickets</label>
                            <input type="number" min="0" max="10"
                                   class="form-control"
                                   name="innings[{{ $inningIndex }}][wickets]"
                                   value="{{ $inning->wickets ?? 0 }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Overs</label>
                            <input type="number" min="0" step="0.1"
                                   class="form-control"
                                   name="innings[{{ $inningIndex }}][overs]"
                                   value="{{ $inning->overs ?? '0.0' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Extras</label>
                            <input type="number" min="0"
                                   class="form-control"
                                   name="innings[{{ $inningIndex }}][extras]"
                                   value="{{ $inning->extras ?? 0 }}">
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h6 class="mb-0">Batters</h6>

                        <button type="button"
                                class="btn btn-sm btn-outline-primary add-batter">
                            + Add Batter
                        </button>
                    </div>

                    <div class="batter-list">
                        @foreach($batters as $rowIndex => $batter)
                            @php
                                $savedBatterName = $batter['name'] ?? '';
                                $batterIsKnown = $players->contains(
                                    fn($player) =>
                                        (int) $player->team_id === $battingTeamId &&
                                        $player->name === $savedBatterName
                                );
                            @endphp

                            <div class="batter-row border rounded p-3 mb-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">Batter</label>

                                        <select class="form-select player-select batter-player"
                                                name="innings[{{ $inningIndex }}][batters][{{ $rowIndex }}][name]"
                                                data-saved-name="{{ $savedBatterName }}"
                                                data-other-name="{{ $batterIsKnown ? '' : $savedBatterName }}">
                                            <option value="">-- Select batter --</option>
                                            @foreach($players as $player)
                                                @if((int) $player->team_id === $battingTeamId)
                                                    <option value="{{ $player->name }}"
                                                        data-team-id="{{ $player->team_id }}"
                                                        @selected($player->name === $savedBatterName)>
                                                        {{ $player->name }}
                                                        {{ $player->role ? ' — '.$player->role : '' }}
                                                    </option>
                                                @endif
                                            @endforeach
                                            <option value="__other__"
                                                @selected($savedBatterName !== '' && !$batterIsKnown)>
                                                Other player…
                                            </option>
                                        </select>

                                        <input type="text"
                                               class="form-control mt-2 custom-player-name"
                                               name="innings[{{ $inningIndex }}][batters][{{ $rowIndex }}][custom_name]"
                                               value="{{ $batterIsKnown ? '' : $savedBatterName }}"
                                               placeholder="Enter player name"
                                               style="{{ $savedBatterName !== '' && !$batterIsKnown ? '' : 'display:none' }}">
                                    </div>

                                    @foreach([
                                        'runs' => 'Runs',
                                        'balls' => 'Balls',
                                        'fours' => '4s',
                                        'sixes' => '6s',
                                        'strike_rate' => 'Strike Rate',
                                    ] as $field => $label)
                                        <div class="col-lg col-md-3 col-6">
                                            <label class="form-label">{{ $label }}</label>
                                            <input type="number" min="0" step="{{ $field === 'strike_rate' ? '0.01' : '1' }}"
                                                   class="form-control"
                                                   name="innings[{{ $inningIndex }}][batters][{{ $rowIndex }}][{{ $field }}]"
                                                   value="{{ $batter[$field] ?? 0 }}">
                                        </div>
                                    @endforeach

                                    <div class="col-lg-1 col-md-2 col-6">
                                        <button type="button"
                                                class="btn btn-outline-danger w-100 remove-row"
                                                title="Remove batter">
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h6 class="mb-0">Bowlers</h6>

                        <button type="button"
                                class="btn btn-sm btn-outline-primary add-bowler">
                            + Add Bowler
                        </button>
                    </div>

                    <div class="bowler-list">
                        @foreach($bowlers as $rowIndex => $bowler)
                            @php
                                $savedBowlerName = $bowler['name'] ?? '';
                                $bowlerIsKnown = $players->contains(
                                    fn($player) =>
                                        (int) $player->team_id === $bowlingTeamId &&
                                        $player->name === $savedBowlerName
                                );
                            @endphp

                            <div class="bowler-row border rounded p-3 mb-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">Bowler</label>

                                        <select class="form-select player-select bowler-player"
                                                name="innings[{{ $inningIndex }}][bowlers][{{ $rowIndex }}][name]"
                                                data-saved-name="{{ $savedBowlerName }}"
                                                data-other-name="{{ $bowlerIsKnown ? '' : $savedBowlerName }}">
                                            <option value="">-- Select bowler --</option>
                                            @foreach($players as $player)
                                                @if((int) $player->team_id === $bowlingTeamId)
                                                    <option value="{{ $player->name }}"
                                                        data-team-id="{{ $player->team_id }}"
                                                        @selected($player->name === $savedBowlerName)>
                                                        {{ $player->name }}
                                                        {{ $player->role ? ' — '.$player->role : '' }}
                                                    </option>
                                                @endif
                                            @endforeach
                                            <option value="__other__"
                                                @selected($savedBowlerName !== '' && !$bowlerIsKnown)>
                                                Other player…
                                            </option>
                                        </select>

                                        <input type="text"
                                               class="form-control mt-2 custom-player-name"
                                               name="innings[{{ $inningIndex }}][bowlers][{{ $rowIndex }}][custom_name]"
                                               value="{{ $bowlerIsKnown ? '' : $savedBowlerName }}"
                                               placeholder="Enter player name"
                                               style="{{ $savedBowlerName !== '' && !$bowlerIsKnown ? '' : 'display:none' }}">
                                    </div>

                                    @foreach([
                                        'overs' => 'Overs',
                                        'maidens' => 'Maidens',
                                        'runs' => 'Runs',
                                        'wickets' => 'Wickets',
                                        'economy' => 'Economy',
                                    ] as $field => $label)
                                        <div class="col-lg col-md-3 col-6">
                                            <label class="form-label">{{ $label }}</label>
                                            <input type="number" min="0" step="{{ in_array($field, ['overs', 'economy']) ? '0.1' : '1' }}"
                                                   class="form-control"
                                                   name="innings[{{ $inningIndex }}][bowlers][{{ $rowIndex }}][{{ $field }}]"
                                                   value="{{ $bowler[$field] ?? 0 }}">
                                        </div>
                                    @endforeach

                                    <div class="col-lg-1 col-md-2 col-6">
                                        <button type="button"
                                                class="btn btn-outline-danger w-100 remove-row"
                                                title="Remove bowler">
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        <div class="d-flex flex-wrap gap-2 justify-content-end mb-5">
            <a href="{{ route('admin.scorecards.index') }}"
               class="btn btn-light border">Cancel</a>

            <button type="submit" class="btn btn-primary px-4">
                Save Scorecard
            </button>
        </div>
    </form>
</div>

{{-- All player options are rendered by Blade as real HTML options. --}}
<template id="all-player-options">
    <option value="">-- Select player --</option>

    @foreach($players as $player)
        <option value="{{ $player->name }}"
                data-team-id="{{ $player->team_id }}">
            {{ $player->name }}{{ $player->role ? ' — '.$player->role : '' }}
        </option>
    @endforeach

    <option value="__other__">Other player…</option>
</template>

{{-- Templates for dynamically added rows --}}
<template id="new-batter-row">
    <div class="batter-row border rounded p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Batter</label>
                <select class="form-select player-select batter-player">
                    <option value="">-- Select batter --</option>
                </select>
                <input type="text" class="form-control mt-2 custom-player-name"
                       placeholder="Enter player name" style="display:none">
            </div>

            @foreach(['runs' => 'Runs', 'balls' => 'Balls', 'fours' => '4s', 'sixes' => '6s', 'strike_rate' => 'Strike Rate'] as $field => $label)
                <div class="col-lg col-md-3 col-6">
                    <label class="form-label">{{ $label }}</label>
                    <input type="number" min="0"
                           step="{{ $field === 'strike_rate' ? '0.01' : '1' }}"
                           class="form-control" data-field="{{ $field }}" value="0">
                </div>
            @endforeach

            <div class="col-lg-1 col-md-2 col-6">
                <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
            </div>
        </div>
    </div>
</template>

<template id="new-bowler-row">
    <div class="bowler-row border rounded p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Bowler</label>
                <select class="form-select player-select bowler-player">
                    <option value="">-- Select bowler --</option>
                </select>
                <input type="text" class="form-control mt-2 custom-player-name"
                       placeholder="Enter player name" style="display:none">
            </div>

            @foreach(['overs' => 'Overs', 'maidens' => 'Maidens', 'runs' => 'Runs', 'wickets' => 'Wickets', 'economy' => 'Economy'] as $field => $label)
                <div class="col-lg col-md-3 col-6">
                    <label class="form-label">{{ $label }}</label>
                    <input type="number" min="0"
                           step="{{ in_array($field, ['overs', 'economy']) ? '0.1' : '1' }}"
                           class="form-control" data-field="{{ $field }}" value="0">
                </div>
            @endforeach

            <div class="col-lg-1 col-md-2 col-6">
                <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
            </div>
        </div>
    </div>
</template>

<style>
    .innings-section .form-label {
        font-size: .875rem;
        font-weight: 600;
    }

    .player-select,
    .team-select {
        min-height: 42px;
    }

    .custom-player-name {
        max-width: 100%;
    }

    .batter-row,
    .bowler-row {
        background: #fff;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const optionTemplate = document.getElementById('all-player-options');

    function getAllOptions() {
        return Array.from(optionTemplate.content.querySelectorAll('option'));
    }

    function refreshPlayerSelect(select, teamId, savedName = '') {
        const previousValue = savedName || select.value;
        const allOptions = getAllOptions();

        select.replaceChildren();

        allOptions.forEach(function (sourceOption) {
            const value = sourceOption.value;
            const optionTeamId = sourceOption.dataset.teamId;

            if (value === '') {
                select.appendChild(sourceOption.cloneNode(true));
                return;
            }

            if (value === '__other__') {
                select.appendChild(sourceOption.cloneNode(true));
                return;
            }

            if (String(optionTeamId) === String(teamId)) {
                select.appendChild(sourceOption.cloneNode(true));
            }
        });

        const matchingOption = Array.from(select.options).find(
            option => option.value === previousValue
        );

        if (matchingOption) {
            select.value = previousValue;
        } else if (previousValue) {
            select.value = '__other__';
        } else {
            select.value = '';
        }

        updateCustomName(select);
    }

    function updateCustomName(select) {
        const row = select.closest('.batter-row, .bowler-row');
        if (!row) return;

        const customInput = row.querySelector('.custom-player-name');
        if (!customInput) return;

        const isOther = select.value === '__other__';

        customInput.style.display = isOther ? '' : 'none';
        customInput.required = isOther;

        if (!isOther) {
            customInput.value = '';
        }
    }

    function updateInnings(section) {
        const battingTeam = section.querySelector('.batting-team');
        const bowlingTeam = section.querySelector('.bowling-team');

        const battingTeamId = battingTeam.value;
        const bowlingTeamId = bowlingTeam.value;

        section.querySelectorAll('.batter-player').forEach(function (select) {
            const savedName = select.dataset.savedName || select.value;
            refreshPlayerSelect(select, battingTeamId, savedName);
            select.dataset.savedName = '';
        });

        section.querySelectorAll('.bowler-player').forEach(function (select) {
            const savedName = select.dataset.savedName || select.value;
            refreshPlayerSelect(select, bowlingTeamId, savedName);
            select.dataset.savedName = '';
        });

        // Batting and bowling teams must be different.
        Array.from(battingTeam.options).forEach(function (option) {
            option.disabled = option.value === bowlingTeamId;
        });

        Array.from(bowlingTeam.options).forEach(function (option) {
            option.disabled = option.value === battingTeamId;
        });
    }

    function setRowNames(row, section, type, rowIndex) {
        const inningsIndex = section.dataset.inningsIndex;
        const select = row.querySelector('.player-select');
        const customInput = row.querySelector('.custom-player-name');

        select.name = `innings[${inningsIndex}][${type}][${rowIndex}][name]`;
        customInput.name = `innings[${inningsIndex}][${type}][${rowIndex}][custom_name]`;

        row.querySelectorAll('[data-field]').forEach(function (input) {
            input.name = `innings[${inningsIndex}][${type}][${rowIndex}][${input.dataset.field}]`;
        });

        row.querySelectorAll('input[name]').forEach(function (input) {
            if (input === customInput || input.hasAttribute('data-field')) return;
            const field = input.name.match(/\[([a-z_]+)\]$/);
            if (field) {
                input.name = `innings[${inningsIndex}][${type}][${rowIndex}][${field[1]}]`;
            }
        });
    }

    function addRow(section, type) {
        const isBatter = type === 'batters';
        const list = section.querySelector(isBatter ? '.batter-list' : '.bowler-list');
        const template = document.getElementById(isBatter ? 'new-batter-row' : 'new-bowler-row');
        const row = template.content.firstElementChild.cloneNode(true);

        const index = list.querySelectorAll(isBatter ? '.batter-row' : '.bowler-row').length;

        // Add the literal Blade-rendered player options, then filter by team.
        const select = row.querySelector('.player-select');
        select.replaceChildren();

        getAllOptions().forEach(function (option) {
            select.appendChild(option.cloneNode(true));
        });

        setRowNames(row, section, type, index);
        list.appendChild(row);
        updateInnings(section);
    }

    document.querySelectorAll('.innings-section').forEach(function (section) {
        section.querySelector('.batting-team').addEventListener('change', function () {
            updateInnings(section);
        });

        section.querySelector('.bowling-team').addEventListener('change', function () {
            updateInnings(section);
        });

        section.querySelector('.add-batter').addEventListener('click', function () {
            addRow(section, 'batters');
        });

        section.querySelector('.add-bowler').addEventListener('click', function () {
            addRow(section, 'bowlers');
        });

        section.addEventListener('change', function (event) {
            if (event.target.matches('.player-select')) {
                updateCustomName(event.target);
            }
        });

        section.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-row');
            if (!removeButton) return;

            const row = removeButton.closest('.batter-row, .bowler-row');
            const list = row.parentElement;

            if (list.children.length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input').forEach(input => {
                    if (input.type === 'number') input.value = 0;
                    else input.value = '';
                });

                const select = row.querySelector('.player-select');
                if (select) select.value = '';

                updateCustomName(select);
            }

            // Re-index the submitted row names after removal.
            section.querySelectorAll('.batter-list .batter-row').forEach((row, index) => {
                setRowNames(row, section, 'batters', index);
            });

            section.querySelectorAll('.bowler-list .bowler-row').forEach((row, index) => {
                setRowNames(row, section, 'bowlers', index);
            });
        });

        updateInnings(section);
    });
});
</script>
@endsection
