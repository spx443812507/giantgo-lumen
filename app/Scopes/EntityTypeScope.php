<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/22
 * Time: 下午4:08
 */

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Entity;

class EntityTypeScope implements Scope
{
    public function apply(Builder $builder, Entity $entity)
    {
        $builder->where('entity_type_id', $entity->getEntityTypeId());
    }
}