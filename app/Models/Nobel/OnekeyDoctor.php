<?php

namespace App\Models\Nobel;

use Illuminate\Database\Eloquent\Model;

class OnekeyDoctor extends Model
{
    protected $connection = 'nobel';
    protected $table = 'qs_onekey_doctors';
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    public $timestamps = false;
}
