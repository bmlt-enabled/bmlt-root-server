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
	                                        'Database_Version_Error'        =>  'ERROR: You must have PHP Version 5.1 or greater installed on this server!',
	                                        'Database_PDO_Error'            =>  'ERROR: You do not have PHP PDO installed!',
	                                        'Database_Type_Error'           =>  'ERROR: Even though you have PDO, you have no database drivers installed!',

                                            'AJAX_Handler_DB_Connect_Error' =>  'The database connection failed! Please make sure that the database exists, IS COMPLETELY EMPTY, the user is created, and that user has full permissions on the empty database.',
                                            'AJAX_Handler_DB_Established_Error' =>  'The database already esists, and has been set up! You cannot use this setup to overwrite an existing database!',
                                            'AJAX_Handler_DB_Incomplete_Error'  =>  'There is not enough information to initialize the database!',
                                            
	                                        'Prev_Button'                   =>  'PREVIOUS',
	                                        'Next_Button'                   =>  'NEXT',

	                                        'Page_1_Tab'                    =>  'STEP 1: Database',
	                                        'Page_1_Heading'                =>  'Database Connection Settings',
	                                        'Page_1_Text'                   =>  'Before you can apply the settings on this page, you must set up a new COMPLETELY EMPTY database, and establish a database user that has full user rights on that database.',
	                                        
	                                        'Database_Name'                 =>  'Database Name:',
	                                        'Database_Name_Default_Text'    =>  'Enter A Database Name',
	                                        'Database_Type'                 =>  'Database Type:',
	                                        'Database_Host'                 =>  'Database Host:',
	                                        'Database_Host_Default_Text'    =>  'Enter A Database Host',
	                                        'Database_Host_Additional_Text' =>  'This is usually "localhost."',
	                                        'Table_Prefix'                  =>  'Table Prefix:',
	                                        'Table_Prefix_Default_Text'     =>  'Enter A Table Prefix',
	                                        'Table_Prefix_Additional_Text'  =>  'Only for multiple root servers sharing a database.',
	                                        'Database_User'                 =>  'Database User:',
	                                        'Database_User_Default_Text'    =>  'Enter A Database User Name',
	                                        'Database_PW'                   =>  'Database Password:',
	                                        'Database_PW_Default_Text'      =>  'Enter A Database Password',
	                                        
	                                        'Page_2_Tab'                    =>  'STEP 2: Default Location',
	                                        'Page_2_Heading'                =>  'Set The Initial Location For Meetings',
	                                        'Page_2_Text'                   =>  'When a new meeting is created, this is its initial map location, so you should choose a location that is central in your covered region.',

	                                        'Page_3_Tab'                    =>  'STEP 3: Server Settings',
	                                        'Page_3_Heading'                =>  'Set Various Global Server Settings',
	                                        'Page_3_Text'                   =>  'These are a few settings that affect the administration and general configuration of this server. Most server settings are done in the server itself.',
	                                        'Admin_Login'                   =>  'Server Administrator Login:',
	                                        'Admin_Login_Default_Text'      =>  'Enter A Server Administrator Login',
	                                        'Admin_Login_Additional_Text'   =>  'This is the login string for the Server Administrator.',
	                                        'Admin_Password'                =>  'Server Administrator Password:',
	                                        'Admin_Password_Default_Text'   =>  'Enter A Server Administrator Password',
	                                        'Admin_Password_Additional_Text'    =>  'Make sure that this is a non-trivial password! It has a great deal of power! (Also, don\' forget it).',
                                            'ServerAdminName'               =>  'Server Administrator',
                                            'ServerAdminDesc'               =>  'Main Server Administrator',
                                            'ServerLangLabel'               =>  'Default Server Language:',
                                            'DistanceUnitsLabel'            =>  'Distance Units:',
                                            'DistanceUnitsMiles'            =>  'Miles',
                                            'DistanceUnitsKM'               =>  'Kilometres',
                                            'SearchDepthLabel'              =>  'Density of Meetings For Automatic Search:',
                                            'SearchDepthText'               =>  'This is how many meetings need to be found in the automatic radius selection. More meetings means a bigger radius.',
                                            'HistoryDepthLabel'             =>  'How Many Meeting Changes To Save:',
                                            'HistoryDepthText'              =>  'The longer the history, the larger the database will become.',
                                            'TitleTextLabel'                =>  'The Title Of The Administration Screen:',
                                            'TitleTextDefaultText'          =>  'Enter A Short Title For the Editing Login Page',
                                            'BannerTextLabel'               =>  'Prompt For Administration Login:',
                                            'BannerTextDefaultText'         =>  'Enter A Short Prompt For The Login Page',
                                            'RegionBiasLabel'               =>  'Region Bias:',
                                            'PasswordLengthLabel'           =>  'Minimum Password Length:',
                                            
	                                        'Page_4_Tab'                    =>  'STEP 4: Save The Settings',
	                                        'Page_4_DB_Setup_Heading'       =>  'Initialize A New Database',
	                                        'Page_4_DB_Setup_Text'          =>  'The button below will create a new, initialized database with default formats, no Service bodies and a Server Administrator user.',
	                                        'Set_Up_Database'               =>  'Initialize Database',
	                                        'Page_4_Heading'                =>  'Create the Settings File',
	                                        'Page_4_Text'                   =>  'Due to security concerns (Yeah, we\'re fairly paranoid -go figure), this program will not attempt to create or modify the settings file. Instead, we ask you to create it yourself, via FTP or a control panel file manager, name it "auto-config.inc.php", and paste the following text into the file:',
                                            
                                            'DefaultDistanceUnits'          =>  'mi',
                                            'DurationTextInitialText'       =>  'N.A. Meetings are usually 90 minutes long (an hour and a half), unless otherwise indicated.',
                                            'time_format'                   =>  'g:i A',
                                            'change_date_format'            =>  'g:i A, n/j/Y',
                                            'BannerTextInitialText'         =>  'Administration Login',
                                            'TitleTextInitialText'          =>  'Basic Meeting List Toolbox Administration',
                                            'DefaultRegionBias'             =>  'us',
                                            'search_spec_map_center'        =>  array ( 'longitude' => -118.563659, 'latitude' => 34.235918, 'zoom' => 6 ),
	                                        'DistanceChoices'               =>  array ( 2, 5, 10, 20, 50 ),
	                                        'HistoryChoices'                =>  array ( 0, 1, 5, 10 ),
                                            'ServerAdminDefaultLogin'       =>  'serveradmin',
	                                        );
?>