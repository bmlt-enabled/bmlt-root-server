<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface MeetingRepositoryInterface
{
    public function getSearchResults(
        array $meetingIds = null,
        array $weekdaysInclude = null,
        array $weekdaysExclude = null,
        array $venueTypesInclude = null,
        array $venueTypesExclude = null,
        array $servicesInclude = null,
        array $servicesExclude = null,
        array $formatsInclude = null,
        array $formatsExclude = null,
        string $formatsComparisonOperator = 'AND',
        string $meetingKey = null,
        string $meetingKeyValue = null,
        string $startsAfter = null,
        string $startsBefore = null,
        string $endsBefore = null,
        string $minDuration = null,
        string $maxDuration = null,
        float $latitude = null,
        float $longitude = null,
        float $geoWidthMiles = null,
        float $geoWidthKilometers = null,
        bool $needsDistanceField = false,
        bool $sortResultsByDistance = false,
        string $searchString = null,
        bool $published = true,
        array $sortKeys = null,
        int $pageSize = null,
        int $pageNum = null,
    ): Collection;
    public function getFieldKeys(): Collection;
    public function getFieldValues(string $fieldName, array $specificFormats = [], bool $allFormats = false): Collection;
}
