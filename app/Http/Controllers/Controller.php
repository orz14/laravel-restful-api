<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendResponse($data = [], $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => true,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response)->setStatusCode($statusCode);
    }

    public function sendError($message = [], $statusCode = 404): JsonResponse
    {
        $response = [
            'status' => false,
        ];

        if (!empty($message)) {
            $response['errors'] = $message;
        }

        return response()->json($response)->setStatusCode($statusCode);
    }
}
