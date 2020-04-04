<?php
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

	// These are the settings created by the installer wizard.

		// Database settings:
		$dbType = 'mysql'; // This is the PHP PDO driver name for your database.

		// Location and Map settings:
		$region_bias = 'us'; // This is a 2-letter code for a 'region bias,' which helps Google Maps to figure out ambiguous search queries.
		$search_spec_map_center = array ( 'longitude' => -79.793701171875, 'latitude' => 36.06575205170711, 'zoom' => 10 ); // This is the default map location for new meetings.
		$comdef_distance_units = 'mi';

		// Display settings:
		$bmlt_title = 'BMLT Administration'; // This is the page title and heading for the main administration login page.
		$banner_text = 'Administration Login'; // This is text that is displayed just above the login box on the main login page.

		// Miscellaneous settings:
		$comdef_global_language ='en'; // This is the 2-letter code for the default root server localization (will default to 'en' -English, if the localization is not available).
		$min_pw_len = 10; // The minimum number of characters in a user account password for this root server.
		$number_of_meetings_for_auto = 10; // This is an approximation of the number of meetings to search for in the auto-search feature. The higher the number, the wider the radius.
		$change_depth_for_meetings = 5; // This is how many changes should be recorded for each meeting. The higher the number, the larger the database will grow, as this can become quite substantial.
		$default_duration_time = '1:00:00'; // This is the default duration for meetings that have no duration specified.
		$g_enable_language_selector = TRUE; // Set this to TRUE (or 1) to enable a popup on the login screen that allows the administrator to select their language.
		$g_enable_email_contact = FALSE; // If this is TRUE (or 1), then this will enable the ability to contact meeting list contacts via a secure email form.

	// These are 'hard-coded,' but can be changed later.

		// These reflect the way that we handle contact emails.

		$include_service_body_admin_on_emails = FALSE; // If this is TRUE (or 1), then any emails sent using the meeting contact will include the Service Body Admin contact for the meeting Service body (ignored, if $g_enable_email_contact is FALSE).

		$include_every_admin_on_emails = FALSE; // If this is TRUE (or 1), then any emails sent using the meeting contact will include all Service Body Admin contacts (including the Server Administrator) for the meeting (ignored, if $g_enable_email_contact or $include_service_body_admin_on_emails is FALSE).


		$time_format = 'g:i A'; // The PHP date() format for the times displayed.
		$change_date_format = 'g:i A, n/j/Y'; // The PHP date() format for times/dates displayed in the change records.
		$admin_session_name = 'BMLT_Admin'; // This is merely the 'tag' used to identify the BMLT admin session.
		$g_enable_semantic_admin = TRUE;
		$default_minute_interval = 5; // This sets the minutes interval for Start Time and Duration.
