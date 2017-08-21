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

    public function getAttributeList(Request $request, $entityTypeId)
    {
        $result = $this->attributeService->getAttributeList($entityTypeId);

        return response()->json($result);
    }
}