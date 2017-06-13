<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午4:53
 */

namespace App\Models\EAV;


use App\Models\Model;

abstract class Entity extends Model
{
    protected $entityTypeId;

    public function getEntityTypeId()
    {
        return $this->entityTypeId;
    }

    public function setEntityTypeId()
    {
        return $this->entityTypeId;
    }
}