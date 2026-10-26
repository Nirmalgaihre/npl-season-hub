<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Season extends Model
{
    protected $fillable = [
        'name',
        'season_number',
        'slug',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'season_number' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'completed']);
    }
    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    return $this->belongsToMany(\App\Models\Team::class)
        ->withTimestamps();
}
}