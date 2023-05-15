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
            'translations' => $this->translations->map(function ($translation) {
                return [
                    'key' => $translation->key_string ?? '',
                    'description' => $translation->description_string ?? '',
                    'language' => $translation->lang_enum,
                ];
            })
        ];
    }
}
