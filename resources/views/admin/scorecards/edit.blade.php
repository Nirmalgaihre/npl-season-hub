
@extends('layouts.admin')

@section('title', 'Edit Scorecard')

@section('content')
@php
    $homeTeam = $teams->firstWhere('id', $fixture->home_team_id);
    $awayTeam = $teams->firstWhere('id', $fixture->away_team_id);
@endphp

<div class="score-editor">

    {{-- Page heading --}}
    <div class="score-page-heading">
        <div>
            <a href="{{ route('admin.scorecards.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> All scorecards
            </a>
            <h2>Edit scorecard</h2>
            <p>Update the innings, select players and enter match statistics.</p>
        </div>

        <span class="editor-status">
            <span></span> Score editor
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Match overview --}}
    <div class="match-banner">
        <div class="match-banner-top">
            <span class="match-label">
                <i class="fas fa-cricket-bat-ball"></i>
                {{ $fixture->match_number ? 'MATCH '.$fixture->match_number : 'MATCH SCORECARD' }}
            </span>
            <span class="match-label muted-label">
                {{ ucfirst(str_replace('_', ' ', $fixture->status ?? 'scheduled')) }}
            </span>
        </div>

        <div class="match-teams">
            <div class="match-team">
                @if($homeTeam && $homeTeam->logo)
                    <img src="{{ asset('storage/'.$homeTeam->logo) }}"
                         alt="{{ $homeTeam->name }}" class="match-team-logo">
                @else
                    <div class="team-placeholder">
                        {{ strtoupper(substr($homeTeam->short_name ?? $homeTeam->name ?? 'H', 0, 3)) }}
                    </div>
                @endif

                <div>
                    <span class="team-side">HOME TEAM</span>
                    <h3>{{ $homeTeam->name ?? 'Home team' }}</h3>
                </div>
            </div>

            <div class="versus">VS</div>

            <div class="match-team away-team">
                <div class="away-team-info">
                    <span class="team-side">AWAY TEAM</span>
                    <h3>{{ $awayTeam->name ?? 'Away team' }}</h3>
                </div>

                @if($awayTeam && $awayTeam->logo)
                    <img src="{{ asset('storage/'.$awayTeam->logo) }}"
                         alt="{{ $awayTeam->name }}" class="match-team-logo">
                @else
                    <div class="team-placeholder">
                        {{ strtoupper(substr($awayTeam->short_name ?? $awayTeam->name ?? 'A', 0, 3)) }}
                    </div>
                @endif
            </div>
        </div>

        @if($fixture->venue)
            <div class="match-venue">
                <i class="fas fa-location-dot"></i> {{ $fixture->venue }}
            </div>
        @endif
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

                $batters = is_array($inning->batters ?? null) ? $inning->batters : [];
                $bowlers = is_array($inning->bowlers ?? null) ? $inning->bowlers : [];

                if (!$batters) {
                    $batters = [[
                        'name' => '', 'runs' => 0, 'balls' => 0,
                        'fours' => 0, 'sixes' => 0, 'strike_rate' => 0,
                    ]];
                }

                if (!$bowlers) {
                    $bowlers = [[
                        'name' => '', 'overs' => 0, 'maidens' => 0,
                        'runs' => 0, 'wickets' => 0, 'economy' => 0,
                    ]];
                }
            @endphp

            <section class="innings-card innings-section"
                     data-innings-index="{{ $inningIndex }}">

                <div class="innings-heading">
                    <div class="innings-title">
                        <span class="innings-number">{{ str_pad($inning->innings_number ?? ($inningIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3>Innings {{ $inning->innings_number ?? ($inningIndex + 1) }}</h3>
                            <p>Match scoring and player statistics</p>
                        </div>
                    </div>

                    <input type="hidden"
                           name="innings[{{ $inningIndex }}][innings_number]"
                           value="{{ $inning->innings_number ?? ($inningIndex + 1) }}">
                </div>

                <div class="innings-body">

                    {{-- Teams --}}
                    <div class="team-selection">
                        <div class="field-group">
                            <label>
                                <span class="label-dot batting-dot"></span>
                                BATTING TEAM
                            </label>

                            <select class="form-select batting-team team-select"
                                    name="innings[{{ $inningIndex }}][batting_team_id]"
                                    required>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}"
                                        @selected($battingTeamId === (int) $team->id)>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="swap-indicator">
                            <i class="fas fa-arrow-right-arrow-left"></i>
                        </div>

                        <div class="field-group">
                            <label>
                                <span class="label-dot bowling-dot"></span>
                                BOWLING TEAM
                            </label>

                            <select class="form-select bowling-team team-select"
                                    name="innings[{{ $inningIndex }}][bowling_team_id]"
                                    required>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}"
                                        @selected($bowlingTeamId === (int) $team->id)>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Main score --}}
                    <div class="score-input-section">
                        <div class="section-heading">
                            <div>
                                <h4>Match score</h4>
                                <p>Enter the current innings totals.</p>
                            </div>
                        </div>

                        <div class="score-input-grid">
                            <div class="score-input runs-input">
                                <label>Total runs</label>
                                <input type="number" min="0" step="1"
                                       name="innings[{{ $inningIndex }}][total_runs]"
                                       value="{{ $inning->total_runs ?? 0 }}"
                                       required>
                                <span>RUNS</span>
                            </div>

                            <div class="score-input">
                                <label>Wickets</label>
                                <input type="number" min="0" max="10" step="1"
                                       name="innings[{{ $inningIndex }}][wickets]"
                                       value="{{ $inning->wickets ?? 0 }}"
                                       required>
                                <span>WICKETS</span>
                            </div>

                            <div class="score-input">
                                <label>Overs</label>
                                <input type="number" min="0" max="999" step="0.1"
                                       name="innings[{{ $inningIndex }}][overs]"
                                       value="{{ $inning->overs ?? '0.0' }}"
                                       required>
                                <span>OVERS</span>
                            </div>

                            <div class="score-input">
                                <label>Extras</label>
                                <input type="number" min="0" step="1"
                                       name="innings[{{ $inningIndex }}][extras]"
                                       value="{{ $inning->extras ?? 0 }}"
                                       required>
                                <span>EXTRAS</span>
                            </div>
                        </div>
                    </div>

                    {{-- Batters --}}
                    <div class="players-section">
                        <div class="section-heading">
                            <div class="player-heading">
                                <span class="player-icon batter-icon">
                                    <i class="fas fa-baseball-bat-ball"></i>
                                </span>
                                <div>
                                    <h4>Batting card</h4>
                                    <p>Select players from the batting team.</p>
                                </div>
                            </div>

                            <button type="button" class="btn-add add-batter">
                                <i class="fas fa-plus"></i> Add batter
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="player-table">
                                <thead>
                                    <tr>
                                        <th class="player-column">PLAYER</th>
                                        <th>RUNS</th>
                                        <th>BALLS</th>
                                        <th>4s</th>
                                        <th>6s</th>
                                        <th>SR</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="batter-list">
                                    @foreach($batters as $rowIndex => $batter)
                                        @php
                                            $savedName = $batter['name'] ?? '';
                                            $knownPlayer = $players->first(
                                                fn($p) => (int) $p->team_id === $battingTeamId
                                                    && $p->name === $savedName
                                            );
                                        @endphp

                                        <tr class="batter-row">
                                            <td class="player-column">
                                                <select class="form-select player-select batter-player"
                                                        name="innings[{{ $inningIndex }}][batters][{{ $rowIndex }}][name]"
                                                        data-saved-name="{{ $savedName }}"
                                                        required>
                                                    <option value="">Select batter</option>
                                                    @foreach($players as $player)
                                                        @if((int) $player->team_id === $battingTeamId)
                                                            <option value="{{ $player->name }}"
                                                                data-team-id="{{ $player->team_id }}"
                                                                @selected($player->name === $savedName)>
                                                                {{ $player->name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                    <option value="__other__"
                                                        @selected($savedName !== '' && !$knownPlayer)>
                                                        Other player…
                                                    </option>
                                                </select>

                                                <input type="text"
                                                       class="form-control custom-player-name mt-2"
                                                       name="innings[{{ $inningIndex }}][batters][{{ $rowIndex }}][custom_name]"
                                                       value="{{ $knownPlayer ? '' : $savedName }}"
                                                       placeholder="Enter player name"
                                                       style="{{ $savedName !== '' && !$knownPlayer ? '' : 'display:none' }}">
                                            </td>

                                            @foreach(['runs', 'balls', 'fours', 'sixes', 'strike_rate'] as $field)
                                                <td>
                                                    <input type="number"
                                                           min="0"
                                                           step="{{ $field === 'strike_rate' ? '0.01' : '1' }}"
                                                           class="form-control stat-input"
                                                           name="innings[{{ $inningIndex }}][batters][{{ $rowIndex }}][{{ $field }}]"
                                                           value="{{ $batter[$field] ?? 0 }}">
                                                </td>
                                            @endforeach

                                            <td>
                                                <button type="button"
                                                        class="btn-remove remove-row"
                                                        aria-label="Remove batter">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Bowlers --}}
                    <div class="players-section bowling-section">
                        <div class="section-heading">
                            <div class="player-heading">
                                <span class="player-icon bowler-icon">
                                    <i class="fas fa-bullseye"></i>
                                </span>
                                <div>
                                    <h4>Bowling card</h4>
                                    <p>Select players from the bowling team.</p>
                                </div>
                            </div>

                            <button type="button" class="btn-add add-bowler">
                                <i class="fas fa-plus"></i> Add bowler
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="player-table">
                                <thead>
                                    <tr>
                                        <th class="player-column">PLAYER</th>
                                        <th>OVERS</th>
                                        <th>MAIDENS</th>
                                        <th>RUNS</th>
                                        <th>WKTS</th>
                                        <th>ECON</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="bowler-list">
                                    @foreach($bowlers as $rowIndex => $bowler)
                                        @php
                                            $savedName = $bowler['name'] ?? '';
                                            $knownPlayer = $players->first(
                                                fn($p) => (int) $p->team_id === $bowlingTeamId
                                                    && $p->name === $savedName
                                            );
                                        @endphp

                                        <tr class="bowler-row">
                                            <td class="player-column">
                                                <select class="form-select player-select bowler-player"
                                                        name="innings[{{ $inningIndex }}][bowlers][{{ $rowIndex }}][name]"
                                                        data-saved-name="{{ $savedName }}"
                                                        required>
                                                    <option value="">Select bowler</option>
                                                    @foreach($players as $player)
                                                        @if((int) $player->team_id === $bowlingTeamId)
                                                            <option value="{{ $player->name }}"
                                                                data-team-id="{{ $player->team_id }}"
                                                                @selected($player->name === $savedName)>
                                                                {{ $player->name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                    <option value="__other__"
                                                        @selected($savedName !== '' && !$knownPlayer)>
                                                        Other player…
                                                    </option>
                                                </select>

                                                <input type="text"
                                                       class="form-control custom-player-name mt-2"
                                                       name="innings[{{ $inningIndex }}][bowlers][{{ $rowIndex }}][custom_name]"
                                                       value="{{ $knownPlayer ? '' : $savedName }}"
                                                       placeholder="Enter player name"
                                                       style="{{ $savedName !== '' && !$knownPlayer ? '' : 'display:none' }}">
                                            </td>

                                            @foreach(['overs', 'maidens', 'runs', 'wickets', 'economy'] as $field)
                                                <td>
                                                    <input type="number"
                                                           min="0"
                                                           step="{{ in_array($field, ['overs', 'economy']) ? '0.1' : '1' }}"
                                                           class="form-control stat-input"
                                                           name="innings[{{ $inningIndex }}][bowlers][{{ $rowIndex }}][{{ $field }}]"
                                                           value="{{ $bowler[$field] ?? 0 }}">
                                                </td>
                                            @endforeach

                                            <td>
                                                <button type="button"
                                                        class="btn-remove remove-row"
                                                        aria-label="Remove bowler">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        @endforeach

        {{-- Sticky save controls --}}
        <div class="save-bar">
            <div class="save-bar-info">
                <i class="fas fa-circle-check"></i>
                <span>Your changes are saved when you click Save scorecard.</span>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.scorecards.index') }}"
                   class="btn-cancel">Cancel</a>

                <button type="submit" class="btn-save" id="save-button">
                    <i class="fas fa-floppy-disk"></i>
                    Save scorecard
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Native HTML player options generated from the database --}}
<template id="all-player-options">
    <option value="">Select player</option>
    @foreach($players as $player)
        <option value="{{ $player->name }}"
                data-team-id="{{ $player->team_id }}">
            {{ $player->name }}{{ $player->role ? ' — '.$player->role : '' }}
        </option>
    @endforeach
    <option value="__other__">Other player…</option>
