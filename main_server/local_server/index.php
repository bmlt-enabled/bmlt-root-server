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

global  $http_vars;
$http_vars = array_merge_recursive ( $_GET, $_POST );

require_once ( dirname ( __FILE__ ).'/install_wizard/index.php' );  // We test for the install wizard, first.

if ( isset ( $http_vars ['bmlt_ajax_callback'] ) )
    {
    require_once ( dirname ( __FILE__ ).'/server_admin/c_comdef_admin_ajax_handler.class.php');
    }
else
    {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
<?php
            $config_file_path = dirname ( __FILE__ ).'/../server/config/get-config.php';
            $url_path = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] );
            if ( file_exists ( $config_file_path ) )
                {
                include ( $config_file_path );
                }

            $shortcut_icon = "$url_path/local_server/server_admin/style/images/shortcut.png";
            $stylesheet = "$url_path/local_server/server_admin/style".( defined( '__DEBUG_MODE__' ) ? '/' : '/style_stripper.php?filename=' )."styles.css";
            
            require_once ( dirname ( __FILE__ ).'/../server/shared/classes/comdef_utilityclasses.inc.php');
            require_once ( dirname ( __FILE__ ).'/../server/c_comdef_server.class.php');
            
            DB_Connect_and_Upgrade ( );

            $server = c_comdef_server::MakeServer();
?>
		<link rel="stylesheet" href="<?php echo c_comdef_htmlspecialchars ( $stylesheet ) ?>" />
		<link rel="icon" href="<?php echo c_comdef_htmlspecialchars ( $shortcut_icon ) ?>" />
		<title>Basic Meeting List Toolbox Administration Console</title>
	</head>
	<body class="admin_body">
<?php
        if ( $server instanceof c_comdef_server )
            {
            // This throws up a tackle if someone wants to just barge in.
            require_once ( dirname ( __FILE__ ).'/server_admin/c_comdef_login.php');
            
            // We can only go past here is we are a logged-in user.
            $user_obj = $server->GetCurrentUserObj();
            if ( ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) )
                {
                echo '<div class="admin_page_wrapper">';
                // OK. If they make it in here, it means they are legit, so display the logged-in console.
                require_once ( dirname ( __FILE__ ).'/server_admin/main_console.php');
                echo '</div>';
                }
	        }
	    else
	        {
?>
	    <h1>ERROR: NO SERVER!</h1>
<?php
	        }
?>
	</body>
</html>
<?php
    }
/**
	\brief This function checks to make sure the database is correct for the current version.
*/
function DB_Connect_and_Upgrade ( )
{
	include ( dirname ( __FILE__ )."/../server/config//get-config.php" );
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
}?>