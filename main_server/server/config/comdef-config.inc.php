<?php
/***********************************************************************/
/** \file	comdef-config.inc.php
	\brief	The basic configuration file for a CoMDEF server.
*/
	defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.
	defined( 'BMLT_COOKIE' ) or define ( 'BMLT_COOKIE', 'bmlt_cookie' );	// The base name for the BMLT cookie.
	ini_set('display_errors', 0);	// Keep it all in the family.
	
	global	$comdef_global_language;				///< The local server language enum (Will default to English)
	global	$comdef_global_languages;				///< This is an array of all available languages.
	global	$comdef_global_more_details_address;	///< This is a format string for the way the address line is displayed in the "more details" screen.
	global	$comdef_global_list_address;			///< The same, but for the list.
			
	/// We default to English.
	$comdef_global_language = "en";
	$comdef_global_languages = array ();

	global	$_COMDEF_DEBUG;	///< This flag, when set to true, will cause extra debug information to be output.
	$_COMDEF_DEBUG = false;
	/** Uncomment this for debug mode. */
	// $_COMDEF_DEBUG = true;
	
	/** We scan through the "lang" directory, and pick out the available localizations.
		These will be kept in the comdef_global_languages array.
	*/
	if ( $handle = opendir ( dirname ( __FILE__ ).'/lang' ) )
		{
		while ( false !== ($file = readdir($handle)) )
			{
			if (!('.' == $file[0])
				&& is_dir ( dirname ( __FILE__ ).'/lang/'.$file )
				&& file_exists ( dirname ( __FILE__ ).'/lang/'.$file.'/name.txt' )
				&& file_exists ( dirname ( __FILE__ ).'/lang/'.$comdef_global_language.'/format_codes.php' ) )
				{
				/// The "name.txt" file contains -ONLY- the language name, in its local language.
				$name = file_get_contents ( dirname ( __FILE__ ).'/lang/'.$file.'/name.txt' );
				/// Each language has a text file, which has its name, in local language.
				$comdef_global_languages[$file] = $name;
				}
			}
		}
	
	$comdef_global_more_details_address = '@@%%location_text%%, @@%%location_street%%@@ (%%location_info%%)@@, %%location_city_subsection%%@@, %%location_municipality%%@@ (%%location_neighborhood%%)@@, %%location_province%%@@ %%location_postal_code_1%%';
	$comdef_global_list_address =  '@@%%location_text%%, @@%%location_street%% @@%%location_info%%';
	
	/// Include the automatically generated configuration.
	if ( file_exists ( dirname ( __FILE__ )."/auto-config.inc.php" ) )
		{
		include ( dirname ( __FILE__ )."/auto-config.inc.php" );
		}
	else
		{
		throw ( new Exception ( "No auto-config.inc.php file!" ) );
		}
?>