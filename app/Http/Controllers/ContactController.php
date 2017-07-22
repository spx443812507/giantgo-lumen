<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/13
 * Time: 下午8:44
 */

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\SocialAccount;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ContactController extends Controller
{
    private function bindSocialAccount($contact, $token)
    {
        try {
            $payload = JWT::decode($token, env('JWT_SECRET'), array('HS256'));
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (SignatureInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        }

        $socialAccount = SocialAccount::find($payload->sub)->first();

        if (!empty($oAuthContact)) {
            $contact->socialAccounts()->save($socialAccount);
        }
    }

    public function signUp(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|max:255|unique:contacts,email',
            'mobile' => 'max:255|unique:contacts,mobile',
            'password' => 'required'
        ]);

        $contactInfo = $request->all();

        try {
            $contact = new Contact;

            $contact->fill($contactInfo)->save();
        } catch (Exception $exception) {
            return response()->json(['error' => 'create_contact_fail'], 500);
        }

        if (!empty($request->input('verify'))) {
            $this->bindSocialAccount($contact, $request->input('verify'));
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

        $user = Auth::guard('api')->user();

        $user['last_login'] = new \DateTime();

        $user->save();

        if (!empty($request->input('verify'))) {
            $this->bindSocialAccount($user, $request->input('verify'));
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
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $contactInfo = $request->except('id', 'password');

        $contact = Auth::guard('api')->user();

        if (!$contact) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $validator = Validator::make($contactInfo, [
            'email' => [
                Rule::unique('contacts')->ignore($contact->id),
            ],
            'mobile' => [
                Rule::unique('contacts')->ignore($contact->id),
            ]
        ]);

        if ($validator->fails()) {
            return response()->json($this->formatValidationErrors($validator), 422);
        }

        if (!empty($contact->entity_type_id)) {
            $contact->bootEntityAttribute($contact->entity_type_id);
        }

        $contact->fill($contactInfo);

        $contact->save();

        return $contact;
    }

    public function get(Request $request, $contactId)
    {
        $contact = Contact::find($contactId);

        if (empty($contact)) {
            return response()->json(['error' => 'contact_not_exists'], 500);
        }

        if (!empty($contact->entity_type_id)) {
            $contact->bootEntityAttribute($contact->entity_type_id);
        }

        return response()->json($contact);
    }

    public function getList(Request $request)
    {
        $contacts = Contact::all();

        return response()->json($contacts);
    }
}