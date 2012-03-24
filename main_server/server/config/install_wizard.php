<?php
/***********************************************************************/
/** \file	install_wizard.php
	\version 1.2.15
	\brief	This is a step-by-step "setup" for a BMLT root server.
    
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
	defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.
	
	global $config_file_path;
	
	$config_file_path = 'server/config/auto-config.inc.php';
	
	if ( (isset ( $_GET['wizard_page'] ) && $_GET['wizard_page']) && !isset ( $_POST['wizard_page'] ) || ! $_POST['wizard_page'] )
		{
		$_POST['wizard_page'] = $_GET['wizard_page'];
		}
	
	$wiz_page = $_POST['wizard_page'];
	
	if ( $wiz_page > 10 )
		{
		$wiz_page = intval ( $wiz_page / 10 );
		}
	
	if ( file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
		}
	
	if ( !isset ( $theme ) || ! $theme )
		{
		$theme = 'default';
		}
	
	$shortcut_icon = "themes/$theme/html/images/shortcut.png";
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>BMLT Root Server Installer</title>
		<link type="text/css" rel="stylesheet" href="server/config/install_wizard.css" />
		<link rel="SHORTCUT ICON" href="<?php echo htmlspecialchars ( $shortcut_icon ) ?>" />
		<link rel="ICON" href="<?php echo htmlspecialchars ( $shortcut_icon ) ?>" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<script type="text/javascript">function LoadSelect(){var elem = document.getElementById('initial_field'); if( elem ) { elem.select(); };};window.onload=LoadSelect</script>
	</head>
	<body class="install_wizard_body">
		<div class="bmlt_installer_header_div">
			<h1 class="page_header">BMLT Root Server Installer</h1>
		</div>
		<div class="bmlt_installer_menu_div">
			<div id="page_1_menu"><?php if ( intval ( $wiz_page ) > 1 ) {echo '<a href="index.php?wizard_page=1">';}else{echo '<strong>';}; ?>Check Environment<?php if ( intval ( $wiz_page ) > 1 ){echo '</a>';}else{echo '</strong>';}; ?></div>
			<div id="page_2_menu"><?php if ( intval ( $wiz_page ) > 2 ) {echo '<a href="index.php?wizard_page=21">';}elseif ( intval ( $wiz_page ) == 2 ){echo '<strong>';}; ?>Optional Features<?php if ( intval ( $wiz_page ) > 2 ){echo '</a>';}elseif ( intval ( $wiz_page ) == 2 ){echo '</strong>';}; ?></div>
			<div id="page_3_menu"><?php if ( intval ( $wiz_page ) > 3 ) {echo '<a href="index.php?wizard_page=31">';}elseif ( intval ( $wiz_page ) == 3 ){echo '<strong>';}; ?>Default Map Location<?php if ( intval ( $wiz_page ) > 3 ){echo '</a>';}elseif ( intval ( $wiz_page ) == 3 ){echo '</strong>';}; ?></div>
			<div id="page_4_menu"><?php if ( intval ( $wiz_page ) > 4 ) {echo '<a href="index.php?wizard_page=41">';}elseif ( intval ( $wiz_page ) == 4 ){echo '<strong>';}; ?>Miscellaneous Settings<?php if ( intval ( $wiz_page ) > 4 ){echo '</a>';}elseif ( intval ( $wiz_page ) == 4 ){echo '</strong>';}; ?></div>
			<div id="page_5_menu"><?php if ( intval ( $wiz_page ) == 5 ){echo '<strong>';}; ?>Create Database<?php if ( intval ( $wiz_page ) == 5 ){echo '</strong>';}; ?></div>
			<div style="clear:both"></div>
		</div>
<?php		
	switch ( intval ( $_POST['wizard_page'] ) )
		{
		case 6:
			if ( SetPageFive ( ) )
				{
				PageSix ( );
				}
			else
				{
				PageFive ( );
				}
		break;
		
		case 51:
			PageFive ( );
		break;
		
		case 5:
			if ( SetPageFour ( ) )
				{
				PageFive ( );
				}
			else
				{
				PageFour ( );
				}
		break;
		
		case 41:
			PageFour ( );
		break;
		
		case 4:
			if ( SetPageThree ( ) )
				{
				PageFour ( );
				}
			else
				{
				PageThree ( );
				}
		break;
		
		case 31:
			PageThree ( );
		break;
		
		case 3:
			if ( SetPageTwo ( ) )
				{
				PageThree ( );
				}
			else
				{
				PageTwo ( );
				}
		break;
		
		case 21:
			PageTwo ( );
		break;
		
		case 2:
			if ( SetPageOne ( ) )
				{
				PageTwo ( );
				break;
				}
		default:
			PageOne ( );
		break;
		}
?>
	</body>
</html>
<?php
function SetPageOne ( )
{
	global $config_file_path;
	$ret = true;
	$err = '';
	$auto_file = "<?php\n";
	$auto_file .= "defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.\n\n";
	$auto_file .= "// ##BEGIN_PAGE_1## Install Wizard Page One Items:\n";
	
	if ( (fileperms ( $config_file_path ) & 0x0002) == 0x0002 )
		{
		if ( isset ( $_POST['db_type'] ) && $_POST['db_type'] )
			{
			$auto_file .= "\t".'$dbType = "'.$_POST['db_type']."\";\n";
			$dbType = $_POST['db_type'];
			}
		else
			{
			$ret = false;
			$err = "The database name is empty!";
			}
		
		if ( $ret && isset ( $_POST['db_name'] ) && $_POST['db_name'] )
			{
			$auto_file .= "\t".'$dbName = \''.$_POST['db_name']."';\n";
			$dbName = $_POST['db_name'];
			}
		else
			{
			$ret = false;
			$err = "The database name is empty!";
			}
		
		if ( $ret && isset ( $_POST['db_host'] ) && $_POST['db_host'] )
			{
			$auto_file .= "\t".'$dbServer = \''.$_POST['db_host']."';\n";
			$dbServer = $_POST['db_host'];
			}
		else
			{
			$ret = false;
			$err = "The database host is empty!";
			}
		
		if ( $ret && isset ( $_POST['db_user'] ) && $_POST['db_user'] )
			{
			$auto_file .= "\t".'$dbUser = \''.$_POST['db_user']."';\n";
			$dbUser = $_POST['db_user'];
			}
		else
			{
			$ret = false;
			$err = "The database user is empty!";
			}
		
		if ( $ret && isset ( $_POST['db_pass'] ) && $_POST['db_pass'] )
			{
			$auto_file .= "\t".'$dbPassword = \''.$_POST['db_pass']."';\n";
			$dbPassword = $_POST['db_pass'];
			}
		else
			{
			$ret = false;
			$err = "The database password is empty!";
			}
		
		if ( $ret && isset ( $_POST['db_prefix'] ) && $_POST['db_prefix'] )
			{
			$auto_file .= "\t".'$dbPrefix = "'.$_POST['db_prefix']."\";\n";
			$dbPrefix = $_POST['db_prefix'];
			}
		else
			{
			$ret = false;
			$err = "The database prefix is empty!";
			}
		
		if ( $ret && isset ( $_POST['bmlt_title'] ) && $_POST['bmlt_title'] )
			{
			$auto_file .= "\t".'$bmlt_title = "'.$_POST['bmlt_title']."\";\n";
			$dbPrefix = $_POST['bmlt_title'];
			}
		else
			{
			$ret = false;
			$err = "The Root Server Name is empty!";
			}
		}
	else
		{
		$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not writable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing (0666 or 0777).';
		$ret = false;
		}
	
	if ( $ret )
		{
		include_once ( 'server/classes/c_comdef_dbsingleton.class.php' );
		
		c_comdef_dbsingleton::init ( $dbType, $dbServer, $dbName, $dbUser, $dbPassword );
		
		try
			{
			c_comdef_dbsingleton::connect();
			}
		catch ( Exception $e )
			{
			$ret = false;
			$err = "The database connection failed! Please make sure that the database is set up, the user is created, and has full permissions.";
			}
		}
	
	if ( $ret )
		{
		$file = fopen ( $config_file_path, 'w' );
		if ( $file )
			{
			if ( !ftruncate ( $file, 0 ) )
				{
				$ret = false;
				$err = "Failed setting the ".htmlspecialchars ( $config_file_path )." file to an initial blank.";
				}
			
			if ( $ret && !fwrite ( $file, $auto_file.'// ##END_PAGE_1##' ) )
				{
				$ret = false;
				$err = "Failed writing page 1 to the ".htmlspecialchars ( $config_file_path )." file.";
				}
			
			fclose ( $file );
			}
		else
			{
			$ret = false;
			$err = "Failed opening the ".htmlspecialchars ( $config_file_path )." file to write.";
			}
		}
	
	if ( !$ret )
		{
		echo "<p class=\"error_disp\">$err</p>";
		}

	return $ret;
}

function SetPageTwo ( )
{
	global $config_file_path;
	$ret = true;
	$err = '';
	
	if ( (fileperms ( $config_file_path ) & 0x0002) == 0x0002 )
		{
		$auto_file = file_get_contents ( $config_file_path );
		
		if ( preg_match ( '|##END_PAGE_1##|', $auto_file ) )
			{
			$auto_file = preg_replace ( '|(.*?)// ##END_PAGE_1##|', "$_1// ##END_PAGE_1##", $auto_file );
			$auto_file .= "\n\n// ##BEGIN_PAGE_2## Install Wizard Page Two Items:\n";
			if ( $auto_file )
				{
				if ( isset ( $_POST['theme'] ) && $_POST['theme'] )
					{
					$auto_file .= "\t".'$theme = "'.$_POST['theme']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The theme is empty!";
					}
				
				if ( isset ( $_POST['comdef_global_language'] ) && $_POST['comdef_global_language'] )
					{
					$auto_file .= "\t".'$comdef_global_language = "'.$_POST['comdef_global_language']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The language is empty!";
					}
				
				if ( isset ( $_POST['min_pw_len'] ) && $_POST['min_pw_len'] )
					{
					$auto_file .= "\t".'$min_pw_len = '.$_POST['min_pw_len'].";\n";
					}
				else
					{
					$ret = false;
					$err = "The minimum password length is empty!";
					}
				
				if ( isset ( $_POST['any_service_body_admin_can_edit_formats'] ) && $_POST['any_service_body_admin_can_edit_formats'] )
					{
					$auto_file .= "\t\$any_service_body_admin_can_edit_formats = true;\n";
					}
				else
					{
					$auto_file .= "\t\$any_service_body_admin_can_edit_formats = false;\n";
					}
				
				if ( isset ( $_POST['any_service_body_admin_can_create_service_bodies'] ) && $_POST['any_service_body_admin_can_create_service_bodies'] )
					{
					$auto_file .= "\t\$any_service_body_admin_can_create_service_bodies = true;\n";
					}
				else
					{
					$auto_file .= "\t\$any_service_body_admin_can_create_service_bodies = false;\n";
					}
				}
			else
				{
				$ret = false;
				$err = 'The '.htmlspecialchars ( $config_file_path ).' file does not seem to be correctly formatted!';
				}
			}
		else
			{
			$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not readable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing AND reading! (0666 or 0777).';
			$ret = false;
			}
		}
	else
		{
		$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not writable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing (0666 or 0777).';
		$ret = false;
		}
	
	if ( $ret )
		{
		$file = fopen ( $config_file_path, 'w' );
		if ( $file )
			{
			if ( !ftruncate ( $file, 0 ) )
				{
				$ret = false;
				$err = "Failed setting the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file to an initial blank.";
				}
			
			if ( $ret && !fwrite ( $file, $auto_file.'// ##END_PAGE_2##' ) )
				{
				$ret = false;
				$err = "Failed writing page 2 to the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file.";
				}
			
			fclose ( $file );
			}
		else
			{
			$ret = false;
			$err = "Failed opening the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file to write.";
			}
		}
	
	if ( !$ret )
		{
		echo "<p class=\"error_disp\">$err</p>";
		}

	return $ret;
}

function SetPageThree ( )
{
	global $config_file_path;
	$ret = true;
	$err = '';
	
	if ( (fileperms ( $config_file_path ) & 0x0002) == 0x0002 )
		{
		$auto_file = file_get_contents ( $config_file_path );
		
		if ( preg_match ( '|##END_PAGE_2##|', $auto_file ) )
			{
			$auto_file = preg_replace ( '|(.*?)// ##END_PAGE_2##|', "$_1// ##END_PAGE_2##", $auto_file );
			$auto_file .= "\n\n// ##BEGIN_PAGE_3## Install Wizard Page Three Items:\n";
			if ( $auto_file )
				{
				$auto_file .= "\t".'$region_bias = ';
				if ( isset ($_POST['region_bias'] ) && trim($_POST['region_bias']) )
					{
					$auto_file .= '"'.strtolower(trim($_POST['region_bias']))."\"";
					}
				else
					{
					$auto_file .= 'null';
					}
					
				$auto_file .= ";\n";
				
				if ( isset ( $_POST['gkey'] ) && $_POST['gkey'] )
					{
					$auto_file .= "\t".'$gkey = "'.$_POST['gkey']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Google Maps API Key is empty!";
					}
	
				if ( $ret && isset ( $_POST['bmlt_map_center_longitude'] ) && isset ( $_POST['bmlt_map_center_latitude'] ) && isset ( $_POST['bmlt_map_zoom'] ) )
					{
					$auto_file .= "\t".'$search_spec_map_center = array ( \'longitude\' => '.$_POST['bmlt_map_center_longitude'].', \'latitude\' => '.$_POST['bmlt_map_center_latitude'].', \'zoom\' => '.$_POST['bmlt_map_zoom']." );\n";
					}
				else
					{
					$ret = false;
					$err = "The location is empty!";
					}
				}
			else
				{
				$ret = false;
				$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file does not seem to be correctly formatted!';
				}
			}
		else
			{
			$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not readable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing AND reading! (0666 or 0777).';
			$ret = false;
			}
		}
	else
		{
		$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not writable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing (0666 or 0777).';
		$ret = false;
		}
	
	if ( $ret )
		{
		$file = fopen ( $config_file_path, 'w' );
		if ( $file )
			{
			if ( !ftruncate ( $file, 0 ) )
				{
				$ret = false;
				$err = "Failed setting the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file to an initial blank.";
				}
			
			if ( $ret && !fwrite ( $file, $auto_file.'// ##END_PAGE_3##' ) )
				{
				$ret = false;
				$err = "Failed writing page 3 to the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file.";
				}
			
			fclose ( $file );
			}
		else
			{
			$ret = false;
			$err = "Failed opening the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file to write.";
			}
		}
	
	if ( !$ret )
		{
		echo "<p class=\"error_disp\">$err</p>";
		}

	return $ret;
}

function SetPageFour ( )
{
	global $config_file_path;
	$ret = true;
	$err = '';
	
	if ( (fileperms ( $config_file_path ) & 0x0002) == 0x0002 )
		{
		$auto_file = file_get_contents ( $config_file_path );
		
		if ( preg_match ( '|##END_PAGE_3##|', $auto_file ) )
			{
			$auto_file = preg_replace ( '|(.*?)// ##END_PAGE_3##|', "$_1// ##END_PAGE_3##", $auto_file );
			$auto_file .= "\n\n// ##BEGIN_PAGE_4## Install Wizard Page Four Items:\n";
			if ( $auto_file )
				{
				if ( isset ( $_POST['results_per_page'] ) && $_POST['results_per_page'] )
					{
					$auto_file .= "\t".'$results_per_page = "'.$_POST['results_per_page']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Results Per Page is empty!";
					}
				
				if ( $ret && isset ( $_POST['number_of_meetings_for_auto'] ) && $_POST['number_of_meetings_for_auto'] )
					{
					$auto_file .= "\t".'$number_of_meetings_for_auto = "'.$_POST['number_of_meetings_for_auto']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Number of Meetings for Auto is empty!";
					}
				
				if ( $ret && isset ( $_POST['default_basic_search'] ) && $_POST['default_basic_search'] )
					{
					$auto_file .= "\t".'$default_basic_search = "'.$_POST['default_basic_search']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Default Basic Search is empty!";
					}
				
				if ( $ret && isset ( $_POST['default_sort_key'] ) && $_POST['default_sort_key'] )
					{
					$auto_file .= "\t".'$default_sort_key = "'.$_POST['default_sort_key']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Default Sort Key is empty!";
					}
				
				if ( $ret && isset ( $_POST['default_sort_dir'] ) && $_POST['default_sort_dir'] )
					{
					$auto_file .= "\t".'$default_sort_dir = "'.$_POST['default_sort_dir']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Default Sort Direction is empty!";
					}
				
				if ( $ret && isset ( $_POST['static_map_size_x'] ) && $_POST['static_map_size_x'] && isset ( $_POST['static_map_size_y'] ) && $_POST['static_map_size_y'] )
					{
					$auto_file .= "\t".'$static_map_size = array ( \'width\' => 600, \'height\'=> 600'." );\n";
					}
				else
					{
					$ret = false;
					$err = "The Static Map Size Array is empty!";
					}
				
				if ( $ret && isset ( $_POST['time_format'] ) && $_POST['time_format'] )
					{
					$auto_file .= "\t".'$time_format = "'.$_POST['time_format']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Time Format is empty!";
					}
				
				if ( $ret && isset ( $_POST['change_date_format'] ) && $_POST['change_date_format'] )
					{
					$auto_file .= "\t".'$change_date_format = "'.$_POST['change_date_format']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Change Date/Time Format is empty!";
					}
				
				if ( $ret && isset ( $_POST['change_depth_for_meetings'] ) && $_POST['change_depth_for_meetings'] )
					{
					$auto_file .= "\t".'$change_depth_for_meetings = "'.$_POST['change_depth_for_meetings']."\";\n";
					}
				else
					{
					$ret = false;
					$err = "The Change Depth for Meetings is empty!";
					}
				
				if ( $ret && isset ( $_POST['allow_contact_form'] ) && $_POST['allow_contact_form'] )
					{
					$auto_file .= "\t\$allow_contact_form = true;\n";
					}
				else
					{
					$auto_file .= "\t\$allow_contact_form = false;\n";
					}
				
				if ( $ret && isset ( $_POST['recursive_contact_form'] ) && $_POST['recursive_contact_form'] )
					{
					$auto_file .= "\t\$recursive_contact_form = true;\n";
					}
				else
					{
					$auto_file .= "\t\$recursive_contact_form = false;\n";
					}
				
				if ( $ret && isset ( $_POST['allow_pdf_downloads'] ) && $_POST['allow_pdf_downloads'] )
					{
					$auto_file .= "\t\$allow_pdf_downloads = true;\n";
					}
				else
					{
					$auto_file .= "\t\$allow_pdf_downloads = false;\n";
					}
				
				if ( $ret && isset ( $_POST['banner_text'] ) && trim ( $_POST['banner_text'] ) )
					{
					$auto_file .= "\t\$banner_text = '".trim ( $_POST['banner_text'] )."';\n";
					}
				else
					{
					$auto_file .= "\t\$banner_text = null;\n";
					}
				
				// These are very solid for now:
				
				$auto_file .= "\t".'$default_sorts = array ('.
							"'weekday' => array('weekday_tinyint','location_municipality','location_city_subsection','start_time','location_neighborhood'),".
							"'time' => array('weekday_tinyint','start_time','location_municipality','location_city_subsection','location_neighborhood'),".
							"'town' => array('location_municipality','location_city_subsection','location_neighborhood','weekday_tinyint','start_time')".
							');
	$page_display_size = 11;			// This is the number of page links shown in the list view before the elipsis (...) is shown to separate them.
	$disable_zoom_in_clicks = false;	// Set this to true if you want the first click to always result in a search, even if the map is zoomed far out.
	$sort_depth = 8;
	$root_server = null;
	$comdef_native_service_body = 1;	// This is the ID of the default Service body, selected in the popup for new meetings.
	$serverNamespace = null;
	$admin_session_name = \'BMLT_Admin\';
	$client_session_name = \'BMLT_Satellite\';'."\n";
				
                if ( $ret && isset ( $_POST['distance_units'] ) && trim ( $_POST['distance_units'] ) )
                    {
                    $auto_file .= "\t\$comdef_distance_units = '".trim ( $_POST['distance_units'] )."';\n";
                    }
                else
                    {
                    $auto_file .= "\t\$comdef_distance_units = \'mi\';\n";
                    }
                
                if ( $ret && isset ( $_POST['comdef_show_sb_desc'] ) && trim ( $_POST['comdef_show_sb_desc'] ) )
                    {
                    $auto_file .= "\t\$comdef_show_sb_desc = ".(trim ( $_POST['comdef_show_sb_desc'] )?'true':'false').";\n";
                    }
                else
                    {
                    $auto_file .= "\t\$comdef_show_sb_desc = false;\n";
                    }

				$auto_file .= "\t".'if ( !defined ( \'_DEFAULT_DURATION\' ) ) define ( \'_DEFAULT_DURATION\', \'N.A. Meetings are usually 90 minutes long (an hour and a half), unless otherwise indicated.\' );
	if ( !defined ( \'WC_FORMAT\' ) ) define ( \'WC_FORMAT\', \'33\' );	// These are used for the NAWS format translation. They are the shared IDs of the wheelchair, open and closed formats.
	if ( !defined ( \'O_FORMAT\' ) ) define ( \'O_FORMAT\', \'17\' );
	if ( !defined ( \'C_FORMAT\' ) ) define ( \'C_FORMAT\', \'4\' );'."\n";
				}
			else
				{
				$ret = false;
				$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file does not seem to be correctly formatted!';
				}
			}
		else
			{
			$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not readable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing AND reading! (0666 or 0777).';
			$ret = false;
			}
		}
	else
		{
		$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not writable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing (0666 or 0777).';
		$ret = false;
		}
	
	if ( $ret )
		{
		$file = fopen ( $config_file_path, 'w' );
		if ( $file )
			{
			if ( !ftruncate ( $file, 0 ) )
				{
				$ret = false;
				$err = "Failed setting the ".htmlspecialchars ( $config_file_path )." file to an initial blank.";
				}
			
			if ( $ret && !fwrite ( $file, $auto_file.'// ##END_PAGE_4##'."\n?>" ) )
				{
				$ret = false;
				$err = "Failed writing page 4 to the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file.";
				}
			
			fclose ( $file );
			}
		else
			{
			$ret = false;
			$err = "Failed opening the ".htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" )." file to write.";
			}
		}
	
	if ( !$ret )
		{
		echo "<p class=\"error_disp\">$err</p>";
		}

	return $ret;
}

function SetPageFive ( )
{
	global $config_file_path;
	$ret = true;
	$err = '';
	
	if ( isset ( $_POST['admin_user'] ) && $_POST['admin_user'] )
		{
		$login = $_POST['admin_user'];
		}
	else
		{
		$ret = false;
		$err = 'The Admin Login ID is Blank!';
		}
	
	if ( isset ( $_POST['admin_password'] ) && $_POST['admin_password'] )
		{
		include_once ( 'server/shared/classes/comdef_utilityclasses.inc.php' );
		$password = FullCrypt ( $_POST['admin_password'] );
		}
	else
		{
		$ret = false;
		$err = 'The Admin Password is Blank!';
		}
	
	if ( $ret && file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
		
		$sql = file_get_contents ( 'server/config/StandardDBTemplate.sql' );
		$sql = str_replace ( '%ADMIN-LOGIN%', $login, trim ( $sql ) );
		$sql = str_replace ( '%SB-ADMIN-PW%', $password, trim ( $sql ) );
		$sql = str_replace ( '%ADMIN-PW%', $password, trim ( $sql ) );
		
		try
		    {
    		include_once ( 'server/classes/c_comdef_dbsingleton.class.php' );
		    c_comdef_dbsingleton::init ( $dbType, $dbServer, $dbName, $dbUser, $dbPassword );
		    c_comdef_dbsingleton::preparedExec ( $sql, array() );
			}
		catch ( Exception $e )
			{
			$ret = false;
echo '<pre>';
echo htmlspecialchars(print_r($e));
die ('</pre>');
//			$err = "The database connection failed! Please make sure that the database is set up, the user is created, and has full permissions.";
			}
		}
	else
		{
		echo '<p class=\"error_disp\">The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file does not exist! This file needs to exist, and be writable!</p>';
		}
	
	if ( !$ret )
		{
		echo "<p class=\"error_disp\">$err</p>";
		}
	
	return $ret;
}

function PageOne ( )
{
	global $config_file_path;
	
	// This might not work, but it's worth a try...
	// Create an empty file.
	if ( !file_exists ( $config_file_path ) )
		{
		if ( $file = @fopen ( $config_file_path, 'w+' ) )
			{
			@fclose ( $file );
			@chmod ( $config_file_path, 0666 );
			}
		}
	
	if ( file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
						
		if ( !isset ( $dbType ) || !$dbType )
			{
			$dbType = 'mysql';
			}
		
		if ( !isset ( $dbServer ) || !$dbServer )
			{
			$dbServer = 'localhost';
			}
		
		if ( !isset ( $dbPrefix ) || !$dbPrefix )
			{
			$dbPrefix = 'na';
			}
		
		if ( !isset ( $dbName ) )
			{
			$dbName = '';
			}
		
		if ( !isset ( $dbUser ) )
			{
			$dbUser = '';
			}
		
		if ( !isset ( $dbPassword ) )
			{
			$dbPassword = '';
			}
?>
		<div class="bmlt_installer_page_div page_1">
			<div class="intro">
				<p class="first">Welcome to the installation wizard for the Basic Meeting List Toolbox. You do not appear to have set up
				a server yet, so this installer will create a minimal installation for you. You'll need to answer a few questions, and we'll
				get you up and running in no time!</p>
			</div>
			<div class="guide">
				<p class="first">The first thing that we need to do is verify the prerequisites for an installation.</p>
				<p class="first">The server upon which this installation will be made has to have the following:</p>
				<form action ="<?php echo htmlspecialchars ( $_SERVER['PHP_SELF'] ) ?>" method="post"><div>
					<input type="hidden" value="2" name="wizard_page" />
					<dl class="req_list">
						<dt><span class="li_item">Config File Writable?</span><span class="li_item_ans">
<?php
						if ( (fileperms ( $config_file_path ) & 0x0002) == 0x0002 )
							{
							echo '<img src="server/config/images/Yes.gif" alt="YES" style="height:24px" /></span></dt>';
							echo '<dt><span class="li_item">PHP <a href="http://us.php.net/ChangeLog-5.php#5.1.0" target="_blank">5.1.0</a> or greater</span><span class="li_item_ans">';
							if ( version_compare (PHP_VERSION,'5.1.0','>') )
								{
								echo '<img src="server/config/images/Yes.gif" alt="YES" style="height:24px" />';
								echo '</span></dt><dt><span class="li_item">PHP <a target="_blank" href="http://us.php.net/pdo">PDO</a> Support</span><span class="li_item_ans">';
								if ( class_exists ( 'PDO' ) )
									{
									echo '<img src="server/config/images/Yes.gif" alt="YES" style="height:24px" /></span></dt>';
		
									if ( count ( PDO::getAvailableDrivers() ) )
										{
										echo '<dt><label for="db_type" class="li_item_sel_prompt">An <a href="http://www.w3schools.com/Sql/default.asp" target="_blank">SQL</a> database:</label><span class="db_select"><select id="db_type" name="db_type">';
										foreach ( PDO::getAvailableDrivers() as $driver )
											{
											echo '<option value="'.htmlspecialchars ( $driver ).'"';
												if ( $driver == $dbType )
													{
													echo ' selected="selected"';
													}
											echo '>'.htmlspecialchars ( $driver ).'</option>';
											}
										echo '</select> <em>(Select one)</em> that can be reached via PDO <img src="server/config/images/Yes.gif" alt="YES" style="height:24px" /></span></dt>';
										echo "<dd>If your database doesn't show up in this list, then its PDO driver needs to be installed. That is beyond the scope of this installer. Contact your hosting provider, or install the driver yourself.</dd>";
										}
									else
										{
										$err = 'Even though you have PDO, you have no database drivers installed!';
										echo '<dt><span class="li_item">An SQL database</span><span class="li_item_ans"><img src="server/config/images/No.gif" alt="NO" style="height:24px" /></span></dt>';
										}
									}
								else
									{
									$err = 'No PDO support!';
									echo '<img src="server/config/images/No.gif" alt="NO" style="height:24px" /></span></dt>';
									}
								}
							else
								{
								$err = 'PHP must be AT LEAST version 5.1!';
								echo '<img src="server/config/images/No.gif" alt="NO" style="height:24px" /></span></dt>';
								}
							}
						else
							{
							$err = 'The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is not writable! This file has to be writable in order for us to continue. Please set the permissions for this file to allow writing (0666 or 0777).';
							echo '<img src="server/config/images/No.gif" alt="NO" style="height:24px" /></span></dt>';
							}
?>
					</dl>
<?php
				if ( $err )
					{
					echo "<p class=\"error_disp\">$err</p>";
					echo '<p class="submit_button"><a href="'.htmlspecialchars ( $_SERVER['PHP_SELF'] ).'">Try Again</a></p>';
					}
				else
					{
					if ( !$bmlt_title )
						{
						if ( !$comdef_global_language )
							{
							$comdef_global_language = 'en';
							}
						
						include ( dirname ( __FILE__ )."/lang/$comdef_global_language/search_results_strings.inc.php" );
						$bmlt_title = $comdef_search_results_strings['Root_Page_Title'];
						}
?>
					<p>That's pretty much it. We don't require a whole lot of fancy stuff.</p>
					<p>Now, let's get the scoop on the database. You should have selected a database type in the popup menu above.</p>
					<p>Before you can install the BMLT, you need to have done two things:</p>
					<ol class="req_list">
						<li><a href="http://www.w3schools.com/Sql/sql_create_db.asp" target="_blank">Created</a> a new, empty database.</li>
						<li>Created a user with full rights to that database.</li>
					</ol>
					<p>Enter the name of your Root Server:</p>
					<p class="text_input"><label class="form_prompt" for="title_field">Root Server Name:</label><span class="form_text_item"><input id="title_field" name="bmlt_title" type="text" size="64" value="<?php echo htmlspecialchars ( $bmlt_title ) ?>" /></p>
					<p>Enter the information for the database and user here:</p>
					<p class="text_input"><label class="form_prompt" for="initial_field">Database Name:</label><span class="form_text_item"><input id="initial_field" name="db_name" type="text" value="<?php echo htmlspecialchars ( $dbName ) ?>" />-</span><span class="side_note">(This is the <em>name</em> of the database, not the <em>type</em> of database).</span></p>
					<p class="text_input"><label class="form_prompt" for="db_host">Database Host:</label><span class="form_text_item"><input id="db_host" name="db_host" type="text" value="<?php echo htmlspecialchars ( $dbServer ) ?>" />-</span><span class="side_note">(Usually &quot;localhost&quot;)</span></p>
					<p class="text_input"><label class="form_prompt" for="db_user">Database User:</label><span class="form_text_item"><input id="db_user" name="db_user" type="text" value="<?php echo htmlspecialchars ( $dbUser ) ?>" />-</span><span class="side_note">(The login name of the database user)</span></p>
					<p class="text_input"><label class="form_prompt" for="db_pass">Database Password:</label><span class="form_text_item"><input id="db_pass" name="db_pass" type="text" value="<?php echo htmlspecialchars ( $dbPassword ) ?>" />-</span><span class="side_note">(The password for the database user)</span></p>
					<p class="text_input"><label class="form_prompt" for="db_prefix">Database Table Prefix:</label><span class="form_text_item"><input id="db_prefix" name="db_prefix" type="text" value="<?php echo htmlspecialchars ( $dbPrefix ) ?>" />-</span><span class="side_note">(Usually left at &quot;na&quot;) The reason for changing this might be multiple root server installs (not currently supported or tested).</span></p>
					<p class="submit_button"><input type="submit" value="Go on to the next step" /></p>
<?php
					}
?>
				</div></form>
			</div>
		</div>
<?php
	}
else
	{
	echo '<p class=\"error_disp\">The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file does not exist! This file needs to exist, and be writable!</p>';
	}
}

function PageTwo ( )
{
	global $config_file_path;
	
	if ( file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
						
		if ( !isset ( $theme ) || !$theme )
			{
			$theme = 'default';
			}
		
		if ( !isset ( $comdef_global_language ) || !$comdef_global_language )
			{
			$comdef_global_language = 'en';
			}
		
		if ( !isset ( $min_pw_len ) || !$min_pw_len )
			{
			$min_pw_len = 6;
			}
		
		if ( !isset ( $any_service_body_admin_can_edit_formats ) )
			{
			$any_service_body_admin_can_edit_formats = false;
			}
		
		if ( !isset ( $any_service_body_admin_can_create_service_bodies ) )
			{
			$any_service_body_admin_can_create_service_bodies = false;
			}
?>
		<div class="bmlt_installer_page_div page_2">
			<div class="intro">
				<p class="first">Okay, we have the database stuff settled. Let's start looking at the other options at our disposal.</p>
			</div>
			<div class="guide">
				<form action ="<?php echo htmlspecialchars ( $_SERVER['PHP_SELF'] ) ?>" method="post"><div>
					<input type="hidden" value="3" name="wizard_page" />
					<p class="text_input">
						<label class="form_prompt" for="theme">Theme:</label>
						<select name="theme" id="theme">
						<?php
							$dirname = dirname ( __FILE__).'/../../themes/';
							$dir = opendir ( $dirname );
							while ( ($file = readdir ( $dir )) !== false )
								{
								if ( is_dir ( $dirname.$file ) && !preg_match ( '|^\.|', $file ) )
									{
									echo '<option value="'.htmlspecialchars ( $file ).'"';
									if ( $file == $theme )
										{
										echo ' selected="selected"';
										}
									echo '>'.htmlspecialchars ( $file ).'</option>';
									}
								}
						?>
						</select>
						(Select One).
					</p>
					<p class="text_input">
						<label class="form_prompt" for="comdef_global_language">Default Server Language:</label>
						<select name="comdef_global_language" id="comdef_global_language">
						<?php
							$dirname = dirname ( __FILE__).'/lang/';
							$dir = opendir ( $dirname );
							while ( ($file = readdir ( $dir )) !== false )
								{
								if ( is_dir ( $dirname.$file ) && !preg_match ( '|^\.|', $file ) && file_exists ( $dirname.$file.'/name.txt' ))
									{
									$name = trim ( file_get_contents ( $dirname.$file.'/name.txt' ) );
									echo '<option value="'.htmlspecialchars ( $file ).'"';
									if ( $file == $comdef_global_language )
										{
										echo ' selected="selected"';
										}
									echo '>'.htmlspecialchars ( $name ).'</option>';
									}
								}
						?>
						</select>
						(Select One).
					</p>
					<p class="text_input">
						<label class="form_prompt" for="min_pw_len">Minimum Password Length:</label>
						<select name="min_pw_len" id="min_pw_len">
							<?php
								for ( $c = 1; $c < 16; $c++ )
									{
									echo '<option value="'.$c.'"';
									
									if ( $c == $min_pw_len )
										{
										echo ' selected="selected"';
										}
									echo '>'.$c.'</option>';
									}
							?>
						</select>
						characters
					</p>
					<p class="text_input"><input type="checkbox" value="true" name="any_service_body_admin_can_edit_formats" id="any_service_body_admin_can_edit_formats"<?php if ( isset ( $any_service_body_admin_can_edit_formats ) && $any_service_body_admin_can_edit_formats ) echo ' checked="checked"'; ?> />
						<label class="checkbox_label" for="any_service_body_admin_can_edit_formats">Any Service Body Administrator Can Edit Formats (Dangerous: Default is off)</label>
					</p>
					<p class="text_input"><input type="checkbox" value="true" name="any_service_body_admin_can_create_service_bodies" id="any_service_body_admin_can_create_service_bodies"<?php if ( isset ( $any_service_body_admin_can_create_service_bodies ) && $any_service_body_admin_can_edit_formats ) echo ' checked="checked"'; ?> />
						<label class="checkbox_label" for="any_service_body_admin_can_create_service_bodies">Any Service Body Administrator Can Create Service Bodies (Dangerous: Default is off)</label>
					</p>

					<p class="submit_button"><input type="submit" value="Go on to the next step" /></p>
				</div></form>
			</div>
		</div>
<?php
	}
else
	{
	echo '<p class=\"error_disp\">The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file does not exist! This file needs to exist, and be writable!</p>';
	}
}

function PageThree ( )
{
	global $config_file_path;
	
	if ( file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
		
		$gkey = '';
		
		if ( isset ( $_POST['gkey_text'] ) && $_POST['gkey_text'] )
			{
			$gkey = $_POST['gkey_text'];
			}
		
		$search_spec_map_center = '';
		
		if ( isset ( $_POST['longitude'] ) && isset ( $_POST['latitude'] ) && isset ( $_POST['zoom'] ) )
			{
			$search_spec_map_center = array ( 'longitude' => $_POST['longitude'], 'latitude' => $_POST['latitude'], 'zoom' => $_POST['zoom'] );
			}
?>
		<div class="bmlt_installer_page_div page_2">
			<div class="intro">
<?php
			
			if ( $gkey && is_array ( $search_spec_map_center ) )
				{
				echo '<p class="centered">Select a center point and zoom for the default search map.</p>';
				echo '<p class="centered smalltext"><a href="index.php?wizard_page=31">Re-Enter the Google Maps API Key</a></p>';
				}
			else
				{
				echo '<p class="first">Now, we\'ll set up the map requirements.</p>';
				}
?>
			</div>
			<div class="guide">
				<form action ="<?php echo htmlspecialchars ( $_SERVER['PHP_SELF'] ) ?>" method="post"><div>
<?php
			
			if ( $gkey && is_array ( $search_spec_map_center ) )
				{
?>
			<p class="submit_button"><input type="submit" value="Go on to the next step" /></p>
			<div id="meeting_map_container">
				<div id="meeting_map"></div>
			</div>
			<input type="hidden" value="<?php echo htmlspecialchars ( $gkey ) ?>" name="gkey" />
			<input type="hidden" value="<?php echo htmlspecialchars ( $_POST['latitude'] ) ?>" id="bmlt_map_center_latitude" name="bmlt_map_center_latitude" />
			<input type="hidden" value="<?php echo htmlspecialchars ( $_POST['longitude'] ) ?>" id="bmlt_map_center_longitude" name="bmlt_map_center_longitude" />
			<input type="hidden" value="<?php echo htmlspecialchars ( $_POST['zoom'] ) ?>" id="bmlt_map_zoom" name="bmlt_map_zoom" />
			<input type="hidden" value="4" name="wizard_page" />
					
			<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo htmlspecialchars ( $gkey ) ?>"></script>
			<script type="text/javascript">
			/* <![CDATA[ */
				var g_geocoder_browser_set_center_map = null;
				
				/*******************************************************************/
				/** \class	c_geocoder_browser_set_center
				
					\brief	This is a special JavaScript Class that manages a Google Map.
				*/

				/** These are the various class data members. */
				c_geocoder_browser_set_center.prototype.point = null;		/**< The current GLatLng for the map marker */
				c_geocoder_browser_set_center.prototype.map = null;			/**< The Google Maps instance */
				c_geocoder_browser_set_center.prototype.marker = null;		/**< The marker instance */
				
				/*******************************************************************/
				/** \brief	Constructor. Sets up the map and the various DOM elements.
				*/
				function c_geocoder_browser_set_center ( in_lat, in_lng, in_zoom ) {
					g_geocoder_browser_set_center_map = this;
				
					if ( GBrowserIsCompatible() )
						{
						/* This should never happen. */
						
						g_geocoder_browser_set_center_map.map = new GMap2(document.getElementById("meeting_map"), {draggableCursor: "crosshair"});
						if ( g_geocoder_browser_set_center_map.map )
							{
							g_geocoder_browser_set_center_map.map.addControl(new GLargeMapControl());
							g_geocoder_browser_set_center_map.map.addControl(new GMapTypeControl());
							
							point = new GLatLng ( in_lat, in_lng );
					
							g_geocoder_browser_set_center_map.map.setCenter(point, in_zoom);
							g_geocoder_browser_set_center_map.marker = new GMarker(point, {draggable: true, title: "Drag to a New Location."});
							GEvent.addListener(g_geocoder_browser_set_center_map.marker, "dragend", g_geocoder_browser_set_center_map.Dragend );
							GEvent.addListener(g_geocoder_browser_set_center_map.map, "zoomend", g_geocoder_browser_set_center_map.Zoomend );
							GEvent.addListener(g_geocoder_browser_set_center_map.map, "click", g_geocoder_browser_set_center_map.MapClickCallback );
							g_geocoder_browser_set_center_map.map.addOverlay(g_geocoder_browser_set_center_map.marker);
							g_geocoder_browser_set_center_map.Dragend ();
							};
						};
				};
				
				/*******************************************************************/
				/** \brief	
				*/
				c_geocoder_browser_set_center.prototype.Zoomend = function ( in_old_zoom, in_new_zoom )
				{
					document.getElementById('bmlt_map_zoom').value = in_new_zoom;
				};
				
				/*******************************************************************/
				/** \brief	
				*/
				c_geocoder_browser_set_center.prototype.Dragend = function (  )
				{
					point = g_geocoder_browser_set_center_map.marker.getLatLng();
					document.getElementById('bmlt_map_center_latitude').value = point.lat();
					document.getElementById('bmlt_map_center_longitude').value = point.lng();
					document.getElementById('bmlt_map_zoom').value = g_geocoder_browser_set_center_map.map.getZoom();
				};
				
				/*******************************************************************/
				/** \brief	Clicking in the map simulates a very fast drag.
				*/
				c_geocoder_browser_set_center.prototype.MapClickCallback = function ( in_overlay, in_point )
				{
					g_geocoder_browser_set_center_map.marker.setLatLng (in_point );
					g_geocoder_browser_set_center_map.Dragend();
				};
				
				new c_geocoder_browser_set_center ( <?php echo $search_spec_map_center['latitude'] ?>, <?php echo $search_spec_map_center['longitude'] ?>, <?php echo $search_spec_map_center['zoom'] ?> );
			/* ]]> */
			</script>
