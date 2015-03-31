<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
	protected $table = 'tags';
	protected $fillable = array('confession_tag');
    protected $primaryKey = 'confession_tag_id';
	public $timestamps = false;

	public function confessions()
	{
		return $this->belongsToMany('Confession', 'confession_tags', 'confession_tag_id', 'confession_id');
	}

}
