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
$report = '';

defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );    // Makes sure that this file is in the correct context.
// This contains the PDO database access stuff.
require_once ( dirname ( __FILE__ ).'/../../server/classes/c_comdef_dbsingleton.class.php' );

// We do everything we can to ensure that the requested language file is loaded.
if ( file_exists ( dirname ( __FILE__ ).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php' ) )
    {
    require_once ( dirname ( __FILE__ ).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php' );
    }
else
    {
    require_once ( dirname ( __FILE__ ).'/../server_admin/lang/en/install_wizard_strings.php' );
    }

if (    isset ( $http_vars['ajax_req'] ) && ($http_vars['ajax_req'] == 'initialize_db')
    &&  isset ( $http_vars['dbName'] ) && $http_vars['dbName']
    &&  isset ( $http_vars['dbUser'] ) && $http_vars['dbUser']
    &&  isset ( $http_vars['dbPassword'] ) && $http_vars['dbPassword']
    &&  isset ( $http_vars['dbType'] ) && $http_vars['dbType']
    &&  isset ( $http_vars['dbServer'] ) && $http_vars['dbServer']
    &&  isset ( $http_vars['dbPrefix'] ) && $http_vars['dbPrefix']
    &&  isset ( $http_vars['admin_login'] ) && $http_vars['admin_login']
    &&  isset ( $http_vars['admin_password'] ) && $http_vars['admin_password']  // This is cleartext, but that can't be helped. This is the only place in the installer where this happens.
     )
    {
    $sql_query = str_replace ( '%%PREFIX%%', $http_vars['dbPrefix'], file_get_contents ( dirname ( __FILE__ ).'/InitialSQL.sql' ) );
    
    // We now have a straight-up SQL query with the prefix resolved. Next, do the server admin login:
    $sql_query = str_replace ( '%%admin_login%%', $http_vars['admin_login'], $sql_query );
    $sql_query = str_replace ( '%%admin_password%%', $http_vars['admin_password'], $sql_query );
    
    // Our SQL is now ready to be set to the server. We need to use PDO, as that is the abstraction mechanism used by the server.
    
    // First, we make sure that the database does not already exist. If so, we immediately fail, as we will not overwrite an existing database.
    
    try
        {
        // We connect the PDO layer:
        c_comdef_dbsingleton::init ( $http_vars['dbType'], $http_vars['dbServer'], $http_vars['dbName'], $http_vars['dbUser'], $http_vars['dbPassword'] );
    
		$result = c_comdef_dbsingleton::preparedQuery ( 'SELECT * FROM `'.$http_vars['dbPrefix'].'_comdef_users`', array() );
		
		$response = array ( 'status' => 'false', 'report' => '' );
		
		if ( isset ( $result ) && is_array ( $result ) && count ( $result ) )
		    {
		    $response['status'] = false;
            $response['report'] = $comdef_install_wizard_strings['AJAX_Handler_DB_Established_Error'];
		    }
		else
		    {
		    $response['status'] = true;
            c_comdef_dbsingleton::preparedExec ( $sql_query, array() );
            };
        
        echo htmlspecialchars ( array2json ( $response ) );
        }
    catch ( Exception $e )
        {
        $report = $comdef_install_wizard_strings['AJAX_Handler_DB_Connect_Error'];
        }
    }
elseif (    isset ( $http_vars['ajax_req'] ) && ($http_vars['ajax_req'] == 'test')
        &&  isset ( $http_vars['dbName'] ) && $http_vars['dbName']
        &&  isset ( $http_vars['dbUser'] ) && $http_vars['dbUser']
        &&  isset ( $http_vars['dbPassword'] ) && $http_vars['dbPassword']
        &&  isset ( $http_vars['dbType'] ) && $http_vars['dbType']
        &&  isset ( $http_vars['dbServer'] ) && $http_vars['dbServer']
        &&  isset ( $http_vars['dbPrefix'] ) && $http_vars['dbPrefix']
        )
    {
    try
        {
        c_comdef_dbsingleton::init ( $http_vars['dbType'], $http_vars['dbServer'], $http_vars['dbName'], $http_vars['dbUser'], $http_vars['dbPassword'] );
    
        // If we have an existing database, we return the word "false".
		$result = c_comdef_dbsingleton::preparedQuery ( 'SELECT * FROM `'.$http_vars['dbPrefix'].'_comdef_users`', array() );
		
		if ( isset ( $result ) && is_array ( $result ) && count ( $result ) )
		    {
		    echo 'false';
		    }
		else
		    {
		    echo 'true';
		    }
        }
    catch ( Exception $e )
        {
        $report = $comdef_install_wizard_strings['AJAX_Handler_DB_Connect_Error'];
        }
    }
elseif ( isset ( $http_vars['ajax_req'] ) && ($http_vars['ajax_req'] == 'test') )
    {
	echo 'false';
    }
die();  // Make sure stop here.