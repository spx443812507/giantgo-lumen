<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午3:18
 */

namespace App\Models\EAV;


use App\Supports\ValueCollection;
use App\Models\Model;

abstract class Value extends Model
{
    /**
     * Determine if value should push to relations when saving.
     *
     * @var bool
     */
    protected $shouldPush = false;

    /**
     * Relationship to the attribute entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'id');
    }

    /**
     * Polymorphic relationship to the entity instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Check if value should push to relations when saving.
     *
     * @return bool
     */
    public function shouldPush()
    {
        return $this->shouldPush;
    }

    /**
     * {@inheritdoc}
     */
    public function newCollection(array $models = [])
    {
        return new ValueCollection($models);
    }
}