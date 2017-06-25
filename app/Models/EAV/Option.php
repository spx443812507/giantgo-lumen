<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/25
 * Time: 下午4:38
 */

namespace App\Models\EAV;

use App\Models\Model;

class Option extends Model
{
    protected $fillable = ['attribute_id', 'value'];

    /**
     * Relationship to the options entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(Option::class, 'attribute_id', 'id');
    }
}