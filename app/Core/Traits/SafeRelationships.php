<?php

namespace App\Core\Traits;

use App\Core\Models\EmptyRelation as ModelsEmptyRelation;
use EmptyRelation;
use Illuminate\Database\Eloquent\Collection;

trait SafeRelationships
{
    public function safeRelation($relationName)
    {
        try {
            return $this->$relationName();
        } catch (\Exception $e) {
            return new Collection();
        }
    }
    
    public function safeHasMany($related, $foreignKey = null, $localKey = null)
    {
        if (!class_exists($related)) {
            return new ModelsEmptyRelation($this);
        }
        
        return $this->hasMany($related, $foreignKey, $localKey);
    }
    
    public function safeBelongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        if (!class_exists($related)) {
            return null;
        }
        
        return $this->belongsTo($related, $foreignKey, $ownerKey);
    }
}