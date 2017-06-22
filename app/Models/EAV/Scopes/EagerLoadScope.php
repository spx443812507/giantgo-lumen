<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午8:56
 */

namespace App\Models\EAV\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Entity;

class EagerLoadScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $entity
     *
     * @return void
     */
    public function apply(Builder $builder, Entity $entity)
    {
        $eagerLoads = $builder->getEagerLoads();
        // If there is any eagerload matching the eav key, we will replace it with
        // all the registered properties for the entity. We'll simulate as if the
        // user has manually added all of these withs in purpose when querying.
        $eagerLoads = array_merge($eagerLoads, $entity->getEntityAttributeRelations());
        $builder->setEagerLoads(array_except($eagerLoads, 'eav'));
    }
}