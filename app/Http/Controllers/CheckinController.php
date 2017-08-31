<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/31
 * Time: 下午11:10
 */

namespace App\Http\Controllers;

use App\Services\CheckinService;
use Exception;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    protected $checkinService;

    public function __construct(CheckinService $checkinService)
    {
        $this->checkinService = $checkinService;
    }

    public function getCheckin(Request $request, $seminarId, $checkinId)
    {
        $checkin = null;

        try {
            $checkin = $this->checkinService->getCheckin($seminarId, $checkinId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($checkin);
    }

    public function getCheckinList(Request $request, $seminarId)
    {
        try {
            $checkins = $this->checkinService->getCheckinList($seminarId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($checkins);
    }
}