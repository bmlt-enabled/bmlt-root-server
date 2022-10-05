<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceBody;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceBodyUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function toPayload(ServiceBody $serviceBody): array
    {
        $values = [
            'parentId' => ($this->sb_owner ?? 0) ?: null,
            'name' => $serviceBody->name_string,
            'description' => $serviceBody->description_string,
            'type' => $serviceBody->sb_type,
            'adminUserId' => $serviceBody->principal_user_bigint,
            'assignedUserIds' => !empty($this->editors_string) ? collect(explode(',', $this->editors_string))->map(fn ($id) => intval($id))->toArray() : [],
            'url' => $serviceBody->uri_string,
            'helpline' => $serviceBody->kml_file_uri_string,
            'email' => $serviceBody->sb_meeting_email,
            'worldId' => $serviceBody->worldid_mixed,
        ];

        return $values;
    }

    public function testUpdateServiceBodyAsAdmin()
    {
        $user1 = $this->createAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createZone('region', 'region', adminUserId: $user1->id_bigint);
        $user2 = $this->createServiceBodyAdminUser();
        $data = [
            'parentId' => $zone->id_bigint,
            'name' => 'updated name',
            'description' => 'update description',
            'type' => ServiceBody::SB_TYPE_AREA,
            'adminUserId' => $user2->id_bigint,
            'assignedUserIds' => [$user2->id_bigint],
            'url' => 'https://na.org',
            'helpline' => '123-456-7890',
            'email' => 'test@test.com',
            'worldId' => 'updated worldId',
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        $region->refresh();
        $this->assertEquals($region->sb_owner, $data['parentId']);
        $this->assertEquals($region->name_string, $data['name']);
        $this->assertEquals($region->description_string, $data['description']);
        $this->assertEquals($region->sb_type, $data['type']);
        $this->assertEquals($region->principal_user_bigint, $data['adminUserId']);
        $this->assertEquals($region->editors_string, implode(',', $data['assignedUserIds']));
        $this->assertEquals($region->uri_string, $data['url']);
        $this->assertEquals($region->kml_file_uri_string, $data['helpline']);
        $this->assertEquals($region->sb_meeting_email, $data['email']);
        $this->assertEquals($region->worldid_mixed, $data['worldId']);

        // validate nulling out parentId comes out as zero in the database
        $data['parentId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        $region->refresh();
        $this->assertTrue($region->sb_owner === 0);
    }

    public function testUpdateServiceBodyAsServiceBodyAdmin()
    {
        $user1 = $this->createServiceBodyAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createRegion('region', 'region', sbOwner: 0, adminUserId: $user1->id_bigint);
        $user2 = $this->createAdminUser();
        $data = [
            'parentId' => $zone->id_bigint,
            'name' => 'updated name',
            'description' => 'update description',
            'type' => ServiceBody::SB_TYPE_AREA,
            'adminUserId' => $user2->id_bigint,
            'assignedUserIds' => [$user2->id_bigint],
            'url' => 'https://na.org',
            'helpline' => '123-456-7890',
            'email' => 'test@test.com',
            'worldId' => 'updated worldId',
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        $region->refresh();
        $this->assertTrue($region->sb_owner === 0);  // did not change
        $this->assertEquals($region->name_string, $data['name']);
        $this->assertEquals($region->description_string, $data['description']);
        $this->assertEquals($region->sb_type, 'RS');  // did not change
        $this->assertEquals($region->principal_user_bigint, $user1->id_bigint);  // did not change
        $this->assertEquals($region->editors_string, implode(',', $data['assignedUserIds']));
        $this->assertEquals($region->uri_string, $data['url']);
        $this->assertEquals($region->kml_file_uri_string, $data['helpline']);
        $this->assertEquals($region->sb_meeting_email, $data['email']);
        $this->assertEquals($region->worldid_mixed, $data['worldId']);

        // validate nulling out parentId comes out as zero in the database
        $data['parentId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        $region->refresh();
        $this->assertTrue($region->sb_owner === 0);
    }

    public function testUpdateServiceBodyOptionalFieldsAsAdmin()
    {
        $user1 = $this->createAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createZone('region', 'region', uri: 'https://test.com', helpline: '5555555555', worldId: 'abc', adminUserId: $user1->id_bigint);
        $user2 = $this->createServiceBodyAdminUser();
        $data = [
            'parentId' => $zone->id_bigint,
            'name' => 'updated name',
            'description' => 'update description',
            'type' => ServiceBody::SB_TYPE_AREA,
            'adminUserId' => $user2->id_bigint,
            'assignedUserIds' => [$user2->id_bigint],
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        $region->refresh();
        $this->assertEquals($region->sb_owner, $data['parentId']);
        $this->assertEquals($region->name_string, $data['name']);
        $this->assertEquals($region->description_string, $data['description']);
        $this->assertEquals($region->sb_type, $data['type']);
        $this->assertEquals($region->principal_user_bigint, $data['adminUserId']);
        $this->assertEquals($region->editors_string, implode(',', $data['assignedUserIds']));
        $this->assertNull($region->uri_string);
        $this->assertNull($region->kml_file_uri_string);
        $this->assertEquals('', $region->sb_meeting_email);
        $this->assertNull($region->worldid_mixed);

        // validate nulling out parentId comes out as zero in the database
        $data['parentId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        $region->refresh();
        $this->assertTrue($region->sb_owner === 0);
    }

    public function testUpdateServiceBodyValidateParentId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createRegion('region', 'region', $zone->id_bigint, adminUserId: $user->id_bigint);
        $data = $this->toPayload($region);

        // it is required
        unset($data['parentId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid service body
        $data['parentId'] = $region->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid service body
        $data['parentId'] = $zone->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);

        // it can be null
        $data['parentId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$region->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it is required
        unset($data['name']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['name'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['name'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // valid
        $data['name'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it is required
        unset($data['description']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['description'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['description'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // valid
        $data['description'] = 'test';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it is required
        unset($data['type']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be empty
        $data['type'] = '   ';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid string
        $data['type'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // valid
        foreach (ServiceBody::VALID_SB_TYPES as $validType) {
            $data['type'] = $validType;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
                ->assertStatus(204);
        }
    }

    public function testUpdateServiceBodyValidateUserId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it is required
        unset($data['adminUserId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['adminUserId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be a userId that doesn't exist
        $data['adminUserId'] = $user->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid user id
        $data['adminUserId'] = $user->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateassignedUserIds()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it is required
        unset($data['assignedUserIds']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['assignedUserIds'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't contain an invalid user id
        $data['assignedUserIds'] = [$user->id_bigint, $user->id_bigint + 1];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be an empty array
        $data['assignedUserIds'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can contain valid user ids
        $data['assignedUserIds'] = [$user->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateUrl()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it can't be an invalid url
        $data['url'] = 'test';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than 255 characters
        $data['url'] = 'https://' . str_repeat('t', 255 - 11) . '.org';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid url with <= 255 characters
        $data['url'] = 'https://' . str_repeat('t', 255 - 12) . '.org';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can be null
        $data['url'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can be empty (gets converted to null)
        $data['url'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it is not required
        unset($data['url']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateHelpline()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it can't be longer than 255 characters
        $data['helpline'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be an arbitrary string 255 characters or less
        $data['helpline'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can be null
        $data['helpline'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can be empty (gets converted to null)
        $data['helpline'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it is not required
        unset($data['helpline']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it can't be an invalid email
        $data['email'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can't be longer than be 255 characters
        $data['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . 'z.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters
        $data['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . '.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can be null
        $data['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it is not required
        unset($data['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateServiceBodyValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone', adminUserId: $user->id_bigint);
        $data = $this->toPayload($zone);

        // it can't be longer than 30 characters
        $data['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(422);

        // it can be 30 characters
        $data['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can be empty (gets converted to null)
        $data['worldId'] = '    ';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it can be null
        $data['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        // it is not required
        unset($data['worldId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);
    }
}
