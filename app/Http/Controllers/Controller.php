<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function formatValidationErrors(Validator $validator)
    {
        $errors = $validator->errors()->getMessages();
        $obj = $validator->failed();
        $result = [];
        foreach ($obj as $input => $rules) {
            $i = 0;
            $error = [];
            foreach ($rules as $rule => $ruleInfo) {
                array_push($error, [
                    'error' => strtolower($rule) . '_rule_error',
                    'message' => $errors[$input][$i]
                ]);

                $i++;
            }

            $result[$input] = $error;
        }

        return ['error' => $result, 'message' => 'validation_failed'];
    }
}
