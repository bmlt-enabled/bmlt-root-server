<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;

class JsonResource extends BaseJsonResource
{
    public function jsonOptions()
    {
        return JSON_UNESCAPED_SLASHES;
    }
}
