<?php

class Category extends Eloquent {
	protected $table = 'categories';
	public $timestamps = false;

	public function confessions()
	{
		return $this->belongsToMany('Confession', 'confession_categories', 'confession_category_id', 'confession_id');
	}

	public function scopeCategoryAsc($query)
	{
		return $query->orderBy('confession_category', 'ASC');
	}
}
