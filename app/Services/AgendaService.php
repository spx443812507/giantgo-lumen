<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/7
 * Time: 下午10:23
 */

namespace App\Services;

use App\Models\Agenda;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AgendaService
{
    protected $seminarService;

    protected $attributeService;

    public function __construct(SeminarService $seminarService, AttributeService $attributeService)
    {
        $this->seminarService = $seminarService;

        $this->attribtueService = $attributeService;
    }

    public function getAgenda($seminarId, $agendaId, $includeAttributes = false)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $agenda = Agenda::find($agendaId);

        $entityTypeId = $agenda->entity_type_id;

        if (empty($agenda)) {
            throw new Exception('agenda_not_exists');
        }

        if ($agenda->seminar_id !== $seminar->id) {
            throw new Exception('agenda_not_belong_to_seminar');
        }

        if (!!$includeAttributes && !empty($entityTypeId)) {
            $agenda->attributes = $this->attributeService->getAttributeList($entityTypeId);
        }

        return $agenda;
    }

    public function getAgendaList($seminarId)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $agendas = $seminar->agendas()->get();

        return $agendas;
    }

    public function createAgenda($seminarId, $agendaInfo)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $validators = [
            'title' => 'required|max:255',
            'start_at' => [
                'required',
                'date',
                'date_format:' . DateTime::ATOM,
                'after_or_equal:' . $seminar->start_at,
                'before:' . $seminar->end_at
            ],
            'end_at' => [
                'required',
                'date',
                'date_format:' . DateTime::ATOM,
                'after:start_at'
            ]
        ];

        $validator = Validator::make($agendaInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $agenda = new Agenda($agendaInfo);
            $seminar->agendas()->save($agenda);
        } catch (Exception $e) {
            throw new Exception('create_agenda_fail');
        }

        return $agenda;
    }

    public function updateAgenda($seminarId, $agendaId, $agendaInfo)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $agenda = $this->getAgenda($seminarId, $agendaId);

        if (!empty($agendaInfo['entity_type_id'])) {
            $agenda->entity_type_id = $agendaInfo['entity_type_id'];
        }

        $validators = array_merge([
            'title' => 'required|max:255',
            'start_at' => [
                'required',
                'date',
                'date_format:' . DateTime::ATOM,
                'after_or_equal:' . $seminar->start_at,
                'before:' . $seminar->end_at
            ],
            'end_at' => [
                'required',
                'date',
                'date_format:' . DateTime::ATOM,
                'after:start_at'
            ]
        ], $agenda->makeValidators(array_keys($agendaInfo)));

        $validator = Validator::make($agendaInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $agenda->fill($agendaInfo)->save();
        } catch (Exception $exception) {
            throw new Exception('update_seminar_fail');
        }

        return $agenda;
    }

    public function deleteAgenda($seminarId, $agendaId)
    {
        $agenda = $this->getAgenda($seminarId, $agendaId);

        return $agenda->delete();
    }

    public function getAgendaDaysList($seminarId)
    {
        $days = [];

        $agendas = $this->getAgendaList($seminarId);

        $seminar = $this->seminarService->getSeminar($seminarId);

        $startDay = $seminar->start_at->startOfDay();

        $diff = $seminar->start_at->diffInDays($seminar->end_at);

        for ($i = -1; $i < $diff; $i++) {
            $date = $startDay->addDay()->format(DateTime::ATOM);
            $days[$date] = [
                'date' => $date,
                'agendas' => []
            ];
        }

        foreach ($agendas as $agenda) {
            $agendaStartDay = $agenda->start_at->startOfDay()->format(DateTime::ATOM);

            if (array_has($days, $agendaStartDay)) {
                $days[$agendaStartDay]['agendas'][] = $agenda;
            }
        }

        return array_pluck($days, null);
    }

    public function getAgendaSpeakerList($seminarId, $agendaId)
    {
        $agenda = $this->getAgenda($seminarId, $agendaId);

        $speakers = $agenda->speakers()->all();

        return $speakers;
    }

    public function deleteAgendaSpeaker($agendaId, $speakerId)
    {

    }
}