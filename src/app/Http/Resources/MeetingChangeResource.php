<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MeetingChangeResource extends JsonResource
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
            'date_int' => strval(strtotime($this->change_date)),
            'date_str' => date('g:i A, n/j/Y', strtotime($this->change_date)),
            'change_type' => $this->change_type_enum,
            'change_id' => strval($this->id_bigint),
            'meeting_id' => strval($this->before_id_bigint ?? $this->after_id_bigint ?? 0),
            'meeting_name' => $this->beforeMeeting?->getName() ?? $this->afterMeeting?->getName() ?? '',
            'user_id' => strval($this->user_id_bigint),
            'user_name' => $this->user?->name_string ?? '',
            'service_body_id' => strval($this->service_body_id_bigint),
            'service_body_name' => $this->serviceBody?->name_string ?? '',
            'meeting_exists' => ($this->before_object || $this->after_object) ? '1' : '0',
            'details' => $this->getChangeDetails(),
            'json_data' => $this->getJsonData(),
        ];
    }

    private function getJsonData()
    {
        $ret = [];

        if ($this->before_object) {
            $ret['before'] = $this->getMeetingJsonData($this->before_object);
        }

        if ($this->after_object) {
            $ret['after'] = $this->getMeetingJsonData($this->after_object);
        }

        return $ret;
    }

    private function getMeetingJsonData($meeting): array
    {
        $ret = [];

        $mainValues = $meeting['main_table_values'] ?? null;
        if ($mainValues) {
            $ret['id_bigint'] = strval($mainValues['id_bigint'] ?? '');
            $ret['service_body_bigint'] = strval($mainValues['service_body_bigint'] ?? '');
            $ret['weekday_tinyint'] = strval($mainValues['weekday_tinyint'] ?? '');
            $ret['venue_type'] = strval($mainValues['venue_type'] ?? '');
            $ret['start_time'] = $mainValues['start_time'] ?? '';
            $ret['lang_enum'] = $mainValues['lang_enum'] ?? '';
            $ret['duration_time'] = $mainValues['duration_time'] ?? '';
            $ret['longitude'] = strval($mainValues['longitude'] ?? '');
            $ret['latitude'] = strval($mainValues['latitude'] ?? '');
            $ret['published'] = strval($mainValues['published'] ?? '');
            // TODO format keys instead of format ids
            $ret['formats'] = $mainValues['formats'] ?? '';
        }

        $dataTableValues = [];
        foreach (($meeting['data_table_values'] ?? []) as $data) {
            if (isset($data['key']) && $data['key'] == 'root_server_uri') {
                continue;
            }
            if (!isset($data['data_string'])) {
                continue;
            }
            $dataTableValues[$data['key']] = $data['data_string'];
        }

        return collect($ret)->merge($dataTableValues)->toArray();
    }
}
