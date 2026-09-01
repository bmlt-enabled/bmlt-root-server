<?php

namespace App\Services;

/**
 * Decodes a geobuf-encoded blob into a GeoJSON-like associative array.
 *
 * Faithful PHP port of https://github.com/mapbox/geobuf `decode.js` (as used by
 * the frontend timezone lookup), scoped to what timezone boundaries require:
 * FeatureCollection / Feature with Polygon and MultiPolygon geometries plus
 * their `tzid` property. A fresh instance is used per blob.
 */
class GeobufDecoder
{
    private const GEOMETRY_TYPES = [
        'Point', 'MultiPoint', 'LineString', 'MultiLineString',
        'Polygon', 'MultiPolygon', 'GeometryCollection',
    ];

    private const UNSAFE_KEYS = ['__proto__', 'constructor', 'prototype'];

    private ProtobufReader $pbf;
    private array $keys = [];
    private array $values = [];
    private ?array $lengths = null;
    private int $dim = 2;
    private float $e = 1000000.0;

    public function decode(string $bytes): array
    {
        $this->pbf = new ProtobufReader($bytes);
        $this->keys = [];
        $this->values = [];
        $this->lengths = null;
        $this->dim = 2;
        $this->e = 10 ** 6;

        $obj = ['type' => 'Point'];
        $end = $this->pbf->length();
        while ($this->pbf->pos() < $end) {
            [$tag, $wireType] = $this->pbf->readTag();
            switch ($tag) {
                case 1:
                    $this->keys[] = $this->pbf->readString();
                    break;
                case 2:
                    $this->dim = $this->pbf->readVarint();
                    break;
                case 3:
                    $this->e = 10 ** $this->pbf->readVarint();
                    break;
                case 4:
                    $obj = $this->readFeatureCollection();
                    break;
                case 5:
                    $obj = $this->readFeature();
                    break;
                case 6:
                    $obj = $this->readGeometry();
                    break;
                default:
                    $this->pbf->skip($wireType);
                    break;
            }
        }
        return $obj;
    }

    private function readFeatureCollection(): array
    {
        $obj = ['type' => 'FeatureCollection', 'features' => []];
        $end = $this->pbf->beginMessage();
        while ($this->pbf->pos() < $end) {
            [$tag, $wireType] = $this->pbf->readTag();
            switch ($tag) {
                case 1:
                    $obj['features'][] = $this->readFeature();
                    break;
                case 13:
                    $this->values[] = $this->readValue();
                    break;
                default:
                    $this->pbf->skip($wireType);
                    break;
            }
        }
        return $obj;
    }

    private function readFeature(): array
    {
        $feature = ['type' => 'Feature', 'geometry' => null, 'properties' => []];
        $end = $this->pbf->beginMessage();
        while ($this->pbf->pos() < $end) {
            [$tag, $wireType] = $this->pbf->readTag();
            switch ($tag) {
                case 1:
                    $feature['geometry'] = $this->readGeometry();
                    break;
                case 11:
                    $feature['id'] = $this->pbf->readString();
                    break;
                case 12:
                    $feature['id'] = $this->pbf->readSVarint();
                    break;
                case 13:
                    $this->values[] = $this->readValue();
                    break;
                case 14:
                    $props = [];
                    $this->readProps($props);
                    $feature['properties'] = $props;
                    break;
                default:
                    $this->pbf->skip($wireType);
                    break;
            }
        }
        return $feature;
    }

    private function readGeometry(): array
    {
        $geom = ['type' => 'Point'];
        $end = $this->pbf->beginMessage();
        while ($this->pbf->pos() < $end) {
            [$tag, $wireType] = $this->pbf->readTag();
            switch ($tag) {
                case 1:
                    $geom['type'] = self::GEOMETRY_TYPES[$this->pbf->readVarint()];
                    break;
                case 2:
                    $this->lengths = $this->readPackedVarint();
                    break;
                case 3:
                    $geom['coordinates'] = $this->readCoords($geom['type']);
                    break;
                case 13:
                    $this->values[] = $this->readValue();
                    break;
                default:
                    $this->pbf->skip($wireType);
                    break;
            }
        }
        return $geom;
    }

