<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/31
 * Time: 下午10:55
 */

namespace App\Services;

use App\Models\Checkin;
use DateTime;
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

        $agendas = $seminar->checkins()->get();

        return $agendas;
    }
}