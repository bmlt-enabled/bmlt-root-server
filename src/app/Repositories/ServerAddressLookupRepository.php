<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use App\Interfaces\ServerAddressLookupRepositoryInterface;

class ServerAddressLookupRepository implements ServerAddressLookupRepositoryInterface
{
    protected array $urls = [
        "aHR0cDovL2NoZWNraXAuYW1hem9uYXdzLmNvbQ==",
        "aHR0cDovL2lmY29uZmlnLm1lL2lw"
    ];

    private const ERROR_STATUS_CODE = 'Error: Unexpected status code in response.';
    private const ERROR_INVALID_IP = 'Error: Invalid IP in response.';

    public function get(): string
    {
        try {
            $url = base64_decode($this->urls[array_rand($this->urls)]);
            $response = Http::get($url);
            if (!$response->ok()) {
                return self::ERROR_STATUS_CODE;
            }

            $ip = trim($response->body());
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return self::ERROR_INVALID_IP;
            }

            return $ip;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
