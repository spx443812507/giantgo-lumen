<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/7
 * Time: 下午10:23
 */

namespace App\Services;

use App\Models\Speaker;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SpeakerService
{
    protected $seminarService;

    protected $agendaService;

    protected $attributeService;

    public function __construct(SeminarService $seminarService, AgendaService $agendaService, AttributeService $attributeService)
    {
        $this->seminarService = $seminarService;

        $this->agendaService = $agendaService;

        $this->attributeService = $attributeService;
    }

    public function getSpeaker($seminarId, $speakerId, $includeAttributes = false)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $speaker = Speaker::find($speakerId);

        if (empty($speaker)) {
            throw new Exception('speaker_not_exists');
        }

        if ($speaker->seminar_id !== $seminar->id) {
            throw new Exception('speaker_not_belong_to_seminar');
        }

        $entityTypeId = $speaker->entity_type_id;

        if (!!$includeAttributes && !empty($entityTypeId)) {
            $speaker->attributes = $this->attributeService->getAttributeList($entityTypeId);
        }

        return $speaker;
    }

    public function getSpeakerList($seminarId, $perPage = null, $name = null)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $speakers = $seminar->speakers();

        if (!is_null($name)) {
            $speakers->where('name', 'like', '%' . $name . '%');
        }

        $speakers = $speakers->paginate($perPage);

        return $speakers;
    }

    public function searchSpeakerList($perPage = null, $seminarId = null, $agendaId = null, $name = null)
    {
        $query = Speaker::query();

        if ($seminarId) {
            $query->where('seminar_id', $seminarId);
        }

        if ($agendaId) {
            $query->whereHas('agendas', function ($query) use ($agendaId) {
                $query->where('id', '=', $agendaId);
            });
        }

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if (empty($perPage)) {
            $perPage = 100;
        } else if ($perPage > 1000) {
            $perPage = 1000;
        }

        $speakers = $query->paginate($perPage);

        return $speakers;
    }

    public function createSpeaker($seminarId, $speakerInfo)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $validators = [
            'name' => 'required|max:255',
            'email' => 'email|max:255',
            'mobile' => 'max:255',
            'company' => 'max:255',
            'position' => 'max:255'
        ];

        $validator = Validator::make($speakerInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $speaker = new Speaker($speakerInfo);
            $seminar->speakers()->save($speaker);
        } catch (Exception $e) {
            throw new Exception('create_speaker_fail');
        }

        return $speaker;
    }

    public function updateSpeaker($seminarId, $speakerId, $speakerInfo)
    {
        $speaker = $this->getSpeaker($seminarId, $speakerId);

        if (!empty($speakerInfo['entity_type_id'])) {
            $speaker->entity_type_id = $speakerInfo['entity_type_id'];
        }

        $validators = array_merge([
            'name' => 'required|max:255',
            'email' => 'email|max:255',
            'mobile' => 'max:255',
            'company' => 'max:255',
            'position' => 'max:255'
        ], $speaker->makeValidators(array_keys($speakerInfo)));

        $validator = Validator::make($speakerInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $speaker->fill($speakerInfo)->save();
        } catch (Exception $exception) {
            throw new Exception('update_speaker_fail');
        }

        return $speaker;
    }

    public function deleteSpeaker($seminarId, $speakerId)
    {
        $speaker = $this->getSpeaker($seminarId, $speakerId);

        return $speaker->delete();
    }

    public function getAgendaSpeakers($seminarId, $agendaId)
    {
        $agenda = $this->agendaService->getAgenda($seminarId, $agendaId);

        return $agenda->speakers()->get();
    }

    public function attachAgendaSpeakers($seminarId, $agendaId, $speakerIds)
    {
        $agenda = $this->agendaService->getAgenda($seminarId, $agendaId);

        $seminar = $this->seminarService->getSeminar($seminarId);

        $seminarSpeakers = $seminar->speakers()->get()->pluck('id')->toArray();

        $validator = Validator::make($speakerIds, [
            '*' => Rule::in($seminarSpeakers)
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $agenda->speakers()->sync($speakerIds);
        } catch (Exception $e) {
            throw new Exception('attach_agenda_speaker_fail');
        }

        return $this->getAgendaSpeakers($seminarId, $agendaId);
    }
}