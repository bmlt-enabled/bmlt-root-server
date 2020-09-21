# Basic Meeting List Toolbox Root Server

[![Build Status](https://travis-ci.org/bmlt-enabled/bmlt-root-server.svg?branch=master)](https://travis-ci.org/bmlt-enabled/bmlt-root-server)

DESCRIPTION
-----------

The Basic Meeting List Toolbox (BMLT, hereafter) is a very powerful client/server system
that has been written for a very specific purpose, for a very specific clientele.

It is designed to track and locate Narcotics Anonymous meetings, which are regularly-scheduled, weekly, recurring events.

The original intended clientele is Narcotics Anonymous Service bodies (although other 12 step fellowships have started
using BMLT as well). The service body implements a BMLT server, and provides the server to other NA Service bodies.
This project is the "root" server for the BMLT. It is the "server" part of the BMLT "client/server" architecture.

You can find out way too much about the BMLT on [the BMLT Documentation Site](https://bmlt.app), including information
on lots of ways you can contribute to the project.

The source files are hosted on [GitHub](https://github.com).

[Follow this link to access the BMLT Root Server GitHub repository](https://github.com/bmlt-enabled/BMLT-Root-Server).
There are also links to various predecessor legacy repositories [here](#older-repositories) at the end of this README.
For specific information on setting up a development environment for work on the BMLT root server, please
see [CONTRIBUTING.md](CONTRIBUTING.md) in the GitHub repository.

[You can follow us on Twitter for release announcements](http://twitter.com/BMLT_NA).

REQUIREMENTS
------------

The entire system is written in PHP, JavaScript, XHTML and CSS. Most of the code is PHP.

PHP 5.6 or above is required to establish a root server, and 5.0 or above for a satellite.

For more information about server requirements, see [the page on Server Requirements](https://bmlt.app/specific-topics/server-requirements/)
 
INSTALLATION
------------

You set up a root server as the central database and administration area for a BMLT
implementation, but most people access it through what we call "satellite servers." These
satellites comprise the "client" part of the BMLT "client/server" architecture.

For instructions on installing the root server, see [the page on installing a new Root Server](https://bmlt.app/installing-a-new-root-server/)

CHANGELIST
----------
***Version 2.15.7* ** *- UNRELEASED*
- Added initial support for time zones, hidden behind feature flag.

***Version 2.15.6* ** *- September 7, 2020*
- Added support for the `OLM` NAWS Format code for online meetings.
- Added the train and bus fields to browser view.
- Minor improvements to install wizard. Fix bug regarding names and passwords with single quotes or backslashes (before they would cause a cryptic error; now they are OK -- not that using them is a great idea, but the system shouldn't die strangely). Also, if there is a database access error, and one of the offending fields has whitespace at the beginning or end, give a hint to the user that might be the cause.

***Version 2.15.5* ** *- July 25, 2020*
- Many service bodies are already using an "HY" format for hybrid virtual and in-person meetings, so we made it official - if you don't already have the "HY" format, this release will create it for you.
- The formats most commonly used for virtual meetings (VM, HY, TC) have received NAWS format codes. This release automatically maps VM, HY, and TC to the appropriate NAWS format code.

***Version 2.15.4* ** *- July 12, 2020*
- Added the new "Virtual Meeting Additional Info" field to the root server, semantic API output, and NAWS export.
- Added a new read-only meeting browser based on CroutonJS. This replaces the old Observer view, exposes the "hidden" fields to authenticated users, and is intended to allow phone line volunteers easy access to meeting/contact data when fielding calls.

***Version 2.15.3* ** *- June 27, 2020*
- Added `services` and `recursive` parameters to `GetServiceBodies`, allowing service bodies to be filtered.
- Added `formats_comparison_operator` parameter to `GetSearchResults`. Valid values are `AND` and `OR`. Applies only to included formats, not to excluded formats.

***Version 2.15.2* ** *- April 5, 2020*
- Added Russian translation.
- Fixed an issue with the NAWS export where unauthenticated exports would not return unpublished meetings.

***Version 2.15.1* ** *- March 28, 2020*
- Added `VM` format for Virtual Meetings (pandemic response).
- Added `TC` format for Temporarily Closed meetings (pandemic response).
- Added the new "Virtual Meeting Link" and "Phone Meeting Dial-in Number" fields to the NAWS export.

***Version 2.15.0* ** *- March 22, 2020*
- Added a "Virtual Meeting Link" field to the Location tab of the Meeting Editor UI for the join link for virtual meetings.
- Added a "Phone Meeting Dial-in Number" field to the Location tab of the Meeting Editor UI for dial-in information for virtual or phone meetings.

***Version 2.14.9* ** *- March 21, 2020*
- Append comments field to direction field in NAWS Export. 
- Fixed issue with Admin UI not returning 6:00PM meetings when searching for evening meetings.

***Version 2.14.8* ** *- March 17, 2020*
- Fixed an issue that caused unpublished meetings without a World ID to be excluded from the NAWS Export. 

***Version 2.14.7* ** *- March 15, 2020*
- Added Portuguese translation and formats.
- Added 255 character limit to input fields in admin ui.

***Version 2.14.6* ** *- December 10, 2019*

- Geocoding does not work well in all parts of the world, so automatic geocoding of meetings is now optional. It is enabled by default, and can disabled by adding `$auto_geocoding_enabled = false;` to `auto-config.inc.php`.
- When `$county_auto_geocoding_enabled = true;` is set in `auto-config.inc.php`, the County field becomes read-only and is automatically populated by geocoding when a meeting is saved.
- When `$zip_auto_geocoding_enabled = true;` is set in `auto-config.inc.php`, the Zip Code field becomes read-only and is automatically populated by geocoding when a meeting is saved. 
- Added additional strings for Polish translation.

***Version 2.14.5* ** *- December 7, 2019*

- Added Polish translations to the administration UI.
- The "Other" tab in the meeting editor user interface can now be translated.
- Forward slashes are now unescaped for JSON semantic endpoints.
- The meeting editor's meeting search page now has a "Check All" and "Uncheck All" checkbox for selecting service bodies.

***Version 2.14.4* ** *- November 11, 2019*

- Added Polish formats.
- Added a data migration so that existing root servers will have the farsi formats.

***Version 2.14.3* ** *- November 3, 2019*

- Fixed an issue where the install wizard could not be completed when a permissions issue prevented writing of `auto-config.inc.php`.
- Added Farsi formats.
- When there is a problem with the Google API Key, a more descriptive error message is now shown in the UI.

***Version 2.14.2* ** *- October 14, 2019*

- Server administrators can now import service bodies and meetings from a NAWS-provided export in the Server Administration section of the UI. Service bodies, users, and meetings are created. If any of the provided service body world IDs or meeting IDs already exist in the root server, no changes are made.
- Refactored the installation wizard's NAWS import functionality, and increased its performance by leveraging a database transaction.
- Fixed possible concurrency issues with meeting saves by leveraging database transactions.

***Version 2.14.1* ** *- October 11, 2019*

- Fixed an issue that prevented data imports from a NAWS export from working when using PHP 7.0.

***Version 2.14.0* ** *- September 30, 2019*

- The installation wizard now allows you to prime a database with a NAWS export. All meetings and service bodies are created, and a service body administrator user is created for each service body. Meetings that are missing data for required fields are not imported. Required fields are: CommitteeName, AreaRegion, Day, Time, Address, City. 

***Version 2.13.7* ** *- September 23, 2019*

- The No Smoking format is now automatically mapped to the appropriate NAWS format.

***Version 2.13.6* ** *- September 22, 2019*

- Changed JSONP mime type to `application/javascript`.
- Rename "Get A NAWS Format Dump" to "Get A NAWS Format Export" in the semantic workshop.
- Fixed an issue where the admin user interface could falsely think a meeting had been edited.
- Place name_strings from unmapped formats in the Room field of NAWS Exports.
- Fix for NAWS format drop-down not sorting correctly in the root server administration.
- Updated to the latest BMLT Satellite Base Class.
- Added new NAWS formats CH (Closed Holidays), GP (Guiding Principles) and NC (No Children).

***Version 2.13.5* ** *- August 15, 2019*

- Fix for NAWS format types not saving correctly in the root server administration.

***Version 2.13.4* ** *- August 5, 2019*

- New meetings are now published by default. This is configurable by adding `$default_meeting_published = false;` to `auto-config.inc.php`.
- Added server version to login screen.
- Fixed a bug that caused changes to meeting start times and durations to not show up in the change history.

***Version 2.13.3* ** *- July 7, 2019*

- When saving a change to a meeting, the edit screen for that meeting is no longer closed. This makes it easier for the user to keep their place in the user interface.
- When the published checkbox is unchecked in the meeting editor UI, a note is displayed: "Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it."

***Version 2.13.2* ** *- July 4, 2019*

- Fixed an issue where the wrong service body could be displayed in the "Service Body Administration" section of the Admin UI when a Service Body Administrator is the Primary Admin of only one service body.
- Service Body Admins can now use the new "Server Administration" section of the UI, allowing them to upload the spreadsheet returned by NAWS to batch update the World IDs for their meetings.
- Removed the "Delete Permanently" capability when deleting meetings as a Server Admin. The "Delete Permanently" checkbox caused a meeting to be deleted with no change record. Abuse of this feature made it difficult for NAWS to reconcile its meeting list with the BMLT. All deleted meetings now result in a change record.

***Version 2.13.1* ** *- July 2, 2019*

- Semantic Administration is now enabled by default on install wizard.
- Fixed a bug with JSONP endpoint emoting incorrect mime type.
- Added format_type to the definition of formats.  This allows satellites to distinguish between classes of formats, and handle them differently (e.g. languages displayed with flags).
  - The following types are defined:
    - `'FC1'  =>  'Meeting Format (Speaker, Book Study, etc.)',`
    - `'FC2'  =>  'Location Code (Wheelchair Accessible, Limited Parking, etc.)',`
    - `'FC3'  =>  'Special Interest and Restrictions (Mens Meeting, LGTBQ, No Children, etc.)',`
    - `'O'    =>  'Attendance by non-addicts (Open, Closed)',`
    - `'LANG' =>  'Language'`
- Added the ability to specify languages for formats that are not included in the languages for which the server admin console has been translated.  This lightweight style of adding languages allows meeting lists to be generated if a satellite has been translated (much less work than the entire server admin).
  - To do this you would add an additional setting in your `auto-config.inc.php` 
    - Ex. `$format_lang_names = ['fa'=>'Farsi'];`
- Added database migration system with database schema versioning, and added dbVersion to GetServerInfo endpoint.
- Formats are now sorted alphabetically in the root server administration, as well as NAWS format dropdown.

***Version 2.13.0* ** *- April 28, 2019*

- Added new "Server Administration" section to the user interface, allowing server admins to upload the spreadsheet returned by NAWS to batch update the World IDs for meetings.
- Added warning when attempting to delete a service body to remove any existing meetings or transfer them to another service body so they don't become "orphaned".
- Fixed a bug where calculated field `root_server_uri` would get written to the database and subsequently reported in change history.

***Version 2.12.8* ** *- April 24, 2019*

- Fixed some PHP warnings in the POI/CSV, KML, and GPX endpoints.
- Fixed some PHP warnings in the GetChanges endpoint.
- Fixed some PHP warnings that occurred when saving meetings.
- Fixed some PHP warnings in the server_admin_strings.
- Fixed a PHP warning with headers being sent twice for NAWS dump.
- Fixed a PHP warning in the GetFieldValues endpoint.
- Fixed a PHP warning that occurred when modifying a user.
- Replaced deprecated PHP function `ereg` with `preg_match`.

***Version 2.12.7* ** *- April 21, 2019*

- The installation wizard now has more intuitive error handling.
- The installation wizard now writes `auto-config.inc.php` automatically, so you don't have to.
- Fixed a couple of warnings that show up in error logs when performing a NAWS Export.

***Version 2.12.6* ** *- February 24, 2019*

- Added meetings by location and day option to bmlt.js.
- Simple GetFormats response is now sorted.
- Added $default_minute_interval to override the minute interval for Start Time and Interval on the Admin UI.
- Fixed issue with Admin UI not returning noon meetings when searching for afternoon meetings.

***Version 2.12.5* ** *- December 24, 2018*

- Added user name to sign out link for the administration UI.
- Added the ability to display a fixed set of counties when editing meetings in a dropdown menu rather than a freeform text field with a new `$meeting_counties_and_sub_provinces` setting.
- Added Danish translations.
- Fixed some minor issues with the /html/ endpoint.
- Fixed some more issues with running the root server behind a reverse proxy.

***Version 2.12.4* ** *- December 15, 2018*
- Made sorting case-insensitive, for users and service bodies.
- When creating URLs for static content, the HTTP_X_FORWARDED_PORT and HTTP_X_FORWARDED_PROTO headers are now inspected for determining the port and protocol. 
- Added sorting to semantic workshop for service bodies and formats.

***Version 2.12.3* ** *- December 7, 2018*

- The service bodies list under the "Search For Meetings Tab" in the "Meeting Editor" is now sorted.
- The users list under the "Full Meeting List Editors" section of "Service Body Administration" is now sorted.
- Fixed an issue where the default duration time was not selected in the meeting editor.
- The format checkboxes in the Formats tab of the meeting editor are now sorted.
- The initialize database button in the installer wizard is now easier to see.
- The settings in the installer wizard are now displayed only after the initialize database button is pressed.
- Added cache busting to installer wizard's javascript and css files.
- Latitude and longitude input boxes are now read only instead of disabled, allowing users to select and copy the calculated values.
- Fixed an issue where static files in the client_interface/html/ endpoint were not being included.
- The German translations for the administration UI were updated.
- The "Set Map to Address" button in the single meeting editor was removed, as it no longer provides value to the workflow.

***Version 2.12.2* ** *- November 22, 2018*

- Fixed an issue where formats could be wrong in a NAWS Dump for servers that were combined using lettuce.
- Fixed the client_interface/html/ endpoint, which was broken by a change in dependency management in 2.12.1.

***Version 2.12.1* ** *- November 17, 2018*

- Both `$gkey` and `$gKey` are now valid settings in auto-config.inc.php.
- Fixed a minor issue with switching between unsaved users and service bodies in the admin UI.

***Version 2.12.0* ** *- November 12, 2018*

- Saving a meeting now geocodes and sets the latitude and longitude automatically.
- Fixed an issue where the schema migration from 2.11.0 could fail.
- Added meeting states and provinces to the available fields returned by the server info.
- Added the ability to display a fixed set of states/provinces when editing meetings in a dropdown menu rather than a freeform text field with a new `$meeting_states_and_provinces` setting.
- Fixed an issue where the Enter key would not submit the meeting search form when using Firefox.
- Fixed a UX issue where the NAWS Export link was in close proximity to the Delete Service Body button, resulting in accidental deletion of service bodies.
- Fixed an issue where the My Account save button would be incorrectly enabled/disabled (again).
- Added formats and unique value functions, and recursive option to bmlt.js

***Version 2.11.1* ** *- October 1, 2018*

- Fixed an issue where the user would erroneously receive a warning when switching between edited users and service bodies.
- Fixed an issue with cutting/pasting into text fields in the administration UI.
- Fixed an issue with editing meetings where the "Save" button would be enabled even if no changes had been made.
- Fixed an issue where the My Account save button would be incorrectly enabled/disabled.

***Version 2.11.0* ** *- September 28, 2018*

- Added the ability to allow Service Body Administrators to edit their users.
- Added cache busting for the server admin JavaScript, eliminating the need to clear the browser cache after an upgrade.
- There was an exceedingly rare bug in the history display. If a user had been changed for a Service body, and the previous user had been deleted, it would hang the history display.
- Added support for disabling the forced port in the include URIs. Some VM servers misrepresent the ports when forcing SSL.
- Improved code commenting.
- Fixed an inconsistency in time format of defaults for duration time

***Version 2.10.7* ** *- July 27, 2018*

- Improved the password hashing algorithm.

***Version 2.10.6* ** *- July 14, 2018*
- Service body and user dropdowns on the admin interface are sorted alphabetically
- Fixed an issue where leading zeros were being left on meeting time for JSONP interface.
- Fixed an issue with the case for the installer wizard generation of the "$gKey" variable (should be "$gkey").

***Version 2.10.5* ** *- April 19, 2018*

- The Italian localization was missing the label and default text for the Helpline field in the Service Body Editor. This has been fixed.
- Made a fix for the Italian translation to avoid text overlap.
- Fixed an issue where stricter PHP 7 settings were causing problems with user admin.

***Version 2.10.4* ** *- April 3, 2018*

- Added the capability to specify multiple Service bodies in the JSONP interface.
- Fixed an urgent bug caused by the new format hack.

***Version 2.10.3* ** *- April 2, 2018*

- Added the Root Server URI and shared code list to the XSD (XML Schema).
- Added the Root Server URI to the available fields returned by the server info.
- Added a list of format shared codes to the available fields returned by the server info.
- Added [JSONP](https://www.w3schools.com/js/js_json_jsonp.asp) as a choice for the Semantic Interface. This allows the use of the BMLT on site-builder systems, like [Wix](https://www.wix.com) and [Weebly](https://www.weebly.com).
- Fixed an old issue, where having two different formats with the same key could mess up in the meeting editor. HOWEVER, we should not allow this to occur.

***Version 2.10.2* ** *- March 24, 2018*

- Added provision for including Service body contact emails in the semantic response. This requires adding "$g_include_service_body_email_in_semantic = TRUE;" to the auto-config.inc.php file.

***Version 2.10.1* ** *- March 14, 2018*

- Added the Root Server URI as a new field to format results. This will help with the new [Sandwich](http://archsearch.org/sandwich/) aggregator.
- If someone enters '24:00:00' or '00:00:00' as the time (midnight), then it gets changed to 23:59:00 (11:59 PM). This ensures that "midnight" is always "TONIGHT," not tomorrow AM.
- In the semantic interface, I now return an explicit empty array ([]) for JSON, if there are no formats, Service bodies or changes.
- Added the API key to the location string lookups. This requires that the API also have Google Geocoding enabled.
- The geocoding API calls now go over HTTPS.

***Version 2.10.0* ** *- February 22, 2018*

- Added the Root Server URI as a new field to meeting search results. This will help with the new [Sandwich](http://archsearch.org/sandwich/) aggregator.
- Updated to the latest [BMLT Satellite Base Class](https://bmlt.app/specific-topics/bmlt-satellite-base-class/).
- Fixed an issue with the initial SQL for the installer wizard, where MySQL was not accepting the default TIMEDATE for users.
- Fixed a bug, in which World IDs were not being set when creating new Service bodies.

***Version 2.9.3* ** *- January 4, 2018*

- Added code to prevent [some, but not all] severly messed-up long/lat from pooching the whole coverage area.
- New Italian Translation.

***Version 2.9.2* ** *- November 12, 2017*

- The installer wizard would not run properly in an SSL environment. This has been fixed.
- The call_curl() function reported an older UA, which caused some security software to choke. Hopefully, this is fixed.
- There was a bug in creating new Service bodies. This is due to the new helpline setting. It has been fixed.

***Version 2.9.1* ** *- October 17, 2017*

- The CSV was returning all formats, when it should have been returning a subset. This obviously didn't have a major effect on things, but now it only returns used formats, like JSON and XML.
- In some cases, unpublished meetings were being returned in specific field dumps. This has been fixed (affected the [[bmlt_quicksearch]] shortcode).

***Version 2.9.0* ** *- October 9, 2017*

- Added the ability to associate a helpline phone number with a Service body.
- Added support for Co-Ops, GSCs and LSCs.

***Version 2.8.24* ** *- September 28, 2017*

- In some cases, searches for specific values of a meeting field (like location_municipality) could return unrelated results. This has been fixed.

***Version 2.8.23* ** *- September 24, 2017*

- This uses the latest Satellite Base Class (fixes text searches for some non-Roman character sets), and also the latest Semantic Workshop (text searches were not being properly escaped).

***Version 2.8.22* ** *- August 20, 2017*

- Added the NAWS "NS" format. This is actually kind of a worthless format, but NAWS does specify it.
- Made the fader banners in the editors a bit more attractive.
- Added support for weeks starting on days other than sunday (for sorting). This is accomplished by adding the following line to the auto-config.inc.php file:
    
    ``$week_starts_on = 1;``
    
    With "1" being Sunday, and "7" being Saturday.

***Version 2.8.21* ** *- June 20, 2017*

- Addressed an issue where misconfigured SSL certs could cause problems. I now just pretend they aren't misconfigured, because everyone else does the same.

***Version 2.8.20* ** *- May 26, 2017*

- Fixed an issue where the coverage area misbehaved around the Prime Meridian.
- Undid a change I made that borked new meetings.

***Version 2.8.19* ** *- May 20, 2017*

- Fixed an issue with the formats editor that was exposed by NA Sweden.
- Fixed a problem where the distances were not being reported in search results.

***Version 2.8.18* ** *- May 18, 2017*

- Addressed some PHP warnings.
- Added the latest BMLT Satellite Base Class, which supports Region Bias. This also applies to the Root Browser.

***Version 2.8.17* ** *- May 12, 2017*

- Fixed a coverage area issue that only affected the Hawai'i Region, because it is the only Region that crosses the IDL.

***Version 2.8.16* ** *- May 10, 2017*

- The NAWS dump will now no longer send unpublished meetings that don't have World IDs.
- Added a "GetCoverageArea" semantic selector. This returns a rectangle, encompassing the meetings served by the server.

***Version 2.8.15* ** *- April 22, 2017*

- Changed the install wizard to reflect the new settings added to the auto-config.inc.php file, including the new Google API key.

***Version 2.8.14* ** *- April 12, 2017*

- Updated to the latest Semantic Workshop (1.2.1).
- Fixed a couple of warnings for undefined variables.
- Updated a file inculde to remove POSIX backreference (/../), as security software no likee.
- Added the ability to have the default status of meetings set to "OPEN" in the case of NAWS dumps.
- Fixed a bug that always forced meetings to be listed as "CLOSED" in the NAWS dump.

***Version 2.8.13* ** *- March 30, 2017*

- Updated to the latest Satellite Base Class (will not make any difference in operation).
- Added a filter for the Service Body Description. People have been pasting junk in there, and it's been causing problems with [the Sandwich project](https://github.com/radius314/sandwich).

***Version 2.8.12* ** *- March 4, 2017*

- Fixed a rather obscure semantic admin bug that caused problems with meeting names and data when duplicating meetings.

***Version 2.8.11* ** *- February 28, 2017*

- Fixed a bug in the new meeting add functionality (semantic admin). This will only affect the [BMLTiOSLib](https://bmlt.app/specific-topics/bmltioslib/).
- Fixed a bug, where setting time between 12:01AM (00:01) -> 12:59AM (00:59) would revert to "PM."

***Version 2.8.10* ** *- January 9, 2017*

- Changes needed to return IDs in the Semantic Interface. This is mostly for the new [BMLTiOSLib](https://bmlt.app/specific-topics/bmltioslib/) project. Since that project is now alpha, we should release this.

***Version 2.8.9* ** *- January 8, 2017*

- There was a minor issue with the 2.8.8 release. Yes, there was data bad enough to make even that ill. Hopefully, it has been addressed.

***Version 2.8.8* ** *- January 8, 2017*

- Added support for a new search criteria: "Ends Before or At." This is mostly for the new [BMLTiOSLib](https://bmlt.app/specific-topics/bmltioslib/) project.
- Fixed an obscure bug in the Change JSON Semantic Response. Some change records had bad data that caused the response format to break JSON validation.

***Version 2.8.7* ** *- January 1, 2017*

- No visible changes. This simply includes the latest version of the base class to match the satellites.

***Version 2.8.6* ** *- December 13, 2016*

- There was an error in the XSD for deleting meetings. Added a temporary "double-dip" element to the XML response in order to avoid interfering with current implementations. Will delete the "meetingId" element in a later release.
- Fixed a legacy error. The name of the Service body field in the meeting change function was wrong. Because of the legacy issue, it can now either be 'service_body_id' or 'service_body_bigint'.
- Removed an erroneous meeting name field in the meeting change XML/JSON response.
- Fixed an old error in the Semantic Admin, where our special "variable" fields needed an extra layer of parsing.
- Fixed an old error in the Semantic Admin, where the formats field was improperly examined.
- Added a scrubber to the contact functionality to remove double-newlines, which bork some servers.
- Fixed an old bug, where Service bodies without World Committee Codes were being included in the NAWS Dump.
- Added an 'unpublished' column to the NAWS export.
- If you are logged in as a Service Body Admin, and you get a NAWS export, it will now include unpublished meetings that your login can see (marked as "unpublished"). If you are not logged in, you will not get any unpublished meetings.
- Now include the latest Satellite Base Class.

***Version 2.8.5* ** *- November 21, 2016*

- This fixes a longstanding search bug, where excluding (negative) weekdays was ignored. This mainly affected the Semantic interface.

***Version 2.8.4* ** *- November 6, 2016*

- Just updates the satellite base class code. No visible change.

***Version 2.8.3* ** *- October 16, 2016*

- Forgot to update the BMLT-Basic module.

***Version 2.8.2* ** *- October 15, 2016*

- Reintroduced support for the Google API keys.

***Version 2.8.1* ** *- August 8, 2016*

- Fixed a bug that prevented deleted meetings from being listed in the NAWS dump. This was newly introduced in 2.8.0.

***Version 2.8.0* ** *- August 6, 2016*

- There were a couple more CSV issues that needed addressing.
- Just getting rid of the independent format dump for CSV. It was pretty useless (especially now with the format extraction), and caused problems with printed lists.
- Fixed a bug that returned improper permissions for non-Service Body Admin logins for the semantic admin.
- Fixed an issue with the semantic admin, where fields that were empty (and supposed to be) were being ignored.
- Beefed up the semantic admin to allow the Service body of a meeting to be switched (a security check is written to ensure the user can admin both bodies).
- Added the capability to load a list of deleted meetings in the semantic admin.
- Added the ability to request changes across multiple Service bodies in the semantic admin (also applies to deleted meetings).
- Added the capability to easily restore deleted meetings in the semantic admin.
- Fixed a bug in the semantic administration that prevented single-Service body admins from creating copies of meetings.
- Fixed a couple of warnings that will really only show up in improperly-configured servers.
- Fixed an issue with saving formats in the new semantic admin.
- Added the capacity to rollback meetings to the semantic admin.
- Fixed the response from the new meeting semantic admin, so we don't have empty oldValue/newValue elements.
- Added the available keys to the server info response, so we can ensure a minimal support level.
- This is the first version to support [the BMLTAdmin app](http://bmlt.app/bmltadmin)

***Version 2.7.20* ** *- May 27, 2016*

- Fixed a bug that caused some CSV dumps to fail.

***Version 2.7.19* ** *- May 22, 2016*

- Did some work to clean up the code that generates the simple HTML response.
- Added the latest Satellite Base Class, which allows the dismissal of the Details window with the escape key and has better SSL support.

***Version 2.7.18* ** *- May 7, 2016*

- Fixes a problem in the simple table output.

***Version 2.7.17* ** *- May 2, 2016*

- Patch fix. Left an unnecessary require_once in there that really hosed local searches.

***Version 2.7.16* ** *- May 2, 2016*

- Changed the format of this README, as BitBucket has changed their markdown syntax a bit.
- Changed the style wrappers for the [bmlt_simple](https://bmlt.app/satellites/simple/) shortcode a bit, so that the separators can be styled a bit more easily.
- Changed the Semantic Admin stuff to be simpler. No one is using it (yet), so I think it should be OK.
- Fixed an issue with the semantic admin that returned the incorrect location for the schema.
- Fixed a warning that affected the semantic administration.
- Fixed two problems with the installer wizard. One was a JS problem exacerbated by Google changing their API, and the other was caused by the new Italian default formats.

***Version 2.7.15* ** *- April 27, 2016*

- Made it so the banner will stretch if more than one notification is displayed.
- Made sure that the Root Server emits 'Content-Type:application/json; charset=UTF-8' whenever JSON is returned.
- Fixed a warning in the Root Server format class.
- Added span elements to contain the various components of the meeting address in the bmlt_simple shortcode.

***Version 2.7.14* ** *- April 16, 2016*

- Added the latest base class. No changes to the Root Server.
- Modified this file to have a consistent markdown format.
- Fixed an issue with PHP 7.

***Version 2.7.13* ** *- April 9, 2016*

- Fixed a typo in the "locationInfo" portion of XML and JSON semantic returns (It appears as if this was never used).
- Added the latest satellite base class, which will not display any new changes to the Root Server.

***Version 2.7.12* ** *- April 6, 2016*

- Fixes a minor issue with the Semantic Workshop, that does not re-enable the weekday and selected fields areas properly after viewing the [[bmlt_table]] shortcode options.
- Fixed a minor issue with the way that start times were sorted, if one of the values was midnight, specified as "00:00".

***Version 2.7.11* ** *- April 4, 2016*

- Incorporated the latest Satellite Base Class and Semantic Workshop. Very little change to server itself.

***Version 2.7.10* ** *- April 1, 2016 (Happy April Fools' Day!)*

- Fixed a couple of warnings.
- Added the latest Satellite Base Class, which won't have any noticeable effect on the Root Server.

***Version 2.7.9* ** *- March 30, 2016*

- Has the latest CMS Base Class. No visible changes.

***Version 2.7.8* ** *- March 29, 2016*

- Fixed a javascript bug introduced in the previous release.

***Version 2.7.7* ** *- March 29, 2016*

- Fixed a warning in the login screen.
- The sort now allows boroughs to have precedence over municipalities.
- Uses the latest satellite with the [[bmlt_table]] shortcode.
- Uses the latest Semantic Workshop.

***Version 2.7.6* ** *- March 20, 2016*

- Using version 1.0.9 Semantic Workshop
- Fixed a bug that should not be a bug, but affected some servers (The Root Server would wipe out with a parse error in the user class).

***Version 2.7.5* ** *- March 20, 2016*

- The Semantic Response for server languages (XML and JSON only) was missing from the Semantic Workshop. That has been added.
- The XSD for SSL pages had the wrong URI (Minor bug that affects no one).
- SSL fixes for the Semantic Workshop.
- Added the JSON Version of Get Server Languages.

***Version 2.7.4* ** *- March 17, 2016 (Happy St. Paddy's day!)*

- The JSON "fix" for 2.7.3 actually broke searches. That should really be fixed, now.

***Version 2.7.3* ** *- March 16, 2016*

- Made the code handling the language selector cookie a bit more generic.
- Added a new Root Browser that has the ability to have language selected via a cookie.
- Added some blank index.htm files for a tiny bit more security.
- Fixed a JSON issue that caused problems with user administration.

***Version 2.7.2* ** *- March 13, 2016*

- The problem with the previous fix, was that it worked ONLY on direct domains. That's fixed now.
- This affects only the Root Browser, or the logged-in (Observer) browser functionality. The rest of the functionality is fine.

***Version 2.7.1* ** *- March 12, 2016*

- Fixed an issue found immediately after release with the Observer Browser on some servers that use subdomains that point to the main directory.

***Version 2.7.0* ** *- March 12, 2016*

- Added the semantic workshop as a submodule. You can reach it by calling "/main_server/semantic".
- Fixed a minor issue with JSON responses. Numeric "0" was being sent back as an empty string ("").
- Removed the ancient header stuff from the semantic interface XHTML.
- Fixed a few warnings.
- Fixed a problem with quotes (") in JSON data.
- Fixed an issue where edit cuts weren't being recognized by text boxes.
- Fixed an SSL issue, where the SSL version of Google Maps was not being called in the admin.
- Fixed an SSL issue with the observer browser.

***Version 2.6.32* ** *- March 5, 2016*

- Added the JSON data field in the change response to the XML schema.
- Changed the way the data is stored in the JSON data field. It is now kept in up to 2 objects: "before" and "after".
- Fixed a bug with the editor paste functionality.
- Fixed an issue with JSON special character encoding that caused issues on some servers.

***Version 2.6.31* ** *- February 17, 2016*

- Added a JSON extra data column to the GetChanges response. This means that getting a change record for a meeting will give you a lot more information about that meeting.
- Fixed a fairly minor cosmetic bug in the history display, where "&" was being shown as "&amp;" (over-aggressive HTML conversion).

***Version 2.6.30* ** *- January 6, 2016*

- Fixed a bug introduced in .29, where the AJAX calls were being borked.

***Version 2.6.29* ** *- January 5, 2016*

- Added an onpaste handler to the editor text fields (detects paste).
- Fixed an issue with SSL.

***Version 2.6.28* ** *- August 15, 2015*

- Put in a fix for possible corruption of some fields that were previously hidden, but are now visible.
- Fixed an issue where a user could not properly change their own user info (My Account).

***Version 2.6.26* ** *- June 26, 2015*

- Added some new fields to the serverInfo response.

***Version 2.6.25* ** *- June 25, 2015*

- Added a flag to the serverInfo response that tells whether or not semantic administration is enabled.

***Version 2.6.24* ** *- May 29, 2015*

- Fixed an issue that borked the distance display for the iOS app and the mobile client.

***Version 2.6.23* ** *- May 25, 2015*

- Fixed some CSS issues with the Satellite Base Class. This is only reflected in the logged-in (Observer) browser.

***Version 2.6.22* ** *- April 29, 2015*


- Fixed a bug in the meeting editor, where setting the duration hour to 0 would fail to be recognized.

***Version 2.6.21* ** *- April 9, 2015*


- Fixed a bug in the simple response format fetch. Sometimes, the wrong formats were being returned.

***Version 2.6.20* ** *- March 28, 2015*


- Fixed a bug in the simple response format fetch. Sometimes, the wrong formats were being returned.
- Added a basic server info semantic call. This returns the version and the lang enums.

***Version 2.6.19* ** *- March 21, 2015*


- Fixed an old JavaScript bug in the meeting editor, where the "Create New Meeting" screen would sometimes refuse to enable the save button.

***Version 2.6.18* ** *- March 19, 2015*


- Fixed an old issue, where the nation can keep repeating itself ad nauseam.

***Version 2.6.17* ** *- March 18, 2015*


- Fixed a bug, in which the CSV output was missing its "published" column.
- Fixed a bug, in which the JSON response data format was different from the previous version, and interfered with 3rd-party applications.

***Version 2.6.16* ** *- March 8, 2015*


- Fixed a bug introduced in Version 2.6.14 that pooched the [[bmlt_simple]] shortcode.

***Version 2.6.15* ** *- March 4, 2015*


- Made a change in the way the WORLD IDs are returned in the semantic selectors. They are now all properly formatted.
- Added a special extra field to XML meeting searches that shows an aggregate for location searches. Useful for maps.
- Added the ability to get only this new location data. This applies to CSV, XML or JSON, but only XML gets it as a matter of course.
- Reformatted the new field values return slightly.
- Fixed an XML schema bug (minor).

***Version 2.6.14* ** *- February 22, 2015*


- Fixed an issue where the "Change Meeting" button wasn't being activated for some browsers when editing the "other" tab values.
- The above fix also addresses an older issue that could cause "Other" tab values to be assigned incorrectly.

***Version 2.6.13* ** *- February 17, 2015*


- Made some changes to make the new semantic responses even more powerful. They are now sorted and reduced.
- Fixed a bug in the new semantic responses that reacted badly to commas in data values.
- The internal response data from the new semantic calls is now in CSV, which makes it a lot more useful.

***Version 2.6.12* ** *- February 17, 2015*

- You are now able to filter for specific formats in the new semantic "GetFieldValues" call by adding a "&specific_formats=1,2,3..." to the parameter list. Also, you can make it an AND (all values) search by adding "&all_formats," which means that the meeting must have all of the given formats to match. Default is "OR," so any one of the formats will trigger a match.

***Version 2.6.11* ** *- February 16, 2015*

- Corrected a couple of bugs in the new semantic "GetFieldValues" and "GetFieldKeys" routines. These make the BMLT slightly more awesome.

***Version 2.6.10* ** *- February 16, 2015*

- Corrected a bug introduced in the previous release.
- The install is substantially smaller than the previous release.

***Version 2.6.9* ** *- February 16, 2015*

- Added the capacity to extract the field keys and values semantically (only of use to administrators and semantic interfaces, but extremely powerful).

***Version 2.6.8* ** *- February 14, 2015 (Happy Valentine's Day!)*

- Fixed a fairly minor bug in the semantic sorter, in which blank fields were not being sorted at all (they need to be first).

***Version 2.6.7* ** *- January 31, 2015*

- Fixed an issue with the extra fields display in the regular shortcode display details (browser).
- Fixed an issue where the arbitrary fields were actually creating too many results (browser).
- Now hide the distance_in_km/miles parameters in the meeting details (these are internal parameters -browser).

***Version 2.6.6* ** *- January 26, 2015*

- Accidentally removed the text for the NAWS format dump in the Service Body Editor. This has been fixed.

***Version 2.6.5* ** *- January 26, 2015*

- Addressed a couple of PHP warnings.
- Fixed a nasty bug that could appear in a brand new install. The editor would display as blank after creating the first user.

***Version 2.6.4* ** *- January 13, 2015*

- Added the main table fields to the data templates in the semantic admin.
- Now allow the language to be forced when instantiating the server object (minimally useful, but should be in place).
- Fixed a bug with including files in a non-standard port server.
- Fixed a couple of bugs that interfered with deployment on an IIS server.

***Version 2.6.3* ** *- January 11, 2015*

- Added some more steps to the the default values for the "auto-range," and expanded it to up to 200 miles.
- Added some special SQL sauce for the distance search that makes distance searching more accurate (It now uses a simple [Haversine](http://en.wikipedia.org/wiki/Haversine_formula) formula, executed in SQL, in the initial database triage). It will tend to return more meetings, but not a whole ton more. Basically, it just improves the quality of the auto-range. This works well with the new added steps.

***Version 2.6.2* ** *- January 9, 2015*

- Tweaked the syntax of this document's changelist to be full markdown, to make it more readable on [Bitbucket](http://bitbucket.org).
- Fixed a minor bug, where the initial Service Body Editor NAWS Dump URI was not the new publicly-accessible one.
- Greatly simplified the distance search functionality, and now allow it to be customized. This was a request from a largely rural community.
- Added a default set of distance ranges for the radius hunt.

***Version 2.6.1* ** *- January 5, 2015*

- Made a slight change, so that the formats are not automatically read upon a server instance being created. Instead, we wait until the first call to load them. This prevents them from being loaded in cases where they are not required.
- Fixed an issue caused by getting too fancy with radius ranges, and ended up causing problems in the hunt for radius functionality. Need to re-explore this.

***Version 2.6.0* ** *- January 4, 2015*

- Changed the NAWS Dump Link in the Service Body Editor to use the new open semantic URI.
- Fixed an admin bug, in which meetings that an admin can't edit can be listed in the search results.
- Fixed a bug, in which doing searches for meetings by ID would return unpublished meetings.
- Added a bit of a kludge to number-only location searches (postal codes). I now append the region bias to the string, as it looks like Google ignores the "region=" parameter.
- Created a common URL to the main server (utility function). This allows use of HTTPS and increases overall quality.
- The server now returns only the formats used somewhere in the database in response to the semantic "switcher=GetFormats" call.
- Continuing to look for places where I can take security measures. Added a few "belt & suspenders" methods, like reloading the user from the DB at every opportunity.
- Added the ability to edit meetings via an XML or JSON semantic interface.

***Version 2.5.3* ** *- November 27, 2014 (Happy Thanksgiving!)*

- The 2.5.2 "fix" was inadequate. I needed to make the "other" field editing more robust.

***Version 2.5.2* ** *- November 26, 2014*

- The 2.5.1 fix exposed another bug in administering the "other" fields.

***Version 2.5.1* ** *- November 26, 2014*

- Fixed a typo that interfered with the new arbitrary fields functionality.

***Version 2.5.0* ** *- November 23, 2014*

- Added support for arbitrary fields in meetings (had neglected to support these after the 2.0 release).

***Version 2.4.10* ** *- October 14, 2014*

- Fixed another issue, where simple searches weren't sorting properly (again, overzealous warning compensation).

***Version 2.4.9* ** *- October 13, 2014*

- Fixed another issue, where meeting edits were being ignored (again, overzealous warning compensation).

***Version 2.4.8* ** *- October 12, 2014*

- Fixed an issue where low-numbered ASCs and RSCs did not have leading zeroes in the NAWS export, which could cause issues for NAWS.
- Fixed a couple of issues with the meeting editors, where inactive anchor elements could cause "page not found" errors.
- Fixed a bug, where searching for selected weekdays, formats or languages stopped working.

***Version 2.4.7* ** *- October 9, 2014*

- Added an ID to the weekday sections of the simple response (allows easy linking on display pages).
- Fixed a number of warnings.
- Added some satellite fixes.
- Fixed a rather nasty error in the semantic response that could have caused a lot of bad search results. I'm shocked I haven't heard anything.

***Version 2.4.6* ** *- August 19, 2014*

- Fixed an issue with apostrophes (') causing problems.
- Added a user-agent to the cCURL calls, as some servers filter out cURL calls.
- Added a simpler, more direct semantic access to NAWS dumps.

***Version 2.4.5* ** *- July 10, 2014*

- Added true Spanish localization to the Root Server.
- Added a cookie for storing the selected language.
- Tweaked the French localization.
- Added a weekday wrapper around block mode simple responses. This allows better integration of search results with CSS.
- Fixed an issue with double-quotes entered on some servers.

***Version 2.4.4* ** *- June 9, 2014*

- Fixed a bug in the semantic interfaces that caused problems with clients. If a meeting had no name, a blank name was returned. It now returns a generic "NA Meeting".
- Fixed a bug in the administration system that prevented logins after a logout.

***Version 2.4.3* ** *- June 4, 2014*

- Fixed a bug introduced by the new code (It only manifested when sending mail, so no one has been affected).

***Version 2.4.2* ** *- June 4, 2014*

- Simplified the response for the test in the email sender.
- Made the email generated from contacts a bit more explanatory.
- Improved the ability to access the browser and the editor for meetings. This works in conjunction with the email sent from contacts.
- Prevent duplicate emails in the contact email.

***Version 2.4.1* ** *- June 1, 2014*

- Oops. Forgot to turn off debug mode.

***Version 2.4.0* ** *- May 31, 2014*

- Fixed some French localization issues with the admin screens.
- Added the ability to specify a language for the administration, so multiple languages can be used for a root server.
- Added (re-added, really) the ability to contact the meeting list administrators for a meeting with a message.
- Added support for directly going to the editor for a particular meeting, via URI.
- Added the capability to continue to the desired editing function after being stopped for a login.

***Version 2.3.5* ** *- May 1, 2014*

- Added a couple more Swedish translated strings.
- Fixed a bug in the French translation that caused the server admin to hose up if that language was specified.

***Version 2.3.4* ** *- April 2, 2014*

- Removed a redundant include of the Google Maps API from the admin screen.
- Fixed a JavaScript error with the set map to address functionality of the editor.

***Version 2.3.3* ** *- March 12, 2014*

- Fixed a rather embarrassing error, in which several fields were left out of deleted meetings in the NAWS export. All root servers that plan to sync with NAWS should apply this update.

***Version 2.3.2* ** *- March 7, 2014*

- Removed a couple of redundant entries from the "seed" format database table.

***Version 2.3.1* ** *- March 5, 2014*

- Added the ability to convey special language formats to NAWS.
- Fixed a bug, in which all deleted meetings in the NAWS dump had the same change date.
- Fixed a bug, in which all deleted meetings, regardless of Service body, were sent to NAWS. It now only filters for the deleted meetings attached to the requesting Service body.

***Version 2.3.0* ** *- March 1, 2014*

- Added a button to the meeting editor location tab, to set the meeting long/lat to the address (people were missing the button in the map).
- Fixed a mild bug, where the confirm dialog was shown twice when canceling a meeting edit.
- Fixed an issue, where including double quotes (") in meeting data could cause the server to experience an AJAX hang.
- Fixed another issue, where adding backslashes (\) to data in the editor resulted in incorrect values in the database.

***Version 2.2.5* ** *- February 24, 2014*

- Fixed an issue that caused problems with the admin screen on IIS (Microsoft Windows) hosting servers.

***Version 2.2.4* ** *- February 23, 2014*

- Added the capability to indicate multiple meeting IDs in the search string, by separating them with commas. This allows a list of specific meetings, selected by BMLT ID, to be returned.
- Added a field to the NAWS export to assist in future assigning NAWS Committee Codes to existing meetings.
- Fixed some issues with non-standard TCP ports.
- Fixed an error with validating the server version in the satellite admin page.

***Version 2.2.3* ** *- February 17, 2014*

- Accidentally removed a format from the default formats (only applied during the install wizard, so this will not affect already installed deployments).

***Version 2.2.2* ** *- February 16, 2014*

- Fixed an issue that happened on a somewhat nonstandard server. This resulted in the format editor not working.
- The GetServiceBodies XML schema (and dump) had some errors. These have been addressed. This does slightly change the semantics for the GetServiceBodies variant.
- Made it so that you don't need to specify array brackets ([]) for the GetFormats variant of the semantic interface.
- Changed the "id" attribute in the XML rows to "sequence_index". This helped some semantic users parse the data.

***Version 2.2.1* ** *- January 21, 2014*

- Fixed a minor warning issue with the simple output.
- Turned off debug mode (oops).
- Changed the value in the "Delete" column for the NAWS export to "D" (as per request from NAWS)

***Version 2.2.0* ** *- January 20, 2014*

- Removed an erroneous French format, and added three new formats. Fixed a broken Swedish format, and made it an English format (Web).
- Added the ability to select a NAWS format code as a translation match for an existing format code. This allows arbitrary format editing.
- Addressed an old bug that caused agita when all formats have been deleted. "Solved" it by not allowing the last format to be deleted.

***Version 2.1.11* ** *- December 31, 2013*

- Addressed a bug that could affect the observer browser in some Internet Explorer sessions (AJAX bug).

***Version 2.1.10* ** *- December 29, 2013*

- Fixed a bug in the install wizard that would result in corrupted initial databases.

***Version 2.1.9* ** *- December 12, 2013*

- Fixed a very small bug in the simple output. This would only affect rare installations. If a "single_meeting_id=" link was provided, it would not resolve properly.


***Version 2.1.8* ** *- December 11, 2013*

- Fixed a bug that could affect rare servers with security set to "11." The Observer browser did not work.
- Fixed a bug that prevented meetings listed at noon and midnight from being displayed in simple searches (localization bug).
- Fixed a bug that created a disorganized sort for simple meeting results.
- Added address information to the meeting search results in the Meeting Editor. This was a request by an ASC administrator, so it had a high priority. It was a low-risk addition.
- Added an example auto-config file.

***Version 2.1.7* ** *- December 9, 2013*

- Fixed a bug in the simple meeting Map URL, caused by the localization work.

***Version 2.1.6* ** *- December 8, 2013*

- Added More French localization.
- Fixed a couple of PHP warnings.

***Version 2.1.5* ** *- December 7, 2013*

- Added French localization.

***Version 2.1.4* ** *- December 3, 2013*

- Fixed a syntax error in the Installer Wizard.

***Version 2.1.3* ** *- November 30, 2013*

- Added the capability to extract only those formats used in a search to the BMLT_SIMPLE capability. This allows things like easily scalable format keys.

***Version 2.1.2* ** *- November 29, 2013*

- There was an error in the KML output. This has been fixed.

***Version 2.1.1* ** *- November 29, 2013*

- Fixed a bug in the simple search, where weekdays were offset by 1 (actually caused by the localization work).

***Version 2.1.0* ** *- November 28, 2013*

- Consolidated and reduced the localization strings. Now, almost all the strings used in administration are in one file.
- Removed the useless PDF generator directory.
- Did some technical work to ensure that the server will work properly in some virtual host environments.
- Added a couple of more choices to the default sort keys.
- Added the capability to specify arbitrary sort keys in the semantic URIs.
- Fixed a bug in the meeting editor, where setting the map to an address did not activate the save button.
- Added GPX, KML and POI/CSV export options to the semantic interface.

***Version 2.0.34** October 19, 2013*

- Fixed a bug in the XML/JSON export, where empty fields could result in incorrect data.

***Version 2.0.33* ** *- October 2, 2013*

- Added the zip code to the simple output.
- Fixed a bug, where setting the map location would not properly enable the save button.
- Added a German translation of the duplicate login error message.
- Tweaked the German translations a bit.

***Version 2.0.32* ** *- June 26, 2013*

- Simply added an error handler for when a duplicate user login is specified when trying to create a new user.

***Version 2.0.31* ** *- June 8, 2013*

- There was actually a nasty bug in the installer wizard that was caused by some of the work to address non-MySQL databases. This has been fixed.

***Version 2.0.30* ** *- June 8, 2013*

- Made the TEST button more useful. Also fixed a bug in it, where it would fail for a perfectly-formed empty database.
- Fixed a bug, where the Service body admin section would not appear properly for Server Administrators if there were no preexisting Service bodies.

***Version 2.0.29* ** *- June 6, 2013*

- Simply trap when the TEST button is hit with no information (used to give no response). It now gives an error message.

***Version 2.0.28* ** *- June 4, 2013*

- Added a database connectivity test button to the installer wizard.

***Version 2.0.27* ** *- May 22, 2013*

- Fixes an obscure bug that can be caused by entry of double-quotes into the format.

***Version 2.0.26* ** *- May 22, 2013*

- Adds German localization to the meeting search portion (used by Observers).

***Version 2.0.25* ** *- May 22, 2013*

- Removed some old code from the header that caused the AJAX responses to be a bit slow. Satellites linking to this version should render more quickly.
- Added a 'recursive' parameter to the semantic interface. This allows the response to return an entire Service body hierarchy, based on 1 Service body.
- Made the search parser a bit more forgiving for Service bodies and weekdays. They are no longer REQUIRED to be arrays, which makes single values easier to specify.
- Updated to the latest CMS Base Class (has no real effect on this project).

***Version 2.0.24* ** *- May 19, 2013*

- Fixed a bug, where clicking the format checkboxes with Internet Explorer did not trigger their callbacks.

***Version 2.0.23* ** *- May 18, 2013*

- Fixed an issue, where the Meeting search (Observer only) could have a bad AJAX URI.

***Version 2.0.22* ** *- May 18, 2013*

- Added compensation for servers running on non-standard HTTP ports.
- Made it so that older auto-config files will not trigger the install wizard.

***Version 2.0.20* ** *- May 13, 2013*

- Reduced the number of times that the marker redraw is called in the standard [[bmlt]] shortcode handler.
- Fixed an issue with CSS that caused displayed maps to get funky.

***Version 2.0.19* ** *- May 12, 2013*

- Fixed a JavaScript bug, in which the "Create New Meeting" button would sometimes trigger an error.

***Version 2.0.18* ** *- May 10, 2013*

- Made more invisible 'belt & suspenders' tweaks to the code to ensure no warnings.

***Version 2.0.17* ** *- May 9, 2013*

- Made some invisible 'belt & suspenders' tweaks to the code to ensure no warnings.

***Version 2.0.16* ** *- May 8, 2013*

- Made it so that hitting the RETURN key in the meeting search will trigger a search. This is a big usability enhancement.

***Version 2.0.15* ** *- May 8, 2013*

- Fixed a bug, in which a Service Body Admin with no Service body privileges would be shown a Service Body Admin editor.

***Version 2.0.14* ** *- May 6, 2013*

- Fixed a bug that occurred when the main_server was at the root of the domain. It would prevent JavaScript from being loaded.
- Adding German localization.

***Version 2.0.12* ** *- May 4, 2013*

- Simply stopped a warning/note from showing up in strict PHP.

***Version 2.0.11* ** *- May 2, 2013*

- Fixed a bug, in which some text fields were over-HTMLing ampersands (&).
- Fixed a bug, in which creating new Service bodies would sometimes result in a "hanging throbber."
- Fixed a bug, in which deleting a Service body did not remove it from the popup menu.

***Version 2.0.10* ** *- May 1, 2013*

- Hopefully, added a workaround for servers with dicey session support.

***Version 2.0.9* ** *- May 1, 2013*

- Fixed a bug, in which single-quotes (apostrophes) were causing problems in some text fields.

***Version 2.0.8* ** *- April 29, 2013*

- Fixed a bug, in which blank text fields were not being saved.

***Version 2.0.7* ** *- April 27, 2013*

- 2.0.6 had issues. This should REALLY fix the problem (Do you know this tune?)...
- Fixed a fairly old bug in the root server, where creating new meetings could result in a hung screen.

***Version 2.0.6* ** *- April 27, 2013*

- 2.0.5 had issues. This should REALLY fix the problem...

***Version 2.0.5* ** *- April 27, 2013*

- Fixed a bug that appeared in some servers, where quotes were escaped. This interfered with administration.

***Version 2.0.4* ** *- April 25, 2013*

- Removed the single meeting details link from the simple search results, as it points to the root server, and the root server has changed a lot.
- Cleaned up some redundant code.

***Version 2.0.3* ** *- April 21, 2013*

- Rewrote the text search for non-location text. It was not handling non-Roman text properly.
- This is the first release on [Bitbucket](http://bitbucket.org)

***Version 2.0.2* ** *- April 18, 2013*

- Fixed an issue where sessions were not being preserved properly across a cURL call.
- Fixed a bug, where the "hidden" fields were having their values "mangled" by the root server.
- Fixed a bug, where a redundant confirm dialog was shown for deleted meetings.

***Version 2.0.1* ** *- April 17, 2013*

- Fixed two bugs in the localization of the "Observer search" feature.
- Fixed a bug, in which the upublished meetings were unintentionally returned as valid search results.
- Fixed some bugs that caused JavaScript issues.

***Version 2.0.0* ** *- April 13, 2013*

- Official release of 2.0.0
- There are 3 open bugs in this release that need to be addressed in a near-future "point release":
    7) Swedish Language Ümlauts Result In Bad Location Searches
        This expresses itself in satellite searches, but is probably a root server bug.
        Search for these cities, using [the Swedish root server](http://www.nasverige.org/bmlt):
            Malmö
            Strängnäs
            Göteborg
        The searches will not produce expected results.
    
    8) Localization Needs to support military time in the satellite/root server.

    9) Non-MySQL databases (in particular, Postgres) don't work.

***Version 2.0b15* ** *- April 9, 2013*

- Did some work to ensure that strings stored in the database would retain their UTF-8 characteristics.

***Version 2.0b14* ** *- April 8, 2013*

- The Swedish localization is all but complete.
- Switched the order of the long/lat fields in the NAWS export.

***Version 2.0b12* ** *- April 6, 2013*

- Added placeholders for the Swedish and Spanish localizations.
- Fixed a bug, in which linefeeds in text entries could bork the administration interface.

***Version 2.0b11* ** *- March 31, 2013*

- Fixed a JavaScript Issue, where the user level popup was not being correctly set, as a result of the removal of the useless "Editor" level.

***Version 2.0b10* ** *- March 31, 2013*

- Fixed a JavaScript Issue, where the "dirty confirm" was not being shown in some instances. It is still an issue in a couple (like closing the browser).

***Version 2.0b9* ** *- March 31, 2013*

- Fixed a number of small JavaScript bugs that had the annoying habit of interfering with the "dirty" flag for meetings.

***Version 2.0b8* ** *- March 31, 2013*

- Removed the useless "trainee" position of "Service Body Editor" as a choice.
- Fixed a rather nasty bug in the new Service Body Parent functionality.

***Version 2.0b7* ** *- March 30, 2013*

- Fixed this bug:
    6) No Way To Nest Service Bodies
        1) Log In as a Server Administrator
        2) Open the "Service Body Administration" panel
        3) Try to change the hierarchy (the "parent" of the Service body)
    
        ANOMALY: No way to do it
        EXPECTED: A popup menu with a choice of other Service bodies that can be used as "parents"

***Version 2.0b6* ** *- March 30, 2013*

- Fixed this bug:
    If there are no Service bodies (new install), the "Service Bodies" section is not displayed (so no new ones can be created).
    
***Version 2.0b5* ** *- March 30, 2013*

- Fixed these bugs:
    3) No default duration
        1) Log in as an Observer
        2) Select "Meeting Search"
        3) Using the GNYR Dataset, enter "Amagansett, NY" into the text search box
    
        ANOMALY: A number of meetings show no duration.
        EXPECTED: A 90-minute duration
        EXTRA INFO: This should probably be set explicitly in the install wizard

    4) In Observer Meeting Search Mode, Multiple Instances of Hidden Fields Can Be Displayed
        1) Log in as an Observer
        2) Select "Meeting Search"
        3) Using the GNYR Dataset, enter "Amagansett, NY" into the text search box. Ensure that the "This is a location or postcode" checkbox is selected.
        4) Hit "GO"
        5) Repeat Search (Return to "Specify A New Search," and hit "GO." No need to re-enter the search criteria)
        6) Select the first meeting displayed
    
        ANOMALY: Multiple instances of the "email_contact" field displayed.
        EXPECTED: Only one instance displayed.

***Version 2.0b4* ** *- March 29, 2013*

- Added full support for observers to browse the database, and view restricted fields in a secure fashion.

***Version 2.0b3* ** *- March 28, 2013*

- I realized that I had completely neglected the needs of observers (logged in, but can't edit).
  I added a basic search for them. Due to the way that the satellites are written, this took about
  an hour, and represents basically zero risk to the main server admin functionality. In order to
  use the new browser, you add /client_interface/html to the main_server URI. It will ask you to
  log in (if you have not already done so), and will present the basic search interface shown in the
  3.X version satellites.
  However, this will require some more work in order to integrate it more completely to the server.
  Even though the functionality is incomplete, I am making a release in order to ensure that the
  main server editor functionality was not affected.

***Version 2.0b2* ** *- March 27, 2013*

- Fixed this bug:
    2) Location Search Fails With Too-Tight Comma
        1)  Log in as a Service Body Admin (or Server Admin)
        2)  Open the "Meeting Editor" dropdown
        3)  Check "This is a Location or PostCode."
        4)  Enter "Babylon,NY" (Notice no spaces between comma and succeeding character).
        5)  Click "Search For Meetings"
        
        ANOMALY:    No Search Results
        EXPECTED:   A number of returned meetings
        EXTRA INFO: Entering "Babylon, NY" (Note space between comma and succeeding character) works.

***Version 2.0b1* ** *- March 25, 2013*

- Fixed this bug:
    1) Open Two Editors Accidentally
        1)  Log in as a Service Body Admin (or Server Admin)
        2)  Open the "Meeting Editor" dropdown
        3)  Do A Meeting Search That Will Yield Results
        4)  Open the "Edit Meetings" tab
        5)  Open the top Search Result dropdown
        6)  Close it
        7)  Click on the "Create A New Meeting" button (Either click on "Cancel", or the meeting name).
    
        ANOMALY:    Both the Create Meeting And First Result dropdowns open
        EXPECTED:   Only the Create Meeting Editor dropdown opens
