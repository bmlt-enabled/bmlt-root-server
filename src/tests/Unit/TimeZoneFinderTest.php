<?php

namespace Tests\Unit;

use App\Services\TimeZoneFinder;
use Tests\TestCase;

class TimeZoneFinderTest extends TestCase
{
    public function testFindResolvesKnownCoordinates(): void
    {
        $cases = [
            [40.7128, -74.0060, 'America/New_York'],
            [41.8781, -87.6298, 'America/Chicago'],
            [39.7392, -104.9903, 'America/Denver'],
            [34.0522, -118.2437, 'America/Los_Angeles'],
            [33.4484, -112.0740, 'America/Phoenix'],
            [21.3069, -157.8583, 'Pacific/Honolulu'],
            [61.2181, -149.9003, 'America/Anchorage'],
            [51.5074, -0.1278, 'Europe/London'],
            [52.5200, 13.4050, 'Europe/Berlin'],
            [25.2048, 55.2708, 'Asia/Dubai'],
            [22.5726, 88.3639, 'Asia/Kolkata'],
            [35.6762, 139.6503, 'Asia/Tokyo'],
            [-33.8688, 151.2093, 'Australia/Sydney'],
            [-23.5505, -46.6333, 'America/Sao_Paulo'],
        ];

        $finder = new TimeZoneFinder();
        foreach ($cases as [$lat, $lon, $expected]) {
            $this->assertEquals($expected, $finder->find($lat, $lon), "coordinates {$lat},{$lon}");
        }
    }

    public function testFindReturnsNullOverOpenOcean(): void
    {
        // Middle of the North Atlantic.
        $this->assertNull((new TimeZoneFinder())->find(30.0, -40.0));
    }

    public function testFindReturnsNullForOutOfRangeCoordinates(): void
    {
        $finder = new TimeZoneFinder();
        $this->assertNull($finder->find(120.0, 10.0));
        $this->assertNull($finder->find(10.0, 200.0));
    }
}
