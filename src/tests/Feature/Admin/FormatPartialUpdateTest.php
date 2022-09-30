<?php

namespace Tests\Feature\Admin;

use App\Models\Format;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class FormatPartialUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function toPayload(Collection $formats, string $fieldName): array
    {
        $payload = [];

        foreach ($formats as $format) {
            if ($fieldName == 'worldId') {
                if (!array_key_exists('worldId', $payload)) {
                    if (!empty($format->worldid_mixed)) {
                        $payload['worldId'] = $format->worldid_mixed;
                    }
                }
            }

            if ($fieldName == 'type') {
                if (!array_key_exists('type', $payload)) {
                    if (!empty($format->format_type_enum)) {
                        $payload['type'] = Format::COMDEF_TYPE_TO_TYPE_MAP[$format->format_type_enum];
                    }
                }
            }

            if ($fieldName == 'translations') {
                $payload['translations'][] = [
                    'key' => $format->key_string,
                    'name' => $format->name_string,
                    'description' => $format->description_string,
                    'language' => $format->lang_enum,
                ];
            }
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
                'worldid_mixed' => 'OPEN',
                'format_type_enum' => 'FC3',
                'lang_enum' => $lang,
            ]);
        });
    }

    public function testPartialUpdateFormatNonTranslationFields()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        $data = ['worldId' => null];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
        foreach ($formats as $format) {
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertNull($format->worldid_mixed);
        }

        $data = ['worldId' => ''];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
        foreach ($formats as $format) {
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertNull($format->worldid_mixed);
        }

        $data = ['worldId' => 'test'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
        foreach ($formats as $format) {
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals($data['worldId'], $format->worldid_mixed);
        }

        $data = ['type' => null];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
        foreach ($formats as $format) {
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertNull($format->format_type_enum);
        }

        $data = ['type' => ''];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
        foreach ($formats as $format) {
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertNull($format->format_type_enum);
        }

        $data = ['type' => Format::TYPE_MEETING_FORMAT];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
        foreach ($formats as $format) {
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals(Format::TYPE_TO_COMDEF_TYPE_MAP[$data['type']], $format->format_type_enum);
        }

        $data = ['type' => Format::TYPE_MEETING_FORMAT];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
        foreach ($formats as $format) {
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals(Format::TYPE_TO_COMDEF_TYPE_MAP[$data['type']], $format->format_type_enum);
        }
    }

    public function testPartialUpdateFormatTranslationsModifiedWithMainFields()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats, 'translations');

        $data['worldId'] = 'modified';
        $data['type'] = Format::TYPE_MEETING_FORMAT;
        foreach ($data['translations'] as $key => $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
            $data['translations'][$key] = $translation;
        }

        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
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

    public function testPartialUpdateFormatOnlyTranslationsModified()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats, 'translations');

        foreach ($data['translations'] as $key => $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
            $data['translations'][$key] = $translation;
        }

        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        foreach ($formats as $format) {
            $translation = collect($data['translations'])->firstWhere('language', $format->lang_enum);
            $oldWorldId = $format->worldid_mixed;
            $oldType = $format->format_type_enum;
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals($oldWorldId, $format->worldid_mixed);
            $this->assertEquals($oldType, $format->format_type_enum);
            $this->assertEquals($translation['key'], $format->key_string);
            $this->assertEquals($translation['name'], $format->name_string);
            $this->assertEquals($translation['description'], $format->description_string);
        }
    }

    public function testPartialUpdateFormatOnlyTranslationsAdded()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats, 'translations');

        foreach ($data['translations'] as $key => $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
            $data['translations'][$key] = $translation;
        }

        $data['translations'][] = [
            'key' => 'N',
            'name' => 'New',
            'description' => 'This translation is new.',
            'language' => 'test',
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        foreach ($data['translations'] as $translation) {
            $format = Format::query()->where('shared_id_bigint', $formats[0]->shared_id_bigint)->where('lang_enum', $translation['language'])->first();
            $this->assertEquals($formats[0]->worldid_mixed, $format->worldid_mixed);
            $this->assertEquals($formats[0]->format_type_enum, $format->format_type_enum);
            $this->assertEquals($translation['key'], $format->key_string);
            $this->assertEquals($translation['name'], $format->name_string);
            $this->assertEquals($translation['description'], $format->description_string);
        }
    }

    public function testPartialUpdateFormatOnlyTranslationsRemoved()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        $removedTranslation = $formats[count($formats) - 1];
        unset($formats[count($formats) - 1]);

        $data = $this->toPayload($formats, 'translations');

        foreach ($data['translations'] as $key => $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
            $data['translations'][$key] = $translation;
        }

        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        $this->assertFalse(
            Format::query()
                ->where('shared_id_bigint', $removedTranslation->shared_id_bigint)
                ->where('lang_enum', $removedTranslation->lang_enum)
                ->exists()
        );

        foreach ($formats as $format) {
            $translation = collect($data['translations'])->firstWhere('language', $format->lang_enum);
            $oldWorldId = $format->worldid_mixed;
            $oldType = $format->format_type_enum;
            $format = Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->where('lang_enum', $format->lang_enum)->first();
            $this->assertEquals($oldWorldId, $format->worldid_mixed);
            $this->assertEquals($oldType, $format->format_type_enum);
            $this->assertEquals($translation['key'], $format->key_string);
            $this->assertEquals($translation['name'], $format->name_string);
            $this->assertEquals($translation['description'], $format->description_string);
        }
    }

    public function testPartialUpdateFormatValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        // it can be an empty string
        $data['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        // it can be an empty string
        $data['worldId'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        // it can't be longer than 30
        $data['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 30
        $data['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        // it is not required
        $data = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateFormatValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        // it can be an empty string
        $data['type'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        // it can be null
        $data['type'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        // it can't be an invalid value
        $data['type'] = 'invalid';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can a valid value
        foreach (array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP) as $validType) {
            $data['type'] = $validType;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
                ->assertStatus(204);
        }

        // it is not required
        $data = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateFormatValidateTranslations()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        // it can't be an empty array
        $data['translations'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it is not required
        $data = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateFormatValidateKey()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        // it is required
        $data['translations'] = [[
            'name' => 'Open',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['translations'] = [[
            'key' => '',
            'name' => 'Open',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 30
        $data['translations'] = [[
            'key' => str_repeat('t', 31),
            'name' => 'Open',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 30
        $data['translations'] = [[
            'key' => str_repeat('t', 30),
            'name' => 'Open',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateFormatValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        // it is required
        $data['translations'] = [[
            'key' => 'O',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['translations'] = [[
            'key' => 'O',
            'name' => '',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 255
        $data['translations'] = [[
            'key' => 'O',
            'name' => str_repeat('t', 256),
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 255
        $data['translations'] = [[
            'key' => 'O',
            'name' => str_repeat('t', 255),
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateFormatValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        // it is required
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => '',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 255
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => str_repeat('t', 256),
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 255
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => str_repeat('t', 255),
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateFormatValidateLanguage()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        // it is required
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => 'This is a valid description',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => 'This is a valid description',
            'language' => '',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can't be longer than 7
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => 'This is a valid description',
            'language' => str_repeat('t', 8),
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(422);

        // it can be 7
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => 'This is a valid description',
            'language' => str_repeat('t', 7),
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);
    }
}
