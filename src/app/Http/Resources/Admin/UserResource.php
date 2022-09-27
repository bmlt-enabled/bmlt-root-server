<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Models\User;

class UserResource extends JsonResource
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
            'username' => $this->login_string,
            'userType' => User::USER_LEVEL_USER_TYPE_MAP[$this->user_level_tinyint] ?? User::USER_TYPE_DISABLED,
            'displayName' => $this->name_string,
            'description' => $this->description_string,
            'email' => $this->email_address_string,
            'ownerId' => $this->owner_id_bigint == -1 ? null : $this->owner_id_bigint,
        ];
    }
}
