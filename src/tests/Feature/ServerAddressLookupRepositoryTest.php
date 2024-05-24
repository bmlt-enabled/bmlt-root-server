<?php

namespace Tests\Unit;

use App\Repositories\ServerAddressLookupRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ServerAddressLookupRepositoryTest extends TestCase
{
    public function testValid()
    {
        Http::fake(['*' => Http::response('123.123.123.123')]);
        $repository = new ServerAddressLookupRepository();
        $ip = $repository->get();
        $this->assertEquals('123.123.123.123', $ip);
    }

    public function testTrimsWhitespace()
    {
        Http::fake(['*' => Http::response('  123.123.123.123  ')]);
        $repository = new ServerAddressLookupRepository();
        $ip = $repository->get();
        $this->assertEquals('123.123.123.123', $ip);
    }

    public function testBadStatusCode()
    {
        Http::fake(['*' => Http::response('123.123.123.123', 300)]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(ServerAddressLookupRepository::ERROR_STATUS_CODE);
        $repository = new ServerAddressLookupRepository();
        $repository->get();
    }

    public function testInvalidIp()
    {
        Http::fake(['*' => Http::response('not an ip')]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(ServerAddressLookupRepository::ERROR_INVALID_IP);
        $repository = new ServerAddressLookupRepository();
        $repository->get();
    }
}
