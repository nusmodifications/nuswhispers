<?php

class Tag extends Model {
	protected $table = 'tags';
	protected $fillable = array('confession_tag');
	public $timestamps = false;

	public function confessions()
	{
		return $this->belongsToMany('Confession', 'confession_tags', 'confession_tag_id', 'confession_id');
	}

}
