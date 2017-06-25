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

class AttributeController extends Controller
{
    public function createAttributes(Request $request)
    {
        $entityTypeId = $request->input('entity_type_id');

        $this->validate($request, [
            'entity_type_id' => 'required|integer',
            'attributes' => 'required|array',
            'attributes.*.attribute_code' => 'required|unique:attributes,attribute_code,NULL,id,entity_type_id,' . $entityTypeId,
            'attributes.*.frontend_label' => 'required',
            'attributes.*.frontend_input' => 'required',
            'attributes.*.options' => 'array'
        ]);

        $result = [];

        $attributes = $request->input('attributes');

        DB::beginTransaction();

        try {

            for ($index = 0; $index < count($attributes); $index++) {
                $attribute = Attribute::create([
                    'entity_type_id' => $entityTypeId,
                    'attribute_code' => $attributes[$index]['attribute_code'],
                    'frontend_input' => $attributes[$index]['frontend_input'],
                    'frontend_model' => empty($attributes[$index]['frontend_model']) ? '' : $attributes[$index]['frontend_model'],
                    'frontend_label' => $attributes[$index]['frontend_label'],
                    'frontend_class' => empty($attributes[$index]['frontend_class']) ? '' : $attributes[$index]['frontend_class'],
                    'is_required' => $attributes[$index]['is_required'],
                    'is_user_defined' => false,
                    'is_unique' => $attributes[$index]['is_unique'],
                    'default_value' => $attributes[$index]['default_value'],
                    'description' => $attributes[$index]['description'],
                ]);

                if (array_has($attributes[$index], 'options') && count($attributes[$index]['options']) > 0) {
                    $options = $attributes[$index]['options'];

                    $optionDataList = [];

                    foreach ($options as $option) {
                        $optionDataList[] = new Option([
                            'attribute_id' => $attribute->id,
                            'value' => $option
                        ]);
                    }

                    $attribute['options'] = $attribute->options()->saveMany($optionDataList);
                }

                $result[] = $attribute;
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'create_error'], 500);
        }

        DB::commit();

        return response()->json($result, 200);
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