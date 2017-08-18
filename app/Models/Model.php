<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/4/4
 * Time: 下午10:25
 */

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public function __construct(array $attributes = [])
    {
        $this->fillable[] = 'entity_type_id';

        if (!empty($attributes['entity_type_id'])) {
            $this->bootEntityAttribute($attributes['entity_type_id']);
        }

        parent::__construct($attributes);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('c');
    }
}