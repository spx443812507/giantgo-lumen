<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午3:25
 */

namespace App\Models\EAV\Types;


use App\Models\EAV\Value;

class Integer extends Value
{
    protected $table = 'value_integer';


    public function setValueAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['value'] = null;
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}