<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConfessionQueue extends Model
{
    /** @use HasFactory<\Database\Factories\ConfessionQueueFactory> */
    use HasFactory;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'update_status_at' => 'datetime',
        'confession_queue_id' => 'string',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'confession_queue';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'confession_queue_id';

    /**
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributes should be mass-assignable.
     */
    protected $fillable = ['status_after', 'update_status_at'];

    /**
     * Defines confession relationship from model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Confession, $this>
     */
    public function confession()
    {
        return $this->belongsTo(Confession::class, 'confession_id', 'confession_id');
    }
}
