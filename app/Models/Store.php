<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores';
    protected $fillable = [
        'name',
        'description',
        'tailor_thumbnail',
        'address',
        'phone_number',
        'email',
        'owner_id'
    ];
}