</template>

<template id="new-batter-template">
    <tr class="batter-row">
        <td class="player-column">
            <select class="form-select player-select batter-player" required>
                <option value="">Select batter</option>
            </select>
            <input type="text" class="form-control custom-player-name mt-2"
                   placeholder="Enter player name" style="display:none">
        </td>
        @foreach(['runs', 'balls', 'fours', 'sixes', 'strike_rate'] as $field)
            <td>
                <input type="number" min="0"
                       step="{{ $field === 'strike_rate' ? '0.01' : '1' }}"
                       class="form-control stat-input"
                       data-field="{{ $field }}" value="0">
            </td>
        @endforeach
        <td>
            <button type="button" class="btn-remove remove-row">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<template id="new-bowler-template">
    <tr class="bowler-row">
        <td class="player-column">
            <select class="form-select player-select bowler-player" required>
                <option value="">Select bowler</option>
            </select>
            <input type="text" class="form-control custom-player-name mt-2"
                   placeholder="Enter player name" style="display:none">
        </td>
        @foreach(['overs', 'maidens', 'runs', 'wickets', 'economy'] as $field)
            <td>
                <input type="number" min="0"
                       step="{{ in_array($field, ['overs', 'economy']) ? '0.1' : '1' }}"
                       class="form-control stat-input"
                       data-field="{{ $field }}" value="0">
            </td>
        @endforeach
        <td>
            <button type="button" class="btn-remove remove-row">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<style>
