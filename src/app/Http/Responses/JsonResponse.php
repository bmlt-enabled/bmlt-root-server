<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{
    public function __construct($data = null, $status = 200, $headers = [], $options = 0, $json = false)
    {
        $options |= JSON_UNESCAPED_SLASHES;
        parent::__construct($data, $status, $headers, $options, $json);
    }
}
