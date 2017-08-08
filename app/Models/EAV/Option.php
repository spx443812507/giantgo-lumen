<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/6/25
 * Time: 下午4:38
 */

namespace App\Models\EAV;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use SoftDeletes;

    protected $fillable = ['attribute_id', 'label'];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

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