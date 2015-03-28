<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    /**
     * The database table used by the model.
     * @var string
     */
	protected $table = 'categories';

    /**
     * Primary key of the model.
     * @var string
     */
    protected $primaryKey = 'confession_category_id';

    /**
     * Disable timestamps functionality.
     * @var boolean
     */
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
