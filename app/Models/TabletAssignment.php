<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabletAssignment extends Model
{
    protected $table = 'tablet_assignments';

    protected $fillable = [
        'employee_id',
        'tablet_id',
        'assigned_at',
        'returned_at'];
}
