<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/18
 * Time: 下午4:22
 */

namespace App\Events;


use App\Models\EAV\Attribute;
use Illuminate\Support\Facades\DB;

class AttributeSaved
{
    /**
     * Refresh cache when saving attributes.
     * @param Attribute $attribute
     */
    public function handle(Attribute $attribute)
    {
        DB::table('entity_attribute')->insert([
            'attribute_id' => $attribute->id,
            'entity_type_id' => $attribute->entity_type_id
        ]);
    }
}