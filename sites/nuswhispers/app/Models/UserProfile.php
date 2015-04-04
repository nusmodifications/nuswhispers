<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model {

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * Primary key of the model.
     * @var string
     */
    protected $primaryKey = 'profile_id';

    /**
     * Disable timestamps functionality.
     * @var boolean
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
