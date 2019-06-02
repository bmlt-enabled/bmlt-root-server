# README #

This is a project to create a "living document," so to speak, for the [BMLT's semantic capabilities](https://bmlt.app/semantic/).

It will present an interactive "worksheet" that can be used to construct a URL (or shortcode) to present alternative BMLT interfaces.

View a live demo at: https://bmlt.app/workshop/

This file is part of the Basic Meeting List Toolbox (BMLT).

Find out more at: https://bmlt.app

License
-------

BMLT is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.

BMLT is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this code.  If not, see <http://www.gnu.org/licenses/>.

CHANGELIST
----------
***Version 1.3.4* ** *- December 14, 2018*

- When creating URLs for static content, the HTTP_X_FORWARDED_PROTO header is now inspected for determining protocol.
- Added sorting to service bodies and formats.

***Version 1.3.3* ** *- November 11, 2017*

- Added a change to the reported UA for the cURL call. Some servers' security software might have issues with the original one.

***Version 1.3.2* ** *- September 24, 2017*

- Changed "escape" to "encodeURIComponent" in order to resolve issues with non-Roman character sets.

***Version 1.3.1* ** *- May 18, 2017*

- Added a setting to the cURL caller to allow it to work in SSL on IIS servers.

***Version 1.3.0* ** *- May 9, 2017*

- Added support for the new Coverage Area option, which includes a map display of the coverage area.

***Version 1.2.1* ** *- March 31, 2017*

- The Google Maps API include needed to be SSL.

***Version 1.2.0* ** *- January 5, 2017*

- Added support for the new "Ends Before" time parameter.

***Version 1.1.4* ** *- December 11, 2016*

- Fixed a bug where certain fields were not being properly hidden, and certain menu values were left enabled when they should have been disabled, when the [[BMLT_TABLE]] shortcode was selected.
- Added support for the Google SAPI key in the embedded (in the Root Server) version.

***Version 1.1.3* ** *- May 2, 2016*

- Tweaked the format of this README to match the rest of the projects.
- Added [Doxygen](http://doxygen.nl) documentation.

***Version 1.1.2* ** *- April 6, 2016*

- The weekday boxes were not being re-shown after the table section was displayed.

***Version 1.1.1* ** *- April 4, 2016*

- Fixed an issue where the specific fields list was shown for simple response. It is not actually valid, there.

***Version 1.1.0* ** *- March 29, 2016*

- Added support for the new [[BMLT_TABLE]] shortcode.

***Version 1.0.11* ** *- March 27, 2016 (Happy Easter!)*

- Added the block mode checkbox to the shortcode searches.

***Version 1.0.10* ** *- March 23, 2016*

- Fixed a JavaScript bug, in which Root Servers with reduced complements of additional meeting data could cause the sort stuff to reference null objects.

***Version 1.0.9* ** *- March 20, 2016*

- Fixed a minor bug with the new server langs, where an invalid display could happen (Select "Server Languages" in XML or JSON, then switch to a different output type).

***Version 1.0.8* ** *- March 20, 2016*

- Added the JSON version of GetServerLangs.

***Version 1.0.7* ** *- March 18, 2016*

- Fixed a warning.
- Added a selection for the XML GetLangs response.
- Fixed an issue with the Root Server not having SSL honored.

***Version 1.0.6* ** *- March 13, 2016*

- Adding better capability for SSL, cleaned URIs and integration with the Root Server.

***Version 1.0.5* ** *- March 5, 2016*

- Tweaked the project to allow embedding in the main server.

***Version 1.0.4* ** *- September 18, 2015*

- Fixed the URL/Shortcode display to better form itself around long URIs.

***Version 1.0.3* ** *- July 2, 2015*

- Corrected the text describing the start times.

***Version 1.0.2* ** *- July 1, 2015*

- Added a link to the documentation page.

***Version 1.0* ** *- April 24, 2015*

- First official Release.

***Version 1.0b3* ** *- April 24, 2015*


- Now don't display the Service body sections if only 1 Service body..

***Version 1.0b2* ** *- April 11, 2015*


- This tool won't work on Root Server versions below 2.6.15. I now test for that.

***Version 1.0b1* ** *- April 11, 2015*


- Oops. Forgot to add the start time/duration sections.

***Version 1.0b0* ** *- April 9, 2015*


- First Beta release. No code change from 1.0a6, except that we are no longer running debug mode.

***Version 1.0a6* ** *- April 9, 2015*


- Made the NOT sections XOR with the "NOT-NOT" sections.

***Version 1.0a5* ** *- April 8, 2015*


- Added the "NOT" Service body section.

***Version 1.0a4* ** *- April 8, 2015*


- Added the weekday header code for the [[BMLT_SIMPLE]] shortcode search results.
- Fixed an issue where the Service body and format labels were not being properly associated with their checkboxes.
- Changed the way that Service bodies are recorded in the state, with an eye towards adding "not" Service bodies (it is possible, but not not reflected in the UI).

***Version 1.0a3* ** *- April 4, 2015*


- Fixed an issue with the way the field values checkboxes were set up in the meeting search fieldset. The labels were not properly hooked up to the checkboxes.

***Version 1.0a2* ** *- March 29, 2015*


- Fixed an issue with the sort selection, where unsorting the first item with more than 3 items would not re-flow the sort order.
- Make the selected sort items more prominent with CSS.
- Added a JavaScript noscript declaration.
- Added some styling to the basic URL entry form.
- The URL entry form now allows return-key submit.
- Prevent sort items from doing anything other than unsorting if they already have a value.
- Added a header and a basic instruction blurb.

***Version 1.0a1* ** *- March 28, 2015*


- First official "feature complete" alpha.
