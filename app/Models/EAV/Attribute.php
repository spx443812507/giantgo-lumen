<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午5:06
 */

namespace App\Models\EAV;

use App\Models\EAV\Types\Varchar;
use App\Models\EAV\Types\Text;
use App\Models\EAV\Types\Boolean;
use App\Models\EAV\Types\Datetime;
use App\Models\EAV\Types\Integer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use SoftDeletes;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type_id', 'attribute_code', 'attribute_model',
        'frontend_model', 'frontend_input', 'frontend_label', 'frontend_class',
        'is_required', 'is_user_defined', 'is_unique', 'default_value', 'description'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'backend_model', 'backend_type', 'backend_table', 'deleted_at'
    ];

    protected $table = 'attributes';

    protected $casts = [
        'is_required' => 'boolean',
        'is_user_defined' => 'boolean',
        'is_unique' => 'boolean',
        'is_collection' => 'boolean'
    ];

    protected $inputMappings = [
        'text' => [
            'backend_model' => Varchar::class,
            'backend_type' => 'Varchar',
            'backend_table' => 'value_varchar',
            'is_collection' => false,
            'has_options' => false,
        ],
        'textarea' => [
            'backend_model' => Text::class,
            'backend_type' => 'Text',
            'backend_table' => 'value_text',
            'is_collection' => false,
            'has_options' => false,
        ],
        'switch' => [
            'backend_model' => Boolean::class,
            'backend_type' => 'Boolean',
            'backend_table' => 'value_boolean',
            'is_collection' => false,
            'has_options' => false,
        ],
        'radio' => [
            'backend_model' => Integer::class,
            'backend_type' => 'Integer',
            'backend_table' => 'value_integer',
            'is_collection' => false,
            'has_options' => true,
        ],
        'checkbox' => [
            'backend_model' => Integer::class,
            'backend_type' => 'Integer',
            'backend_table' => 'value_integer',
            'is_collection' => true,
            'has_options' => true,
        ],
        'select' => [
            'backend_model' => Integer::class,
            'backend_type' => 'Integer',
            'backend_table' => 'value_integer',
            'is_collection' => false,
            'has_options' => true,
        ],
        'number' => [
            'backend_model' => Integer::class,
            'backend_type' => 'Integer',
            'backend_table' => 'value_integer',
            'is_collection' => false,
            'has_options' => false,
        ],
        'datetime' => [
            'backend_model' => Datetime::class,
            'backend_type' => 'Datetime',
            'backend_table' => 'value_datetime',
            'is_collection' => false,
            'has_options' => false,
        ]
    ];

    public function setFrontendInputAttribute($input)
    {
        $this->attributes['frontend_input'] = $input;
        $this->attributes['backend_model'] = $this->inputMappings[$input]['backend_model'];
        $this->attributes['backend_type'] = $this->inputMappings[$input]['backend_type'];
        $this->attributes['backend_table'] = $this->inputMappings[$input]['backend_table'];
        $this->attributes['is_collection'] = $this->inputMappings[$input]['is_collection'];
    }

    public function hasOptions()
    {
        return $this->inputMappings[$this->attributes['frontend_input']]['has_options'];
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

    /**
     * Relationship to the options.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(Option::class, 'attribute_id', 'id');
    }

    public function entities()
    {
        return $this->belongsToMany('App\Models\Eav\Entity', 'entity_attribute', 'attribute_id', 'entity_type_id');
    }
}