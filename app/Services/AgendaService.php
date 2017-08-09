<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/7
 * Time: 下午10:23
 */

namespace App\Services;


use App\Models\Agenda;
use App\Models\Seminar;
use App\Models\User;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AgendaService
{
    public function getAgenda($agendaId)
    {
        $agenda = Agenda::find($agendaId);

        if (empty($agenda)) {
            throw new Exception('agenda_not_exists');
        }

        if (!empty($agenda->entity_type_id)) {
            $agenda->bootEntityAttribute($agenda->entity_type_id);
        }

        return $agenda;
    }

    public function getAgendaList($perPage)
    {
        $agendas = Agenda::paginate($perPage);

        return $agendas;
    }

    public function createAgenda(Seminar $seminar, $agendaInfo, User $user)
    {
        $validators = [
            'title' => 'required|max:255',
            'start_at' => [
                'required',
                'date',
                'date_format' => DateTime::ATOM,
                'after_or_equal' => $seminar->start_at,
                'before' => $seminar->end_at
            ],
            'end_at' => 'required|date|date_format:' . DateTime::ATOM . '|after:start_at',
        ];

        $validator = Validator::make($agendaInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $agenda = new Agenda($agendaInfo);
            $user->agendas()->save($agenda);
        } catch (Exception $e) {
            throw new Exception('create_agenda_fail');
        }

        return $seminar;
    }

    public function updateAgenda(Seminar $seminar, $agendaId, $agendaInfo)
    {
        $agenda = $this->getAgenda($agendaId);

        $validators = array_merge([
            'title' => 'required|max:255',
            'start_at' => [
                'required',
                'date',
                'date_format' => DateTime::ATOM,
                'after_or_equal' => $seminar->start_at,
                'before' => $seminar->end_at
            ],
            'end_at' => 'required|date|date_format:' . DateTime::ATOM . '|after:start_at',
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

    public function deleteAgenda($agendaId)
    {
        $agenda = $this->getAgenda($agendaId);

        return $agenda->delete();
    }
}