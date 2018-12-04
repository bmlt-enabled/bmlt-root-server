<?php
/***********************************************************************/
/** \file   c_comdef_data_importer.class.php

    \brief  This file contains a range of functions to be used by BMLT database importing scripts.
    Including this file instantiates a BMLT root server.
*/

defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.
ini_set('auto_detect_line_endings', 1);
define("__FILE_DUMP_DEFAULT_FILENAME__", "input_file");

$config_file_path = dirname(__FILE__).'/../../server/config/get-config.php';

if (file_exists($config_file_path)) {
    include($config_file_path);
}

require_once(dirname(__FILE__).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once(dirname(__FILE__).'/../../server/c_comdef_server.class.php');
$supress_header = true;
require_once(dirname(__FILE__).'/c_comdef_login.php');

class c_comdef_data_importer
{
    var $m_server;
    var $m_local_strings;
    
    /***********************************************************************/
    /**
        \brief Object Constructor.
    */
    function __construct()
    {
    // We actually instantiate a root server, here.
        $this->m_server = c_comdef_server::MakeServer();
    
        if (!($this->m_server instanceof c_comdef_server)) {
            die('Cannot instantiate the root server!');
        }
        
        if ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) {
            require_once(dirname(__FILE__).'/lang/'.$lang_enum.'/data_transfer_strings.php');

            $this->m_local_strings = $comdef_data_transfer_strings;
        } else {
            die('NOT AUTHORIZED');
        }
    }
    
    /***********************************************************************/
    /**
        \brief Opens a tab- or comma-delimited file, and loads it into an associative array.
        \returns an associative array with the file contents.
    */
    function bmlt_get_delimited_file_contents( $in_default_filename = null ///< The default filename
                                                )
    {
        if (!$in_default_filename) {
            $in_default_filename = __FILE_DUMP_DEFAULT_FILENAME__;
        }

        $ret = null;

        if (!file_exists($in_default_filename)) {
            $file = $in_default_filename.".tsv";

            if (!file_exists($file)) {
                $file = $in_default_filename.".txt";
            }
    
            if (!file_exists($file)) {
                $file = $in_default_filename.".csv";
            }
        } else {
            $file = in_default_filename;
        }
    
        if (file_exists($file)) {
            $keys = null;
            $file_handle = fopen($file, "r");
    
            if ($file_handle) {
                $ret = array();
                $delimiter = ",";
        
                $key_line = fgetcsv($file_handle, 1000, $delimiter);
        
                if (!is_array($key_line) || !(count($key_line) > 2)) {
                    $delimiter = "\t";
                    rewind($file_handle);
                    $key_line = fgetcsv($file_handle, null, $delimiter);
                }

                while (($data = fgetcsv($file_handle, null, $delimiter) ) !== false) {
                    $data = array_combine($key_line, $data);
                    array_push($ret, $data);
                }
        
                fclose($handle);
            }
        }

        return $ret;
    }

    /***********************************************************************/
    /**
        \brief Adds one meeting to the database.

        \returns a boolean. This is true if the meeting was successfully added.
    */
    function AddOneMeeting(
        &$in_out_meeting_array,  ///< The meeting data, as an associative array. It must have the exact keys for the database table columns. No prompts, data type, etc. That will be supplied by the routine. Only a value. The 'id_bigint' field will be set to the new meeting ID.
        $in_templates_array     ///< This contains the key/value templates for the meeting data.
    ) {
        $ret = false;
        
        // We break the input array into elements destined for the main table, and elements destined for the data table(s).
        $in_meeting_array_main = array_intersect_key($in_out_meeting_array, $this->m_local_strings['key_strings']);
        $in_meeting_array_other = array_diff_key($in_out_meeting_array, $this->m_local_strings['key_strings']);
        
        // OK, we'll be creating a PDO prepared query, so we break our main table data into keys, placeholders and values.
        $keys = array();
        $values = array();
        $values_placeholders = array();
        foreach ($in_meeting_array_main as $key => $value) {
            array_push($keys, $key);
            array_push($values, $value);
            array_push($values_placeholders, '?');
        }
        
        // Now that we have the main table keys, placeholders and arrays, we create the INSERT query and add the meeting's main data.
        $keys = "(`".implode("`,`", $keys)."`)";
        $values_placeholders = "(".implode(",", $values_placeholders).")";
        $sql = "INSERT INTO `".$this->m_server->GetMeetingTableName_obj()."_main` $keys VALUES $values_placeholders";
        
        try // Catch any thrown exceptions.
            {
            $result = c_comdef_dbsingleton::preparedExec($sql, $values);
            
            // If that was successful, we extract the ID for the meeting.
            if ($result) {
                $ret = true;
                
                $sql = "SELECT LAST_INSERT_ID()";
                $row2 = c_comdef_dbsingleton::preparedQuery($sql, array());
                if (is_array($row2) && count($row2) == 1) {
                    $meeting_id = intval($row2[0]['last_insert_id()']);
                } else {
                    die("Can't get the meeting ID!");
                }
                
                $in_out_meeting_array['id_bigint'] = $meeting_id;
                
                // OK. We have now created the basic meeting info, and we have the ID necessary to create the key/value pairs for the data tables.
                // In 99% of the cases, we will only fill the _data table. However, we should check for long data, in case we need to use the _longdata table.
                $data_values = null;
                $londata_values = null;
                
                // Here, we simply extract the parts of the array that correspond to the data and longdata tables.
                if (isset($in_templates_array['data']) && is_array($in_templates_array['data']) && count($in_templates_array['data'])) {
                    $data_values = array_intersect_key($in_out_meeting_array, $in_templates_array['data']);
                }
                
                if (isset($in_templates_array['longdata']) && is_array($in_templates_array['longdata']) && count($in_templates_array['longdata'])) {
                    $londata_values = array_intersect_key($in_out_meeting_array, $in_templates_array['longdata']);
                }
                
                // What we do here, is expand each of the input key/value pairs to have the characteristics assigned by the template for that key.
                foreach ($data_values as $key => &$data_value) {
                    $val = $data_value; // We replace a single value with an associative array, so save the value.
                    if (isset($val)) {
                        $data_value = array();
                        $data_value['meetingid_bigint'] = $meeting_id;
                        $data_value['key'] = $key;
                        $data_value['field_prompt'] = $in_templates_array['data'][$key]['field_prompt'];
                        $data_value['lang_enum'] = $in_templates_array['data'][$key]['lang_enum'];
                        $data_value['visibility'] = $in_templates_array['data'][$key]['visibility'];
                        if (isset($in_templates_array['data'][$key]['data_bigint']) && $in_templates_array['data'][$key]['data_bigint']) {
                            $data_value['data_bigint'] = intval($val);
                            $data_value['data_double'] = null;
                            $data_value['data_string'] = null;
                        } elseif (isset($in_templates_array['data'][$key]['data_double']) && $in_templates_array['data'][$key]['data_double']) {
                            $data_value['data_double'] = floatval($val);
                            $data_value['data_bigint'] = null;
                            $data_value['data_string'] = null;
                        } elseif (isset($in_templates_array['data'][$key]['data_string']) && $in_templates_array['data'][$key]['data_string']) {
                            $data_value['data_string'] = $val;
                            $data_value['data_bigint'] = null;
                            $data_value['data_double'] = null;
                        }
                    } else {
                        $data_value = null;
                        unset($data_value);
                    }
                }

                foreach ($londata_values as $key => &$londata_value) {
                    $val = $data_value; // We replace a single value with an associative array, so save the value.
                    if (isset($val)) {
                        $londata_value['meetingid_bigint'] = $meeting_id;
                        $londata_value['key'] = $key;
                        $londata_value['field_prompt'] = $in_templates_array['data'][$key]['field_prompt'];
                        $londata_value['lang_enum'] = $in_templates_array['data'][$key]['lang_enum'];
                        $londata_value['visibility'] = $in_templates_array['data'][$key]['visibility'];
                        if ((isset($in_templates_array['longdata'][$key]['data_longtext']) && $in_templates_array['longdata'][$key]['data_longtext'])) {
                            $londata_value['data_longtext'] = $val;
                            $londata_value['data_blob'] = null;
                        } elseif ((isset($in_templates_array['longdata'][$key]['data_blob']) && $in_templates_array['longdata'][$key]['data_blob'])) {
                            $londata_value['data_blob'] = $val;
                            $londata_value['data_longtext'] = null;
                        } else {
                            $londata_value = null;
                        }
                    } else {
                        $londata_value = null;
                        unset($londata_value);
                    }
                }
                    
                // OK. At this point, we have 2 arrays, one that corresponds to entries into the _data table, and the other into the _longdata table. Time to insert the data.
                
                // First, we do the data array.
                if (isset($data_values) && is_array($data_values) && count($data_values)) {
                    foreach ($data_values as $value) {
                        if (isset($value) && is_array($value) && count($value)) {
                            $keys = array();
                            $values = array();
                            $values_placeholders = array();
                        
                            foreach ($value as $key => $val) {
                                array_push($keys, $key);
                                array_push($values, $val);
                                array_push($values_placeholders, '?');
                            }
                    
                            if (is_array($values) && count($values)) {
                                // Now that we have the main table keys, placeholders and arrays, we create the INSERT query and add the meeting's main data.
                                $keys = "(`".implode("`,`", $keys)."`)";
                                $values_placeholders = "(".implode(",", $values_placeholders).")";
                                $sql = "INSERT INTO `".$this->m_server->GetMeetingTableName_obj()."_data` $keys VALUES $values_placeholders";
                                $result = c_comdef_dbsingleton::preparedExec($sql, $values);
                            }
                        }
                    }
                }
                
                // Next, we do the longdata array.
                if (isset($londata_values) && is_array($londata_values) && count($londata_values)) {
                    foreach ($londata_values as $value) {
                        if (isset($value) && is_array($value) && count($value)) {
                            $keys = array();
                            $values = array();
                            $values_placeholders = array();
                        
                            foreach ($value as $key => $val) {
                                array_push($keys, $key);
                                array_push($values, $val);
                                array_push($values_placeholders, '?');
                            }
                            
                            if (is_array($values) && count($values)) {
                                // Now that we have the longdata table keys, placeholders and arrays, we create the INSERT query and add the meeting's main data.
                                $keys = "(`".implode("`,`", $keys)."`)";
                                $values_placeholders = "(".implode(",", $values_placeholders).")";
                                $sql = "INSERT INTO `".$this->m_server->GetMeetingTableName_obj()."_longdata` $keys VALUES $values_placeholders";
                                $result = c_comdef_dbsingleton::preparedExec($sql, $values);
                            }
                        }
                    }
                }
            } else {
                die("Can't create a new meeting!");
            }
        } catch (Exception $e) {
            die('<pre>'.htmlspecialchars(print_r($e, true)).'</pre>');
        }
        
        return $ret;
    }
    
    /***********************************************************************/
    /**
        \brief Fetch the ID = 0 templates for the key/value pairs.

        \returns an associative array, with two main sub-arrays: 'data' and 'longdata'.
        Each will contain an associative array with keys equal to the database keys, and the following fields:
            - 'field_prompt'
            - 'lang_enum'
            - 'visibility'
            - The 'data' array will only have one of these:
                - 'data_string'
                - 'data_bigint'
                - 'data_double'
            - The 'longdata' array will only have one of these:
                - 'data_longtext'
                - 'data_blob'
    */
    function FetchTemplates()
    {
        $ret = null;
        
        $retData = array();
        $retLongData = array();
        
        try // Catch any thrown exceptions.
            {
            $sql = "SELECT * FROM `".$this->m_server->GetMeetingTableName_obj()."_data` WHERE `meetingid_bigint` = 0";
            $row_result = c_comdef_dbsingleton::preparedQuery($sql, array());
            
            if (is_array($row_result) && count($row_result) > 0) {
                foreach ($row_result as $row) {
                    $retData[$row['key']]['field_prompt'] = $row['field_prompt'];
                    $retData[$row['key']]['lang_enum'] = $row['lang_enum'];
                    $retData[$row['key']]['visibility'] = $row['visibility'];
                    $retData[$row['key']]['data_string'] = $row['data_string'];
                    $retData[$row['key']]['data_bigint'] = $row['data_bigint'];
                    $retData[$row['key']]['data_double'] = $row['data_double'];
                }
            }
            
            $sql = "SELECT * FROM `".$this->m_server->GetMeetingTableName_obj()."_longdata` WHERE `meetingid_bigint` = 0";
            $row_result = c_comdef_dbsingleton::preparedQuery($sql, array());
            
            if (is_array($row_result) && count($row_result) > 0) {
                foreach ($row_result as $row) {
                    $retLongData[$row['key']]['field_prompt'] = $row['field_prompt'];
                    $retLongData[$row['key']]['lang_enum'] = $row['lang_enum'];
                    $retLongData[$row['key']]['visibility'] = $row['visibility'];
                    if (array_key_exists($row, 'data_longtext')) {
                        $retLongData[$row['key']]['data_longtext'] = $row['data_longtext'];
                    } elseif (array_key_exists($row, 'data_blob')) {
                        $retLongData[$row['key']]['data_blob'] = $row['data_blob'];
                    }
                }
            }
        } catch (Exception $e) {
            die('<pre>'.htmlspecialchars(print_r($e, true)).'</pre>');
        }
        
        if (is_array($retData) && count($retData)) {
            $ret['data'] = $retData;
        }
        
        if (is_array($retLongData) && count($retLongData)) {
            $ret['longdata'] = $retLongData;
        }
        
        return $ret;
    }

    /***********************************************************************/
    /** \brief  Reads the Service bodies from the database, and returns an associative
                array that can be used to match them to World IDs, or to clear out
                previous data for those Service bodies.

        \returns an array of associative arrays, containing the Service body data.
                Each array element represent one Service body that is available for
                meetings. All that Service body's BMLT data is available in the array element
                (which is an associative array). The Array element key is the World ID of
                the Service body, so it can be used to match up with imported data.
    */
    function extract_service_bodies(   $in_file_contents   ///< The parsed "raw" file contents.
                                    )
    {
        $ret = null;
        
        try // Catch any thrown exceptions.
            {
            $sql = "SELECT * FROM `".$this->m_server->GetServiceBodiesTableName_obj()."`";    // Get every Service body
            $row_result = c_comdef_dbsingleton::preparedQuery($sql, array());
            if (is_array($row_result) && count($row_result) > 0) {
                $ret = array();
                foreach ($row_result as $row) {
                    if (trim(strtoupper($row['worldid_mixed']))) {
                        $ret[trim(strtoupper($row['worldid_mixed']))] = $row;
                    }
                }
            }
        } catch (Exception $e) {
            die('<pre>'.htmlspecialchars(print_r($e, true)).'</pre>');
        }
        
        return $ret;
    }
    
    /***********************************************************************/
    /** \brief  This deletes all meeting data, and changes (permanent delete)
                for all meeting data for the Service bodies passed in via the
                array.
    */
    function DeleteAllOldMeetings( $in_sb_array    ///< An array of Service body IDs. Only meetings in these IDs will be deleted
                                    )
    {
        $ret = '';
        // We don't do this relationally, because this is not always gonna be used for MySQL, and this is more flexible (and possibly faster).
        if (is_array($in_sb_array) && count($in_sb_array)) {
            $subsql = "";
            $id_array = array ();
            $values = array();
            foreach ($in_sb_array as $id) {
                if (intval($id)) {
                    if ($subsql) {
                        $subsql .= " OR ";
                    }
                    $subsql .= "(".$this->m_server->GetMeetingTableName_obj()."_main.service_body_bigint=?)";
                    array_push($values, $id);
                }
            }
            $subsql = "($subsql)";
            
            try {
                $sql = "SELECT id_bigint FROM `".$this->m_server->GetMeetingTableName_obj()."_main` WHERE $subsql";
        
                $rows = c_comdef_dbsingleton::preparedQuery($sql, $values);
                
                if (is_array($rows) && count($rows)) {
                    foreach ($rows as $row) {
                        $r = intval($row['id_bigint']);
                        
                        if ($r) {
                            array_push($id_array, $r);
                        }
                    }
                }
                
                if (is_array($id_array) && count($id_array)) {
                    $sql = "DELETE FROM `".$this->m_server->GetMeetingTableName_obj()."_main` WHERE $subsql";
            
                    c_comdef_dbsingleton::preparedExec($sql, $values);
        
                    $ret .= count($id_array)." meetings were deleted.<br />";
                    
                    $data_count = 0;
                    $longdata_count = 0;
                    $change_count = 0;
                    foreach ($id_array as $id) {
                        if ($id > 0) {  // Don't delete the placeholders.
                            $sql = "DELETE FROM `".$this->m_server->GetMeetingTableName_obj()."_data` WHERE meetingid_bigint=?";
                
                            c_comdef_dbsingleton::preparedExec($sql, array ( $id ));
                            
                            $sql = "DELETE FROM `".$this->m_server->GetMeetingTableName_obj()."_longdata` WHERE meetingid_bigint=?";
                
                            c_comdef_dbsingleton::preparedExec($sql, array ( $id ));
                            
                            $sql = "DELETE FROM `".$this->m_server->GetChangesTableName_obj()."` WHERE object_class_string='c_comdef_meeting' AND (before_id_bigint=? OR after_id_bigint=?)";
                
                            c_comdef_dbsingleton::preparedExec($sql, array ( $id, $id ));
                        }
                    }
                }
            } catch (Exception $e) {
                die('<pre>'.htmlspecialchars(print_r($e, true)).'</pre>');
            }
        }
        
        return $ret;
    }
    
    /***********************************************************************/
    /**
        \brief Given an address string, return a geocode.

        \returns an array of string. The contents are as follows:
            - These will appear in all results:
                - 'original'
                    The original address string
                - 'google_key'
                    The Google API key

            - This may be set if the call was not complete. It is an integer:
                - 'status'
                    The numerical status code for the failure.

            - These three fields will be in every reply:
                - 'accuracy'
                    This is the accuracy of the reply. It is an integer, 0-10.
                - 'long'
                    The longitude, in degrees. This is a floating-point number.
                - 'lat'
                    The latitude, in degrees. This is a floating-point number.

            - These fields will be in most replies, depending upon what the Geocoder finds. They are all strings:
                - 'nation'
                    The nation. This should be a two-letter code.
                - 'state'
                    The state or province name.
                - 'county'
                    The county or shire name.
                - 'town'
                    The municipality, town or city.
                - 'borough'
                    The incorporated city subsection.
                - 'street'
                    The street address.
                - 'zip'
                    The postal or zip code.
    */
    function GeocodeAddress(
        $in_address,    ///< The address, in a single string, to be sent to the geocoder.
        $in_gkey,       ///< The Google API key.
        $in_region = null ///< If there is a nation region bias, it is entered here.
    ) {
        $ret = array('original' => $in_address,'google_key' => $in_gkey);
        $status = null;
        $uri = 'http://maps.googleapis.com/maps/api/geocode/xml?address='.urlencode($in_address).'&sensor=false';
        
        if ($in_region) {
            $uri .= '&region='.strtolower(trim($in_region));
        }
        
        do {
            $retry = false;
        
            $xml = simplexml_load_file($uri);
            $status = $xml->status;
            $result = $xml->result;
            
            if ($xml->status == 'OK') {
                $retry = false;
                $ret = xml_parser_parse_xml($xml->result);
            } elseif (!$retry) {
                $retry = true;
                $t = microtime_float() + 2; // Two second delay.
                while (microtime_float() <= $t) {
                };   // Real, REAL basic delay, because Google doesn't like 'bots.
            } else {
                $ret = null;
                break;
            }
        } while ($retry);
    
        return $ret;
    }
    
    /***********************************************************************/
    /**
        \brief  A basic function to parse the XML reply from Google Geocode, and return usable fields.

        \returns an array of string. The contents are as follows:
            - These three fields will be in every reply:
                - 'accuracy'
                    This is the accuracy of the reply. It is an integer, 0-10.
                - 'long'
                    The longitude, in degrees. This is a floating-point number.
                - 'lat'
                    The latitude, in degrees. This is a floating-point number.
            - These fields will be in most replies, depending upon what the Geocoder finds. They are all strings:
                - 'nation'
                    The nation. This should be a two-letter code.
                - 'state'
                    The state or province name.
                - 'county'
                    The county or shire name.
                - 'town'
                    The municipality, town or city.
                - 'borough'
                    The incorporated city subsection.
                - 'street'
                    The street address.
                - 'zip'
                    The postal or zip code.

    */
    function xml_parser_parse_xml( $in_xml ///< The XML data, as a <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML</a> object.
                                    )
    {
        $ret = null;
    
        if (isset($in_xml->geometry) && isset($in_xml->geometry->location) && isset($in_xml->geometry->location->lng) && isset($in_xml->geometry->location->lat)) {
            $ret ['long'] = floatval($in_xml->geometry->location->lng);
            $ret ['lat'] = floatval($in_xml->geometry->location->lat);
        }

        $ret ['nation'] = '';
        $ret ['state'] = '';
        $ret ['county'] = '';
        $ret ['town'] = '';
        $ret ['borough'] = '';
        $ret ['street'] = '';
        $ret ['zip'] = '';
    
        return $ret;
    }
    
    /***********************************************************************/
    /**
    */
    function BuildAddress( $row    ///< A meeting data array.
                            )
    {
        $address = '';
        
        if (isset($row['_location_street_address']) && trim($row['_location_street_address'])) {
            $address .= trim($row['_location_street_address']);
        }
            
        if (isset($row['_location_city_subsection']) && trim($row['_location_city_subsection'])) {
            if ($address) {
                $address .= ', ';
            }
            $address .= ucwords(strtolower(trim($row['_location_city_subsection'])));
        }
            
        if (isset($row['_location_town']) && trim($row['_location_town'])) {
            if ($address) {
                $address .= ', ';
            }
            $address .= ucwords(strtolower(trim($row['_location_town'])));
        }
            
        if (isset($row['location_province']) && trim($row['location_province'])) {
            if ($address) {
                $address .= ', ';
            }
            $address .= trim($row['location_province']);
        }
        
        if (isset($row['_location_zip']) && trim($row['_location_zip'])) {
            if ($address) {
                $address .= ' ';
            }
            $address .= trim($row['_location_zip']);
        }
            
        if (isset($row['_location_nation']) && trim($row['_location_nation'])) {
            if ($address) {
                $address .= ' ';
            }
            $address .= trim($row['_location_nation']);
        }
        
        return $address;
    }
    
    /***********************************************************************/
    /**
        \brief This function validates email addresses. The input can be a single email
        address, or a series of comma-delimited addresses.

        If any of the addresses fails a simple test (must have an "@," and at least one
        period (.) in the second part), the function returns false.

        \global $g_validation_error This contains a "log" of the errors, as an array.

        \returns true, if the email is valid, false, otherwise.
    */
    function ValidEmailAddress(    $in_test_address    ///< Either a single email address, or a list of them, comma-separated.
                                    )
    {
        $valid = false;
        
        if ($in_test_address) {
            global $g_validation_error; ///< This contains an array of strings, that "log" bad email addresses.
            $g_validation_error = array();
            $addr_array = explode(",", $in_test_address);
            // Start off optimistic.
            $valid = true;
            
            // If we have more than one address, we iterate through each one.
            foreach ($addr_array as $addr_elem) {
                // This splits any name/address pair (ex: "Jack Schidt" <jsh@spaz.com>)
                $addr_temp = preg_split("/ </", $addr_elem);
                if (count($addr_temp) > 1) { // We also want to trim off address brackets.
                    $addr_elem = trim($addr_temp[1], "<>");
                } else {
                    $addr_elem = trim($addr_temp[0], "<>");
                }
                $regexp = "/^([_a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+)(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/";
                if (!preg_match($regexp, $addr_elem)) {
                    array_push($g_validation_error, 'The address'." '$addr_elem' ".'is not correct.');
                    $valid = false;
                }
            }
        }
        
        return $valid;
    }
    
    /***********************************************************************/
    /**
    */
    function format_sorter($a, $b)
    {
        if ($a == $b) {
            return 0;
        } else {
            return ( $a < $b ) ? -1 : 1;
        }
    }
    
    /***********************************************************************/
    /**
    */
    function translate_format_code($in_code)
    {
        global $format_array;
        if (key_exists($in_code, $format_array)) {
            return $format_array[$in_code];
        } else {
            return null;
        }
    }
    
    /***********************************************************************/
    /**
    */
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /***********************************************************************/
    /** \brief  Converts an English full-day ('Sunday', 'Monday', etc.) to a BMLT number (1-7). Case counts.

        \returns an integer. 1-7 (Sun-Sat), or null, if the day was not found.
    */
    function func_convert_from_english_full_weekday(   $in_weekday ///< The day of the week, spelled out, in English ('Sunday' -> 'Saturday').
                                                    )
    {
        $ret = null;
        
        $ret = array_search($in_weekday, $this->m_local_strings['days']);
        
        if ($ret !== false) {
            $ret = min(6, max(0, intval($ret)));
        }
            
        return $ret;
    }
    
    /***********************************************************************/
    /** \brief  Converts an integer time in simple military format to an SQL-format time (HH:MM:SS) as a string.

        \returns a string, with the time as full military.
    */
    function func_start_time_from_simple_military( $in_military_time   ///< The military time as an integer (100s are hours 0000 -> 2359).
                                                    )
    {
        $time = abs(intval($in_military_time));
        $hours = min(23, $time / 100);
        $minutes = min(59, ($time - (intval($time / 100) * 100)));
        
        return sprintf("%d:%02d:00", $hours, $minutes);
    }
};
