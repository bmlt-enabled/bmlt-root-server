<?php

namespace App\Http\Resources\Query;

use App\Http\Resources\JsonResource;

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
        $isAggregatorModeEnabled = (bool)legacy_config('aggregator_mode_enabled');

        return [
            'key_string' => $this->key_string,
            'name_string' => $this->name_string ?? '',
            'description_string' => $this->description_string ?? '',
            'lang' => $this->lang_enum,
            'id' => (string)$this->shared_id_bigint,
            'world_id' => $this->worldid_mixed ?? '',
            'format_type_enum' => $this->format_type_enum ?? '',
            'root_server_uri' => $isAggregatorModeEnabled && $this->root_server_id ? $this->rootServer->url : $request->getSchemeAndHttpHost() . $request->getBaseUrl(),
            'root_server_id' => $this->when($isAggregatorModeEnabled, $this->root_server_id ?? '')
        ];
    }
}
