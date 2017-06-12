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
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model implements AttributeInterface
{
    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_code', 'attribute_model',
        'backend_model', 'backend_type', 'backend_table',
        'frontend_model', 'frontend_input', 'frontend_label', 'frontend_class',
        'is_required', 'is_user_defined', 'is_unique', 'default_value', 'note'
    ];

    protected $table = 'eav_attribute';

    protected $casts = [
        'is_required' => 'boolean',
        'is_user_defined' => 'boolean',
        'is_unique' => 'boolean'
    ];

    /** @var EntityInterface */
    protected $entity;

    /** @var string */
    protected $group;

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $validationRules = [];

    /**
     * Attribute constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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
}