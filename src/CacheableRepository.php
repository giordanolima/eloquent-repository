<?php

namespace GiordanoLima\EloquentRepository;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        });
    }

    protected function all($columns = ['*'])
    {
        if ($this->skipCache) {
            return parent::all($columns);
        }
        parent::select($columns);
        return $this->defaulReturn(function() {
            return parent::all();
        });
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
        })->pluck($column, $getKey);
    }

    protected function pluck($column, $key = null)
    {
        if ($this->skipCache) {
            return parent::pluck($column, $key);
        }

        $columns = [$column];
        if ($key) {
            $columns[] = $key;
        }
        $this->model = $this->model->select($columns);

        $getKey = str_contains($key, '.') ? collect(explode('.', $key))->last() : $key;

        return $this->defaulReturn(function() {
            return parent::get();
        })->pluck($column, $getKey);
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
        });
    }

    protected function findOrNew($id)
    {
        if ($this->skipCache) {
            return parent::findOrNew($id);
        }
        return $this->defaulReturn(function() use ($id) {
            return parent::findOrNew($id);
        });
    }

    protected function findOrFail($id)
    {
        if ($this->skipCache) {
            return parent::findOrFail($id);
        }
        return $this->defaulReturn(function() use ($id) {
            return parent::findOrFail($id);
        });
    }

    protected function first()
    {
        if ($this->skipCache) {
            return parent::first();
        }
        return $this->defaulReturn(function() {
            return parent::first();
        });
    }

    public function value($column)
    {
        if ($this->skipCache) {
            return parent::value($column);
        }

        $this->model = $this->model->select($column.' as returnfield')->take(1);
        $r = $this->defaulReturn('get')->first();

        return $r ? $r->returnfield : null;
    }

    public function create(array $attributes = [])
    {
        $r = parent::create($attributes);
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function update(array $attributes = [])
    {
        $r = parent::update($attributes);
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function save(array $options = [])
    {
        $r = parent::save($options);
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function delete()
    {
        $r = parent::delete();
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function destroy($ids)
    {
        $r = parent::destroy($ids);
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function restore($id)
    {
        $r = parent::restore($id);
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function forceDelete($id)
    {
        $r = parent::forceDelete($id);
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $r = parent::updateOrCreate($attributes, $values);
        if (!$this->skipCache) {
            $this->clearCache();
        }

        return $r;
    }

    public function attach($id, $relation, $values, array $attributes = [])
    {
        $r = $this->find($id)->{$relation}()->attach($values, $attributes);
        if (!$this->skipCache) {
            $this->clearCache();
        }
        $this->resetQuery();

        return $r;
    }

    public function detach($id, $relation, $values, array $attributes = [])
    {
        $r = $this->find($id)->{$relation}()->detach($values, $attributes);
        if (!$this->skipCache) {
            $this->clearCache();
        }
        $this->resetQuery();

        return $r;
    }

    public function updateExistingPivot($id, $relation, $related, array $attributes)
    {
        $r = $this->find($id)->{$relation}()->updateExistingPivot($related, $attributes);
        if (!$this->skipCache) {
            $this->clearCache();
        }
        $this->resetQuery();

        return $r;
    }

    public function sync($id, $relation, $values)
    {
        $r = $this->find($id)->{$relation}()->sync($values);
        if (!$this->skipCache) {
            $this->clearCache();
        }
        $this->resetQuery();

        return $r;
    }

    private function defaulReturn($closure)
    {
        if (in_array('getEagerLoads', get_class_methods($this->model()))) {
            $this->eagerLoads = $this->model->getEagerLoads();
        }

        $key = $this->getCacheKey();
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
