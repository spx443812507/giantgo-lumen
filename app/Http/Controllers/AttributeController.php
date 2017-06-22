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
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function createAttributes(Request $request)
    {
        $this->validate($request, [
            'entity_type_id' => 'required|integer',
            'attributes' => 'required|array',
            'attributes.*.attribute_code' => 'required|unique:attributes,attribute_code',
            'attributes.*.frontend_label' => 'required',
            'attributes.*.frontend_input' => 'required',
        ]);

        $result = [];

        $attributes = $request->input('attributes');

        for ($index = 0; $index < count($attributes); $index++) {
            $result[] = Attribute::create([
                'entity_type_id' => $request->input('entity_type_id'),
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
        }

        return response()->json($result, 200);
    }

    public function getAttributes(Request $request)
    {
        $this->validate($request, [
            'entity_type_id' => 'required|integer'
        ]);

        $entityClass = EntityFactory::getEntity($request->input('entity_type_id'));

        $entity = new  $entityClass();

        return response()->json($entity->attributes());
    }
}