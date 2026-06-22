<?php

namespace App\Models\Nobel;

use Illuminate\Database\Eloquent\Model;

class OnekeyPharmacy extends Model
{
    protected $connection = 'nobel';
    protected $table = 'qs_onekey_pharmacy';
    protected $primaryKey = 'organization_id';
    public $incrementing = false;
    public $timestamps = false;
}
