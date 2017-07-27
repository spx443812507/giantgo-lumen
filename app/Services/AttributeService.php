<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/24
 * Time: 下午11:39
 */

namespace App\Services;

use App\Models\EAV\Attribute;
use App\Models\EAV\Entity;
use App\Models\EAV\Option;
use Exception;
use Illuminate\Support\Facades\DB;

class AttributeService
{
    public function getAttributeList($entityTypeId)
    {
        $entityType = Entity::find($entityTypeId);

        if (empty($entityType)) {
            return [];
        }

        $instance = new $entityType->entity_model();

        $attributes = $instance->attributes();

        $result = [];

        if (!empty($attributes)) {
            foreach ($attributes as $key => $attribute) {
                if ($attribute->hasOptions()) {
                    $attribute->load('options');
                }

                $result[] = $attribute;
            }
        }

        return $result;
    }

    public function createAttribute($entityTypeId, $attribute)
    {
        //要创建的属性
        $attributes = [];
        //已创建的属性
        $results = [];

        if (is_array($attribute)) {
            $attributes = $attribute;
        }

        if (is_object($attribute)) {
            $attributes[] = $attribute;
        }

        $entity = Entity::find($entityTypeId);

        if (empty($entity)) {
            return response()->json(['error' => 'entity_type_not_exists'], 500);
        }

        DB::beginTransaction();

        try {
            $results[] = $entity->attributes()->create([
                'entity_type_id' => $entity->id,
                'attribute_code' => $attribute['attribute_code'],
                'frontend_input' => $attribute['frontend_input'],
                'frontend_model' => empty($attribute['frontend_model']) ? '' : $attribute['frontend_model'],
                'frontend_label' => $attribute['frontend_label'],
                'frontend_class' => empty($attribute['frontend_class']) ? '' : $attribute['frontend_class'],
                'is_required' => $attribute['is_required'],
                'is_user_defined' => false,
                'is_unique' => $attribute['is_unique'],
                'default_value' => $attribute['default_value'],
                'description' => $attribute['description'],
            ]);

            if (array_has($attribute, 'options') && count($attribute['options']) > 0) {
                $options = $attribute['options'];

                $optionDataList = [];

                foreach ($options as $option) {
                    $optionDataList[] = new Option([
                        'attribute_id' => $attribute->id,
                        'value' => $option['value']
                    ]);
                }

                $attribute['options'] = $attribute->options()->saveMany($optionDataList);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'create_error'], 500);
        }

        DB::commit();

        return $results;
    }

    public function updateAttribute($attributeId, $attributeData)
    {
        $attribute = Attribute::find($attributeId);

        if (empty($attribute)) {
            throw new Exception('attribute_not_exists');
        }

        $attribute->attribute_code = $attributeData->attribute_code;
        $attribute->frontend_label = $attributeData->frontend_label;
        $attribute->frontend_input = $attributeData->frontend_input;
        $attribute->is_required = $attributeData->is_required;
        $attribute->is_unique = $attributeData->is_unique;

        $options = $attributeData->options;

        if (isset($options)) {
            $requestOptionIds = [];

            $attributeOptions = $attribute->options()->get();
            $attributeOptionMaps = $attributeOptions->keyBy('id');
            $attributeOptionIds = $attributeOptions->pluck('id');

            foreach ($options as $option) {
                if (isset($option['id']) && !empty($option['id'])) {
                    $requestOptionIds[] = $option['id'];

                    if (array_has($attributeOptionMaps, $option['id'])) {
                        $attributeOptionMaps[$option['id']]->value = $option['value'];
                        $attributeOptionMaps[$option['id']]->save();
                    }
                } else {
                    $attribute->options()->saveMany([
                        new Option([
                            'attribute_id' => $attribute->id,
                            'value' => $option['value']
                        ])
                    ]);
                }
            }

            Option::whereIn('id', array_diff($attributeOptionIds->toArray(), $requestOptionIds))->delete();
        }

        $attribute->save();

        return $attribute;
    }
}