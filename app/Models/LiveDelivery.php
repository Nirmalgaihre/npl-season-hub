<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveDelivery extends Model
{
    protected $fillable = [
        'innings_id',
        'ball_number',
        'striker',
        'non_striker',
        'bowler',
        'batter_runs',
        'extra_runs',
        'extra_type',
        'is_wicket',
        'wicket_type',
        'dismissed_batter',
        'incoming_batter',
        'counts_as_legal_ball',
        'free_hit_before',
        'striker_after',
        'non_striker_after',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'is_wicket' => 'boolean',
            'counts_as_legal_ball' => 'boolean',
            'free_hit_before' => 'boolean',
            'batter_runs' => 'integer',
            'extra_runs' => 'integer',
            'ball_number' => 'integer',
        ];
    }

    public function innings(): BelongsTo
    {
        return $this->belongsTo(LiveInnings::class, 'innings_id');
    }

    public function totalRuns(): int
    {
        return (int) $this->batter_runs + (int) $this->extra_runs;
    }

    public function isBowlerChargedWicket(): bool
    {
        return $this->is_wicket && !in_array(
            strtolower((string) $this->wicket_type),
            [
                'run out',
                'retired hurt',
                'retired out',
                'obstructing the field',
            ],
            true
        );
    }
}
