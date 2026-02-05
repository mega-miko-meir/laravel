<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/ActivityLog.php
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'method',
        'ip',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

