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
    protected function makeValidators($entityTypeId, $attributeId = null)
    {
        $validators = [
            'attribute_code' => 'required',
            'frontend_label' => 'required',
            'frontend_input' => 'required',
            'options' => 'array',
            'options.*.label' => 'required'
        ];

        $unique = Rule::unique('attributes')->where(function ($query) use ($entityTypeId) {
            $query->where('entity_type_id', $entityTypeId);
        });

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
            throw new Exception('entity_type_not_exists');
        }

        $entityClass = new $entityType->entity_model();

        $attributes = $entityClass->attributes();

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

    public function createAttribute($entityTypeId, $attributeInfo)
    {
        $attribute = null;

        $entity = Entity::find($entityTypeId);

        if (empty($entity)) {
            throw new Exception('entity_type_not_exists');
        }

        $validators = $this->makeValidators($entityTypeId);

        $validator = Validator::make($attributeInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $attribute = $entity->attributes()->create([
                'entity_type_id' => $entity->id,
                'attribute_code' => $attributeInfo['attribute_code'],
                'frontend_input' => $attributeInfo['frontend_input'],
                'frontend_model' => empty($attributeInfo['frontend_model']) ? '' : $attributeInfo['frontend_model'],
                'frontend_label' => $attributeInfo['frontend_label'],
                'frontend_class' => empty($attributeInfo['frontend_class']) ? '' : $attributeInfo['frontend_class'],
                'is_required' => $attributeInfo['is_required'],
                'is_user_defined' => false,
                'is_unique' => empty($attributeInfo['is_unique']) ? false : $attributeInfo['is_unique'],
                'default_value' => empty($attributeInfo['default_value']) ? '' : $attributeInfo['default_value'],
                'description' => empty($attributeInfo['description']) ? '' : $attributeInfo['description']
            ]);

            if (array_has($attributeInfo, 'options') && count($attributeInfo['options']) > 0) {
                $options = $attributeInfo['options'];

                $optionDataList = [];

                foreach ($options as $option) {
                    $optionDataList[] = new Option([
                        'attribute_id' => $attribute->id,
                        'label' => $option['label']
                    ]);
                }

                $attribute['options'] = $attribute->options()->saveMany($optionDataList);
            }
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception('create_error');
        }

        DB::commit();

        return $attribute;
    }

    public function createAttributes($entityTypeId, $attributes)
    {
        $results = [];

        $entity = Entity::find($entityTypeId);

        if (empty($entity)) {
            throw new Exception('entity_type_not_exists');
        }

        $validator = Validator::make($attributes, [
            '*.attribute_code' => 'required|unique:attributes,attribute_code,NULL,id,entity_type_id,' . $entityTypeId,
            '*.frontend_label' => 'required',
            '*.frontend_input' => 'required',
            '*.options' => 'array',
            '*.options.*.label' => 'required'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        foreach ($attributes as $attribute) {
            $results[] = $this->createAttribute($entityTypeId, $attribute);
        }

        return $results;
    }

    public function updateAttribute($attributeId, $attributeInfo)
    {
        $attribute = Attribute::find($attributeId);

        if (empty($attribute)) {
            throw new Exception('attribute_not_exists');
        }

        $validators = $this->makeValidators($attribute->entity_type_id, $attributeId);

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
                        $attributeOptionMaps[$option['id']]->label = $option['label'];
                        $attributeOptionMaps[$option['id']]->save();
                    }
                } else {
                    $attribute->options()->saveMany([
                        new Option([
                            'attribute_id' => $attribute->id,
                            'label' => $option['label']
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