.score-editor {
    --score-red: #c6283d;
    --score-red-dark: #a91f32;
    --score-text: #202633;
    --score-muted: #778092;
    --score-border: #e7eaf0;
    color: var(--score-text);
    padding-bottom: 100px;
}

.score-page-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 24px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    color: #687386;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.back-link:hover {
    color: var(--score-red);
}

.score-page-heading h2 {
    margin: 0;
    font-size: clamp(24px, 3vw, 31px);
    font-weight: 800;
    letter-spacing: -1px;
}

.score-page-heading p,
.innings-title p,
.section-heading p {
    margin: 5px 0 0;
    color: var(--score-muted);
    font-size: 13px;
}

.editor-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--score-border);
    border-radius: 30px;
    padding: 9px 13px;
    background: #fff;
    color: #586274;
    font-size: 12px;
    font-weight: 700;
}

.editor-status span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #16a36a;
}

.match-banner {
    overflow: hidden;
    margin-bottom: 24px;
    border: 1px solid #262d3b;
    border-radius: 16px;
    padding: 22px;
    background: #171d29;
    color: #fff;
}

.match-banner-top {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.match-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.1px;
}

.match-label i {
    margin-right: 5px;
    color: #ff697a;
}

.muted-label {
    color: #aeb7c6;
}

