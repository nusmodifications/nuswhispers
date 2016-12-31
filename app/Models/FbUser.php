<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Model;

class FbUser extends Model
{
    protected $table = 'fb_users';
    protected $primaryKey = 'fb_user_id';
    public $timestamps = false;
    protected $fillable = ['fb_user_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'fb_user_id' => 'string',
    ];

    /**
     * Defines favourites fb_users relationship to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favourites()
    {
        return $this->belongsToMany('NUSWhispers\Models\Confession', 'favourites', 'fb_user_id', 'confession_id');
    }
}
