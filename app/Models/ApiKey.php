<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    /**
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'api_key_id' => 'string',
    ];

    /**
     * Attributes should be mass-assignable.
     */
    protected $fillable = ['user_id', 'key', 'last_used_on', 'created_on'];

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'api_key_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_keys';

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public static function generateKey()
    {
        return hash_hmac('sha256', Str::random(128), microtime());
    }

    /**
     * Automatically mutate the date fields.
     *
     * @return array
     */
    public function getDates()
    {
        return ['created_on', 'last_used_on'];
    }
}
