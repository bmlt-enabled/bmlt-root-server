<?php

namespace Tests\Feature\Admin;

use App\Models\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormatShowTest extends TestCase
{
    use RefreshDatabase;

    private function createFormat(array $values): Format
    {
        return Format::create(array_merge([
            'shared_id_bigint' => Format::query()->max('shared_id_bigint') + 1,
            'key_string' => 'T',
            'worldid_mixed' => 'test',
            'lang_enum' => 'en',
            'name_string' => 'test',
            'description_string' => 'test',
            'format_type_enum' => 'FC1',
        ], $values));
    }

    public function testShowFormatWorldIdNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['worldid_mixed' => 'blah']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['worldId']);
        $this->assertEquals($format->worldid_mixed, $data['worldId']);
    }

    public function testShowFormatWorldIdNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['worldid_mixed' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['worldId']);
    }

    public function testShowFormatTypeNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['format_type_enum' => 'FC3']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['type']);
        $this->assertEquals(FormatUpdateTest::COMDEF_TYPE_TO_TYPE_MAP[$format->format_type_enum], $data['type']);
    }

    public function testShowFormatTypeNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['format_type_enum' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['type']);
    }

    public function testShowFormatKeyNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['key_string' => 'blah']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['translations'][0]['key']);
        $this->assertEquals($format->key_string, $data['translations'][0]['key']);
    }

    public function testShowFormatKeyNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['key_string' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['translations'][0]['key']);
        $this->assertEquals('', $data['translations'][0]['key']);
    }

    public function testShowFormatNameNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['name_string' => 'blah']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['translations'][0]['name']);
        $this->assertEquals($format->name_string, $data['translations'][0]['name']);
    }

    public function testShowFormatNameNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['name_string' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['translations'][0]['name']);
        $this->assertEquals('', $data['translations'][0]['name']);
    }

    public function testShowFormatDescriptionNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['description_string' => 'blah']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['translations'][0]['description']);
        $this->assertEquals($format->description_string, $data['translations'][0]['description']);
    }

    public function testShowFormatDescriptionNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['description_string' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['translations'][0]['description']);
        $this->assertEquals('', $data['translations'][0]['description']);
    }

    public function testShowFormatLanguage()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = $this->createFormat(['lang_enum' => 'blah']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['translations'][0]['language']);
        $this->assertEquals($format->lang_enum, $data['translations'][0]['language']);
    }
}
