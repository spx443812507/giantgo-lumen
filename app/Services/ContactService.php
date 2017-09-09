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
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ContactService
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attribtueService = $attributeService;
    }

    public function bindSocialAccountByVerify($contact, $verify)
    {
        try {
            $payload = JWT::decode($verify, env('JWT_SECRET'), array('HS256'));
        } catch (ExpiredException $e) {
            throw new Exception('token_expired');
        } catch (SignatureInvalidException $e) {
            throw new Exception('token_invalid');
        }

        $socialAccount = SocialAccount::find($payload->sub)->first();

        if (!empty($socialAccount)) {
            $contact->socialAccounts()->save($socialAccount);
        }

        return $contact;
    }

    public function bindSocialAccount(Contact $contact, SocialAccount $socialAccount)
    {
        if (!empty($socialAccount)) {
            $contact->socialAccounts()->save($socialAccount);
        }

        return $contact;
    }

    public function getContact($contactId, $includeAttributes = false)
    {
        $contact = Contact::find($contactId);

        $entityTypeId = $contact->entity_type_id;

        if (empty($contact)) {
            throw new Exception('contact_not_exists');
        }

        if ($includeAttributes && !empty($entityTypeId)) {
            $contact->attributes = $this->attributeService->getAttributeList($entityTypeId);
        }

        return $contact;
    }

    public function getContactList($perPage = null)
    {
        $contacts = Contact::paginate($perPage);

        return $contacts;
    }

    public function createContact($contactInfo, $verify)
    {
        $contact = new Contact;

        if (!empty($contactInfo['entity_type_id'])) {
            $contact->entity_type_id = $contactInfo['entity_type_id'];
        }

        $messages = [];

        $validators = array_merge([
            'email' => 'required_without:mobile|email|max:255|unique:contacts,email',
            'mobile' => 'max:255|unique:contacts,mobile',
            'password' => 'required'
        ], $contact->makeValidators(array_keys($contactInfo), $messages));

        $validator = Validator::make($contactInfo, $validators, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $contact->fill($contactInfo)->save();

            if (!empty($verify)) {
                $contact = $this->bindSocialAccount($contact, $verify);
            }
        } catch (Exception $exception) {
            throw new Exception('create_contact_fail');
        }

        return $contact;
    }

    public function updateContact($contactId, $contactInfo, $verify = null)
    {
        $contact = $this->getContact($contactId);

        if (!empty($contactInfo['entity_type_id'])) {
            $contact->entity_type_id = $contactInfo['entity_type_id'];
        }

        $validator = Validator::make($contactInfo, $contact->makeValidators(array_keys($contactInfo)));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $contact->fill($contactInfo)->save();

            if (!empty($verify)) {
                $contact = $this->bindSocialAccount($contact, $verify);
            }
        } catch (Exception $exception) {
            throw new Exception('update_contact_fail');
        }

        return $contact;
    }
}