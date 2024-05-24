<?php

namespace App\Repositories;

use App\Interfaces\ServerAddressLookupRepositoryInterface;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;

class ServerAddressLookupRepository implements ServerAddressLookupRepositoryInterface
{
    protected array $urls = [
        "aHR0cDovL2NoZWNraXAuYW1hem9uYXdzLmNvbQ==",
        "aHR0cDovL2lmY29uZmlnLm1lL2lw"
    ];

    const ERROR_STATUS_CODE = 'Error: Unexpected status code in response.';
    const ERROR_INVALID_IP = 'Error: Invalid IP in response.';
    const ERROR_CONNECTION = "Error: Couldn't establish connection.";

    public function get(): string
    {
        $url = $this->urls[array_rand($this->urls)];
        $url = base64_decode($url);

        try {
            $response = Http::get($url);
        } catch (HttpClientException) {
            throw new \Exception(self::ERROR_CONNECTION);
        }

        if (!$response->ok()) {
            throw new \Exception(self::ERROR_STATUS_CODE);
        }

        $ip = trim($response->body());
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception(self::ERROR_INVALID_IP);
        }

        return $ip;
    }
}
