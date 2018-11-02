<?php

namespace NUSWhispers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use SammyK\LaravelFacebookSdk\FacebookFacade as Facebook;

class Confession extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'confession_id' => 'string',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'confessions';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'confession_id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'status_updated_at',
    ];

    /**
     * Attributes should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
        'fingerprint',
        'images',
        'fb_post_id',
        'status',
        'status_updated_at',
    ];

    /**
     * List of available statuses.
     *
     * @return array
     */
    public static function statuses(): array
    {
        return ['Featured', 'Scheduled', 'Pending', 'Approved', 'Rejected'];
    }

    /**
     * Defines confession categories relationship to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'confession_categories', 'confession_id', 'confession_category_id');
    }

    /**
     * Defines confession tags relationship to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'confession_tags', 'confession_id', 'confession_tag_id');
    }

    /**
     * Defines confession favourites relationship to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favourites(): Relations\BelongsToMany
    {
        return $this->belongsToMany(FbUser::class, 'favourites', 'confession_id', 'fb_user_id');
    }

    /**
     * Defines confession logs relationship to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs(): Relations\HasMany
    {
        return $this->hasMany(ConfessionLog::class, 'confession_id', 'confession_id')
            ->orderByDesc('created_on');
    }

    /**
     * Defines moderator comments relationship to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function moderatorComments(): Relations\HasMany
    {
        return $this->hasMany(ModeratorComment::class, 'confession_id', 'confession_id')
            ->orderByDesc('created_at');
    }

    /**
     * Defines queue relationship to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function queue(): Relations\HasOne
    {
        return $this->hasOne(ConfessionQueue::class, 'confession_id', 'confession_id');
    }

    /**
     * Retrieve Facebook information.
     *
     * @return void
     */
    public function getFacebookInformation(): void
    {
        if ($fbPostId = $this->getAttribute('fb_post_id')) {
            $accessToken = config('laravel-facebook-sdk.facebook_config.page_access_token');
            $pageId = config('services.facebook.page_id');

            $fbRequest = sprintf(
                '/%s?oauth_token=%s&fields=comments.summary(true).filter(toplevel).fields(%s),likes.summary(true)',
                $pageId . '_' . $fbPostId,
                $accessToken,
                'parent.fields(id),comments.summary(true),message,from,created_time,is_hidden'
            );

            $this->setAttribute('facebook_information', Facebook::get($fbRequest, $accessToken)->getDecodedBody());
        }
    }

    /**
     * Returns formatted content.
     *
     * @return string
     */
    public function getFormattedContent(): string
    {
        // Encode HTML entities
        $content = htmlentities($this->getAttribute('content'));

        // Wrap URLs with <a>
        $content = preg_replace('/(\b(https?|ftp):\/\/[A-Z0-9+&@#\/%?=~_|!:,.;-]*[-A-Z0-9+&@#\/%=~_|])/im', '<a href="$1" target="_blank">$1</a>', $content);

        // Wrap tags with <a>
        $matches = [];
        preg_match_all('/(#\w+)/', $content, $matches);
        foreach ($matches[0] as $tag) {
            $content = str_replace($tag, '<a target="_blank" href="' . url('/#!tag/' . substr($tag, 1)) . '">' . $tag . '</a>', $content);
        }

        return $content;
    }

    /**
     * Checks whether the confession is approved or featured.
     *
     * @return bool
     */
    public function approved(): bool
    {
        return \in_array($this->getAttribute('status'), ['Featured', 'Approved'], true);
    }

    /**
     * Query scope for popular confessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePopular($query): Builder
    {
        return $query->orderByRaw('
            LOG10( views + fb_like_count + ( fb_comment_count * 2 ) ) * 
            SIGN( views + fb_like_count + ( fb_comment_count * 2 ) ) + 
            ( UNIX_TIMESTAMP( status_updated_at ) / 300000 ) DESC
        ');
    }

    /**
     * Query scope for pending confessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query): Builder
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Query scope for featured confessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query): Builder
    {
        return $query->where('status', 'Featured');
    }

    /**
     * Query scope for scheduled confessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query): Builder
    {
        return $query->where('status', 'Scheduled');
    }

    /**
     * Query scope for approved confessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query): Builder
    {
        return $query->where(function (Builder $query) {
            return $query
                ->where('status', 'Approved')
                ->orWhere('status', 'Featured');
        });
    }

    /**
     * Query scope for rejected confessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query): Builder
    {
        return $query->where('status', 'Rejected');
    }
}
