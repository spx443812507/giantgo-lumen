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

    public function __construct(SeminarService $seminarService)
    {
        $this->seminarService = $seminarService;
    }

    public function getAgenda($seminarId, $agendaId)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $agenda = Agenda::find($agendaId);

        if (empty($agenda)) {
            throw new Exception('agenda_not_exists');
        }

        if ($agenda->seminar_id !== $seminar->id) {
            throw new Exception('agenda_not_belong_to_seminar');
        }

        if (!empty($agenda->entity_type_id)) {
            $agenda->bootEntityAttribute($agenda->entity_type_id);
        }

        return $agenda;
    }

    public function getAgendaList($seminarId, $perPage)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $agendas = $seminar->agendas()->paginate($perPage);

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