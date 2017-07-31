<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/13
 * Time: 下午8:44
 */

namespace App\Http\Controllers;

use App\Services\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function signUp(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|max:255|unique:contacts,email',
            'mobile' => 'max:255|unique:contacts,mobile',
            'password' => 'required'
        ]);

        $contact = $this->contactService->createContact($request->all(), $request->input('verify'));

        $token = Auth::guard('api')->fromUser($contact);

        return response()->json(compact('token'), 201);
    }

    public function signIn(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'email' => 'email|required_without:mobile'
        ]);

        $email = $request->input('email');
        $mobile = $request->input('mobile');

        $credential = [
            'password' => $request->input('password')
        ];

        if (isset($email)) {
            $credential['email'] = $request->input('email');
        } else if (isset($mobile)) {
            $credential['mobile'] = $request->input('mobile');
        }

        if (!$token = Auth::guard('api')->attempt($credential)) {
            return response()->json(['error' => 'username_or_password_error'], 404);
        }

        $contact = Auth::guard('api')->user();

        $contact['last_login'] = new \DateTime();

        $contact->save();

        $verify = $request->input('verify');

        if (!empty($verify)) {
            $this->bindSocialAccount($contact, $verify);
        }

        return response()->json(compact('token'));
    }

    public function me()
    {
        try {
            $contact = Auth::guard('api')->user();

            if (empty($contact)) {
                return response()->json(['error' => 'unauthorized'], 401);
            }

            if (!empty($contact->entity_type_id)) {
                $contact->bootEntityAttribute($contact->entity_type_id);
            }
        } catch (Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        } catch (Exceptions\JWTException $e) {
            return response()->json(['error' => 'token_absent'], 500);
        }

        return response()->json($contact);
    }

    public function updateMyInfo(Request $request)
    {
        $contactInfo = $request->except('id', 'password', 'email', 'mobile');

        $contact = Auth::guard('api')->user();

        if (!$contact) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        if (!empty($contact->entity_type_id)) {
            $contact->bootEntityAttribute($contact->entity_type_id);
        }

        $validator = Validator::make($contactInfo, $this->generateValidators($contact, array_keys($contactInfo)));

        if ($validator->fails()) {
            return response()->json($this->formatValidationErrors($validator), 422);
        }

        $contact->fill($contactInfo);

        $contact->save();

        return $contact;
    }

    public function get(Request $request, $contactId)
    {
        $contact = $this->contactService->getContact($contactId);

        return response()->json($contact);
    }

    public function getList(Request $request)
    {
        $perPage = $request->input('per_page');

        $contacts = $this->contactService->getContactList($perPage);

        return response()->json($contacts);
    }
}