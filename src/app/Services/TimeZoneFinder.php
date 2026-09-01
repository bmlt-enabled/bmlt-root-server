<?php

namespace App\Services;

/**
 * Resolves an IANA time zone from a latitude/longitude, entirely offline.
 *
 * PHP port of the browser lookup in resources/js/lib/timeZone/find.ts (from
 * kevmo314/browser-geo-tz). It reuses the same data shipped in public/:
 *   - timezones-1970.geojson.index.json : a quadtree of the world; each leaf is
 *       either a list of exact-match timezone indices, or a {pos,len} slice into
 *       the geobuf file to be point-in-polygon tested, or absent (open ocean).
 *   - timezones-1970.geojson.geo.dat    : concatenated geobuf blobs of timezone
 *       boundary polygons, each carrying a `tzid` property.
 *
 * Registered as a singleton so the index and file handle load once per process.
 * Returns null at sea / when no boundary contains the point (callers leave the
 * time zone unset rather than inventing an Etc/GMT offset).
 */
class TimeZoneFinder
{
    private const EDGE = 89.9999;
    private const EDGE_LON = 179.9999;

    private ?array $timezones = null;
    private ?array $lookup = null;
    /** @var resource|null */
    private $geoHandle = null;

    public function find(float $lat, float $lon): ?string
    {
        if (is_nan($lat) || $lat > 90 || $lat < -90) {
            return null;
        }
        if (is_nan($lon) || $lon > 180 || $lon < -180) {
            return null;
        }

        // Clamp the edges of the world, matching the reference implementation.
        $lat = max(-self::EDGE, min(self::EDGE, $lat));
        $lon = max(-self::EDGE_LON, min(self::EDGE_LON, $lon));

        $this->ensureLoaded();

        $quad = [
            'top' => self::EDGE,
            'bottom' => -self::EDGE,
            'left' => -self::EDGE_LON,
            'right' => self::EDGE_LON,
            'midLat' => 0.0,
            'midLon' => 0.0,
        ];

        $cur = $this->lookup;
        while (true) {
            if ($lat >= $quad['midLat'] && $lon >= $quad['midLon']) {
                $nextQuad = 'a';
                $quad['bottom'] = $quad['midLat'];
                $quad['left'] = $quad['midLon'];
            } elseif ($lat >= $quad['midLat'] && $lon < $quad['midLon']) {
                $nextQuad = 'b';
                $quad['bottom'] = $quad['midLat'];
                $quad['right'] = $quad['midLon'];
            } elseif ($lat < $quad['midLat'] && $lon < $quad['midLon']) {
                $nextQuad = 'c';
                $quad['top'] = $quad['midLat'];
                $quad['right'] = $quad['midLon'];
            } else {
                $nextQuad = 'd';
                $quad['top'] = $quad['midLat'];
                $quad['left'] = $quad['midLon'];
            }

            $cur = (is_array($cur) && array_key_exists($nextQuad, $cur)) ? $cur[$nextQuad] : null;

            if ($cur === null) {
                // No timezone in this quad: open ocean.
                return null;
            }
            if (is_array($cur) && isset($cur['pos']) && !empty($cur['len'])) {
                return $this->lookupBoundary((int)$cur['pos'], (int)$cur['len'], $lat, $lon);
            }
            if (is_array($cur) && array_is_list($cur) && count($cur) > 0) {
                // Exact match: the whole quad belongs to a single timezone.
                return $this->timezones[$cur[0]] ?? null;
            }
            if (!is_array($cur)) {
                throw new \RuntimeException('Unexpected timezone index node');
            }

            $quad['midLat'] = ($quad['top'] + $quad['bottom']) / 2;
            $quad['midLon'] = ($quad['left'] + $quad['right']) / 2;
        }
    }

    private function lookupBoundary(int $pos, int $len, float $lat, float $lon): ?string
    {
        $geoJson = (new GeobufDecoder())->decode($this->readSlice($pos, $len));
        $pt = [$lon, $lat];

        $features = match ($geoJson['type'] ?? null) {
            'FeatureCollection' => $geoJson['features'] ?? [],
            'Feature' => [$geoJson],
            default => [],
        };

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? null;
            $tzid = $feature['properties']['tzid'] ?? null;
            if ($geometry !== null && $tzid !== null && $this->pointInGeometry($pt, $geometry)) {
                return $tzid;
            }
        }

        return null;
    }

    private function pointInGeometry(array $pt, array $geometry): bool
    {
        $coordinates = $geometry['coordinates'] ?? null;
        if ($coordinates === null) {
            return false;
        }
        $polygons = match ($geometry['type'] ?? null) {
            'Polygon' => [$coordinates],
            'MultiPolygon' => $coordinates,
            default => [],
        };

        foreach ($polygons as $polygon) {
            if ($this->inRing($pt, $polygon[0], false)) {
                $inHole = false;
                for ($i = 1; $i < count($polygon); $i++) {
                    if ($this->inRing($pt, $polygon[$i], true)) {
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
    private function inRing(array $pt, array $ring, bool $ignoreBoundary): bool
    {
        $isInside = false;
        $n = count($ring);
        if (
            $n > 0
            && $ring[0][0] === $ring[$n - 1][0]
            && $ring[0][1] === $ring[$n - 1][1]
        ) {
            $n--;
        }

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0];
            $yi = $ring[$i][1];
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            $onBoundary = ($pt[1] * ($xi - $xj) + $yi * ($xj - $pt[0]) + $yj * ($pt[0] - $xi)) === 0.0
                && ($xi - $pt[0]) * ($xj - $pt[0]) <= 0
                && ($yi - $pt[1]) * ($yj - $pt[1]) <= 0;
            if ($onBoundary) {
                return !$ignoreBoundary;
            }

            $intersect = (($yi > $pt[1]) !== ($yj > $pt[1]))
                && ($pt[0] < ($xj - $xi) * ($pt[1] - $yi) / ($yj - $yi) + $xi);
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

    private function ensureLoaded(): void
    {
        if ($this->lookup !== null) {
            return;
        }

        $index = json_decode(file_get_contents($this->indexPath()), true);
        $this->timezones = $index['timezones'];
        $this->lookup = $index['lookup'];
        $this->geoHandle = fopen($this->geoDataPath(), 'rb');
    }

    private function indexPath(): string
    {
        return public_path('timezones-1970.geojson.index.json');
    }

    private function geoDataPath(): string
    {
        return public_path('timezones-1970.geojson.geo.dat');
    }
}