    private function readCoords(string $type): array
    {
        return match ($type) {
            'Point' => $this->readPoint(),
            'LineString' => $this->readLine(),
            'MultiLineString' => $this->readMultiLine(false),
            'Polygon' => $this->readMultiLine(true),
            'MultiPolygon' => $this->readMultiPolygon(),
            default => [],
        };
    }

    private function readValue(): mixed
    {
        $end = $this->pbf->beginMessage();
        $value = null;
        while ($this->pbf->pos() < $end) {
            [$tag, $wireType] = $this->pbf->readTag();
            switch ($tag) {
                case 1:
                    $value = $this->pbf->readString();
                    break;
                case 2:
                    $value = $this->pbf->readDouble();
                    break;
                case 3:
                    $value = $this->pbf->readVarint();
                    break;
                case 4:
                    $value = -$this->pbf->readVarint();
                    break;
                case 5:
                    $value = $this->pbf->readBoolean();
                    break;
                case 6:
                    $value = json_decode($this->pbf->readString(), true);
                    break;
                default:
                    $this->pbf->skip($wireType);
                    break;
            }
        }
        return $value;
    }

    private function readProps(array &$props): void
    {
        $end = $this->pbf->beginMessage();
        while ($this->pbf->pos() < $end) {
            $key = $this->keys[$this->pbf->readVarint()];
            $val = $this->values[$this->pbf->readVarint()];
            if (!in_array($key, self::UNSAFE_KEYS, true)) {
                $props[$key] = $val;
            }
        }
        $this->values = [];
    }

    /** @return array<int, float> */
    private function readPoint(): array
    {
        $end = $this->pbf->beginMessage();
        $coords = [];
        while ($this->pbf->pos() < $end) {
            $coords[] = $this->pbf->readSVarint() / $this->e;
        }
        return $coords;
    }

    private function readLinePart(int $end, ?int $len, bool $closed): array
    {
        $i = 0;
        $coords = [];
        $prevP = array_fill(0, $this->dim, 0);
        while ($len !== null ? $i < $len : $this->pbf->pos() < $end) {
            $p = [];
            for ($d = 0; $d < $this->dim; $d++) {
                $prevP[$d] += $this->pbf->readSVarint();
                $p[$d] = $prevP[$d] / $this->e;
            }
            $coords[] = $p;
            $i++;
        }
        if ($closed) {
            $coords[] = $coords[0];
        }
        return $coords;
    }

    private function readLine(): array
    {
        return $this->readLinePart($this->pbf->beginMessage(), null, false);
    }

    private function readMultiLine(bool $closed): array
    {
        $end = $this->pbf->beginMessage();
        if ($this->lengths === null) {
            return [$this->readLinePart($end, null, $closed)];
        }
        $coords = [];
        foreach ($this->lengths as $len) {
            $coords[] = $this->readLinePart($end, $len, $closed);
        }
        $this->lengths = null;
        return $coords;
    }

    private function readMultiPolygon(): array
    {
        $end = $this->pbf->beginMessage();
        if ($this->lengths === null) {
            return [[$this->readLinePart($end, null, true)]];
        }
        $coords = [];
        $j = 1;
        for ($i = 0; $i < $this->lengths[0]; $i++) {
            $rings = [];
            for ($k = 0; $k < $this->lengths[$j]; $k++) {
                $rings[] = $this->readLinePart($end, $this->lengths[$j + 1 + $k], true);
            }
            $j += $this->lengths[$j] + 1;
            $coords[] = $rings;
        }
        $this->lengths = null;
        return $coords;
    }

    /** @return array<int, int> */
    private function readPackedVarint(): array
    {
        $end = $this->pbf->beginMessage();
        $arr = [];
        while ($this->pbf->pos() < $end) {
            $arr[] = $this->pbf->readVarint();
        }
        return $arr;
    }
}
