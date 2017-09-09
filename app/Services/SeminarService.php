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

        if (empty($seminar)) {
            throw new Exception('seminar_not_exists');
        }

        $entityTypeId = $seminar->entity_type_id;

        if ($includeAttributes && !empty($entityTypeId)) {
            $seminar->attributes = $this->attributeService->getAttributeList($entityTypeId);
        }

        return $seminar;
    }

    public function getSeminarList($perPage = null, $title = null, $startAt = null, $endAt = null, $sortBy = null, $order = 'desc')
    {
        $query = Seminar::query();

        if (!is_null($title)) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if (!is_null($startAt)) {
            $query->where('start_at', '>=', $startAt);
        }

        if (!is_null($endAt)) {
            $query->where('end_at', '<=', $endAt);
        }

        $query->when($sortBy, function ($query) use ($sortBy, $order) {
            return $query->orderBy($sortBy, $order);
        });

        $seminars = $query->paginate($perPage);

        $seminars->load('entity');

        return $seminars;
    }

    public function createSeminar($seminarInfo, User $user)
    {
        $seminar = new Seminar($seminarInfo);

        if (!empty($seminarInfo['entity_type_id'])) {
            $seminar->entity_type_id = $seminarInfo['entity_type_id'];
        }

        $messages = [];

        $validators = array_merge([
            'title' => 'required|max:255',
            'start_at' => 'required|date|date_format:' . DateTime::ATOM,
            'end_at' => 'required|date|date_format:' . DateTime::ATOM . '|after:start_at',
        ], $seminar->makeValidators(array_keys($seminarInfo), $messages));

        $validator = Validator::make($seminarInfo, $validators, $messages);

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

        $messages = [];

        $validators = array_merge([
            'title' => 'required|max:255',
            'start_at' => 'required|date|date_format:' . DateTime::ATOM,
            'end_at' => 'required|date|date_format:' . DateTime::ATOM . '|after:start_at',
        ], $seminar->makeValidators(array_keys($seminarInfo), $messages));

        $validator = Validator::make($seminarInfo, $validators, $messages);

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