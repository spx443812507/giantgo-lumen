<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午4:27
 */

namespace App\Models\EAV\Supports;

use Closure;
use App\Models\EAV\Attribute;
use Illuminate\Database\Eloquent\Model as Entity;

class RelationBuilder
{
    /**
     * Build the relations for the entity attributes.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     *
     * @return void
     */
    public function build(Entity $entity)
    {
        $attributes = $entity->getEntityAttributes();
        // We will manually add a relationship for every attribute registered
        // of this entity. Once we know the relation method we have to use,
        // we will just add it to the entityAttributeRelations property.
        foreach ($attributes as $attribute) {
            $relation = $this->getRelationClosure($entity, $attribute);
            $entity->setEntityAttributeRelation($attribute->getAttribute('attribute_code'), $relation);
        }
    }

    /**
     * Generate the entity attribute relation closure.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     *
     * @param \App\Models\EAV\Attribute $attribute
     * @return \Closure
     */
    protected function getRelationClosure(Entity $entity, Attribute $attribute)
    {
        $method = $attribute->isCollection() ? 'hasMany' : 'hasOne';
        // This will return a closure fully binded to the current entity instance,
        // which will help us to simulate any relation as if it was made in the
        // original entity class definition using a function statement.
        return Closure::bind(function () use ($entity, $attribute, $method) {
            $relation = $entity->$method($attribute->getAttribute('backend_type'), 'entity_id', 'id');
            // Since an attribute could be attached to multiple entities, then values could have
            // same entity ID, but for different entity types, so we need to add type where
            // clause to fetch only values related to the given entity ID + entity type.
            $relation->where('backend_model', get_class($entity));
            // We add a where clause in order to fetch only the elements that are
            // related to the given attribute. If no condition is set, it will
            // fetch all the value rows related to the current entity.
            return $relation->where($attribute->getForeignKey(), $attribute->getKey());
        }, $entity, get_class($entity));
    }
}