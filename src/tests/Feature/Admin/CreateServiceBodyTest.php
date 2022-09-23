<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceBody;

use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateServiceBodyTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload($user): array
    {
        return [
            'parentId' => null,
            'name' => 'test name',
            'description' => 'test description',
            'type' => 'AS',
            'userId' => $user->id_bigint,
            'editorUserIds' => [],
            'url' => 'http://blah.com',
            'helpline' => '555-555-5555',
            'worldId' => 'test world id',
        ];
    }

    public function testCreateSuccessNoParent()
    {
        $user = $this->createAdminUser();
        $user2 = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);
        $data['editorUserIds'] = [$user->id_bigint, $user2->id_bigint];

        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201)
            ->assertJsonFragment(['parentId' => $data['parentId']])
            ->assertJsonFragment(['name' => $data['name']])
            ->assertJsonFragment(['description' => $data['description']])
            ->assertJsonFragment(['type' => $data['type']])
            ->assertJsonFragment(['userId' => $data['userId']])
            ->assertJsonFragment(['editorUserIds' => $data['editorUserIds']])
            ->assertJsonFragment(['url' => $data['url']])
            ->assertJsonFragment(['helpline' => $data['helpline']])
            ->assertJsonFragment(['worldId' => $data['worldId']])
            ->json();
    }

    public function testCreateSuccessWithParent()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $data = $this->validPayload($user);
        $data['parentId'] = $zone->id_bigint;

        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201)
            ->assertJsonFragment(['parentId' => $data['parentId']])
            ->assertJsonFragment(['name' => $data['name']])
            ->assertJsonFragment(['description' => $data['description']])
            ->assertJsonFragment(['type' => $data['type']])
            ->assertJsonFragment(['userId' => $data['userId']])
            ->assertJsonFragment(['editorUserIds' => $data['editorUserIds']])
            ->assertJsonFragment(['url' => $data['url']])
            ->assertJsonFragment(['helpline' => $data['helpline']])
            ->assertJsonFragment(['worldId' => $data['worldId']])
            ->json();
    }

    public function testStoreServiceBodyValidateParentId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $data = $this->validPayload($user);

        // it is required
        unset($data['parentId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be an invalid service body
        $data['parentId'] = $zone->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can be a valid service body
        $data['parentId'] = $zone->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);

        // it can be null
        $data['parentId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is required
        unset($data['name']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be null
        $data['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be empty
        $data['name'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['name'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // valid
        $data['name'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is required
        unset($data['description']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be null
        $data['description'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be empty
        $data['description'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // valid
        $data['description'] = 'test';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is required
        unset($data['type']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be empty
        $data['type'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be an invalid string
        $data['type'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // valid
        foreach (ServiceBody::VALID_SB_TYPES as $validType) {
            $data['type'] = $validType;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/servicebodies', $data)
                ->assertStatus(201);
        }
    }

    public function testStoreServiceBodyValidateUserId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is required
        unset($data['userId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be null
        $data['userId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be a userId that doesn't exist
        $data['userId'] = $user->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can be a valid user id
        $data['userId'] = $user->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateEditorUserIds()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is required
        unset($data['editorUserIds']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be null
        $data['editorUserIds'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't contain an invalid user id
        $data['editorUserIds'] = [$user->id_bigint, $user->id_bigint + 1];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can be an empty array
        $data['editorUserIds'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);

        // it can contain valid user ids
        $data['editorUserIds'] = [$user->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateUrl()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is not required
        unset($data['url']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);

        // it can't be null
        $data['url'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be empty
        $data['url'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be an invalid url
        $data['url'] = 'test';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['url'] = 'https://' . str_repeat('t', 255 - 11) . '.org';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can be a valid url with <= 255 characters
        $data['url'] = 'https://' . str_repeat('t', 255 - 12) . '.org';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateHelpline()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is not required
        unset($data['helpline']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);

        // it can't be null
        $data['helpline'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be empty
        $data['helpline'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['helpline'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can be an arbitrary string 255 characters or less
        $data['helpline'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is not required
        unset($data['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);

        // it can't be null
        $data['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be an invalid email
        $data['email'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be longer than be 255 characters
        $data['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . 'z.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can be 255 characters
        $data['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . '.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }

    public function testStoreServiceBodyValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload($user);

        // it is not required
        unset($data['worldId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);

        // it can't be null
        $data['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be empty
        $data['worldId'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can't be longer than 30 characters
        $data['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(422);

        // it can be 30 characters
        $data['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->assertStatus(201);
    }
}
