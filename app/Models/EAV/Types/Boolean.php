<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午3:22
 */

namespace App\Models\EAV\Types;


use App\Models\EAV\Value;

class Boolean extends Value
{
    /**
     * {@inheritdoc}
     */
    protected $casts = ['value' => 'boolean'];

    protected $table = 'value_boolean';

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