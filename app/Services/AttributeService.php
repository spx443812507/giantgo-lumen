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
            'attribute_code' => ['required'],
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

        $validators['attribute_code'][] = $unique;

        return $validators;
    }

    public function getAttribute($entityTypeId, $attributeId)
    {
        $attribute = Attribute::find($attributeId);

        if (empty($attribute)) {
            throw new Exception('attribute_not_exists');
        }

        if ($attribute->entity_type_id != $entityTypeId) {
            throw new Exception('attribute_not_belong_to_entity');
        }

        return $attribute;
    }

    public function getAttributeList($entityTypeId)
    {
        $entityType = Entity::find($entityTypeId);

        if (empty($entityType)) {
            throw new Exception('entity_type_not_exists');
        }

        $attributes = Attribute::where('entity_type_id', $entityTypeId)->get();

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

    public function searchAttributeList(
        $perPage = null,
        $entityTypeId = null,
        $attributeCode = null
    )
    {
        $query = Attribute::query();

        if (!empty($entityTypeId)) {
            $query->where('entity_type_id', $entityTypeId);
        }

        if (!empty($attributeCode)) {
            $query->where('attribute_code', 'like', '%' . $attributeCode . '%');
        }

        if (empty($perPage)) {
            $perPage = 100;
        } else if ($perPage > 1000) {
            $perPage = 1000;
        }

        $attributes = $query->paginate($perPage);

        return $attributes;
    }

    public function checkAttributeCode($entityTypeId, $attributeCode, $attributeId = null)
    {
        $query = Attribute::query();

        if (!empty($entityTypeId)) {
            $query->where('entity_type_id', $entityTypeId);
        }

        if (!empty($attributeCode)) {
            $query->where('attribute_code', $attributeCode);
        }

        if (!empty($attributeId)) {
            $query->whereDoesntHave('entities', function ($query) use ($attributeId) {
                $query->where('attribute_id', '=', $attributeId);
            });
        }

        return $query->get()->isEmpty();
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
            $attribute = new Attribute();

            $attribute->fill($attributeInfo);

            $attribute->entity_type_id = $entityTypeId;

            $attribute = $entity->attributes()->save($attribute);

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

    public function updateAttribute($entityTypeId, $attributeId, $attributeInfo)
    {
        $attribute = Attribute::find($attributeId);

        if (empty($attribute)) {
            throw new Exception('attribute_not_exists');
        }

        if ($attribute->entity_type_id != $entityTypeId) {
            throw new Exception('attribute_not_belong_entity');
        }

        $validators = $this->makeValidators($attribute->entity_type_id, $attributeId);

        $validator = Validator::make($attributeInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $attribute->fill($attributeInfo);

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

    public function deleteAttribute($entityTypeId, $attributeId)
    {
        $attribute = $this->getAttribute($entityTypeId, $attributeId);

        if ($attribute->hasOptions()) {
            $attribute->options()->delete();
        }

        $valueClass = $attribute->backend_model;

        $valueClass::where('attribute_id', $attribute->id)->delete();

        $attribute->delete();
    }
}