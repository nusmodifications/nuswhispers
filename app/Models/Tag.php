<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'confession_tag_id' => 'string',
    ];

    protected $table = 'tags';
    protected $fillable = ['confession_tag'];
    protected $primaryKey = 'confession_tag_id';
    public $timestamps = false;

    public function confessions()
    {
        return $this->belongsToMany(Confession::class, 'confession_tags', 'confession_tag_id', 'confession_id');
    }
}