.match-teams {
    display: grid;
    grid-template-columns: 1fr 50px 1fr;
    align-items: center;
    gap: 16px;
    padding: 27px 0 20px;
}

.match-team {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.away-team {
    justify-content: flex-end;
}

.match-team h3 {
    margin: 5px 0 0;
    color: #fff;
    font-size: clamp(15px, 2vw, 21px);
    font-weight: 750;
}

.team-side {
    color: #9da8bb;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
}

.match-team-logo,
.team-placeholder {
    width: 53px;
    height: 53px;
    flex: 0 0 53px;
    border-radius: 13px;
    object-fit: contain;
    background: #fff;
    padding: 5px;
}

.team-placeholder {
    display: grid;
    place-items: center;
    color: #242b38;
    font-size: 12px;
    font-weight: 900;
}

.versus {
    display: grid;
    place-items: center;
    width: 43px;
    height: 43px;
    border: 1px solid #454c5a;
    border-radius: 50%;
    color: #d0d5df;
    font-size: 11px;
    font-weight: 800;
}

.match-venue {
    border-top: 1px solid #343b49;
    padding-top: 14px;
    color: #b4becc;
    font-size: 12px;
}

.match-venue i {
    margin-right: 6px;
    color: #ff697a;
}

.innings-card {
    overflow: hidden;
    margin-bottom: 25px;
    border: 1px solid var(--score-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 20px rgb(28 38 56 / 3%);
}

.innings-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--score-border);
    padding: 19px 22px;
    background: #fcfcfd;
}

