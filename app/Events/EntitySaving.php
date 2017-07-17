<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/22
 * Time: 下午11:15
 */

namespace App\Events;

use Illuminate\Database\Eloquent\Model as Entity;

class EntitySaving
{
    public function handle(Entity $entity)
    {
        if (empty($entity->entity_type_id) && !empty($entity->getEntityTypeId())) {
            $entity->entity_type_id = $entity->getEntityTypeId();
        }
    }
}