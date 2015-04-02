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
        $facebookRequest = sprintf('/%s?oauth_token=%s&fields=comments.summary(true),likes.summary(true)', '1448006645491039_1453009484990755', \Config::get('laravel-facebook-sdk.facebook_config.app_access_token'));
        $facebookResponse = \Facebook::get($facebookRequest)->getDecodedBody();
        $this->facebook_information = $facebookResponse;
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
        return $query->whereStatus('Approved');
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

}
