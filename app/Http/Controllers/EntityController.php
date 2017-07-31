<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/25
 * Time: 上午11:20
 */

namespace App\Http\Controllers;


use App\Models\EAV\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

class EntityController extends Controller
{
    private $entityMappings = [
        'contact' => [
            'entity_model' => 'App\Models\Contact',
            'entity_table' => 'contacts'
        ],
        'seminar' => [
            'entity_model' => 'App\Models\Seminar',
            'entity_table' => 'seminars'
        ],
        'speaker' => [
            'entity_model' => 'App\Models\Speaker',
            'entity_table' => 'speakers'
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
            'user_id' => Auth::user()->id
        ]);

        try {
            $entity = Entity::create($entityInfo);
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
            $entities = Entity::where('entity_type_code', $entityTypeCode)->paginate();

            foreach ($entities as $entity) {
                $entityClass = $entity->entity_model;

                $entity->entity_instance_count = count($entityClass::where('entity_type_id', $entity->id)->get());
            }
        } catch (Exception $e) {
            return response()->json('entity_not_exists', 500);
        }

        return response()->json($entities, 200);
    }
}