<?php

namespace App\Repositories;

use App\Geo\GeobufDecoder;
use App\Interfaces\TimeZoneRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Queries the world's IANA time zone boundaries by coordinate, entirely offline.
 *
 * PHP port of the browser lookup in resources/js/lib/timeZone/find.ts (itself from
 * kevmo314/browser-geo-tz), reading the same two files the frontend is served:
 *   - timezones-1970.geojson.index.json : a quadtree of the world; each leaf is either
 *       a list of exact-match time zone indices, a {pos,len} slice into the geobuf file
 *       to be point-in-polygon tested, or absent (open ocean).
 *   - timezones-1970.geojson.geo.dat    : concatenated geobuf blobs of boundary
 *       polygons, each carrying a `tzid` property.
 *
 * The Makefile downloads both into public/ and ships them in the release zip. They are
 * build artifacts rather than tracked files, so a missing dataset is a real possibility;
 * it is reported once and then treated as "no time zone", never as a fatal error.
 *
 * Registered as a singleton so the index and file handle load once per process.
 */
class TimeZoneRepository implements TimeZoneRepositoryInterface
{
    private const EDGE_LAT = 89.9999;
    private const EDGE_LON = 179.9999;

    private ?array $timezones = null;
    private ?array $lookup = null;
    /** @var resource|null */
    private $geoHandle = null;
    private bool $loadAttempted = false;
    private bool $isAvailable = false;

    public function getByCoordinates(float $latitude, float $longitude): ?string
    {
        if (is_nan($latitude) || $latitude > 90 || $latitude < -90) {
            return null;
        }
        if (is_nan($longitude) || $longitude > 180 || $longitude < -180) {
            return null;
        }

        if (!$this->ensureLoaded()) {
            return null;
        }

        // Clamp the edges of the world, matching the reference implementation.
        $latitude = max(-self::EDGE_LAT, min(self::EDGE_LAT, $latitude));
        $longitude = max(-self::EDGE_LON, min(self::EDGE_LON, $longitude));

        $quad = [
            'top' => self::EDGE_LAT,
            'bottom' => -self::EDGE_LAT,
            'left' => -self::EDGE_LON,
            'right' => self::EDGE_LON,
            'midLat' => 0.0,
            'midLon' => 0.0,
        ];

        $cur = $this->lookup;
        while (true) {
            if ($latitude >= $quad['midLat'] && $longitude >= $quad['midLon']) {
                $nextQuad = 'a';
                $quad['bottom'] = $quad['midLat'];
                $quad['left'] = $quad['midLon'];
            } elseif ($latitude >= $quad['midLat'] && $longitude < $quad['midLon']) {
                $nextQuad = 'b';
                $quad['bottom'] = $quad['midLat'];
                $quad['right'] = $quad['midLon'];
            } elseif ($latitude < $quad['midLat'] && $longitude < $quad['midLon']) {
                $nextQuad = 'c';
                $quad['top'] = $quad['midLat'];
                $quad['right'] = $quad['midLon'];
            } else {
                $nextQuad = 'd';
                $quad['top'] = $quad['midLat'];
                $quad['left'] = $quad['midLon'];
            }

            $cur = (is_array($cur) && array_key_exists($nextQuad, $cur)) ? $cur[$nextQuad] : null;

            if (is_null($cur)) {
                // No time zone in this quad: open ocean.
                return null;
            }
            if (is_array($cur) && isset($cur['pos']) && !empty($cur['len'])) {
                return $this->getFromBoundary((int)$cur['pos'], (int)$cur['len'], $latitude, $longitude);
            }
            if (is_array($cur) && array_is_list($cur) && count($cur) > 0) {
                // Exact match: the whole quad belongs to a single time zone.
                return $this->timezones[$cur[0]] ?? null;
            }
            if (!is_array($cur)) {
                throw new \RuntimeException('Unexpected time zone index node');
            }

            $quad['midLat'] = ($quad['top'] + $quad['bottom']) / 2;
            $quad['midLon'] = ($quad['left'] + $quad['right']) / 2;
        }
    }

