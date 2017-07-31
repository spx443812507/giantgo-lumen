<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/31
 * Time: 下午8:44
 */

namespace App\Services;


use Illuminate\Validation\Rule;

class EntityService
{
    public function makeValidators($entity, $columns = ['*'])
    {
        $validators = [];

        foreach ($columns as $column) {
            if ($entity->isEntityAttribute($column)) {
                $validators[$column] = [];

                $attributes = $entity->attributes();

                if ($attributes[$column]->is_required) {
                    $validators[$column][] = 'required';
                }

                if ($attributes[$column]->is_unique) {
                    $unique = Rule::unique($attributes[$column]->backend_table);

                    if (!empty($entity->id)) {
                        $unique->ignore($entity->id);
                    }

                    $validators[$column][] = $unique;
                }

                if ($attributes[$column]->hasOptions()) {
                    $optionIds = $attributes[$column]->options()->get()->pluck('id')->toArray();

                    if ($attributes[$column]->is_collection) {
                        $validators[$column . '.*'][] = Rule::in($optionIds);
                    } else {
                        $validators[$column][] = Rule::in($optionIds);
                    }
                }
            }
        }

        return $validators;
    }
}