<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
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

    public function confessions()
    {
        return $this->belongsToMany('App\Models\Confession', 'confession_categories', 'confession_category_id', 'confession_id');
    }

    public function scopeCategoryAsc($query)
    {
        return $query->orderBy('confession_category', 'ASC');
    }
}
