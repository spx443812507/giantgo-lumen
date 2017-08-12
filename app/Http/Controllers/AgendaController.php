<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/9
 * Time: 下午9:30
 */

namespace App\Http\Controllers;

use App\Services\AgendaService;
use Exception;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    protected $agendaService;

    public function __construct(AgendaService $agendaService)
    {
        $this->agendaService = $agendaService;
    }

    public function getAgenda(Request $request, $seminarId, $agendaId)
    {
        $agenda = null;

        try {
            $agenda = $this->agendaService->getAgenda($seminarId, $agendaId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($agenda);
    }

    public function getAgendaList(Request $request, $seminarId)
    {
        $perPage = $request->input('per_page');

        try {
            $agendas = $this->agendaService->getAgendaList($seminarId, $perPage);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($agendas);
    }

    public function createAgenda(Request $request, $seminarId)
    {
        $agendaInfo = $request->input('agenda');

        try {
            $agenda = $this->agendaService->createAgenda($seminarId, $agendaInfo);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($agenda, 201);
    }

    public function updateAgenda(Request $request, $seminarId, $agendaId)
    {
        $agendaInfo = $request->except('id');

        try {
            $agenda = $this->agendaService->updateAgenda($seminarId, $agendaId, $agendaInfo);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($agenda);
    }

    public function deleteAgenda(Request $request, $seminarId, $agendaId)
    {
        try {
            $this->agendaService->deleteAgenda($seminarId, $agendaId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(null, 204);
    }
}