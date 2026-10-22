<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    /** @use HasFactory<\Database\Factories\ApiKeyFactory> */
    use HasFactory;

    /**
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_on' => 'datetime',
        'last_used_on' => 'datetime',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public static function generateKey()
    {
        return hash_hmac('sha256', Str::random(128), microtime());
    }
}
