<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://bmlt.magshare.org

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this code.  If not, see <http://www.gnu.org/licenses/>.
*/
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.
require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2Json.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2XML.php');
require_once ( dirname ( __FILE__ ).'/../../client_interface/csv/search_results_csv.php' );
require_once ( dirname ( __FILE__ ).'/PhpJsonXmlArrayStringInterchanger.inc.php' );

/***********************************************************************************************************//**
    \class c_comdef_admin_main_console
    \brief Controls display of the main BMLT administration console.
***************************************************************************************************************/
class c_comdef_admin_ajax_handler
{
    var $my_localized_strings;          ///< This will contain the localized strings and whatnot for display.
    var $my_server;                     ///< This hold the server object.
    var $my_user;                       ///< This holds the instance of the logged-in user.
    var $my_http_vars;                  ///< Contains the HTTP vars sent in.
    
    /*******************************************************************************************************//**
    \brief
    ***********************************************************************************************************/
    function __construct (  $in_http_vars   ///< The HTTP transaction parameters
                        )
    {
        $this->my_http_vars = $in_http_vars;
        $this->my_localized_strings = c_comdef_server::GetLocalStrings();
        $this->my_server = c_comdef_server::MakeServer();
        $this->my_user = $this->my_server->GetCurrentUserObj();
        
        // We check this every chance that we get.
        if ( !$this->my_user || ($this->my_user->GetUserLevel() == _USER_LEVEL_DISABLED) )
            {
            die ( '<h2>NOT AUTHORIZED</h2>' );
            }
    }
    
    /*******************************************************************************************************//**
    \brief
    \returns
    ***********************************************************************************************************/
    function parse_ajax_call()
    {
        $returned_text = '';
        
        $account_changed = false;
        
        if ( isset ( $this->my_http_vars['set_meeting_change'] ) && $this->my_http_vars['set_meeting_change'] )
            {
            $this->HandleMeetingUpdate ( $this->my_http_vars['set_meeting_change'] );
            }
        else if ( isset ( $this->my_http_vars['do_meeting_search'] ) )
            {
            $returned_text = $this->TranslateToJSON ( $this->GetSearchResults ( $this->my_http_vars ) );
            }
        else
            {
            if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_password_value'] ) )
                {
                $this->my_user->SetNewPassword ( $this->my_http_vars['account_password_value'] );
                $success = $this->my_user->UpdateToDB ( false, null, true );
                $account_changed = true;
                if ( $ret )
                    {
                    $ret .= ',';
                    }
                $ret .= '{\'PASSWORD_CHANGED\':'.($success ? 'true' : 'false').'}';
                }
        
            if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_email_value'] ) )
                {
                $this->my_user->SetEmailAddress ( $this->my_http_vars['account_email_value'] );
                $success = $this->my_user->UpdateToDB ( );
                $account_changed = true;
                if ( $ret )
                    {
                    $ret .= ',';
                    }
                $ret .= '{\'EMAIL_CHANGED\':'.($success ? 'true' : 'false').'}';
                }
        
            if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_description_value'] ) )
                {
                $this->my_user->SetLocalDescription ( $this->my_http_vars['account_description_value'] );
                $account_changed = true;
                $success = $this->my_user->UpdateToDB ( );
                if ( $ret )
                    {
                    $ret .= ',';
                    }
                $ret .= '{\'DESCRIPTION_CHANGED\':'.($success ? 'true' : 'false').'}';
                }
        
            if ( $account_changed )
                {
                $returned_text .= '{\'ACCOUNT_CHANGED\':'.$ret.'}';
                }
            }
        
        return  $returned_text;
    }

    /*******************************************************************/
    /**
        \brief	This handles updating an existing meeting, or adding a new one.
    */	
    function HandleMeetingUpdate (  $in_meeting_data    ///< A JSON object, containing the new meeting data.
                                )
    {
        $the_new_meeting = json_decode ( $in_meeting_data, true );
        
        if ( is_array ( $the_new_meeting ) && count ( $the_new_meeting ) )
            {
            $this->SetMeetingDataValues ( $the_new_meeting );
            }
    }
    
