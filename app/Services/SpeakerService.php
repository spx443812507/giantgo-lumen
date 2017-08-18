<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/7
 * Time: 下午10:23
 */

namespace App\Services;

use App\Models\Speaker;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SpeakerService
{
    protected $seminarService;

    protected $attributeService;

    public function __construct(SeminarService $seminarService, AttributeService $attributeService)
    {
        $this->seminarService = $seminarService;

        $this->attribtueService = $attributeService;
    }

    public function getSpeaker($seminarId, $speakerId, $includeAttributes = false)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $speaker = Speaker::find($speakerId);

        $entityTypeId = $speaker->entity_type_id;

        if (empty($speaker)) {
            throw new Exception('speaker_not_exists');
        }

        if ($speaker->seminar_id !== $seminar->id) {
            throw new Exception('speaker_not_belong_to_seminar');
        }

        if (!!$includeAttributes && !empty($entityTypeId)) {
            $speaker->attributes = $this->attributeService->getAttributeList($entityTypeId);
        }

        return $speaker;
    }

    public function getSpeakerList($seminarId, $perPage)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $speakers = $seminar->speakers()->paginate($perPage);

        return $speakers;
    }

    public function createSpeaker($seminarId, $speakerInfo)
    {
        $seminar = $this->seminarService->getSeminar($seminarId);

        $validators = [
            'name' => 'required|max:255',
            'email' => 'email|max:255',
            'mobile' => 'max:255',
            'company' => 'max:255',
            'position' => 'max:255'
        ];

        $validator = Validator::make($speakerInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $speaker = new Speaker($speakerInfo);
            $seminar->speakers()->save($speaker);
        } catch (Exception $e) {
            throw new Exception('create_agenda_fail');
        }

        return $speaker;
    }

    public function updateSpeaker($seminarId, $speakerId, $speakerInfo)
    {
        $speaker = $this->getSpeaker($seminarId, $speakerId);

        if (!empty($speakerInfo['entity_type_id'])) {
            $speaker->entity_type_id = $speakerInfo['entity_type_id'];
        }

        $validators = array_merge([
            'name' => 'required|max:255',
            'email' => 'email|max:255',
            'mobile' => 'max:255',
            'company' => 'max:255',
            'position' => 'max:255'
        ], $speaker->makeValidators(array_keys($speakerInfo)));

        $validator = Validator::make($speakerInfo, $validators);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $speaker->fill($speakerInfo)->save();
        } catch (Exception $exception) {
            throw new Exception('update_speaker_fail');
        }

        return $speaker;
    }

    public function deleteSpeaker($seminarId, $speakerId)
    {
        $speaker = $this->getSpeaker($seminarId, $speakerId);

        return $speaker->delete();
    }
}