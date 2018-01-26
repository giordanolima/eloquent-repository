<?php

namespace GiordanoLima\EloquentRepository;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

trait CacheableRepository
{
    /**
     * @var CacheRepository
     */
    protected $cacheRepository = null;
    private $eagerLoads;

    protected function get($columns = ['*'])
    {
        if ($this->skipCache) {
            return parent::get($columns);
        }
        parent::select($columns);
        return $this->defaulReturn(function() {
            return parent::get();
        }, "get");
    }

    protected function all($columns = ['*'])
    {
        if ($this->skipCache) {
            return parent::all($columns);
        }
        parent::select($columns);
        return $this->defaulReturn(function() {
            return parent::all();
        }, "all");
    }

    protected function paginate($perPage = null, $columns = ['*'])
    {
        if ($this->skipCache) {
            return parent::paginate($perPage, $columns);
        }

        if (is_null($perPage)) {
            $perPage = $this->perPage;
        }

        $this->eagerLoads = $this->model->getEagerLoads();
        $key = $this->getCacheKey('paginate', $this->getSql().'@perPage='.$perPage.'page='.request('page'));
        $value = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () use ($perPage, $columns) {
            return parent::paginate($perPage, $columns);
        });
        $this->loadRelationships($value);
        $this->resetQuery();

