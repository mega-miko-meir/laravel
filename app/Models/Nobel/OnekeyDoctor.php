<?php

namespace App\Models\Nobel;

use Illuminate\Database\Eloquent\Model;

class OnekeyDoctor extends Model
{
    protected $connection = 'nobel';
    protected $table = 'qs_onekey_doctors';
    public $timestamps = false;
}
