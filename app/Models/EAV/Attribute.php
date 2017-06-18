<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午5:06
 */

namespace App\Models\EAV;


use App\Events\AttributeSaved;
use App\Models\EAV\Contracts\AttributeInterface;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model implements AttributeInterface
{
    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type_id', 'attribute_code', 'attribute_model',
        'backend_model', 'backend_type', 'backend_table',
        'frontend_model', 'frontend_input', 'frontend_label', 'frontend_class',
        'is_required', 'is_user_defined', 'is_unique', 'default_value', 'description'
    ];

    protected $table = 'attributes';

    protected $casts = [
        'is_required' => 'boolean',
        'is_user_defined' => 'boolean',
        'is_unique' => 'boolean'
    ];

    protected $inputMappings = [
        'text' => [
            'backend_type' => 'varchar',
            'is_collection' => false
        ],
        'textarea' => [
            'backend_type' => 'text',
            'is_collection' => false
        ],
        'swith' => [
            'backend_type' => 'boolean',
            'is_collection' => false
        ],
        'radio' => [
            'backend_type' => 'integer',
            'is_collection' => false
        ],
        'checkbox' => [
            'backend_type' => 'integer',
            'is_collection' => true
        ],
        'number' => [
            'backend_type' => 'integer',
            'is_collection' => false
        ],
        'datetime' => [
            'backend_type' => 'datetime',
            'is_collection' => false
        ]
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
     * Registering events.
     */
    public static function boot()
    {
        parent::boot();

        static::saved(AttributeSaved::class . '@handle');
    }

    public function setFrontendInputAttribute($input)
    {
        $this->attributes['backend_type'] = $this->inputMappings[$input]['backend_type'];
        $this->attributes['is_collection'] = $this->inputMappings[$input]['is_collection'];
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

    /**
     * When an attribute is multiple, it's also a collection
     *
     * @return boolean
     */
    public function isCollection()
    {
        return $this->getAttribute('is_collection');
    }
}