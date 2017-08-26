<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/7
 * Time: 下午10:23
 */

namespace App\Services;

use App\Models\Seminar;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SeminarService
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function getSeminar($seminarId, $includeAttributes = false)
    {
        $seminar = Seminar::find($seminarId);

        $entityTypeId = $seminar->entity_type_id;

        if (empty($seminar)) {
            throw new Exception('seminar_not_exists');
        }

        if (!!$includeAttributes && !empty($entityTypeId)) {
            $seminar->attributes = $this->attributeService->getAttributeList($entityTypeId);
        }

        return $seminar;
    }

    public function getSeminarList($perPage)
    {
        $seminars = Seminar::paginate($perPage);

        return $seminars;
    }

    public function createSeminar($seminarInfo, User $user)
    {
        $seminar = new Seminar($seminarInfo);

        $validators = array_merge([
            'title' => 'required|max:255',
            'start_at' => 'required|date|date_format:' . DateTime::ATOM,
            'end_at' => 'required|date|date_format:' . DateTime::ATOM . '|after:start_at',
        ], $seminar->makeValidators(array_keys($seminarInfo)));

        $validator = Validator::make($seminarInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $user->seminars()->save($seminar);
        } catch (Exception $e) {
            throw new Exception('create_seminar_fail');
        }

        return $seminar;
    }

    public function updateSeminar($seminarId, $seminarInfo)
    {
        $seminar = $this->getSeminar($seminarId);

        if (!empty($seminarInfo['entity_type_id'])) {
            $seminar->entity_type_id = $seminarInfo['entity_type_id'];
        }

        $validators = array_merge([
            'title' => 'required|max:255',
            'start_at' => 'required|date|date_format:' . DateTime::ATOM,
            'end_at' => 'required|date|date_format:' . DateTime::ATOM . '|after:start_at',
        ], $seminar->makeValidators(array_keys($seminarInfo)));

        $validator = Validator::make($seminarInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $seminar->fill($seminarInfo)->save();
        } catch (Exception $exception) {
            throw new Exception('update_seminar_fail');
        }

        return $seminar;
    }

    public function deleteSeminar($seminarId)
    {
        $seminar = $this->getSeminar($seminarId);

        return $seminar->delete();
    }
}