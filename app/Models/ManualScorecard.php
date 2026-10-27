<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualScorecard extends Model
{
    protected $fillable = [
        'fixture_id',
        'innings_number',
        'batting_team_id',
        'bowling_team_id',
        'total_runs',
        'wickets',
        'overs',
        'extras',
        'batters',
        'bowlers',
    ];

    protected function casts(): array
    {
        return [
            'innings_number' => 'integer',
            'total_runs' => 'integer',
            'wickets' => 'integer',
            'overs' => 'decimal:1',
            'extras' => 'integer',
            'batters' => 'array',
            'bowlers' => 'array',
        ];
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    public function battingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'batting_team_id');
    }

    public function bowlingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'bowling_team_id');
    }
}
