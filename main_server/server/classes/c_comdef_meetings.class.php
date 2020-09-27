<?php
/***********************************************************************/
/** \file   c_comdef_meetings.class.php
    \brief The file for the c_comdef_meetings class.

    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

require_once(dirname(__FILE__)."/c_comdef_meeting.class.php");

/***********************************************************************/
/** \class c_comdef_meetings
    \brief  A class to hold a collection of c_comdef_meeting objects.

***********************************************************************/
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class c_comdef_meetings implements i_comdef_has_parent
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
{
    /// A reference to the "parent" object for this instance.
    private $_local_id_parent_obj = null;
    
    /// An array of new c_comdef_meeting objects that comprises the content.
    private $_meetings_objects = null;
    
    /// An array of references to the c_comdef_format objects that are used in this dataset.
    private $_formats_used = null;
    
    /// An array of references to the c_comdef_service_body objects that are used in this dataset.
    private $_service_bodies_used = null;
    
    /// An array of string. This is used during sorting, to indicate which key is being sorted.
    private $_sort_key = null;
    
    /** A string. This is used during sorting, to indicate which direction to take for the sort.
            It is either:
                - "asc" -Ascending
                - "desc" -Descending
    */
    private $_sort_dir = null;
    
    /// This is true if the meetings have a distance by which they can be sorted, and the sort should be done with it.
    private $_sort_search_by_distance = false;
    
    /*******************************************************************/
    /** \brief Constructor

    */
    public function __construct(
        $in_parent_obj,
        $in_meetings_data
    ) {
        $this->SetParentObj($in_parent_obj);
        $this->CreateMeetingObjects($in_meetings_data);
    }
    
    /*******************************************************************/
    /** \brief Run through the list, and remove meetings that do not show up as "valid."

        If a user is logged in, they see dead meetings.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function RemoveInvalidMeetings()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $cur_user = c_comdef_server::GetCurrentUserObj();
        
        // Logged-in user sees dead meetings.
        if (!($cur_user instanceof c_comdef_user)) {
            $cull_list = array();
            
            foreach ($this->_meetings_objects as &$one_meeting) {
                if ($one_meeting instanceof c_comdef_meeting) {
                    if (!$one_meeting->IsValidMeeting()) {
                        array_push($cull_list, $one_meeting->GetID());
                    }
                }
            }
        
            foreach ($cull_list as $id) {
                $this->_meetings_objects[$id] = null;
                unset($this->_meetings_objects[$id]);
            }
        }
    }

    /*******************************************************************/
    /**
        \brief Uses the Vincenty calculation to estimate a distance between
        the two given lat/long pairs, then returns true or false as to whether
        or not the distance between them falls within the given distance (in KM).

        \returns a Boolean. true, if the points are within the distance given.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function IsInDistance(
        $lat1,              ///< These four parameters are the given two points long/lat, in degrees.
        $lon1,
        $lat2,
        $lon2,
        $in_distance_in_KM, ///< The distance they must be within to return true (in Kilometers).
        &$out_dist_in_KM    ///< This is an optional field that allows the calculated distance to be returned (in KM).
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $dist = self::GetDistance($lat1, $lon1, $lat2, $lon2);
        
        if (isset($out_dist_in_KM)) {
            $out_dist_in_KM = $dist;
        }
    
        return ( floatval($dist) <= floatval($in_distance_in_KM) );
    }
    
    /*******************************************************************/
    /**
        \brief Uses the Vincenty calculation to estimate a distance between
        the two given lat/long pairs, then returns true or false as to whether
        or not the distance between them falls within the given distance (in KM).

        \returns a Float with the distance, in kilometers.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function GetDistance(
        $lat1,              ///< These four parameters are the given two points long/lat, in degrees.
        $lon1,
        $lat2,
        $lon2
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $a = 6378137;
        $b = 6356752.3142;
        $f = 1/298.257223563;  // WGS-84 ellipsiod
        $L = ($lon2-$lon1)/57.2957795131;
        $U1 = atan((1.0-$f) * tan($lat1/57.2957795131));
        $U2 = atan((1.0-$f) * tan($lat2/57.2957795131));
        $sinU1 = sin($U1);
        $cosU1 = cos($U1);
        $sinU2 = sin($U2);
        $cosU2 = cos($U2);
          
        $lambda = $L;
        $lambdaP = $L;
        $iterLimit = 100;
        
        do {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSigma = sqrt(($cosU2*$sinLambda) * ($cosU2*$sinLambda) + ($cosU1*$sinU2-$sinU1*$cosU2*$cosLambda) * ($cosU1*$sinU2-$sinU1*$cosU2*$cosLambda));
            if ($sinSigma==0) {
                return true;  // co-incident points
            }
            $cosSigma = $sinU1*$sinU2 + ($cosU1*$cosU2*$cosLambda);
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = ($cosU1 * $cosU2 * $sinLambda) / $sinSigma;
            $cosSqAlpha = 1.0 - $sinAlpha*$sinAlpha;
            $cos2SigmaM = $cosSigma - 2.0*$sinU1*$sinU2/$cosSqAlpha;
//          if (isNaN(cos2SigmaM))
//              {
//              cos2SigmaM = 0;  // equatorial line: cosSqAlpha=0 (§6)
//              }
            $C = $f/(16.0*$cosSqAlpha*(4.0+$f*(4.0-3.0*$cosSqAlpha)));
            $lambdaP = $lambda;
            $lambda = $L + (1.0-$C) * $f * $sinAlpha * ($sigma + $C*$sinSigma*($cos2SigmaM+$C*$cosSigma*(-1.0+2.0*$cos2SigmaM*$cos2SigmaM)));
        } while (abs($lambda-$lambdaP) > 1e-12 && --$iterLimit>0);

//      if ($iterLimit==0) return NaN  // formula failed to converge

        $uSq = $cosSqAlpha * ($a*$a - $b*$b) / ($b*$b);
        $A = 1.0 + $uSq/16384.0*(4096.0+$uSq*(-768.0+$uSq*(320.0-175.0*$uSq)));
        $B = $uSq/1024.0 * (256.0+$uSq*(-128.0+$uSq*(74.0-47.0*$uSq)));
        $deltaSigma = $B*$sinSigma*($cos2SigmaM+$B/4.0*($cosSigma*(-1.0+2.0*$cos2SigmaM*$cos2SigmaM)-$B/6.0*$cos2SigmaM*(-3.0+4.0*$sinSigma*$sinSigma)*(-3.0+4.0*$cos2SigmaM*$cos2SigmaM)));
        $s = $b*$A*($sigma-$deltaSigma);
        
        return ( abs(round($s) / 1000.0) );
    }
    
    /*******************************************************************/
    /** \brief Set the parent object of this instance.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SetParentObj(
        $in_parent_obj  ///< A reference to the parent object.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_local_id_parent_obj = null;
        $this->_local_id_parent_obj = $in_parent_obj;
    }
    
    /*******************************************************************/
    /** \brief Return a reference to the parent object of this instance.

        \returns a reference to the parent instance of the object.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetParentObj()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_local_id_parent_obj;
    }
    
    /*******************************************************************/
    /** \brief Create a bunch of meeting objects from the given data.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    private function CreateMeetingObjects(
        $in_meetings_data   ///< An array of data for one meeting.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_meetings_objects = null;
        $this->_formats_used = null;
        $this->_service_bodies_used = null;
        
        foreach ($in_meetings_data as $one_meeting) {
            if ($one_meeting instanceof c_comdef_meeting) {
                $id = $one_meeting->GetID();
                $meeting = $one_meeting;
            } else {
                $id = $one_meeting['id_bigint'];
                $formats = $one_meeting['formats'];
                $lang = $one_meeting['lang_enum'];
                // We make a list of references to the objects of the formats used, and index by the shared ID.
                // This makes it convenient for the user.
                foreach ($formats as $format_obj) {
                    if ($format_obj instanceof c_comdef_format) {
                        $format_id = $format_obj->GetSharedID();
                        $this->_formats_used[$format_id] = $format_obj;
                    }
                }
                $key = $one_meeting['service_body_bigint'];
                
                $value = c_comdef_server::GetServiceBodyByIDObj($key);
                
                if ($value instanceof c_comdef_service_body) {
                    $this->_service_bodies_used[$key] = $value;
                }
                
                $meeting = new c_comdef_meeting($this, $one_meeting);
            }
            $this->_meetings_objects[$id] = $meeting;
        }
    }
    
    /*******************************************************************/
    /** \brief Get the number of meetings.

        \returns an integer, containing the number of meeting objects in the internal meetings object array.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetNumMeetings()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = 0;
        
        if (isset($this->_meetings_objects) && is_array($this->_meetings_objects) && count($this->_meetings_objects)) {
            $ret = count($this->_meetings_objects);
        }
            
        return $ret;
    }
    
    /*******************************************************************/
    /** \brief Accessor -Get the meetings array.

        \returns a reference to the internal meetings object array.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function &GetMeetingObjects()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if ($this->GetNumMeetings()) {
            return $this->_meetings_objects;
        }
        
        return null;
    }
    
    /*******************************************************************/
    /** \brief Accessor -Get the formats array.

        \returns a reference to the array that references the c_comdef_format objects used within this dataset.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function &GetFormatsUsed()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_formats_used;
    }
    
    /*******************************************************************/
    /** \brief Accessor -Get the service bodies array.

        \returns a reference to the array that references the c_comdef_service_body objects used within this dataset.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function &GetServiceBodiesUsed()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_service_bodies_used;
    }
    
    /*******************************************************************/
    /** Returns the name of the Meeting Table.

        \returns the name of the meetings table.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetMeetingTableName()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $parent_obj = $this->GetParentObj();
        
        $name = $parent_obj->GetMeetingTableName();
        
        return $name;
    }
    
    /*******************************************************************/
    /** \brief Removes a meeting by a given ID from the set.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function RemoveMeeting(
        $in_id  ///< Integer: the id_bigint for the meeting to be removed.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_meetings_objects[$in_id] = null;
        unset($this->_meetings_objects[$in_id]);
    }
    
    /*******************************************************************/
    /** \brief Return all the meetings that are within a certain radius of the given location.

        \returns a new c_comdef_meetings object, containing the subset of meetings
        that are within the radius. Null if nothing found.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetMeetingsByDistance(
        $in_longitude,          ///< Floating-point. The longitude value of the center point.
        $in_latitude,           ///< Floating-point. The latitude value of the center point.
        $in_radius,             ///< Floating-point. The radius from the center
        $in_radius_is_km = true,  ///< Boolean. Set this to false if the radius is in miles (default is true).
        $in_sort_by_distance = false    ///< Boolean. Set this to true to sort the results by distance.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = null;

        if (!$in_radius_is_km) {
            $in_radius *= 1.609344;
        }
        
        $iterating_target = $this->GetMeetingObjects();
        foreach ($iterating_target as &$meeting) {
            if ($meeting instanceof c_comdef_meeting) {
                // Get a reference to the meeting data.
                $meeting_data = $meeting->GetMeetingData();
                $lat = $meeting->GetMeetingDataValue('latitude');
                $long = $meeting->GetMeetingDataValue('longitude');

                $dist = 0;
                
                if (self::IsInDistance($in_latitude, $in_longitude, $lat, $long, $in_radius, $dist)) {
                    $ret[$meeting->GetID()] = $meeting;
                }
            }
        }

        if (is_array($ret) && count($ret)) {
            $reto = new c_comdef_meetings($this->GetParentObj(), $ret);
            
            if ($reto instanceof c_comdef_meetings) {
                $reto->SortMeetingObjects($this->_sort_key, $this->_sort_dir, $in_sort_by_distance);
            }
            
            return $reto;
        } else {
            return null;
        }
    }
    
    /*******************************************************************/
    /** \brief Return all the meetings that have a given key value

        Use this to filter for meetings within a single key/value.

        \returns a new c_comdef_meetings object, containing the subset of meetings
        that contain the search query. Null if nothing found.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetMeetingsByKeyValue(
        $in_key_string_array,   ///< This is data item keys (an array of string). These must match exactly.
        $in_value,              ///< This is a string with a literal value to find.
        $in_contains = false,   ///< If this is false, then the entire value must match (Defalt is false).
        $in_match_case = false
        // If this is true, then the case must match (Default is false).
        // NOTE: As of Version 1.5, the behavior has changed. This now refers to
        // using metaphone or not. If it is true, the the string must match exactly,
        // including case. However, if it is false, a case-insensitive metaphone search
        // is done in the native server language.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = null;
        $reto = array();
        
        if (is_array($in_key_string_array) && count($in_key_string_array)) {
            if (!$in_match_case) {
                $in_value = mb_strtolower($in_value, 'UTF-8');
            }
            $iterating_target = $this->GetMeetingObjects();
    
            foreach ($iterating_target as &$meeting) {
                // Get a reference to the meeting data.
                $meeting_data = $meeting->GetMeetingData();
                
                // We won't mess with data values that are objects or arrays.
                if (is_array($meeting_data) && count($meeting_data)) {
                    reset($in_key_string_array);
                    foreach ($in_key_string_array as $key_string) {
                        if (isset($meeting_data[$key_string])) {
                            if (is_array($meeting_data[$key_string]) && isset($meeting_data[$key_string]['value'])) {
                                $value = strval($meeting_data[$key_string]['value']);    // Make sure it's a string. We've had problems with non-string fields.
                            } else {
                                $value = strval($meeting_data[$key_string]); // Make sure it's a string. We've had problems with non-string fields.
                            }
                            
                            if (isset($value)) {
                                if (!$in_match_case) {
                                    $value = mb_strtolower($value, 'UTF-8');
                                }
                                
                                if ($in_contains) {
                                    $preg = preg_quote($in_value);
                                    $preg = '|.*'.$preg.'.*|';
                                    $match = (1 == preg_match($preg, $value));
                                } else {
                                    $match = (0 == strcmp(trim($in_value), trim($value)));
                                }
                            
                                if ($match) {
                                    $reto[] = $meeting;
                                } elseif (!$in_match_case) {
                                    /*
                                        This is all new, as of Version 1.5.
                                        If the "match_case" parameter is off, then we do a metaphone search in the local language of the server.
                                    */
                                    $in_string_comp = SplitIntoMetaphone($in_value, c_comdef_server::GetServer()->GetLocalLang());
                                    $comp = SplitIntoMetaphone($value, c_comdef_server::GetServer()->GetLocalLang());
                                    
                                    $found = false;
            
                                    foreach ($in_string_comp as $test) {
                                        if (array_search($test, $comp)) {
                                            $found = true;
                                            if ($in_contains) {
                                                break;
                                            }
                                        } else {
                                            $found = false;
                                            if (!$in_contains) {
                                                break;
                                            }
                                        }
                                    }
                                
                                    if ($found === true) {
                                        $reto[] = $meeting;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if (is_array($reto) && count($reto)) {
            $ret = new c_comdef_meetings($this->GetParentObj(), $reto);
            
            if ($ret instanceof c_comdef_meetings) {
                $ret->SortMeetingObjects($this->_sort_key, $this->_sort_dir);
            }
        }
        
        return $ret;
    }
    
    /*******************************************************************/
    /** \brief Return all the meetings that contain the given string.

        If the language is English or Spanish, this does a metaphone search of all
        all the strings in the "en" or "es" formats and weekdays. If it is any other
        language, then a simple string search is performed.

        \returns a new c_comdef_meetings object, containing the subset of meetings
        that contain the search query. Null if nothing found.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetMeetingsByString(
        $in_string,                 ///< This is the string to search for.
        $in_lang_enum = null,       ///< This is the code for the desired language. If not given, the server localization will be used.
        $in_all_words_bool = false, ///< If true, then all the words in the given search term need to be found (They could be distributed all around the various components of the meeting record).
        $in_literal = false         ///< If this is set to true, then metaphone-capable languages will not use metaphone, and will literally test the strings. Default is false.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = null;
        /// If no language is given, we use the server's native language.
        if (null == $in_lang_enum) {
            $in_lang_enum = c_comdef_server::GetServer()->GetLocalLang();
        }
        
        $in_all_words_bool = (isset($in_all_words_bool) && $in_all_words_bool) ? true : false;   // Just make sure that there is a value in here.
        $in_literal = (isset($in_literal) && $in_literal) ? true : false;   // Just make sure that there is a value in here.
            
        $localized_strings = c_comdef_server::GetLocalStrings();

        $local_weekdays = $localized_strings['comdef_server_admin_strings']['user_editor_account_type_1'];
        
        /// We force the search to happen in lowercase. This is a very basic search.
        $iterating_target = $this->GetMeetingObjects();
        
        if (is_array($iterating_target) && count($iterating_target)) {
            $count = 0;
            foreach ($iterating_target as &$meeting) {
                $count++;
                if ($meeting instanceof c_comdef_meeting) {
                    // Get a reference to the meeting data.
                    $meeting_data = $meeting->GetMeetingData();
                    
                    /*
                        What we do here, is build up a list of every text field in the meeting record.
                        We resolve the weekday into a localized name, and we grab the localized format
                        text as well.

                        We will then search through all this text for our string[s].
                    */
                    $text_fields = array();
                    foreach ($meeting_data as $key => $value) {
                        // We ignore the values in the main table.
                        switch ($key) {
                            case 'id_bigint':
                            case 'worldid_mixed':
                            case 'start_time':
                            case 'duration_time':
                            case 'time_zone':
                            case 'longitude':
                            case 'latitude':
                            case 'published':
                            case 'email_contact':
                                break;
                            
                            case 'service_body_bigint':
                                $text_fields[$key] = $meeting->GetServiceBodyName();
                                break;
                            
                            case 'lang_enum':
                                $lang_path = dirname(__FILE__)."/../../local_server/server_admin/lang/".$meeting_data['lang_enum']."/name.txt";
                                if (file_exists($lang_path)) {
                                    $text_fields[$key] = file_get_contents($lang_path);
                                }
                                break;
        
                            case 'weekday_tinyint':
                                if (isset($meeting_data['weekday_tinyint'])) {
                                    $text_fields[$key] = $local_weekdays[$meeting_data['weekday_tinyint']];
                                }
                                break;
        
                            case 'formats':
                                $formats_list = c_comdef_server::GetServer()->GetFormatsObj();
                                
                                foreach ($meeting_data[$key] as $key2 => $value) {
                                    $the_format_object = $formats_list->GetFormatBySharedIDCodeAndLanguage($key2, $in_lang_enum);
                                    
                                    if ($the_format_object) {
                                        $text_fields["format_$key2"] = $the_format_object->GetLocalName()." ".$the_format_object->GetLocalDescription();
                                    }
                                }
                                break;
                            
                            default:
                                if (isset($meeting_data[$key]['value']) && ( null != $meeting_data[$key]['value'] )) {
                                    $val = null;
                                    $val_key = null;
                                    if (!is_float($meeting_data[$key]['value']) && !is_int($meeting_data[$key]['value'])) {
                                        if (($meeting_data[$key]['visibility'] != _VISIBILITY_NONE_) || $meeting->UserCanObserve()) {
                                            $text_fields[$key] = $meeting_data[$key]['value'];
                                        }
                                    }
                                }
                                break;
                        }
                    }
                    
                    $found_string = false;

                    if (is_array($text_fields) && count($text_fields)) {
                        $in_string = mb_strtolower(trim($in_string), 'UTF-8');
                        $in_string = preg_replace('|\s+|', ' ', $in_string);
                        
                        if (!$in_literal) {
                            $string_metaphone = SplitIntoMetaphone($in_string, $in_lang_enum);
                            $string_metaphone = implode(' ', $string_metaphone);
                        }
                        
                        foreach ($text_fields as $test_text) {
                            // We will be checking the field text.
                            $test_text = mb_strtolower(trim($test_text), 'UTF-8');
                            $test_text = preg_replace('|\s+|', ' ', $test_text);
                            
                            if ($test_text) {
                                if (isset($string_metaphone)) {
                                    $test_metaphone = SplitIntoMetaphone($test_text, $in_lang_enum);
                                    $test_metaphone = implode(' ', $test_metaphone);
                                    if (mb_strpos($test_metaphone, $string_metaphone, 0, 'UTF-8') !== false) {
                                        if (!$ret) {
                                            $ret = array ( $meeting );
                                        } else {
                                            $ret[] = $meeting;
                                        }
                            
                                        break;
                                    }
                                } else {
                                    $string_test = preg_split('|\s+|', $in_string);
                                
                                    if ($in_all_words_bool) {
                                        $test_string = mb_strtolower(trim($test_string), 'UTF-8');
                                    
                                        if (isset($string_test) && is_array($string_test) && count($string_test)) {
                                            $found_string = true;
                                        
                                            foreach ($string_test as $str) {
                                                $str = mb_strtolower(trim($str), 'UTF-8');
                                                $found_string = preg_match('|'.preg_quote($str).'|', $test_text);
                                            
                                                if ($found_string != $in_all_words_bool) {
                                                    break;
                                                }
                                            }
                                        } else {
                                            $test_string = preg_replace('|^[\'\"]*?(.*?)[\'\"]*?$|', '', $in_string);
                                            $found_string = preg_match('|'.preg_quote($test_string).'|', $test_text);
                                        }
                                    }
                                
                                    if ($found_string) {
                                        if (!$ret) {
                                            $ret = array ( $meeting );
                                        } else {
                                            $ret[] = $meeting;
                                        }
                                
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $reto = null;
        
        if (is_array($ret) && count($ret)) {
            $reto = new c_comdef_meetings($this->GetParentObj(), $ret);
            
            if ($reto instanceof c_comdef_meetings) {
                $reto->SortMeetingObjects($this->_sort_key, $this->_sort_dir);
            }
        }

        return $reto;
    }
    
    /*******************************************************************/
    /** \brief Returns an array of all the field keys used by the meetings
        in this search set.

        \returns an array of strings, with the key being the same as the value.
        This reflects
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetMeetingKeys()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = null;
        
        $iterating_target = $this->GetMeetingObjects();
        foreach ($iterating_target as &$meeting) {
            // Get a reference to the meeting data.
            $meeting_data = $meeting->GetMeetingData();
            
            foreach ($meeting_data as $key => $value) {
                $ret[$key] = $key;
            }
        }
        
        return $ret;
    }
    
    /*******************************************************************/
    /** \brief Sorts the meetings.
        This will apply a sort, dependent upon the given fields.
        The given array contains the field names (SQL columns and keys)
        for the data to be sorted.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SortMeetingObjects(
        $in_sort_fields_array,
        // An array of strings. The array will deliniate the sort order, by field name.
        // Array element [0] will be the highest priority, and it will descend from there.
        $in_desc = false,           ///< If this is set to true, the sort will be highest to lowest. Default is false.
        $in_by_distance = false     ///< If true, then, after the regular sort, we sort by distance (May be ignored if the meetings don't have a
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_sort_key = null;
        $my_meeting_data =& $this->GetMeetingObjects();

        if (is_array($my_meeting_data) && count($my_meeting_data) && is_array($in_sort_fields_array) && count($in_sort_fields_array)) {
            $this->_sort_key = $in_sort_fields_array;
            $this->_sort_dir = $in_desc ? "desc" : "asc";
            usort($my_meeting_data, array ( 'c_comdef_meetings', 'SortKernel' ));
        }
        
        if ($this->_sort_search_by_distance || $in_by_distance) {
            $this->_sort_search_by_distance = true;
            usort($my_meeting_data, array ( 'c_comdef_meetings', 'SortDistanceKernel' ));
        }
    }
    
    /*******************************************************************?
    /** \brief Accessor -get the sort key.

        \returns an array of strings. The current sort key.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetSortKey()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_sort_key;
    }
    
    /*******************************************************************?
    /** \brief Accessor -get the sort direction.

        \returns a string. The current sort direction.
            It is either:
                - "asc" -Ascending
                - "desc" -Descending
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetSortDir()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_sort_dir;
    }
    
    /*******************************************************************/
    /** \brief This is the standard PHP sort callback used in the sort
        loops for distance. This will not be effective if the meetings
        don't both have _distance_in_km fields.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function SortDistanceKernel($object_a, $object_b)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = 0;
        
        if (($object_a instanceof c_comdef_meeting) && ($object_b instanceof c_comdef_meeting) && isset($object_a->_distance_in_km) && isset($object_b->_distance_in_km)) {
            $ret = ($object_a->_distance_in_km < $object_b->_distance_in_km) ? -1 : 1;
            
            if (0 == $ret) {
                $ret = $this::SortKernel($object_a, $object_b);
            }
        }
        
        return $ret;
    }
    
    /*******************************************************************/
    /** \brief This is the standard PHP sort callback used in the sort
        loops.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function SortKernel($object_a, $object_b)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = 0;
        
        if (($object_a instanceof c_comdef_meeting) && ($object_b instanceof c_comdef_meeting)) {
            $parent = $object_a->GetParentObj();    // This is how we get the sort key.
            if ($parent instanceof c_comdef_meetings) {
                $sort_key = $parent->GetSortKey();
                $sort_dir = $parent->GetSortDir();
                
                if (is_array($sort_key) && count($sort_key)) {
                    $meeting_data_a = $object_a->GetMeetingData();
                    $meeting_data_b = $object_b->GetMeetingData();
                    
                    if (is_array($meeting_data_a) && count($meeting_data_a) && is_array($meeting_data_b) && count($meeting_data_b)) {
                        foreach ($sort_key as $s_key) {
                            if ($s_key == 'start_time') {
                                $value_a_arr = explode(":", $meeting_data_a[$s_key]);
                                $value_b_arr = explode(":", $meeting_data_b[$s_key]);
                                
                                $value_a = (intval($value_a_arr[0]) * 100) + intval($value_a_arr[1]);
                                $value_b = (intval($value_b_arr[0]) * 100) + intval($value_b_arr[1]);
                                
                                // Kludge to make midnight higher.
                                if ($value_a == 0) {
                                    $value_a = 10000;
                                }
                                
                                if ($value_b == 0) {
                                    $value_b = 10000;
                                }
                                
                                if ($value_a == $value_b) {
                                    $ret = 0;
                                } elseif ($value_a < $value_b) {
                                    $ret = -1;
                                } elseif ($value_a > $value_b) {
                                    $ret = 1;
                                }
                            } elseif ($s_key == 'weekday_tinyint') {
                                $local_strings = c_comdef_server::GetLocalStrings();
                                
                                $value_a = intval($meeting_data_a[$s_key]) - ($local_strings['week_starts_on'] - 1);
                                $value_b = intval($meeting_data_b[$s_key]) - ($local_strings['week_starts_on'] - 1);

                                if (1 > $value_a) {
                                    $value_a += 7;
                                }
                                
                                if (1 > $value_b) {
                                    $value_b += 7;
                                }
                                    
                                if ($value_a == $value_b) {
                                    $ret = 0;
                                } elseif ($value_a < $value_b) {
                                    $ret = -1;
                                } elseif ($value_a > $value_b) {
                                    $ret = 1;
                                }
                            } elseif ((($s_key == 'location_municipality') || ($s_key == 'location_city_subsection')) && in_array('location_municipality', $sort_key) && in_array('location_city_subsection', $sort_key)) {
                                if (isset($meeting_data_a['location_city_subsection']) && !isset($meeting_data_b['location_city_subsection'])) {
                                    if (isset($meeting_data_b['location_municipality'])) {
                                        $value_a = $meeting_data_a['location_city_subsection']['value'];
                                        $value_b = $meeting_data_b['location_municipality']['value'];
                                        $cmp = strcmp($value_a, $value_b);
                                        if (0 > $cmp) {
                                            $ret = -1;
                                        } elseif (0 < $cmp) {
                                            $ret = 1;
                                        }
                                    } else {
                                        $ret = -1;
                                    }
                                } elseif (isset($meeting_data_b['location_city_subsection']) && !isset($meeting_data_a['location_city_subsection'])) {
                                    if (isset($meeting_data_a['location_municipality'])) {
                                        $value_a = $meeting_data_b['location_city_subsection']['value'];
                                        $value_b = $meeting_data_a['location_municipality']['value'];
                                        $cmp = strcmp($value_a, $value_b);
                                        if (0 > $cmp) {
                                            $ret = 1;
                                        } elseif (0 < $cmp) {
                                            $ret = -1;
                                        }
                                    } else {
                                        $ret = 1;
                                    }
                                } elseif (isset($meeting_data_a[$s_key]) && is_array($meeting_data_a[$s_key]) && isset($meeting_data_a[$s_key]['value']) && isset($meeting_data_b[$s_key]) && is_array($meeting_data_b[$s_key]) && isset($meeting_data_b[$s_key]['value'])) {
                                    $value_a = $meeting_data_a[$s_key]['value'];
                                    $value_b = $meeting_data_b[$s_key]['value'];
                                    $cmp = strcmp($value_a, $value_b);
                                    if (0 > $cmp) {
                                        $ret = -1;
                                    } elseif (0 < $cmp) {
                                        $ret = 1;
                                    }
                                } else {
                                    $ret = ( isset($meeting_data_a[$s_key]) && !isset($meeting_data_b[$s_key]) ) ? -1 : (( !isset($meeting_data_a[$s_key]) && isset($meeting_data_b[$s_key]) ) ? 1 : 0);
                                }
                            } elseif (isset($meeting_data_a[$s_key]) && isset($meeting_data_b[$s_key])) {
                                // This is if we have an optional field, with a "value" sub array element.
                                if (is_array($meeting_data_a[$s_key]) && isset($meeting_data_a[$s_key]['value'])) {
                                    $value_a = $meeting_data_a[$s_key]['value'];
                                } else // If not, we use the actual value of the parameter itself.
                                    {
                                    $value_a = $meeting_data_a[$s_key];
                                }
                            
                                // We do the same for the next element.
                                if (is_array($meeting_data_b[$s_key]) && isset($meeting_data_b[$s_key]['value'])) {
                                    $value_b = $meeting_data_b[$s_key]['value'];
                                } else {
                                    $value_b = $meeting_data_b[$s_key];
                                }
                            
                                // If they are strings, we do a binary-safe comparison.
                                if (is_string($value_a)) {
                                    if (($value_a && !$value_b) || (isset($value_a) && !isset($value_b))) {
                                        $ret = 1;
                                    } elseif (($value_b && !$value_a) || (isset($value_b) && !isset($value_a))) {
                                        $ret = -1;
                                    } else {
                                        $cmp = strcmp($value_a, $value_b);
                                        if (0 > $cmp) {
                                            $ret = -1;
                                        } elseif (0 < $cmp) {
                                            $ret = 1;
                                        }
                                    }
                                } else // Otherwise, we do a simple comparison.
                                    {
                                    if ($value_a < $value_b) {
                                        $ret = -1;
                                    } elseif ($value_a > $value_b) {
                                        $ret = 1;
                                    }
                                }
                            } else {
                                $ret = ( isset($meeting_data_a[$s_key]) && !isset($meeting_data_b[$s_key]) ) ? -1 : (( !isset($meeting_data_a[$s_key]) && isset($meeting_data_b[$s_key]) ) ? 1 : 0);
                            }
                        
                            if ($ret != 0) {
                                if ($sort_dir == "desc") {
                                    $ret = -$ret;
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        return $ret;
    }
}
