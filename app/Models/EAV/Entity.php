<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午4:53
 */

namespace App\Models\EAV;

use App\Models\EAV\Supports\RelationBuilder;
use App\Models\EAV\Supports\ValueCollection;
use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class Entity extends Model
{
    /**
     * The entity type.
     */
    public static $entityTypeId;
    /**
     * The entity attributes.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected static $entityAttributes;
    /**
     * The entity attribute value trash.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $trash;
    /**
     * The entity attribute relations.
     *
     * @var array
     */
    protected $attributeRelations = [];
    /**
     * Determine if the entity attribute relations have been booted.
     *
     * @var bool
     */
    protected $attributeRelationsBooted = false;


    public function getEntityTypeId()
    {
        return self::$entityTypeId;
    }

    public function setEntityTypeId($entityTypeId)
    {
        return self::$entityTypeId = $entityTypeId;
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (isset(static::$entityTypeId)) {
            $this->setEntityTypeId(static::$entityTypeId);

            $attributeIds = DB::table('entity_attribute')->where('entity_type_id', $this->getEntityTypeId())->get()->pluck('attribute_id');

            static::$entityAttributes = Attribute::whereIn('id', $attributeIds)->get()->keyBy('attribute_code');

            if (!$this->attributeRelationsBooted) {
                app(RelationBuilder::class)->build($this);
                $this->attributeRelationsBooted = true;
            }
        }
    }

    /**
     * Get the entity attributes.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getEntityAttributes()
    {
        return static::$entityAttributes;
    }

    /**
     * Get the attributes attached to this entity.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function attributes()
    {
        return $this->getEntityAttributes();
    }

    /**
     * {@inheritdoc}
     */
    protected function fillAttributes(array $attributes)
    {
        foreach (array_diff_key($attributes, array_flip($this->getFillable())) as $key => $value) {
            if ($this->isEntityAttribute($key)) {
                $this->setEntityAttribute($key, $value);
            }
        }
        if (count($this->getFillable()) > 0 && !static::$unguarded) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($key, $value)
    {
        return $this->isEntityAttribute($key) ? $this->setEntityAttribute($key, $value) : parent::setAttribute($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($key)
    {
        return $this->isEntityAttribute($key) ? $this->getEntityAttribute($key) : parent::getAttribute($key);
    }

    /**
     * Set the entity attribute relation.
     *
     * @param string $relation
     * @param mixed $value
     *
     * @return $this
     */
    public function setEntityAttributeRelation($relation, $value)
    {
        $this->attributeRelations[$relation] = $value;
        return $this;
    }

    /**
     * Check if the given key is an entity attribute relation.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isEntityAttributeRelation($key)
    {
        return isset($this->attributeRelations[$key]);
    }

    /**
     * Get the entity attribute value trash.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEntityAttributeValueTrash()
    {
        return $this->trash ?: $this->trash = collect([]);
    }

    /**
     * Get the entity attribute relations.
     *
     * @return array
     */
    public function getEntityAttributeRelations()
    {
        return $this->attributeRelations;
    }

    /**
     * Check if the given key is an entity attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function isEntityAttribute($key)
    {
        $key = $this->getEntityAttributeName($key);
        return $this->getEntityAttributes()->has($key);
    }

    /**
     * Get the entity attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getEntityAttribute($key)
    {
        if ($this->isRawEntityAttribute($key)) {
            return $this->getEntityAttributeRelation($key);
        }
        return $this->getEntityAttributeValue($key);
    }

    /**
     * Get the entity attribute value.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($key)
    {
        $value = $this->getEntityAttributeRelation($key);
        // In case we are accessing to a multivalued attribute, we will return
        // a collection with pairs of id and value content. Otherwise we'll
        // just return the single model value content as a plain result.
        if ($this->getEntityAttributes()->get($key)->isCollection()) {
            return $value->pluck('value');
        }
        return !is_null($value) ? $value->getAttribute('value') : null;
    }

    /**
     * Get the entity attribute relationship.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getEntityAttributeRelation($key)
    {
        $key = $this->getEntityAttributeName($key);
        if ($this->relationLoaded($key)) {
            return $this->getRelation($key);
        }
        return $this->getRelationValue($key);
    }

    /**
     * Set the entity attribute.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function setEntityAttribute($key, $value)
    {
        $current = $this->getEntityAttributeRelation($key);
        $attribute = $this->getEntityAttributes()->get($key);
        // $current will always contain a collection when an attribute is multivalued
        // as morphMany provides collections even if no values were matched, making
        // us assume at least an empty collection object will be always provided.
        if ($attribute->isCollection()) {
            if (is_null($current)) {
                $this->setRelation($key, $current = new ValueCollection());
            }
            $current->replace($value);
            return $this;
        }
        // If the attribute to set is a collection, it will be replaced by the
        // new value. If the value model does not exist, we will just create
        // and set a new value model, otherwise its value will get updated.
        if (is_null($current)) {
            return $this->setEntityAttributeValue($attribute, $value);
        }

        if ($value instanceof Value) {
            $value = $value->getAttribute('value');
        }

        $current->setAttribute('entity_type_id', $this->getEntityTypeId());

        return $current->setAttribute('value', $value);
    }

    /**
     * Set the entity attribute value.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function setEntityAttributeValue(Attribute $attribute, $value)
    {
        if (!is_null($value) && !$value instanceof Value) {
            $model = $attribute->getAttribute('backend_type');

            $instance = new $model();
            $instance->setAttribute('entity_id', $this->getKey());
            $instance->setAttribute('entity_type_id', $this->getEntityTypeId());
            $instance->setAttribute($attribute->getForeignKey(), $attribute->getKey());
            $instance->setAttribute('value', $value);
            $value = $instance;
        }
        return $this->setRelation($attribute->getAttribute('attribute_code'), $value);
    }

    /**
     * Determine if the given key is a raw entity attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isRawEntityAttribute($key)
    {
        return (bool)preg_match('/^raw(\w+)object$/i', $key);
    }

    /**
     * Get entity attribute bare name.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getEntityAttributeName($key)
    {
        return $this->isRawEntityAttribute($key) ? Str::camel(str_ireplace(['raw', 'object'], ['', ''], $key)) : $key;
    }

    /**
     * Scope query with the given entity attribute.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param mixed $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasAttribute(Builder $query, $key, $value)
    {
        return $query->whereHas($key, function (Builder $query) use ($value) {
            $query->where('value', $value)->where('entity_type_id', $this->getEntityTypeId());
        });
    }

    /**
     * Dynamically pipe calls to attribute relations.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
//        $this->bootIfNotBooted();

//        if ($this->isAttributeRelation($method)) {
//            return call_user_func_array($this->attributeRelations[$method], $parameters);
//        }

        return parent::__call($method, $parameters);
    }
}