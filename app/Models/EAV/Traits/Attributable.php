<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午3:05
 */

namespace App\Models\EAV\Traits;

use App\Events\EntityWasDeleted;
use App\Events\EntityWasSaved;
use App\Models\EAV\Scopes\EagerLoadScope;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SuperClosure\Serializer;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as BaseCollection;

use App\Models\EAV\Supports\RelationBuilder;
use App\Models\EAV\Supports\ValueCollection;
use App\Models\EAV\Value;
use App\Models\EAV\Attribute;

trait Attributable
{
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
    protected $entityAttributeValueTrash;
    /**
     * The entity attribute relations.
     *
     * @var array
     */
    protected $entityAttributeRelations = [];
    /**
     * Determine if the entity attribute relations have been booted.
     *
     * @var bool
     */
    protected $entityAttributeRelationsBooted = false;

    /**
     * Booting the Attributable trait.
     *
     * @return void
     */
    public static function bootAttributable()
    {
        $models = array_merge([static::class], array_values(class_parents(static::class)));
        $entityTypeIds = DB::table('entity_type')->whereIn('entity_model', $models)->get()->pluck('id');
        $attributes = DB::table('entity_attribute')->whereIn('entity_type_id', $entityTypeIds)->get()->pluck('attribute_id');
        static::$entityAttributes = Attribute::whereIn('id', $attributes)->get()->keyBy('attribute_code');
        static::addGlobalScope(new EagerLoadScope());
        static::saved(EntityWasSaved::class . '@handle');
        static::deleted(EntityWasDeleted::class . '@handle');
    }

    /**
     * {@inheritdoc}
     */
    protected function bootIfNotBooted()
    {
        parent::bootIfNotBooted();
        if (!$this->entityAttributeRelationsBooted) {
            app(RelationBuilder::class)->build($this);
            $this->entityAttributeRelationsBooted = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function relationsToArray()
    {
        $eavAttributes = [];
        $attributes = parent::relationsToArray();
        $relations = array_keys($this->getEntityAttributeRelations());
        foreach ($relations as $relation) {
            if (array_key_exists($relation, $attributes)) {
                $eavAttributes[$relation] = $this->getAttribute($relation) instanceof BaseCollection
                    ? $this->getAttribute($relation)->toArray() : $this->getAttribute($relation);
                // By unsetting the relation from the attributes array we will make
                // sure we do not provide a duplicity when adding the namespace.
                // Otherwise it would keep the relation as a key in the root.
                unset($attributes[$relation]);
            }
        }
        if (is_null($namespace = $this->getEntityAttributesNamespace())) {
            $attributes = array_merge($attributes, $eavAttributes);
        } else {
            Arr::set($attributes, $namespace, $eavAttributes);
        }
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelation($key, $value)
    {
        if ($value instanceof ValueCollection) {
            $value->link($this, $this->getEntityAttributes()->get($key));
        }
        return parent::setRelation($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationValue($key)
    {
        $value = parent::getRelationValue($key);
        // In case any relation value is found, we will just provide it as is.
        // Otherwise, we will check if exists any attribute relation for the
        // given key. If so, we will load the relation calling its method.
        if (is_null($value) && !$this->relationLoaded($key) && $this->isEntityAttributeRelation($key)) {
            $value = $this->getRelationshipFromMethod($key);
        }
        if ($value instanceof ValueCollection) {
            $value->link($this, $this->getEntityAttributes()->get($key));
        }
        return $value;
    }

    /**
     * Get the entity attributes namespace if exists.
     *
     * @return string|null
     */
    public function getEntityAttributesNamespace()
    {
        return property_exists($this, 'entityAttributesNamespace') ? $this->entityAttributesNamespace : null;
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
     * {@inheritdoc}
     */
    protected function fillableFromArray(array $attributes)
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
        $this->entityAttributeRelations[$relation] = $value;
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
        return isset($this->entityAttributeRelations[$key]);
    }

    /**
     * Get the entity attribute value trash.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEntityAttributeValueTrash()
    {
        return $this->entityAttributeValueTrash ?: $this->entityAttributeValueTrash = collect([]);
    }

    /**
     * Get the entity attribute relations.
     *
     * @return array
     */
    public function getEntityAttributeRelations()
    {
        return $this->entityAttributeRelations;
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
            return $value->pluck('content');
        }
        return !is_null($value) ? $value->getAttribute('content') : null;
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
     * Get the attributes attached to this entity.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function attributes()
    {
        return $this->getEntityAttributes();
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
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($this->isEntityAttributeRelation($method)) {
            return call_user_func_array($this->entityAttributeRelations[$method], $parameters);
        }
        return parent::__call($method, $parameters);
    }

    /**
     * Prepare the instance for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        if ($this->entityAttributeRelations && current($this->entityAttributeRelations) instanceof Closure) {
            $relations = $this->entityAttributeRelations;
            $this->entityAttributeRelations = [];
            foreach ($relations as $key => $value) {
                if ($value instanceof Closure) {
                    $this->setEntityAttributeRelation($key, (new Serializer())->serialize($value));
                }
            }
        }
        return array_keys(get_object_vars($this));
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        if ($this->entityAttributeRelations && is_string(current($this->entityAttributeRelations))) {
            $relations = $this->entityAttributeRelations;
            $this->entityAttributeRelations = [];
            foreach ($relations as $key => $value) {
                if (is_string($value)) {
                    $this->setEntityAttributeRelation($key, (new Serializer())->unserialize($value));
                }
            }
        }
    }
}