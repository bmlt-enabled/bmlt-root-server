<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://magshare.org/bmlt

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
	define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
	include ( 'server/config/auto-config.inc.php' );
	$page_one_complete = isset ( $dbType ) && $dbType;
	$page_one_complete = $page_one_complete && isset ( $dbServer ) && $dbServer;
	$page_one_complete = $page_one_complete && isset ( $dbName ) && $dbName;
	$page_one_complete = $page_one_complete && isset ( $dbUser ) && $dbUser;
	$page_one_complete = $page_one_complete && isset ( $dbPassword ) && $dbPassword;
	$page_one_complete = $page_one_complete && isset ( $dbPrefix ) && $dbPrefix;
	
	$page_two_complete = isset ( $theme ) && $theme;
	$page_two_complete = $page_two_complete && isset ( $comdef_global_language ) && $comdef_global_language;
	$page_two_complete = $page_two_complete && isset ( $any_service_body_admin_can_edit_formats );
	$page_two_complete = $page_two_complete && isset ( $any_service_body_admin_can_create_service_bodies );
	$page_two_complete = $page_two_complete && isset ( $min_pw_len ) && $min_pw_len;
	
	$page_three_complete = isset ( $gkey ) && $gkey;
	$page_three_complete = $page_three_complete && isset ( $search_spec_map_center ) && is_array ( $search_spec_map_center );
	
	$page_four_complete = isset ( $results_per_page ) && $results_per_page;
	$page_four_complete = $page_four_complete && isset ( $number_of_meetings_for_auto ) && $number_of_meetings_for_auto;
	$page_four_complete = $page_four_complete && isset ( $default_basic_search ) && $default_basic_search;
	$page_four_complete = $page_four_complete && isset ( $default_sort_key ) && $default_sort_key;
	$page_four_complete = $page_four_complete && isset ( $default_sort_dir ) && $default_sort_dir;
	$page_four_complete = $page_four_complete && isset ( $static_map_size ) && is_array ( $static_map_size );
	$page_four_complete = $page_four_complete && isset ( $time_format ) && $time_format;
	$page_four_complete = $page_four_complete && isset ( $change_date_format ) && $change_date_format;
	$page_four_complete = $page_four_complete && isset ( $change_depth_for_meetings ) && $change_depth_for_meetings;
	$page_four_complete = $page_four_complete && isset ( $default_sorts ) && is_array ( $default_sorts );
	$page_four_complete = $page_four_complete && isset ( $page_display_size ) && $page_display_size;
	$page_four_complete = $page_four_complete && isset ( $sort_depth ) && $sort_depth;
 	$page_four_complete = $page_four_complete && isset ( $admin_session_name ) && $admin_session_name;
 	$page_four_complete = $page_four_complete && isset ( $client_session_name ) && $client_session_name;

	$inited = true;
	
	if ( $page_four_complete )
		{
		include_once ( 'server/classes/c_comdef_dbsingleton.class.php' );
		
		c_comdef_dbsingleton::init ( $dbType, $dbServer, $dbName, $dbUser, $dbPassword );
	
		try
			{
			c_comdef_dbsingleton::connect();
			}
		catch ( Exception $e )
			{
			$page_four_complete = false;
			$inited = false;
			}
		}

	if (	$inited
		&&(	(isset ( $_POST['wizard_page'] ) && $_POST['wizard_page'])
		||	(isset ( $_GET['wizard_page'] ) && $_GET['wizard_page'])
		||	!$page_one_complete
		||	!$page_two_complete
		||	!$page_three_complete
		||	!$page_four_complete) )
		{
		$inited = false;
		}
	elseif ( $inited &&	$page_one_complete )
		{
		include_once ( 'server/classes/c_comdef_dbsingleton.class.php' );
		
		c_comdef_dbsingleton::init ( $dbType, $dbServer, $dbName, $dbUser, $dbPassword );
	
		try
			{
			c_comdef_dbsingleton::connect();
			}
		catch ( Exception $e )
			{
			$inited = false;
			}
		}
	
	if ( !$inited )
		{
		include ( 'server/config/install_wizard.php' );
		}
	else
		{
		if ( (fileperms ( 'server/config/auto-config.inc.php' ) & 0x0002) == 0x0002 )
			{
			die ('The server/config/auto-config.inc.php file is still writeable! You have finished setting it up, and security requires that it be set to read-only!');
			}
		$offset = '';
		$root_uri_spec = 'http://'.htmlspecialchars ( preg_replace ( '|\/\/$|', '/', $_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/' ) );
	
		include ( 'local_server/index.php' );
		}
?>