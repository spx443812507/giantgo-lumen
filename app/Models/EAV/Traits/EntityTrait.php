<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午4:49
 */

namespace App\Models\EAV\Traits;


trait EntityTrait
{
    /** @var string */
    protected $code;

    public function getCode()
    {
        return $this->code;
    }

    public function attributes()
    {
        return $this->hasMany('App\Models\Attribute', 'eav_attributes');
    }
}