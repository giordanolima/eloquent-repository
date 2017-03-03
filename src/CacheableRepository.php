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

    public function get()
    {
        return $this->defaulReturn('get');
    }

    public function all()
    {
        return $this->defaulReturn('get');
    }

    public function paginate($perPage = null, $columns = ['*'])
    {
        if (is_null($perPage)) {
            $perPage = $this->perPage;
        }

        $this->eagerLoads = $this->model->getEagerLoads();
        $key = $this->getCacheKey('class', $this->getSql().'@perPage='.$perPage.'page='.request('page'));
        $value = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () use ($perPage, $columns) {
            return parent::paginate($perPage, $columns);
        });
        $this->loadRelationships($value);
        $this->resetQuery();

        return $value;
    }

    public function lists($column, $key = null)
    {
        $columns = [$column];
        if ($key) {
            $columns[] = $key;
        }
        $this->model = $this->model->select($columns);

        $getKey = str_contains($key, ".") ? collect(explode(".", $key))->last() : $key;
        return $this->defaulReturn("get")->pluck($column, $getKey);
    }

    public function count()
    {
        if (in_array('getQuery', get_class_methods($this->model))) {
            $this->model = $this->model->getQuery();
        }
        $this->model = $this->model->setBindings([], 'select');
        $this->model->aggregate = [
            'function' => 'count',
            'columns'  => ['*'],
        ];
        $key = $this->getCacheKey();
        $value = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () {
            return parent::count();
        });
        $this->resetQuery();

        return $value;
    }

    public function find($id)
    {
        if (is_array($id)) {
            $this->model = $this->model->whereIn(app()->make($this->model())->getKeyName(), $id);

            return $this->defaulReturn('get');
        }

        $this->model = $this->model->where(app()->make($this->model())->getKeyName(), $id)->take(1);

        return $this->defaulReturn('get')->first();
    }

    public function findOrNew($id)
    {
        if (is_array($id)) {
            $this->model = $this->model->whereIn(app()->make($this->model())->getKeyName(), $id);
            $r = $this->defaulReturn('get');
        } else {
            $this->model = $this->model->where(app()->make($this->model())->getKeyName(), $id)->take(1);
            $r = $this->defaulReturn('get')->first();
        }

        if (is_null($r)) {
            return app()->make($this->model());
        }

        return $r;
    }

    public function findOrFail($id)
    {
        if (is_array($id)) {
            $this->model = $this->model->whereIn(app()->make($this->model())->getKeyName(), $id);
            $r = $this->defaulReturn('get');
        } else {
            $this->model = $this->model->where(app()->make($this->model())->getKeyName(), $id)->take(1);
            $r = $this->defaulReturn('get')->first();
        }

        if (is_null($r)) {
            throw (new ModelNotFoundException())->setModel($this->model);
        }

        return $r;
    }

    public function first()
    {
        $this->model = $this->model->take(1);

        return $this->defaulReturn('get')->first();
    }

    public function firstOrNew()
    {
        $this->model = $this->model->take(1);
        $r = $this->defaulReturn('get')->first();
        if (is_null($r)) {
            return app()->make($this->model());
        }

        return $r;
    }

    public function firstOrFail()
    {
        $this->model = $this->model->take(1);
        $r = $this->defaulReturn('get')->first();
        if (is_null($r)) {
            throw (new ModelNotFoundException())->setModel($this->model);
        }

        return $r;
    }

    public function value($column)
    {
        $this->model = $this->model->select($column.' as returnfield')->take(1);
        $r = $this->defaulReturn('get')->first();

        return $r ? $r->returnfield : null;
    }

    public function create(array $attributes = [])
    {
        $r = parent::create($attributes);
        $this->clearCache();

        return $r;
    }

    public function update(array $attributes = [])
    {
        $r = parent::update($attributes);
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

    private function defaulReturn($call)
    {
        if (in_array('getEagerLoads', get_class_methods($this->model()))) {
            $this->eagerLoads = $this->model->getEagerLoads();
        }

        $key = $this->getCacheKey();
        $value = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () use ($call) {
            return parent::$call();
        });
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
                    $key = $this->getCacheKey($name, serialize($model));
                    $results = $this->getCacheRepository()->remember($key, config('repository.cache_time', 360), function () use ($model, $name, $constraints) {
                        return $this->loadRelationship([$model], $name, $constraints);
                    });
                    $relation->match([$model], $results, $name);
                }
            } else {
                foreach ($value as $model) {
                    $key = $this->getCacheKey($name, serialize($model));
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
    private function getCacheKey($what = 'class', $key = null)
    {
        if (is_null($key)) {
            $key = $this->getSql();
        }

        $key = sprintf('%s--%s', get_called_class().'@'.$what, md5($key));
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
