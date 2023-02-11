<?php

namespace Tests\Feature;

use App\LegacyConfig;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GetServerInfoTest extends TestCase
{
    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    public function testJsonp()
    {
        $content = $this->get('/client_interface/jsonp/?switcher=GetServerInfo&callback=asdf')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8')
            ->content();
        $this->assertStringStartsWith('/**/asdf([', $content);
        $this->assertStringEndsWith(']);', $content);
    }

    public function testIsList()
    {
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testVersion()
    {
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['version' => config('app.version')]);
    }

    public function testVersionIntBeta()
    {
        Config::set('app.version', '3.0.2-beta');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['versionInt' => '3000002']);
    }

    public function testVersionIntNonBeta()
    {
        Config::set('app.version', '3.0.2');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['versionInt' => '3000002']);
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
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['defaultDuration' => 'blah']);
    }

    public function testRegionBias()
    {
        LegacyConfig::set('region_bias', 'blah');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['regionBias' => 'blah']);
    }

    public function testDistanceUnits()
    {
        LegacyConfig::set('distance_units', 'blah');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['distanceUnits' => 'blah']);
    }

    public function testSemanticAdmin()
    {
        LegacyConfig::set('enable_semantic_admin', true);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['semanticAdmin' => '1']);

        LegacyConfig::set('enable_semantic_admin', false);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['semanticAdmin' => '0']);
    }

    public function testEmailEnabled()
    {
        LegacyConfig::set('enable_email_contact', true);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['emailEnabled' => '1']);

        LegacyConfig::set('enable_email_contact', false);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['emailEnabled' => '0']);
    }

    public function testEmailIncludesServiceBodies()
    {
        LegacyConfig::set('include_service_body_admin_on_emails', true);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['emailIncludesServiceBodies' => '1']);

        LegacyConfig::set('include_service_body_admin_on_emails', false);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['emailIncludesServiceBodies' => '0']);
    }

    public function testChangesPerMeeting()
    {
        LegacyConfig::set('change_depth_for_meetings', 99999);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['changesPerMeeting' => '99999']);
    }

    public function testMeetingsStatesProvinces()
    {
        LegacyConfig::set('meeting_states_and_provinces', []);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['meeting_states_and_provinces' => '']);

        LegacyConfig::set('meeting_states_and_provinces', ['abc', 'def']);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['meeting_states_and_provinces' => 'abc,def']);
    }

    public function testMeetingsCountiesAndSubprovinces()
    {
        LegacyConfig::set('meeting_counties_and_sub_provinces', []);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['meeting_counties_and_sub_provinces' => '']);

        LegacyConfig::set('meeting_counties_and_sub_provinces', ['abc', 'def']);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['meeting_counties_and_sub_provinces' => 'abc,def']);
    }

    public function testGoogleApiKey()
    {
        LegacyConfig::remove('google_api_key');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['google_api_key' => '']);

        LegacyConfig::set('google_api_key', 'blah');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['google_api_key' => 'blah']);
    }

    public function testMeetingTimeZonesEnabled()
    {
        LegacyConfig::set('meeting_time_zones_enabled', true);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['meeting_time_zones_enabled' => '1']);

        LegacyConfig::set('meeting_time_zones_enabled', false);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['meeting_time_zones_enabled' => '0']);
    }

    public function testCenterLongitude()
    {
        LegacyConfig::remove('search_spec_map_center_longitude');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['centerLongitude' => '']);

        LegacyConfig::set('search_spec_map_center_longitude', -79.793701171875);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['centerLongitude' => '-79.793701171875']);
    }

    public function testCenterLatitude()
    {
        LegacyConfig::remove('search_spec_map_center_latitude');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['centerLatitude' => '']);

        LegacyConfig::set('search_spec_map_center_latitude', 36.065752051707);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['centerLatitude' => '36.065752051707']);
    }

    public function testCenterZoom()
    {
        LegacyConfig::remove('search_spec_map_center_zoom');
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['centerZoom' => '']);

        LegacyConfig::set('search_spec_map_center_zoom', 10);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['centerZoom' => '10']);
    }

    public function testAutoGeocodingEnabled()
    {
        LegacyConfig::set('auto_geocoding_enabled', true);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['auto_geocoding_enabled' => true]);

        LegacyConfig::set('auto_geocoding_enabled', false);
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['auto_geocoding_enabled' => false]);
    }

    public function testCommit()
    {
        $this->get('/client_interface/json/?switcher=GetServerInfo')
            ->assertStatus(200)
            ->assertJsonFragment(['commit' => config('app.commit')]);
    }
}
