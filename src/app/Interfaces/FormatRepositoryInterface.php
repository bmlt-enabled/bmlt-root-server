<?php

namespace App\Interfaces;

interface FormatRepositoryInterface
{
    public function getFormats(
        array $langEnums = ['en'],
        array $keyStrings = null,
        bool $showAll = false,
    );
}
