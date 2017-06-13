<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午4:53
 */

namespace App\Models\EAV;


use App\Models\EAV\Traits\Attributable;
use App\Models\Model;

abstract class Entity extends Model
{
    use Attributable;

    abstract public function getEntityTypeId();
}