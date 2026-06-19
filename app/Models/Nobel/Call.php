<?php

namespace App\Models\Nobel;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $connection = 'nobel';
    protected $table = 'qs_calls';
    public $timestamps = false;

    protected $casts = [
        'appointment_Date' => 'date',
    ];
}
