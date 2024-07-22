<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreMaterial extends Model
{
    use HasFactory;

    protected $table = 'store_materials';
    protected $fillable = ['store_id', 'material_id', 'price'];
}
