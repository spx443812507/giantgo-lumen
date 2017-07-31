<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/3/26
 * Time: 下午1:47
 */

namespace App\Http\Controllers;

use App\Services\AttributeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
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

        $attribute = $this->attributeService->createAttribute($entityTypeId, $request->all());

        return response()->json($attribute[0], 200);
    }

    public function batchCreateAttribute(Request $request)
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

        try {
            $result = $this->attributeService->createAttribute($entityTypeId, $request->input('attributes'));
        } catch (Exception $e) {
            throw $e;
        }

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

        $attribute = $this->attributeService->updateAttribute($request->input('id'), $attributeData);

        return response()->json($attribute, 200);
    }

    public function getAttributeList(Request $request)
    {
        $this->validate($request, [
            'entity_type_id' => 'required|integer'
        ]);

        $entityTypeId = $request->input('entity_type_id');

        $result = $this->attributeService->getAttributeList($entityTypeId);

        return response()->json($result);
    }
}