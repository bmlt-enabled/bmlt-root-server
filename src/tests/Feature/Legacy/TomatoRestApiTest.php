<?php

namespace Tests\Feature;

use App\LegacyConfig;
use App\Models\Format;
use App\Models\RootServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TomatoRestApiTest extends TestCase
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
        return $this->createFormat(1, 'O1', 'Open1', 'desc1', $langEnum, 'worldid1');
    }

    private function createFormat2(string $langEnum = 'en')
    {
        return $this->createFormat(2, 'C2', 'Closed2', 'desc2', $langEnum, 'worldid2');
    }

    private function createFormat3(string $langEnum = 'en')
    {
        return $this->createFormat(3, 'C3', 'Closed3', 'desc3', $langEnum, 'worldid3');
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

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    public function testFormatsWithAggregatorDisabled()
    {
        Format::query()->delete();
        $this->get('/rest/v1/formats')->assertStatus(404);
    }

    public function testFormatsWithAggregatorEnabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        Format::query()->delete();
        $format1English = $this->createFormat1('en');
        $format1English->source_id = 123;
        $format1English->save();
        $format1Spanish = $this->createFormat1('es');
        $format1Spanish->source_id = 123;
        $format1Spanish->save();
        $format2English = $this->createFormat2('en');
        $format2English->source_id = 456;
        $format2English->save();
        $format2Spanish = $this->createFormat2('es');
        $format2Spanish->source_id = 456;
        $format2Spanish->save();
        $this->createFormat3();
        $response = collect($this->get("/rest/v1/formats/?id__in=$format1English->shared_id_bigint,$format2English->shared_id_bigint")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(2)
            ->json());

        $format = $response->firstWhere(fn ($f) => str_ends_with($f['url'], "/$format1English->shared_id_bigint/"));
        $this->assertNotNull($format);
        $this->assertEquals($format1English->source_id, $format['source_id']);
        $this->assertEquals($format1English->format_type_enum, $format['type']);
        $this->assertEquals($format1English->worldid_mixed, $format['world_id']);
        $this->assertEquals(2, count($format['translatedformats']));
        $translation = collect($format['translatedformats'])->firstWhere(fn ($t) => $t['language'] == $format1English->lang_enum);
        $this->assertNotNull($translation);
        $this->assertEquals($format1English->key_string, $translation['key_string']);
        $this->assertEquals($format1English->name_string, $translation['name']);
        $this->assertEquals($format1English->description_string, $translation['description']);
        $this->assertEquals($format1English->lang_enum, $translation['language']);
        $translation = collect($format['translatedformats'])->firstWhere(fn ($t) => $t['language'] == $format1Spanish->lang_enum);
        $this->assertNotNull($translation);
        $this->assertEquals($format1Spanish->key_string, $translation['key_string']);
        $this->assertEquals($format1Spanish->name_string, $translation['name']);
        $this->assertEquals($format1Spanish->description_string, $translation['description']);
        $this->assertEquals($format1Spanish->lang_enum, $translation['language']);
        $format = $response->firstWhere(fn ($f) => str_ends_with($f['url'], "/$format2English->shared_id_bigint/"));
        $this->assertNotNull($format);
        $this->assertEquals($format2English->source_id, $format['source_id']);
        $this->assertEquals($format2English->format_type_enum, $format['type']);
        $this->assertEquals($format2English->worldid_mixed, $format['world_id']);
        $this->assertEquals(2, count($format['translatedformats']));
        $translation = collect($format['translatedformats'])->firstWhere(fn ($t) => $t['language'] == $format2English->lang_enum);
        $this->assertNotNull($translation);
        $this->assertEquals($format2English->key_string, $translation['key_string']);
        $this->assertEquals($format2English->name_string, $translation['name']);
        $this->assertEquals($format2English->description_string, $translation['description']);
        $this->assertEquals($format2English->lang_enum, $translation['language']);
        $translation = collect($format['translatedformats'])->firstWhere(fn ($t) => $t['language'] == $format2Spanish->lang_enum);
        $this->assertNotNull($translation);
        $this->assertEquals($format2Spanish->key_string, $translation['key_string']);
        $this->assertEquals($format2Spanish->name_string, $translation['name']);
        $this->assertEquals($format2Spanish->description_string, $translation['description']);
        $this->assertEquals($format2Spanish->lang_enum, $translation['language']);
    }

    public function testFormatWithAggregatorDisabled()
    {
        Format::query()->delete();
        $format1English = $this->createFormat1();
        $this->get("/rest/v1/formats/$format1English->shared_id_bigint/")->assertStatus(404);
    }

    public function testFormatWithAggregatorEnabledNotFound()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        Format::query()->delete();
        $this->get("/rest/v1/formats/1/")->assertStatus(404);
    }

    public function testFormatWithAggregatorEnabledFound()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        Format::query()->delete();
        $format1English = $this->createFormat1();
        $this->get("/rest/v1/formats/$format1English->shared_id_bigint/")->assertStatus(200);
    }
}
