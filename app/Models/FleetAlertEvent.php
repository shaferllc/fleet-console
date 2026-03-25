<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetAlertEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'target_key',
        'type',
        'subject',
        'body',
        'channels',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
