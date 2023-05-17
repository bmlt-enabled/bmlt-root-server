<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Repositories\FormatTypeRepository;

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
        $format_type_resouce = new FormatTypeRepository();
        return [
            'id' => $this->shared_id_bigint,
            'worldId' => $this->worldid_mixed,
            'type' => $format_type_resouce->getDescriptionFromKey($this->format_type_enum),
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
