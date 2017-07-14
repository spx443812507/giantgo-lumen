<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/12
 * Time: 下午5:06
 */

namespace App\Models\EAV;


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
        'is_unique' => 'boolean'
    ];

    protected $inputMappings = [
        'text' => [
            'backend_type' => 'App\Models\EAV\Types\Varchar',
            'is_collection' => false,
            'has_options' => false,
        ],
        'textarea' => [
            'backend_type' => 'App\Models\EAV\Types\Text',
            'is_collection' => false,
            'has_options' => false,
        ],
        'switch' => [
            'backend_type' => 'App\Models\EAV\Types\Boolean',
            'is_collection' => false,
            'has_options' => false,
        ],
        'radio' => [
            'backend_type' => 'App\Models\EAV\Types\Integer',
            'is_collection' => false,
            'has_options' => true,
        ],
        'checkbox' => [
            'backend_type' => 'App\Models\EAV\Types\Integer',
            'is_collection' => true,
            'has_options' => true,
        ],
        'select' => [
            'backend_type' => 'App\Models\EAV\Types\Integer',
            'is_collection' => false,
            'has_options' => true,
        ],
        'number' => [
            'backend_type' => 'App\Models\EAV\Types\Integer',
            'is_collection' => false,
            'has_options' => false,
        ],
        'datetime' => [
            'backend_type' => 'App\Models\EAV\Types\Datetime',
            'is_collection' => false,
            'has_options' => false,
        ]
    ];

    public function setFrontendInputAttribute($input)
    {
        $this->attributes['frontend_input'] = $input;
        $this->attributes['backend_type'] = $this->inputMappings[$input]['backend_type'];
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