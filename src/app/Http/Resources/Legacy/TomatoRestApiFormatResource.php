<?php

namespace App\Http\Resources\Legacy;

use App\Http\Resources\JsonResource;

class TomatoRestApiFormatResource extends JsonResource
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
            'url' => $request->getSchemeAndHttpHost() . $request->getBaseUrl() . "/rest/v1/formats/$this->shared_id_bigint/",
            'source_id' => $this->source_id,
            'type' => $this->format_type_enum,
            'world_id' => $this->worldid_mixed,
            'translatedformats' => $this->translations->map(function ($translation) {
                return [
                    'key_string' => $translation->key_string ?? '',
                    'name' => $translation->name_string ?? '',
                    'description' => $translation->description_string ?? '',
                    'language' => $translation->lang_enum,
                ];
            })
        ];
    }
}