    /*******************************************************************/
    /**
        \brief
    */	
    function SetMeetingDataValues (  $in_meeting_data    ///< A JSON object, containing the new meeting data.
                                )
    {
		try
            {
            if ( $in_meeting_data['id_bigint'] )
                {
                $meeting =& $this->my_server->GetOneMeeting($in_meeting_data['id_bigint']);
                }
            else
                {
                $data = array ( 'service_body_bigint' => intval ( $the_new_meeting['service_body_bigint'] ),
                                'weekday_tinyint' => intval ( $the_new_meeting['weekday_tinyint'] ),
                                'start_time' => $the_new_meeting['start_time'],
                                );
                $meeting = new c_comdef_meeting ( $this->my_server, $data );
                }
            
            if ( $meeting instanceof c_comdef_meeting )
                {
                // Security precaution: We check the session to make sure that the user is authorized for this meeting.
                if ( $meeting->UserCanEdit() )
                    {
                    $result_data = array ( 'meeting_id' => $in_meeting_data['id_bigint'] );
                    $data =& $meeting->GetMeetingData();

                    // We prepare the "template" array. These are the data values for meeting 0 in the two tables.
                    // We will use them to provide default visibility values. Only the server admin can override these.
                    // This is where we get a list of the available "optional" fields to put in a popup for adding a new one.
                    $template_data = c_comdef_meeting::GetDataTableTemplate();
                    $template_longdata = c_comdef_meeting::GetLongDataTableTemplate();
                
                    // We merge the two tables (data and longdata).
                    if ( is_array ( $template_data ) && count ( $template_data ) && is_array ( $template_longdata ) && count ( $template_longdata ) )
                        {
                        $template_data = array_merge ( $template_data, $template_longdata );
                        }
                
                    foreach ( $the_new_meeting as $key => $value )
                        {
                        // Skip the visibility flags.
                        if ( !preg_match ( '|_visibility$|', $key ) )
                            {
                            if ( isset ( $the_new_meeting[$key."_visibility"] ) && c_comdef_server::IsUserServerAdmin() )	// Only server admins can override the visibility.
                                {
                                $visibility = intval ( $the_new_meeting[$key."_visibility"] );
                                }
                            elseif ( isset ( $data[$key] ) && is_array ( $data[$key] ) )	// existing value
                                {
                                $visibility = intval ( $data[$key]['visibility'] );
                                }
                            else	// New field gets the template value.
                                {
                                $visibility = intval ( $template_data[$key]['visibility'] );
                                }
                        
//                             if ( $key == 'formats' )
//                                 {
//                                 $vals = array();
//                                 $value = explode ( ",", $value );
//                                 $lang = $this->my_server->GetLocalLang();
//                                 foreach ( $value as $id )
//                                     {
//                                     $vals[$id] = c_comdef_server::GetServer()->GetFormatsObj()->GetFormatBySharedIDCodeAndLanguage ( $id, $lang );
//                                     }
//                                 uksort ( $vals, array ( 'c_comdef_meeting','format_sorter_simple' ) );
//                                 $value = $vals;
//                                 }
                        
                            switch ( $key )
                                {
case 'formats':
break;

                                case	'distance_in_km':		// These are ignored.
                                case	'distance_in_miles':
                                break;
                            
                                // These are the "fixed" or "core" data values.
                                case	'id_bigint':
                                case	'worldid_mixed':
                                case	'service_body_bigint':
                                case	'start_time':
                                case	'lang_enum':
                                case	'duration_time':
                                case	'formats':
                                case	'longitude':
                                case	'latitude':
                                case	'latitude':
                                    $data[$key] = $value;
                                break;
                            
                                case	'email_contact':
                                    $value = trim ( $value );
                                    if ( $value )
                                        {
                                        if ( c_comdef_vet_email_address ( $value ) )
                                            {
                                            $data[$key] = $value;
                                            }
                                        else
                                            {
                                            $info = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$in_meeting_data['id_bigint'];
                                            $err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_format_bad'] );
                                            header ( 'Content-type: application/json' );
                                            die ( "{'error':true,'type':'email_format_bad','report':'$err_string','id':'".$in_meeting_data['id_bigint']."',info:'$info'}" );
                                            }
                                        }
                                    else
                                        {
                                        $data[$key] = $value;
                                        }
                                break;
                            
                                // We only accept a 1 or a 0.
                                case	'published':
                                    // Meeting list editors can't publish meetings.
                                    if ( c_comdef_server::GetCurrentUserObj(true)->GetUserLevel() != _USER_LEVEL_EDITOR )
                                        {
                                        $data[$key] = (intval ( $value ) != 0) ? 1 : 0;
                                        }
                                break;

                                // This one is special. The editor sends in one less than it should be.
                                case	'weekday_tinyint':
                                    $data[$key] = intval ( $value );
                                break;
                            
                                // These are the various "optional" fields.
                                default:
                                    if ( isset ( $data[$key] ) )
                                        {
                                        $data[$key]['meetingid_bigint'] = $in_meeting_data['id_bigint'];
                                        $data[$key]['value'] = $value;
                                        $data[$key]['visibility'] = $visibility;
                                        }
                                    else
                                        {
                                        if ( !preg_match ( "/_deleted_input$/", $key ) )
                                            {
                                            if ( isset ( $the_new_meeting["new_visibility"] ) && c_comdef_server::IsUserServerAdmin() )	// Only server admins can override the visibility.
                                                {
                                                $visibility = intval ( $the_new_meeting["new_visibility"] );
                                                }
                                            $result_data['new_data']['key'] = $key;
                                            $result_data['new_data']['field_prompt'] = $template_data[$key]['field_prompt'];
                                            $result_data['new_data']['value'] = $value;
                                            $meeting->AddDataField ( $key, $template_data[$key]['field_prompt'], $value, null, $visibility );
                                            }
                                        }
                                break;
                                }
                            }
                        }
                
                    foreach ( $the_new_meeting as $key => $value )
                        {
                        if ( preg_match ( "/_deleted_input$/", $key ) && ($value == "1") )
                            {
                            $key = preg_replace ( "/_deleted_input$/", "", $key );
                            $result_data['deleted_data'][$key]['key'] = $key;
                            $result_data['deleted_data'][$key]['prompt'] = $template_data[$key]['field_prompt'];
                            $meeting->DeleteDataField ( $key );
                            }
                        }
                
                    if ( $meeting->UpdateToDB() )
                        {
                        $result_data['meeting_published'] = $meeting->IsPublished();
                        $result_data['meeting_id'] = $meeting->GetID();
                        $result_data['town_html'] = BuildTown ( $meeting );
                        $result_data['name_html'] = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting->GetMeetingDataValue('meeting_name') ) ) );
                        $result_data['weekday_html'] = c_comdef_htmlspecialchars ( trim ( stripslashes ( $localized_strings['weekdays'][$meeting->GetMeetingDataValue('weekday_tinyint') - 1] ) ) );
                        $result_data['time_html'] = BuildTime ( $meeting->GetMeetingDataValue('start_time') );
                        $result_data['location_html'] = BuildLocation ( $meeting );
                        $result_data['format_html'] = BuildFormats ( $meeting );
                        header ( 'Content-type: application/json' );
                        echo array2json ( json_prepare ( $result_data ) );
                        }
                    else
                        {
                        $in_meeting_data['id_bigint'] = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$in_meeting_data['id_bigint'];
                        $err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
                        header ( 'Content-type: application/json' );
                        echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                        }
                    }
                else
                    {
                    $in_meeting_data['id_bigint'] = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$in_meeting_data['id_bigint'];
                    $err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
                    header ( 'Content-type: application/json' );
                    echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                    }
                }
            else
                {
                $in_meeting_data['id_bigint'] = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$in_meeting_data['id_bigint'];
                $err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['object_not_found'] );
                header ( 'Content-type: application/json' );
                echo "{'error':true,'type':'object_not_found','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                }
            }
        catch ( Exception $e )
            {
            $in_meeting_data['id_bigint'] = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$in_meeting_data['id_bigint'];
            $err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['object_not_changed'] );
            header ( 'Content-type: application/json' );
            echo "{'error':true,'type':'object_not_changed','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
            }
    }
    
    /*******************************************************************/
    /**
        \brief	This returns the search results, in whatever form was requested.
    
        \returns CSV data, with the first row a key header.
    */	
    function GetSearchResults ( 
                                $in_http_vars,	///< The HTTP GET and POST parameters.
                                &$formats_ar    ///< This will return the formats used in this search.
                                )
    {
        if ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset ( $in_http_vars['advanced_radius'] ) && isset ( $in_http_vars['advanced_mapmode'] ) && $in_http_vars['advanced_mapmode'] && ( floatval ( $in_http_vars['advanced_radius'] != 0.0 ) ) && isset ( $in_http_vars['lat_val'] ) &&	 isset ( $in_http_vars['long_val'] ) && ( (floatval ( $in_http_vars['lat_val'] ) != 0.0) || (floatval ( $in_http_vars['long_val'] ) != 0.0) ) )
            {
            $in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
            }
        elseif ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') )
            {
            $in_http_vars['lat_val'] = null;
            $in_http_vars['long_val'] = null;
            }
        elseif ( !isset ( $in_http_vars['geo_loc'] ) || $in_http_vars['geo_loc'] != 'yes' )
            {
            if ( !isset( $in_http_vars['geo_width'] ) )
                {
                $in_http_vars['geo_width'] = 0;
                }
            }

        $geocode_results = null;
        $ignore_me = null;
        $meeting_objects = array();
        $result = DisplaySearchResultsCSV ( $in_http_vars, $ignore_me, $geocode_results, $meeting_objects );

        if ( is_array ( $meeting_objects ) && count ( $meeting_objects ) && is_array ( $formats_ar ) )
            {
            foreach ( $meeting_objects as $one_meeting )
                {
                $formats = $one_meeting->GetMeetingDataValue('formats');

                foreach ( $formats as $format )
                    {
                    if ( $format && ($format instanceof c_comdef_format) )
                        {
                        $format_shared_id = $format->GetSharedID();
                        $formats_ar[$format_shared_id] = $format;
                        }
                    }
                }
            }
    
        if ( isset ( $in_http_vars['data_field_key'] ) && $in_http_vars['data_field_key'] )
            {
            // At this point, we have everything in a CSV. We separate out just the field we want.
            $temp_keyed_array = array();
            $result = explode ( "\n", $result );
            $keys = array_shift ( $result );
            $keys = explode ( "\",\"", trim ( $keys, '"' ) );
            $the_keys = explode ( ',', $in_http_vars['data_field_key'] );
        
            $result2 = array();
            foreach ( $result as $row )
                {
                if ( $row )
                    {
                    $index = 0;
                    $row = explode ( '","', trim ( $row, '",' ) );
                    $row_columns = array();
                    foreach ( $row as $column )
                        {
                        if ( isset ( $column ) )
                            {
                            if ( in_array ( $keys[$index++], $the_keys ) )
                                {
                                array_push ( $row_columns, $column );
                                }
                            }
                        }
                    $result2[$row[0]] = '"'.implode ( '","', $row_columns ).'"';
                    }
                }

            $the_keys = array_intersect ( $keys, $the_keys );
            $result = '"'.implode ( '","', $the_keys )."\"\n".implode ( "\n", $result2 );
            }
    
        return $result;
    }

    /*******************************************************************/
    /**
        \brief Translates CSV to JSON.
    
        \returns a JSON string, with all the data in the CSV.
    */	
    function TranslateToJSON ( $in_csv_data ///< An array of CSV data, with the first element being the field names.
                            )
    {
        $temp_keyed_array = array();
        $in_csv_data = explode ( "\n", $in_csv_data );
        $keys = array_shift ( $in_csv_data );
        $keys = explode ( "\",\"", trim ( $keys, '"' ) );
    
        foreach ( $in_csv_data as $row )
            {
            if ( $row )
                {
                $line = null;
                $index = 0;
                $row = explode ( '","', trim ( $row, '",' ) );
                foreach ( $row as $column )
                    {
                    if ( isset ( $column ) )
                        {
                        $line[$keys[$index++]] = $column;
                        }
                    }
                array_push ( $temp_keyed_array, $line );
                }
            }
    
        $out_json_data = array2json ( $temp_keyed_array );

        return $out_json_data;
    }
};

$handler = new c_comdef_admin_ajax_handler($http_vars);

$ret = 'ERROR';

if ( $handler instanceof c_comdef_admin_ajax_handler )
    {
    $ret = $handler->parse_ajax_call();
    }

echo $ret;
?>