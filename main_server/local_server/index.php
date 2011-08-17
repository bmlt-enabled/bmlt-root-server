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
$http_vars = array_merge_recursive ( $_GET, $_POST );
define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
//define ( '__DEBUG_MODE__', 1 ); // Uncomment to make the CSS and JavaScript easier to trace (and less efficient).
if ( (isset ( $http_vars['disp_format'] ) && ($http_vars['disp_format'] == 'csv'))
	|| (($http_vars['bmlt_search_type'] == 'advanced') && isset ( $http_vars['result_type_advanced'] ) && ($http_vars['result_type_advanced'] == 'csv'))
	|| (($http_vars['bmlt_search_type'] == 'advanced') && isset ( $http_vars['result_type_advanced'] ) && ($http_vars['result_type_advanced'] == 'csv_naws')) )
	{
	$search_action = 'search_csv';
	}
else
	{
	$search_action = null;
	}

$root_offset = '/';
if ( !isset ( $offset ) )
	{
	$offset = '../';
	$root_offset .= $offset;
	}
	
$config_file_path = $offset.'server/config/auto-config.inc.php';

if ( file_exists ( $config_file_path ) )
	{
	include ( $config_file_path );
	}

if ( !isset ( $theme ) || ! $theme )
	{
	$theme = 'default';
	}

$shortcut_icon = "themes/$theme/html/images/shortcut.png";
if ( zlib_get_coding_type() === false )
	{
		ob_start("ob_gzhandler");
	}
	else
	{
		ob_start();
	}

ob_start();
require_once ( dirname ( __FILE__ ).'/../server/shared/classes/comdef_utilityclasses.inc.php');

require_once ( dirname ( __FILE__ ).'/../server/c_comdef_server.class.php');
DB_Connect_and_Upgrade ( );

$server = c_comdef_server::MakeServer();

