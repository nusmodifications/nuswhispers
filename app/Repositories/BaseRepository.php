<?php namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository {

    /**
     * @var \App
     */
    private $_app;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Constructor
     * @param \App $app
     */
    public function __construct(\App $app)
    {
        $this->_app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name
     * @return mixed
     */
    public abstract function model();

    public function makeModel()
    {
        $model = $this->_app->getFacadeApplication()->make($this->model());

        if (!$model instanceof Model)
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

}
