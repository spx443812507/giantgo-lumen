<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/13
 * Time: 下午3:23
 */

namespace App\Models\EAV\Types;

use App\Models\EAV\Value;
use Carbon\Carbon;

class Datetime extends Value
{
    /**
     * {@inheritdoc}
     */
    protected $dates = ['value'];

    protected $table = 'value_datetime';

    protected function getValueAttribute()
    {
        $value = $this->attributes['value'];

        if (!empty($value)) {
            return $this->serializeDate($this->asDateTime($value));
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if (strlen($value)) {
            $this->attributes['value'] = Carbon::createFromFormat(\DateTime::ATOM, $value);
        } else {
            $this->attributes['value'] = null;
        }
    }

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}