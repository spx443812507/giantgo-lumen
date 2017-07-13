<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/10
 * Time: 下午9:11
 */

namespace App\Http\Controllers;

use App\Models\EAV\Factories\EntityFactory;
use App\Models\Seminar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller
{
    public function getSeminar(Request $request, $seminarId)
    {
        $entityTypeId = $request->input('entity_type_id');

        $seminarClass = empty($entityTypeId) ? Seminar::class : EntityFactory::getEntity($entityTypeId);

        $seminar = $seminarClass::find($seminarId);

        if (empty($seminar)) {
            return response()->json(['error' => 'seminar_not_exists'], 500);
        }

        return response()->json($seminar);
    }

    public function getSeminarList(Request $request)
    {

    }

    public function createSeminar(Request $request)
    {
        $entityTypeId = $request->input('entity_type_id');

        $this->validate($request, [
            'title' => 'required|max:255',
            'start_date' => 'required|date|after_or_equal:' . Carbon::now(),
            'end_date' => 'required|date|after:start_date',
        ]);

        $seminarClass = empty($entityTypeId) ? Seminar::class : EntityFactory::getEntity($entityTypeId);

        $seminarInfo = $request->all();

        try {
            $seminar = new $seminarClass;
            $seminar->fill($seminarInfo);
            $seminar->user_id = Auth::user()->id;
            $seminar->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'create_seminar_fail'], 500);
        }

        return response()->json($seminar, 201);
    }

    public function updateSeminar(Request $request, $seminarId)
    {
        $entityTypeId = $request->input('entity_type_id');

        $this->validate($request, [
            'title' => 'required|max:255',
            'start_date' => 'required|date|after_or_equal:' . Carbon::now(),
            'end_date' => 'required|date|after:start_date',
        ]);

        $seminarClass = empty($entityTypeId) ? Seminar::class : EntityFactory::getEntity($entityTypeId);

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