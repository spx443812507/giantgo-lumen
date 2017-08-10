<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/7/31
 * Time: 下午10:42
 */

namespace App\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait RestExceptionHandlerTrait
{
    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForException(Request $request, Exception $e)
    {
        switch (true) {
            case ($e instanceof NotFoundHttpException):
                $response = $this->httpNotFound();
                break;
            case ($e instanceof ModelNotFoundException):
                $response = $this->modelNotFound();
                break;
            case ($e instanceof ValidationException):
                $response = $this->validationFail($e->validator);
                break;
            default:
                $response = $this->badRequest($e->getMessage());
        }

        return $response;
    }

    /**
     * Returns json response for generic bad request.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest($message = 'bad_request', $statusCode = 400)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound($message = 'model_not_found', $statusCode = 404)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    protected function httpNotFound($message = 'route_not_found', $statusCode = 404)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    protected function validationFail(Validator $validator)
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

        return $this->jsonResponse(['error' => $result], 422);
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload = null, $statusCode = 404)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }
}