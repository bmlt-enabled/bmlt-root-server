<?php

namespace App\Http\Resources\Query;

use App\Http\Resources\JsonResource;

class ServiceBodyResource extends JsonResource
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
            'id' => (string)$this->id_bigint,
            'parent_id' => (string)$this->sb_owner ?? 0,
            'name' => $this->name_string,
            'description' => $this->description_string,
            'type' => $this->sb_type ?? '',
            'url' => $this->uri_string ?? '',
            'helpline' => $this->kml_file_uri_string ?? '',
            'world_id' => $this->worldid_mixed ?? '',
        ];
    }
}
