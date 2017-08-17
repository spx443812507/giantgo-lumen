<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/10
 * Time: 下午9:11
 */

namespace App\Http\Controllers;

use App\Services\SeminarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class SeminarController extends Controller
{
    protected $seminarService;

    public function __construct(SeminarService $seminarService)
    {
        $this->seminarService = $seminarService;
    }

    public function getSeminar(Request $request, $seminarId)
    {
        $seminar = null;

        try {
            $seminar = $this->seminarService->getSeminar($seminarId, true);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($seminar);
    }

    public function getSeminarList(Request $request)
    {
        $perPage = $request->input('per_page');

        try {
            $seminars = $this->seminarService->getSeminarList($perPage);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($seminars);
    }

    public function createSeminar(Request $request)
    {
        $seminarInfo = $request->input('seminar');

        $user = Auth::user();

        try {
            $seminar = $this->seminarService->createSeminar($seminarInfo, $user);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($seminar, 201);
    }

    public function updateSeminar(Request $request, $seminarId)
    {
        $seminarInfo = $request->input('seminar');

        try {
            $seminar = $this->seminarService->updateSeminar($seminarId, $seminarInfo);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($seminar);
    }

    public function deleteSeminar(Request $request, $seminarId)
    {
        try {
            $this->seminarService->deleteSeminar($seminarId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(null, 204);
    }
}