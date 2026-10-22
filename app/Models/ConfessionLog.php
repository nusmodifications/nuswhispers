<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConfessionLog extends Model
{
    /** @use HasFactory<\Database\Factories\ConfessionLogFactory> */
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'confession_logs';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_on' => 'datetime',
        'confession_log_id' => 'string',
    ];

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'confession_log_id';

    /**
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributes should be mass-assignable.
     */
    protected $fillable = ['status_before', 'status_after', 'changed_by_user', 'created_on'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Confession, $this>
     */
    public function confession()
    {
        return $this->belongsTo(Confession::class, 'confession_id', 'confession_id');
    }

    /**
     * Defines user relationship from model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by_user', 'user_id');
    }
}
