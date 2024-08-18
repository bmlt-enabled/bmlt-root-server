<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LogTest extends TestCase
{
    use RefreshDatabase;

    public function testLaravelLogDownloadSuccess()
    {
        Storage::fake('logs');
        $content = Str::random(1000);
        Storage::disk('logs')->put('laravel.log', $content);

        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $gzipped = $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/logs/laravel')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename=laravel.log.gz')
            ->streamedContent();
        $this->assertEquals($content, gzdecode($gzipped));
    }

    public function testLogDownloadFileNotFound()
    {
        Storage::fake('logs');

        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/logs/laravel')
            ->assertJson(['message' => 'Log file not found.'])
            ->assertStatus(404);
    }

    public function testLaravelLogDownloadAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/logs/laravel")
            ->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized.']);
    }

    public function testLaravelLogDownloadAsUnauthenticatedDenied()
    {
        $this->getJson('/api/v1/logs/laravel')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testLaravelLogDownloadAsServiceBodyObserverDenied()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/logs/laravel")
            ->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized.']);
    }

    public function testLaravelLogDownloadAsDeactivatedDenied()
    {
        $user = $this->createDeactivatedUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/logs/laravel")
            ->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized.']);
    }
}
