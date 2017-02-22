<?php

namespace GiordanoLima\EloquentRepository;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository {

    /**
     *
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;
    
    protected $perPage;
    protected $orderBy = null;
    protected $orderByDirection = "ASC";

    public $debug = false;
    private $skipGlobalScope = false;
    private $skipOrderBy = false;

    private $model_get_methods = [
        "get", 
        "all", 
        "lists", 
        "find", 
        "findOrNew", 
        "findOrFail", 
        "first", 
        "firstOrNew", 
        "firstOrCreate", 
        "firstOrFail", 
        "updateOrCreate", 
        "create", 
        "update", 
        "save", 
        "delete",
        "destroy",
        "count",
        "value",
    ];
    
    private $model_dinamic_methods = [
        "whereNotNull"
    ];

    /**
     * @param Application $app
     */
    public function __construct(Application $app) {
        $this->app = $app;
        $this->perPage = config("repository.per_page", 15);
        $this->resetQuery();
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract protected function model();
    
    /**
     * This method has been overridden to manage the default $perPage by repository property
     */    
    public function paginate($perPage = null, $columns = ['*']) {
        if(is_null($perPage))
            $perPage = $this->perPage;
        
        $r = $this->model->paginate($perPage, $columns);
        $this->resetQuery();
        return $r;
    }

    /**
     * This method must be overridden because sometimes the repository adds twice a order column
     */
    public function orderBy($column, $direction = 'asc') {
        $order = compact('column', 'direction');
        
        if($this->model instanceof Model || in_array("getQuery", get_class_methods($this->model))){
            $orders = (Array)$this->model->getQuery()->orders;
            $property = $this->model->getQuery()->unions ? 'unionOrders' : 'orders';
        } else {
            $orders = (Array)$this->model->orders;
            $property = $this->model->unions ? 'unionOrders' : 'orders';
        }
        
        if( !in_array($order, $orders) ){
            $direction = strtolower($direction) == 'asc' ? 'asc' : 'desc';
            if($this->model instanceof Model || in_array("getQuery", get_class_methods($this->model))){
                $this->model->getQuery()->{$property}[] = $order;
            } else {
                $this->model->{$property}[] = $order;
            }
        }
        
        $this->skipOrderBy();
        return $this;
    }

    public function destroy($id) {
        $ids = (Array)$id;
        $this->model->whereIn(app()->make($this->model())->getKeyName(), $ids)->delete();
    }
    
    public function __call($method, $parameters) {
        if (
                in_array($method, get_class_methods($this->model())) 
             || in_array($method, $this->model_dinamic_methods)
             || in_array($method, get_class_methods($this->model->newQuery()))
             || in_array($method, get_class_methods($this->model->newQuery()->getQuery()))) {

            if (in_array($method, $this->model_get_methods)) {
                
                if(!$this->skipOrderBy && !is_null($this->orderBy)){
                    $this->model = $this->model->orderBy($this->orderBy, $this->orderByDirection);      
                }
                if(!$this->skipGlobalScope)
                    $this->globalScope();
                $r = call_user_func_array([$this->model, $method], $parameters);
                $this->resetQuery();
                $this->skipGlobalScope = false;
                $this->skipOrderBy = false;
                return $r;
            }

            $this->model = call_user_func_array([$this->model, $method], $parameters);
            return $this;
        }

        $className = get_class($this);
        throw new \BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }

    /**
     * Reset model query
     * @return \Ensino\Repositories\Base\BaseRepository
     */
    public function newQuery() {
         $this->resetQuery();
        return $this;
    }
    
    protected function resetQuery() {
        $this->model = $this->app->make($this->model());
    }

    protected function globalScope() {
        return $this;
    }
    
    protected function skipGlobalScope() {
        $this->skipGlobalScope = true;
        return $this;
    }
    
    protected function skipOrderBy() {
        $this->skipOrderBy = true;
        return $this;
    }
}
