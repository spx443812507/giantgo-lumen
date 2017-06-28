<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/25
 * Time: 上午11:20
 */

namespace App\Http\Controllers;


use App\Models\EAV\Entity;
use App\Models\EAV\Factories\EntityFactory;
use Illuminate\Http\Request;
use Mockery\Exception;

class EntityController extends Controller
{
    private $entityMappings = [
        'user' => [
            'entity_model' => 'App\Models\User',
            'entity_table' => 'users'
        ]
    ];

    public function createEntity(Request $request)
    {
        $this->validate($request, [
            'entity_type_name' => 'required|max:255',
            'entity_type_code' => 'required'
        ]);

        $entityTypeCode = $request->input('entity_type_code');

        if (!array_has($this->entityMappings, $entityTypeCode)) {
            return response()->json(['error' => 'entity_type_not_support'], 500);
        }

        $entityInfo = array_merge($request->only('entity_type_name', 'entity_type_code', 'description'), [
            'entity_model' => $this->entityMappings[$entityTypeCode]['entity_model'],
            'entity_table' => $this->entityMappings[$entityTypeCode]['entity_table'],
        ]);

        try {
            $entity = EntityFactory::create($entityInfo);
        } catch (Exception $e) {
            return response()->json('create_error', 500);
        }

        return response()->json($entity, 200);
    }

    public function getEntityList(Request $request, $entityTypeCode)
    {
        if (!array_has($this->entityMappings, $entityTypeCode)) {
            return response()->json(['error' => 'entity_type_not_support'], 500);
        }

        try {
            $entities = EntityFactory::where('entity_type_code', $entityTypeCode)->where('id', '<>', '1')->get();

            foreach ($entities as $entity) {
                $entityClass = EntityFactory::getEntity($entity->id);

                $entity->entity_instance_count = count($entityClass::where('entity_type_id', $entity->id)->get());
            }
        } catch (Exception $e) {
            return response()->json('entity_not_exists', 500);
        }

        return response()->json($entities, 200);
    }
}