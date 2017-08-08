<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/10
 * Time: 下午9:11
 */

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\User;
use App\Services\SeminarService;
use Carbon\Carbon;
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
            $seminar = $this->seminarService->getSeminar($seminarId);
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
        $seminarInfo = $request->all();

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
        $entityTypeId = $request->input('entity_type_id');

        $this->validate($request, [
            'title' => 'required|max:255',
            'start_at' => 'required|date|after_or_equal:' . Carbon::now(),
            'end_at' => 'required|date|after:start_at',
        ]);

        $seminarClass = empty($entityTypeId) ? Seminar::class : Entity::getEntity($entityTypeId);

        $seminarInfo = $request->except('id');

        $seminar = $seminarClass::find($seminarId);

        if (empty($seminar)) {
            return response()->json(['error' => 'seminar_not_exists'], 500);
        }

        $seminar->fill($seminarInfo);

        $seminar->save();

        return response()->json($seminar);
    }
}