<?php

namespace Tests\Feature\Admin;

use App\Models\Format;

use Illuminate\Foundation\Testing\RefreshDatabase;

class FormatCreateTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'worldId' => 'test',
            'type' => FormatTypeConsts::TYPE_OPEN_CLOSED,
            'translations' => [[
                'key' => 'O',
                'name' => 'Open',
                'description' => 'Meeting is open to non-addicts.',
                'language' => 'en',
            ]],
        ];
    }

    public function testCreateFormatWithNulls()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();
        $data['worldId'] = null;
        $data['type'] = null;

        $format = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201)
            ->json();

        $this->assertNull($format['worldId']);
        $this->assertNull($format['type']);
    }

    public function testCreateFormatWithEmptyString()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();
        $data['worldId'] = '';

        $format = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201)
            ->json();

        $this->assertNull($format['worldId']);
    }

    public function testCreateFormatNoNulls()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();
        $translation = $data['translations'][0];
        $data['translations'] = collect([1,2,3,4,5])
            ->map(function ($i) use ($translation) {
                $copy = array_merge($translation);
                foreach ($copy as $key => $value) {
                    $copy[$key] = $value . $i;
                }
                return $copy;
            })
            ->toArray();

        $nextId = Format::query()->max('shared_id_bigint') + 1;

        $format = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201)
            ->json();

        $this->assertEquals($nextId, $format['id']);
        $this->assertEquals($data['worldId'], $format['worldId']);
        $this->assertEquals($data['type'], $format['type']);
        $this->assertEquals(count($data['translations']), count($format['translations']));
        foreach ([0,1,2,3,4] as $i) {
            $this->assertEquals($data['translations'][$i], $format['translations'][$i]);
        }
    }

    public function testCreateFormatValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it can't be longer than 30 characters
        $data['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can be than 30 characters
        $data['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);

        // it can be null
        $data['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);
    }

    public function testCreateFormatValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it can't be an invalid value
        $data['type'] = 'asdf';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can be a valid value
        foreach (array_keys(FormatTypeConsts::TYPE_TO_COMDEF_TYPE_MAP) as $validType) {
            $data['type'] = $validType;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/formats', $data)
                ->assertStatus(201);
        }

        // it can be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);
    }

    public function testCreateFormatValidateTranslations()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['translations']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be empty
        $data['translations'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't have duplicate translations
        $data['translations'] = [
            [
                'key' => 'O',
                'name' => 'Open',
                'description' => 'Meeting is open to non-addicts.',
                'language' => 'en',
            ],
            [
                'key' => 'O',
                'name' => 'Open',
                'description' => 'Meeting is open to non-addicts.',
                'language' => 'en',
            ],
        ];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can be non-empty
        $data['translations'] = [[
            'key' => 'O',
            'name' => 'Open',
            'description' => 'Meeting is open to non-addicts.',
            'language' => 'en',
        ]];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);
    }

    public function testCreateFormatValidateKey()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['translations'][0]['key']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['key'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['key'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be a reserved format key
        foreach (['VM', 'TC', 'HY'] as $key) {
            $data['translations'][0]['key'] = $key;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/formats', $data)
                ->assertStatus(422);
        }

        // it can't be longer than 10
        $data['translations'][0]['key'] = str_repeat('t', 11);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can be 10
        $data['translations'][0]['key'] = str_repeat('t', 10);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);
    }

    public function testCreateFormatValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['translations'][0]['name']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['name'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be longer than 255
        $data['translations'][0]['name'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can be 255
        $data['translations'][0]['name'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);
    }

    public function testCreateFormatValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['translations'][0]['description']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['description'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['description'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be longer than 255
        $data['translations'][0]['description'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can be 255
        $data['translations'][0]['description'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);
    }

    public function testCreateFormatValidateLanguage()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['translations'][0]['language']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be null
        $data['translations'][0]['language'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be an empty string
        $data['translations'][0]['language'] = '';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can't be longer than 7
        $data['translations'][0]['language'] = str_repeat('t', 8);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(422);

        // it can be 7
        $data['translations'][0]['language'] = str_repeat('t', 7);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/formats', $data)
            ->assertStatus(201);
    }
}
