<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/22
 * Time: 下午4:42
 */

namespace App\Http\Middleware;

use App\Models\EAV\Entity;
use Closure;

class EntityAttributeValue
{
    public function handle($request, Closure $next)
    {
        $entityTypeId = $request->input('entity_type_id');

        if (!empty($entityTypeId)) {
            $entity = Entity::find($entityTypeId);

            if (!empty($entity)) {
                $entityClass = $entity->entity_model;

                $entityClass::$entityTypeId = $entityTypeId;
            }
        }

        $response = $next($request);

        return $response;
    }
}