<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\MeetingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCoverageAreaTest extends TestCase
{
    use RefreshDatabase;

    private static $mainFieldDefaults = [
        'worldid_mixed' => 'worldid_mixed_default',
        'service_body_bigint' => 1,
        'weekday_tinyint' => 1,
        'venue_type' => 1,
        'start_time' => '19:00:00',
        'duration_time' => '01:00:00',
        'time_zone' => '',
        'formats' => '17,29,30', // O,To,Tr
        'lang_enum' => 'en',
        'longitude' => -79.793701171875,
        'latitude' => 36.065752051707,
        'published' => 1,
    ];

    private static $dataFieldDefaults = [
        'meeting_name' => 'NA Meeting',
    ];

    private function createMeeting(array $mainFields = [], array $dataFields = [], array $longDataFields = [])
    {
        static $dataFieldTemplates;
        if (!isset($dataFieldTemplates)) {
            $dataFieldTemplates = MeetingData::query()
                ->where('meetingid_bigint', 0)
                ->get()
                ->mapWithKeys(fn ($value, $_) => [$value->key => $value]);
        }

        $meeting = Meeting::create(array_merge(self::$mainFieldDefaults, $mainFields));

        $dataFields = array_merge(self::$dataFieldDefaults, $dataFields);
        foreach (array_keys($longDataFields) as $fieldName) {
            unset($dataFields[$fieldName]);
        }

        foreach ($dataFields as $fieldName => $fieldValue) {
            $fieldTemplate = $dataFieldTemplates->get($fieldName);
            if (is_null($fieldTemplate)) {
                throw new \Exception("unknown field '$fieldName' specified in test meeting");
            }

            $meeting->data()->create([
                'key' => $fieldName,
                'field_prompt' => $fieldTemplate->field_prompt,
                'lang_enum' => 'en',
                'data_string' => $fieldValue,
                'visibility' => $fieldTemplate->visibility,
            ]);
        }

        foreach ($longDataFields as $fieldName => $fieldValue) {
            $fieldTemplate = $dataFieldTemplates->get($fieldName);
            if (is_null($fieldTemplate)) {
                throw new \Exception("unknown field '$fieldName' specified in test meeting");
            }

            $meeting->longdata()->create([
                'key' => $fieldName,
                'field_prompt' => $fieldTemplate->field_prompt,
                'lang_enum' => 'en',
                'data_blob' => $fieldValue,
                'visibility' => $fieldTemplate->visibility,
            ]);
        }

        return $meeting;
    }

    public function testJsonp()
    {
        $content = $this->get('/client_interface/jsonp/?switcher=GetCoverageArea&callback=asdf')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8')
            ->content();
        $this->assertStringStartsWith('/**/asdf([', $content);
        $this->assertStringEndsWith(']);', $content);
    }

    public function testIsList()
    {
        $this->get('/client_interface/json/?switcher=GetCoverageArea')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testKeys()
    {
        $expectedKeys = ['nw_corner_longitude', 'nw_corner_latitude', 'se_corner_longitude', 'se_corner_latitude'];
        $content = $this->get('/client_interface/json/?switcher=GetCoverageArea')
            ->assertStatus(200)
            ->json();
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $content[0]);
        }
    }

    public function testCrossMeridian()
    {
        $meeting1 = $this->createMeeting(['latitude' => 60.1720037, 'longitude' => 24.9366797]); // Helsinki, Finland
        $meeting2 = $this->createMeeting(['latitude' => 41.3901451, 'longitude' => -70.5141987]); // Edgartown MA, US

        $this->get('/client_interface/json/?switcher=GetCoverageArea')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'nw_corner_longitude' => '-70.5141987',
                'nw_corner_latitude' => '60.1720037',
                'se_corner_longitude' => '24.9366797',
                'se_corner_latitude' => '41.3901451'
            ]);
    }

    public function testCrossEquator()
    {
        $meeting1 = $this->createMeeting(['latitude' => -34.6145394, 'longitude' => -58.4063841]); // Buenos Aires, Argentina
        $meeting2 = $this->createMeeting(['latitude' => 41.3901451, 'longitude' => -70.5141987]); // Edgartown MA, US

        $this->get('/client_interface/json/?switcher=GetCoverageArea')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'nw_corner_longitude' => '-70.5141987',
                'nw_corner_latitude' => '41.3901451',
                'se_corner_longitude' => '-58.4063841',
                'se_corner_latitude' => '-34.6145394'
            ]);
    }

    public function testCrossEquatorAndMeridian()
    {
        $meeting1 = $this->createMeeting(['latitude' => 60.1720037, 'longitude' => 24.9366797]); // Helsinki, Finland
        $meeting2 = $this->createMeeting(['latitude' => 41.3901451, 'longitude' => -70.5141987]); // Edgartown MA, US
        $meeting3 = $this->createMeeting(['latitude' => -34.6145394, 'longitude' => -58.4063841]); // Buenos Aires, Argentina

        $this->get('/client_interface/json/?switcher=GetCoverageArea')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'nw_corner_longitude' => '-70.5141987',
                'nw_corner_latitude' => '60.1720037',
                'se_corner_longitude' => '24.9366797',
                'se_corner_latitude' => '-34.6145394'
            ]);
    }
}
