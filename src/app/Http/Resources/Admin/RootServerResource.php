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
        $stats = $this->statistics->first();

        return [
            'id' => $this->id,
            'sourceId' => $this->source_id,
            'name' => $this->name,
            'url' => $this->url,
            'statistics' => [
                'serviceBodies' => [
                    'numZones' => $stats?->num_zones,
                    'numRegions' => $stats?->num_regions,
                    'numAreas' => $stats?->num_areas,
                    'numGroups' => $stats?->num_groups,
                ],
                'meetings' => [
                    'numTotal' => $stats?->num_total_meetings,
                    'numInPerson' => $stats?->num_in_person_meetings,
                    'numVirtual' => $stats?->num_virtual_meetings,
                    'numHybrid' => $stats?->num_hybrid_meetings,
                    'numUnknown' => $stats?->num_unknown_meetings,
                ]
            ],
            'serverInfo' => $this->server_info,
            'lastSuccessfulImport' => $this->last_successful_import,
        ];
    }
}
