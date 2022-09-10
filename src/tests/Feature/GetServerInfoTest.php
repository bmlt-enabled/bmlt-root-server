<?php

namespace Tests\Feature;

use App\LegacyConfig;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GetServerInfoTest extends TestCase
{
    public function testXml()
    {
        $this->get('/client_interface/xml/?switcher=GetFieldKeys')
            ->assertStatus(404);
    }

    public function testJsonp()
    {
        $response = $this->get('/client_interface/jsonp/?switcher=GetFieldKeys&callback=asdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
        $content = $response->content();
        $this->assertStringStartsWith('/**/asdf([', $content);
        $this->assertStringEndsWith(']);', $content);
    }

    public function testIsList()
    {
        $data = $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->json();
        $this->assertEquals(1, count($data));
    }

    public function testVersion()
    {
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['version' => config('app.version')]);
    }

    public function testNativeLang()
    {
        Config::set('app.locale', 'es');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['nativeLang' => 'es']);
    }

    public function testDefaultDuration()
    {
        LegacyConfig::set('default_duration_time', 'blah');
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['defaultDuration' => 'blah']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testRegionBias()
    {
        LegacyConfig::set('region_bias', 'blah');
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['regionBias' => 'blah']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testDistanceUnits()
    {
        LegacyConfig::set('distance_units', 'blah');
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['distanceUnits' => 'blah']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testSemanticAdmin()
    {
        LegacyConfig::set('enable_semantic_admin', true);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['semanticAdmin' => '1']);
        } finally {
            LegacyConfig::reset();
        }

        LegacyConfig::set('enable_semantic_admin', false);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['semanticAdmin' => '0']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testEmailEnabled()
    {
        LegacyConfig::set('enable_email_contact', true);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['emailEnabled' => '1']);
        } finally {
            LegacyConfig::reset();
        }

        LegacyConfig::set('enable_email_contact', false);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['emailEnabled' => '0']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testEmailIncludesServiceBodies()
    {
        LegacyConfig::set('include_service_body_admin_on_emails', true);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['emailIncludesServiceBodies' => '1']);
        } finally {
            LegacyConfig::reset();
        }

        LegacyConfig::set('include_service_body_admin_on_emails', false);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['emailIncludesServiceBodies' => '0']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testChangesPerMeeting()
    {
        LegacyConfig::set('change_depth_for_meetings', 99999);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['changesPerMeeting' => '99999']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testMeetingsStatesProvinces()
    {
        LegacyConfig::set('meeting_states_and_provinces', []);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['meeting_states_and_provinces' => '']);
        } finally {
            LegacyConfig::reset();
        }

        LegacyConfig::set('meeting_states_and_provinces', ['abc', 'def']);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['meeting_states_and_provinces' => 'abc,def']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testMeetingsCountiesAndSubprovinces()
    {
        LegacyConfig::set('meeting_counties_and_sub_provinces', []);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['meeting_counties_and_sub_provinces' => '']);
        } finally {
            LegacyConfig::reset();
        }

        LegacyConfig::set('meeting_counties_and_sub_provinces', ['abc', 'def']);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['meeting_counties_and_sub_provinces' => 'abc,def']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testGoogleApiKey()
    {
        LegacyConfig::remove('google_api_key');
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['google_api_key' => '']);
        } finally {
            LegacyConfig::reset();
        }

        LegacyConfig::set('google_api_key', 'blah');
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['google_api_key' => 'blah']);
        } finally {
            LegacyConfig::reset();
        }
    }

    public function testMeetingTimeZonesEnabled()
    {
        LegacyConfig::set('meeting_time_zones_enabled', true);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['meeting_time_zones_enabled' => '1']);
        } finally {
            LegacyConfig::reset();
        }

        LegacyConfig::set('meeting_time_zones_enabled', false);
        try {
            $this->get('/client_interface/json/?switcher=GetServerInfo')
                ->assertStatus(200)
                ->assertJsonFragment(['meeting_time_zones_enabled' => '0']);
        } finally {
            LegacyConfig::reset();
        }
    }
}
