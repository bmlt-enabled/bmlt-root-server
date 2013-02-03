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
        
        if ( isset ( $this->my_http_vars['do_meeting_search'] ) )
            {
            $returned_text = $this->TranslateToJSON ( $this->GetSearchResults ( $http_vars ) );
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