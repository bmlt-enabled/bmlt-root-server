<?php

namespace App\Aggregator;

use App\Repositories\External\ExternalMeeting;
use DateTimeZone;

/**
 * Fork addition: fills in a meeting's IANA time zone when the source root server
 * provides none. Pure PHP -- no extensions or external data. Uses the bundled
 * tzdata (via DateTimeZone::getLocation) to find the zone whose representative
 * coordinates are nearest the meeting. Accuracy is city-level, which can pick a
 * neighboring zone right on a border; that is an accepted trade-off here.
 */
class TimezoneResolver
{
    /** @var array<string, string|null> memoized results keyed by rounded coords */
    private static array $cache = [];

    /** @var list<array{tz: string, lat: float, lon: float}>|null */
    private static ?array $zones = null;

    public function __construct(private readonly ?AddressGeocoder $geocoder = null)
    {
    }

    public static function reset(): void
    {
        self::$cache = [];
    }

    public function resolve(ExternalMeeting $meeting): ?string
    {
        $lat = $meeting->latitude;
        $lon = $meeting->longitude;

        if (is_null($lat) || is_null($lon) || ($lat == 0.0 && $lon == 0.0)) {
            if (config('aggregator.geocode_addresses') && !is_null($this->geocoder)) {
                $coords = $this->geocoder->geocode($this->addressFields($meeting));
                if (is_null($coords)) {
                    return null;
                }
                [$lat, $lon] = $coords;
            } else {
                return null;
            }
        }

        return $this->nearestZone((float) $lat, (float) $lon);
    }

    private function nearestZone(float $lat, float $lon): ?string
    {
        $key = round($lat, 3) . ',' . round($lon, 3);
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $best = null;
        $bestDist = INF;
        foreach ($this->zones() as $zone) {
            $dist = $this->haversine($lat, $lon, $zone['lat'], $zone['lon']);
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = $zone['tz'];
            }
        }

        return self::$cache[$key] = $best;
    }

    /**
     * Every IANA zone that has representative coordinates in the tzdata (zone.tab).
     * Etc/* and UTC-style zones have no location and are skipped.
     *
     * @return list<array{tz: string, lat: float, lon: float}>
     */
    private function zones(): array
    {
        if (!is_null(self::$zones)) {
            return self::$zones;
        }

        $zones = [];
        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $loc = (new DateTimeZone($tz))->getLocation();
            if ($loc === false) {
                continue;
            }
            $zones[] = ['tz' => $tz, 'lat' => (float) $loc['latitude'], 'lon' => (float) $loc['longitude']];
        }

        return self::$zones = $zones;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * asin(min(1.0, sqrt($a)));
    }

    /** @return list<string> */
    private function addressFields(ExternalMeeting $meeting): array
    {
        return array_values(array_filter([
            $meeting->locationStreet,
            $meeting->locationMunicipality,
            $meeting->locationProvince,
            $meeting->locationPostalCode1,
            $meeting->locationNation,
        ], fn ($v) => !is_null($v) && trim($v) !== ''));
    }
}