.innings-title {
    display: flex;
    align-items: center;
    gap: 13px;
}

.innings-number {
    display: grid;
    place-items: center;
    width: 43px;
    height: 43px;
    border-radius: 12px;
    background: #fff0f2;
    color: var(--score-red);
    font-size: 15px;
    font-weight: 900;
}

.innings-title h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
}

.innings-body {
    padding: 22px;
}

.team-selection {
    display: grid;
    grid-template-columns: 1fr 42px 1fr;
    align-items: end;
    gap: 14px;
    margin-bottom: 29px;
}

.field-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 9px;
    font-size: 10px;
    font-weight: 850;
    letter-spacing: .8px;
}

.label-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.batting-dot {
    background: #c6283d;
}

.bowling-dot {
    background: #3676df;
}

.field-group .form-select {
    min-height: 47px;
    border-color: #dfe4ec;
    border-radius: 9px;
    color: #252d3b;
    font-size: 13px;
    font-weight: 650;
}

.swap-indicator {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    margin-bottom: 4px;
    border: 1px solid var(--score-border);
    border-radius: 10px;
    color: #727d8e;
}

.score-input-section {
    margin-bottom: 31px;
    border-radius: 13px;
    padding: 20px;
    background: #f7f8fb;
}

.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.section-heading h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
}

.score-input-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 13px;
}

.score-input {
    position: relative;
    overflow: hidden;
    border: 1px solid #e4e8ef;
    border-radius: 11px;
    padding: 13px 14px 11px;
    background: #fff;
}

.score-input label {
    display: block;
    margin-bottom: 7px;
    color: #6d7788;
    font-size: 11px;
    font-weight: 700;
}

.score-input input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    padding: 0;
    color: #202633;
    background: transparent;
    font-size: 27px;
    font-weight: 850;
}

.score-input input:focus {
    color: var(--score-red);
}

.score-input > span {
    display: block;
    margin-top: 5px;
    color: #a1a9b7;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1px;
}

.runs-input {
    border-color: #f0c8ce;
}

.runs-input input {
    color: var(--score-red);
}

.players-section {
    margin-top: 30px;
}

.player-heading {
    display: flex;
    align-items: center;
    gap: 12px;
}

.player-icon {
    display: grid;
    place-items: center;
    width: 39px;
    height: 39px;
    border-radius: 11px;
}

.batter-icon {
    background: #fff0f2;
    color: #c6283d;
}

.bowler-icon {
    background: #edf4ff;
    color: #3676df;
}

.btn-add {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 1px solid #e5c2c8;
    border-radius: 8px;
    padding: 9px 12px;
    background: #fff;
    color: var(--score-red);
    font-size: 12px;
    font-weight: 750;
    white-space: nowrap;
}

.btn-add:hover {
    background: #fff1f3;
}

.player-table {
    width: 100%;
    min-width: 650px;
    border-collapse: separate;
    border-spacing: 0;
}

.player-table thead th {
    border-top: 1px solid #e9ecf1;
    border-bottom: 1px solid #e9ecf1;
    padding: 12px 8px;
    background: #f8f9fb;
    color: #798395;
    font-size: 9px;
    font-weight: 850;
    letter-spacing: .7px;
    text-align: center;
    white-space: nowrap;
}

.player-table thead th:first-child {
    border-left: 1px solid #e9ecf1;
    border-radius: 9px 0 0 0;
    text-align: left;
}

.player-table thead th:last-child {
    border-right: 1px solid #e9ecf1;
    border-radius: 0 9px 0 0;
}

.player-table tbody td {
    border-bottom: 1px solid #edf0f4;
    padding: 10px 6px;
    text-align: center;
    vertical-align: top;
}

.player-table .player-column {
    width: 32%;
    min-width: 180px;
    padding-left: 0;
    text-align: left;
}

.player-table tbody .player-column {
    padding-left: 0;
}

.player-select,
.stat-input {
    min-height: 39px;
    border-color: #e0e5ed;
    border-radius: 7px;
    font-size: 12px;
}

