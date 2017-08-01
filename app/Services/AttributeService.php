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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AttributeService
{
    protected function makeValidators($attributeId = null)
    {
        $validators = [
            'attribute_code' => 'required',
            'frontend_label' => 'required',
            'options' => 'array',
            'options.*.value' => 'required'
        ];

        $unique = Rule::unique('attributes');

        if (!empty($attributeId)) {
            $unique = $unique->ignore($attributeId);
        }

        $validators['attribute_code'] = $unique;

        return $validators;
    }

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

    public function createAttribute($entityTypeId, $attributes = [])
    {
        //已创建的属性
        $results = [];

        if (is_object($attributes)) {
            $attributes = [$attributes];
        }

        $entity = Entity::find($entityTypeId);

        if (empty($entity)) {
            throw new Exception('entity_type_not_exists');
        }

        DB::beginTransaction();

        try {
            foreach ($attributes as $attr) {
                $attribute = $entity->attributes()->create([
                    'entity_type_id' => $entity->id,
                    'attribute_code' => $attr['attribute_code'],
                    'frontend_input' => $attr['frontend_input'],
                    'frontend_model' => empty($attr['frontend_model']) ? '' : $attr['frontend_model'],
                    'frontend_label' => $attr['frontend_label'],
                    'frontend_class' => empty($attr['frontend_class']) ? '' : $attr['frontend_class'],
                    'is_required' => $attr['is_required'],
                    'is_user_defined' => false,
                    'is_unique' => $attr['is_unique'],
                    'default_value' => $attr['default_value'],
                    'description' => $attr['description'],
                ]);

                if (array_has($attr, 'options') && count($attr['options']) > 0) {
                    $options = $attr['options'];

                    $optionDataList = [];

                    foreach ($options as $option) {
                        $optionDataList[] = new Option([
                            'attribute_id' => $attribute->id,
                            'value' => $option['value']
                        ]);
                    }

                    $attribute['options'] = $attribute->options()->saveMany($optionDataList);
                }

                $results[] = $attribute;
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('create_error');
        }

        DB::commit();

        return $results;
    }

    public function updateAttribute($attributeId, $attributeInfo)
    {
        $attribute = Attribute::find($attributeId);

        if (empty($attribute)) {
            throw new Exception('attribute_not_exists');
        }

        $validators = $this->makeValidators($attributeId);

        $validator = Validator::make($attributeInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $attribute->attribute_code = $attributeInfo['attribute_code'];
        $attribute->frontend_label = $attributeInfo['frontend_label'];
        $attribute->frontend_input = $attributeInfo['frontend_input'];
        $attribute->is_required = $attributeInfo['is_required'];
        $attribute->is_unique = $attributeInfo['is_unique'];

        $options = $attributeInfo['options'];

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

        $attribute->load('options');

        $attribute->save();

        return $attribute;
    }
}