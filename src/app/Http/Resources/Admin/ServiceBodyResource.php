<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Models\ServiceBody;

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
            'id' => $this->id_bigint,
            'parentId' => ($this->sb_owner ?? 0) ?: null,
            'name' => $this->name_string,
            'description' => $this->description_string,
            'type' => in_array($this->sb_type, ServiceBody::VALID_SB_TYPES) ? $this->sb_type : null,
            'adminUserId' => $this->principal_user_bigint ?? null,
            'assignedUserIds' => !empty($this->editors_string) ? collect(explode(',', $this->editors_string))->map(fn ($id) => intval($id))->toArray() : [],
            'url' => $this->uri_string,
            'helpline' => $this->kml_file_uri_string,
            'email' => $this->sb_meeting_email,
            'worldId' => $this->worldid_mixed,
        ];
    }
}
