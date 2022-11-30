<?php

namespace Tests\Unit;

use App\Models\Format;
use App\Repositories\External\ExternalFormat;
use App\Repositories\External\InvalidFormatException;
use PHPUnit\Framework\TestCase;

class ExternalFormatTest extends TestCase
{
    private function validValues(string $language): array
    {
        return [
            'id' => '342',
            'key_string' => 'HY',
            'name_string' => 'Hybrid',
            'description_string' => 'Meets virtually and in person',
            'lang' => $language,
            'format_type_enum' =>'FC2',
            'world_id' => 'HYBR',
        ];
    }

    private function getModel(array $validValues): Format
    {
        return new Format([
            'source_id' => $validValues['id'],
            'key_string' => $validValues['key_string'],
            'name_string' => $validValues['name_string'],
            'description_string' => $validValues['description_string'],
            'lang_enum' => $validValues['lang'],
            'format_type_enum' => $validValues['format_type_enum'],
            'worldid_mixed' => $validValues['world_id'],
        ]);
    }

    public function testValidWithoutNulls()
    {
        $values = $this->validValues('en');
        $format = new ExternalFormat($values);
        $this->assertEquals($values['id'], $format->id);
        $this->assertEquals($values['key_string'], $format->key);
        $this->assertEquals($values['name_string'], $format->name);
        $this->assertEquals($values['description_string'], $format->description);
        $this->assertEquals($values['lang'], $format->language);
        $this->assertEquals($values['format_type_enum'], $format->type);
        $this->assertEquals($values['world_id'], $format->worldId);
    }

    public function testValidWithEmpty()
    {
        $values = $this->validValues('en');
        $values['format_type_enum'] = '';
        $values['world_id'] = '';
        $format = new ExternalFormat($values);
        $this->assertEquals($values['id'], $format->id);
        $this->assertEquals($values['key_string'], $format->key);
        $this->assertEquals($values['name_string'], $format->name);
        $this->assertEquals($values['description_string'], $format->description);
        $this->assertEquals($values['lang'], $format->language);
        $this->assertNull($format->type);
        $this->assertNull($format->worldId);
    }

    public function testValidWithNulls()
    {
        $values = $this->validValues('en');
        $values['format_type_enum'] = null;
        $values['world_id'] = null;
        $format = new ExternalFormat($values);
        $this->assertEquals($values['id'], $format->id);
        $this->assertEquals($values['key_string'], $format->key);
        $this->assertEquals($values['name_string'], $format->name);
        $this->assertEquals($values['description_string'], $format->description);
        $this->assertEquals($values['lang'], $format->language);
        $this->assertNull($format->type);
        $this->assertNull($format->worldId);
    }

    public function testValidWithMissing()
    {
        $values = $this->validValues('en');
        unset($values['format_type_enum']);
        unset($values['world_id']);
        $format = new ExternalFormat($values);
        $this->assertEquals($values['id'], $format->id);
        $this->assertEquals($values['key_string'], $format->key);
        $this->assertEquals($values['name_string'], $format->name);
        $this->assertEquals($values['description_string'], $format->description);
        $this->assertEquals($values['lang'], $format->language);
        $this->assertNull($format->type);
        $this->assertNull($format->worldId);
    }

    public function testMissingId()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        unset($values['id']);
        new ExternalFormat($values);
    }

    public function testInvalidId()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        $values['id'] = 'string';
        new ExternalFormat($values);
    }

    public function testMissingKey()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        unset($values['key_string']);
        new ExternalFormat($values);
    }

    public function testInvalidKey()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        $values['key_string'] = 123;
        new ExternalFormat($values);
    }

    public function testMissingName()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        unset($values['name_string']);
        new ExternalFormat($values);
    }

    public function testInvalidName()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        $values['name_string'] = 123;
        new ExternalFormat($values);
    }

    public function testMissingDescription()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        unset($values['description_string']);
        new ExternalFormat($values);
    }

    public function testInvalidDescription()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        $values['description_string'] = 123;
        new ExternalFormat($values);
    }

    public function testMissingLanguage()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        unset($values['lang']);
        new ExternalFormat($values);
    }

    public function testInvalidLanguage()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        $values['lang'] = 123;
        new ExternalFormat($values);
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        $values['format_type_enum'] = 123;
        new ExternalFormat($values);
    }

    public function testInvalidWorldId()
    {
        $this->expectException(InvalidFormatException::class);
        $values = $this->validValues('en');
        $values['world_id'] = 123;
        new ExternalFormat($values);
    }

    // isEqual
    //
    //
    public function testNoDifferences()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $this->assertTrue($external->isEqual($db));
    }

    public function testSourceId()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $db->source_id = $external->id + 1;
        $this->assertFalse($external->isEqual($db));
    }

    public function testKey()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $db->key_string = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testName()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $db->name_string = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testDescription()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $db->description_string = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testLanguage()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $db->lang_enum = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testType()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $db->format_type_enum = 'changed';
        $this->assertFalse($external->isEqual($db));
    }

    public function testWorldId()
    {
        $values = $this->validValues('en');
        $external = new ExternalFormat($values);
        $db = $this->getModel($values);
        $db->worldid_mixed = 'changed';
        $this->assertFalse($external->isEqual($db));
    }
}
