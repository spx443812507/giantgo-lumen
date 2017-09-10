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

    public function getAttribute(Request $request, $entityTypeId, $attributeId)
    {
        try {
            $attribute = $this->attributeService->getAttribute($entityTypeId, $attributeId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($attribute);
    }

    public function getAttributeList(Request $request, $entityTypeId)
    {
        try {
            $attributes = $this->attributeService->getAttributeList($entityTypeId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($attributes);
    }

    public function searchAttributeList(Request $request)
    {
        $perPage = $request->input('per_page');
        $entityTypeId = $request->input('entity_type_id');
        $attributeCode = $request->input('attribute_code');

        try {
            $attributes = $this->attributeService->searchAttributeList($perPage, $entityTypeId, $attributeCode);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($attributes);
    }

    public function checkAttributeCode(Request $request, $entityTypeId, $attributeCode)
    {
        $attributeId = $request->input('attribute_id');

        try {
            $result = $this->attributeService->checkAttributeCode($entityTypeId, $attributeCode, $attributeId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($result);
    }

    public function createAttribute(Request $request, $entityTypeId)
    {
        try {
            $attribute = $this->attributeService->createAttribute($entityTypeId, $request->all());
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($attribute, 200);
    }

    public function batchCreateAttribute(Request $request, $entityTypeId)
    {
        try {
            $result = $this->attributeService->createAttributes($entityTypeId, $request->input('attributes'));
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($result, 200);
    }

    public function updateAttribute(Request $request, $entityTypeId, $attributeId)
    {
        try {
            $attribute = $this->attributeService->updateAttribute($entityTypeId, $attributeId, $request->all());
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($attribute, 200);
    }

    public function deleteAttribute(Request $request, $entityTypeId, $attributeId)
    {
        try {
            $this->attributeService->deleteAttribute($entityTypeId, $attributeId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(null, 204);
    }
}