<?php
				}
			else
				{
?>
			<input type="hidden" value="31" name="wizard_page" />
			<input type="hidden" value="40" name="latitude" />
			<input type="hidden" value="-102" name="longitude" />
			<input type="hidden" value="5" name="zoom" />
			<p>Enter the Google Maps API key here:</p>
			<p class="text_input"><label class="form_prompt" for="initial_field">Google Maps API Key:</label><span class="form_text_item"><input size="90" id="initial_field" name="gkey_text" type="text" />-</span><span class="side_note">(You must get this from <a href="http://code.google.com/apis/maps/signup.html">Google</a>. Use <em>&quot;<?php echo htmlspecialchars ( 'http://'.$_SERVER['SERVER_NAME'] ) ?>&quot;</em> as the URL).</span></p>
					
			<p>If there is <a href="http://code.google.com/apis/maps/documentation/geocoding/#RegionCodes">a region bias code</a>, enter it here:</p>
			<p class="text_input"><label class="form_prompt" for="initial_field">Region Bias Code:</label><span class="form_text_item"><input size="5" id="region_bias" name="region_bias" type="text" />-</span><span class="side_note">(For the US, leave this blank).</span></p>
			<p class="submit_button"><input type="submit" value="Set Up The Map" /></p>
<?php
				}
