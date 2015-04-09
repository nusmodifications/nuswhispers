<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Confession extends Model {

    /**
     * The database table used by the model.
     * @var string
     */
	protected $table = 'confessions';

    /**
     * Primary key of the model.
     * @var string
     */
    protected $primaryKey = 'confession_id';

    /**
     * Attributes should be mass-assignable.
     */
    protected $fillable = ['content', 'images', 'status'];

    public function getFacebookInformation()
    {
        if ($this->fb_post_id) {
            $facebookRequest = sprintf('/%s?oauth_token=%s&fields=comments.summary(true),likes.summary(true)', $this->fb_post_id, \Config::get('laravel-facebook-sdk.facebook_config.page_access_token'));
            $facebookResponse = \Facebook::get($facebookRequest)->getDecodedBody();
            $this->facebook_information = $facebookResponse;
        }
    }

    /**
     * Defines confession categories relationship to model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
	public function categories()
	{
		return $this->belongsToMany('App\Models\Category', 'confession_categories', 'confession_id', 'confession_category_id');
	}

    /**
     * Defines confession tags relationship to model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
	public function tags()
	{
		return $this->belongsToMany('App\Models\Tag', 'confession_tags', 'confession_id', 'confession_tag_id');
	}

    /**
     * Defines confession favourites relationship to model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favourites()
    {
        return $this->belongsToMany('App\Models\FbUser', 'favourites', 'confession_id', 'fb_user_id');
    }

    /**
     * Defines confession logs relationship to model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function logs()
    {
        return $this->hasMany('App\Models\ConfessionLog');
    }

    public function isApproved()
    {
        return $this->status === 'Featured' || $this->status === 'Approved';
    }

    /**
     * Query scope for pending confessions
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->whereStatus('Pending');
    }

    /**
     * Query scope for featured confessions
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->whereStatus('Featured');
    }

    /**
     * Query scope for approved confessions
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where(function ($query)
            {
                $query->where('status', '=', 'Approved')
                    ->orWhere('status', '=', 'Featured');
            });
    }

    /**
     * Automatically mutate the date fields
     * @return array
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'status_updated_at'];
    }

    /**
     * Query scope for rejected confessions
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->whereStatus('Rejected');
    }

    public function statuses()
    {
        return ['Featured', 'Pending', 'Approved', 'Rejected'];
    }

}
