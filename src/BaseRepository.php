<?php

namespace GiordanoLima\EloquentRepository;

use Closure;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /**
     * @var Application
     */
    protected $app;

    protected $perPage;
    protected $orderBy = null;
    protected $orderByDirection = 'ASC';

    public $debug = false;
    private $skipGlobalScope = false;
    private $skipOrderBy = false;
    private $model = false;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        if (!$this->perPage) {
            $this->perPage = config('repository.per_page', 15);
        }
        $this->resetQuery();
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    abstract protected function model();

    // -------------------- //
    // Repo manager methods //
    // -------------------- //

    /**
     * Reset model query.
     *
     * @return \Ensino\Repositories\Base\BaseRepository
     */
    protected function newQuery()
    {
        $this->resetQuery();

        return $this;
    }

    protected function resetQuery($model = null)
    {
        if(!$model)
            $model = $this->model();
            
        $this->model = $this->app->make($model);

        return $this;
    }

    protected function getQuery()
    {
        return $this->model;
    }

    protected function globalScope()
    {
        return $this;
    }

    protected function skipGlobalScope()
    {
        $this->skipGlobalScope = true;

        return $this;
    }

    protected function skipOrderBy()
    {
        $this->skipOrderBy = true;

        return $this;
    }

    // ------------- //
    // Model methods //
    // ------------- //

    protected function all($columns = ['*'])
    {
        $this->prepareQuery();
        $r = $this->model->all($columns);
        $this->finishQuery();

        return $r;
    }

    protected function with($relations)
    {
        $this->model = $this->model->with(is_string($relations) ? func_get_args() : $relations);

        return $this;
    }

    protected function has($relations, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        $this->model = $this->model->has($relations, $operator, $count, $boolean, $callback);

        return $this;
    }

    protected function hasNested($relations, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        $this->model = $this->model->hasNested($relations, $operator, $count, $boolean, $callback);

        return $this;
    }

    protected function orHas($relations, $operator = '>=', $count = 1)
    {
        $this->model = $this->model->orHas($relations, $operator, $count);

        return $this;
    }

    protected function doesntHave($relations, $boolean = 'and', Closure $callback = null)
    {
        $this->model = $this->model->doesntHave($relations, $boolean, $callback);

        return $this;
    }

    protected function orDoesntHave($relation)
    {
        $this->model = $this->model->orDoesntHave($relation);

        return $this;
    }

    protected function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        $this->model = $this->model->whereHas($relation, $callback, $operator, $count);

        return $this;
    }

    protected function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        $this->model = $this->model->orWhereHas($relation, $callback, $operator, $count);

        return $this;
    }

    protected function whereDoesntHave($relation, Closure $callback = null)
    {
        $this->model = $this->model->whereDoesntHave($relation, $callback);

        return $this;
    }

    protected function orWhereDoesntHave($relation, Closure $callback = null)
    {
        $this->model = $this->model->orWhereDoesntHave($relation, $callback);

        return $this;
    }

    protected function withCount($relations)
    {
        $this->model = $this->model->withCount($relations);

        return $this;
    }

    protected function without($relations)
    {
        $this->model = $this->model->without($relations);

        return $this;
    }

    protected function destroy($ids)
    {
        $this->resetQuery();
        $r = $this->model->destroy($ids);
        $this->finishQuery();

        return $r;
    }

    // ----------------- //
    // Query get methods //
    // ----------------- //

    protected function select($columns = ['*'])
    {
        $this->model = $this->model->select($columns);

        return $this;
    }

    protected function addSelect($column)
    {
        $this->model = $this->model->addSelect($column);

        return $this;
    }

    protected function distinct()
    {
        $this->model = $this->model->distinct();

        return $this;
    }

    protected function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $this->model = $this->model->join($table, $first, $operator, $second, $type, $where);

        return $this;
    }

    protected function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $this->model = $this->model->joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false);

        return $this;
    }

    protected function selectRaw($expression, array $bindings = [])
    {
        $this->model = $this->model->selectRaw($expression, $bindings);

        return $this;
    }

    protected function whereKey($id)
    {
        $this->model = $this->model->whereKey($id);

        return $this;
    }

    protected function whereKeyNot($id)
    {
        $this->model = $this->model->whereKeyNot($id);

        return $this;
    }

    protected function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->model = $this->model->where($column, $operator, $value, $boolean);

        return $this;
    }

    protected function orWhere($column, $operator = null, $value = null)
    {
        $this->model = $this->model->orWhere($column, $operator, $value);

        return $this;
    }

    protected function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $this->model = $this->model->whereRaw($sql, $bindings, $boolean);

        return $this;
    }

    protected function orWhereRaw($sql, $bindings = [])
    {
        $this->model = $this->model->orWhereRaw($sql, $bindings);

        return $this;
    }

    protected function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->model = $this->model->whereIn($column, $values, $boolean, $not);

        return $this;
    }

    protected function orWhereIn($column, $values)
    {
        $this->model = $this->model->orWhereIn($column, $values);

        return $this;
    }

    protected function whereNotIn($column, $values, $boolean = 'and')
    {
        $this->model = $this->model->whereNotIn($column, $values, $boolean);

        return $this;
    }

    protected function orWhereNotIn($column, $values)
    {
        $this->model = $this->model->orWhereNotIn($column, $values);

        return $this;
    }

    protected function whereNull($column, $boolean = 'and', $not = false)
    {
        $this->model = $this->model->whereNull($column, $boolean, $not);

        return $this;
    }

    protected function orWhereNull($column)
    {
        $this->model = $this->model->orWhereNull($column);

        return $this;
    }

    protected function whereNotNull($column, $boolean = 'and')
    {
        $this->model = $this->model->whereNotNull($column, $boolean);

        return $this;
    }

    protected function onlyTrashed()
    {
        $this->model = $this->model->onlyTrashed();

        return $this;
    }

    protected function withTrashed()
    {
        $this->model = $this->model->withTrashed();

        return $this;
    }

    protected function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $this->model = $this->model->whereBetween($column, $values, $boolean, $not);

        return $this;
    }

    protected function orWhereBetween($column, array $values)
    {
        $this->model = $this->model->orWhereBetween($column, $values);

        return $this;
    }

    protected function whereNotBetween($column, array $values, $boolean = 'and')
    {
        $this->model = $this->model->whereNotBetween($column, $values, $boolean);

        return $this;
    }

    protected function orWhereNotBetween($column, array $values)
    {
        $this->model = $this->model->orWhereNotBetween($column, $values);

        return $this;
    }

    protected function orWhereNotNull($column)
    {
        $this->model = $this->model->orWhereNotNull($column);

        return $this;
    }

    protected function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        $this->model = $this->model->whereDate($column, $operator, $value, $boolean);

        return $this;
    }

    protected function orWhereDate($column, $operator, $value)
    {
        $this->model = $this->model->orWhereDate($column, $operator, $value);

        return $this;
    }

    protected function whereTime($column, $operator, $value, $boolean = 'and')
    {
        $this->model = $this->model->whereTime($column, $operator, $value, $boolean);

        return $this;
    }

    protected function orWhereTime($column, $operator, $value)
    {
        $this->model = $this->model->orWhereTime($column, $operator, $value);

        return $this;
    }

    protected function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        $this->model = $this->model->whereDay($column, $operator, $value, $boolean);

        return $this;
    }

    protected function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        $this->model = $this->model->whereMonth($column, $operator, $value, $boolean);

        return $this;
    }

    protected function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        $this->model = $this->model->whereYear($column, $operator, $value = null, $boolean);

        return $this;
    }

    protected function groupBy($group)
    {
        $this->model = $this->model->groupBy($group);

        return $this;
    }

    protected function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->model = $this->model->having($column, $operator, $value, $boolean);

        return $this;
    }

    protected function orHaving($column, $operator = null, $value = null)
    {
        $this->model = $this->model->orHaving($column, $operator, $value);

        return $this;
    }

    protected function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $this->model = $this->model->havingRaw($sql, $bindings, $boolean);

        return $this;
    }

    protected function orHavingRaw($sql, array $bindings = [])
    {
        $this->model = $this->model->havingRaw($sql, $bindings);

        return $this;
    }

    protected function latest($column = 'created_at')
    {
        $this->model = $this->model->latest($column);

        return $this;
    }

    protected function oldest($column = 'created_at')
    {
        $this->model = $this->model->oldest($column);

        return $this;
    }

    protected function orderByRaw($sql, $bindings = [])
    {
        $this->model = $this->model->orderByRaw($sql, $bindings);

        return $this;
    }

    protected function skip($value)
    {
        $this->model = $this->model->skip($value);

        return $this;
    }

    protected function offset($value)
    {
        $this->model = $this->model->offset($value);

        return $this;
    }

    protected function take($value)
    {
        $this->model = $this->model->take($value);

        return $this;
    }

    protected function limit($value)
    {
        $this->model = $this->model->limit($value);

        return $this;
    }

    protected function find($id)
    {
        $this->resetQuery();
        $r = $this->model->find($id);
        $this->finishQuery();

        return $r;
    }

    protected function findMany($ids, $columns = ['*'])
    {
        $this->resetQuery();
        $r = $this->model->findMany($ids, $columns);
        $this->finishQuery();

        return $r;
    }

    protected function findOrFail($id)
    {
        $this->resetQuery();
        $r = $this->model->findOrFail($id);
        $this->finishQuery();

        return $r;
    }

    protected function findOrNew($id)
    {
        $this->resetQuery();
        $r = $this->model->findOrNew($id);
        $this->finishQuery();

        return $r;
    }

    protected function updateOrCreate(array $attributes, array $values = [])
    {
        $this->resetQuery();
        $r = $this->model->updateOrCreate($attributes, $values);
        $this->finishQuery();

        return $r;
    }

    protected function first()
    {
        $this->prepareQuery();
        $r = $this->model->first();
        $this->finishQuery();

        return $r;
    }

    protected function firstOrCreate(array $attributes, array $values = [])
    {
        $this->resetQuery();
        $r = $this->model->firstOrCreate($attributes, $values);
        $this->finishQuery();

        return $r;
    }

    protected function firstOrFail($columns = ['*'])
    {
        $this->prepareQuery();
        $r = $this->model->firstOrFail($columns);
        $this->finishQuery();

        return $r;
    }

    protected function firstOr($columns = ['*'], Closure $callback = null)
    {
        $this->prepareQuery();
        $r = $this->model->firstOr($columns, $callback);
        $this->finishQuery();

        return $r;
    }

    protected function value($column)
    {
        $this->prepareQuery();
        $r = $this->model->value($column);
        $this->finishQuery();

        return $r;
    }

    protected function get($columns = ['*'])
    {
        $this->prepareQuery();
        $r = $this->model->get($columns);
        $this->finishQuery();

        return $r;
    }

    protected function lists($column, $key = null)
    {
        $this->prepareQuery();
        $r = $this->model->lists($column, $key);
        $this->finishQuery();

        return $r;
    }

    protected function pluck($column, $key = null)
    {
        $this->prepareQuery();
        $r = $this->model->pluck($column, $key);
        $this->finishQuery();

        return $r;
    }

    protected function count()
    {
        $this->prepareQuery();
        $r = $this->model->count();
        $this->finishQuery();

        return $r;
    }

    protected function paginate($perPage = null, $columns = ['*'])
    {
        if (is_null($perPage)) {
            $perPage = $this->perPage;
        }

        if (!$this->skipOrderBy && !is_null($this->orderBy)) {
            $this->model = $this->model->orderBy($this->orderBy, $this->orderByDirection);
        }
        if (!$this->skipGlobalScope) {
            $this->globalScope();
        }

        $r = $this->model->paginate($perPage, $columns);
        $this->resetQuery();
        $this->skipGlobalScope = false;
        $this->skipOrderBy = false;

        return $r;
    }

    protected function simplePaginate($perPage = null, $columns = ['*'])
    {
        if (is_null($perPage)) {
            $perPage = $this->perPage;
        }

        if (!$this->skipOrderBy && !is_null($this->orderBy)) {
            $this->model = $this->model->orderBy($this->orderBy, $this->orderByDirection);
        }
        if (!$this->skipGlobalScope) {
            $this->globalScope();
        }

        $r = $this->model->simplePaginate($perPage, $columns);
        $this->resetQuery();
        $this->skipGlobalScope = false;
        $this->skipOrderBy = false;

        return $r;
    }

    protected function create(array $attributes = [])
    {
        $r = $this->newQuery()->model->create($attributes);
        $this->skipGlobalScope = false;
        $this->skipOrderBy = false;
        $this->resetQuery();

        return $r;
    }

    protected function update(array $values)
    {
        $this->prepareQuery();
        $r = $this->model->update($values);
        $this->finishQuery();

        return $r;
    }

    protected function increment($column, $amount = 1, array $extra = [])
    {
        $this->prepareQuery();
        $r = $this->model->increment($column, $amount, $extra);
        $this->finishQuery();

        return $r;
    }

    protected function decrement($column, $amount = 1, array $extra = [])
    {
        $this->prepareQuery();
        $r = $this->model->decrement($column, $amount, $extra);
        $this->finishQuery();

        return $r;
    }

    protected function delete()
    {
        $this->prepareQuery();
        $r = $this->model->delete();
        $this->finishQuery();

        return $r;
    }

    protected function forceDelete()
    {
        $this->prepareQuery();
        $r = $this->model->forceDelete();
        $this->finishQuery();

        return $r;
    }

    protected function min($column)
    {
        $this->prepareQuery();
        $r = $this->model->min($column);
        $this->finishQuery();

        return $r;
    }

    protected function max($column)
    {
        $this->prepareQuery();
        $r = $this->model->max($column);
        $this->finishQuery();

        return $r;
    }

    protected function sum($column)
    {
        $this->prepareQuery();
        $r = $this->model->sum($column);
        $this->finishQuery();

        return $r;
    }

    protected function avg($column)
    {
        $this->prepareQuery();
        $r = $this->model->avg($column);
        $this->finishQuery();

        return $r;
    }

    protected function average($column)
    {
        $this->prepareQuery();
        $r = $this->model->average($column);
        $this->finishQuery();

        return $r;
    }

    protected function insert(array $values)
    {
        $this->resetQuery();
        $r = $this->model->insert($values);
        $this->finishQuery();

        return $r;
    }

    protected function insertGetId(array $values)
    {
        $this->resetQuery();
        $r = $this->model->insertGetId($values);
        $this->finishQuery();

        return $r;
    }

    protected function orderBy($column, $direction = 'asc')
    {
        $order = compact('column', 'direction');

        if ($this->model instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
            $orders = (array) $this->model->getQuery()->getQuery()->orders;
        } elseif ($this->model instanceof Model || in_array('getQuery', get_class_methods($this->model))) {
            $orders = (array) $this->model->getQuery()->orders;
        } else {
            $orders = (array) $this->model->orders;
        }

        if (!in_array($order, $orders)) {
            $this->model = $this->model->orderBy($column, $direction);
        }

        $this->skipOrderBy();

        return $this;
    }

    protected function orderByDesc($column)
    {
        $this->orderBy($column);

        return $this;
    }

    // --------------- //
    // private methods //
    // --------------- //

    private function prepareQuery()
    {
        if (!$this->skipOrderBy && !is_null($this->orderBy)) {
            $this->model = $this->model->orderBy($this->orderBy, $this->orderByDirection);
        }
        if (!$this->skipGlobalScope) {
            $this->globalScope();
        }
    }

    private function finishQuery()
    {
        $this->resetQuery();
        $this->skipGlobalScope = false;
        $this->skipOrderBy = false;
    }
}
