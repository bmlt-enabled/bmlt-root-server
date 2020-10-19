<?php
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class TimeZoneMigrator
{
    private $multi_time_zone_states = array(
        "oregon",
        "or",
        "idaho",
        "id",
        "nebraska",
        "ne",
        "kansas",
        "ks",
        "texas",
        "tx",
        "north dakota",
        "nd",
        "south dakota",
        "sd",
        "florida",
        "fl",
        "michigan",
        "mi",
        "indiana",
        "in",
        "kentucky",
        "ky",
        "tennessee",
        "tn",
        "alaska",
        "ak"
    );

    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
    public function migrateOne()
    {
        try {
            $location = $this->getNext();
            if (!$location) {
                return false;
            }

            $city = $location[0];
            $zip_code = $location[1];
            $county = $location[2];
            $state = $location[3];
            $country = $location[4];
            if ($city && $state) {
                $location = "$city, $state";
                if ($country) {
                    $location .= ", $country";
                }
            } else {
                $location = "";
                // If city is here we will use it to help with geocoding, but
                // the update will be by country plus county/state/zip
                if ($city) {
                    $location = $city;
                }

                if ($county) {
                    $location .= ($location ? ", $county" : $county);
                }

                if ($state) {
                    $location .= ($location ? ", $state" : $state);
                }

                if ($zip_code) {
                    $location .= ($location ? ", $zip_code" : $zip_code);
                }

                if ($country) {
                    $location .= ($location ? ", $country" : $country);
                }
            }


            $time_zone = $this->getTimeZone($location);
            if (!$time_zone) {
                return;
            }

            if ($city && $state) {
                $this->updateTimeZoneByCityAndState($city, $state, $time_zone);
            } else {
                $this->updateTimeZoneByCountryAndZipOrStateOrCounty($zip_code, $county, $state, $country, $time_zone);
            }
        } catch (Exception $e) {
            // do nothing
        }
    }

    private function updateTimeZoneByCountryAndZipOrStateOrCounty($zip_code, $county, $state, $country, $time_zone)
    {
        $meetings_main = c_comdef_server::GetMeetingTableName_obj() . "_main";
        $meetings_data = c_comdef_server::GetMeetingTableName_obj() . "_data";

        if (!$country) {
            return;
        }
        if (!$zip_code && !$county && !$state) {
            return;
        }

        $conditionTemplate = "    AND\n";
        $conditionTemplate .= "    EXISTS (\n";
        $conditionTemplate .= "        SELECT 1\n";
        $conditionTemplate .= "        FROM $meetings_data\n";
        $conditionTemplate .= "        WHERE\n";
        $conditionTemplate .= "            $meetings_data.meetingid_bigint = $meetings_main.id_bigint\n";
        $conditionTemplate .= "            AND\n";
        $conditionTemplate .= "            $meetings_data.key = ?\n";
        $conditionTemplate .= "            AND\n";
        $conditionTemplate .= "            $meetings_data.data_string = ?\n";
        $conditionTemplate .= "    )\n";

        $sql = "UPDATE $meetings_main\n";
        $sql .= "SET $meetings_main.time_zone = ?\n";
        $sql .= "WHERE\n";
        $sql .= "    EXISTS (\n";
        $sql .= "        SELECT 1\n";
        $sql .= "        FROM $meetings_data\n";
        $sql .= "        WHERE\n";
        $sql .= "            $meetings_data.meetingid_bigint = $meetings_main.id_bigint\n";
        $sql .= "            AND\n";
        $sql .= "            $meetings_data.key = 'location_nation'\n";
        $sql .= "            AND\n";
        $sql .= "            $meetings_data.data_string = ?\n";
        $sql .= "    )\n";

        $params = array($time_zone, $country);
        if ($zip_code) {
            $sql .= $conditionTemplate;
            array_push($params, "location_postal_code_1", $zip_code);
        }
        if ($county) {
            $sql .= $conditionTemplate;
            array_push($params, "location_sub_province", $county);
        }
        if ($state) {
            $sql .= $conditionTemplate;
            array_push($params, "location_province", $state);
        }

        c_comdef_dbsingleton::preparedExec($sql, $params);
    }

    private function updateTimeZoneByCityAndState($city, $state, $time_zone)
    {
        $meetings_main = c_comdef_server::GetMeetingTableName_obj() . "_main";
        $meetings_data = c_comdef_server::GetMeetingTableName_obj() . "_data";

        $sql = "UPDATE $meetings_main\n";
        $sql .= "SET $meetings_main.time_zone = ?\n";
        $sql .= "WHERE\n";
        $sql .= "    EXISTS (\n";
        $sql .= "        SELECT 1\n";
        $sql .= "        FROM $meetings_data\n";
        $sql .= "        WHERE\n";
        $sql .= "            $meetings_data.meetingid_bigint = $meetings_main.id_bigint\n";
        $sql .= "            AND\n";
        $sql .= "            $meetings_data.key = 'location_municipality'\n";
        $sql .= "            AND\n";
        $sql .= "            $meetings_data.data_string = ?\n";
        $sql .= "    )\n";
        $sql .= "    AND\n";
        $sql .= "    EXISTS (\n";
        $sql .= "        SELECT 1\n";
        $sql .= "        FROM $meetings_data\n";
        $sql .= "        WHERE\n";
        $sql .= "            $meetings_data.meetingid_bigint = $meetings_main.id_bigint\n";
        $sql .= "            AND\n";
        $sql .= "            $meetings_data.key = 'location_province'\n";
        $sql .= "            AND\n";
        $sql .= "            $meetings_data.data_string = ?\n";
        $sql .= "    )";

        $params = array($time_zone, $city, $state);

        c_comdef_dbsingleton::preparedExec($sql, $params);
    }

    private function getTimeZone($location)
    {
        // little bit obfuscate
        $url = base64_decode("aHR0cHM6Ly91YWE4cmRjaGcyLmV4ZWN1dGUtYXBpLnVzLWVhc3QtMS5hbWF6b25hd3MuY29tL3Rlc3QvYm1sdC8=");
        $url .= "?location=$location";
        $xml = simplexml_load_file($url);
        if ($xml->status == 'OK') {
            $time_zone = strval($xml->time_zone_id);
            return $time_zone;
        }
        return false;
    }

    private function getNext()
    {
        $meetings_main = c_comdef_server::GetMeetingTableName_obj() . "_main";
        $meetings_data = c_comdef_server::GetMeetingTableName_obj() . "_data";
        
        $sql = "SELECT city, zip_code, county, state, country FROM (\n";
        $sql .= "    SELECT DISTINCT\n";
        $sql .= "        (SELECT data_string FROM $meetings_data WHERE $meetings_data.meetingid_bigint = $meetings_main.id_bigint AND $meetings_data.key = 'location_municipality') as city,\n";
        $sql .= "        (SELECT data_string FROM $meetings_data WHERE $meetings_data.meetingid_bigint = $meetings_main.id_bigint AND $meetings_data.key = 'location_postal_code_1') as zip_code,\n";
        $sql .= "        (SELECT data_string FROM $meetings_data WHERE $meetings_data.meetingid_bigint = $meetings_main.id_bigint AND $meetings_data.key = 'location_sub_province') as county,\n";
        $sql .= "        (SELECT data_string FROM $meetings_data WHERE $meetings_data.meetingid_bigint = $meetings_main.id_bigint AND $meetings_data.key = 'location_province') as state,\n";
        $sql .= "        (SELECT data_string FROM $meetings_data WHERE $meetings_data.meetingid_bigint = $meetings_main.id_bigint AND $meetings_data.key = 'location_nation') as country\n";
        $sql .= "    FROM $meetings_main\n";
        $sql .= "    WHERE $meetings_main.time_zone IS NULL OR $meetings_main.time_zone = \"\"\n";
        $sql .= ") blah\n";
        $sql .= "WHERE\n";
        $sql .= "    (city IS NOT NULL AND state IS NOT NULL)\n";
        $sql .= "    OR\n";
        $sql .= "    (country IS NOT NULL AND (zip_code IS NOT NULL OR state IS NOT NULL OR county IS NOT NULL))\n";

        $result = c_comdef_dbsingleton::preparedQuery($sql);

        if (is_array($result) && count($result)) {
            foreach ($result as $row) {
                $city = $row['city'];
                $zip_code = $row['zip_code'];
                $county = $row['county'];
                $state = $row['state'];
                $country = $row['country'];

                // if city is not set, the country is us or usa, the state has multiple time zones,
                // and the county and zip code are not set... skip. basically, for multi-time zone
                // us states, don't make a decision based on state and country alone
                if (!$city && $state) {
                    if (strtolower($country) == "us" || strtolower($country) == "usa" || strtolower($country) == "united states") {
                        if (in_array(strtolower($state), $this->multi_time_zone_states)) {
                            if (!$county && !$zip_code) {
                                continue;
                            }
                        }
                    }
                }

                return array($city, $zip_code, $county, $state, $country);
            }
        }

        return false;
    }
}
