<?php
/***********************************************************************/
/** This file is part of the Basic Meeting List Toolbox (BMLT).
    
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
    along with this code.  If not, see <http://www.gnu.org/licenses/>.*/
	defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.
	
	global $comdef_install_wizard_strings;
	
	$comdef_install_wizard_strings = array (
	                                        'Database_Setup'                =>  'Database Setup',
	                                        'Map_Setup'                     =>  'Map Setup',
	                                        'User_Setup'                    =>  'User Setup',
	                                        
	                                        'Database_Name'                 =>  'Database Name:',
	                                        'Database_Name_Default_Text'    =>  'Enter A Database Name',
	                                        'Database_Type'                 =>  'Database Type:',
	                                        'Database_Version_Error'        =>  'ERROR: You must have PHP Version 5.1 or greater installed on this server!',
	                                        'Database_PDO_Error'            =>  'ERROR: You do not have PHP PDO installed!',
	                                        'Database_Type_Error'           =>  'ERROR: Even though you have PDO, you have no database drivers installed!',
	                                        'Database_Host'                 =>  'Database Host:',
	                                        'Database_Host_Default_Text'    =>  'Enter A Database Host',
	                                        'Database_User'                 =>  'Database User:',
	                                        'Database_User_Default_Text'    =>  'Enter A Database User Name',
	                                        'Database_PW'                   =>  'Database Password:',
	                                        'Database_PW_Default_Text'      =>  'Enter A Database Password',
	                                        'Table_Prefix'                  =>  'Table Prefix:',
	                                        'Table_Prefix_Default_Text'     =>  'Enter A Table Prefix',
	                                        );
?>