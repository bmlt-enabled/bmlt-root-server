<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LegacyTestCase;

class LegacyAuthTest extends LegacyTestCase
{
    use RefreshDatabase;

    private string $goodPassword = 'goodpassword';
    private string $badPassword = 'badpassword';

    public function createServerAdmin()
    {
        return User::create([
            'user_level_tinyint' => 1,
            'name_string' => 'test',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'test',
            'password_string' => password_hash($this->goodPassword, PASSWORD_BCRYPT),
        ]);
    }

    public function createServiceBodyAdmin()
    {
        return User::create([
            'user_level_tinyint' => 2,
            'name_string' => 'test',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'test',
            'password_string' => password_hash($this->goodPassword, PASSWORD_BCRYPT),
        ]);
    }

    public function testSuccessfulLoginServiceBodyAdminWeb()
    {
        $user = $this->createServiceBodyAdmin();
        $urls = ['', '/', '/index.php'];
        foreach ($urls as $url) {
            $data = [
                'admin_action' => 'login',
                'c_comdef_admin_login' => $user->login_string,
                'c_comdef_admin_password' => $this->goodPassword
            ];
            $this->post($url, $data)
                ->assertStatus(302)
                ->assertSessionHas('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $user->id_bigint);
        }
    }

    public function testSuccessfulLoginServerAdminWeb()
    {
        $user = $this->createServerAdmin();
        $urls = ['', '/', '/index.php'];
        foreach ($urls as $url) {
            $data = [
                'admin_action' => 'login',
                'c_comdef_admin_login' => $user->login_string,
                'c_comdef_admin_password' => $this->goodPassword
            ];
            $this->post($url, $data)
                ->assertStatus(302)
                ->assertSessionHas('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $user->id_bigint);
        }
    }

    public function testFailedLoginWeb()
    {
        $user = $this->createServiceBodyAdmin();
        $urls = ['', '/', '/index.php'];
        foreach ($urls as $url) {
            $data = [
                'admin_action' => 'login',
                'c_comdef_admin_login' => $user->login_string,
                'c_comdef_admin_password' => $this->badPassword
            ];
            $this->post($url, $data)
                ->assertStatus(302)
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
        }
    }

    public function testLogoutWeb()
    {
        $user = $this->createServiceBodyAdmin();
        $urls = ['', '/', '/index.php'];
        foreach ($urls as $url) {
            $this->actingAs($user)
                ->withSession([
                    'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
                ])
                ->post($url, ['admin_action' => 'logout'])
                ->assertStatus(302)
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
        }
    }

    public function testSuccessfulLoginAdminXml()
    {
        $user = $this->createServiceBodyAdmin();
        $data = [
            'admin_action' => 'login',
            'c_comdef_admin_login' => $user->login_string,
            'c_comdef_admin_password' => $this->goodPassword
        ];
        $this->assertEquals(
            'OK',
            $this->post('/local_server/server_admin/xml.php', $data)
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionHas('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $user->id_bigint)
                ->content()
        );
        $this->assertEquals(
            'OK',
            $this->get("/local_server/server_admin/xml.php?admin_action=login&c_comdef_admin_login=$user->login_string&c_comdef_admin_password=$this->goodPassword")
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionHas('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $user->id_bigint)
                ->content()
        );
    }

    public function testFailedLoginServiceBodyAdminAdminXml()
    {
        $user = $this->createServiceBodyAdmin();
        $data = [
            'admin_action' => 'login',
            'c_comdef_admin_login' => $user->login_string,
            'c_comdef_admin_password' => $this->badPassword
        ];
        $this->assertEquals(
            '<h1>NOT AUTHORIZED</h1>',
            $this->post('/local_server/server_admin/xml.php', $data)
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
        $this->assertEquals(
            '<h1>NOT AUTHORIZED</h1>',
            $this->get("/local_server/server_admin/xml.php?admin_action=login&c_comdef_admin_login=$user->login_string&c_comdef_admin_password=$this->badPassword")
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
    }

    public function testServerAdminAdminXml()
    {
        $user = $this->createServerAdmin();
        $data = [
            'admin_action' => 'login',
            'c_comdef_admin_login' => $user->login_string,
            'c_comdef_admin_password' => $this->goodPassword
        ];
        $this->assertEquals(
            '<h1>NOT AUTHORIZED</h1>',
            $this->post('/local_server/server_admin/xml.php', $data)
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
        $this->assertEquals(
            '<h1>NOT AUTHORIZED</h1>',
            $this->get("/local_server/server_admin/xml.php?admin_action=login&c_comdef_admin_login=$user->login_string&c_comdef_admin_password=$this->badPassword")
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
    }

    public function testLogoutAdminXml()
    {
        $user = $this->createServiceBodyAdmin();
        $this->assertEquals(
            'BYE',
            $this->actingAs($user)
                ->withSession([
                    'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
                ])
                ->post('/local_server/server_admin/xml.php', ['admin_action' => 'logout'])
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
        $this->assertEquals(
            'BYE',
            $this->actingAs($user)
                ->withSession([
                    'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
                ])
                ->get('/local_server/server_admin/xml.php?admin_action=logout')
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
    }

    public function testSuccessfulLoginAdminJson()
    {
        $user = $this->createServiceBodyAdmin();
        $data = [
            'admin_action' => 'login',
            'c_comdef_admin_login' => $user->login_string,
            'c_comdef_admin_password' => $this->goodPassword
        ];
        $this->assertEquals(
            'OK',
            $this->post('/local_server/server_admin/json.php', $data)
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionHas('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $user->id_bigint)
                ->content()
        );
        $this->assertEquals(
            'OK',
            $this->get("/local_server/server_admin/json.php?admin_action=login&c_comdef_admin_login=$user->login_string&c_comdef_admin_password=$this->goodPassword")
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionHas('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $user->id_bigint)
                ->content()
        );
    }

    public function testFailedLoginAdminJson()
    {
        $user = $this->createServiceBodyAdmin();
        $data = [
            'admin_action' => 'login',
            'c_comdef_admin_login' => $user->login_string,
            'c_comdef_admin_password' => $this->badPassword
        ];
        $this->assertEquals(
            'NOT AUTHORIZED',
            $this->post('/local_server/server_admin/json.php', $data)
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
        $this->assertEquals(
            'NOT AUTHORIZED',
            $this->get("/local_server/server_admin/json.php?admin_action=login&c_comdef_admin_login=$user->login_string&c_comdef_admin_password=$this->badPassword")
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
    }

    public function testServerAdminUserAdminJson()
    {
        $user = $this->createServerAdmin();
        $data = [
            'admin_action' => 'login',
            'c_comdef_admin_login' => $user->login_string,
            'c_comdef_admin_password' => $this->goodPassword
        ];
        $this->assertEquals(
            'NOT AUTHORIZED',
            $this->post('/local_server/server_admin/json.php', $data)
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
        $this->assertEquals(
            'NOT AUTHORIZED',
            $this->get("/local_server/server_admin/json.php?admin_action=login&c_comdef_admin_login=$user->login_string&c_comdef_admin_password=$this->badPassword")
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
    }

    public function testLogoutAdminJson()
    {
        $user = $this->createServiceBodyAdmin();
        $this->assertEquals(
            'BYE',
            $this->actingAs($user)
                ->withSession([
                    'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
                ])
                ->post('/local_server/server_admin/json.php', ['admin_action' => 'logout'])
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
        $this->assertEquals(
            'BYE',
            $this->actingAs($user)
                ->withSession([
                    'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
                ])
                ->get('/local_server/server_admin/json.php?admin_action=logout')
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertSessionMissing('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')
                ->content()
        );
    }
}
