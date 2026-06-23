<?php

namespace App\Models\Nobel;

use Illuminate\Database\Eloquent\Model;

class Kmp extends Model
{
    protected $connection = 'nobel';
    protected $table = 'kmp';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'Номер заказа Pharmcenter';
    protected $keyType = 'float';
}
