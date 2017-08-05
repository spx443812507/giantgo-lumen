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

        try {
            $attribute = $this->attributeService->createAttribute($entityTypeId, $request->all());
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($attribute[0], 200);
    }

    public function batchCreateAttribute(Request $request)
    {
        $entityTypeId = $request->input('entity_type_id');

        $this->validate($request, [
            'attributes' => 'required|array',
            'attributes.*.attribute_code' => 'required|unique:attributes,attribute_code,NULL,id,entity_type_id,' . $entityTypeId,
            'attributes.*.frontend_label' => 'required',
            'attributes.*.frontend_input' => 'required',
            'attributes.*.options' => 'array',
            'attributes.*.options.*.value' => 'required'
        ]);

        try {
            $result = $this->attributeService->createAttributes($entityTypeId, $request->input('attributes'));
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($result, 200);
    }

    public function updateAttribute(Request $request, $attributeId)
    {
        $attributeInfo = $request->input('attribute');

        try {
            $attribute = $this->attributeService->updateAttribute($attributeId, $attributeInfo);
        } catch (Exception $e) {
            throw $e;
        }

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