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
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('c');
    }
}