<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveInnings extends Model
{
    protected $fillable = [
        'fixture_id',
        'innings_number',
        'batting_team_id',
        'bowling_team_id',
        'striker',
        'non_striker',
        'current_bowler',
        'total_runs',
        'wickets',
        'legal_balls',
        'extras',
        'is_current',
        'free_hit_next',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'free_hit_next' => 'boolean',
            'innings_number' => 'integer',
            'total_runs' => 'integer',
            'wickets' => 'integer',
            'legal_balls' => 'integer',
            'extras' => 'integer',
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

    public function deliveries(): HasMany
    {
        return $this->hasMany(LiveDelivery::class, 'innings_id')
            ->orderBy('ball_number');
    }

    public function oversText(): string
    {
        return intdiv((int) $this->legal_balls, 6)
            . '.'
            . ((int) $this->legal_balls % 6);
    }

    public function runRate(): float
    {
        if ((int) $this->legal_balls === 0) {
            return 0.0;
        }

        return round(
            ((int) $this->total_runs * 6) / (int) $this->legal_balls,
            2
        );
    }
}
