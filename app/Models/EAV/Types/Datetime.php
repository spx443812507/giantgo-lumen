<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午3:23
 */

namespace App\Models\EAV\Types;


use App\Models\EAV\Value;

class Datetime extends Value
{
    /**
     * {@inheritdoc}
     */
    protected $dates = ['value'];

    protected $table = 'value_datetime';

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