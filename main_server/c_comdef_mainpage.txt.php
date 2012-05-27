<?php
/***********************************************************************/
/** \mainpage
	<div style="height:24px"></div>
	<div style="height:80px;background-color: #0013d2;background-image:url(../images/DocHeader.gif);background-position:center top;background-repeat:no-repeat"></div>

	<h2>INTRODUCTION</h2>
	
	The Basic Meeting List Toolbox is a PHP framework that is designed to be the
	entire meeting list system for an NA Service Body. It is comprised of a
	fairly involved "Main Server," and substantially simpler "Satellite Servers"
	that communicate with each other using standard HTTP protocols (a
	<a href="http://www.xfront.com/REST-Web-Services.html">REST</a> interface,
	for nerds, because we have to give new names to old concepts, and feel that
	we discovered something new).
	
	The main server contains the meeting list database, and is where the database
	is administered, but the satellite servers can be installed on other sites
	that connect to the main server via the Internet. The satellite servers can be
	easily customized, as can the main server, so each site can retain its own
	character, while presenting a unified look-and-feel.
	
	<div style="text-align:center;font-style:italic"><img src="../images/HowDoesItWork.png" alt="The Overall Architecture" width="949" height ="705" /><br />
	Figure 1: The Overall Architecture of the BMLT</div>
	
	The BMLT relies heavily on <a href="http://maps.google.com">Google Maps</a>, which
	provides <a href="http://code.google.com/apis/maps/documentation/index.html">powerful tools for programmers</a>.
	
	The idea is to present a natural, highly-usable and extremely consistent user
	experience to finding NA meetings.
	
	The BMLT is designed to scale well. It does not rely on only one database. It uses
	<a href="http://us.php.net/pdo">a database abstraction layer</a> to allow a number
	of different database engines to be used.
	
	The BMLT main server requires <a href="http://us.php.net/releases/#5.1.0">PHP 5.1</a>
	or greater, but the satellite servers can use older versions of PHP.
	
	It is designed so that many different types of browsers can use the system. The
	more capability the browser has, the more useful and interactive the user experience.

	<h2>LICENSE</h2>

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this code.  If not, see <a href="http://www.gnu.org/licenses/">the GNU General Public License</a>.
	
	<h2>TECHNICAL DETAILS</h2>
	
	The Basic Meeting List Toolbox is a system designed to store, transmit,
	format and share NA meeting data. It uses an
	<a href="http://msdn.microsoft.com/en-us/library/ms978748.aspx">MVC pattern</a>,
	with the server classes representing the Model, various interpretation files
	representing the Controller, and the rendered
	<a href="http://www.w3.org/TR/xhtml1/">XHMTL</a>
	/ <a href="http://www.w3.org/XML/">XML</a>
	/ <a href="http://www.w3schools.com/css/">CSS</a>
	/ <a href="http://www.w3schools.com/js/default.asp">JavaScript</a>
	representing the View.
	
	It will have a number of different View layer implementations, including basic
	<a href="http://www.w3.org/TR/html401/">HTML</a>,
	<a href="http://www.wapforum.org/what/technical.htm">WML2</a>,
	<a href="http://www.xml.com/pub/a/2002/12/18/dive-into-xml.html">RSS,
	<a href="http://json.org">JSON</a> and <a href="http://www.w3.org/XML/">XML</a>.
	
	The whole idea of the BMLT is to abstract a necessarily complex infrastructure
	behind a very easy to use, and easy to customize "front end."
	
	The BMLT is designed to be a federated system, with multiple servers addressing
	localized needs, and providing a common interface for visitors and aggregators.
	
	It is designed to be easily localized for different languages and script systems.
	
	<h2>DOCUMENTATION AND CONFIGURATION</h2>
	
	The BMLT is designed, primarily, as an SDK (Software Development Kit) and an API
	(Advanced Programming Interface). It is delivered as a standalone system, but has
	been designed in a fashion that allows easy integration into existing systems.
	
	This documentation is the API documentation. It describes the internal
	<a href="php.net">PHP</a> and
	<a href="http://www.w3schools.com/js/default.asp">JavaScript</a> code of the BMLT.
	
	Other documentation, for usage and administration, will be provided separately.
	
	<div style="text-align:center;font-style:italic"><img src="../images/TechStructure.png" alt="The BMLT MVC Architecture" width="917" height ="632" /><br />
	Figure 2: The Basic MVC Architecture of the BMLT</div>
	
	<h2>NOTE TO OO ADDICTS</h2>
	
	PHP is a poopy object-oriented language. Learn to accept the fact and move on.
	The BMLT uses objects to handle complexity (Like a database model), but the lions' share
	of the code in this project is procedural. A lot of the JS is OO, but even that
	is carefully controlled, as OO is not necessary for the one-trick ponies we use
	in most of our JS code.
	
	Procedural PHP is MUCH faster and less bug-prone than OO. It is entirely possible
	to model an MVC pattern in non-OO code, and this system proves it. There are some
	folks who think that you need to have MVC class hierarchies to have a "real"
	MVC pattern. These folks seem to have a hard time grasping the concept of "pattern."
	
	<a href="http://www.enode.com/x/markup/tutorial/mvc.html">Note the heavy reliance on objects and classes in this description.</a>
	
	We're sure that PHP's OO capabilities will improve over time (it already has, by the time
	of this writing), so maybe a future rewrite might see more OO being introduced.
	
	Another consideration is maintainability. A lot more people are comfortable with
	procedural code than they are with OO. Most of the code that will require modification
	in the future (the Controller layers) is written in procedural code. The Model doesn't
	need much work, and has a lot of complexity, so it directly benefits from OO.
	
	However, the system is complex and involved. It needs as much performance as
	possible, and it won't get that from excessive OO. There's too many examples of
	sluggish, bug-ridden OO PHP "camels" out there as it is.
	
	<h2>STORAGE AND IMPLEMENTATION</h2>
	
	The database format uses a main table to represent the few "key" attributes
	of each meeting, and then two ancillary tables to contain key/value sets of
	additional data. This makes for a tremendously flexible system. It's known
	as the "KVP" (<strong>K</strong>ey/<strong>V</strong>alue<strong>P</strong>air) Pattern.
	
	The KVP tables are the *_comdef_meetings_data and the *_comdef_meetings_longdata tables.
	
	The *_comdef_meetings_data table will contain all of the extra meeting data in most implementations.
	It will hold integer, floating-point or strings less than 256 bytes in length.
	
	The *_comdef_meetings_longdata table is only there in the case that some Service Bodies may
	wish to associate extra-long (Greater than 255 bytes) data with meetings. In practice, this
	should be avoided, as it will lead to large databases and reduced performance.
	
	It stores information about Service Bodies and users (with login credentials).
	This allows very easily-specified security to control changes, and to specify
	information from Service bodies (Such as "Find all the meetings in the XXX Region").
	
	It stores changes to various objects as "before" and "after" "snapshots," and
	has the ability to "rollback" to a state before the change.
	
	BMLT uses five different types of objects. These are all stored in the database,
	and four of them are editable by the user (change records cannot be directly edited).
	
		- Meeting Records (These are actually kept in three related SQL tables)
		- Format Codes (These are segregated by language, and referenced by ID)
		- Service Bodies (Fairly generic, more like "groups" than anything else)
		- Users (Different users log in with their own credentials)
		- Changes (Every time one of the above entries is changed, it is recorded)
		
	These are managed and abstracted by the Model layer, which consists of a set of classes
	that model the database.
		
	The change recording feature is very powerful. Entire objects are saved as "before"
	and "after," and these can be used to give detailed reports and to restore objects
	to older versions. Due to the fact that each and every change is recorded, changes
	for meetings are removed after a preset number of changes have been made. This reduces
	the impact on the amount of data in the database.
	
	Service Bodies are assigned to entities such as ASCs and RSCs, but are really used
	within the BMLT as "groups," allowing fine control over the authority of logged-in
	users. The Service Body system allows a hierarchy, so RSCs can have editors that
	can edit all of the meetings in all of the ASCs within that RSC, but each ASC can
	only edit their own meetings, etc.
	
	<div style="text-align:center;font-style:italic"><img src="../images/SQLSchema.gif" alt="The Database Schema" width="917" height ="617" /><br />
	Figure 3: The BMLT Database Schema</div>
	
	<h2>LOCALIZATION</h2>
	
	The BMLT is designed to handle multi-language systems. Every single string that is displayed
	comes from a language directory. New languages can be added by creating a new directory,
	and localizing the strings in the directory.
	
	<h2>OUTPUT</h2>
	
	The View is designed to allow general users as much accessibility as possible. You
	don't have to have a particularly modern browser or JavaScript, etc. to use the BMLT,
	but administrators need to have fairly modern browsers that can handle
	<a href="http://www.w3schools.com/Ajax/Default.Asp">AJAX</a>.
	
	The BMLT is provided as a "top to bottom" package that will work "out of the box."
	However, it is designed to be "skinned" or customized. All the CSS is
	<a href="http://magshare.org/2010/10/08/introduction-to-specificity/">low-specificity</a>
	<a href="http://www.w3.org/TR/CSS21/">CSS 2.1</a>, the markup is extremely well-formed
	to ensure
	<a href="http://www.w3.org/TR/xhtml1/#a_dtd_XHTML-1.0-Strict">XHTML 1.0 Strict</a>
	/ <a href="http://www.w3.org/TR/xhtml11/">XHTML 1.1</a> compliance,
	and it will validate
	<a href="http://www.w3.org/TR/WAI-WEBCONTENT/#wc-priority-3">WAI AAA (Priority 3)</a>.
	
	The meeting search result lists are displayed as tabular data (table elements), but
	everything else is done with block-level elements.
	
	<a href="http://code.google.com/apis/maps/documentation/index.html">Google Maps</a>
	are integral to the system. Meeting locations are described by a long/lat pair, not
	a street address. The address is actually very flexible, so the combination
	allows tremendous flexibility and accuracy in presenting a meeting location.
	
	Meeting data are kept in an extremely flexible manner. There are a few "core" fields,
	but most of the meeting data are kept as key/value pairs in separate tables, which
	allows tremendous flexibility. A server can be set up to allow only certain fields,
	and the system does not have to rely on these fields.
	
	<h2>CUSTOMIZATION</h2>
	
	The BMLT is designed to allow easy customization via <a href="http://www.w3.org/Style/CSS/">CSS</a>. The system is
	written using <a href="http://magshare.org/2010/10/08/introduction-to-specificity/">low-specificity CSS</a>. This means that it is fairly
	straightforward to write CSS of a higher specificity to alter the presentation. In
	addition, the BMLT uses a "theme" system for the root server, so new "themes" can be
	written.
	
	<h2>ADMINISTRATION</h2>
	
	The BMLT was designed to provide highly secure, highly usable administration. The
	administration interface is designed to use a great many modern features of Web clients,
	such as <a href="http://www.w3schools.com/Ajax/Default.Asp">AJAX</a>, so any browser
	used for <strong>administering</strong> the system must be a modern browser, capable of
	running active content. However, a <strong>user</strong> of the system can have an old
	browser, or one with active content disabled, and the BMLT will tailor its output to
	provide the richest experience possible.
	
	<h2 id="docs_security_details">SECURITY</h2>
	
	Security of administration is provided in a number of ways:
	
	- The first method is that all logins are tracked by <a href="http://us2.php.net/session">sessions</a>, not
		<a href="http://us2.php.net/manual/en/features.cookies.php">cookies</a>.
		
	- Because of this, administrators have to log in each time they want to administer the system. This is
		usually not much of a hardship, due to browsers caching logins (watch out for shared computers, like
		at schools or libraries). However, it does make a pretty big difference in security.
		
	- Administrative access is very carefully controlled. There is only one user that is able to administer
		all aspects of the site. All other administrators have restrictions on their capabilities. Only the
		Site Administrator can create or change users or Service Bodies.
	
	- All administration must be done at the root server. We do not send administrative data over our satellite REST connections.
	
	- A login function is provided that kills the administration session dead if credentials are
		not satisfied. No access to any code that manages site changes is provided until AFTER the
		login credentials are satisfied.
	
	- No code for which the current user is not authorized ever leaves the server. You'd be surprised at how often Web designers simply
		hide unauthorized code, which leaves the system vulnerable to even basic scraping.
	
	- The highest level of encryption is used for a given server, and passwords are never stored
		in anything other than the database and the on-server session handler. Passwords are one-way encoded
		(can never be retrieved -only reset), and are never stored by the BMLT on the client. Once the
		client has sent in the password on initial login; everything is handled by the session. No
		passwords are ever stored or passed on until they have been one-way encrypted. This eliminates
		cleartext passwords.
		
	- It is possible to specify minimum password length. Default is 6. Blank passwords are not allowed.
	
	- We don't allow input that would let the caller of a REST connection inject scripts or redirects. In most
		cases, we ignore things like AJAX handlers passed in, and set them to the local files.
	
	- All files, including AJAX handlers (a common place for "leaks") are subjected to the same
		level of session-based authentication.
	
	- We use <a href="http://php.net/manual/en/book.pdo.php">PDO</a>, with
		<a href="http://www.php.net/manual/en/pdo.prepared-statements.php">prepared statements</a>
		for database abstraction. This also scrubs DB interactions, eliminating SQL injection vectors.
	
	- When an object is checked for credentials, it is always reloaded from the database prior to
		being checked. This prevents the "live object" from being altered to spoof credentials.
	
	- No object can be modified in the database without the user having their credentials validated,
		and compared with the credentials of the object. This is a very low-level process, and is in
		addition to the above measures. We have taken great pains to design security into the most
		basic levels of the system.
	
	- We use the simple, Joomla-style "context check." A define is set in the calling context, and is
		checked in every PHP file. If the define isn't set, the transaction is terminated. This reduces
		the possibility that PHP files will be directly executed. Some of them need to be directly
		executed (like AJAX handlers), but we do this, so that only the appropriate files are able to be run.
		
	- All non-markup output is "scrubbed" through <a href="http://www.php.net/manual/en/function.htmlspecialchars.php">htmlspecialchars()</a>
		or <a href="http://www.php.net/manual/en/function.intval.php">intval()</a>. This makes XSS attacks quite difficult; if not impossible.
		
	- Our ears are wide open. Please contact us at webmaster -at- magnaws.com if you find any vulnerabilities.
	
	<h2 id="docs_release_notes">RELEASE NOTES:</h2>
	- May 26, 2012 - 1.10.1 Release
	    - The new way of reading formats did not honor multiple languages.
	    
	- May 25, 2012 - 1.10 Release
	    - Removed the "Ag" code from the "primer" database, because it is a somewhat controversial format. However, it can easily be replaced by Service bodies that install the server.
	    - Created the ability for XML and JSON responses to return only the formats used in the meeting search.
	    
	- March 24, 2012 - 1.9 Release
	    - Fixed a bug that did not apply localizations to PDF generation.
	    - Fixed a bug in the Metaphone splitter (It was in rarely used code, and had been there forever).
	    - Added some functionality to aid in syncing with the NAWS system.
	    - Changed the base "primer" DB to include the new standard set of formats to be used to sync with NAWS.
	    - Changed the way the "install wizard" works, in order to make it easier to "prime" the database.
	    
	- November 24, 2011 - 1.8.43 Release
	    - Backed out the fix for the time. It caused problems in other servers.
	    
	- November 17, 2011 - 1.8.42 Release
	    - Fixed a bug in which day/time searches were being offset improperly on some servers.
	    - Fixed a bug, in which the CSV response could cause a crash.
	    
	- October 23, 2011 - 1.8.41 Release
	    - Addressed a bug, in which the wrong timezone was possibly being specified. The auto-config file will now allow a timezone to be specified in a variable called $default_timezone.
	    - Somehow or another, some junk characters got into the AJAX Thread Driver file. That has been fixed.
	    
	- October 21, 2011 - 1.8.40 Release
	    - Swedish localization added.
	    - There was a minor error in the XML schema for formats that interfered with operation on the iPhone app. This has been fixed.
	    
	- September 5, 2011 - 1.8.39 Release
	    - NA Sweden had an error, in which their server reports that it has all the various crypt() methodologies, but can't actually deliver. This broke the FullCrypt() function. I addressed it by adding a fallback to the most primitive crypt() function.

	- August 17, 2011 - 1.8.38 Release
	    - Addressed an error, where overrides of the address format strings were being ignored. This should also slightly speed up page loads.

	- August 15, 2011 - 1.8.37 Release
        - Fixed a bug that prevented the "Contact Us About This Meeting" link from appearing in "More Details."
        
	- August 12, 2011 - 1.8.36 Release
        - Fixed a bug discovered by UKNA, in which subsequent pages of a multi-page location result woul have bad links.
	    
	- July 11, 2011 - 1.8.35 Release
        - Fixed a couple of minor bugs in the installer wizard.
        - Also added a change to the search spec. throbber location that should make it appear in the correct place, now.
	    
	- July 4, 2011 - 1.8.34 Release
	    - Fixed a validation issue with the displayed search form (not a big deal at all).
	    - Also added a change to the search spec. throbber location that should make it appear in the correct place, now.
	    
	- June 26, 2011 - 1.8.33 Release
	    - Added new fields to the CSV/JSON/XML Change response.
	    - There was a minor security issue that could have occurred with the email_contact field. It may have been displayed in some change records. This has been addressed.
	    - Made the changes response dig into a hierarchy of Service bodies, if a "parent" Service body ID is presented in service_body_id=
	    
	- June 22, 2011 - 1.8.32 Release
	    - Added the ability to filter by Service body, when looking for changes, and now only return meeting changes (previously, some Service body and user changes could also be supplied).
        - Made a minor fix in the default details address string, so that meeting locations with no name won't show an empty comma.
        - Added the address format strings to the shared local strings, which should help performance, and decouple the linking to global variables.
        
	- June 7, 2011 - 1.8.31 Release
	    - Added additional capability to the <a href="http://magshare.org/blog/welcome-to-magshare/bmlt-the-basic-meeting-list-toolbox/bmlt-in-depth/implementing-the-bmlt/high-geek-factor-stuff/export-options/">CSV, JSON and XML</a> outputs, so that <a href="http://magshare.org/blog/welcome-to-magshare/bmlt-the-basic-meeting-list-toolbox/bmlt-in-depth/implementing-the-bmlt/high-geek-factor-stuff/the-satellite-driver-class/">the Satellite Driver</a> can extract more relevant information.
	
	- June 5, 2011 - 1.8.30 Release
        - Fixed a fairly minor bug, in which failed geocode lookups would result in a blank screen. They now result in a message.
        
	- June 4, 2011 - 1.8.29 Release
	    - Moved the entire Root Server project to <a href="https://github.com/MAGSHARE/BMLT-Root-Server">GitHub</a>. The history will be restarted. If you want to access all the previous versions, they will be forever available on <a href="https://sourceforge.net/projects/comdef/">SourceForge</a>.
		
	- June 2, 2011 - 1.8.28 Release
	    - Fixed a bug, in which the start time was not being displayed in single meetings..

	- May 27, 2011 - 1.8.27 Release
	    - Fixed yet another bug introduced by over-aggressive optimization. The auto-radius calculation didn't work properly.

	- May 27, 2011 - 1.8.26 Release
	    - Fixed another bug introduced by over-aggressive optimization. The auto-radius calculation didn't work properly.

	- May 25, 2011 - 1.8.25 Release
	    - I was over-aggressive in my optimization efforts. I needed to add another parameter to the localized strings array.
	
	- May 23, 2011 - 1.8.24 Release
	    - Added the capability to specify that Service Body descriptions be shown in the Advanced Search.
	    - Made a number of changes to try to improve performance of searches.
	
	- May 3, 2011 - 1.8.23 Release
	    - Changed the class of the default map basic/advanced selector to allow the selector to be hidden when the map is displayed, using CSS.
	    - Converted the project to GPLv3 (Raises white flag).
	    - Added explicit content-type headers to prevent servers from playing with the JSON responses.
	    - Added a regex to the js files to strip naughty linefeeds from servers that just can't resist.
	    - Fixed a long-standing bug in the JSON encoder.
	    
	- April 27, 2011 - 1.8.22 Release
	    - Fixed a bug, in which satellites could get interminable spinning throbbers, when a map search returns an empty search.
	
	- April 20, 2011 - 1.8.21 Release
	    - Fixed a bug, in which address string searches were calculating their radius in Km, regardless of the local setting.
	    - Fixed an old issue, in which text searches would often fail for correctly-spelled substrings of meeting data. This should now work.
	
	- April 20, 2011 - 1.8.20 Release
	    - Addressed an issue with the advanced map not opening properly.
	    
	- April 11, 2011 - 1.8.19 Release
	    - Fixed a bug caused by my 1.8.18 fix.
	
	- April 9, 2011 - 1.8.18 Release
	    - Fixed a Firefox JavaScript error in the admin interface.
	    
	- April 4, 2011 - 1.8.17 Release
	    - Worked around an issue where XSS filtering in a particular server was interfering with the contact form submission.
	    
	- April 3, 2011 - 1.8.16 Release
	    - Now disable and uncheck the checkbox for a selected user in the Service Body Admin, so that an admin won't be selected as both an editor and the main admin.
	    - There was a rather nasty bug in the distance calculations that manifested when Kilometers were chosen. This should now be fixed.
	    - Added the ability to select the distance units to the wizard, and to the auto-config file.
	    
	- March 22, 2011 - 1.8.15 Release
	    - Clear the default charset (php_ini). This allows better localization.
	    - Improved German Localization
	    - Speed up of various functions (the localized strings needed to be cached).
	    
	- March 9, 2011 - 1.8.14 Release
	    - Added a debug check to the client_interface/server_access.php file to make debugging satellites easier. Make sure it is commented out for release.
	    - Added some code to enable display of non-Roman characters. This affects a lot of code, and requires fairly extensive testing.
	    - Fixed a longstanding bug, in which long data (greater than 256 bytes) was not properly stored or retrieved.
	    - Added the beginning of the German localization.
	    
	- January 30, 2010 - 1.8.13 Release
		- The changes weren't using the localized strings properly. This was an "invisible" bug, and has been fixed.
		- Added a new security measure to ensure that unauthorized folks cannot see hidden values in change records.
		- Added a new capability to get Changes returned between given dates in the REST interface.
	
	- January 22, 2011 - 1.8.12 Release
		- Addressed a couple of fairly cosmetic issues, including an incorrect licensing header.
		- Added the distance from a center point, in both KM and miles, as regular fields in CSV, XML and JSON responses.
		
	- January 15, 2011 - 1.8.11 Release
		- Added the ability to sort results by distance from a radius point.
		- Fixed another bug (not so stupid) in the PDF export.
		
	- January 8, 2011 - 1.8.10 Release
		- Fixed a REALLY STUPID bug, introduced while trying to add support for compressed CSV. data. Geez, is my face red...
		
	- January 7, 2011 - 1.8.9 Release
		- Fixed a bug in the "hunt" feature, in which the weekday filter wasn't being properly applied.
		
	- January 4, 2011 - 1.8.8 Release
		- More warning work (being more careful, this time).
		- Removed a bit more broken code that was never called.
		- Fixed a bug in the weekdays search that prevented the new satellite class from operating properly.
		
	- December 29, 2010 - 1.8.7 Release
		- Added ob_ stuff to the two new XML files.
		- Added the ability to request XML be sent compressed by specifying 'compress_xml=1'
		- Fixed a really, really dumb mistake in the install wizard, caused by my smart-ass attempt to squash warnings. duh. :P
		
	- December 28, 2010 - 1.8.6 Release
		- Optimized the Service Body XML response.
		- Fixed a minor error in the GetServiceBodies schema file.
		- Fixed a couple of minor issues that generated warnings in PHP. No operational issues, but I don't like warnings.
		- Added Doxygen comments to the new PHP files.
	
	- December 27, 2010 -1.8.5 Release
		- Added an XML export function to get the Service bodies. This will be for integration with the new Satellite Controller Class, under development.
		- Got rid of some broken code that was never called, but would have caused problems if it had. Is there a PHP code coverage tool?
		
	- December 27, 2010 -1.8.4 Release
		- Added an XML export function to get the server languages. This will be for integration with the new Satellite Controller Class, under development.
		
	- December 19, 2010 -1.8.3 Release
		- Fixed a couple of "invisible" bugs that were pointed out. These were matters of bad styling, more than operational issues.
	
	- November 28, 2010 -1.8.2 Release
		- Fixed a bug in the focused data item responses (XML, CSV and JSON), where the results were being erroneously sorted.
		- Fixed a bug, in which the contact form did not work for single meetings.
		- Adjusted the CSS to make the little version display stand away from the right side just a bit.
		- Adjusted the CSS for the contact form for single meetings. It was a bit offset.
		- Some slight adjustments the the JavaScript in the Data Item submission to try to alleviate possible encoding issues (Probably will not make any difference).
		
	- November 25, 2010 -1.8.1 Release
		- Fixed a bug in the CSV export.
		
	- November 24, 2010 -1.8 Release
		- Added an XML reply format and a JSON reply format. These allow the use of the BMLT in a more semantic environment, and sets the stage for more powerful satellites.
		
	- November 8, 2010 -1.7.5 Release
		- Added a checkbox to the batch data item add, to force overwrites of existing data. This allows the function to be "gentler," and also allows deletes.
		
	- November 5, 2010 -1.7.4 Release
		- Fixed a bug introduced by the security patch.
	
	- November 2, 2010 -1.7.3 Release
		- Fixed a bug in the new function for adding bulk data items.
		- Fixed a minor security hole, where admins without edit/observe rights on accounts could still see the hidden info on those accounts via a CSV dump.
		
	- October 22, 2010 -1.7.2 Release
		- Added the ability to set a "template" for the address display in the "More Info" and list screens.
		- Fixed a really stupid SQL error introduced in 1.7 (Installer).
		
	- September 29, 2010 -1.7.1 Release
		- Made it so that the unusable edit fields are hidden from logged-in observers, and that observers can get to the meeting search page from the list reults page.
		- Fixed some validation errors in the administration screen.
		
	- September 28, 2010 -1.7 Release
		- Added a powerful (and dangerous) function: Server admins can now apply a data item value to checked groups of meetings.
		- Added a checkbox to the list results that will set/clear all checkboxes.
		- Added full meeting dumps for deleted meetings (for NAWS DB, at their request).
		- Do not set a value into the NAWS CSV dump if there is no Service Body ID provided (It was adding AR0 or RG0).
		- If a value in a data field is 100% URI (in 'http://domain.tld' form), it will be replaced by an anchor to the URI.
		- The World Services ID for a Group now displays in the correct "G00XXXXXXX" format for individual meetings More Details display.
		- Now load the "History" section via AJAX. The immediate load caused an unacceptable page load time.
		- Observer-level users can no longer edit their information (allows the same account to be shared by a number of people). You can only log in or out.
		
	- September 18, 2010 -1.6.2 Release
		- Added the "Ag" (Agnostic), "FD" (Five and Dime) and "AB" (Ask-It-Basket) formats to the default formats.
		- Added a NAWS-format CSV export to the logged-in admin Advanced Search.
		- Added the ability for satellites to specify 'advanced map' and 'advanced text' initial displays.
		
	- August 6, 2010 -1.6.1 Release
		- Fixed a minor bug that doesn't affect the operation of the system, but which made ugly URIs and HTML. The 'preset_service_bodies' field was being set up as inputs in the form.
		- Fixed a possible (unlikely) security issue.
		
	- June 28, 2010 -1.6 Release
		- Added an "Observer" user level. This is a "read-only" user that can see hidden fields in meetings for which they are authorized. This is how helplines can see contacts for meetings.
		- Added a "no_print" class to the "Contact Us About This Meeting" link.
		- Added the ability to specify a banner over the login. This helps to reduce confusion for "practice" servers.
		- Fixed <a href="http://sourceforge.net/tracker/?func=detail&aid=3013722&group_id=228122&atid=1073410">a bug</a>, in which "zombie markers" could come back after a zoom.
		- Fixed <a href="http://sourceforge.net/tracker/?func=detail&aid=3014328&group_id=228122&atid=1073410">a bug</a>, in which parentheses in meeting names caused bad map URIs.
		- Added "belt and suspenders" email validation to the contact form.
		
	- June 5, 2010 -1.5.12 Release
		- Added a region bias to the config file and the install wizard.
		- Did a rather kludgy fix to work around the problem of Google Maps Geocode API ignoring the Region Bias
		
	- May 31, 2010 -1.5.11 Release
		- Fixed a bug in the JavaScript for the GPS location code (FireFox).
		- Made the specification throbber appear in the middle, where it belongs.
		
	- May 6, 2010 -1.5.10 Release
		- Fixed a bug in the location text search.
		
	- May 2, 2010 -1.5.9 Release
		- Added a switch to detect compression conflicts with ZLIB.
		
	- April 29, 2010 -1.5.8 Release
		- Large change lists can cause the Edit Functions page to time out, so I added a "timeout bumper" to the list.
		- Some mostly cosmetic changes to some JavaScript.
		
	- April 26, 2010 -1.5.7 Release
		- Fixed an old bug that could affect the way the server interaction works (curl).
		
	- April 25, 2010 - 1.5.6 Release
		- Added a "nocompress" parameter to the simple call. This is so it can be used in SSI. Nothing else is affected, and there is no reason to upgrade unless you want your clients to have the option of using SSI.
		
	- April 16, 2010 - 1.5.5 Release
		- Fixed a nasty new variant on the bug discovered in 1.5.2, and fixed in 1.5.3. The symptom was empty searches in map view, from locations entered by string.
		- Added the ability to specify a radius for address string searches.
		
	- April 13, 2010 - 1.5.4 Release
		- Fixed a bug, in which feedback was not being provided for permanent meeting deletions or undeleted meeting restores.
		- Made sure that deleted meetings that are no longer deleted, don't show up in the "Deleted Meetings" list.
		
	- April 12, 2010 - 1.5.3 Release
		- Added a report facility to view deleted and changed meetings. The "Deleted Meetings" section has been moved into here.
		- Fixed an odd bug, in which some string locality searches could give different results between map and list mode.
		- Added the ability to specify the title of the Root server page in the auto config file.
		
	- April 7, 2010 - 1.5.2 Release
		- Fixed a bug, in which the "before" time was not correctly set.
		- Fixed a bug in the kilometer radius search.
	
	- April 5, 2010 - 1.5.1 Release
		- Added formats to the "simple" output. It was an oversight they weren't in the first release.
	
	- April 2, 2010 - 1.5 Release
		- Added support for multiple fields to be searched for text.
		- Added better support for "pinch zoom" of map results on smaller screens.
		- Enabled scroll wheel zooming on map results.
		- Added the ability to get "simple" table (or block elements) search results returned from the server. These can be embedded into pages.
		
	- March 24, 2010 - 1.4.7 Release
		- Added support for Google Gears, as that is how Android does geolocation.
		
	- March 18, 2010 - 1.4.6 Release
		- Added support for Blackberry and Opera Mini to the mobile browser
		- Fixed a bug, in which the iPhone user agent was not being properly detected by the root.
		- Fixed a possible JavaScript parsing issue. It only caused problems with the Joomla plugin.
		
	- March 6, 2010 - 1.4.5 Release
		- Replaced deprecated eregi call with preg_match.
		- Now explicitly turn off error reporting (minor security issue).
		- Made map redraw MUCH faster after a zoom.
		- Fixed the annoying thing that can happen if you click a bunch of times in rapid succession in the map (trail of markers).
		- Improved the security of the js_stripper and style_stripper files for DOS systems.
		- Improved the performance of the AJAX calls by aborting all prior ones.
		- Re-enabled AJAX call return compression in the regular user client.
	
	- March 1, 2010 - 1.4.4 Release
		- Using a different method to get a new ID for a meeting. The prior method can cause issues with tightly-locked databases.
	
	- February 21, 2010 - 1.4.3 Release
		- Keep the throbber from displaying if a PDF download is being done.
		
	- February 19, 2010 - 1.4.2 Release
		- If the main map in a single meeting display is small, we display only the small zoom control.
		- Fixed a minor bug that caused format codes to be displayed in map view for meetings with no formats.
		
	- February 16, 2010 - 1.4.1 Release
		- Make sure that the marker is removed when the zoom is brought out to where it disappears.
		- Added support for Android.
	
	- February 13, 2010 - 1.4 Release
		- Made the "code cleaners" more efficient.
		- Added support for the geolocation API, so you can have a "Find meetings near me" facility.
		- Added support for small (iPhone) screens.
		- Added a throbber display to the initial search, so you know when a search is in progress.
		- Fixed a few usability bugs with the advanced map.
		- Added a weekday filter to the radius hunt. Helps to make the results a bit more relevant. This expands the radius to include more meetings.
		- Fixed a minor issue in the cURL caller
		- Made the "throbber" more stable. It had a tendency to wander around.
		
	- January 28, 2010 - 1.3.13 Release
		- Addressed a minor security issue, in which it was possible to "tailgate" on a prior login. However, it required physical access to a machine with an already established session.
	
	- January 24, 2010 - 1.3.12 Release
		- Fixed a bug, in which the format types were not displaying for a meeting edit.
		
	- January 18, 2010 - 1.3.11 Release
		- Added a visibility parameter to the data fields. Fields can be marked as "invisible" (only admins can see it), Web-only (Does not show up for prints) and Print-only.
		- Make sure that email contacts are only given to logged-in admins with the right to edit a meeting (hidden, otherwise).
		
	- January 7, 2010 - 1.3.10 Release
		- Fixed a nasty bug that caused string searches to fail if any meetings had the new email contact field filled in.
		
	- January 4, 2010 - 1.3.9 Release
		- Simply added the ability to override the "default" text in the printed PDF lists. This is useful for Service Bodies that use it to create their own lists.
		
	- January 2, 2010 - 1.3.8 Release
		- Fixed a newly introduced bug in the formats editor that prevented proper listing of formats in new languages. This is not a data-loss bug, and only affects servers with multilingual formats.
		- Fixed a bug in the way the email contact system works.
		- Cleaned up and vet the email forms a bit. If the email address is not correctly formatted, the meeting, user or Service Body cannot be submitted.
		- The "return key" submit for the meeting editor did not work. That has been fixed.
		- Now ensure that unauthorized sessions are destroyed.
		
	- January 1, 2010 - 1.3.7 Release
		- Had to take out <a href="http://us2.php.net/manual/en/function.session-regenerate-id.php">session_regenerate_id()</a>. It caused problems with Chrome.
		- Fixed a series of sorting bugs in PHP 5.1.X
		- Removed the strings for the two awkward alerts that are no longer given when adding users or Service Bodies.
		- Fixed a bug in the format editor that could result in "clashes" with existing format IDs.
		- The Add and Delete formats functions now refresh the screen. Much better and more "solid."
		
	- January 1, 2010 -1.3.6 Release
		- Removed the inclusion of the Service Body Administrator in the contact email "percolation." Only emails attached to the Service Body will be considered.
		- Added the ability to associate an email address with an individual meeting. Hopefully, this will be enough to reduce requests to associate personal information with meetings. An email address is less harmful than most, and it is masked by the contact form.
		- Made the behavior of the Service Body and User Editors a great deal more "natural," with more useful user feedback.
		- Switched the main throbber to an "NA Radar" throbber. I want to remove the BMLT branding, as that might imply endorsement. I'll leave the root server shortcut icon, as that is so small that it doesn't say much.
		- Fixed another bug that has been in there since 1.3.3, in which non-server admin users couldn't edit their own account info.
		- Fixed an old bug in the map search, in which a zoom would result in an extra marker draw.
		- Made sure that you don't need to specify meeting_key_contains in key searches (It should be true by default).
		- This release should make the administration far more natural and easy.
		
	- December 29, 2009 -1.3.5 Release
		- Made the calculation of the radius circle a lot more accurate and speedy. It was having trouble with Northern latitudes. I switched both the JS and PHP to a fast Vincenty calculation.
		- Fixed a bug that caused user object edits to get ignored (Oops -caused by my centralization of the c_comdef_server::IsUserServerAdmin() function in 1.3.3).
		
	- December 28, 2009 -1.3.4 Release
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
		
	- December 22, 2009 -1.3.3 Release
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
		
	- December 19, 2009 -1.3.2 Release
		- Fixed a rather annoying inaccuracy in the map circle when zoomed out.
		- Got some basic "bulk edit" operations working. From list view, you can now publish, unpublish or delete meetings in bulk, by using checkboxes.
		- Made it so that longitude and latitude values can now be entered directly as text.
		- Reduced the number of session regenerators, because some browsers drop logins during AJAX sessions.
		- Added the ability for Server Administrators to make meeting deletions permanent.
		
	- December 13, 2009 -1.3.1 Release
		- Added some styles to the various additional editor checkboxes in the Service Body Editor. Bold are Service Body Editors, and italic are Meeting List Editors.
		- Added the ability to sort the format codes in the standard meeting editor.
		- Changed the styles for the format codes in the meeting editor, so the default formats are red (replaces the asterisk).
		- Removed the new meeting confirm alert, just to try to reduce the number of clicks involved.
		- Added a new AJAX throbber to the Create New Meeting screen, and disable the button while the editor is up, in order to try to reduce errors.
		- Disable the publish checkbox if the long/lat has not been set.
		- Added an extra layer of security to drop out of the editor if the meeting is published, and the editor is not a Service Body Admin.
		- Cleaned up a bunch of deprecated PHP functions to reduce PHP warnings to only a few that concern the clock.
		
	- December 5, 2009 -1.3.0 Release
		- Simple release with no changes.
	
	- November 27, 2009 -1.3B0 Release
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
		
	- November 25, 2009 -1.2.22 Release
		- Fixed an admin error, in which you are not allowed to assign meetings to Service bodies that don't already have meetings.
		
	- November 15, 2009 -1.2.21 Release
		- Fixed a JavaScript error that would sometimes result in the advanced tab becoming disabled, in some browsers. It should be fixed now. Really.
			
	- November 14, 2009 -1.2.20 Release
		- Fixed a JavaScript error that would sometimes result in the advanced tab becoming disabled, in some browsers.

	- November 11, 2009 -1.2.19 Release
		- The style for the new GO button was messed-up. This has been fixed.
		- Removed a redundant empty script element from the header.
		- Sped up the loading for satellites that have an AJAX check.
		- Fixed a nasty JavaScript bug that showed up in Firefox.
		
	- November 10, 2009 -1.2.18 Release
		- Fixed <a href="https://sourceforge.net/tracker/?func=detail&aid=2895410&group_id=228122&atid=1073410">a nasty bug</a>, in which auto-radius within very dense urban areas could fail.
		
	- November 9, 2009 -1.2.17 Release
		- Added an extra "GO" button to the top of the Advanced Search screen. This enhances usability.
		- Now hide "dead" meetings from all but the admins.
				
	- November 6, 2009 -1.2.16 Release
		- Fixed <a href="https://sourceforge.net/tracker/?func=detail&aid=2891653&group_id=228122&atid=1073410">a bug</a>, in which a nonfunctional PDF popup was displayed on the root server.
		- Hardcoded the root PDF directory. This wasn't really a security breach, but it is possible to write bad URIs that could give ugly results. It's also a good idea to be anal by habit.
		- Added a note to the Advanced Tab formats and Service Bodies to hold the cursor over the items.
		- Made some tweaks to the styling and text of the search specification form to increase usability.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2893015&group_id=228122&atid=1073410">a bug</a>, in which the advanced marker would disappear after a zoom.
		
	- November 3, 2009 -1.2.15 Release
		- Increased the minimum zoom for the "two-click zoom," because zoom 8 is still a bit too wide.
		- Added a field to the auto-config.inc.php file, that allows you to disable the "auto zoom-in" feature, so every click results in meetings found. This is good for rural areas.
		- Added on-root-server PDF generation. This allows satellites to use the PDF dumps <strong>(IMPORTANT: You MUST use version 1.2.15 satellites!)</strong>.
		- Split the documentation and standalone satellite projects into separate projects. As of version 1.2.15, they will have their own tracking.
		- Made a slight change in the default "weekday" sort. Won't make a difference anywhere but NYC, maybe. The "neighborhood" now sorts after start time.
		
	- October 31, 2009 -1.2.14 Release
		- Added a new behavior for the search map. If the map starts off with a zoom less than 8, the first click brings the zoom to 8, then the next click finds meetings. This helps for maps that cover wide areas. It shouldn't affect any of the implementations to date.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2888335&group_id=228122&atid=1073410">a bug</a>, in which the overlay did not properly follow the marker in advanced search mode.
		- Made "Location" into "Location Name", and "Location Information" into "Additional Location Information" for the default meeting data fields. Bit more self-explanatory.
		- Added a language parameter for the standalone satellite server.
		- Removed the "Secondary Parent" capability. The code is still there, and commented out, but it was too confusing. A basic hierarchy, with assigned additional editors is much simpler.
		- Fixed <a href="https://sourceforge.net/tracker/?func=detail&aid=2889829&group_id=228122&atid=1073410">a bug</a>, in which setting a "Parent" Service Body to none failed.
		
	- October 27, 2009 -1.2.13 Release
		- Fixed <a href="https://sourceforge.net/tracker/?func=detail&atid=1073410&aid=2886043&group_id=228122">a bug</a> in the main server and the standalone satellite, in which the advanced pane could interfere with the basic pane.
		- Added a circular overlay to the Advanced Search map. Makes it much more usable.
		
	- October 25, 2009 -1.2.12 Release
		- Changed the title strings (tool tips) for the map markers to give information about the weekdays, and added one for the center marker.
		- Forgot to close a &lt;label&gt; element in the advanced search screen. It is now closed.
		- Simplified this doc page slightly, and added a header.
		- Spruced up all the Doxygen-generated docs.
		
	- October 23, 2009 -1.2.11 Release
		- Fixed a couple of JavaScript bugs that showed up in good ol' IE8. We can always rely on IE to throw a monkey wrench into the works.
		- Added an AJAX throbber to the meeting change submit button. The button becomes replaced by a throbber, until the form is refreshed.
		- Tweaked the default styling for the Advanced Search Type popup to better mesh with the rest of the screen.
		- The new map marker now pans to the marker.
		- Updated the Advanced Search documentation to mention the map.
		
	- October 20, 2009 -1.2.10 Release
		- Made the contact form textarea slightly smaller, in order to prevent an ugly "stretch."
		- Now make absolutely sure that there's something to write out for location info, so empty parentheses aren't shown.
		- Changed the background color of the contact form to be a bit more consistent with its container.
		- Fixed a broken div container in the main root server interface (did not cause any problems, but we HATES invalid pages. HATESSSS them. NASSSTY invalid pages &lt;gollum/&gt;).
		- Made the Advanced Search tab able to use the map. This is big juju.
		- Made the "No Results" display on the root server show up as white.
		- There was a problem with PDF dumps being corrupted by embedded linefeeds. The BMLT replaces linefeeds with "; " now.
		
	- October 18, 2009 -1.2.9 Release
		- Fixed a minor IE6 bug, in which the response to the contact form submission would trigger a JS error.
		- Made the center marker of a single meeting details display the black center marker, and now ensure that it is not clickable. This was a usability issue.
	
	- October 17, 2009 -1.2.8 Release
		- Fixed a bug that was causing issues with the Contact form of the FSRNA server.
		- Further simplified the "Single Meeting" URL.
		- Fixed a warning in the contact sent screen.
		- Fixed a bug in which Meeting ID searches weren't being done properly (This really only affects administrative use).
		- Made it so that a single meeting ID search when logged in as an admin, results in a list search, as opposed to a single meeting search. This is because you can't edit meetings in single meeting mode. The list search is a list of one meeting.
		- Changed the contact email to have a direct link to the root server, instead of the client satellite.
		
	- October 8, 2009 -1.2.7 Release
		- Fixed a bug in the satellite server that caused problems when doing list searches from the advanced search.
		- Fixed a bug in the "More Details" view, in which the GPS POI download link was broken.
		- Fixed an annoying habit the tabs in the search spec had of toggling the map visibility.

	- October 6, 2009 -1.2.6 Release
		- Removed some more extraneous parameters from the "one meeting" URL. It should be as simple as possible.
		- Fixed the styling of the center marker info window.
		- Fixed a bug in the root server installer wizard that produced a broken config file (Sorry).
		- Fixed an issue with the way the standalone satellite does its PDF dumps.
	
	- October 3, 2009 -1.2.5 Release
		- Fixed a bug, in which a certain filtered map search list view link did not work.
		- Fixed some bugs in the satellite implementations of the contact form.
		- Did some formatting work to allow more flexibility with the list display.
		- Added the ability to specify 'advanced' in the 'start_view' parameter, which will open the search specification in the Advanced Tab.
		
	- October 1, 2009 -1.2.4 Release
		- Fixed a VERY strange Joomla! bug. If there is a multi-page reply in list form, then Joomla croaks when it hits a 'style="display:none"' in one of the two hidden &lt;div&gt; elements at the top of the page. I worked around it by setting the hides via JS. What a kludge...
		
	- September 26, 2009 -1.2.3 Release
		- Adjusted the XML output for the Service Body query, in order to accommodate CMS plugin satellites.
		- Fixed a bug in which the class specifier for no results was broken.
		- Added a class definition for no results.
		
	- September 17, 2009 -1.2.2 Release
		- Fixed a bug, in which the AJAX handler could fail on some implementations (most embarrassingly, the demo implementation for our satellite server).
		
	- September 14, 2009 -1.2.1 Release
		- Added a link to a Service Body site, if a URI is provided. This is in the meeting details form.
		- Added the capability to specify "pre-checked" Service Body checkboxes in the main server Advanced Search tab. However, we still need to add support for this in the plugin satellites.
		
	- September 7, 2009 -1.2 Release
		- Added a US-Letter-Sized list option to the printable PDF.
		- Fixed a bug in which clicking on page numbers in the list resulted in an unexpected return to map view.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2845523&group_id=228122&atid=1073410">a bug</a>, in which entering ampersand (&) characters in any of the fields in the editors caused pretty big problems.
		- Fixed <a href="https://sourceforge.net/tracker/?func=detail&aid=2792463&group_id=228122&atid=1073410">a bug</a>, in which deleted Service bodies with nested components weren't updated properly. The 'fix' is kind of a kludge, as we simply reload the page.
		
	- September 7, 2009 -1.2b2 Release
		- Added a "busy throbber" to the AJAX contact form, and fixed a couple of bugs that only showed when bad input was entered for the form.
		- Made the booklet a true "chapbook" size, and started work on a full US letter list.
		
	- September 6, 2009 -1.2b1 Release
		- Made the booklet PDF use a two-column format.
		- Added a "Contact Us" email form to each meeting, allowing people to send an email to the administrator for a given meeting.

	- September 1, 2009 -1.2b0 Release
		- Added support for a basic, standalone-satellite-based PDF generator (booklet).

	- August 31, 2009 -1.1.2 Release
		- Moving all documentation, including the user and admin manual, into the docs directory, and created a frameset to display them.
		- Tweaked the Doxygen docs to reflect the new structure.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2846972&group_id=228122&atid=1073410">a bug</a>, in which the list wasn't working in Map view.
		- Fixed an unreported bug, in which zooming the map caused JavaScript slowdowns.
		
	- July 26, 2009 -1.1.1 Release
		- Added printout handlers for the six New York meeting lists.
		- Added some base classes and handlers to the satellite server to afford on-the-fly PDF generation.
		- Split up the project structure: Moving the Drupal, WordPress and Joomla! modules off into their own projects. The main satellite server will now only be the standalone and PDF generators.
		- Modified the CSV data export to better accommodate list printing.
		- Added the ability to do searches, based upon a single key/value (example: county or town).
		- Fixed <a href="https://sourceforge.net/tracker/?func=detail&aid=2827219&group_id=228122&atid=1073410">a bug</a>, in which map searches with filters didn't work properly when called from a satellite server.
		- Marked <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2791321&group_id=228122&atid=1073410">a bug</a> as closed. Not sure how it was fixed (Hate it when that happens).
		- The current server version is now discretely displayed at the bottom, right corner of the search specification screen for the main server.
		- Optimized the standalone satellite server to be even more crazy simple to implement, and wrote up some detailed docs on it.
		
	- July 6, 2009 -1.1 Release
		- Added the ability to dump results as a CSV file. If you are logged in as an admin (to the Root Server), you will have a new choice for return format: CSV file.
		- Added the ability to retrieve formats as a CSV file as well.
		
	- June 26, 2009 -1.0.1 Release
		- Made the satellite servers use POST for their cURL calls. Increases the robustness a bit.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2809650&group_id=228122&atid=1073410">a bug</a>, in which meeting rollbacks didn't work, and re-enabled the change rollback system and meeting undelete system.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2812707&group_id=228122&atid=1073410">a bug</a>, in which the meeting edit functions did not work in Map View.
		
	- June 25, 2009 -1.0.0 Release
		- Tweaked the WordPress plugin to prevent it from slowing down the whole site.
		
	- June 23, 2009 -1.0RC6 Release
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2810067&group_id=228122&atid=1073410">a bug</a>, in which singular meeting results caused problems with administration.
		- Added a new field to the three CMS plugins, that allows you to specify a specific URL for the "New Search" link.
		- Fixed an old bug in the Joomla! plugin that prevented the support older browsers functionality from working properly.
		
	- June 21, 2009 -1.0RC5 Release
		- Removed the Spanish localization. It was not complete, and was only there for testing. It should not be distributed.
		- Added a couple of notes to the <a href="../main_server/AdminManual/">Admin Manual</a> about the user interface issues that affect creating new users or Service Bodies, or deleting Service Bodies with nested Service Bodies.
		- Broke the styles out from inside the WordPress plugin, and put them in their own stylesheet. This makes tweaking them easier.
		- Made the styles for the WordPress plugin far more specific, in an effort to enforce the appearance in a chaotic environment.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2809609&group_id=228122&atid=1073410">a bug</a>, in which the '&' character caused issues with editing meetings. Addressed the same issue with format editing.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2809622&group_id=228122&atid=1073410">a bug</a>, in which the Edit Formats AJAX functionality was broken.
		- Addressed an issue in which slashes could be added to text field value display in the meeting editor.
		- Disabled the change rollback feature for meetings. It isn't working properly, and the fix would be too drastic at this stage.
		
	- June 16, 2009 -1.0RC4 Release
		- Changed the name of the WordPress plugin, as it is now available from <a href="http://wordpress.org/extend/plugins/bmlt-wordpress-satellite-plugin/">WordPress.org</a>. This means that any prior installs need to first, deactivate, then reinstall and reactivate the new plugin. However, after this, it will be able to be auto-updated.
		- Fixed a reported bug, in which some of the files used the wrong line-endings.
		
	- June 14, 2009 -1.0RC3 Release
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2806255&group_id=228122&atid=1073410">a bug</a>, in which the "Search for Text" link in the search specifier was broken for some browsers (notably, IE6).
		
	- June 13, 2009 -1.0RC2 Release
		- Tweaked the CSS for the info windows in the map results page to lessen the "overflow" caused by resized text.
		- Fixed a minor bug in the WordPress plugin, in which the tbody element wasn't being properly closed.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2805723&group_id=228122&atid=1073410">a bug</a>, in which the throbber wasn't being displayed properly in the admin control panel.
		- Removed some redundant code in the admin files. The dangers of copy-and-paste programming...
		- Added a note to the last page of the install wizard, instructing the server admin to create first a Service Body Admin, then a Service Body, then meetings.
		- Added a style to the satellite implementations to ensure that the single meeting header starts off at a manageable size.
		- Slightly tweaked the CSS in the WordPress style to keep the "New Search" menu centered.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2805918&group_id=228122&atid=1073410">a bug</a>, in which a long/lat of 0 was considered a valid location, even though it is the ocean off of Africa. 0,0 is now considered an empty long/lat.
		
	- June 11, 2009 -1.0RC1 Release
		- Added a bit of CSS padding to the fieldsets in the Advanced Search.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2803269&group_id=228122&atid=1073410">a bug</a> in the WordPress plugin, in which the "New Search" link was bad for default WordPress (non-pretty URIs) installations.
		- Added a link to the root server in the case that JavaScript is required by satellite servers.
		
	- June 7, 2009 -1.0b5 Release
		- Vastly improved the code for the standalone satellite server.
		- Added the capability for satellite servers to override the root server setting, and determine the initial view for their Basic Search.
		- Made the call_curl function a wee bit more robust.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2800165&group_id=228122&atid=1073410">a bug</a>, in which we need to append the state to the town in List View.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2802511&group_id=228122&atid=1073410">a bug</a>, in which the map view kept being closed whenever the search was switched between Basic and Advanced.
		- Completed the Administration Guide.
		
	- June 5, 2009 -1.0b4 Release
		- Added a shortcut icon.
		- Fixed <a href="https://sourceforge.net/tracker/index.php?func=detail&aid=2801941&group_id=228122&atid=1073410">a bug</a> in WebKit browsers, where the initial map in Basic mode comes up skewed.
		- Wrote an installer "wizard" to make installing a new root server fairly simple.
		- Bundled a "raw" root server into a single <a href="http://comdef.svn.sourceforge.net/viewvc/comdef/tags/1.0b4/main_server.zip">downloadable zip file</a>.
		
	- June 1, 2009 -1.0b3 Release
		- Fixed an admin bug that prevented a user from changing their own password or login.
		- Added an SQL file with a "Minimal" BMLT DB. The DB is called "minimal_bmlt", and the admin login is "admin" and the password is "minimal_bmlt".
		
	-  May 31, 2009 -1.0b2 Release
		- Fixed several bugs that only showed up when we tried to create a new top-level server.
		- Made the entire background of the local server dark.
		
	-  May 31, 2009 -1.0b1 Release
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
		- The <a href="http://comdef.svn.sourceforge.net/viewvc/comdef/trunk/satellite_server/drupal/">Drupal Module</a> is done.
		- The <a href="http://comdef.svn.sourceforge.net/viewvc/comdef/trunk/satellite_server/joomla/">Joomla Module</a> is done.
		- The <a href="http://comdef.svn.sourceforge.net/viewvc/comdef/trunk/satellite_server/wordpress/">WordPress Module</a> is done.
		- Rearranged the location of the submit button in the various plugin admin pages.
		- Removed a server call in the initial form that slowed down the display of the form by about a second and a half.
		- Added a useful profiler class to the utilities.
		
	- May 21, 2009 -1.0a5 Release
		- Did a lot of rearranging of the styles and contexts for the local server. This makes it more useful as a standalone editing context.
		- Fixed some bugs in the presentation of the "Deleted Meetings" panel in the control panel.
		- The frameset was raised above the main server. The main_server directory now comprises a full administrative site application.
		- Moved the simple PHP for the satellite server up a level, and set the configuration to reach over to the main server. This makes the satellite_server directory a full standalone meeting search for the local server.
		- Fixed a bug that caused bad links to single meetings in the headers of details opened from list view.
		- Added full options for the Joomla! component. It now works in exactly the same way as does the WordPress plugin.
		- Improved the Joomla! component styling. The Joomla! component is definitely on a par with the WordPress component, which had been ahead of it.
		- Fixed a couple more minor issues in the WordPress plugin.
		
	- May 17, 2009 -1.0a4 Release
		- Fixed some formatting issues with the WordPress plugin. The plugin is now enclosed in a table, which enhances the robustness and independence of the plugin.
		- Fixed a bug in the WordPress plugin that didn't record zoom changes properly in the admin
		- Removed the "Display Map Search As Whole Page" option from the WordPress plugin. Requires too much CSS intervention on the part of the implementor.
		- Found and fixed an unnoticed bug that would affect attempts to localize the server.
		- Added an "admin bar" that allows access to the various admin areas when logged in as an admin.
		- Fixed a number of JavaScript issues for logged-in admins.
		- Fixed some IE6 issues (STILL NOT FIXED: Clicking a Region checkbox in Advanced Search does not visibly affect enclosed checkboxes).
		- Added the meta tag <a href="http://msdn.microsoft.com/en-us/library/cc817574.aspx">as recommended by Microsoft</a>, to correct for IE8 deficiencies.
		
	- May 15, 2009 -1.0a3 Release
		- Fixed a few WAI AAA issues.
		- Fixed a couple of XHTML 1.0 Strict violations.
		- Do an isset() check on the cookie in the login to prevent warnings.
		- Made sure <a href="http://icab.de">iCab</a> smiles on every page.
		- <a href="https://sourceforge.net/tracker/?func=detail&aid=2791909&group_id=228122&atid=1073410">Removed the Google Bar</a>. They're gonna stick ads on it, so bye-bye.
		- Fixed a bug in which the GPS POI wasn't downloading correctly for the single meeting display.
		- An older version text editor must have polluted many files with garbage characters. This is fixed.
		- Fixed some styling issues for browsers with JS disabled.
		- Added the <a href="http://msdn.microsoft.com/en-us/library/cc817574.aspx">X-UA-Compatible Meta Tag</a> to the local server.
		
	- May 14, 2009 -1.0a2 Release
		- Fixed <a href="http://sourceforge.net/tracker/?func=detail&aid=2791387&group_id=228122&atid=1073410">Bug in Meeting Administration, where meetings need to reflect changes in List View</a>
		- Fixed <a href="http://sourceforge.net/tracker/?func=detail&aid=2791843&group_id=228122&atid=1073410">Bug in Meeting Administration, where the weekday popup menu set the wrong weekday</a>
		- Added the first iteration of the <a href="http://magshare.magnaws.com/comdef/UserManual/">User Guide</a>

	- May 11, 2009 -1.0a1 Release
*/
?>