<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/10
 * Time: 上午10:22
 */

namespace App\Http\Controllers;

use App\Services\SpeakerService;
use Exception;
use Illuminate\Http\Request;

class SpeakerController extends Controller
{
    protected $speakerService;

    public function __construct(SpeakerService $speakerService)
    {
        $this->speakerService = $speakerService;
    }

    public function getSpeaker(Request $request, $seminarId, $speakerId)
    {
        $speaker = null;

        try {
            $speaker = $this->speakerService->getSpeaker($seminarId, $speakerId, true);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($speaker);
    }

    public function getSpeakerList(Request $request, $seminarId)
    {
        $name = $request->input('name');

        try {
            $speakers = $this->speakerService->getSpeakerList($seminarId, $name);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($speakers);
    }

    public function searchSpeakerList(Request $request)
    {
        $perPage = $request->input('per_page');
        $seminarId = $request->input('seminar_id');
        $agendaId = $request->input('agenda_id');
        $name = $request->input('name');

        try {
            $speakers = $this->speakerService->searchSpeakerList($perPage, $seminarId, $agendaId, $name);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($speakers);
    }

    public function createSpeaker(Request $request, $seminarId)
    {
        $speakerInfo = $request->all();

        try {
            $speaker = $this->speakerService->createSpeaker($seminarId, $speakerInfo);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($speaker, 201);
    }

    public function updateSpeaker(Request $request, $seminarId, $speakerId)
    {
        $speakerInfo = $request->except('id');

        try {
            $speaker = $this->speakerService->updateSpeaker($seminarId, $speakerId, $speakerInfo);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($speaker);
    }

    public function deleteSpeaker(Request $request, $seminarId, $speakerId)
    {
        try {
            $this->speakerService->deleteSpeaker($seminarId, $speakerId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(null, 204);
    }

    public function getAgendaSpeakers(Request $request, $seminarId, $agendaId)
    {
        try {
            $speakers = $this->speakerService->getAgendaSpeakers($seminarId, $agendaId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($speakers);
    }

    public function attachAgendaSpeakers(Request $request, $seminarId, $agendaId)
    {
        $speakerIds = $request->input('speakers');

        try {
            $speakers = $this->speakerService->attachAgendaSpeakers($seminarId, $agendaId, $speakerIds);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($speakers);
    }
}