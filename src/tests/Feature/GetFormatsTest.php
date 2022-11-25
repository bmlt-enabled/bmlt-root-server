<?php

namespace Tests\Feature;

use App\LegacyConfig;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\RootServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetFormatsTest extends TestCase
{
    use RefreshDatabase;

    private function createRootServer(int $sourceId, string $name = 'test', string $url = 'https://test.com'): RootServer
    {
        return RootServer::create([
            'source_id' => $sourceId,
            'name' => $name,
            'url' => $url
        ]);
    }

    private function createFormat1(string $langEnum = 'en')
    {
        return $this->createFormat(1, 'O1', 'Open1', 'desc1', $langEnum, 'worldid');
    }

    private function createFormat2(string $langEnum = 'en')
    {
        return $this->createFormat(2, 'C2', 'Closed2', 'desc2', $langEnum, 'worldid');
    }

    private function createFormat3(string $langEnum = 'en')
    {
        return $this->createFormat(3, 'C3', 'Closed3', 'desc3', $langEnum, 'worldid');
    }

    private function createFormat(int $sharedId, string $keyString, string $nameString, string $description = null, string $langEnum = 'en', string $worldId = null, string $formatTypeEnum = 'FC')
    {
        return Format::create([
            'shared_id_bigint' => $sharedId,
            'key_string' => $keyString,
            'name_string' => $nameString,
            'lang_enum' => $langEnum,
            'description_string' => $description,
            'worldid_mixed' => $worldId,
            'format_type_enum' => $formatTypeEnum,
        ]);
    }

    private function createMeeting(array $formatIds)
    {
        return Meeting::create([
            'service_body_bigint' => 1,
            'formats' => implode(',', $formatIds),
        ]);
    }

    private function allFormatsInArray($expectedItems, $array): bool
    {
        foreach ($expectedItems as $item) {
            if (!in_array([
                'key_string' => $item->key_string,
                'name_string' => $item->name_string ?? '',
                'description_string' => $item->description_string ?? '',
                'lang' => $item->lang_enum,
                'id' => (string)$item->shared_id_bigint,
                'world_id' => $item->worldid_mixed ?? '',
                'format_type_enum' => $item->format_type_enum ?? '',
                'root_server_uri' => 'http://localhost',
            ], $array)) {
                return false;
            }
        }

        return true;
    }

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    public function testJsonp()
    {
        Format::query()->delete();
        $response = $this->get('/client_interface/jsonp/?switcher=GetFormats&callback=asdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
        $this->assertEquals('/**/asdf([]);', $response->content());
    }

    public function testNone()
    {
        Format::query()->delete();
        $this->createFormat1();
        $this->get('/client_interface/json/?switcher=GetFormats')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson([]);
    }

    public function testNoneShowAll()
    {
        Format::query()->delete();
        $this->get('/client_interface/json/?switcher=GetFormats&show_all=1')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson([]);
    }

    public function testOneUsed()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $this->createFormat2();
        $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint]);
        $response = $this->get('/client_interface/json/?switcher=GetFormats')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(1)
            ->json();
        $expected = [$format1];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testMultipleUsed()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint, $format2->shared_id_bigint]);
        $this->createMeeting([$format1->shared_id_bigint]);
        $response = $this->get('/client_interface/json/?switcher=GetFormats')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(2)
            ->json();
        $expected = [$format1, $format2];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testOneShowAll()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $response = $this->get('/client_interface/json/?switcher=GetFormats&show_all=1')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(1)
            ->json();
        $expected = [$format1];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testMultipleShowAll()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $format3 = $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint, $format2->shared_id_bigint]);
        $this->createMeeting([$format1->shared_id_bigint]);
        $response = $this->get('/client_interface/json/?switcher=GetFormats&show_all=1')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(3)
            ->json();
        $expected = [$format1, $format2, $format3];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testOneKeyStrings()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $format3 = $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint]);
        $this->createMeeting([$format2->shared_id_bigint]);
        $this->createMeeting([$format3->shared_id_bigint]);
        $response = $this->get("/client_interface/json/?switcher=GetFormats&key_strings=$format1->key_string")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(1)
            ->json();
        $expected = [$format1];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testMultipleKeyStrings()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $format3 = $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint]);
        $this->createMeeting([$format2->shared_id_bigint]);
        $this->createMeeting([$format3->shared_id_bigint]);
        $response = $this->get("/client_interface/json/?switcher=GetFormats&key_strings[]=$format1->key_string&key_strings[]=$format2->key_string")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(2)
            ->json();
        $expected = [$format1, $format2];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testOneLangEnum()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1('it');
        $format2 = $this->createFormat2();
        $format3 = $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint]);
        $this->createMeeting([$format2->shared_id_bigint]);
        $this->createMeeting([$format3->shared_id_bigint]);
        $response = $this->get("/client_interface/json/?switcher=GetFormats&lang_enum=it")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(1)
            ->json();
        $expected = [$format1];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testMultipleLangEnums()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1('it');
        $format2 = $this->createFormat2();
        $format3 = $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint]);
        $this->createMeeting([$format2->shared_id_bigint]);
        $this->createMeeting([$format3->shared_id_bigint]);
        $response = $this->get("/client_interface/json/?switcher=GetFormats&lang_enum[]=it&lang_enum[]=en")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(3)
            ->json();
        $expected = [$format1, $format2, $format3];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testLangEnumAndKeyString()
    {
        Format::query()->delete();
        $format1 = $this->createFormat1('it');
        $format2 = $this->createFormat2('it');
        $format3 = $this->createFormat3();
        $this->createMeeting([$format1->shared_id_bigint]);
        $this->createMeeting([$format2->shared_id_bigint]);
        $this->createMeeting([$format3->shared_id_bigint]);
        $response = $this->get("/client_interface/json/?switcher=GetFormats&key_strings=$format1->key_string&lang_enum=it")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(1)
            ->json();
        $expected = [$format1];
        $this->assertTrue($this->allFormatsInArray($expected, $response));
    }

    public function testRootServerUriWhenAggregatorEnabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer = $this->createRootServer(1);
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $format1->rootServer()->associate($rootServer);
        $format1->save();
        $format1->refresh();
        $this->createMeeting([$format1->shared_id_bigint]);
        $response = $this->get("/client_interface/json/?switcher=GetFormats")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(1)
            ->json();
        $this->assertEquals($rootServer->url, $response[0]['root_server_uri']);
    }

    public function testRootServerIdWhenAggregatorEnabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer = $this->createRootServer(1);
        Format::query()->delete();
        $format1 = $this->createFormat1();
        $format1->rootServer()->associate($rootServer);
        $format1->save();
        $format1->refresh();
        $this->createMeeting([$format1->shared_id_bigint]);
        $response = $this->get("/client_interface/json/?switcher=GetFormats")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(1)
            ->json();
        $this->assertEquals($rootServer->id, $response[0]['root_server_id']);
    }
}
