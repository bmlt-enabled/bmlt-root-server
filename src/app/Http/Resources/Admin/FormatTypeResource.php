<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Models\FormatType;

class FormatTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function toArray($request)
    {
        return [
                    'key' => $request->key_string ?? '',
                    'description' => $request->description_string ?? '',
                ];
    }
}
