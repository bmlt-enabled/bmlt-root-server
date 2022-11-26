<?php

namespace App\Interfaces;

use App\Models\Meeting;
use Illuminate\Support\Collection;

interface MeetingRepositoryInterface
{
    public function getSearchResults(
        array $meetingIds = null,
        array $rootServersInclude = null,
        array $rootServersExclude = null,
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
        bool $eagerServiceBodies = false,
        bool $eagerRootServers = false,
        array $sortKeys = null,
        int $pageSize = null,
        int $pageNum = null,
    ): Collection;
    public function getFieldKeys(): Collection;
    public function getFieldValues(string $fieldName, array $specificFormats = [], bool $allFormats = false): Collection;
    public function getMainFields(): Collection;
    public function getDataTemplates(): Collection;
    public function getBoundingBox(): array;
    public function create(array $values): Meeting;
    public function update(int $id, array $values): bool;
    public function delete(int $id): bool;
}
