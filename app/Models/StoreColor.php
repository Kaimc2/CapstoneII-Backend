<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreColor extends Model
{
    use HasFactory;

    protected $table = 'store_colors';
    protected $fillable = ['store_id', 'color_id', 'price'];
}
