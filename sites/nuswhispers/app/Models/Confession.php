<?php

class Confession extends Eloquent {
	protected $table = 'confessions';
	public $timestamps = false;

	public function categories()
	{
		return $this->belongsToMany('Category', 'confession_categories', 'confession_id', 'confession_category_id');
	}

	public function tags()
	{
		return $this->belongsToMany('Tag', 'confession_tags', 'confession_id', 'confession_tag_id');
	}
}
