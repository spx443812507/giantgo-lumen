<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/13
 * Time: 下午8:44
 */

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\EAV\Factories\EntityFactory;
use App\Models\SocialAccount;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions;

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
        $entityTypeId = $request->input('entity_type_id');

        $uniqueRule = isset($entityTypeId) ? ',NULL,id,entity_type_id,' . $entityTypeId : '';

        $this->validate($request, [
            'email' => 'email|max:255|unique:contacts,email' . $uniqueRule,
            'mobile' => 'max:255|unique:contacts,mobile' . $uniqueRule,
            'password' => 'required'
        ]);

        $contactClass = empty($entityTypeId) ? Contact::class : EntityFactory::getEntity($entityTypeId);

        $contactInfo = $request->all();

        try {
            $contact = new $contactClass;

            $contact->fill($contactInfo);

            $contact->save();
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

        $entityTypeId = $request->input('entity_type_id');

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

        if (isset($entityTypeId)) {
            $credential['entity_type_id'] = $entityTypeId;
        }

        try {
            if (!$token = Auth::guard('api')->attempt($credential)) {
                return response()->json(['error' => 'username_or_password_error'], 404);
            }
        } catch (Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        } catch (Exceptions\JWTException $e) {
            return response()->json(['error' => 'token_absent'], 500);
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
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json(['error' => 'unauthorized'], 401);
            }
        } catch (Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        } catch (Exceptions\JWTException $e) {
            return response()->json(['error' => 'token_absent'], 500);
        }

        return response()->json($user);
    }

    public function get(Request $request, $contactId)
    {
        $contact = Contact::find($contactId);

        if (empty($contact)) {
            return response()->json(['error' => 'contact_not_exists'], 500);
        }

        return response()->json($contact);
    }
}