<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confession_tag_id' => 'string',
    ];

    protected $table = 'tags';
    protected $fillable = ['confession_tag'];
    protected $primaryKey = 'confession_tag_id';
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Confession, $this>
     */
    public function confessions()
    {
        return $this->belongsToMany(Confession::class, 'confession_tags', 'confession_tag_id', 'confession_id');
    }
}
