<?php

namespace Tests\Unit;

use App\Models\ServiceBody;
use App\Repositories\External\ExternalServiceBody;
use App\Repositories\External\InvalidServiceBodyException;
use PHPUnit\Framework\TestCase;

class ExternalServiceBodyTest extends TestCase
{
    private function validValues(): array
    {
        return [
            'id' => '171',
            'parent_id' => '20',
            'name' => 'Trans Umbrella Area',
            'description' => 'description',
            'type' => 'AS',
            'url' => 'http://transuana.org',
            'helpline' => 'helpline',
            'world_id' => 'AR6339',
        ];
    }

    private function getModel(array $validValues): ServiceBody
    {
        return new ServiceBody([
            'source_id' => $validValues['id'],
            'sb_owner' => $validValues['parent_id'],
            'name_string' => $validValues['name'],
            'description_string' => $validValues['description'],
            'sb_type' => $validValues['type'],
            'uri_string' => $validValues['url'],
            'kml_file_uri_string' => $validValues['helpline'],
            'worldid_mixed' => $validValues['world_id'],
        ]);
    }

    public function testValidWithoutNulls()
    {
        $values = $this->validValues();
        $serviceBody = new ExternalServiceBody($values);
        $this->assertEquals($values['id'], $serviceBody->id);
        $this->assertEquals($values['parent_id'], $serviceBody->parentId);
        $this->assertEquals($values['name'], $serviceBody->name);
        $this->assertEquals($values['description'], $serviceBody->description);
        $this->assertEquals($values['type'], $serviceBody->type);
        $this->assertEquals($values['url'], $serviceBody->url);
        $this->assertEquals($values['helpline'], $serviceBody->helpline);
        $this->assertEquals($values['world_id'], $serviceBody->worldId);
    }

    public function testValidWithEmpty()
    {
        $values = $this->validValues();
        $values['type'] = '';
        $values['url'] = '';
        $values['helpline'] = '';
        $values['world_id'] = '';
        $serviceBody = new ExternalServiceBody($values);
        $this->assertEquals($values['id'], $serviceBody->id);
        $this->assertEquals($values['parent_id'], $serviceBody->parentId);
        $this->assertEquals($values['name'], $serviceBody->name);
        $this->assertEquals($values['description'], $serviceBody->description);
        $this->assertNull($serviceBody->type);
        $this->assertNull($serviceBody->url);
        $this->assertNull($serviceBody->helpline);
        $this->assertNull($serviceBody->worldId);
    }

    public function testValidWithNulls()
    {
        $values = $this->validValues();
        $values['type'] = null;
        $values['url'] = null;
        $values['helpline'] = null;
        $values['world_id'] = null;
        $serviceBody = new ExternalServiceBody($values);
        $this->assertEquals($values['id'], $serviceBody->id);
        $this->assertEquals($values['parent_id'], $serviceBody->parentId);
        $this->assertEquals($values['name'], $serviceBody->name);
        $this->assertEquals($values['description'], $serviceBody->description);
        $this->assertNull($serviceBody->type);
        $this->assertNull($serviceBody->url);
        $this->assertNull($serviceBody->helpline);
        $this->assertNull($serviceBody->worldId);
    }

    public function testValidWithMissing()
    {
        $values = $this->validValues();
        unset($values['type']);
        unset($values['url']);
        unset($values['helpline']);
        unset($values['world_id']);
        $serviceBody = new ExternalServiceBody($values);
        $this->assertEquals($values['id'], $serviceBody->id);
        $this->assertEquals($values['parent_id'], $serviceBody->parentId);
        $this->assertEquals($values['name'], $serviceBody->name);
        $this->assertEquals($values['description'], $serviceBody->description);
        $this->assertNull($serviceBody->type);
        $this->assertNull($serviceBody->url);
        $this->assertNull($serviceBody->helpline);
        $this->assertNull($serviceBody->worldId);
    }

    public function testMissingId()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        unset($values['id']);
        new ExternalServiceBody($values);
    }

    public function testInvalidId()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['id'] = 'string';
        new ExternalServiceBody($values);
    }

    public function testMissingParentId()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        unset($values['parent_id']);
        new ExternalServiceBody($values);
    }

    public function testInvalidParentId()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['parent_id'] = 'string';
        new ExternalServiceBody($values);
    }

    public function testMissingName()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        unset($values['name']);
        new ExternalServiceBody($values);
    }

    public function testInvalidName()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['name'] = 123;
        new ExternalServiceBody($values);
    }

    public function testMissingDescription()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        unset($values['description']);
        new ExternalServiceBody($values);
    }

    public function testInvalidDescription()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['description'] = 123;
        new ExternalServiceBody($values);
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['type'] = 123;
        new ExternalServiceBody($values);
    }

    public function testInvalidUrl()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['url'] = 123;
        new ExternalServiceBody($values);
    }

    public function testInvalidHelpline()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['helpline'] = 123;
        new ExternalServiceBody($values);
    }

    public function testInvalidWorldId()
    {
        $this->expectException(InvalidServiceBodyException::class);
        $values = $this->validValues();
        $values['world_id'] = 123;
        new ExternalServiceBody($values);
    }

    // isEqual
    //
    //
    public function testNoDifferences()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $this->assertTrue($external->isEqual($db));
    }

    public function testParentId()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $db->sb_owner = 999;
        // parentId is not part of the equality comparison
        $this->assertTrue($external->isEqual($db));
    }

    public function testName()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $db->name_string = 'new name';
        $this->assertFalse($external->isEqual($db));
    }

    public function testDescription()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $db->description_string = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testType()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $db->sb_type = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testUrl()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $db->uri_string = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testHelpline()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $db->kml_file_uri_string = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testWorldId()
    {
        $values = $this->validValues();
        $external = new ExternalServiceBody($values);
        $db = $this->getModel($values);
        $db->worldid_mixed = 'changed';
        $this->assertFalse($external->isEqual($db));
    }
}