        return $value;
    }

    protected function lists($column, $key = null)
    {
        if ($this->skipCache) {
            return parent::lists($column, $key);
        }

        $columns = [$column];
        if ($key) {
            $columns[] = $key;
        }
        $this->model = $this->model->select($columns);

        $getKey = str_contains($key, '.') ? collect(explode('.', $key))->last() : $key;

        return $this->defaulReturn(function() {
            return parent::get();
        }, "lists-" . $column."/".$key)->pluck($column, $getKey);
    }

    protected function pluck($column, $key = null)
    {
        return $this->lists($column, $key);
    }

    protected function count()
    {
        if ($this->skipCache) {
            return parent::count();
        }

        if (in_array('getQuery', get_class_methods($this->model))) {
            $this->model = $this->model->getQuery();
        }
        $this->model = $this->model->setBindings([], 'select');
        $this->model->aggregate = [
            'function' => 'count',
            'columns'  => ['*'],
        ];
        $key = $this->getCacheKey('count');
        $value = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () {
            return parent::count();
        });
        $this->resetQuery();

        return $value;
    }

    protected function find($id)
    {
        if ($this->skipCache) {
            return parent::find($id);
        }
        return $this->defaulReturn(function() use ($id) {
            return parent::find($id);
        }, "find-" . $id);
    }

    protected function findOrNew($id)
    {
        if ($this->skipCache) {
            return parent::findOrNew($id);
        }
        return $this->defaulReturn(function() use ($id) {
            return parent::findOrNew($id);
        }, "findOrNew-".$id);
    }

    protected function findOrFail($id)
    {
        if ($this->skipCache) {
            return parent::findOrFail($id);
        }
        return $this->defaulReturn(function() use ($id) {
            return parent::findOrFail($id);
        }, "findOrFail" . $id);
    }

    protected function first()
    {
        if ($this->skipCache) {
            return parent::first();
        }
        return $this->defaulReturn(function() {
            return parent::first();
        }, "first");
    }

    public function value($column)
    {
        if ($this->skipCache) {
            return parent::value($column);
        }

        return $this->defaulReturn(function() use ($column) {
            return parent::value($column);
        }, "value-".$column);
    }

    public function min($column)
    {
        if ($this->skipCache) {
            return parent::min($column);
        }

        return $this->defaulReturn(function() use ($column) {
            return parent::min($column);
        }, "min-".$column);
    }

    public function max($column)
    {
        if ($this->skipCache) {
            return parent::max($column);
        }

        return $this->defaulReturn(function() use ($column) {
            return parent::max($column);
        }, "max-".$column);
    }

    public function sum($column)
    {
        if ($this->skipCache) {
            return parent::sum($column);
        }

        return $this->defaulReturn(function() use ($column) {
            return parent::sum($column);
        }, "sum-".$column);
    }

    public function avg($column)
    {
        if ($this->skipCache) {
            return parent::avg($column);
        }

        return $this->defaulReturn(function() use ($column) {
            return parent::avg($column);
        }, "avg-".$column);
    }

    public function average($column)
    {
        if ($this->skipCache) {
            return parent::average($column);
        }

        return $this->defaulReturn(function() use ($column) {
            return parent::average($column);
        }, "average-".$column);
    }

    public function create(array $attributes = [])
    {
        $r = parent::create($attributes);
        $this->clearCache();
        return $r;
    }

    public function insert(array $values)
    {
        $r = parent::insert($values);
        $this->clearCache();
        return $r;
    }

    public function insertGetId(array $values)
    {
        $r = parent::insertGetId($values);
        $this->clearCache();
        return $r;
    }

    public function update(array $values)
    {
        $r = parent::update($values);
        $this->clearCache();
        return $r;
    }

    public function save(array $options = [])
    {
        $r = parent::save($options);
        $this->clearCache();

        return $r;
    }

    public function delete()
    {
        $r = parent::delete();
        $this->clearCache();

        return $r;
    }

    public function destroy($ids)
    {
        $r = parent::destroy($ids);
        $this->clearCache();

        return $r;
    }

    public function restore($id)
    {
        $r = parent::restore($id);
        $this->clearCache();

        return $r;
    }

    public function forceDelete($id)
    {
        $r = parent::forceDelete($id);
        $this->clearCache();

        return $r;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $r = parent::updateOrCreate($attributes, $values);
        $this->clearCache();

        return $r;
    }

    public function attach($id, $relation, $values, array $attributes = [])
    {
        $r = $this->find($id)->{$relation}()->attach($values, $attributes);
        $this->clearCache();
        $this->resetQuery();

        return $r;
    }

    public function detach($id, $relation, $values, array $attributes = [])
    {
        $r = $this->find($id)->{$relation}()->detach($values, $attributes);
        $this->clearCache();
        $this->resetQuery();

        return $r;
    }

    public function updateExistingPivot($id, $relation, $related, array $attributes)
    {
        $r = $this->find($id)->{$relation}()->updateExistingPivot($related, $attributes);
        $this->clearCache();
        $this->resetQuery();

        return $r;
    }

    public function sync($id, $relation, $values)
    {
        $r = $this->find($id)->{$relation}()->sync($values);
        $this->clearCache();
        $this->resetQuery();

        return $r;
    }

    private function defaulReturn($closure, $method)
    {
        if (in_array('getEagerLoads', get_class_methods($this->model()))) {
            $this->eagerLoads = $this->model->getEagerLoads();
        }

        $key = $this->getCacheKey($method);
        $value = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), $closure);
        $this->resetQuery();

        if (in_array('getEagerLoads', get_class_methods($this->model()))) {
            $this->loadRelationships($value);
        }

        return collect($value);
    }

    private function loadRelationships(&$value)
    {
        foreach ($this->eagerLoads as $name => $constraints) {
            if (strpos($name, '.') === false) {
                foreach ($value as $model) {
                    $relation = $model->$name();
                    $key = $this->getCacheKey('relation', serialize($model), $name);
                    $results = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () use ($model, $name, $constraints) {
                        return $this->loadRelationship([$model], $name, $constraints);
                    });
                    $relation->match([$model], $results, $name);
                }
            } else {
                foreach ($value as $model) {
                    $key = $this->getCacheKey('relation', serialize($model), $name);
                    $results = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () use ($model, $name) {
                        return $model->newCollection(collect($model->load($name)->getRelationValue($name))->toArray());
                    });
                    $relation->match([$model], $results, $name);
                }
            }
        }
    }

    private function loadRelationship($models, $name, $constraints)
    {
        $relation = app()->make($this->model())->{$name}();

        $relation->addEagerConstraints($models);
        call_user_func($constraints, $relation);
        $results = $relation->getEager();

        return $results;
    }

    public function clearCache()
    {
        $cacheKeys = collect(CacheKeys::loadKeys());
        $cacheKeys = $cacheKeys->flatten();

        foreach ($cacheKeys as $key) {
            $this->getCacheRepository()->forget($key);
        }
    }

    /**
     * Return a sanitized key for cache.
     *
     * @return string
     */
    private function getCacheKey($method = 'default', $key = null, $what = 'class')
    {
        if (is_null($key)) {
            $key = $this->getSql();
        }

        $key = sprintf('%s--%s', get_called_class().'@'.$what."#".$method, md5($key));
        CacheKeys::putKey(get_called_class(), $key);

        return $key;
    }

    /**
     * Return the current SQL in QueryBuilder of Model.
     *
     * @return string
     */
    private function getSql($builder = null)
    {
        if (is_null($builder)) {
            $builder = $this->model;
        }

        $replace = function ($sql, $bindings) {
            $needle = '?';
            foreach ($bindings as $replace) {
                $pos = strpos($sql, $needle);
                if ($pos !== false) {
                    $sql = substr_replace($sql, $replace, $pos, strlen($needle));
                }
            }

            return $sql;
        };
        $sql = $replace($builder->toSql(), $builder->getBindings());

        return $sql;
    }

    /**
     * Return instance of Cache Repository.
     *
     * @return CacheRepository
     */
    private function getCacheRepository()
    {
        if (is_null($this->cacheRepository)) {
            $this->cacheRepository = app('cache');
        }

        return $this->cacheRepository;
    }
}
