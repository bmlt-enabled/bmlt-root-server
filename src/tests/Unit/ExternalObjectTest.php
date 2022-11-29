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

    public function validateNullableInt(array $values, string $key): ?int
    {
        return parent::validateNullableInt($values, $key);
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

    public function validateTime(array $values, string $key): string
    {
        return parent::validateTime($values, $key);
    }

    public function validateNullableFloat(array $values, string $key): ?float
    {
        return parent::validateNullableFloat($values, $key);
    }

    public function validateBool(array $values, string $key): bool
    {
        return parent::validateBool($values, $key);
    }

    public function validateIntArray(array $values, string $key): array
    {
        return parent::validateIntArray($values, $key);
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

    // validateNullableInt
    //
    //

    // validateNullableInt
    //
    //
    public function testValidateNullableIntSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals(123, $obj->validateNullableInt(['a' => 123], 'a'));
        $this->assertEquals(123, $obj->validateNullableInt(['a' => '123'], 'a'));
        $this->assertEquals(123, $obj->validateNullableInt(['a' => ' 123 '], 'a'));
        $this->assertNull($obj->validateNullableInt(['a' => ''], 'a'));
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

    // validateTime
    //
    //
    public function testValidateTimeSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals('00:00:00', $obj->validateTime(['a' => '00:00:00'], 'a'));
        $this->assertEquals('01:00:00', $obj->validateTime(['a' => '01:00:00'], 'a'));
        $this->assertEquals('00:00:00', $obj->validateTime(['a' => '24:00:00'], 'a'));
        $this->assertEquals('00:00:00', $obj->validateTime(['a' => '00:00'], 'a'));
        $this->assertEquals('01:00:00', $obj->validateTime(['a' => '01:00'], 'a'));
        $this->assertEquals('00:00:00', $obj->validateTime(['a' => '24:00'], 'a'));
    }

    public function testValidateTimeBadKey()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateTime(['a' => '00:00:00'], 'b');
    }

    public function testValidateTimeNullValue()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateTime(['a' => null], 'a');
    }

    public function testValidateTimeEmptyString()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateTime(['a' => ''], 'a');
    }

    public function testValidateTimeBadString()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateTime(['a' => 'string'], 'a');
    }

    // validateNullableFloat
    //
    //
    public function testValidateNullableFloatSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals(1.234, $obj->validateNullableFloat(['a' => '1.234'], 'a'));
        $this->assertEquals(-1.234, $obj->validateNullableFloat(['a' => '-1.234'], 'a'));
        $this->assertNull($obj->validateNullableFloat(['a' => ''], 'a'));
        $this->assertNull($obj->validateNullableFloat(['a' => null], 'a'));
        $this->assertNull($obj->validateNullableFloat(['a' => null], 'b'));
    }

    // validateBool
    //
    //
    public function testValidateBoolSuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertIsBool($obj->validateBool(['a' => true], 'a'));
        $this->assertTrue($obj->validateBool(['a' => true], 'a'));
        $this->assertIsBool($obj->validateBool(['a' => 1], 'a'));
        $this->assertTrue($obj->validateBool(['a' => 1], 'a'));
        $this->assertIsBool($obj->validateBool(['a' => '1'], 'a'));
        $this->assertTrue($obj->validateBool(['a' => '1'], 'a'));
        $this->assertIsBool($obj->validateBool(['a' => true], 'a'));
        $this->assertFalse($obj->validateBool(['a' => false], 'a'));
        $this->assertIsBool($obj->validateBool(['a' => 0], 'a'));
        $this->assertFalse($obj->validateBool(['a' => 0], 'a'));
        $this->assertIsBool($obj->validateBool(['a' => '0'], 'a'));
        $this->assertFalse($obj->validateBool(['a' => '0'], 'a'));
        $this->assertIsBool($obj->validateBool(['a' => ''], 'a'));
        $this->assertFalse($obj->validateBool(['a' => ''], 'a'));
    }

    public function testValidateBoolBadKey()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateBool(['a' => true], 'b');
    }

    // validateIntArray
    //
    //
    public function testValidateIntArraySuccess()
    {
        $obj = new ExternalTestObject();
        $this->assertEquals([], $obj->validateIntArray(['a' => ''], 'a'));
        $this->assertEquals([999], $obj->validateIntArray(['a' => '999'], 'a'));
        $this->assertEquals([1,999], $obj->validateIntArray(['a' => '999,1'], 'a'));
        $this->assertEquals([1,999], $obj->validateIntArray(['a' => '999, 1'], 'a'));
        $this->assertEquals([1,999], $obj->validateIntArray(['a' => '999,999,1'], 'a'));
    }

    public function testValidateIntArrayBadKey()
    {
        $this->expectException(InvalidTestObjectException::class);
        $obj = new ExternalTestObject();
        $obj->validateIntArray(['a' => '123'], 'b');
    }
}
