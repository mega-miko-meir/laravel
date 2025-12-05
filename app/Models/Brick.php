<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brick extends Model{
    use HasFactory;

    protected $fillable = ['country', 'code', 'description', 'additional_code'];

    public function territories(){
        return $this->belongsToMany(Territory::class, 'brick_territory'); // Связь many-to-many с моделью Territory
    }
}