    private function getFromBoundary(int $pos, int $len, float $latitude, float $longitude): ?string
    {
        $geoJson = (new GeobufDecoder())->decode($this->readSlice($pos, $len));
        $point = [$longitude, $latitude];

        $features = match ($geoJson['type'] ?? null) {
            'FeatureCollection' => $geoJson['features'] ?? [],
            'Feature' => [$geoJson],
            default => [],
        };

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? null;
            $tzid = $feature['properties']['tzid'] ?? null;
            if (!is_null($geometry) && !is_null($tzid) && $this->isPointInGeometry($point, $geometry)) {
                return $tzid;
            }
        }

        return null;
    }

    private function isPointInGeometry(array $point, array $geometry): bool
    {
        $coordinates = $geometry['coordinates'] ?? null;
        if (is_null($coordinates)) {
            return false;
        }

        $polygons = match ($geometry['type'] ?? null) {
            'Polygon' => [$coordinates],
            'MultiPolygon' => $coordinates,
            default => [],
        };

        foreach ($polygons as $polygon) {
            if ($this->isPointInRing($point, $polygon[0], false)) {
                $inHole = false;
                for ($i = 1; $i < count($polygon); $i++) {
                    if ($this->isPointInRing($point, $polygon[$i], true)) {
                        $inHole = true;
                        break;
                    }
                }
                if (!$inHole) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ray-casting point-in-ring test, matching @turf/boolean-point-in-polygon
     * (points ON the boundary count as inside unless $ignoreBoundary).
     */
    private function isPointInRing(array $point, array $ring, bool $ignoreBoundary): bool
    {
        $isInside = false;
        $n = count($ring);
        if ($n > 0 && $ring[0][0] === $ring[$n - 1][0] && $ring[0][1] === $ring[$n - 1][1]) {
            $n--;
        }

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0];
            $yi = $ring[$i][1];
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            $onBoundary = ($point[1] * ($xi - $xj) + $yi * ($xj - $point[0]) + $yj * ($point[0] - $xi)) === 0.0
                && ($xi - $point[0]) * ($xj - $point[0]) <= 0
                && ($yi - $point[1]) * ($yj - $point[1]) <= 0;
            if ($onBoundary) {
                return !$ignoreBoundary;
            }

            $intersect = (($yi > $point[1]) !== ($yj > $point[1]))
                && ($point[0] < ($xj - $xi) * ($point[1] - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $isInside = !$isInside;
            }
        }

        return $isInside;
    }

    private function readSlice(int $pos, int $len): string
    {
        fseek($this->geoHandle, $pos);
        $data = '';
        while (strlen($data) < $len) {
            $chunk = fread($this->geoHandle, $len - strlen($data));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $data .= $chunk;
        }
        return $data;
    }

    /**
     * Load the index and open the boundary file, at most once per process. A missing or
     * unreadable dataset is logged a single time and then reported as unavailable, so an
     * import degrades to "derive nothing" instead of failing or warning per lookup.
     */
    private function ensureLoaded(): bool
    {
        if ($this->loadAttempted) {
            return $this->isAvailable;
        }
        $this->loadAttempted = true;

        $indexPath = $this->indexPath();
        $geoDataPath = $this->geoDataPath();

        if (!is_readable($indexPath) || !is_readable($geoDataPath)) {
            Log::warning("Time zone boundary data is unavailable, so time zones cannot be looked up by coordinate. Expected $indexPath and $geoDataPath (the Makefile's timezone target downloads them).");
            return false;
        }

        $index = json_decode(file_get_contents($indexPath), true);
        if (!is_array($index) || !isset($index['timezones'], $index['lookup'])) {
            Log::warning("Time zone boundary index at $indexPath could not be parsed, so time zones cannot be looked up by coordinate.");
            return false;
        }

        $geoHandle = fopen($geoDataPath, 'rb');
        if ($geoHandle === false) {
            Log::warning("Time zone boundary data at $geoDataPath could not be opened, so time zones cannot be looked up by coordinate.");
            return false;
        }

        $this->timezones = $index['timezones'];
        $this->lookup = $index['lookup'];
        $this->geoHandle = $geoHandle;
        $this->isAvailable = true;
        return true;
    }

    private function indexPath(): string
    {
        return config('aggregator.timezone_index_path') ?: public_path('timezones-1970.geojson.index.json');
    }

    private function geoDataPath(): string
    {
        return config('aggregator.timezone_data_path') ?: public_path('timezones-1970.geojson.geo.dat');
    }
}
