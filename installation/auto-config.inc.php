<?php

// This is a template for the file auto-config.inc.php -- see installation instructions for where this goes

// Leave this line as is (it makes sure that this file is used in the correct context)
defined('BMLT_EXEC') or die ('Cannot Execute Directly');

// SETTINGS THAT MUST BE UPDATED
$dbUser = 'USERNAME'; // This is the SQL user that is authorized for the above database.
$dbPassword = 'USERPASSWORD'; // This is the password for the above authorized user. Make it a big, ugly hairy one. It is powerful, and there is no need to remember it.
$gkey = 'a long string of random characters'; // This is the Google Maps JavaScript API Key, necessary for using Google Maps.
// $gkey is optional -- if you just omit it, the server will use OSM for maps and nominatim for geocoding.
// But if present, it must be a valid Google key.

// OTHER DATABASE SETTINGS (CHANGE IF NEEDED, BUT THE DEFAULTS MAY BE OK AS IS)
$dbType = 'mysql'; // This is the PHP PDO driver name for your database.
$dbName = 'rootserver'; // This is the name of the database.
$dbServer = 'localhost'; // This is the host/server for accessing the database.
$dbPrefix = 'na'; // This is a table name prefix that can be used to differentiate tables used by different root server instances that share the same database.

// OTHER SETTINGS FOLLOW (CHANGE IF NEEDED, BUT THE DEFAULTS MAY BE OK AS IS)

// Location and Map settings:
$region_bias = 'us'; // This is a 2-letter country code for a 'region bias,' which helps figure out ambiguous search queries.
// The default above is for the United States; other examples are 'au' (Australia), 'de' (Germany), 'fr' (France), 'jp' (Japan), etc.
$search_spec_map_center = array('longitude' => -118.563659, 'latitude' => 34.235918, 'zoom' => 6);
// The above coordinates are for the NAWS office in Los Angeles -- change as appropriate
$comdef_distance_units = 'mi'; // the other option is 'km'

// Display settings:
$bmlt_title = 'Basic Meeting List Toolbox Administration';
$banner_text = 'Administration Login';

// Miscellaneous settings:
$comdef_global_language = 'en'; // This is the 2-letter code for the default root server localization (will default to 'en' -English, if the localization is not available).
$min_pw_len = 10;   // The minimum number of characters in a user account password for this root server.
$number_of_meetings_for_auto = 10;   // This is an approximation of the number of meetings to search for in the auto-search feature. The higher the number, the wider the radius.
$change_depth_for_meetings = 5;   // This is how many changes should be recorded for each meeting. The higher the number, the larger the database will grow, as this can become quite substantial.";
$default_duration_time = '01:30'; // This is the default duration for meetings that have no duration specified.
$g_enable_language_selector = FALSE;   // Set this to TRUE (or 1) to enable a popup on the login screen that allows the administrator to select their language.
$g_enable_semantic_admin = TRUE;   // If this is TRUE (or 1), then Semantic Administration for this Server is enabled (Administrators can log in using apps).
$g_defaultClosedStatus = TRUE;   // If this is FALSE (or 0), then the default (unspecified) Open/Closed format for meetings reported to NAWS is OPEN. Otherwise, it is CLOSED.
// These reflect the way that we handle contact emails.
$g_enable_email_contact = FALSE;   // If this is TRUE (or 1), then this will enable the ability to contact meeting list contacts via a secure email form.
$include_service_body_admin_on_emails = FALSE;   // If this is TRUE (or 1), then any emails sent using the meeting contact will include the Service Body Admin contact for the meeting Service body (ignored, if $g_enable_email_contact is FALSE).
$include_every_admin_on_emails = FALSE;   // If this is TRUE (or 1), then any emails sent using the meeting contact will include all Service Body Admin contacts (including the Server Administrator) for the meeting (ignored, if $g_enable_email_contact or $include_service_body_admin_on_emails is FALSE).

//The server languages are supported by default, the langs specified here add to them
$format_lang_names = array(
);
// These are 'hard-coded,' but can be changed later:
$time_format = 'g:i A';  // The PHP date() format for the times displayed.
$change_date_format = 'g:i A, n/j/Y';  // The PHP date() format for times/dates displayed in the change records.
$admin_session_name = 'BMLT_Admin';  // This is merely the 'tag' used to identify the BMLT admin session.

?>
