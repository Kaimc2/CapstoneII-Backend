<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    use HasFactory;
    protected $table = 'designs';
    protected $fillable = [
        'name',
        'user_id',
        'design_thumbnail',
        'front_content',
        'back_content',
        'status',
        'deleted'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

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
