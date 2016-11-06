<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfessionQueue extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function confession()
    {
        return $this->belongsTo('App\Models\Confession', 'confession_id');
    }

    /**
     * Automatically mutate the date fields.
     *
     * @return array
     */
    public function getDates()
    {
        return ['update_status_at'];
    }
}
