<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午5:06
 */

namespace app\Models\EAV\Contracts;


use app\Models\EAV\EntityInterface;

interface AttributeInterface
{
    /**
     * @return EntityInterface
     */
    public function getEntity();

    /**
     * Optional, used to separate attributes in different groups
     *
     * @return string
     */
    public function getGroup();

    /**
     * @param string $group
     */
    public function setGroup($group);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * Generic options that can be used in any applications using the EAV Model
     *
     * @param string $code
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($code, $default = null);

    /**
     * @param string $code
     * @param mixed $value
     */
    public function addOption($code, $value);

    /**
     * @return array
     */
    public function getValidationRules();

    /**
     * @param array $options
     */
    public function addValidationRule(array $options);

    /**
     * @param array $validationRules
     */
    public function setValidationRules(array $validationRules);
}