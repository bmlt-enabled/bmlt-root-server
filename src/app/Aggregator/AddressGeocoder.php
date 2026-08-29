<?php

namespace App\Aggregator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Fork addition (optional, gated by aggregator.geocode_addresses): turns a
 * meeting's location fields into [lat, lon] so meetings with no coordinates can
 * still get a time zone. Results are cached by normalized address. Nominatim's
 * usage policy asks for <= 1 request/second and a descriptive User-Agent.
 */
class AddressGeocoder
{
    /**
     * @param  list<string>  $addressFields
     * @return array{0: float, 1: float}|null
     */
    public function geocode(array $addressFields): ?array
    {
        $query = trim(implode(', ', $addressFields));
        if ($query === '') {
            return null;
        }

        return Cache::rememberForever('aggregator:geocode:' . md5($query), function () use ($query) {
            try {
                $response = Http::withHeaders(['User-Agent' => config('aggregator.geocoder_user_agent')])
                    ->timeout(15)
                    ->get(config('aggregator.geocoder_endpoint'), [
                        'q' => $query,
                        'format' => 'json',
                        'limit' => 1,
                    ]);
            } catch (\Throwable) {
                return null;
            }

            if (!$response->successful()) {
                return null;
            }

            $first = $response->json()[0] ?? null;
            if (!isset($first['lat'], $first['lon'])) {
                return null;
            }

            return [(float) $first['lat'], (float) $first['lon']];
        });
    }
}
