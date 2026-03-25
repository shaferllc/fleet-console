<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetPollSample extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'target_key',
        'ok',
        'http_status',
        'latency_ms',
        'error_message',
        'summary_snapshot',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'ok' => 'boolean',
            'http_status' => 'integer',
            'latency_ms' => 'integer',
            'summary_snapshot' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
