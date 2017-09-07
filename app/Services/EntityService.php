<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/9/6
 * Time: 下午10:00
 */

namespace App\Services;

use App\Models\EAV\Entity;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EntityService
{
    protected $attributeService;

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
        ],
        'agenda' => [
            'entity_model' => 'App\Models\Agenda',
            'entity_table' => 'agendas'
        ]
    ];

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function getEntity($entityTypeId)
    {
        $entity = Entity::find($entityTypeId);

        if (empty($entity)) {
            throw new Exception('entity_not_exists');
        }

        return $entity;
    }

    public function getEntityList($entityTypeCode)
    {
        if (!array_has($this->entityMappings, $entityTypeCode)) {
            throw new Exception('entity_type_not_support');
        }

        try {
            $entities = Entity::where('entity_type_code', $entityTypeCode)->get();

            foreach ($entities as $entity) {
                $entityClass = $entity->entity_model;

                $entity->attributes = $this->attributeService->getAttributeList($entity->id);

                $entity->instances_count = count($entityClass::where('entity_type_id', $entity->id)->get());
            }
        } catch (Exception $e) {
            throw new Exception('fetch_entity_fail');
        }

        return $entities;
    }

    public function createEntity($entityInfo)
    {
        $validators = [
            'entity_type_name' => 'required|max:255'
        ];

        $validator = Validator::make($entityInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $entityTypeCode = $entityInfo['entity_type_code'];

        if (!array_has($this->entityMappings, $entityTypeCode)) {
            throw new Exception('entity_type_not_support');
        }

        $entityInfo = array_merge($entityInfo, [
            'entity_model' => $this->entityMappings[$entityTypeCode]['entity_model'],
            'entity_table' => $this->entityMappings[$entityTypeCode]['entity_table']
        ]);

        try {
            $entity = Entity::create($entityInfo);
        } catch (Exception $e) {
            throw new Exception('create_entity_fail');
        }

        return $entity;
    }

    public function updateEntity($entityTypeId, $entityInfo)
    {
        $validators = [
            'entity_type_name' => 'required|max:255'
        ];

        $validator = Validator::make($entityInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $entity = $this->getEntity($entityTypeId);

            $entity->fill($entityInfo)->save();
        } catch (Exception $e) {
            throw new Exception('create_entity_fail');
        }

        return $entity;
    }

    public function deleteEntity($entityTypeId)
    {
        $entity = $this->getEntity($entityTypeId);

        $entity->attributes()->sync([]);

        return $entity->delete();
    }
}