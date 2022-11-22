<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;

class RootServerResource extends JsonResource
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
            'id' => $this->id,
            'sourceId' => $this->source_id,
            'name' => $this->name,
            'url' => $this->url,
            'lastSuccessfulImport' => $this->last_successful_import,
        ];
    }
}
