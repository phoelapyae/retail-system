<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Collection;

class EmptyRelation
{
    private $parent;
    
    public function __construct($parent)
    {
        $this->parent = $parent;
    }
    
    public function get() { return new Collection(); }
    public function count() { return 0; }
    public function exists() { return false; }
    public function first() { return null; }
    public function find($id) { return null; }
    public function where(...$args) { return $this; }
    public function orderBy(...$args) { return $this; }
    public function latest(...$args) { return $this; }
    public function limit($limit) { return $this; }
    public function take($limit) { return $this; }
    public function paginate($perPage = 15) { 
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
    }
}