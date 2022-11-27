<?php

namespace Tests\Unit;

use App\Repositories\External\ExternalObject;
use App\Repositories\External\InvalidObjectException;
use PHPUnit\Framework\TestCase;

class InvalidTestObjectException extends InvalidObjectException
{
    public function __construct()
    {
        parent::__construct('TestObject');
    }
}

class ExternalTestObject extends ExternalObject
{
    protected function throwInvalidObjectException(): void
    {
        throw new InvalidTestObjectException();
    }

    public function validateInt(array $values, string $key): int
    {
        return parent::validateInt($values, $key);
    }

    public function validateString(array $values, string $key): string
    {
        return parent::validateString($values, $key);
    }

    public function validateNullableString(array $values, string $key): ?string
    {
        return parent::validateNullableString($values, $key);
    }

    public function validateUrl(array $values, string $key): string
    {
        return parent::validateUrl($values, $key);
    }
}

class ExternalObjectTest extends TestCase
{
    // validateInt
    //
    //
    public function testValidateIntSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals(123, $obj->validateInt(['a' => 123], 'a'));
        $this->assertEquals(123, $obj->validateInt(['a' => '123'], 'a'));
        $this->assertEquals(123, $obj->validateInt(['a' => ' 123 '], 'a'));
    }

    public function testValidateIntBadKey()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateInt(['a' => 123], 'b');
    }

    public function testValidateIntNullValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateInt(['a' => null], 'a');
    }

    public function testValidateIntStringValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateInt(['a' => 'test'], 'a');
    }

    // validateString
    //
    //
    public function testValidateStringSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals('abc', $obj->validateString(['a' => 'abc'], 'a'));
        $this->assertEquals('abc', $obj->validateString(['a' => ' abc '], 'a'));
    }

    public function testValidateStringBadKey()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateString(['a' => 'abc'], 'b');
    }

    public function testValidateStringNullValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateString(['a' => null], 'a');
    }

    public function testValidateStringIntValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateString(['a' => 123], 'a');
    }

    // validateNullableString
    //
    //
    public function testValidateNullableStringSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals('abc', $obj->validateNullableString(['a' => 'abc'], 'a'));
        $this->assertEquals('abc', $obj->validateNullableString(['a' => ' abc '], 'a'));
        $this->assertNull($obj->validateNullableString(['a' => 'abc'], 'b'));
        $this->assertNull($obj->validateNullableString(['a' => null], 'a'));
    }

    public function testValidateNullableStringIntValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateNullableString(['a' => 123], 'a');
    }

    // validateUrl
    //
    //
    public function testValidateUrlSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals('https://test.com/blah', $obj->validateUrl(['a' => 'https://test.com/blah'], 'a'));
        $this->assertEquals('https://test.com/blah', $obj->validateUrl(['a' => ' https://test.com/blah '], 'a'));
    }

    public function testValidateUrlBadKey()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateUrl(['a' => 'https://test.com/blah'], 'b');
    }

    public function testValidateUrlNullValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateUrl(['a' => null], 'a');
    }

    public function testValidateUrlStringValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateUrl(['a' => 'notAUrl'], 'a');
    }
}
