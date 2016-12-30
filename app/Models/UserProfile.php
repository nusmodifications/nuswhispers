<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'profile_id' => 'string',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'profile_id';

    /**
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['provider_name', 'provider_id', 'provider_token', 'page_token', 'data'];

    /**
     * Defines user relationship from model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('NUSWhispers\Models\User');
    }
}
