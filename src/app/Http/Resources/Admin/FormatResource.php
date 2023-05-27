<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Models\FormatType;

class FormatResource extends JsonResource
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
            'id' => $this->shared_id_bigint,
            'worldId' => $this->worldid_mixed,
            'type' => FormatType::getApiEnumFromKey($this->format_type_enum),
            'translations' => $this->translations->map(function ($translation) {
                return [
                    'key' => $translation->key_string ?? '',
                    'name' => $translation->name_string ?? '',
                    'description' => $translation->description_string ?? '',
                    'language' => $translation->lang_enum,
                ];
            })
        ];
    }
}
