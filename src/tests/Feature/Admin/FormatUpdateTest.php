<?php

namespace Tests\Feature\Admin;

use App\Models\Format;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class FormatUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function toPayload(Collection $formats): array
    {
        $payload = [
            'worldId' => null,
            'type' => null,
            'translations' => [],
        ];

        foreach ($formats as $format) {
            if (is_null($payload['worldId'])) {
                if (!empty($format->worldid_mixed)) {
                    $payload['worldId'] = $format->worldid_mixed;
                }
            }

            if (is_null($payload['type'])) {
                if (!empty($format->format_type_enum)) {
                    $payload['type'] = Format::COMDEF_TYPE_TO_TYPE_MAP[$format->format_type_enum];
                }
            }

            $payload['translations'][] = [
                'key' => $format->key_string,
                'name' => $format->name_string,
                'description' => $format->description_string,
                'language' => $format->lang_enum,
            ];
        }

        return $payload;
    }

    private function createFormats(): Collection
    {
        $nextId = Format::query()->max('shared_id_bigint') + 1;
        return collect(['en', 'es'])->map(function ($lang) use ($nextId) {
            return Format::create([
                'shared_id_bigint' => $nextId,
                'key_string' => 'O' . $lang,
                'name_string' => 'Open' . $lang,
                'description_string' => 'Open Description' . $lang,
                'worldid_mixed' => 'OPEN' . $lang,
                'format_type_enum' => 'FC3',
                'lang_enum' => $lang,
            ]);
        });
    }

    public function testUpdateFormatNoTranslationsRemoved()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        $data['worldId'] .= 'updated';
        $data['type'] = Format::TYPE_MEETING_FORMAT;
        foreach ($data['translations'] as $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
        }

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        foreach ($formats as $format) {
            $translation = collect($data['translations'])->firstWhere('language', $format->lang_enum);
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals($data['worldId'], $format->worldid_mixed);
            $this->assertEquals(Format::TYPE_TO_COMDEF_TYPE_MAP[$data['type']], $format->format_type_enum);
            $this->assertEquals($translation['key'], $format->key_string);
            $this->assertEquals($translation['name'], $format->name_string);
            $this->assertEquals($translation['description'], $format->description_string);
        }
    }

    public function testUpdateFormatOneTranslationRemoved()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        $removedTranslation = $formats[count($formats) - 1];
        unset($formats[count($formats) - 1]);

        $data = $this->toPayload($formats);

        $data['worldId'] .= 'updated';
        $data['type'] = Format::TYPE_MEETING_FORMAT;
        foreach ($data['translations'] as $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
        }

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        $this->assertFalse(
            Format::query()
                ->where('shared_id_bigint', $removedTranslation->shared_id_bigint)
                ->where('lang_enum', $removedTranslation->lang_enum)
                ->exists()
        );

        foreach ($formats as $format) {
            $translation = collect($data['translations'])->firstWhere('language', $format->lang_enum);
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals($data['worldId'], $format->worldid_mixed);
            $this->assertEquals(Format::TYPE_TO_COMDEF_TYPE_MAP[$data['type']], $format->format_type_enum);
            $this->assertEquals($translation['key'], $format->key_string);
            $this->assertEquals($translation['name'], $format->name_string);
            $this->assertEquals($translation['description'], $format->description_string);
        }
    }

    public function testUpdateFormatOneTranslationAdded()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        $data['worldId'] .= 'updated';
        $data['type'] = Format::TYPE_MEETING_FORMAT;
        foreach ($data['translations'] as $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
        }

        $data['translations'][] = [
            'key' => 'N',
            'name' => 'New',
            'description' => 'This translation is new.',
            'language' => 'test',
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        foreach ($formats as $format) {
            $translation = collect($data['translations'])->firstWhere('language', $format->lang_enum);
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals($data['worldId'], $format->worldid_mixed);
            $this->assertEquals(Format::TYPE_TO_COMDEF_TYPE_MAP[$data['type']], $format->format_type_enum);
            $this->assertEquals($translation['key'], $format->key_string);
            $this->assertEquals($translation['name'], $format->name_string);
            $this->assertEquals($translation['description'], $format->description_string);
        }
    }

    public function testUpdateFormatValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        // it can't be longer than 30 characters
        $data['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be than 30 characters
        $data['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        // it can be null
        $data['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testUpdateFormatValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        // it can't be an invalid value
        $data['type'] = 'asdf';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be a valid value
        foreach (array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP) as $validType) {
            $data['type'] = $validType;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
                ->assertStatus(204);
        }

        // it can be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testUpdateFormatValidateTranslations()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        // it is required
        unset($data['translations']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['translations'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be non-empty
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testUpdateFormatValidateKey()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        // it is required
        unset($data['translations'][0]['key']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['key'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['key'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 30
        $data['translations'][0]['key'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 30
        $data['translations'][0]['key'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testUpdateFormatValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        // it is required
        unset($data['translations'][0]['name']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['name'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 255
        $data['translations'][0]['name'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 255
        $data['translations'][0]['name'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testUpdateFormatValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        // it is required
        unset($data['translations'][0]['description']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['description'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['description'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 255
        $data['translations'][0]['description'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 255
        $data['translations'][0]['description'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testUpdateFormatValidateLanguage()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);

        // it is required
        unset($data['translations'][0]['language']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['language'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['language'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 7
        $data['translations'][0]['language'] = str_repeat('t', 8);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 7
        $data['translations'][0]['language'] = str_repeat('t', 7);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }
}
