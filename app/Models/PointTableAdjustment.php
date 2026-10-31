<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTableAdjustment extends Model
{
    protected $fillable = [
        'season_id',
        'team_id',
        'penalty_points',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'season_id' => 'integer',
            'team_id' => 'integer',
            'penalty_points' => 'integer',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
