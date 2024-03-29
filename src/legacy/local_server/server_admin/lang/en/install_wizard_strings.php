<?php
/***********************************************************************/
/** This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.*/
    defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.
    
    global $comdef_install_wizard_strings;
    
    $comdef_install_wizard_strings = array (
                                            'Database_Version_Error'        =>  'ERROR: You must have PHP Version 5.6 or greater installed on this server!',
                                            'Database_PDO_Error'            =>  'ERROR: You do not have PHP PDO installed!',
                                            'Database_Type_Error'           =>  'ERROR: Even though you have PDO, you have no database drivers installed!',
                                            'Database_Type_MySQL_Error'     =>  'ERROR: Even though you have PDO and you have database drivers installed, none of the are MySQL (the only supported driver)!',
                                            'Database_TestButton_Text'      =>  'TEST',
                                            'Database_TestButton_Success'   =>  'The database connection was successful.',
                                            'Database_TestButton_Fail'      =>  'The database connection failed: ',
                                            'Database_TestButton_Fail2'     =>  'The database connection failed because there is already an initialized database.',
                                            'Database_Whitespace_Note'      =>  'Warning: %s has whitespace at the beginning or end.',

                                            'AJAX_Handler_DB_Connect_Error' =>  'The database connection failed! Please make sure that the database exists, IS COMPLETELY EMPTY, the user is created, and that user has full permissions on the empty database.',
                                            'AJAX_Handler_DB_Established_Error' =>  'The database already exists, and has been set up! You cannot use this setup to overwrite an existing database!',
                                            'AJAX_Handler_DB_Incomplete_Error'  =>  'There is not enough information to initialize the database!',

                                            'NoDatabase_Note_AlreadySet'    =>  'The database has already been initialized with the provided table prefix. Please choose a new one.',
                                            'NoDatabase_Note_GenericError'  =>  'There is an error connecting to the database. Please check your database settings.',
                                            'NoDatabase_Note_ClickHere'     =>  'Click here to go back to the database set up page.',
                                            'NoDatabase_Note_PasswordIssue' =>  'You must choose a username and password for the Server Administrator user.',
                                            'NoDatabase_Note_ServerSettings_ClickHere' => 'Click here to go back to the server settings page.',
                                            'NoServerAdmin_Note_AlreadySet' =>  'There is already an existing database, so you cannot set up a Server Administrator account (One already exists).',
                                            'NeedLongerPasswordNote'        =>  'This password is too short. It must be at least %d characters long.',
                                            
                                            'Prev_Button'                   =>  'PREVIOUS',
                                            'Next_Button'                   =>  'NEXT',

                                            'Page_1_Tab'                    =>  'STEP 1: Database',
                                            'Page_1_Heading'                =>  'Database Connection Settings',
                                            'Page_1_Text'                   =>  'Before you can apply the settings on this page, you must set up a new COMPLETELY EMPTY database, and create a database user that has full user rights on that database.',
                                            
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
                                            'Database_PW_Additional_Text'   =>  'Make this an ugly, difficult password. It has a great deal of power, and you will never need to remember it.',

                                            'Maps_API_Key_Warning'          =>  'There is a problem with the Google Maps API Key.',
                                            'Maps_API_Key_Not_Set'          =>  'The Google Maps API key is not set.',
                                            'Maps_API_Key_Valid'            =>  'Google Maps API Key is valid.',
                                            'Maps_API_Key_ClickHere'        =>  'Click here to go back to the Google Maps API Key set up page.',
                                            
                                            'Page_2_Tab'                    =>  'STEP 2: Google Maps API Settings',
                                            'Page_2_Heading'                =>  'Google Maps API Settings',
                                            'Page_2_API_Key_Prompt'         =>  'Enter the Google API Key for Geocoding:',
                                            'Page_2_API_Key_Set_Button'     =>  'TEST KEY',
                                            'Page_2_API_Key_Not_Set_Prompt' =>  'SET API KEY FIRST',
                                            'Page_2_Text'                   =>  'When saving a meeting, the BMLT Root Server uses the Google Maps API to determine the latitude and longitude for the meeting address. These settings are required to allow the BMLT Root Server to communicate with the Google Maps API.',

                                            'Page_3_Tab'                    =>  'STEP 3: Server Settings',
                                            'Page_3_Heading'                =>  'Set Various Global Server Settings',
                                            'Page_3_Text'                   =>  'These are a few settings that affect the administration and general configuration of this server. Most server settings are done in the server itself.',
                                            'Admin_Login'                   =>  'Server Administrator Login:',
                                            'Admin_Login_Default_Text'      =>  'Enter A Server Administrator Login',
                                            'Admin_Login_Additional_Text'   =>  'This is the login string for the Server Administrator.',
                                            'Admin_Password'                =>  'Server Administrator Password:',
                                            'Admin_Password_Default_Text'   =>  'Enter A Server Administrator Password',
                                            'Admin_Password_Additional_Text'    =>  'Make sure that this is a non-trivial password! It has a great deal of power! (Also, don\'t forget it).',
                                            'ServerAdminName'               =>  'Server Administrator',
                                            'ServerAdminDesc'               =>  'Main Server Administrator',
                                            'ServerLangLabel'               =>  'Default Server Language:',
                                            'DistanceUnitsLabel'            =>  'Distance Units:',
                                            'DistanceUnitsMiles'            =>  'Miles',
                                            'DistanceUnitsKM'               =>  'Kilometres',
                                            'SearchDepthLabel'              =>  'Density of Meetings For Automatic Search:',
                                            'SearchDepthText'               =>  'This is an approximation of how many meetings need to be found in the automatic radius selection. More meetings means a bigger radius.',
                                            'HistoryDepthLabel'             =>  'How Many Meeting Changes To Save:',
                                            'HistoryDepthText'              =>  ' The longer the history, the larger the database will become.',
                                            'TitleTextLabel'                =>  'The Title Of The Administration Screen:',
                                            'TitleTextDefaultText'          =>  'Enter A Short Title For the Editing Login Page',
                                            'BannerTextLabel'               =>  'Prompt For Administration Login:',
                                            'BannerTextDefaultText'         =>  'Enter A Short Prompt For The Login Page',
                                            'RegionBiasLabel'               =>  'Region Bias:',
                                            'PasswordLengthLabel'           =>  'Minimum Password Length:',
                                            'PasswordLengthExtraText'       =>  'This will also affect the Server Administrator password, above.',
                                            'DefaultClosedStatus'           =>  'Meetings Are Considerd "CLOSED" by Default:',
                                            'DefaultClosedStatusExtraText'  =>  'This primarily affects the export to NAWS.',
                                            'DurationLabel'                 =>  'Default Meeting Duration:',
                                            'DurationHourLabel'             =>  'Hours',
                                            'DurationMinutesLabel'          =>  'Minutes',
                                            'LanguageSelectorEnableLabel'   =>  'Display Language Selector On Login:',
                                            'LanguageSelectorEnableExtraText'   =>  'If you select this, a popup menu will appear in the login screen, so administrators can select their language.',
                                            'SemanticAdminLabel'            =>  'Enable Semantic Administration:',
                                            'SemanticAdminExtraText'        =>  'If not checked, then all administration must be done via the Root Server login (No apps).',
                                            'EmailContactEnableLabel'       =>  'Allow Email Contacts From Meetings:',
                                            'EmailContactEnableExtraText'   =>  'If you select this, site visitors will be able to send emails from meeting records.',
                                            'EmailContactAdminEnableLabel'      =>  'Include Service Body Administrator On These Emails:',
                                            'EmailContactAdminEnableExtraText'  =>  'Sends copies of these emails to the Service Body Administrator (if they are not the primary recipient).',
                                            'EmailContactAllAdminEnableLabel'       =>  'Include All Service Body Administrators On These Emails:',
                                            'EmailContactAllAdminEnableExtraText'   =>  'Sends copies of these emails to all of the relevant Service Body Administrators.',

                                            'Page_4_Initialize_Root_Server_Heading' => 'Initialize the Root Server',
                                            'Page_4_Initialize_Root_Server_Text'    => 'The button below will initialize the Root Server with an empty database and a Server Administrator user.',
                                            'Page_4_Initialize_Root_Server_Button'  => 'Initialize Root Server',

                                            'Page_4_Tab'                    =>  'STEP 4: Save The Settings',
                                            'Page_4_Heading'                =>  'Create the Settings File',
                                            'Page_4_Text'                   =>  'The root server was unable to create the settings file for you. Instead, we ask you to create it yourself, via FTP or a control panel file manager, name it "auto-config.inc.php", and paste the following text into the file:',

                                            'NAWS_Export_Spreadsheet_Optional' => 'NAWS Export Spreadsheet (Optional): ',
                                            'NAWS_Export_Spreadsheet_Initially_Publish' => 'Initialize imported meetings to \'published\': ',
                                            'DefaultPasswordLength'         =>  10,
                                            'DefaultMeetingCount'           =>  10,
                                            'DefaultChangeDepth'            =>  5,
                                            'DefaultDistanceUnits'          =>  'mi',
                                            'DefaultDurationTime'           =>  '01:30:00',
                                            'DurationTextInitialText'       =>  'N.A. Meetings are usually 90 minutes long (an hour and a half), unless otherwise indicated.',
                                            'time_format'                   =>  'g:i A',
                                            'change_date_format'            =>  'g:i A, n/j/Y',
                                            'BannerTextInitialText'         =>  'Administration Login',
                                            'TitleTextInitialText'          =>  'Basic Meeting List Toolbox Administration',
                                            'DefaultRegionBias'             =>  'us',
                                            'search_spec_map_center'        =>  array ( 'longitude' => -118.563659, 'latitude' => 34.235918, 'zoom' => 6 ),
                                            'DistanceChoices'               =>  array ( 2, 5, 10, 20, 50 ),
                                            'HistoryChoices'                =>  array ( 1, 2, 3, 5, 8, 10, 15 ),
                                            'PW_LengthChices'               =>  array ( 6, 8, 10, 12, 16 ),
                                            'ServerAdminDefaultLogin'       =>  'serveradmin',
                                            
                                            'Explanatory_Text_1_Initial_Intro'  =>  'This install wizard will guide you through the process of creating an initial database, as well as a configuration file. In the final step, we will create a settings file, and initialize an empty database.',
                                            'Explanatory_Text_1_DB_Intro'       =>  'The first thing that you need to do, is create a new, EMPTY database, and a database user that has full access to that database. This is usually done via your Web site Control Panel. Once you have created the database, you need to enter the information about that database into the text items on this page.',

                                            'Explanatory_Text_2_Region_Bias_Intro'  =>  'The "Region Bias" is a code that is sent to Google when a location search is done, and can help Google to make sense of ambiguous search queries.',
                                            'Explanatory_Text_2_API_key_Intro'      =>  'The "API Key" is a key that <a target="_blank" title="Follow this link to go to a page that discusses the Google API Key." href="https://bmlt.app/google-api-key/">you need to register with Google</a> in order to be able to use their mapping service.',
                                            'Explanatory_Text_2_API_key_2_Intro'    =>  'You will need to provide a valid API Key in order to create new meetings in the Root Server.',

                                            'Explanatory_Text_3_Server_Admin_Intro' =>  'The Server Administrator is the main user for the server. It is the only account that can create new users and Service bodies, and is very powerful. You should create a login ID and a non-trivial password for this account. You\'ll be able to modify the other aspects of the account on the main server, once the database has been set up.',
                                            'Explanatory_Text_3_Misc_Intro'     =>  'These are various settings that affect how the root server behaves and appears.',
                                            
                                            'Explanatory_Text_4_Main_Intro'     =>  'If you have entered the database information, provided a valid Google Maps API Key, and specified the login information for the Server Administrator, then you can initialize the root server here. Remember that the database must be COMPLETELY EMPTY of BMLT Root Server tables for this server (It can have tables for other servers or services).',
                                            'Explanatory_Text_4_NAWS_Export'    =>  'Optionally, you can import the meetings from a NAWS export spreadsheet. Uncheck the box to initialize them to \'unpublished\'. (This is useful if many of the new meetings will need to be edited or deleted, and you don\'t want them showing up in the meantime.)',
                                            'Explanatory_Text_4_File_Intro'     =>  'The text in the box below is the PHP source code for the main settings file. You will need to create a file on the server with this text in it. The file is at the same level as the main server directory for the root server.',
                                            'Explanatory_Text_4_File_Extra'     =>  'You also need to make sure that the file permissions are restricted (chmod 0644). This prevents the file from being written, and the root server will not run unless the file has the correct permissions.',
                                            'Page_4_PathInfo'                   =>  'The file needs to be placed as %s/auto-config.inc.php, which is where your %s directory is. After the file has been created and you have put the above text into it, you should execute the following command to make sure that the permissions are correct:',
                                            'Page_4_Final'                      =>  'Once all this is complete, refresh this page, and you should see the root server login page.',
                                            'FormatLangNamesLabel'              =>  'Enter extra languages in format code1:name1 (example "fa:farsi ru:russian"):',
                                        );