if ( $server instanceof c_comdef_server )
	{
	$localized_strings = c_comdef_server::GetLocalStrings();
	if ( ('search_csv' != $search_action) )
		{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<link rel="SHORTCUT ICON" href="<?php echo c_comdef_htmlspecialchars ( $shortcut_icon ) ?>" />
		<link rel="ICON" href="<?php echo c_comdef_htmlspecialchars ( $shortcut_icon ) ?>" />
		<script type="text/javascript" src="<?php echo $offset ?>server/shared/js_stripper.php?filename=ajax_threads.js"></script>
		<?php
		if ( !special_small_site ( $http_vars ) && !(isset( $http_vars['supports_ajax'] ) && ($http_vars['supports_ajax'] == 'yes')) )
			{
		?>
		<script type="text/javascript" src="<?php echo $offset ?>server/shared/js_stripper.php?filename=check_ajax.js"></script>
		<?php
			}
		?>
		<?php
			include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
			define ('___EDITOR___', false );
			$http_vars['script_name'] = $_SERVER['SCRIPT_NAME'];
			$http_vars['bmlt_root'] = dirname( $_SERVER['SCRIPT_NAME'] ).$root_offset;
			
			if ( special_small_site ( $http_vars ) || isset( $http_vars['supports_ajax'] ) && ($http_vars['supports_ajax'] == 'yes') )
				{
				$http_vars['supports_ajax'] = 'yes';
		?><script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $gkey ?>" type="text/javascript"></script>
		<script type="text/javascript" src="http://code.google.com/apis/gears/gears_init.js"></script>
		<script src="<?php echo $offset ?>server/shared/js_stripper.php?filename=einsert.js" type="text/javascript"></script>
		<?php
				}
		if ( special_small_site ( $http_vars ) )
			{
		?>
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
		<link rel="stylesheet" href="<?php echo $offset ?>local_server/style_stripper.php?filename=styles.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/small/style_stripper.php?filename=search_specification.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/small/style_stripper.php?filename=search_results.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/small/style_stripper.php?filename=search_results_single_meeting.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/small/style_stripper.php?filename=search_results_list.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/small/style_stripper.php?filename=search_results_map.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/small/style_stripper.php?filename=styles.css" />
		<?php
			}
		else
			{
		?>
		<link rel="stylesheet" href="<?php echo $offset ?>local_server/style_stripper.php?filename=styles.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_specification.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results_single_meeting.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results_list.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results_map.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results_print.css" media="print" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results_list_print.css" media="print" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results_single_meeting_print.css" media="print" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=search_results_map_print.css" media="print" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/admin/style_stripper.php?filename=admin_meeting.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/admin/style_stripper.php?filename=c_comdef_login.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/admin/style_stripper.php?filename=edit_formats.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/admin/style_stripper.php?filename=service_body_admin.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/admin/style_stripper.php?filename=user_admin.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/admin/style_stripper.php?filename=reports.css" />
		<link rel="stylesheet" href="<?php echo $offset ?>themes/<?php echo ( c_comdef_htmlspecialchars ( $theme ) ) ?>/html/style_stripper.php?filename=styles.css" />
		<?php
			}
		?>
		<title><?php
		if ( !$bmlt_title )
			{
			if ( !$comdef_global_language )
				{
				$comdef_global_language = 'en';
				}
			
			include ( dirname ( __FILE__ )."/server/config/lang/$comdef_global_language/search_results_strings.inc.php" );
			$bmlt_title = $localized_strings['comdef_search_results_strings']['Root_Page_Title'];
			}

		echo c_comdef_htmlspecialchars ( $bmlt_title );
		?></title>
	</head>
	<body class="admin_body"><?php
		}
		if ( isset ( $http_vars['disp_format'] ) && ($http_vars['disp_format'] == 'force_list') )
			{
			$http_vars['disp_format'] = 'list';
			}
		
		if ( $server instanceof c_comdef_server )
			{
			if ( !isset ( $_SESSION ) )
				{
				session_start();
				}
			
			if ( !special_small_site ( $http_vars ) )
				{
				$user_obj = $server->GetCurrentUserObj();
				}
	
			if ( isset ( $http_vars['logout'] ) || (($user_obj instanceof c_comdef_user) && (($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) || ($user_obj->GetUserLevel() > _USER_LEVEL_OBSERVER) || ($user_obj->GetUserLevel() <= 0))) )
				{
				c_comdef_LogoutUser();
				}

			if ( isset ( $http_vars['admin_action'] ) && ($http_vars['admin_action'] == 'login') )
				{
				require_once ( dirname ( __FILE__ ).'/../admin/c_comdef_login.php' );
				
				$user_obj = $server->GetCurrentUserObj();
				if ( !($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) || ($user_obj->GetUserLevel() > _USER_LEVEL_OBSERVER) || ($user_obj->GetUserLevel() <= 0) )
					{
					c_comdef_LogoutUser();
					die ("NOT AUTHORIZED");
					}
				
				// We don't want this propagating around. It causes agita.
				unset ( $http_vars['admin_action'] );
				unset ( $_GET ['admin_action'] );
				unset ( $_POST ['admin_action'] );
				unset ( $http_vars['c_comdef_admin_login'] );
				unset ( $_GET['c_comdef_admin_login'] );
				unset ( $_POST['c_comdef_admin_login'] );
				unset ( $http_vars['c_comdef_admin_password'] );
				unset ( $_GET['c_comdef_admin_password'] );
				unset ( $_POST['c_comdef_admin_password'] );
				$user_obj = null;
				}
			
			if ( !($user_obj instanceof c_comdef_user) && isset ( $http_vars['login'] ) && !special_small_site ( $http_vars ) )
				{
				require_once ( dirname ( __FILE__ ).'/../admin/c_comdef_login.php' );
				
				if ( !special_small_site ( $http_vars ) )
					{
					$server_info = GetServerInfo($offset);
					echo '<div class="bmlt_login_banner">';
					
					if ( isset ( $server_info['banner_text'] ) && $server_info['banner_text'] )
						{
						echo c_comdef_htmlspecialchars ( $server_info['banner_text'] ).' ';
						}
					echo '</div>';
					}
				}
			else
				{
				if ( !special_small_site ( $http_vars ) )
					{
					if ( isset ( $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'] ) )
						{
						$prefs = $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'];
						}
					else
						{
						$prefs = array ( 'format_sort' => 'format_key' );
						setcookie ( BMLT_COOKIE.'_meeting_edit_format_key', $prefs['format_sort'], time() + (60 * 60 * 24 * 366), '/' );
						}
					}
				
				if ( ('search_csv' != $search_action) )
					{
					echo '<div class="bmlt_admin_container" id="bmlt_admin_container">';
					}

				if ( isset ( $http_vars['edit_cp'] ) && !special_small_site ( $http_vars ) )
					{
					$search_action = 'edit_cp';
					}
				elseif ( isset ( $http_vars['single_meeting_id'] ) && $http_vars['single_meeting_id'] )
					{
					if ( ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) )
						{
						$search_action = 'search_list';
						$http_vars['meeting_ids'] = array ( intval ( $http_vars['single_meeting_id'] ) );
						}
					else
						{
						$search_action = 'single_meeting';
						}
					}
				elseif ( isset ( $http_vars['do_search'] ) )
					{
					if ( !isset ( $http_vars['geo_loc'] ) || $http_vars['geo_loc'] != 'yes' )
						{
						if ( !isset( $http_vars['geo_width'] ) )
							{
							$http_vars['geo_width'] = 0;
							}
						if ( !isset( $http_vars['geo_width_km'] ) )
							{
							$http_vars['geo_width_km'] = 0;
							}
						}
					
					if ( ('search_csv' != $search_action) )
						{
						if ( !(isset ( $http_vars['disp_format'] ) && $http_vars['disp_format'] == 'list') && (isset ( $http_vars['StringSearchIsAnAddress'] ) && $http_vars['StringSearchIsAnAddress']) )
							{
							$http_vars['disp_format'] = 'map';
							}
						
						if ( (isset ( $http_vars['disp_format'] ) && $http_vars['disp_format'] == 'list') || (($http_vars['geo_width'] == 0) && ($http_vars['geo_width_km'] == 0) && !(isset ( $http_vars['StringSearchIsAnAddress'] ) && $http_vars['StringSearchIsAnAddress'])) || !(isset( $http_vars['supports_ajax'] ) && ($http_vars['supports_ajax'] == 'yes')) )
							{
							$search_action = 'search_list';
							}
						else
							{
							$search_action = 'search_map';
							}
						}
					}
				else
					{
					$search_action = 'new_search';
					}
	
				if ( ('search_csv' != $search_action) && ($search_action != 'search_map') && isset ( $http_vars['supports_ajax'] ) && ($http_vars['supports_ajax'] == 'yes') && !special_small_site ( $http_vars ) )
					{
					echo c_comdef_admin_bar ( $http_vars );
					}
				
				switch ( $search_action )
					{
					case	'edit_cp':
					 	if ( !special_small_site ( $http_vars ) )
					 		{
							require_once ( dirname ( __FILE__ ).'/../admin/control_panel.inc.php' );
							echo GetControlPanelHTML ( $http_vars, $root_offset );
							}
					break;
					
					case	'single_meeting':
						require_once ( dirname ( __FILE__ ).'/../client/html/single_display.php' );
						echo DisplaySingleMeeting ( $http_vars );
					break;
					
					case	'search_list':
						require_once ( dirname ( __FILE__ ).'/../client/html/search_results_list.php' );
						echo DisplaySearchResultsList ( $http_vars, $root_offset );
					break;
					
					case	'search_csv':
					 	if ( !special_small_site ( $http_vars ) )
					 		{
							require_once ( dirname ( __FILE__ ).'/../client/html/search_results_csv.php' );
							if ( isset ( $http_vars ['result_type_advanced'] ) && ($http_vars ['result_type_advanced'] == 'csv_naws') )
								{
								echo ReturnNAWSFormatCSV ( $http_vars, $server );
								}
							else
								{
								echo DisplaySearchResultsCSV ( $http_vars );
								}
							}
					break;
					
					case	'search_map':
						require_once ( dirname ( __FILE__ ).'/../client/html/search_results_map.php' );
						echo DisplaySearchResultsMap ( $http_vars, $root_offset );
					break;
					
					default:
						if ( isset ( $root_uri_spec ) && !special_small_site ( $http_vars ) )
							{
							echo '<div class="root_decl">'.$localized_strings['comdef_search_results_strings']['Root_Decl'].$root_uri_spec.'</div>';
							}

						if ( special_small_site ( $http_vars ) )
							{
							require_once ( dirname ( __FILE__ ).'/../client/small/search_specification.php' );
							}
						else
							{
							require_once ( dirname ( __FILE__ ).'/../client/html/search_specification.php' );
							}
						
						echo DisplayMeetingSearchForm ( $http_vars );
						
						if ( !special_small_site ( $http_vars ) )
							{
							$server_info = GetServerInfo($offset);
							echo '<div class="bmlt_corner_version">';
							echo c_comdef_htmlspecialchars ( $server_info['serverVersion']['readableString'] );
							echo '</div>';
							}
					break;
					}
			
				if ( ('search_csv' != $search_action) )
					{
					echo '</div>';
					}
				}
			}
		
	if ( ('search_csv' != $search_action) )
		{
		?></body>
</html>
	<?php
		$ret = ob_get_contents();
		ob_end_clean();
		echo optimizeReturn ( $ret );
		ob_end_flush();
		}
	else
		{
		$ret = ob_get_contents();
		ob_end_clean();
		if ( isset ( $http_vars ['result_type_advanced'] ) && ($http_vars ['result_type_advanced'] == 'csv_naws') )
			{
			header ( 'content-disposition:attachment;filename="meeting_search_naws_format.csv"' );
			}
		else
			{
			header ( 'content-disposition:attachment;filename="meeting_search.csv"' );
			}
		header ( "content-type:text/csv" );
		echo $ret;
		ob_end_flush();
		}
	}
/**
	\brief This function checks to make sure the database is correct for the current version.
*/
function DB_Connect_and_Upgrade ( )
{
	include ( dirname ( __FILE__ )."/../server/config/auto-config.inc.php" );
	c_comdef_dbsingleton::init ( $dbType, $dbServer, $dbName, $dbUser, $dbPassword, 'utf8' );

	try
		{
		// Version 1.3 added 1 column to the main meeting table.
		$table = "$dbPrefix"."_comdef_meetings_main";

		// We start with default 1, to set the existing records, then we change to 0 for future records.
		$alter_sql = "ALTER TABLE $table ADD published TINYINT NOT NULL DEFAULT 1";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		$alter_sql = "ALTER TABLE $table ALTER COLUMN published SET DEFAULT 0";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		// Make sure we can look it up quickly.
		$alter_sql = "CREATE INDEX published ON $table (published)";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		}
	catch ( Exception $e )
		{
		// We don't die if the thing already exists. We just mosey on along as if nothing happened.
		}

	try
		{
		// Version 1.3.4 added 1 column to the service body table.
		$table = "$dbPrefix"."_comdef_service_bodies";

		$alter_sql = "ALTER TABLE $table ADD sb_meeting_email VARCHAR(255) DEFAULT NULL";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		$alter_sql = "CREATE INDEX sb_meeting_email ON $table (sb_meeting_email)";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		}
	catch ( Exception $e )
		{
		// We don't die if the thing already exists. We just mosey on along as if nothing happened.
		}

	try
		{
		// Version 1.3.6 added 1 column to the main meeting table for meeting-specific email contact.
		$table = "$dbPrefix"."_comdef_meetings_main";

		$alter_sql = "ALTER TABLE $table ADD email_contact VARCHAR(255) DEFAULT NULL";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		$alter_sql = "CREATE INDEX email_contact ON $table (email_contact)";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		}
	catch ( Exception $e )
		{
		// We don't die if the thing already exists. We just mosey on along as if nothing happened.
		}
		
	try
		{
		// Version 1.3.6 added 1 column to the meeting data tables for visibility.
		$table = "$dbPrefix"."_comdef_meetings_data";
		$alter_sql = "ALTER TABLE `$table` ADD `visibility` INT( 1 ) NULL DEFAULT NULL AFTER `lang_enum`";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		$alter_sql = "CREATE INDEX visibility ON $table (visibility)";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		$table = "$dbPrefix"."_comdef_meetings_longdata";
		$alter_sql = "ALTER TABLE `$table` ADD `visibility` INT( 1 ) NULL DEFAULT NULL AFTER `lang_enum`";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		$alter_sql = "CREATE INDEX visibility ON $table (visibility)";
		c_comdef_dbsingleton::preparedExec($alter_sql);
		}
	catch ( Exception $e )
		{
		// We don't die if the thing already exists. We just mosey on along as if nothing happened.
		}
}

/**
	\brief This function parses the main server version from the XML file.
	
	\returns a string, containing the version info.
*/
function GetServerInfo($in_offset)
{
	$ret = null;
	
	if ( file_exists ( dirname ( __FILE__ ).'/../client_interface/serverInfo.xml' ) )
		{
		$info_file = new DOMDocument;
		if ( $info_file instanceof DOMDocument )
			{
			if ( @$info_file->load ( dirname ( __FILE__ ).'/../client_interface/serverInfo.xml' ) )
				{
				$has_info = $info_file->getElementsByTagName ( "bmltInfo" );
				
				if ( ($has_info instanceof domnodelist) && $has_info->length )
					{
					$ret = XML2Array ( $has_info->item(0) );
					}
				}
			}
		}
	
	$config_file_path = $in_offset.'server/config/auto-config.inc.php';
	
	if ( file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
		if ( isset ( $banner_text ) && trim ( $banner_text ) )
			{
			$ret['banner_text'] = trim ( $banner_text );
			}
		}
	
	return $ret;
}

/**
	\brief This function returns a DOMNodeList as an array.
	
	\returns an associative, nested array, containing the data in the DOMNodeList.
*/
function DList2Array ( $in_xml_dom_list )
{
	$ret = null;
	
	for ( $i = 0; $i < $in_xml_dom_list->length; $i++ )
		{
		$the_item = $in_xml_dom_list->item($i);
		if ( $the_item instanceof domnode )
			{
			if ( $the_item->localName )
				{
				$ret[$the_item->localName] = XML2Array ( $the_item );
				}
			}
		}
	
	return $ret;
}

/**
	\brief This function returns an XML entity or a DOMNodeList as an array.
	
	\returns an associative, nested array, containing the data in the XML/DOMNodeList.
*/
function XML2Array ( $in_xml_dom_obj )
{
	$ret = null;
	
	if ( ($in_xml_dom_obj instanceof domnode) || ($in_xml_dom_obj instanceof domnodelist) )
		{
		if ( $in_xml_dom_obj instanceof domnodelist )
			{
			$ret = DList2Array ( $in_xml_dom_obj );
			}
		elseif ( ($in_xml_dom_obj instanceof domnode) && $in_xml_dom_obj->hasChildNodes () && ($in_xml_dom_obj->childNodes->length > 1) )
			{
			$ret = DList2Array ( $in_xml_dom_obj->childNodes );
			}
		else
			{
			$ret = $in_xml_dom_obj->nodeValue;
			}
		}
	
	return $ret;
}
	
/**
	\brief see if we are dealing with a mobile browser that uses a small screen and limited bandwidth.
	
	\returns a Boolean. True if the browser is one that should get the special version of our site.
*/
function special_small_site ( $in_http_vars = null	///< The HTTP GET and POST variables. If not supplied, we try GET first, then POST.
							)
{
	$ret = isset ( $in_http_vars['simulate_iphone'] ) || preg_match ( '/ipod/i', $_SERVER['HTTP_USER_AGENT'] ) || preg_match ( '/iphone/i', $_SERVER['HTTP_USER_AGENT'] );
	
	if ( !$ret )
		{
		$ret = isset ( $in_http_vars['simulate_android'] ) || preg_match ( '/android/i', $_SERVER['HTTP_USER_AGENT'] );
		}

	if ( !$ret )
		{
		$ret = isset ( $in_http_vars['simulate_blackberry'] ) || preg_match ( '/blackberry/i', $_SERVER['HTTP_USER_AGENT'] );
		}

	if ( !$ret )
		{
		$ret = isset ( $in_http_vars['simulate_opera_mini'] ) || preg_match ( "/opera\s+mini/i", $_SERVER['HTTP_USER_AGENT'] );
		}
	
	return $ret;
}

/**
	\brief Optimizes the given browser code. It removes PHP, for safety.
	
	\returns a string, containing the optimized browser code.
*/
function optimizeReturn ( $in_data
						)
	{
    if ( !defined ( '__DEBUG_MODE__' ) )
        {
        $script_head = '<script type="text/javascript">/* <![CDATA[ */';
        $script_foot = '/* ]]> */</script>';
        $style_head = '<style type="text/css">/* <![CDATA[ */';
        $style_foot = '/* ]]> */</style>';
        $ret = preg_replace('/\<\?php.*?\?\>/', '', $in_data);
        $ret = preg_replace('/<!--(.|\s)*?-->/', '', $ret);
        $ret = preg_replace('/\/\*(.|\s)*?\*\//', '', $ret);
        $ret = preg_replace( "|\s+\/\/.*|", " ", $ret );
        $ret = preg_replace( "/\s+/", " ", $ret );
        $ret = preg_replace( "|\<script type=\"text\/javascript\"\>(.*?)\<\/script\>|", "$script_head$1$script_foot", $ret );
        $ret = preg_replace( "|\<style type=\"text\/css\"\>(.*?)\<\/style\>|", "$style_head$1$style_foot", $ret );
        
        return $ret;
        }
    else
        {
        return $in_data;
        }
	}
?>