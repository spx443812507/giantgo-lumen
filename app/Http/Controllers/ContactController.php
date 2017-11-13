<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/13
 * Time: 下午8:44
 */

namespace App\Http\Controllers;

use App\Services\ContactService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions;

class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function signUp(Request $request)
    {
        $contactInfo = $request->all();

        $verify = $request->input('verify');

        try {
            $contact = $this->contactService->createContact($contactInfo, $verify);
        } catch (Exception $e) {
            throw $e;
        }

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
            $this->contactService->bindSocialAccountByVerify($contact, $verify);
        }

        return response()->json(compact('token'));
    }

    public function me()
    {
        try {
            $contact = Auth::guard('api')->user();

            if (!$contact) {
                return response()->json(['error' => 'unauthorized'], 401);
            }

            $contact = $this->contactService->getContact($contact->id, true);
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

        $contact = $this->contactService->updateContact($contact->id, $contactInfo);

        return $contact;
    }

    public function get(Request $request, $contactId)
    {
        try {
            $contact = $this->contactService->getContact($contactId);
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($contact);
    }

    public function getList(Request $request)
    {
        try {
            $contacts = $this->contactService->getContactList($request->input('per_page'));
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json($contacts);
    }

    public function createSeminarContact(Request $request, $seminarId)
    {
        $contactInfo = $request->all();

        $verify = $request->input('verify');

        try {
            $contact = $this->contactService->createSeminarContact($contactInfo, $seminarId, $verify);
        } catch (Exception $e) {
            throw $e;
        }

        return $contact;
    }

    public function registerSeminarContact(Request $request, $seminarId)
    {
        try {
            $contact = Auth::guard('api')->user();

            if (!$contact) {
                return response()->json(['error' => 'unauthorized'], 401);
            }

            $contact = $this->contactService->getContact($contact->id);

            $this->contactService->registerSeminarContact($seminarId, $contact->id);

            return response()->json(null, 204);
        } catch (Exception $e) {
            throw $e;
        }
    }
}