?>
				</div></form>
			</div>
		</div>
<?php
		}
}

function PageFour ( )
{
	global $config_file_path;
	
	if ( file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
		
		if ( !isset ( $results_per_page ) || !$results_per_page )
			{
			$results_per_page = 30;
			}
		
		if ( !isset ( $number_of_meetings_for_auto ) || !$number_of_meetings_for_auto )
			{
			$number_of_meetings_for_auto = 10;
			}
		
		if ( !isset ( $default_basic_search ) || !$default_basic_search )
			{
			$default_basic_search = 'text';
			}
		
		if ( !isset ( $change_depth_for_meetings ) || !$change_depth_for_meetings )
			{
			$change_depth_for_meetings = 5;
			}
?>
		<div class="bmlt_installer_page_div page_2">
			<div class="intro">
				<p class="first">Now, we have various miscellaneous settings that need to be chosen.</p>
			</div>
			<div class="guide">
				<form action ="<?php echo htmlspecialchars ( $_SERVER['PHP_SELF'] ) ?>" method="post"><div>
					<input type="hidden" value="5" name="wizard_page" />
					<p class="text_input">
						<label class="form_prompt" for="results_per_page">Meetings Per Page in List:</label>
						<select name="results_per_page" id="results_per_page">
							<?php
								for ( $c = 10; $c < 101; $c += 10 )
									{
									echo '<option value="'.$c.'"';
									
									if ( $c == $results_per_page )
										{
										echo ' selected="selected"';
										}
									echo '>'.$c.'</option>';
									}
							?>
						</select>
						<span class="side_note">The more meetings, the longer the list, and fewer pages. However, some satellite servers can have problems with long lists.</span>
					</p>
					<p class="text_input">
						<label class="form_prompt" for="number_of_meetings_for_auto">Auto Select Target:</label>
						<select name="number_of_meetings_for_auto" id="number_of_meetings_for_auto">
							<?php
								for ( $c = 5; $c < 31; $c += 5 )
									{
									echo '<option value="'.$c.'"';
									
									if ( $c == $number_of_meetings_for_auto )
										{
										echo ' selected="selected"';
										}
									echo '>'.$c.'</option>';
									}
							?>
						</select>
						<span class="side_note">The automatic selection of search radius looks for a rough number of meetings.</span>
					</p>
					<p class="text_input">
						<label class="form_prompt" for="default_basic_search">Default for Basic Search:</label>
						<select name="default_basic_search" id="default_basic_search">
							<option value="text"<?php if ( $default_basic_search == 'text' ) echo ' selected="selected"'; ?>>Text</option>
							<option value="map"<?php if ( $default_basic_search == 'map' ) echo ' selected="selected"'; ?>>Map</option>
						</select>
						<span class="side_note">This is the view that the Basic Search starts with.</span>
					</p>
					<p class="text_input">
						<label class="form_prompt" for="default_sort_key">Default Sort for Lists:</label>
						<select name="default_sort_key" id="default_sort_key">
							<option value="weekday"<?php if ( $default_sort_key == 'weekday' ) echo ' selected="selected"'; ?>>Weekday (Day of Week, then Town)</option>
							<option value="time"<?php if ( $default_sort_key == 'time' ) echo ' selected="selected"'; ?>>Time (Day of Week, then Start Time)</option>
							<option value="town"<?php if ( $default_sort_key == 'town' ) echo ' selected="selected"'; ?>>Town (Town, then Day of Week)</option>
						</select>
						<span class="side_note">This is the Default sort for list results.</span>
					</p>
					<p class="text_input">
						<label class="form_prompt" for="change_depth_for_meetings">The Number of Changes Saved:</label>
						<select name="change_depth_for_meetings" id="change_depth_for_meetings">
							<?php
								for ( $c = 1; $c < 11; $c++ )
									{
									echo '<option value="'.$c.'"';
									
									if ( $c == $change_depth_for_meetings )
										{
										echo ' selected="selected"';
										}
									echo '>'.$c.'</option>';
									}
							?>
						</select>
						<span class="side_note">The more changes that are saved, the further back you go, but it can cause tremendous strain on the database.</span>
					</p>
					<p class="text_input"><input type="checkbox" value="true" name="allow_contact_form" id="allow_contact_form"<?php if ( isset ( $allow_contact_form ) && $allow_contact_form ) echo ' checked="checked"'; ?> />
						<label class="checkbox_label" for="allow_contact_form">Allow Users to Contact Administrators from the Meeting List (Adds a &quot;Contact Us&quot; form to the meeting details page).</label>
					</p>
					<p class="text_input"><input type="checkbox" value="true" name="recursive_contact_form" id="recursive_contact_form"<?php if ( isset ( $recursive_contact_form ) && $recursive_contact_form ) echo ' checked="checked"'; ?> />
						<label class="checkbox_label" for="recursive_contact_form">If contacts are allowed, let them &quot;percolate&quot; to the Responsible Service Body, if no contact is given for the direct meeting Service Body.</label>
					</p>
					<p class="text_input"><input type="checkbox" value="true" name="allow_pdf_downloads" id="allow_pdf_downloads"<?php if ( isset ( $allow_pdf_downloads ) && $allow_pdf_downloads ) echo ' checked="checked"'; ?> />
						<label class="checkbox_label" for="allow_pdf_downloads">Allow Users to Download &quot;on the Fly&quot; PDF Files from the Advanced Search of the Standalone Satellite.</label>
					</p>
					<p class="text_input"><label class="form_prompt" for="banner_text">Admin Login Screen Banner:</label><span class="form_text_item"><input size="90" id="banner_text" name="banner_text" type="text" value="<?php echo htmlspecialchars ( $banner_text ) ?>" />-</span><span class="side_note">(Leave this blank for no banner above the admin login).</span></p>
					<p class="text_input">
						<label class="form_prompt" for="distance_units">The Units for Distance Measurements:</label>
						<select name="distance_units" id="distance_units">
						    <option value="mi" selected="selected">Miles</option>
						    <option value="km">Kilometers</option>
						</select>
					</p>
					<p class="text_input" title="If you select this, then the descriptions for Service bodies will be displayed all the time. If not, they will be displayed as tooltip text (rollover text).">
						<label class="form_prompt" for="comdef_show_sb_desc">Show Service Body Text in Search:</label>
						<select name="comdef_show_sb_desc" id="comdef_show_sb_desc">
						    <option value="" selected="selected">Show Only On MouseOver (Abbreviation)</option>
						    <option value="true">Show All the Time, as text, under the Service Body Name.</option>
						</select>
					</p>

					<input type="hidden" value="asc" name="default_sort_dir" />
					<input type="hidden" value="600" name="static_map_size_x" />
					<input type="hidden" value="600" name="static_map_size_y" />
					<input type="hidden" value="g:i A" name="time_format" />
					<input type="hidden" value="g:i A, n/j/Y" name="change_date_format" />
					
					<p>This will finalize the construction of the <?php echo htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ) ?> file. After this, you will need to change the permissions on that file to 0644 (non-writable).</p>
					<p class="submit_button"><input type="submit" value="Go on to the last step" /></p>
				</div></form>
			</div>
		</div>
<?php
		}
}

