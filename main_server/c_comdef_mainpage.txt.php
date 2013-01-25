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
    <pre>
    \verbinclude ../README
    </pre>
*/
?>