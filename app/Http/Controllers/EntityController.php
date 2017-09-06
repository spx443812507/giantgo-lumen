<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/25
 * Time: 上午11:20
 */

namespace App\Http\Controllers;

use App\Services\EntityService;
use Illuminate\Http\Request;
use Mockery\Exception;

class EntityController extends Controller
{
    protected $entityService;

    public function __construct(EntityService $entityService)
    {
        $this->entityService = $entityService;
    }

    public function getEntity(Request $request, $entityTypeId)
    {
        try {
            $entity = $this->entityService->getEntity($entityTypeId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($entity);
    }

    public function getEntityList(Request $request, $entityTypeCode)
    {
        try {
            $entities = $this->entityService->getEntityList($entityTypeCode);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($entities);
    }

    public function createEntity(Request $request)
    {
        $entityInfo = $request->only('entity_type_name', 'entity_type_code', 'description');

        try {
            $entity = $this->entityService->createEntity($entityInfo);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($entity, 201);
    }

    public function updateEntity(Request $request, $entityTypeId)
    {
        $entityInfo = $request->only('entity_type_name', 'description');

        try {
            $entity = $this->entityService->updateEntity($entityTypeId, $entityInfo);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($entity);
    }

    public function deleteEntity(Request $request, $entityTypeId)
    {
        try {
            $this->entityService->deleteEntity($entityTypeId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(null, 204);
    }
}