<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceBody;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceBodyPartialUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function testPartialUpdateServiceBodyAsAdmin()
    {
        $user1 = $this->createAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createRegion('region', 'region', sbOwner: 0, userId: $user1->id_bigint);
        $user2 = $this->createServiceBodyAdminUser();

        $data = ['parentId' => $zone->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->sb_owner, $data['parentId']);

        $data = ['name' => 'updated name'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->name_string, $data['name']);

        $data = ['description' => 'updated description'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->description_string, $data['description']);


        $data = ['type' => 'AS'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->sb_type, $data['type']);

        $data = ['userId' => $user2->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->principal_user_bigint, $data['userId']);

        $data = ['editorUserIds' => [$user2->id_bigint]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->editors_string, implode(',', $data['editorUserIds']));

        $data = ['url' => 'https://www.na.org'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->uri_string, $data['url']);

        $data = ['helpline' => '123-456-7890'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->kml_file_uri_string, $data['helpline']);

        $data = ['email' => 'test@test.com'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->sb_meeting_email, $data['email']);

        $data = ['worldId' => 'new worldId'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->worldid_mixed, $data['worldId']);
    }

    public function testPartialUpdateServiceBodyAsServiceBodyAdmin()
    {
        $user1 = $this->createServiceBodyAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createRegion('region', 'region', sbOwner: 0, userId: $user1->id_bigint);
        $user2 = $this->createAdminUser();

        $data = ['parentId' => $zone->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertTrue($region->sb_owner === 0);  // did not change

        $data = ['name' => 'updated name'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->name_string, $data['name']);

        $data = ['description' => 'updated description'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->description_string, $data['description']);


        $data = ['type' => 'AS'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->sb_type, 'RS');  // did not change

        $data = ['userId' => $user2->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->principal_user_bigint, $user1->id_bigint);  // did not change

        $data = ['editorUserIds' => [$user2->id_bigint]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->editors_string, implode(',', $data['editorUserIds']));

        $data = ['url' => 'https://www.na.org'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->uri_string, $data['url']);

        $data = ['helpline' => '123-456-7890'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->kml_file_uri_string, $data['helpline']);

        $data = ['email' => 'test@test.com'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->sb_meeting_email, $data['email']);

        $data = ['worldId' => 'new worldId'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
        $region->refresh();
        $this->assertEquals($region->worldid_mixed, $data['worldId']);
    }

    public function testPartialUpdateServiceBodyEmpty()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createRegion('region', 'region', $zone->id_bigint, userId: $user->id_bigint);
        $data = [];

        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateParentId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createRegion('region', 'region', $zone->id_bigint, userId: $user->id_bigint);
        $data = [];

        // it can't be an invalid service body
        $data['parentId'] = $region->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid service body
        $data['parentId'] = $zone->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        // it can be null
        $data['parentId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['name'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['name'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // valid
        $data['name'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['description'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['description'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // valid
        $data['description'] = 'test';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['type'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid string
        $data['type'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // valid
        foreach (ServiceBody::VALID_SB_TYPES as $validType) {
            $data['type'] = $validType;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateServiceBodyValidateUserId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['userId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be a userId that doesn't exist
        $data['userId'] = $user->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid user id
        $data['userId'] = $user->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateEditorUserIds()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['editorUserIds'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't contain an invalid user id
        $data['editorUserIds'] = [$user->id_bigint, $user->id_bigint + 1];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be an empty array
        $data['editorUserIds'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can contain valid user ids
        $data['editorUserIds'] = [$user->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateUrl()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['url'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['url'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid url
        $data['url'] = 'test';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['url'] = 'https://' . str_repeat('t', 255 - 11) . '.org';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid url with <= 255 characters
        $data['url'] = 'https://' . str_repeat('t', 255 - 12) . '.org';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateHelpline()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['helpline'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['helpline'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['helpline'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be an arbitrary string 255 characters or less
        $data['helpline'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid email
        $data['email'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than be 255 characters
        $data['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . 'z.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters
        $data['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . '.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateServiceBodyValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', userId: $user->id_bigint);
        $data = [];

        // it can't be null
        $data['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['worldId'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than 30 characters
        $data['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be 30 characters
        $data['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }
}
