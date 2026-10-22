<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confession_category_id' => 'string',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'confession_category_id';

    /**
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Confession, $this>
     */
    public function confessions()
    {
        return $this->belongsToMany(Confession::class, 'confession_categories', 'confession_category_id', 'confession_id');
    }

    public function scopeCategoryAsc($query)
    {
        return $query->orderBy('confession_category', 'asc');
    }
}
