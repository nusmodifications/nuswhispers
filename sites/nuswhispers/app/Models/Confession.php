<?php

class Confession extends Model {

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

	public function categories()
	{
		return $this->belongsToMany('Category', 'confession_categories', 'confession_id', 'confession_category_id');
	}

	public function tags()
	{
		return $this->belongsToMany('Tag', 'confession_tags', 'confession_id', 'confession_tag_id');
	}

}