.player-select {
    min-width: 150px;
    font-weight: 600;
}

.stat-input {
    width: 65px;
    min-width: 52px;
    padding: 6px;
    text-align: center;
    font-weight: 700;
}

.stat-input:focus,
.player-select:focus,
.custom-player-name:focus {
    border-color: #d45a6a;
    box-shadow: 0 0 0 3px rgb(198 40 61 / 8%);
}

.custom-player-name {
    font-size: 12px;
}

.btn-remove {
    display: inline-grid;
    place-items: center;
    width: 34px;
    height: 36px;
    border: 1px solid #f1d7db;
    border-radius: 7px;
    background: #fff;
    color: #bd3345;
}

.btn-remove:hover {
    background: #fff0f2;
}

.save-bar {
    position: sticky;
    bottom: 12px;
    z-index: 20;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 25px;
    border: 1px solid #e3e7ee;
    border-radius: 13px;
    padding: 13px 16px;
    background: rgb(255 255 255 / 96%);
    box-shadow: 0 8px 35px rgb(24 33 49 / 12%);
    backdrop-filter: blur(10px);
}

.save-bar-info {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #727d8e;
    font-size: 11px;
}

.save-bar-info i {
    color: #169968;
}

.btn-cancel,
.btn-save {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    border-radius: 8px;
    padding: 0 17px;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
}

.btn-cancel {
    border: 1px solid #dfe4ec;
    background: #fff;
    color: #535e70;
}

.btn-save {
    border: 1px solid var(--score-red);
    background: var(--score-red);
    color: #fff;
}

.btn-save:hover {
    border-color: var(--score-red-dark);
    background: var(--score-red-dark);
    color: #fff;
}

