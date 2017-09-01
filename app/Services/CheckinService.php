<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/31
 * Time: 下午10:55
 */

namespace App\Services;

use App\Models\Checkin;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CheckinService
{
    protected $seminarService;

    protected $attributeService;

    public function __construct(SeminarService $seminarService, AttributeService $attributeService)
    {
        $this->seminarService = $seminarService;

        $this->attribtueService = $attributeService;
    }

    public function getCheckin($seminarId, $checkinId)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $checkin = Checkin::find($checkinId);

        if (empty($checkin)) {
            throw new Exception('checkin_not_exists');
        }

        if ($checkin->seminar_id !== $seminar->id) {
            throw new Exception('checkin_not_belong_to_seminar');
        }

        return $checkin;
    }

    public function getCheckinList($seminarId)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $checkins = $seminar->checkins()->get();

        return $checkins;
    }

    public function createCheckin($seminarId, $checkinInfo)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $validators = [
            'title' => 'required|max:255',
            'staff_name' => 'max:255',
            'staff_mobile' => 'max:255'
        ];

        $validator = Validator::make($checkinInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $checkin = new Checkin($checkinInfo);
            $seminar->checkins()->save($checkin);
        } catch (Exception $e) {
            throw new Exception('create_checkin_fail');
        }

        return $checkin;
    }

    public function updateCheckin($seminarId, $checkinId, $checkinInfo)
    {
        $checkin = $this->getCheckin($seminarId, $checkinId);

        $validators = [
            'title' => 'required|max:255',
            'staff_name' => 'max:255',
            'staff_mobile' => 'max:255'
        ];

        $validator = Validator::make($checkinInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $checkin->fill($checkinInfo)->save();
        } catch (Exception $exception) {
            throw new Exception('update_checkin_fail');
        }

        return $checkin;
    }

    public function deleteCheckin($seminarId, $checkinId)
    {
        $checkin = $this->getCheckin($seminarId, $checkinId);

        return $checkin->delete();
    }
}