- Turned off debug mode.

***Version 2.0b0* ** *- March 22, 2013*

- Official Beta Release.

***Version 2.0a12* ** *- March 20, 2013*

- Added alerts to display a message if the AJAX authorization fails.

***Version 2.0a11* ** *- March 19, 2013*

- Fixed a bug, in which the server admin account was not being properly set in the setup install wizard.

***Version 2.0a10* ** *- March 16, 2013*

- Finished major coding for the root server, as the installer wizard was the last part.

***Version 2.0a9* ** *- March 9, 2013*

- Fixed a rather serious issue, in which apostrophes (') in text could cause the editor to go kablooey.
- Fixed an issue with the wizard detector, where a null-set global was misinterpreted.

***Version 2.0a8* ** *- March 8, 2013*

- More work on the installer wizard.
- The Server Admin can now change their own login and name (Other admins cannot).

***Version 2.0a7* ** *- March 7, 2013*

- Fixed a bug in which the map set button was not being properly enabled.

***Version 2.0a6* ** *- March 7, 2013*

- The password setting wasn't working correctly, so I fixed it. It also forces a logout upon successful change.
- Fixed the styling on the various warnings and admonishments.

***Version 2.0a5* ** *- March 6, 2013*

- Internet Explorer was displaying the wrong cursor in the meeting results list. This has been fixed.
- There was a big issue with text fields not working properly on IE. This has been addressed. Apparently, cloning a DOM node does not properly transfer event handlers in IE.
- Removed the now-unnecessary "supports_ajax" argument from the login.
- Simplified the login form a bit, so that the URI will remain consistent.
- Fixed a bug, in which changing multiple items at once in the "My Account" section triggered a JavaScript error.

***Version 2.0a4* ** *- March 6, 2013*

- Fixed an issue where the format editor did not work properly on Internet Exploder.

***Version 2.0a3* ** *- March 4, 2013*

- Tweaked the config loader to allow the auto-config to be fetched from the old location. This will change by beta.

***Version 2.0a2* ** *- March 3, 2013*

- Reduced the size of the text in the Service Body Editor Save button.
- Sort the languages to ensure consistency, and that the native language of the server is always first.
- The format list is delineated by color stripes. When formats were added or deleted, these stripes could get pooched. That has been fixed.

***Version 2.0a1* ** *- March 3, 2013*

- Fixed a minor issue, in which the warning banners were not bing set when a format was deleted.

***Version 2.0a0* ** *- March 3, 2013*

- Major rewrite of the administration system.
- Simplified the administration, and got rid of all client-side root server HTML. All clients are now required to communicate with the root via semantic interfaces (JSON/XML/CSV).
- The server administration is now heavily AJAX-based, and is now a completely interactive Web app.

***Version 1.10.3* ** *- January 16, 2013*

- Added a "GetServiceBodies" variant to the semantic export.

***Version 1.10.2* ** *- January 8, 2013*

- The miles from search center value was incorrect (improper conversion from Km). This has been fixed.

***Version 1.10.1* ** *- The main change in this version is that the formats can be exported so that only the ones actually used in the search are given.*


***Version 1.9* ** *- March 24, 2012*

- Fixed a bug that did not apply localizations to PDF generation.
- Fixed a bug in the Metaphone splitter (It was in rarely used code, and had been there forever).
- Added some functionality to aid in syncing with the NAWS system.
- Changed the base "primer" DB to include the new standard set of formats to be used to sync with NAWS.
- Changed the way the "install wizard" works, in order to make it easier to "prime" the database.

***Version 1.8.43* ** *- November 24, 2011*

- Undid the date bug fix, as it caused problems on some servers, by reporting the wrong times.

***Version 1.8.42* ** *- November 17, 2011*

- Fixed a bug in which day/time searches were being offset improperly on some servers.
- Fixed a bug, in which the CSV response could cause a crash.

***Version 1.8.41* ** *- October 22, 2011*

- Addressed a bug, in which the wrong timezone was possibly being specified. The auto-config file will now allow a timezone to be specified in a variable called $default_timezone.
- Somehow or another, some junk characters got into the AJAX Thread Driver file. That has been fixed.
	    
***Version 1.8.40* ** *- October 21, 2011*

- Swedish localization added.
- There was a minor error in the XML schema for formats that interfered with operation on the iPhone app. This has been fixed.
	    
***Version 1.8.39* ** *- September 5, 2011*

- NA Sweden had an error, in which their server reports that it has all the various crypt() methodologies, but can't actually deliver. This broke the FullCrypt() function. I addressed it by adding a fallback to the most primitive crypt() function.

***Version 1.8.38* ** *- August 17, 2011*

- This addresses an issue where overrides of the address format strings were being ignored. It may also give a slight improvement to page load times.

***Version 1.8.37* ** *- August 15, 2011*

- Fixed a bug that prevented the "Contact Us About This Meeting" link from appearing in "More Details."

***Version 1.8.36* ** *- August 12, 2011*

- Fixed a bug discovered by UKNA, in which subsequent pages of a multi-page location result woul have bad links.

***Version 1.8.35* ** *- July 11, 2011*

- Fixed a couple of minor bugs in the installer wizard.
- Also added a change to the search spec. throbber location that should make it appear in the correct place, now.

***Version 1.8.34* ** *- July 4, 2011*

- Fixed a validation issue with the search form.

***Version 1.8.33* ** *- June 26, 2011*

- Added new fields to the CSV/JSON/XML Change response.
- There was a minor security issue that could have occurred with the email_contact field. It may have been displayed in some change records. This has been addressed.
- Made the changes response dig into a hierarchy of Service bodies, if a "parent" Service body ID is presented in service_body_id=
	    
***Version 1.8.32* ** *- June 22, 2011*

- Added the ability to filter by Service body, when looking for changes, and now only return meeting changes (previously, some Service body and user changes could also be supplied).
- Made a minor fix in the default details address string, so that meeting locations with no name won't show an empty comma.
- Added the address format strings to the shared local strings, which should help performance, and decouple the linking to global variables.

***Version 1.8.31* ** *- June 7, 2011*

- Added additional capability to the CSV, JSON and XML outputs, so that the Satellite Driver can extract more relevant information.

***Version 1.8.30* ** *- June 5, 2011*

- Fixed a fairly minor bug, in which failed geocode lookups would result in a blank screen. They now result in a message.

***Version 1.8.29* ** *- June 4, 2011*

- Initial check-in of [GitHub](http://github.com) project

	************* ORIGINAL SVN CHANGELOG (From SourceForge) *************

***Version 1.8.28* ** *- June 2, 2011*

- Fixed a bug, in which the start time was not being displayed in single meetings..

***Version 1.8.27* ** *- May 27, 2011*

- Fixed yet another bug introduced by over-aggressive optimization. The auto-radius calculation didn't work properly.

***Version 1.8.26* ** *- May 27, 2011*

- Fixed another bug introduced by over-aggressive optimization. The auto-radius calculation didn't work properly.

***Version 1.8.25* ** *- May 25, 2011*

- I was over-aggressive in my optimization efforts. I needed to add another parameter to the localized strings array.

***Version 1.8.24* ** *- May 23, 2011*

- Added the capability to specify that Service Body descriptions be shown in the Advanced Search.
- Made a number of changes to try to improve performance of searches.

***Version 1.8.23* ** *- May 3, 2011*

- Changed the class of the default map basic/advanced selector to allow the selector to be hidden when the map is displayed, using CSS.
- Converted the project to GPLv3 (Raises white flag).
- Added explicit content-type headers to prevent servers from playing with the JSON responses.
- Added a regex to the js files to strip naughty linefeeds from servers that just can't resist.
- Fixed a long-standing bug in the JSON encoder.
    
***Version 1.8.22* ** *- April 27, 2011*

- Fixed a bug, in which satellites could get interminable spinning throbbers, when a map search returns an empty search.

***Version 1.8.21* ** *- April 20, 2011*

- Fixed a bug, in which address string searches were calculating their radius in Km, regardless of the local setting.
- Fixed an old issue, in which text searches would often fail for correctly-spelled substrings of meeting data. This should now work.

***Version 1.8.20* ** *- April 20, 2011*

- Addressed an issue with the advanced map not opening properly.
    
***Version 1.8.19* ** *- April 11, 2011*

- Fixed a bug caused by my 1.8.18 fix.

***Version 1.8.18* ** *- April 9, 2011*

- Fixed a Firefox JavaScript error in the admin interface.
    
***Version 1.8.17* ** *- April 4, 2011*

- Worked around an issue where XSS filtering in a particular server was interfering with the contact form submission.
    
***Version 1.8.16* ** *- April 3, 2011*

- Now disable and uncheck the checkbox for a selected user in the Service Body Admin, so that an admin won't be selected as both an editor and the main admin.
- There was a rather nasty bug in the distance calculations that manifested when Kilometers were chosen. This should now be fixed.
- Added the ability to select the distance units to the wizard, and to the auto-config file.
    
***Version 1.8.15* ** *- March 22, 2011*

- Clear the default charset (php_ini). This allows better localization.
- Improved German Localization
- Speed up of various functions (the localized strings needed to be cached).
    
***Version 1.8.14* ** *- March 9, 2011*

- Added a debug check to the client_interface/server_access.php file to make debugging satellites easier. Make sure it is commented out for release.
- Added some code to enable display of non-Roman characters. This affects a lot of code, and requires fairly extensive testing.
- Fixed a longstanding bug, in which long data (greater than 256 bytes) was not properly stored or retrieved.
- Added the beginning of the German localization.
    
***Version 1.8.13* ** *- January 30, 2010*

- The changes weren't using the localized strings properly. This was an "invisible" bug, and has been fixed.
- Added a new security measure to ensure that unauthorized folks cannot see hidden values in change records.
- Added a new capability to get Changes returned between given dates in the REST interface.

***Version 1.8.12* ** *- January 22, 2011*

- Addressed a couple of fairly cosmetic issues, including an incorrect licensing header.
- Added the distance from a center point, in both KM and miles, as regular fields in CSV, XML and JSON responses.
    
***Version 1.8.11* ** *- January 15, 2011*

- Added the ability to sort results by distance from a radius point.
- Fixed another bug (not so stupid) in the PDF export.
    
***Version 1.8.10* ** *- January 8, 2011*

- Fixed a REALLY STUPID bug, introduced while trying to add support for compressed CSV. data. Geez, is my face red...
    
***Version 1.8.9* ** *- January 7, 2011*

- Fixed a bug in the "hunt" feature, in which the weekday filter wasn't being properly applied.
    
***Version 1.8.8* ** *- January 4, 2011*

- More warning work (being more careful, this time).
- Removed a bit more broken code that was never called.
- Fixed a bug in the weekdays search that prevented the new satellite class from operating properly.
    
***Version 1.8.7* ** *- December 29, 2010*

- Added ob_ stuff to the two new XML files.
- Added the ability to request XML be sent compressed by specifying 'compress_xml=1'
- Fixed a really, really dumb mistake in the install wizard, caused by my smart-ass attempt to squash warnings. duh. :P
    
***Version 1.8.6* ** *- December 28, 2010*

- Optimized the Service Body XML response.
- Fixed a minor error in the GetServiceBodies schema file.
- Fixed a couple of minor issues that generated warnings in PHP. No operational issues, but I don't like warnings.
- Added Doxygen comments to the new PHP files.

***Version 1.8.5* ** *- December 27, 2010*

- Added an XML export function to get the Service bodies. This will be for integration with the new Satellite Controller Class, under development.
- Got rid of some broken code that was never called, but would have caused problems if it had. Is there a PHP code coverage tool?
    
***Version 1.8.4* ** *- December 27, 2010*

- Added an XML export function to get the server languages. This will be for integration with the new Satellite Controller Class, under development.
    
***Version 1.8.3* ** *- December 19, 2010*

- Fixed a couple of "invisible" bugs that were pointed out. These were matters of bad styling, more than operational issues.

***Version 1.8.2* ** *- November 28, 2010*

- Fixed a bug in the focused data item responses (XML, CSV and JSON), where the results were being erroneously sorted.
- Fixed a bug, in which the contact form did not work for single meetings.
- Adjusted the CSS to make the little version display stand away from the right side just a bit.
- Adjusted the CSS for the contact form for single meetings. It was a bit offset.
- Some slight adjustments the the JavaScript in the Data Item submission to try to alleviate possible encoding issues (Probably will not make any difference).
    
***Version 1.8.1* ** *- November 25, 2010*

- Fixed a bug in the CSV export.
    
***Version 1.8* ** *- November 24, 2010*

- Added an XML reply format and a JSON reply format. These allow the use of the BMLT in a more semantic environment, and sets the stage for more powerful satellites.
    
***Version 1.7.5* ** *- November 8, 2010*

- Added a checkbox to the batch data item add, to force overwrites of existing data. This allows the function to be "gentler," and also allows deletes.
    
***Version 1.7.4* ** *- November 5, 2010*

- Fixed a bug introduced by the security patch.

***Version 1.7.3* ** *- November 2, 2010*

- Fixed a bug in the new function for adding bulk data items.
- Fixed a minor security hole, where admins without edit/observe rights on accounts could still see the hidden info on those accounts via a CSV dump.
    
***Version 1.7.2* ** *- October 22, 2010*

- Added the ability to set a "template" for the address display in the "More Info" and list screens.
- Fixed a really stupid SQL error introduced in 1.7 (Installer).
    
***Version 1.7.1* ** *- September 29, 2010*

- Made it so that the unusable edit fields are hidden from logged-in observers, and that observers can get to the meeting search page from the list reults page.
- Fixed some validation errors in the administration screen.
    
***Version 1.7* ** *- September 28, 2010*

- Added a powerful (and dangerous) function: Server admins can now apply a data item value to checked groups of meetings.
- Added a checkbox to the list results that will set/clear all checkboxes.
- Added full meeting dumps for deleted meetings (for NAWS DB, at their request).
- Do not set a value into the NAWS CSV dump if there is no Service Body ID provided (It was adding AR0 or RG0).
- If a value in a data field is 100% URI (in 'http://domain.tld' form), it will be replaced by an anchor to the URI.
- The World Services ID for a Group now displays in the correct "G00XXXXXXX" format for individual meetings More Details display.
- Now load the "History" section via AJAX. The immediate load caused an unacceptable page load time.
- Observer-level users can no longer edit their information (allows the same account to be shared by a number of people). You can only log in or out.
    
***Version 1.6.2* ** *- September 18, 2010*

- Added the "Ag" (Agnostic), "FD" (Five and Dime) and "AB" (Ask-It-Basket) formats to the default formats.
- Added a NAWS-format CSV export to the logged-in admin Advanced Search.
- Added the ability for satellites to specify 'advanced map' and 'advanced text' initial displays.
    
***Version 1.6.1* ** *- August 6, 2010*

- Fixed a minor bug that doesn't affect the operation of the system, but which made ugly URIs and HTML. The 'preset_service_bodies' field was being set up as inputs in the form.
- Fixed a possible (unlikely) security issue.
    
***Version 1.6* ** *- June 28, 2010*

- Added an "Observer" user level. This is a "read-only" user that can see hidden fields in meetings for which they are authorized. This is how helplines can see contacts for meetings.
- Added a "no_print" class to the "Contact Us About This Meeting" link.
- Added the ability to specify a banner over the login. This helps to reduce confusion for "practice" servers.
- Fixed [a bug](http://sourceforge.net/tracker/?func=detail&aid=3013722&group_id=228122&atid=1073410), in which "zombie markers" could come back after a zoom.
- Fixed [a bug](http://sourceforge.net/tracker/?func=detail&aid=3014328&group_id=228122&atid=1073410), in which parentheses in meeting names caused bad map URIs.
- Added "belt and suspenders" email validation to the contact form.
    
***Version 1.5.12* ** *- June 5, 2010*

- Added a region bias to the config file and the install wizard.
- Did a rather kludgy fix to work around the problem of Google Maps Geocode API ignoring the Region Bias
    
***Version 1.5.11* ** *- May 31, 2010*

- Fixed a bug in the JavaScript for the GPS location code (FireFox).
- Made the specification throbber appear in the middle, where it belongs.
    
***Version 1.5.10* ** *- May 6, 2010*

- Fixed a bug in the location text search.
    
***Version 1.5.9* ** *- May 2, 2010*

- Added a switch to detect compression conflicts with ZLIB.
    
***Version 1.5.8* ** *- April 29, 2010*

- Large change lists can cause the Edit Functions page to time out, so I added a "timeout bumper" to the list.
- Some mostly cosmetic changes to some JavaScript.
    
***Version 1.5.7* ** *- April 26, 2010*

- Fixed an old bug that could affect the way the server interaction works (curl).
    
***Version 1.5.6* ** *- April 25, 2010*

- Added a "nocompress" parameter to the simple call. This is so it can be used in SSI. Nothing else is affected, and there is no reason to upgrade unless you want your clients to have the option of using SSI.
    
***Version 1.5.5* ** *- April 16, 2010*

- Fixed a nasty new variant on the bug discovered in 1.5.2, and fixed in 1.5.3. The symptom was empty searches in map view, from locations entered by string.
- Added the ability to specify a radius for address string searches.
    
***Version 1.5.4* ** *- April 13, 2010*

- Fixed a bug, in which feedback was not being provided for permanent meeting deletions or undeleted meeting restores.
- Made sure that deleted meetings that are no longer deleted, don't show up in the "Deleted Meetings" list.
    
***Version 1.5.3* ** *- April 12, 2010*

- Added a report facility to view deleted and changed meetings. The "Deleted Meetings" section has been moved into here.
- Fixed an odd bug, in which some string locality searches could give different results between map and list mode.
- Added the ability to specify the title of the Root server page in the auto config file.
    
***Version 1.5.2* ** *- April 7, 2010*

- Fixed a bug, in which the "before" time was not correctly set.
- Fixed a bug in the kilometer radius search.

***Version 1.5.1* ** *- April 5, 2010*

- Added formats to the "simple" output. It was an oversight they weren't in the first release.

***Version 1.5* ** *- April 2, 2010*

- Added support for multiple fields to be searched for text.
- Added better support for "pinch zoom" of map results on smaller screens.
- Enabled scroll wheel zooming on map results.
- Added the ability to get "simple" table (or block elements) search results returned from the server. These can be embedded into pages.
    
***Version 1.4.7* ** *- March 24, 2010*

- Added support for Google Gears, as that is how Android does geolocation.
    
***Version 1.4.6* ** *- March 18, 2010*

- Added support for Blackberry and Opera Mini to the mobile browser
- Fixed a bug, in which the iPhone user agent was not being properly detected by the root.
- Fixed a possible JavaScript parsing issue. It only caused problems with the Joomla plugin.
    
***Version 1.4.5* ** *- March 6, 2010*

- Replaced deprecated eregi call with preg_match.
- Now explicitly turn off error reporting (minor security issue).
- Made map redraw MUCH faster after a zoom.
- Fixed the annoying thing that can happen if you click a bunch of times in rapid succession in the map (trail of markers).
- Improved the security of the js_stripper and style_stripper files for DOS systems.
- Improved the performance of the AJAX calls by aborting all prior ones.
- Re-enabled AJAX call return compression in the regular user client.

***Version 1.4.4* ** *- March 1, 2010*

- Using a different method to get a new ID for a meeting. The prior method can cause issues with tightly-locked databases.

***Version 1.4.3* ** *- February 21, 2010*

- Keep the throbber from displaying if a PDF download is being done.
    
***Version 1.4.2* ** *- February 19, 2010*

- If the main map in a single meeting display is small, we display only the small zoom control.
- Fixed a minor bug that caused format codes to be displayed in map view for meetings with no formats.
    
***Version 1.4.1* ** *- February 16, 2010*

- Make sure that the marker is removed when the zoom is brought out to where it disappears.
- Added support for Android.

***Version 1.4* ** *- February 13, 2010*

- Made the "code cleaners" more efficient.
- Added support for the geolocation API, so you can have a "Find meetings near me" facility.
- Added support for small (iPhone) screens.
- Added a throbber display to the initial search, so you know when a search is in progress.
- Fixed a few usability bugs with the advanced map.
- Added a weekday filter to the radius hunt. Helps to make the results a bit more relevant. This expands the radius to include more meetings.
- Fixed a minor issue in the cURL caller
- Made the "throbber" more stable. It had a tendency to wander around.
    
***Version 1.3.13* ** *- January 28, 2010*

- Addressed a minor security issue, in which it was possible to "tailgate" on a prior login. However, it required physical access to a machine with an already established session.

***Version 1.3.12* ** *- January 24, 2010*

- Fixed a bug, in which the format types were not displaying for a meeting edit.
    
***Version 1.3.11* ** *- January 18, 2010*

- Added a visibility parameter to the data fields. Fields can be marked as "invisible" (only admins can see it), Web-only (Does not show up for prints) and Print-only.
- Make sure that email contacts are only given to logged-in admins with the right to edit a meeting (hidden, otherwise).
    
***Version 1.3.10* ** *- January 7, 2010*

- Fixed a nasty bug that caused string searches to fail if any meetings had the new email contact field filled in.
    
***Version 1.3.9* ** *- January 4, 2010*

- Simply added the ability to override the "default" text in the printed PDF lists. This is useful for Service Bodies that use it to create their own lists.
    
***Version 1.3.8* ** *- January 2, 2010*

- Fixed a newly introduced bug in the formats editor that prevented proper listing of formats in new languages. This is not a data-loss bug, and only affects servers with multilingual formats.
- Fixed a bug in the way the email contact system works.
- Cleaned up and vet the email forms a bit. If the email address is not correctly formatted, the meeting, user or Service Body cannot be submitted.
- The "return key" submit for the meeting editor did not work. That has been fixed.
- Now ensure that unauthorized sessions are destroyed.
    
***Version 1.3.7* ** *- January 1, 2010*

- Had to take out [session_regenerate_id()](http://us2.php.net/manual/en/function.session-regenerate-id.php). It caused problems with Chrome.
- Fixed a series of sorting bugs in PHP 5.1.X
- Removed the strings for the two awkward alerts that are no longer given when adding users or Service Bodies.
- Fixed a bug in the format editor that could result in "clashes" with existing format IDs.
- The Add and Delete formats functions now refresh the screen. Much better and more "solid."
    
***Version 1.3.6* ** *- January 1, 2010*

- Removed the inclusion of the Service Body Administrator in the contact email "percolation." Only emails attached to the Service Body will be considered.
- Added the ability to associate an email address with an individual meeting. Hopefully, this will be enough to reduce requests to associate personal information with meetings. An email address is less harmful than most, and it is masked by the contact form.
- Made the behavior of the Service Body and User Editors a great deal more "natural," with more useful user feedback.
- Switched the main throbber to an "NA Radar" throbber. I want to remove the BMLT branding, as that might imply endorsement. I'll leave the root server shortcut icon, as that is so small that it doesn't say much.
- Fixed another bug that has been in there since 1.3.3, in which non-server admin users couldn't edit their own account info.
- Fixed an old bug in the map search, in which a zoom would result in an extra marker draw.
- Made sure that you don't need to specify meeting_key_contains in key searches (It should be true by default).
- This release should make the administration far more natural and easy.
    
***Version 1.3.5* ** *- December 29, 2009*

- Made the calculation of the radius circle a lot more accurate and speedy. It was having trouble with Northern latitudes. I switched both the JS and PHP to a fast Vincenty calculation.
- Fixed a bug that caused user object edits to get ignored (Oops -caused by my centralization of the c_comdef_server::IsUserServerAdmin() function in 1.3.3).
    
***Version 1.3.4* ** *- December 28, 2009*

- Added some styles to the edit screens for meetings that are duplicates or unpublished. Duplicate meetings now have uneditable Duplicate data fields, and a purple background (ugly), and unpublished meetings have a light orange background.
- Changed the start time granularity to every five minutes.
- Added a couple more fields to the "single meeting URI" filter.
- Added a lot of flexibility to the email contact system. It is now possible to set a "percolate up" email address, or a separate email address in the Service Body to receive meeting contact emails.
- Fixed a bug that caused the meeting editor to crash if a Service Body was deleted, when it was mentioned in a change record.
- Made the meeting editor place the copy data item on top.
- Fixed a bug, in which the proper copy style was not being updated in the background list when a copy was edited without removing the copy data item.
- Switched the displayed strings to use a centralized function.
- Added some indicators in single meeting (More Details) windows, when the meeting is unpublished and/or a duplicate.
- Added some stripslashes() calls to the text being stored to the database for meetings.
    
***Version 1.3.3* ** *- December 22, 2009*

- Ensure that all undeleted meetings are restored unpublished.
- Added the capability to duplicate a meeting (Creates a new meeting that is an exact duplicate of an existing one). This is done in the "bulk list operations," and results in purple stripes. Purple meetings cannot be published until the "Duplicate" data field has been removed.
- Fixed an issue where the server meeting ID count wasn't being done properly. It made it possible to have meeting IDs clash when resurrecting deleted meetings. So far, this has not come up.
- Made it so that the proper server c_comdef_server::IsUserServerAdmin() is used to check all users.
- Fixed a bug, in which restoring a deleted meeting from the Server Admin account would leave the permanent delete line behind.
- Fixed a bug, in which warnings could appear in the format sorter, because I didn't check properly to make sure the arrays were valid first.
- Tweaked the security just a wee bit more, by forcing a user object reload prior to ANY request for its level. Prevents "live object" shenanigans.
- Added titles to the unpublished rows and the disabled publish button that explain what is going on.
- Fixed a bug, in which unnamed meetings resulted in "null" being displayed in the map info windows. Now, "NA Meeting" is displayed.
- Fixed a possible security issue, in which login details could be passed beyond the initial login in cleartext (oops).
- Fixed a bug in which meeting editors were not allowed to delete unpublished meetings (They should be allowed to do so, and also undelete them).
- Fixed a couple more bugs around NA meetings without names.
- Updated the documentation to show the current structure of the BMLT.
    
***Version 1.3.2* ** *- December 19, 2009*

- Fixed a rather annoying inaccuracy in the map circle when zoomed out.
- Got some basic "bulk edit" operations working. From list view, you can now publish, unpublish or delete meetings in bulk, by using checkboxes.
- Made it so that longitude and latitude values can now be entered directly as text.
- Reduced the number of session regenerators, because some browsers drop logins during AJAX sessions.
- Added the ability for Server Administrators to make meeting deletions permanent.
    
***Version 1.3.1* ** *- December 13, 2009*

- Added some styles to the various additional editor checkboxes in the Service Body Editor. Bold are Service Body Editors, and italic are Meeting List Editors.
- Added the ability to sort the format codes in the standard meeting editor.
- Changed the styles for the format codes in the meeting editor, so the default formats are red (replaces the asterisk).
- Removed the new meeting confirm alert, just to try to reduce the number of clicks involved.
- Added a new AJAX throbber to the Create New Meeting screen, and disable the button while the editor is up, in order to try to reduce errors.
- Disable the publish checkbox if the long/lat has not been set.
- Added an extra layer of security to drop out of the editor if the meeting is published, and the editor is not a Service Body Admin.
- Cleaned up a bunch of deprecated PHP functions to reduce PHP warnings to only a few that concern the clock.
    
***Version 1.3.0* ** *- December 5, 2009*

- Simple release with no changes.

***Version 1.3B0* ** *- November 27, 2009*

- Added handling of "published" meetings, including a silent database update.
- Added an empty auto-config.inc.php.rename file.
- Made some substantial changes to the way that the meeting changes are displayed. They now give an itemized report of the exact changes made.
- Fixed a bug that would not have shown up until localization was attempted (one of the buttons in the meeting editor had a hardcoded name).
- Added a submit button to the new data field fieldset. This greatly enhances the meeting edit workflow; especially for new meetinggs.
- Fixed a couple of really lame, stoopid bugs in the format editor. I got my _GET and _POST mixed up. Duh.
- Fixed some CSS and JS issues that caused complaints with validators.
- Added the ability to sort the formats in the format editor.
- Fixed a bug in the way the meeting object serializes data. The weekday was being reduced by 1. The fix may affect old changes. Check the weekday if you do a revert.
- Changed the role of the Meeting List Editor, so they can't publish meetings, or work on published meetings. They can't delete meetings either.
    
***Version 1.2.22* ** *- November 25, 2009*

- Fixed an admin error, in which you are not allowed to assign meetings to Service bodies that don't already have meetings.
    
***Version 1.2.21* ** *- November 15, 2009*

- Fixed a JavaScript error that would sometimes result in the advanced tab becoming disabled, in some browsers. It should be fixed now. Really.
        
***Version 1.2.20* ** *- November 14, 2009*

- Fixed a JavaScript error that would sometimes result in the advanced tab becoming disabled, in some browsers.

***Version 1.2.19* ** *- November 11, 2009*

- The style for the new GO button was messed-up. This has been fixed.
- Removed a redundant empty script element from the header.
- Sped up the loading for satellites that have an AJAX check.
- Fixed a nasty JavaScript bug that showed up in Firefox.
    
***Version 1.2.18* ** *- November 10, 2009*

- Fixed [a nasty bug](https://sourceforge.net/tracker/?func=detail&aid=2895410&group_id=228122&atid=1073410), in which auto-radius within very dense urban areas could fail.
    
***Version 1.2.17* ** *- November 9, 2009*

- Added an extra "GO" button to the top of the Advanced Search screen. This enhances usability.
- Now hide "dead" meetings from all but the admins.
            
***Version 1.2.16* ** *- November 6, 2009*

- Fixed [a bug](https://sourceforge.net/tracker/?func=detail&aid=2891653&group_id=228122&atid=1073410), in which a nonfunctional PDF popup was displayed on the root server.
- Hardcoded the root PDF directory. This wasn't really a security breach, but it is possible to write bad URIs that could give ugly results. It's also a good idea to be anal by habit.
- Added a note to the Advanced Tab formats and Service Bodies to hold the cursor over the items.
- Made some tweaks to the styling and text of the search specification form to increase usability.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2893015&group_id=228122&atid=1073410), in which the advanced marker would disappear after a zoom.
    
***Version 1.2.15* ** *- November 3, 2009*

- Increased the minimum zoom for the "two-click zoom," because zoom 8 is still a bit too wide.
- Added a field to the auto-config.inc.php file, that allows you to disable the "auto zoom-in" feature, so every click results in meetings found. This is good for rural areas.
- Added on-root-server PDF generation. This allows satellites to use the PDF dumps <strong>(IMPORTANT: You MUST use version 1.2.15 satellites!)</strong>.
- Split the documentation and standalone satellite projects into separate projects. As of version 1.2.15, they will have their own tracking.
- Made a slight change in the default "weekday" sort. Won't make a difference anywhere but NYC, maybe. The "neighborhood" now sorts after start time.
    
***Version 1.2.14* ** *- October 31, 2009*

- Added a new behavior for the search map. If the map starts off with a zoom less than 8, the first click brings the zoom to 8, then the next click finds meetings. This helps for maps that cover wide areas. It shouldn't affect any of the implementations to date.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2888335&group_id=228122&atid=1073410), in which the overlay did not properly follow the marker in advanced search mode.
- Made "Location" into "Location Name", and "Location Information" into "Additional Location Information" for the default meeting data fields. Bit more self-explanatory.
- Added a language parameter for the standalone satellite server.
- Removed the "Secondary Parent" capability. The code is still there, and commented out, but it was too confusing. A basic hierarchy, with assigned additional editors is much simpler.
- Fixed [a bug](https://sourceforge.net/tracker/?func=detail&aid=2889829&group_id=228122&atid=1073410), in which setting a "Parent" Service Body to none failed.
    
***Version 1.2.13* ** *- October 27, 2009*

- Fixed [a bug](https://sourceforge.net/tracker/?func=detail&atid=1073410&aid=2886043&group_id=228122) in the main server and the standalone satellite, in which the advanced pane could interfere with the basic pane.
- Added a circular overlay to the Advanced Search map. Makes it much more usable.
    
***Version 1.2.12* ** *- October 25, 2009*

- Changed the title strings (tool tips) for the map markers to give information about the weekdays, and added one for the center marker.
- Forgot to close a &lt;label&gt; element in the advanced search screen. It is now closed.
- Simplified this doc page slightly, and added a header.
- Spruced up all the Doxygen-generated docs.
    
***Version 1.2.11* ** *- October 23, 2009*

- Fixed a couple of JavaScript bugs that showed up in good ol' IE8. We can always rely on IE to throw a monkey wrench into the works.
- Added an AJAX throbber to the meeting change submit button. The button becomes replaced by a throbber, until the form is refreshed.
- Tweaked the default styling for the Advanced Search Type popup to better mesh with the rest of the screen.
- The new map marker now pans to the marker.
- Updated the Advanced Search documentation to mention the map.
    
***Version 1.2.10* ** *- October 20, 2009*

- Made the contact form textarea slightly smaller, in order to prevent an ugly "stretch."
- Now make absolutely sure that there's something to write out for location info, so empty parentheses aren't shown.
- Changed the background color of the contact form to be a bit more consistent with its container.
- Fixed a broken div container in the main root server interface (did not cause any problems, but we HATES invalid pages. HATESSSS them. NASSSTY invalid pages &lt;gollum/&gt;).
- Made the Advanced Search tab able to use the map. This is big juju.
- Made the "No Results" display on the root server show up as white.
- There was a problem with PDF dumps being corrupted by embedded linefeeds. The BMLT replaces linefeeds with "; " now.
    
***Version 1.2.9* ** *- October 18, 2009*

- Fixed a minor IE6 bug, in which the response to the contact form submission would trigger a JS error.
- Made the center marker of a single meeting details display the black center marker, and now ensure that it is not clickable. This was a usability issue.

***Version 1.2.8* ** *- October 17, 2009*

- Fixed a bug that was causing issues with the Contact form of the FSRNA server.
- Further simplified the "Single Meeting" URL.
- Fixed a warning in the contact sent screen.
- Fixed a bug in which Meeting ID searches weren't being done properly (This really only affects administrative use).
- Made it so that a single meeting ID search when logged in as an admin, results in a list search, as opposed to a single meeting search. This is because you can't edit meetings in single meeting mode. The list search is a list of one meeting.
- Changed the contact email to have a direct link to the root server, instead of the client satellite.
    
***Version 1.2.7* ** *- October 8, 2009*

- Fixed a bug in the satellite server that caused problems when doing list searches from the advanced search.
- Fixed a bug in the "More Details" view, in which the GPS POI download link was broken.
- Fixed an annoying habit the tabs in the search spec had of toggling the map visibility.

***Version 1.2.6* ** *- October 6, 2009*

- Removed some more extraneous parameters from the "one meeting" URL. It should be as simple as possible.
- Fixed the styling of the center marker info window.
- Fixed a bug in the root server installer wizard that produced a broken config file (Sorry).
- Fixed an issue with the way the standalone satellite does its PDF dumps.

***Version 1.2.5* ** *- October 3, 2009*

- Fixed a bug, in which a certain filtered map search list view link did not work.
- Fixed some bugs in the satellite implementations of the contact form.
- Did some formatting work to allow more flexibility with the list display.
- Added the ability to specify 'advanced' in the 'start_view' parameter, which will open the search specification in the Advanced Tab.
    
***Version 1.2.4* ** *- October 1, 2009*

- Fixed a VERY strange Joomla! bug. If there is a multi-page reply in list form, then Joomla croaks when it hits a 'style="display:none"' in one of the two hidden &lt;div&gt; elements at the top of the page. I worked around it by setting the hides via JS. What a kludge...
    
***Version 1.2.3* ** *- September 26, 2009*

- Adjusted the XML output for the Service Body query, in order to accommodate CMS plugin satellites.
- Fixed a bug in which the class specifier for no results was broken.
- Added a class definition for no results.
    
***Version 1.2.2* ** *- September 17, 2009*

- Fixed a bug, in which the AJAX handler could fail on some implementations (most embarrassingly, the demo implementation for our satellite server).
    
***Version 1.2.1* ** *- September 14, 2009*

- Added a link to a Service Body site, if a URI is provided. This is in the meeting details form.
- Added the capability to specify "pre-checked" Service Body checkboxes in the main server Advanced Search tab. However, we still need to add support for this in the plugin satellites.
    
***Version 1.2* ** *- September 7, 2009*

- Added a US-Letter-Sized list option to the printable PDF.
- Fixed a bug in which clicking on page numbers in the list resulted in an unexpected return to map view.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2845523&group_id=228122&atid=1073410), in which entering ampersand (&) characters in any of the fields in the editors caused pretty big problems.
- Fixed [a bug](https://sourceforge.net/tracker/?func=detail&aid=2792463&group_id=228122&atid=1073410), in which deleted Service bodies with nested components weren't updated properly. The 'fix' is kind of a kludge, as we simply reload the page.
    
***Version 1.2b2* ** *- September 7, 2009*

- Added a "busy throbber" to the AJAX contact form, and fixed a couple of bugs that only showed when bad input was entered for the form.
- Made the booklet a true "chapbook" size, and started work on a full US letter list.
    
***Version 1.2b1* ** *- September 6, 2009*

- Made the booklet PDF use a two-column format.
- Added a "Contact Us" email form to each meeting, allowing people to send an email to the administrator for a given meeting.

***Version 1.2b0* ** *- September 1, 2009*

- Added support for a basic, standalone-satellite-based PDF generator (booklet).

***Version 1.1.2* ** *- August 31, 2009*

- Moving all documentation, including the user and admin manual, into the docs directory, and created a frameset to display them.
- Tweaked the Doxygen docs to reflect the new structure.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2846972&group_id=228122&atid=1073410), in which the list wasn't working in Map view.
- Fixed an unreported bug, in which zooming the map caused JavaScript slowdowns.
    
***Version 1.1.1* ** *- July 26, 2009*

- Added printout handlers for the six New York meeting lists.
- Added some base classes and handlers to the satellite server to afford on-the-fly PDF generation.
- Split up the project structure: Moving the Drupal, WordPress and Joomla! modules off into their own projects. The main satellite server will now only be the standalone and PDF generators.
- Modified the CSV data export to better accommodate list printing.
- Added the ability to do searches, based upon a single key/value (example: county or town).
- Fixed [a bug](https://sourceforge.net/tracker/?func=detail&aid=2827219&group_id=228122&atid=1073410), in which map searches with filters didn't work properly when called from a satellite server.
- Marked [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2791321&group_id=228122&atid=1073410) as closed. Not sure how it was fixed (Hate it when that happens).
- The current server version is now discretely displayed at the bottom, right corner of the search specification screen for the main server.
- Optimized the standalone satellite server to be even more crazy simple to implement, and wrote up some detailed docs on it.
    
***Version 1.1* ** *- July 6, 2009*

- Added the ability to dump results as a CSV file. If you are logged in as an admin (to the Root Server), you will have a new choice for return format: CSV file.
- Added the ability to retrieve formats as a CSV file as well.
    
***Version 1.0.1* ** *- June 26, 2009*

- Made the satellite servers use POST for their cURL calls. Increases the robustness a bit.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2809650&group_id=228122&atid=1073410), in which meeting rollbacks didn't work, and re-enabled the change rollback system and meeting undelete system.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2812707&group_id=228122&atid=1073410), in which the meeting edit functions did not work in Map View.
    
***Version 1.0.0* ** *- June 25, 2009*

- Tweaked the WordPress plugin to prevent it from slowing down the whole site.
    
***Version 1.0RC6* ** *- June 23, 2009*

- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2810067&group_id=228122&atid=1073410), in which singular meeting results caused problems with administration.
- Added a new field to the three CMS plugins, that allows you to specify a specific URL for the "New Search" link.
- Fixed an old bug in the Joomla! plugin that prevented the support older browsers functionality from working properly.
    
***Version 1.0RC5* ** *- June 21, 2009*

- Removed the Spanish localization. It was not complete, and was only there for testing. It should not be distributed.
- Added a couple of notes to the [Admin Manual](../main_server/AdminManual/) about the user interface issues that affect creating new users or Service Bodies, or deleting Service Bodies with nested Service Bodies.
- Broke the styles out from inside the WordPress plugin, and put them in their own stylesheet. This makes tweaking them easier.
- Made the styles for the WordPress plugin far more specific, in an effort to enforce the appearance in a chaotic environment.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2809609&group_id=228122&atid=1073410), in which the '&' character caused issues with editing meetings. Addressed the same issue with format editing.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2809622&group_id=228122&atid=1073410), in which the Edit Formats AJAX functionality was broken.
- Addressed an issue in which slashes could be added to text field value display in the meeting editor.
- Disabled the change rollback feature for meetings. It isn't working properly, and the fix would be too drastic at this stage.
    
***Version 1.0RC4* ** *- June 16, 2009*

- Changed the name of the WordPress plugin, as it is now available from [WordPress.org](http://wordpress.org/extend/plugins/bmlt-wordpress-satellite-plugin/). This means that any prior installs need to first, deactivate, then reinstall and reactivate the new plugin. However, after this, it will be able to be auto-updated.
- Fixed a reported bug, in which some of the files used the wrong line-endings.
    
***Version 1.0RC3* ** *- June 14, 2009*

- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2806255&group_id=228122&atid=1073410), in which the "Search for Text" link in the search specifier was broken for some browsers (notably, IE6).
    
***Version 1.0RC2* ** *- June 13, 2009*

- Tweaked the CSS for the info windows in the map results page to lessen the "overflow" caused by resized text.
- Fixed a minor bug in the WordPress plugin, in which the tbody element wasn't being properly closed.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2805723&group_id=228122&atid=1073410), in which the throbber wasn't being displayed properly in the admin control panel.
- Removed some redundant code in the admin files. The dangers of copy-and-paste programming...
- Added a note to the last page of the install wizard, instructing the server admin to create first a Service Body Admin, then a Service Body, then meetings.
- Added a style to the satellite implementations to ensure that the single meeting header starts off at a manageable size.
- Slightly tweaked the CSS in the WordPress style to keep the "New Search" menu centered.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2805918&group_id=228122&atid=1073410), in which a long/lat of 0 was considered a valid location, even though it is the ocean off of Africa. 0,0 is now considered an empty long/lat.
    
***Version 1.0RC1* ** *- June 11, 2009*

- Added a bit of CSS padding to the fieldsets in the Advanced Search.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2803269&group_id=228122&atid=1073410) in the WordPress plugin, in which the "New Search" link was bad for default WordPress (non-pretty URIs) installations.
- Added a link to the root server in the case that JavaScript is required by satellite servers.
    
***Version 1.0b5* ** *- June 7, 2009*

- Vastly improved the code for the standalone satellite server.
- Added the capability for satellite servers to override the root server setting, and determine the initial view for their Basic Search.
- Made the call_curl function a wee bit more robust.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2800165&group_id=228122&atid=1073410), in which we need to append the state to the town in List View.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2802511&group_id=228122&atid=1073410), in which the map view kept being closed whenever the search was switched between Basic and Advanced.
- Completed the Administration Guide.
    
