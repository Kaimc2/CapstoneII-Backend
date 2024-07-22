<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSize extends Model
{
    use HasFactory;

    protected $table = 'store_sizes';
    protected $fillable = ['store_id', 'size_id', 'price'];
}
