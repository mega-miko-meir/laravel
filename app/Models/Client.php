<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'full_name',
        'organization_name',
        'organization_type',
        'specialty',
        'specialty2',
        'parent_organization',
        'workplace',
        'primary_address',
        'brick_name',
        'onekey_id',
        'city',
        'coordinates',
        'brick_label'
    ];
}
