<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Model;

class ConfessionLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'confession_logs';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
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
     * Defines user relationship from model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->belongsTo('NUSWhispers\Models\User', 'changed_by_user');
    }

    /**
     * Automatically mutate the date fields.
     *
     * @return array
     */
    public function getDates()
    {
        return ['created_on'];
    }
}
