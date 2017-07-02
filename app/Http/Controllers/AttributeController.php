<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午1:47
 */

namespace App\Http\Controllers;

use App\Models\EAV\Attribute;
use App\Models\EAV\Factories\EntityFactory;
use App\Models\EAV\Option;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    private function saveAttribute($entityTypeId, $attributeInfo)
    {
        $attribute = Attribute::create([
            'entity_type_id' => $entityTypeId,
            'attribute_code' => $attributeInfo['attribute_code'],
            'frontend_input' => $attributeInfo['frontend_input'],
            'frontend_model' => empty($attributeInfo['frontend_model']) ? '' : $attributeInfo['frontend_model'],
            'frontend_label' => $attributeInfo['frontend_label'],
            'frontend_class' => empty($attributeInfo['frontend_class']) ? '' : $attributeInfo['frontend_class'],
            'is_required' => $attributeInfo['is_required'],
            'is_user_defined' => false,
            'is_unique' => $attributeInfo['is_unique'],
            'default_value' => $attributeInfo['default_value'],
            'description' => $attributeInfo['description'],
        ]);

        if (array_has($attributeInfo, 'options') && count($attributeInfo['options']) > 0) {
            $options = $attributeInfo['options'];

            $optionDataList = [];

            foreach ($options as $option) {
                $optionDataList[] = new Option([
                    'attribute_id' => $attribute->id,
                    'value' => $option['value']
                ]);
            }

            $attribute['options'] = $attribute->options()->saveMany($optionDataList);
        }

        return $attribute;
    }

    public function createAttribute(Request $request)
    {
        $entityTypeId = $request->input('entity_type_id');

        $this->validate($request, [
            'entity_type_id' => 'required|integer',
            'attribute_code' => 'required|unique:attributes,attribute_code,NULL,id,entity_type_id,' . $entityTypeId,
            'frontend_label' => 'required',
            'frontend_input' => 'required',
            'options' => 'array',
            'options.*.value' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $attribute = $this->saveAttribute($entityTypeId, $request->all());
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'create_error'], 500);
        }

        DB::commit();

        return response()->json($attribute, 200);
    }

    public function createAttributes(Request $request)
    {
        $entityTypeId = $request->input('entity_type_id');

        $this->validate($request, [
            'entity_type_id' => 'required|integer',
            'attributes' => 'required|array',
            'attributes.*.attribute_code' => 'required|unique:attributes,attribute_code,NULL,id,entity_type_id,' . $entityTypeId,
            'attributes.*.frontend_label' => 'required',
            'attributes.*.frontend_input' => 'required',
            'attributes.*.options' => 'array',
            'attributes.*.options.*.value' => 'required'
        ]);

        $result = [];

        $attributes = $request->input('attributes');

        DB::beginTransaction();

        try {
            for ($index = 0; $index < count($attributes); $index++) {
                $attribute = $this->saveAttribute($entityTypeId, $attributes[$index]);
                $result[] = $attribute;
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'create_error'], 500);
        }

        DB::commit();

        return response()->json($result, 200);
    }

    public function updateAttribute(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'attribute_code' => 'required',
            'frontend_label' => 'required',
            'options' => 'array',
            'options.*.value' => 'required'
        ]);

        $attributeData = $request->all();

        $validator = Validator::make($attributeData, [
            'attribute_code' => [
                Rule::unique('attributes')->ignore($attributeData['id']),
            ]
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'attribute_code_has_exists'], 400);
        }

        $attribute = Attribute::find($request->input('id'));

        if (empty($attribute)) {
            return response()->json(['error' => 'attribute_not_exists'], 400);
        }

        $attribute->attribute_code = $request->input('attribute_code');
        $attribute->frontend_label = $request->input('frontend_label');
        $attribute->frontend_input = $request->input('frontend_input');
        $attribute->is_required = $request->input('is_required');
        $attribute->is_unique = $request->input('is_unique');

        $options = $request->input('options');

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

        return response()->json($attribute, 200);
    }

    public function getAttributes(Request $request)
    {
        $this->validate($request, [
            'entity_type_id' => 'required|integer'
        ]);

        $entityClass = EntityFactory::getEntity($request->input('entity_type_id'));

        $entity = new $entityClass();

        $attributes = $entity->attributes();

        $result = [];

        foreach ($attributes as $key => $attribute) {
            if ($attribute->hasOptions()) {
                $attribute->load('options');
            }

            $result[] = $attribute;
        }

        return response()->json($result);
    }
}