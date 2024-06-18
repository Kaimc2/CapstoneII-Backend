<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'design_id',
        'tailor_id',
        'start_date',
        'status',
        'end_date',
        'deleted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted' => 'boolean',
    ];

    /**
     * Scope a query to only include non-deleted designs.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNonDeleted($query)
    {
        return $query->where('deleted', false);
    }
}
