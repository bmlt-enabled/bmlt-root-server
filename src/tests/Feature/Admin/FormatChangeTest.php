<?php

namespace Tests\Feature\Admin;

use App\Models\Change;
use App\Models\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class FormatChangeTest extends TestCase
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
                    $payload['type'] = FormatTypeConsts::COMDEF_TYPE_TO_TYPE_MAP[$format->format_type_enum];
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

    public function testCreateFormat()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [
            'worldId' => 'test',
            'type' => FormatTypeConsts::TYPE_OPEN_CLOSED,
            'translations' => [
                [
                    'key' => 'O1',
                    'name' => 'Open1',
                    'description' => 'Meeting is open to non-addicts.1',
                    'language' => 'en',
                ],
                [
                    'key' => 'O2',
                    'name' => 'Open2',
                    'description' => 'Meeting is open to non-addicts.2',
                    'language' => 'es',
                ],
            ],
        ];

        $format = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201)
            ->json();

        $changes = Change::all();
        $this->assertEquals(count($data['translations']), count($format['translations']));
        $this->assertEquals(count($data['translations']), $changes->count());

        foreach ($data['translations'] as $translation) {
            $change = $changes
                ->where('service_body_id_bigint', $format['id'])
                ->where('lang_enum', $translation['language'])
                ->first();
            $this->assertNotNull($change);
            $this->assertEquals($user->id_bigint, $change->user_id_bigint);
            $this->assertEquals($format['id'], $change->service_body_id_bigint);
            $this->assertEquals($translation['language'], $change->lang_enum);
            $this->assertEquals('c_comdef_format', $change->object_class_string);
            $this->assertNull($change->before_id_bigint);
            $this->assertNull($change->before_lang_enum);
            $this->assertEquals($format['id'], $change->after_id_bigint);
            $this->assertEquals($translation['language'], $change->after_lang_enum);
            $this->assertEquals('comdef_change_type_new', $change->change_type_enum);
            $this->assertNull($change->before_object);
            $this->assertNotNull($change->after_object);
            $afterObject = $change->after_object;
            $this->assertEquals($format['id'], $afterObject[0]);
            $this->assertEquals(FormatTypeConsts::TYPE_TO_COMDEF_TYPE_MAP[$format['type']], $afterObject[1]);
            $this->assertEquals($translation['key'], $afterObject[2]);
            $this->assertNull($afterObject[3]);
            $this->assertEquals($format['worldId'], $afterObject[4]);
            $this->assertEquals($translation['language'], $afterObject[5]);
            $this->assertEquals($translation['name'], $afterObject[6]);
            $this->assertEquals($translation['description'], $afterObject[7]);
        }
    }

    public function testTranslationsModifiedOnly()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);
        foreach ($data['translations'] as $key => $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
            $data['translations'][$key] = $translation;
        }

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        $changes = Change::all();
        $this->assertEquals(count($formats), $changes->count());

        foreach ($formats as $oldTranslation) {
            $newTranslation = Format::query()->where('shared_id_bigint', $oldTranslation->shared_id_bigint)->where('lang_enum', $oldTranslation->lang_enum)->first();
            $change = $changes
                ->where('service_body_id_bigint', $oldTranslation->shared_id_bigint)
                ->where('lang_enum', $oldTranslation->lang_enum)
                ->first();
            $this->assertNotNull($change);
            $this->assertEquals($user->id_bigint, $change->user_id_bigint);
            $this->assertEquals($oldTranslation->shared_id_bigint, $change->service_body_id_bigint);
            $this->assertEquals($oldTranslation->lang_enum, $change->lang_enum);
            $this->assertEquals('c_comdef_format', $change->object_class_string);
            $this->assertEquals($oldTranslation->shared_id_bigint, $change->before_id_bigint);
            $this->assertEquals($oldTranslation->lang_enum, $change->before_lang_enum);
            $this->assertEquals($newTranslation->shared_id_bigint, $change->after_id_bigint);
            $this->assertEquals($newTranslation->lang_enum, $change->after_lang_enum);
            $this->assertEquals('comdef_change_type_change', $change->change_type_enum);
            $this->assertNotNull($change->before_object);
            $beforeObject = $change->before_object;
            $this->assertEquals($oldTranslation->shared_id_bigint, $beforeObject[0]);
            $this->assertEquals($oldTranslation->format_type_enum, $beforeObject[1]);
            $this->assertEquals($oldTranslation->key_string, $beforeObject[2]);
            $this->assertNull($beforeObject[3]);
            $this->assertEquals($oldTranslation->worldid_mixed, $beforeObject[4]);
            $this->assertEquals($oldTranslation->lang_enum, $beforeObject[5]);
            $this->assertEquals($oldTranslation->name_string, $beforeObject[6]);
            $this->assertEquals($oldTranslation->description_string, $beforeObject[7]);
            $this->assertNotNull($change->after_object);
            $afterObject = $change->after_object;
            $this->assertEquals($newTranslation->shared_id_bigint, $afterObject[0]);
            $this->assertEquals($newTranslation->format_type_enum, $afterObject[1]);
            $this->assertEquals($newTranslation->key_string, $afterObject[2]);
            $this->assertNull($afterObject[3]);
            $this->assertEquals($newTranslation->worldid_mixed, $afterObject[4]);
            $this->assertEquals($newTranslation->lang_enum, $afterObject[5]);
            $this->assertEquals($newTranslation->name_string, $afterObject[6]);
            $this->assertEquals($newTranslation->description_string, $afterObject[7]);
        }
    }

    public function testTranslationsAddedOne()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();
        $data = $this->toPayload($formats);
        foreach ($data['translations'] as $key => $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
            $data['translations'][$key] = $translation;
        }
        $data['translations'][] = [
            'key' => 'N',
            'name' => 'New',
            'description' => 'New Description',
            'language' => 'new',
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        $changes = Change::all();
        $this->assertEquals(3, $changes->count());

        // created
        $change = $changes
            ->where('service_body_id_bigint', $formats[0]->shared_id_bigint)
            ->where('lang_enum', $data['translations'][2]['language'])
            ->first();
        $this->assertNotNull($change);
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($formats[0]->shared_id_bigint, $change->service_body_id_bigint);
        $this->assertEquals($data['translations'][2]['language'], $change->lang_enum);
        $this->assertEquals('c_comdef_format', $change->object_class_string);
        $this->assertNull($change->before_id_bigint);
        $this->assertNull($change->before_lang_enum);
        $this->assertEquals($formats[0]->shared_id_bigint, $change->after_id_bigint);
        $this->assertEquals($data['translations'][2]['language'], $change->after_lang_enum);
        $this->assertEquals('comdef_change_type_new', $change->change_type_enum);
        $this->assertNull($change->before_object);
        $this->assertNotNull($change->after_object);
        $afterObject = $change->after_object;
        $this->assertEquals($formats[0]->shared_id_bigint, $afterObject[0]);
        $this->assertEquals($formats[0]->format_type_enum, $afterObject[1]);
        $this->assertEquals($data['translations'][2]['key'], $afterObject[2]);
        $this->assertNull($afterObject[3]);
        $this->assertEquals($formats[0]->worldid_mixed, $afterObject[4]);
        $this->assertEquals($data['translations'][2]['language'], $afterObject[5]);
        $this->assertEquals($data['translations'][2]['name'], $afterObject[6]);
        $this->assertEquals($data['translations'][2]['description'], $afterObject[7]);

        // changes
        foreach ($formats as $oldTranslation) {
            $newTranslation = Format::query()->where('shared_id_bigint', $oldTranslation->shared_id_bigint)->where('lang_enum', $oldTranslation->lang_enum)->first();
            $change = $changes
                ->where('service_body_id_bigint', $oldTranslation->shared_id_bigint)
                ->where('lang_enum', $oldTranslation->lang_enum)
                ->first();
            $this->assertNotNull($change);
            $this->assertEquals($user->id_bigint, $change->user_id_bigint);
            $this->assertEquals($oldTranslation->shared_id_bigint, $change->service_body_id_bigint);
            $this->assertEquals($oldTranslation->lang_enum, $change->lang_enum);
            $this->assertEquals('c_comdef_format', $change->object_class_string);
            $this->assertEquals($oldTranslation->shared_id_bigint, $change->before_id_bigint);
            $this->assertEquals($oldTranslation->lang_enum, $change->before_lang_enum);
            $this->assertEquals($newTranslation->shared_id_bigint, $change->after_id_bigint);
            $this->assertEquals($newTranslation->lang_enum, $change->after_lang_enum);
            $this->assertEquals('comdef_change_type_change', $change->change_type_enum);
            $this->assertNotNull($change->before_object);
            $beforeObject = $change->before_object;
            $this->assertEquals($oldTranslation->shared_id_bigint, $beforeObject[0]);
            $this->assertEquals($oldTranslation->format_type_enum, $beforeObject[1]);
            $this->assertEquals($oldTranslation->key_string, $beforeObject[2]);
            $this->assertNull($beforeObject[3]);
            $this->assertEquals($oldTranslation->worldid_mixed, $beforeObject[4]);
            $this->assertEquals($oldTranslation->lang_enum, $beforeObject[5]);
            $this->assertEquals($oldTranslation->name_string, $beforeObject[6]);
            $this->assertEquals($oldTranslation->description_string, $beforeObject[7]);
            $this->assertNotNull($change->after_object);
            $afterObject = $change->after_object;
            $this->assertEquals($newTranslation->shared_id_bigint, $afterObject[0]);
            $this->assertEquals($newTranslation->format_type_enum, $afterObject[1]);
            $this->assertEquals($newTranslation->key_string, $afterObject[2]);
            $this->assertNull($afterObject[3]);
            $this->assertEquals($newTranslation->worldid_mixed, $afterObject[4]);
            $this->assertEquals($newTranslation->lang_enum, $afterObject[5]);
            $this->assertEquals($newTranslation->name_string, $afterObject[6]);
            $this->assertEquals($newTranslation->description_string, $afterObject[7]);
        }
    }

    public function testTranslationsRemovedOne()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        $removedTranslation = $formats[count($formats) - 1];
        unset($formats[count($formats) - 1]);

        $data = $this->toPayload($formats);
        foreach ($data['translations'] as $key => $translation) {
            $translation['key'] .= 'updated';
            $translation['name'] .= 'updated';
            $translation['description'] .= 'updated';
            $data['translations'][$key] = $translation;
        }

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/{$formats[0]->shared_id_bigint}", $data)
            ->assertStatus(204);

        $changes = Change::all();
        $this->assertEquals(2, $changes->count());

        // deleted
        $change = $changes
            ->where('service_body_id_bigint', $removedTranslation->shared_id_bigint)
            ->where('lang_enum', $removedTranslation->lang_enum)
            ->first();
        $this->assertNotNull($change);
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($removedTranslation->shared_id_bigint, $change->service_body_id_bigint);
        $this->assertEquals($removedTranslation->lang_enum, $change->lang_enum);
        $this->assertEquals('c_comdef_format', $change->object_class_string);
        $this->assertEquals($removedTranslation->shared_id_bigint, $change->before_id_bigint);
        $this->assertEquals($removedTranslation->lang_enum, $change->before_lang_enum);
        $this->assertNull($change->after_id_bigint);
        $this->assertNull($change->after_lang_enum);
        $this->assertEquals('comdef_change_type_delete', $change->change_type_enum);
        $this->assertNotNull($change->before_object);
        $this->assertNull($change->after_object);
        $beforeObject = $change->before_object;
        $this->assertEquals($removedTranslation->shared_id_bigint, $beforeObject[0]);
        $this->assertEquals($removedTranslation->format_type_enum, $beforeObject[1]);
        $this->assertEquals($removedTranslation->key_string, $beforeObject[2]);
        $this->assertNull($beforeObject[3]);
        $this->assertEquals($removedTranslation->worldid_mixed, $beforeObject[4]);
        $this->assertEquals($removedTranslation->lang_enum, $beforeObject[5]);
        $this->assertEquals($removedTranslation->name_string, $beforeObject[6]);
        $this->assertEquals($removedTranslation->description_string, $beforeObject[7]);

        // changed
        $newTranslation = Format::query()->where('shared_id_bigint', $formats[0]->shared_id_bigint)->where('lang_enum', $formats[0]->lang_enum)->first();
        $change = $changes
            ->where('service_body_id_bigint', $formats[0]->shared_id_bigint)
            ->where('lang_enum', $formats[0]->lang_enum)
            ->first();
        $this->assertNotNull($change);
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($formats[0]->shared_id_bigint, $change->service_body_id_bigint);
        $this->assertEquals($formats[0]->lang_enum, $change->lang_enum);
        $this->assertEquals('c_comdef_format', $change->object_class_string);
        $this->assertEquals($formats[0]->shared_id_bigint, $change->before_id_bigint);
        $this->assertEquals($formats[0]->lang_enum, $change->before_lang_enum);
        $this->assertEquals($newTranslation->shared_id_bigint, $change->after_id_bigint);
        $this->assertEquals($newTranslation->lang_enum, $change->after_lang_enum);
        $this->assertEquals('comdef_change_type_change', $change->change_type_enum);
        $this->assertNotNull($change->before_object);
        $beforeObject = $change->before_object;
        $this->assertEquals($formats[0]->shared_id_bigint, $beforeObject[0]);
        $this->assertEquals($formats[0]->format_type_enum, $beforeObject[1]);
        $this->assertEquals($formats[0]->key_string, $beforeObject[2]);
        $this->assertNull($beforeObject[3]);
        $this->assertEquals($formats[0]->worldid_mixed, $beforeObject[4]);
        $this->assertEquals($formats[0]->lang_enum, $beforeObject[5]);
        $this->assertEquals($formats[0]->name_string, $beforeObject[6]);
        $this->assertEquals($formats[0]->description_string, $beforeObject[7]);
        $this->assertNotNull($change->after_object);
        $afterObject = $change->after_object;
        $this->assertEquals($newTranslation->shared_id_bigint, $afterObject[0]);
        $this->assertEquals($newTranslation->format_type_enum, $afterObject[1]);
        $this->assertEquals($newTranslation->key_string, $afterObject[2]);
        $this->assertNull($afterObject[3]);
        $this->assertEquals($newTranslation->worldid_mixed, $afterObject[4]);
        $this->assertEquals($newTranslation->lang_enum, $afterObject[5]);
        $this->assertEquals($newTranslation->name_string, $afterObject[6]);
        $this->assertEquals($newTranslation->description_string, $afterObject[7]);
    }

    public function testDeleteFormat()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $formats = $this->createFormats();

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/{$formats[0]->shared_id_bigint}")
            ->assertStatus(204);

        $changes = Change::all();
        $this->assertEquals(count($formats), $changes->count());

        foreach ($formats as $translation) {
            $change = $changes
                ->where('service_body_id_bigint', $translation->shared_id_bigint)
                ->where('lang_enum', $translation->lang_enum)
                ->first();
            $this->assertNotNull($change);
            $this->assertEquals($user->id_bigint, $change->user_id_bigint);
            $this->assertEquals($translation->shared_id_bigint, $change->service_body_id_bigint);
            $this->assertEquals($translation->lang_enum, $change->lang_enum);
            $this->assertEquals('c_comdef_format', $change->object_class_string);
            $this->assertEquals($translation->shared_id_bigint, $change->before_id_bigint);
            $this->assertEquals($translation->lang_enum, $change->before_lang_enum);
            $this->assertNull($change->after_id_bigint);
            $this->assertNull($change->after_lang_enum);
            $this->assertEquals('comdef_change_type_delete', $change->change_type_enum);
            $this->assertNotNull($change->before_object);
            $this->assertNull($change->after_object);
            $beforeObject = $change->before_object;
            $this->assertEquals($translation->shared_id_bigint, $beforeObject[0]);
            $this->assertEquals($translation->format_type_enum, $beforeObject[1]);
            $this->assertEquals($translation->key_string, $beforeObject[2]);
            $this->assertNull($beforeObject[3]);
            $this->assertEquals($translation->worldid_mixed, $beforeObject[4]);
            $this->assertEquals($translation->lang_enum, $beforeObject[5]);
            $this->assertEquals($translation->name_string, $beforeObject[6]);
            $this->assertEquals($translation->description_string, $beforeObject[7]);
        }
    }
}
