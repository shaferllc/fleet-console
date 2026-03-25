<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetPollDailyStat extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'target_key',
        'stat_date',
        'sample_count',
        'ok_count',
        'latency_p50',
        'latency_p95',
        'aggregated_at',
    ];

    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'sample_count' => 'integer',
            'ok_count' => 'integer',
            'latency_p50' => 'integer',
            'latency_p95' => 'integer',
            'aggregated_at' => 'datetime',
        ];
    }
}
