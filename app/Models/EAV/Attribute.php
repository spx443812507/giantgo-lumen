<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午5:06
 */

namespace pp\Models\EAV;


use app\Models\EAV\Contracts\AttributeInterface;
use app\Models\EAV\EntityInterface;

class Attribute implements AttributeInterface
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var EntityInterface */
    protected $entity;

    /** @var AttributeType */
    protected $type;

    /** @var string */
    protected $group;

    /** @var array */
    protected $options = [];

    /** @var bool */
    protected $required = false;

    /** @var bool */
    protected $unique = false;

    /** @var array */
    protected $validationRules = [];

    /** @var mixed */
    protected $default;

    /**
     * @param string $code
     * @param array $configuration
     *
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return AttributeType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param EntityInterface $entity
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $code
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($code, $default = null)
    {
        if (!array_key_exists($code, $this->options)) {
            return $default;
        }

        return $this->options[$code];
    }

    /**
     * @param string $code
     * @param mixed $value
     */
    public function addOption($code, $value)
    {
        $this->options[$code] = $value;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return boolean
     */
    public function isUnique()
    {
        return $this->unique;
    }

    /**
     * @param boolean $unique
     */
    public function setUnique($unique)
    {
        $this->unique = $unique;
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return $this->validationRules;
    }

    /**
     * @param array $options
     */
    public function addValidationRule(array $options)
    {
        $this->validationRules[] = $options;
    }

    /**
     * @param array $validationRules
     */
    public function setValidationRules(array $validationRules)
    {
        $this->validationRules = $validationRules;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Attribute
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return Attribute
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }
}