***Version 1.0b4* ** *- June 5, 2009*

- Added a shortcut icon.
- Fixed [a bug](https://sourceforge.net/tracker/index.php?func=detail&aid=2801941&group_id=228122&atid=1073410) in WebKit browsers, where the initial map in Basic mode comes up skewed.
- Wrote an installer "wizard" to make installing a new root server fairly simple.
- Bundled a "raw" root server into a single [downloadable zip file](http://comdef.svn.sourceforge.net/viewvc/comdef/tags/1.0b4/main_server.zip).
    
***Version 1.0b3* ** *- June 1, 2009*

- Fixed an admin bug that prevented a user from changing their own password or login.
- Added an SQL file with a "Minimal" BMLT DB. The DB is called "minimal_bmlt", and the admin login is "admin" and the password is "minimal_bmlt".
    
***Version 1.0b2* ** *- May 31, 2009*

- Fixed several bugs that only showed up when we tried to create a new top-level server.
- Made the entire background of the local server dark.
    
***Version 1.0b1* ** *- May 31, 2009*

- Tweaked the CSS for the plugins to give a smoother user experience.
- Greatly simplified the URIs. The long URIs were causing problems with some servers.
- Added a new field to the satellite server plugins. This allows support for only js-capable browsers, and speeds up the load a bit.
- Fixed a bug in the WordPress plugin that gave the wrong URI for the "New Search."
- Made it so the WordPress plugin will only work on pages. Posts are too problematic for it.
- Fixed a "Pretty URL" problem in the WordPress plugin.
- Re-enabled the Googlebar for the meeting editor. It's just too damn useful.
- Fixed a bug in the create new meeting script that set the wrong weekday.
- Fixed a bug in the create new meeting script that was also caused by the rearrangement.
- Removed the Undelete Meeting Feature (temporarily). It has some strange bugs, and isn't important enough a feature to justify the possible instability.
- Fixed a bug that prevented the edit window from being properly refreshed after a revert.
- Fixed a bug, in which opening a new meeting details page while an edit window was up covered the edit window, and looked awful. The edit window is now closed.
- Wrote an Administration Manual.
- Fixed a silly bug in which the login check would erroneously flag a failure.
- Fixed yet another bug caused by the new arrangement. In this one, the Edit link in meetings opened from the Map Search went 404.
- Set the background of the local server dark, so there is no mistaking where you are.
- Added a note that displays the root server for the local server's main search box.
- Fixed a bug in which the POI CSV file link was bad for single meetings.
- Fixed a couple of more admin bugs introduced by the new setup for a standalone server.
- Fixed a bug that was introduced by a lame attempt to spackle over another bug. Let this be a lesson: ALWAYS FIX IT -DON'T WORKAROUND!
- Fixed a bug in which the proper JavaScript AJAX URIs were not being set up because of the new indirect index. This only affected control panel admin operations.
- The [Drupal Module](http://comdef.svn.sourceforge.net/viewvc/comdef/trunk/satellite_server/drupal/) is done.
- The [Joomla Module](http://comdef.svn.sourceforge.net/viewvc/comdef/trunk/satellite_server/joomla/) is done.
- The [WordPress Module](http://comdef.svn.sourceforge.net/viewvc/comdef/trunk/satellite_server/wordpress/) is done.
- Rearranged the location of the submit button in the various plugin admin pages.
- Removed a server call in the initial form that slowed down the display of the form by about a second and a half.
- Added a useful profiler class to the utilities.
    
***Version 1.0a5* ** *- May 21, 2009*

- Did a lot of rearranging of the styles and contexts for the local server. This makes it more useful as a standalone editing context.
- Fixed some bugs in the presentation of the "Deleted Meetings" panel in the control panel.
- The frameset was raised above the main server. The main_server directory now comprises a full administrative site application.
- Moved the simple PHP for the satellite server up a level, and set the configuration to reach over to the main server. This makes the satellite_server directory a full standalone meeting search for the local server.
- Fixed a bug that caused bad links to single meetings in the headers of details opened from list view.
- Added full options for the Joomla! component. It now works in exactly the same way as does the WordPress plugin.
- Improved the Joomla! component styling. The Joomla! component is definitely on a par with the WordPress component, which had been ahead of it.
- Fixed a couple more minor issues in the WordPress plugin.
    
***Version 1.0a4* ** *- May 17, 2009*

- Fixed some formatting issues with the WordPress plugin. The plugin is now enclosed in a table, which enhances the robustness and independence of the plugin.
- Fixed a bug in the WordPress plugin that didn't record zoom changes properly in the admin
- Removed the "Display Map Search As Whole Page" option from the WordPress plugin. Requires too much CSS intervention on the part of the implementor.
- Found and fixed an unnoticed bug that would affect attempts to localize the server.
- Added an "admin bar" that allows access to the various admin areas when logged in as an admin.
- Fixed a number of JavaScript issues for logged-in admins.
- Fixed some IE6 issues (STILL NOT FIXED: Clicking a Region checkbox in Advanced Search does not visibly affect enclosed checkboxes).
- Added the meta tag [as recommended by Microsoft](http://msdn.microsoft.com/en-us/library/cc817574.aspx), to correct for IE8 deficiencies.
    
***Version 1.0a3* ** *- May 15, 2009*

- Fixed a few WAI AAA issues.
- Fixed a couple of XHTML 1.0 Strict violations.
- Do an isset() check on the cookie in the login to prevent warnings.
- Made sure [iCab](http://icab.de) smiles on every page.
- [Removed the Google Bar](https://sourceforge.net/tracker/?func=detail&aid=2791909&group_id=228122&atid=1073410). They're gonna stick ads on it, so bye-bye.
- Fixed a bug in which the GPS POI wasn't downloading correctly for the single meeting display.
- An older version text editor must have polluted many files with garbage characters. This is fixed.
- Fixed some styling issues for browsers with JS disabled.
- Added the [X-UA-Compatible Meta Tag](http://msdn.microsoft.com/en-us/library/cc817574.aspx) to the local server.
    
***Version 1.0a2* ** *- May 14, 2009*

- Fixed [a bug](http://sourceforge.net/tracker/?func=detail&aid=2791387&group_id=228122&atid=1073410) in Meeting Administration, where meetings need to reflect changes in List View</a>
- Fixed [a bug](http://sourceforge.net/tracker/?func=detail&aid=2791843&group_id=228122&atid=1073410) in Meeting Administration, where the weekday popup menu set the wrong weekday</a>
- Added the first iteration of [the User Guide](http://magshare.magnaws.com/comdef/UserManual/)

***Version 1.0a1* ** *- May 11, 2009*

OLDER REPOSITORIES
------------------

The first BMLT release was in 2009. 

Here are various legacy repositories that are predecessors to the
[current BMLT Root Server GitHub repository](https://github.com/bmlt-enabled/BMLT-Root-Server).

[Follow this link to see the legacy BitBucket repository](https://bitbucket.org/bmlt/bmlt-root-server-deprecated/src/Release/).

[Follow this link to see the legacy GitHub repository](https://github.com/MAGSHARE/BMLT-Root-Server).

[Follow this link to see the legacy antediluvian repository on SourceForge](https://sourceforge.net/projects/comdef/).
(Note: this link isn't working any more as of July 2020.)

NOTE: The repository origin has been transferred to [Bitbucket](http://bitbucket.org).
[The legacy GitHub repository](https://github.com/MAGSHARE/BMLT-Root-Server) is now only archival
(ends at version 2.0.2).
