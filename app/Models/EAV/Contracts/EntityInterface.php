<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午3:58
 */

namespace app\Models\EAV;

interface EntityInterface
{
    /**
     * @return string
     */
    public function getCode();
}