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
        return Seminar::find($seminarId);
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
            'start_at' => 'required|date|after_or_equal:' . Carbon::now(),
            'end_at' => 'required|date|after:start_at',
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

    public function updateSeminar()
    {

    }

    public function deleteSeminar()
    {

    }
}