<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\FormatType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormatTypesTest extends TestCase
{
    use RefreshDatabase;

    public function testGetApiEnums()
    {
        $types = FormatType::getApiEnums();
        $this->assertEquals(count($types), 5);
        $this->assertIsString($types[0]);
    }
    public function testGetApiEnumFromKey()
    {
        $key = FormatType::GetApiEnumFromKey('FC1');
        $this->assertEquals($key, 'MEETING_FORMAT');
        $key = FormatType::GetApiEnumFromKey('FC2');
        $this->assertEquals($key, 'LOCATION');
        $key = FormatType::GetApiEnumFromKey('blah');
        $this->assertNull($key);
    }
}
