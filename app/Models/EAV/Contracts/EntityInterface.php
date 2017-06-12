<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午3:58
 */

namespace app\Models\EAV;

use app\Models\EAV\Contracts\AttributeInterface;
use Sidus\EAVModelBundle\Exception\MissingAttributeException;

interface EntityInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return AttributeInterface[]
     */
    public function getAttributes();

    /**
     * @param string $code
     *
     * @throws MissingAttributeException
     *
     * @return AttributeInterface
     */
    public function getAttribute($code);

    /**
     * @param string $code
     *
     * @return bool
     */
    public function hasAttribute($code);
}