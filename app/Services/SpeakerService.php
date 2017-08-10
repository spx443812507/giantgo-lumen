<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/7
 * Time: 下午10:23
 */

namespace App\Services;

use App\Models\Speaker;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SpeakerService
{
    protected $seminarService;

    public function __construct(SeminarService $seminarService)
    {
        $this->seminarService = $seminarService;
    }

    public function getSpeaker($speakerId)
    {
        $speaker = Speaker::find($speakerId);

        if (empty($speaker)) {
            throw new Exception('speaker_not_exists');
        }

        if (!empty($speaker->entity_type_id)) {
            $speaker->bootEntityAttribute($speaker->entity_type_id);
        }

        return $speaker;
    }

    public function getSpeakerList($seminarId, $perPage)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $speakers = $seminar->seapkers()->paginate($perPage);

        return $speakers;
    }

    public function createAgenda($seminarId, $agendaInfo)
    {

    }

    public function updateAgenda($seminarId, $agendaId, $agendaInfo)
    {

    }

    public function deleteAgenda($seminarId, $agendaId)
    {

    }
}