@media (max-width: 767px) {
    .score-page-heading {
        align-items: flex-start;
    }

    .editor-status {
        display: none;
    }

    .match-banner {
        padding: 16px;
    }

    .match-teams {
        grid-template-columns: minmax(0, 1fr) 35px minmax(0, 1fr);
        gap: 8px;
        padding: 23px 0 17px;
    }

    .match-team {
        gap: 8px;
    }

    .match-team-logo,
    .team-placeholder {
        width: 37px;
        height: 37px;
        flex-basis: 37px;
    }

    .match-team h3 {
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .team-side {
        font-size: 8px;
    }

    .away-team-info {
        text-align: right;
    }

    .versus {
        width: 33px;
        height: 33px;
        font-size: 9px;
    }

    .innings-heading,
    .innings-body {
        padding: 16px;
    }

    .team-selection {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .swap-indicator {
        display: none;
    }

    .score-input-section {
        padding: 13px;
    }

    .score-input-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .score-input input {
        font-size: 24px;
    }

    .section-heading {
        align-items: flex-start;
    }

    .player-heading {
        gap: 8px;
    }

    .player-icon {
        width: 33px;
        height: 33px;
        flex-shrink: 0;
    }

    .btn-add {
        padding: 8px;
        font-size: 10px;
    }

    .save-bar {
        bottom: 6px;
        padding: 10px;
    }

    .save-bar-info {
        display: none;
    }

    .save-bar > div:last-child {
        width: 100%;
    }

    .btn-cancel,
    .btn-save {
        flex: 1;
        padding: 0 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const optionTemplate = document.getElementById('all-player-options');

    function allPlayerOptions() {
        return Array.from(optionTemplate.content.querySelectorAll('option'));
    }

    function filterPlayerSelect(select, teamId, savedName = '') {
        const currentValue = savedName || select.value;
        select.replaceChildren();

        allPlayerOptions().forEach(function (option) {
            if (
                option.value === '' ||
                option.value === '__other__' ||
                String(option.dataset.teamId) === String(teamId)
            ) {
                select.appendChild(option.cloneNode(true));
            }
        });

        const exists = Array.from(select.options).some(
            option => option.value === currentValue
        );

        if (exists) {
            select.value = currentValue;
        } else if (currentValue) {
            select.value = '__other__';
        } else {
            select.value = '';
        }

        toggleCustomName(select);
    }

    function toggleCustomName(select) {
        if (!select) return;

        const row = select.closest('tr');
        const input = row?.querySelector('.custom-player-name');

        if (!input) return;

        const isOther = select.value === '__other__';
        input.style.display = isOther ? '' : 'none';
        input.required = isOther;

        if (!isOther) {
            input.value = '';
        }
    }

    function refreshInnings(section) {
        const battingTeam = section.querySelector('.batting-team');
        const bowlingTeam = section.querySelector('.bowling-team');

        section.querySelectorAll('.batter-player').forEach(select => {
            filterPlayerSelect(
                select,
                battingTeam.value,
                select.dataset.savedName || select.value
            );
            select.dataset.savedName = '';
        });

        section.querySelectorAll('.bowler-player').forEach(select => {
            filterPlayerSelect(
                select,
                bowlingTeam.value,
                select.dataset.savedName || select.value
            );
            select.dataset.savedName = '';
        });

        Array.from(battingTeam.options).forEach(option => {
            option.disabled = option.value === bowlingTeam.value;
        });

        Array.from(bowlingTeam.options).forEach(option => {
            option.disabled = option.value === battingTeam.value;
        });
    }

    function renumberRows(section, type) {
        const isBatter = type === 'batters';
        const selector = isBatter ? '.batter-row' : '.bowler-row';
        const listSelector = isBatter ? '.batter-list' : '.bowler-list';
        const playerSelector = isBatter ? '.batter-player' : '.bowler-player';
        const index = section.dataset.inningsIndex;

        section.querySelectorAll(listSelector + ' ' + selector).forEach((row, rowIndex) => {
            const select = row.querySelector(playerSelector);
            const custom = row.querySelector('.custom-player-name');

            select.name = `innings[${index}][${type}][${rowIndex}][name]`;
            custom.name = `innings[${index}][${type}][${rowIndex}][custom_name]`;

            row.querySelectorAll('[data-field]').forEach(input => {
                input.name = `innings[${index}][${type}][${rowIndex}][${input.dataset.field}]`;
            });

            const fields = isBatter
                ? ['runs', 'balls', 'fours', 'sixes', 'strike_rate']
                : ['overs', 'maidens', 'runs', 'wickets', 'economy'];

            fields.forEach(field => {
                const input = row.querySelector(`[name$="[${field}]"]`);
                if (input) {
                    input.name = `innings[${index}][${type}][${rowIndex}][${field}]`;
                }
            });
        });
    }

    function addPlayerRow(section, type) {
        const isBatter = type === 'batters';
        const list = section.querySelector(isBatter ? '.batter-list' : '.bowler-list');
        const template = document.getElementById(isBatter ? 'new-batter-template' : 'new-bowler-template');
        const row = template.content.firstElementChild.cloneNode(true);
        const select = row.querySelector('.player-select');

        allPlayerOptions().forEach(option => {
            select.appendChild(option.cloneNode(true));
        });

        list.appendChild(row);
        renumberRows(section, type);
        refreshInnings(section);
    }

    document.querySelectorAll('.innings-section').forEach(section => {
        section.querySelector('.batting-team').addEventListener('change', () => {
            refreshInnings(section);
        });

        section.querySelector('.bowling-team').addEventListener('change', () => {
            refreshInnings(section);
        });

        section.querySelector('.add-batter').addEventListener('click', () => {
            addPlayerRow(section, 'batters');
        });

        section.querySelector('.add-bowler').addEventListener('click', () => {
            addPlayerRow(section, 'bowlers');
        });

        section.addEventListener('change', event => {
            if (event.target.matches('.player-select')) {
                toggleCustomName(event.target);
            }
        });

        section.addEventListener('click', event => {
            const button = event.target.closest('.remove-row');
            if (!button) return;

            const row = button.closest('.batter-row, .bowler-row');
            const type = row.classList.contains('batter-row') ? 'batters' : 'bowlers';
            const list = row.parentElement;

            if (list.children.length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input').forEach(input => {
                    input.value = input.classList.contains('custom-player-name') ? '' : 0;
                });

                const select = row.querySelector('.player-select');
                select.value = '';
                toggleCustomName(select);
            }

            renumberRows(section, type);
        });

        refreshInnings(section);
    });

    document.getElementById('scorecard-form').addEventListener('submit', function () {
        const button = document.getElementById('save-button');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    });
});
</script>
@endsection
