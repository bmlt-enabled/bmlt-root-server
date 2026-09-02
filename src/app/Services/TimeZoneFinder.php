<?php

namespace App\Services;

use Galahad\TimezoneMapper\TimezoneMapper;

/**
 * Resolves an IANA time zone from a latitude/longitude, entirely offline and in
 * memory, via glhd/laravel-timezone-mapper (a PHP port of LatLongToTimezone).
 *
 * The zone boundaries are simplified polygons baked into PHP, so a lookup is a
 * fast in-memory point-in-polygon walk with no data files loaded at request time
 * beyond the one-time polygon table. This trades exactness near some zone borders
 * for speed. The underlying mapper covers oceans with nautical zones, so it
 * effectively always returns a zone for in-range coordinates.
 *
 * Registered as a singleton so the polygon table initializes once per process.
 */
class TimeZoneFinder
{
    public function __construct(private TimezoneMapper $mapper)
    {
    }

    public function find(float $lat, float $lon): ?string
    {
        if (is_nan($lat) || $lat > 90 || $lat < -90) {
            return null;
        }
        if (is_nan($lon) || $lon > 180 || $lon < -180) {
            return null;
        }

        return $this->mapper->mapCoordinates($lat, $lon);
    }
}
