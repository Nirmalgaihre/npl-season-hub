<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    protected $fillable = [
        'season_id',
        'home_team_id',
        'away_team_id',
        'match_number',
        'match_type',
        'scheduled_at',
        'venue',
        'status',
        'home_runs',
        'home_wickets',
        'home_overs',
        'away_runs',
        'away_wickets',
        'away_overs',
        'winner_team_id',
        'player_of_match',
        'result_summary',
        'is_result_verified',
        'result_verified_by',

        // Toss information
        'toss_winner_team_id',
        'toss_decision',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'home_runs' => 'integer',
            'home_wickets' => 'integer',
            'home_overs' => 'decimal:1',
            'away_runs' => 'integer',
            'away_wickets' => 'integer',
            'away_overs' => 'decimal:1',
            'is_result_verified' => 'boolean',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function resultVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_verified_by');
    }

    public function tossWinner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'toss_winner_team_id');
    }
}
