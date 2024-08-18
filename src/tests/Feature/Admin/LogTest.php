<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LogTest extends TestCase
{
    use RefreshDatabase;

    public function testLaravelLogDownloadSuccess()
    {
        if (!File::exists(storage_path('logs'))) {
            File::makeDirectory(storage_path('logs'), 0755, true);
        }
        File::put(storage_path('logs/laravel.log'), 'Sample log content');
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/logs/laravel')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertHeader('Content-Encoding', 'gzip')
            ->assertHeader('Content-Disposition', 'attachment; filename=laravel.log.gz')
            ->streamedContent();
    }

    public function testLogDownloadFileNotFound()
    {
        if (File::exists(storage_path('logs/laravel.log'))) {
            File::delete(storage_path('logs/laravel.log'));
        }
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
            ->assertJson(['error' => 'Unauthorized.']);
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
            ->assertJson(['error' => 'Unauthorized.']);
    }

    public function testLaravelLogDownloadAsDeactivatedDenied()
    {
        $user = $this->createDeactivatedUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/logs/laravel")
            ->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized.']);
    }
}
