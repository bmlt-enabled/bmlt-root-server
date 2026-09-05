<?php

namespace App\Interfaces;

interface TimeZoneRepositoryInterface
{
    public function getByCoordinates(float $latitude, float $longitude): ?string;
}
