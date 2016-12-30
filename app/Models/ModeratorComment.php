<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Model;

class ModeratorComment extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'comment_id' => 'string',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'moderator_comments';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'comment_id';

    /**
     * Attributes should be mass-assignable.
     */
    protected $fillable = ['user_id', 'content', 'created_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Defines confession relationship from model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function confession()
    {
        return $this->belongsTo('NUSWhispers\Models\Confession');
    }

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
