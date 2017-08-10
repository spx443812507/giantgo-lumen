<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/10
 * Time: 上午10:22
 */

namespace App\Http\Controllers;

use App\Services\SpeakerService;
use Illuminate\Http\Request;

class SpeakerController extends Controller
{
    protected $speakerService;

    public function __construct(SpeakerService $speakerService)
    {
        $this->speakerService = $speakerService;
    }

    public function getSpeaker(Request $request, $seminarId, $agendaId)
    {

    }

    public function getSpeakerList(Request $request, $seminarId)
    {
        $perPage = $request->input('per_page');

    }

    public function createSpeaker(Request $request, $seminarId)
    {
        $agendaInfo = $request->all();

    }

    public function updateSpeaker(Request $request, $seminarId, $agendaId)
    {

    }

    public function deleteSpeaker(Request $request, $seminarId, $agendaId)
    {
       
    }
}