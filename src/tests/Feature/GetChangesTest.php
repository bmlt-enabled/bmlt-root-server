<?php

namespace Tests\Feature;

use App\Http\Resources\Query\MeetingChangeResource;
use App\Models\Change;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\ServiceBody;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetChangesTest extends TestCase
{
    use RefreshDatabase;

    private $mainTableFields = [
        'worldid_mixed' => null,
        'service_body_bigint' => '1',
        'weekday_tinyint' => null,
        'venue_type' => null,
        'start_time' => null,
        'lang_enum' => 'en',
        'duration_time' => null,
        'time_zone' => null,
        'longitude' => null,
        'latitude' => null,
        'published' => '1',
        'formats' => '',
    ];

    private $mainTablePublicFields = [
        'worldid_mixed' => null,
        'service_body_bigint' => '1',
        'weekday_tinyint' => null,
        'venue_type' => null,
        'start_time' => null,
        'lang_enum' => 'en',
        'duration_time' => null,
        'time_zone' => null,
        'longitude' => null,
        'latitude' => null,
        'published' => '1',
        'formats' => '',
    ];

    protected function tearDown(): void
    {
        MeetingChangeResource::resetStaticVariables();
        parent::tearDown();
    }

    private function createChange($beforeValues, $afterValues, $user, $langEnum = 'en', $changeDate = '2022-01-01')
    {
        $meeting = null;
        $afterObject = null;
        if (!is_null($afterValues)) {
            $mainValuesArray = $this->getMainValuesArray($afterValues);
            $meeting = Meeting::create($mainValuesArray);
            $mainValuesArray['id_bigint'] = strval($meeting->id_bigint);
            $dataValuesArray = $this->getDataValuesArray($meeting, $afterValues);
            collect($dataValuesArray)->each(fn ($value) => MeetingData::create($value));
            $afterObject = serialize([
                'main_table_values' => serialize($mainValuesArray),
                'data_table_values' => serialize($dataValuesArray),
                'longdata_table_values' => serialize([]),
            ]);
        }

        $beforeObject = null;
        if (!is_null($beforeValues)) {
            $mainValuesArray = $this->getMainValuesArray($beforeValues);
            $needsCreateDataValues = false;
            if (!$meeting) {
                $meeting = Meeting::create($mainValuesArray);
                $needsCreateDataValues = true;
            }
            $mainValuesArray['id_bigint'] = strval($meeting->id_bigint);
            $dataValuesArray = $this->getDataValuesArray($meeting, $beforeValues);
            if ($needsCreateDataValues) {
                collect($dataValuesArray)->each(fn($value) => MeetingData::create($value));
            }
            $beforeObject = serialize([
                'main_table_values' => serialize($mainValuesArray),
                'data_table_values' => serialize($dataValuesArray),
                'longdata_table_values' => serialize([]),
            ]);
        }

        if (!is_null($beforeObject) && !is_null($afterObject)) {
            $changeTypeEnum = 'comdef_change_type_change';
        } elseif (!is_null($beforeObject)) {
            $changeTypeEnum = 'comdef_change_type_delete';
        } else {
            $changeTypeEnum = 'comdef_change_type_new';
        }
        return Change::create([
            'user_id_bigint' => $user?->id_bigint,
            'service_body_id_bigint' => $meeting?->service_body_bigint ?? $meeting?->service_body_bigint,
            'lang_enum' => $langEnum,
            'change_date' => $changeDate,
            'object_class_string' => 'c_comdef_meeting',
            'change_name_string' => null,
            'change_description_text' => null,
            'before_id_bigint' => $meeting?->id_bigint,
            'before_lang_enum' => $langEnum,
            'after_id_bigint' => $meeting?->id_bigint,
            'after_lang_enum' => $langEnum,
            'change_type_enum' => $changeTypeEnum,
            'before_object' => $beforeObject,
            'after_object' => $afterObject,
        ]);
    }

    private function getMainValuesArray(array $valuesArray)
    {
        return collect($this->mainTableFields)
            ->mapWithKeys(fn ($defaultValue, $key) => [$key => $valuesArray[$key] ?? $defaultValue])
            ->toArray();
    }

    private function getMainValuesPublicArray($meeting, array $valuesArray)
    {
        return collect($this->getMainValuesArray($valuesArray))
            ->map(fn ($value) => $value ?? '')
            ->merge(['id_bigint' => strval($meeting?->id_bigint ?? '')])
            ->reject(fn ($value, $key) => is_null($value) || $value == '')
            ->toArray();
    }

    private function getDataValuesArray($meeting, array $valuesArray)
    {
        return MeetingData::query()
            ->where('meetingid_bigint', 0)
            ->get()
            ->map(function ($field) use ($meeting, $valuesArray) {
                $value = $valuesArray[$field->key] ?? null;
                if (!is_null($value)) {
                    return [
                        'meetingid_bigint' => $meeting->id_bigint,
                        'lang_enum' => 'en',
                        'field_prompt' => $field->field_prompt,
                        'visibility' => $field->visibility,
                        'key' => $field->key,
                        'data_string' => $value,
                    ];
                }
                return null;
            })
            ->filter()
            ->toArray();
    }

    private function createServiceBody($name, $sbOwner = 0)
    {
        return ServiceBody::create([
            'sb_owner' => $sbOwner,
            'name_string' => $name,
            'description_string' => 'test',
            'sb_type' => 'AS',
            'uri_string' => '',
            'kml_file_uri_string' => '',
            'worldid_mixed' => '',
            'sb_meeting_email' => '',
        ]);
    }

    public function createUser()
    {
        return User::create([
            'user_level_tinyint' => 2,
            'name_string' => 'test',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'test',
            'password_string' => '',
        ]);
    }

    private function createFormat(int $sharedId, string $keyString, string $langEnum = 'en', string $worldId = null, string $formatTypeEnum = 'FC')
    {
        return Format::create([
            'shared_id_bigint' => $sharedId,
            'key_string' => $keyString,
            'name_string' => $keyString,
            'lang_enum' => $langEnum,
            'description_string' => $keyString,
            'worldid_mixed' => $worldId,
            'format_type_enum' => $formatTypeEnum,
        ]);
    }

    public function testXml()
    {
        $this->get('/client_interface/xml/?switcher=GetChanges')
            ->assertStatus(404);
    }

    public function testJsonp()
    {
        $response = $this->get('/client_interface/jsonp/?switcher=GetChanges&callback=asdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
        $content = $response->content();
        $this->assertEquals('/**/asdf([]);', $content);
    }

    public function testCreated()
    {
        $user = $this->createUser();
        $afterValues = ['meeting_name' => 'after'];
        $change = $this->createChange(null, $afterValues, $user);
        $user->delete();
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => 'after',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => '',
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting was created.',
                    'json_data' => [
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testDeleted()
    {
        $user = $this->createUser();
        $beforeValues = ['meeting_name' => 'after'];
        $change = $this->createChange($beforeValues, null, $user);
        $user->delete();
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => 'after',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => '',
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '0',
                    'details' => 'The meeting was deleted.',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testNoUser()
    {
        $user = $this->createUser();
        $beforeValues = ['latitude' => '1.1'];
        $afterValues = ['latitude' => '-1.1'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $user->delete();
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => '',
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting latitude was changed from "1.1" to "-1.1".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testWeekdayChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['weekday_tinyint' => '0'];
        $afterValues = ['weekday_tinyint' => '1'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        // for some reason changes add 1
        $beforeValues = ['weekday_tinyint' => '1'];
        $afterValues = ['weekday_tinyint' => '2'];
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The day of the week on which the meeting gathers was changed from "Sunday" to "Monday".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testFormatsChanged()
    {
        $format1 = $this->createFormat(101, 'X');
        $format2 = $this->createFormat(102, 'A');
        $format3 = $this->createFormat(103, 'B');
        $user = $this->createUser();
        $beforeValues = ['formats' => implode(',', [$format1->shared_id_bigint])];
        $afterValues = ['formats' => implode(',', [$format1->shared_id_bigint, $format2->shared_id_bigint, $format3->shared_id_bigint])];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $beforeArray = collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray();
        $beforeArray['formats'] = ['X'];
        $afterArray = collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray();
        $afterArray['formats'] = ['A', 'B', 'X'];
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting format was changed from "X" to "A, B, X".',
                    'json_data' => [
                        'before' => $beforeArray,
                        'after' => $afterArray,
                    ],
                ]
            ]);
    }

    public function testVenueTypeChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['venue_type' => '1'];
        $afterValues = ['venue_type' => '2'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'venue_type was changed from "1" to "2".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testWorldIdChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['worldid_mixed' => 'before'];
        $afterValues = ['worldid_mixed' => 'after'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The World Committee Code was changed from "before" to "after".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testTimeZoneChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['time_zone' => 'before'];
        $afterValues = ['time_zone' => 'after'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'time_zone was changed from "before" to "after".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testStartTimeChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['start_time' => '10:00'];
        $afterValues = ['start_time' => '11:00'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting start time was changed from "10:00" to "11:00".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testDurationTimeChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['duration_time' => '1:00'];
        $afterValues = ['duration_time' => '1:30'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting duration was changed from "1:00" to "1:30".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testLongitudeChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['longitude' => '1.1'];
        $afterValues = ['longitude' => '-1.1'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting longitude was changed from "1.1" to "-1.1".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testLatitudeChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['latitude' => '1.1'];
        $afterValues = ['latitude' => '-1.1'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting latitude was changed from "1.1" to "-1.1".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testServiceBodyChanged()
    {
        $user = $this->createUser();
        $beforeServiceBody = $this->createServiceBody('before');
        $afterServiceBody = $this->createServiceBody('after');
        $beforeValues = ['service_body_bigint' => strval($beforeServiceBody->id_bigint)];
        $afterValues = ['service_body_bigint' => strval($afterServiceBody->id_bigint)];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => strval($afterServiceBody->id_bigint),
                    'service_body_name' => $afterServiceBody->name_string,
                    'meeting_exists' => '1',
                    'details' => 'The meeting changed its Service Body from before to after.',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testPublished()
    {
        $user = $this->createUser();
        $beforeValues = ['published' => '0'];
        $afterValues = ['published' => '1'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting was published.',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testUnpublished()
    {
        $user = $this->createUser();
        $beforeValues = ['published' => '1'];
        $afterValues = ['published' => '0'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => '',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'The meeting was unpublished.',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testMeetingNameChanged()
    {
        $user = $this->createUser();
        $beforeValues = ['meeting_name' => 'first'];
        $afterValues = ['meeting_name' => 'second'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $this->get('/client_interface/json/?switcher=GetChanges')
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'date_int' => strval(strtotime($change->change_date)),
                    'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                    'change_type' => $change->change_type_enum,
                    'change_id' => strval($change->id_bigint),
                    'meeting_id' => strval($change->afterMeeting->id_bigint),
                    'meeting_name' => 'second',
                    'user_id' => strval($user->id_bigint),
                    'user_name' => $user->name_string,
                    'service_body_id' => '1',
                    'service_body_name' => '',
                    'meeting_exists' => '1',
                    'details' => 'Meeting Name was changed from "first" to "second".',
                    'json_data' => [
                        'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                        'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                    ],
                ]
            ]);
    }

    public function testPureDataFieldsChanged()
    {
        $fieldAndPrompts = [
            'location_text' => 'Location Name',
            'location_info' => 'Additional Location Information',
            'location_street' => 'Street Address',
            'location_city_subsection' => 'Borough',
            'location_neighborhood' => 'Neighborhood',
            'location_municipality' => 'Town',
            'location_sub_province' => 'County',
            'location_province' => 'State',
            'location_postal_code_1' => 'Zip Code',
            'location_nation' => 'Nation',
            'comments' => 'Comments',
            'train_lines' => 'Train Lines',
            'bus_lines' => 'Bus Lines',
            'phone_meeting_number' => 'Phone Meeting Dial-in Number',
            'virtual_meeting_link' => 'Virtual Meeting Link',
            'virtual_meeting_additional_info' => 'Virtual Meeting Additional Info',
        ];

        $user = $this->createUser();
        foreach ($fieldAndPrompts as $fieldName => $prompt) {
            $beforeValues = [$fieldName => 'first'];
            $afterValues = [$fieldName => 'second'];
            $change = $this->createChange($beforeValues, $afterValues, $user);
            $this->get('/client_interface/json/?switcher=GetChanges')
                ->assertStatus(200)
                ->assertExactJson([
                    [
                        'date_int' => strval(strtotime($change->change_date)),
                        'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                        'change_type' => $change->change_type_enum,
                        'change_id' => strval($change->id_bigint),
                        'meeting_id' => strval($change->afterMeeting->id_bigint),
                        'meeting_name' => '',
                        'user_id' => strval($user->id_bigint),
                        'user_name' => $user->name_string,
                        'service_body_id' => '1',
                        'service_body_name' => '',
                        'meeting_exists' => '1',
                        'details' => "$prompt was changed from \"$beforeValues[$fieldName]\" to \"$afterValues[$fieldName]\".",
                        'json_data' => [
                            'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                            'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                        ],
                    ]
                ]);
            Change::query()->delete();
            Meeting::query()->delete();
            MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
        }
    }

    public function testPureDataFieldsAdded()
    {
        $fieldAndPrompts = [
            'location_text' => 'Location Name',
            'location_info' => 'Additional Location Information',
            'location_street' => 'Street Address',
            'location_city_subsection' => 'Borough',
            'location_neighborhood' => 'Neighborhood',
            'location_municipality' => 'Town',
            'location_sub_province' => 'County',
            'location_province' => 'State',
            'location_postal_code_1' => 'Zip Code',
            'location_nation' => 'Nation',
            'comments' => 'Comments',
            'train_lines' => 'Train Lines',
            'bus_lines' => 'Bus Lines',
            'phone_meeting_number' => 'Phone Meeting Dial-in Number',
            'virtual_meeting_link' => 'Virtual Meeting Link',
            'virtual_meeting_additional_info' => 'Virtual Meeting Additional Info',
        ];

        $user = $this->createUser();
        foreach ($fieldAndPrompts as $fieldName => $prompt) {
            $beforeValues = [];
            $afterValues = [$fieldName => 'after'];
            $change = $this->createChange($beforeValues, $afterValues, $user);
            $this->get('/client_interface/json/?switcher=GetChanges')
                ->assertStatus(200)
                ->assertExactJson([
                    [
                        'date_int' => strval(strtotime($change->change_date)),
                        'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                        'change_type' => $change->change_type_enum,
                        'change_id' => strval($change->id_bigint),
                        'meeting_id' => strval($change->afterMeeting->id_bigint),
                        'meeting_name' => '',
                        'user_id' => strval($user->id_bigint),
                        'user_name' => $user->name_string,
                        'service_body_id' => '1',
                        'service_body_name' => '',
                        'meeting_exists' => '1',
                        'details' => "$prompt was added as \"after\".",
                        'json_data' => [
                            'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                            'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                        ],
                    ]
                ]);
            Change::query()->delete();
            Meeting::query()->delete();
            MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
        }
    }

    public function testPureDataFieldsRemoved()
    {
        $fieldAndPrompts = [
            'location_text' => 'Location Name',
            'location_info' => 'Additional Location Information',
            'location_street' => 'Street Address',
            'location_city_subsection' => 'Borough',
            'location_neighborhood' => 'Neighborhood',
            'location_municipality' => 'Town',
            'location_sub_province' => 'County',
            'location_province' => 'State',
            'location_postal_code_1' => 'Zip Code',
            'location_nation' => 'Nation',
            'comments' => 'Comments',
            'train_lines' => 'Train Lines',
            'bus_lines' => 'Bus Lines',
            'phone_meeting_number' => 'Phone Meeting Dial-in Number',
            'virtual_meeting_link' => 'Virtual Meeting Link',
            'virtual_meeting_additional_info' => 'Virtual Meeting Additional Info',
        ];

        $user = $this->createUser();
        foreach ($fieldAndPrompts as $fieldName => $prompt) {
            $beforeValues = [$fieldName => 'before'];
            $afterValues = [];
            $change = $this->createChange($beforeValues, $afterValues, $user);
            $this->get('/client_interface/json/?switcher=GetChanges')
                ->assertStatus(200)
                ->assertExactJson([
                    [
                        'date_int' => strval(strtotime($change->change_date)),
                        'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                        'change_type' => $change->change_type_enum,
                        'change_id' => strval($change->id_bigint),
                        'meeting_id' => strval($change->afterMeeting->id_bigint),
                        'meeting_name' => '',
                        'user_id' => strval($user->id_bigint),
                        'user_name' => $user->name_string,
                        'service_body_id' => '1',
                        'service_body_name' => '',
                        'meeting_exists' => '1',
                        'details' => "$prompt was deleted.",
                        'json_data' => [
                            'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                            'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                        ],
                    ]
                ]);
            Change::query()->delete();
            Meeting::query()->delete();
            MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
        }
    }

    public function testSensitiveDataFieldsChanged()
    {
        $fieldAndPrompts = [
            'contact_phone_2' => 'Contact 2 Phone',
            'contact_email_2' => 'Contact 2 Email',
            'contact_name_2' => 'Contact 2 Name',
            'contact_phone_1' => 'Contact 1 Phone',
            'contact_email_1' => 'Contact 1 Email',
            'contact_name_1' => 'Contact 1 Name',
        ];

        $user = $this->createUser();
        foreach ($fieldAndPrompts as $fieldName => $prompt) {
            $beforeValues = [$fieldName => 'first'];
            $afterValues = [$fieldName => 'second'];
            $change = $this->createChange($beforeValues, $afterValues, $user);
            $this->get('/client_interface/json/?switcher=GetChanges')
                ->assertStatus(200)
                ->assertExactJson([
                    [
                        'date_int' => strval(strtotime($change->change_date)),
                        'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                        'change_type' => $change->change_type_enum,
                        'change_id' => strval($change->id_bigint),
                        'meeting_id' => strval($change->afterMeeting->id_bigint),
                        'meeting_name' => '',
                        'user_id' => strval($user->id_bigint),
                        'user_name' => $user->name_string,
                        'service_body_id' => '1',
                        'service_body_name' => '',
                        'meeting_exists' => '1',
                        'details' => "$prompt was changed.",
                        'json_data' => [
                            'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                            'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                        ],
                    ]
                ]);
            Change::query()->delete();
            Meeting::query()->delete();
            MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
        }
    }

    public function testSensitiveDataFieldsAdded()
    {
        $fieldAndPrompts = [
            'contact_phone_2' => 'Contact 2 Phone',
            'contact_email_2' => 'Contact 2 Email',
            'contact_name_2' => 'Contact 2 Name',
            'contact_phone_1' => 'Contact 1 Phone',
            'contact_email_1' => 'Contact 1 Email',
            'contact_name_1' => 'Contact 1 Name',
        ];

        $user = $this->createUser();
        foreach ($fieldAndPrompts as $fieldName => $prompt) {
            $beforeValues = [];
            $afterValues = [$fieldName => 'after'];
            $change = $this->createChange($beforeValues, $afterValues, $user);
            $this->get('/client_interface/json/?switcher=GetChanges')
                ->assertStatus(200)
                ->assertExactJson([
                    [
                        'date_int' => strval(strtotime($change->change_date)),
                        'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                        'change_type' => $change->change_type_enum,
                        'change_id' => strval($change->id_bigint),
                        'meeting_id' => strval($change->afterMeeting->id_bigint),
                        'meeting_name' => '',
                        'user_id' => strval($user->id_bigint),
                        'user_name' => $user->name_string,
                        'service_body_id' => '1',
                        'service_body_name' => '',
                        'meeting_exists' => '1',
                        'details' => "$prompt was changed.",
                        'json_data' => [
                            'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                            'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                        ],
                    ]
                ]);
            Change::query()->delete();
            Meeting::query()->delete();
            MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
        }
    }

    public function testSensitiveDataFieldsRemoved()
    {
        $fieldAndPrompts = [
            'contact_phone_2' => 'Contact 2 Phone',
            'contact_email_2' => 'Contact 2 Email',
            'contact_name_2' => 'Contact 2 Name',
            'contact_phone_1' => 'Contact 1 Phone',
            'contact_email_1' => 'Contact 1 Email',
            'contact_name_1' => 'Contact 1 Name',
        ];

        $user = $this->createUser();
        foreach ($fieldAndPrompts as $fieldName => $prompt) {
            $beforeValues = [$fieldName => 'before'];
            $afterValues = [];
            $change = $this->createChange($beforeValues, $afterValues, $user);
            $this->get('/client_interface/json/?switcher=GetChanges')
                ->assertStatus(200)
                ->assertExactJson([
                    [
                        'date_int' => strval(strtotime($change->change_date)),
                        'date_string' => date('g:i A, n/j/Y', strtotime($change->change_date)),
                        'change_type' => $change->change_type_enum,
                        'change_id' => strval($change->id_bigint),
                        'meeting_id' => strval($change->afterMeeting->id_bigint),
                        'meeting_name' => '',
                        'user_id' => strval($user->id_bigint),
                        'user_name' => $user->name_string,
                        'service_body_id' => '1',
                        'service_body_name' => '',
                        'meeting_exists' => '1',
                        'details' => "$prompt was deleted.",
                        'json_data' => [
                            'before' => collect($this->getMainValuesPublicArray($change->beforeMeeting, $beforeValues))->merge($beforeValues)->toArray(),
                            'after' => collect($this->getMainValuesPublicArray($change->afterMeeting, $afterValues))->merge($afterValues)->toArray(),
                        ],
                    ]
                ]);
            Change::query()->delete();
            Meeting::query()->delete();
            MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
        }
    }

    public function testStartDateFilter()
    {
        $user = $this->createUser();
        $beforeValues = ['latitude' => '1.1'];
        $afterValues = ['latitude' => '-1.1'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $change = Change::query()->where('id_bigint', $change->id_bigint)->first();
        $timestamp = strtotime($change->change_date);

        $startDate = date('Y-m-d', $timestamp);
        $this->get("/client_interface/json/?switcher=GetChanges&start_date=$startDate")
            ->assertStatus(200)
            ->assertJsonCount(1);

        $startDate = date('Y-m-d', $timestamp - 86400);
        $this->get("/client_interface/json/?switcher=GetChanges&start_date=$startDate")
            ->assertStatus(200)
            ->assertJsonCount(1);

        $startDate = date('Y-m-d', $timestamp + 86400);
        $this->get("/client_interface/json/?switcher=GetChanges&start_date=$startDate")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testEndDateFilter()
    {
        $user = $this->createUser();
        $beforeValues = ['latitude' => '1.1'];
        $afterValues = ['latitude' => '-1.1'];
        $change = $this->createChange($beforeValues, $afterValues, $user);
        $timestamp = strtotime($change->change_date);

        $endDate = date('Y-m-d', $timestamp);
        $this->get("/client_interface/json/?switcher=GetChanges&end_date=$endDate")
            ->assertStatus(200)
            ->assertJsonCount(1);

        $endDate = date('Y-m-d', $timestamp + 86400);
        $this->get("/client_interface/json/?switcher=GetChanges&end_date=$endDate")
            ->assertStatus(200)
            ->assertJsonCount(1);

        $endDate = date('Y-m-d', $timestamp - 86400);
        $this->get("/client_interface/json/?switcher=GetChanges&end_date=$endDate")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testMeetingIdFilter()
    {
        $user = $this->createUser();
        $beforeValues = ['latitude' => '1.1'];
        $afterValues = ['latitude' => '-1.1'];
        $change1 = $this->createChange($beforeValues, $afterValues, $user);
        $change2 = $this->createChange($beforeValues, $afterValues, $user);

        $changes = $this->get("/client_interface/json/?switcher=GetChanges&meeting_id=$change1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->json();
        $this->assertEquals(strval($change1->id_bigint), $changes[0]['change_id']);

        $changes = $this->get("/client_interface/json/?switcher=GetChanges&meeting_id=$change2->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->json();
        $this->assertEquals(strval($change2->id_bigint), $changes[0]['change_id']);

        $missingId = $change2->id_bigint + 1;
        $this->get("/client_interface/json/?switcher=GetChanges&meeting_id=$missingId")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testServiceBodyIdFilter()
    {
        $user = $this->createUser();
        $sb1 = $this->createServiceBody('1');
        $sb2 = $this->createServiceBody('1', $sb1->id_bigint);
        $sb3 = $this->createServiceBody('1', $sb2->id_bigint);
        $this->createChange(null, ['service_body_bigint' => strval($sb1->id_bigint)], $user);
        $this->createChange(null, ['service_body_bigint' => strval($sb2->id_bigint)], $user);
        $this->createChange(null, ['service_body_bigint' => strval($sb3->id_bigint)], $user);

        $this->get("/client_interface/json/?switcher=GetChanges&service_body_id=$sb1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(3);

        $this->get("/client_interface/json/?switcher=GetChanges&service_body_id=$sb2->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(2);

        $this->get("/client_interface/json/?switcher=GetChanges&service_body_id=$sb3->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }
}
