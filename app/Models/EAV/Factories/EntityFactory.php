<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/16
 * Time: 下午9:25
 */

namespace App\Models\EAV\Factories;


use app\Exceptions\MissingEntityException;
use App\Models\Model;
use Illuminate\Support\Facades\DB;

class EntityFactory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type_code', 'entity_model', 'attribute_model', 'entity_table'
    ];

    public static function getEntity($entityTypeId)
    {
        $entity = DB::table('entity_type')->find($entityTypeId);

        if (empty($entity)) {
            throw new MissingEntityException();
        }

        $entityClass = $entity->entity_model;

        $entityClass::$entityTypeId = $entityTypeId;

        return $entityClass;
    }
}