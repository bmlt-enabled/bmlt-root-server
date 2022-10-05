<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ResourceController extends Controller
{
    protected function resourceAbilityMap()
    {
        return array_merge(parent::resourceAbilityMap(), ['partialUpdate' => 'partialUpdate']);
    }
}
