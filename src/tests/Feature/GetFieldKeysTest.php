<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

class GetFieldKeysTest extends TestCase
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

    public function testGetFieldKeys()
    {
        $this->get('/client_interface/json/?switcher=GetFieldKeys')
            ->assertStatus(200)
            ->assertExactJson([
                ['key' => 'id_bigint', 'description' => 'ID'],
                ['key' => 'worldid_mixed', 'description' => 'World ID'],
                ['key' => 'service_body_bigint', 'description' => 'Service Body ID'],
                ['key' => 'weekday_tinyint', 'description' => 'Weekday'],
                ['key' => 'venue_type', 'description' => 'Venue Type'],
                ['key' => 'start_time', 'description' => 'Start Time'],
                ['key' => 'duration_time', 'description' => 'Duration'],
                ['key' => 'time_zone', 'description' => 'Time Zone'],
                ['key' => 'formats', 'description' => 'Formats'],
                ['key' => 'lang_enum', 'description' => 'Language'],
                ['key' => 'longitude', 'description' => 'Longitude'],
                ['key' => 'latitude', 'description' => 'Latitude'],
                ['key' => 'meeting_name', 'description' => 'Meeting Name'],
                ['key' => 'location_text', 'description' => 'Location Name'],
                ['key' => 'location_info', 'description' => 'Additional Location Information'],
                ['key' => 'location_street', 'description' => 'Street Address'],
                ['key' => 'location_city_subsection', 'description' => 'Borough'],
                ['key' => 'location_neighborhood', 'description' => 'Neighborhood'],
                ['key' => 'location_municipality', 'description' => 'Town'],
                ['key' => 'location_sub_province', 'description' => 'County'],
                ['key' => 'location_province', 'description' => 'State'],
                ['key' => 'location_postal_code_1', 'description' => 'Zip Code'],
                ['key' => 'location_nation', 'description' => 'Nation'],
                ['key' => 'comments', 'description' => 'Comments'],
                ['key' => 'train_lines', 'description' => 'Train Lines'],
                ['key' => 'bus_lines', 'description' => 'Bus Lines'],
                ['key' => 'phone_meeting_number', 'description' => 'Phone Meeting Dial-in Number'],
                ['key' => 'virtual_meeting_link', 'description' => 'Virtual Meeting Link'],
                ['key' => 'virtual_meeting_additional_info', 'description' => 'Virtual Meeting Additional Info'],
            ]);
    }

    public function testGetFieldKeysItalian()
    {
        $oldLocale = App::currentLocale();
        App::setLocale('it');
        try {
            $this->get('/client_interface/json/?switcher=GetFieldKeys')
                ->assertStatus(200)
                ->assertExactJson([
                    ['key' => 'id_bigint', 'description' => 'ID'],
                    ['key' => 'worldid_mixed', 'description' => 'ID mondiale'],
                    ['key' => 'service_body_bigint', 'description' => 'ID della struttura di servizio'],
                    ['key' => 'weekday_tinyint', 'description' => 'Giorno della settimana'],
                    ['key' => 'venue_type', 'description' => 'Venue Type'],
                    ['key' => 'start_time', 'description' => 'Ora d\'inizio'],
                    ['key' => 'duration_time', 'description' => 'Durata'],
                    ['key' => 'time_zone', 'description' => 'Time Zone'],
                    ['key' => 'formats', 'description' => 'Formati'],
                    ['key' => 'lang_enum', 'description' => 'Lingua'],
                    ['key' => 'longitude', 'description' => 'Longitudine'],
                    ['key' => 'latitude', 'description' => 'Latitudine'],
                    ['key' => 'meeting_name', 'description' => 'Meeting Name'],
                    ['key' => 'location_text', 'description' => 'Location Name'],
                    ['key' => 'location_info', 'description' => 'Additional Location Information'],
                    ['key' => 'location_street', 'description' => 'Street Address'],
                    ['key' => 'location_city_subsection', 'description' => 'Borough'],
                    ['key' => 'location_neighborhood', 'description' => 'Neighborhood'],
                    ['key' => 'location_municipality', 'description' => 'Town'],
                    ['key' => 'location_sub_province', 'description' => 'County'],
                    ['key' => 'location_province', 'description' => 'State'],
                    ['key' => 'location_postal_code_1', 'description' => 'Zip Code'],
                    ['key' => 'location_nation', 'description' => 'Nation'],
                    ['key' => 'comments', 'description' => 'Comments'],
                    ['key' => 'train_lines', 'description' => 'Train Lines'],
                    ['key' => 'bus_lines', 'description' => 'Bus Lines'],
                    ['key' => 'phone_meeting_number', 'description' => 'Phone Meeting Dial-in Number'],
                    ['key' => 'virtual_meeting_link', 'description' => 'Virtual Meeting Link'],
                    ['key' => 'virtual_meeting_additional_info', 'description' => 'Virtual Meeting Additional Info'],
                ]);
        } finally {
            App::setLocale($oldLocale);
        }
    }

    public function testGetFieldKeysAllLocales200()
    {
        $oldLocale = App::currentLocale();
        try {
            $locales = ['de', 'dk', 'en', 'es', 'fa', 'fr', 'it', 'pl', 'pt', 'ru', 'sv'];
            foreach ($locales as $locale) {
                App::setLocale($locale);
                $this->get('/client_interface/json/?switcher=GetFieldKeys')->assertStatus(200);
            }
        } finally {
            App::setLocale($oldLocale);
        }
    }
}
