<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/16
 * Time: 下午9:44
 */

namespace app\Exceptions;

class MissingEntityException extends \UnexpectedValueException implements EAVExceptionInterface
{
    /**
     */
    public function __construct()
    {
        parent::__construct("Undefined entity");
    }
}