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
    public function getSeminar($seminarId)
    {
        $seminar = Seminar::find($seminarId);

        if (empty($seminar)) {
            throw new Exception('contact_not_exists');
        }

        if (!empty($seminar->entity_type_id)) {
            $seminar->bootEntityAttribute($seminar->entity_type_id);
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
        $validators = [
            'title' => 'required|max:255',
            'start_at' => 'required|date|date_format:' . DateTime::ATOM . '|after_or_equal:' . Carbon::now('UTC'),
            'end_at' => 'required|date|date_format:' . DateTime::ATOM . '|after:start_at',
        ];

        $validator = Validator::make($seminarInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $seminar = new Seminar($seminarInfo);
            $user->seminars()->save($seminar);
        } catch (Exception $e) {
            throw new Exception('create_seminar_fail');
        }

        return $seminar;
    }

    public function updateSeminar($seminarId, $seminarInfo)
    {
        $seminar = $this->getSeminar($seminarId);

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