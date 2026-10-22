<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModeratorComment extends Model
{
    /** @use HasFactory<\Database\Factories\ModeratorCommentFactory> */
    use HasFactory;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'comment_id' => 'string',
        'created_at' => 'datetime',
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
     * Disable timestamps functionality.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Defines confession relationship from model.
     *
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
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
