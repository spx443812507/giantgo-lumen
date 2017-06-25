<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/25
 * Time: 上午11:20
 */

namespace App\Http\Controllers;


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
}