function PageFive ( )
{
	global $config_file_path;
	
	if ( file_exists ( $config_file_path ) )
		{
		include ( $config_file_path );
?>
		<div class="bmlt_installer_page_div page_2">
			<div class="intro">
				<p class="first">Okay, we've set up the autoconfig file. Now, we need to set up a minimal default database, and create the Server Administrator user account.</p>
			</div>
			<div class="guide">
<?php
		// Try to chmod the file to an unwritable state. Might not work.
		@chmod ( $config_file_path, 0644 );
	
		if ( (fileperms ( $config_file_path ) & 0x0002) == 0x0002 )
			{
			echo '<p class="error_disp">The '.htmlspecialchars ( dirname ( __FILE__ )."/auto-config.inc.php" ).' file is still writeable! You have finished setting it up, and security requires that it be set to read-only! (chmod 0644)</p>';
			echo '<p class="submit_button"><a href="'.htmlspecialchars ( $_SERVER['PHP_SELF'] ).'?wizard_page=51">Try Again</a></p>';
			}
		else
			{
?>
				<form action ="<?php echo htmlspecialchars ( $_SERVER['PHP_SELF'] ) ?>" onsubmit="return confirm ('If you do this, you will wipe out any original data in the database, and set the database to a new, empty database. Are you sure that you want to continue?')" method="post"><div>
					<input type="hidden" value="6" name="wizard_page" />
					<p class="first">Select a Server Administrator Login ID and Password. The Server Administrator user account will be created when we initialize the database.</p>
					<p class="text_input"><label class="form_prompt" for="initial_field">Login ID:</label><span class="form_text_item"><input id="initial_field" name="admin_user" type="text" value="admin" />-</span><span class="side_note">(This is the login ID. The initial name will be &quot;Server Administrator&quot;).</span></p>
					<p class="text_input"><label class="form_prompt" for="admin_password">Password:</label><span class="form_text_item"><input id="admin_password" name="admin_password" type="text" />-</span><span class="side_note">(Pick a difficult one).</span></p>
					<p class="submit_button"><input type="submit" value="Finalize the Process" /></p>
				</div></form>
			</div>
		</div>
<?php
			}
		}
}

function PageSix ( )
{
?>
		<div class="bmlt_installer_page_div page_2">
			<div class="intro">
				<h2 class="centered">Congratulations! You have successfully set up your root server!</h2>
			</div>
			<div class="guide">
				<p class="first"><strong>IMPORTANT:</strong> You have been set up with an initial Service Body Administrator User, and an initial Service Body. The user has the same password as the Server Administrator, and has a login ID of 'service_body_admin'. The Service Body is a generic Regional Service Body that has the Service Body Administrator as its principal editor. You will probably want to change these.</p>
				<p class="centered"><a href="index.php">Go to your new server.</a></p>
			</div>
		</div>
<?php
}
?>
