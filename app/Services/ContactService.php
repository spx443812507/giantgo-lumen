<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/31
 * Time: 下午8:43
 */

namespace App\Services;

use App\Models\Contact;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

use App\Models\SocialAccount;


class ContactService
{
    public function bindSocialAccount($contact, $verify)
    {
        try {
            $payload = JWT::decode($verify, env('JWT_SECRET'), array('HS256'));
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (SignatureInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        }

        $socialAccount = SocialAccount::find($payload->sub)->first();

        if (!empty($oAuthContact)) {
            $contact->socialAccounts()->save($socialAccount);
        }

        return $contact;
    }

    public function createContact($contactInfo, $verify)
    {
        try {
            $contact = new Contact;

            $contact->fill($contactInfo)->save();

            if (!empty($verify)) {
                $contact = $this->bindSocialAccount($contact, $verify);
            }
        } catch (Exception $exception) {
            return response()->json(['error' => 'create_contact_fail'], 500);
        }

        return $contact;
    }

    public function updateContact($contactInfo)
    {
        $contactAttributes = array_keys($contactInfo);


    }

    public function getContact($contactId)
    {
        $contact = Contact::find($contactId);

        if (empty($contact)) {
            return response()->json(['error' => 'contact_not_exists'], 500);
        }

        if (!empty($contact->entity_type_id)) {
            $contact->bootEntityAttribute($contact->entity_type_id);
        }

        return $contact;
    }

    public function getContactList($perPage)
    {
        $contacts = Contact::paginate($perPage);

        return $contacts;
    }
}