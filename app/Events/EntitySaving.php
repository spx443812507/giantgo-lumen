<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/22
 * Time: 下午11:15
 */

namespace App\Events;

use App\Models\EAV\Entity;
use Exception;

class EntitySaving
{
    public function handle(Entity $entity)
    {
        if (empty($entity->entity_type_id)) {
            $entity->entity_type_id = $entity->getEntityTypeId();
        }
    }
}