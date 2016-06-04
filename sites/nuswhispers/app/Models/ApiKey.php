<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model {
    
    /**
     * The attributes that should be casted to native types.
     * @var array
     */
    protected $casts = [
        'api_key_id' => 'string'
    ];
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'api_keys';
    
    /**
     * Primary key of the model.
     * @var string
     */
    protected $primaryKey = 'api_key_id';
    
    /**
     * Disable timestamps functionality.
     * @var boolean
     */
    public $timestamps = false;
    
    
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'users', 'user_id', 'user_id');
    }
    
    public function generateKey()
    {
        return hash_hmac('sha256', Str::random(128), $this->getKey());
    }
}