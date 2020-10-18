<?php
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class TimeZoneMigrator
{
    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
    public function migrateOne()
    {
        try {
            $city_and_state =$this->getNextCityAndState();
            if (!$city_and_state) {
                return;
            }
            $city = $city_and_state[0];
            $state = $city_and_state[1];

            $time_zone = $this->getTimeZone($city, $state);
            if (!$time_zone) {
                return;
            }

            $this->updateTimeZone($city, $state, $time_zone);
        } catch (Exception $e) {
            // do nothing
        }
    }

    private function updateTimeZone($city, $state, $time_zone)
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

    private function getTimeZone($city, $state)
    {
        // little bit obfuscate
        $url = base64_decode("aHR0cHM6Ly91YWE4cmRjaGcyLmV4ZWN1dGUtYXBpLnVzLWVhc3QtMS5hbWF6b25hd3MuY29tL3Rlc3QvYm1sdC8=");
        $url .= "?location=$city,$state";
        $xml = simplexml_load_file($url);
        if ($xml->status == 'OK') {
            $time_zone = strval($xml->time_zone_id);
            return $time_zone;
        }
        return false;
    }

    private function getNextCityAndState()
    {
        $meetings_main = c_comdef_server::GetMeetingTableName_obj() . "_main";
        $meetings_data = c_comdef_server::GetMeetingTableName_obj() . "_data";

        $sql =  "SELECT location_municipality, location_province FROM (\n";
        $sql .= "    SELECT DISTINCT\n";
        $sql .= "        (SELECT data_string FROM $meetings_data WHERE $meetings_data.meetingid_bigint = $meetings_main.id_bigint AND $meetings_data.key = 'location_municipality') as location_municipality,\n";
        $sql .= "        (SELECT data_string FROM $meetings_data WHERE $meetings_data.meetingid_bigint = $meetings_main.id_bigint AND $meetings_data.key = 'location_province') as location_province\n";
        $sql .= "    FROM $meetings_main\n";
        $sql .= "    WHERE $meetings_main.time_zone IS NULL OR $meetings_main.time_zone = \"\"\n";
        $sql .= ") blah\n";
        $sql .= "WHERE location_municipality IS NOT NULL AND location_province IS NOT NULL\n";
        $sql .= "LIMIT 1";

        $result = c_comdef_dbsingleton::preparedQuery($sql);

        if (is_array($result) && count($result)) {
            $result = $result[0];
            $city = $result['location_municipality'];
            $state = $result['location_province'];
            return array($city, $state);
        }

        return false;
    }
}
