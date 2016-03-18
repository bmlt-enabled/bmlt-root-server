# README #

This is a project to create a "living document," so to speak, for the [BMLT's semantic capabilities](http://bmlt.magshare.net/semantic/).

It will present an interactive "worksheet" that can be used to construct a URL (or shortcode) to present alternative BMLT interfaces.

View a live demo at: http://bmlt.magshare.net/workshop/

This file is part of the Basic Meeting List Toolbox (BMLT).

Find out more at: http://bmlt.magshare.org

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
** Version 1.0.7 **

- TBD

- Fixed a warning.

** Version 1.0.6 **

- March 13, 2016

- Adding better capability for SSL, cleaned URIs and integration with the Root Server.

** Version 1.0.5 **

- March 5, 2016

- Tweaked the project to allow embedding in the main server.

** Version 1.0.4 **

- September 18, 2015

- Fixed the URL/Shortcode display to better form itself around long URIs.

** Version 1.0.3 **

- July 2, 2015

- Corrected the text describing the start times.

** Version 1.0.2 **

- July 1, 2015

- Added a link to the documentation page.

** Version 1.0 **

- April 24, 2015

- First official Release.

** Version 1.0b3 **

- April 24, 2015

- Now don't display the Service body sections if only 1 Service body..

** Version 1.0b2 **

- April 11, 2015

- This tool won't work on Root Server versions below 2.6.15. I now test for that.

** Version 1.0b1 **

- April 11, 2015

- Oops. Forgot to add the start time/duration sections.

** Version 1.0b0 **

- April 9, 2015

- First Beta release. No code change from 1.0a6, except that we are no longer running debug mode.

** Version 1.0a6 **

- April 9, 2015

- Made the NOT sections XOR with the "NOT-NOT" sections.

** Version 1.0a5 **

- April 8, 2015

- Added the "NOT" Service body section.

** Version 1.0a4 **

- April 8, 2015

- Added the weekday header code for the [[BMLT_SIMPLE]] shortcode search results.
- Fixed an issue where the Service body and format labels were not being properly associated with their checkboxes.
- Changed the way that Service bodies are recorded in the state, with an eye towards adding "not" Service bodies (it is possible, but not not reflected in the UI).

** Version 1.0a3 **

- April 4, 2015

- Fixed an issue with the way the field values checkboxes were set up in the meeting search fieldset. The labels were not properly hooked up to the checkboxes.

** Version 1.0a2 **

- March 29, 2015

- Fixed an issue with the sort selection, where unsorting the first item with more than 3 items would not re-flow the sort order.
- Make the selected sort items more prominent with CSS.
- Added a JavaScript noscript declaration.
- Added some styling to the basic URL entry form.
- The URL entry form now allows return-key submit.
- Prevent sort items from doing anything other than unsorting if they already have a value.
- Added a header and a basic instruction blurb.

** Version 1.0a1 **

- March 28, 2015

- First official "feature complete" alpha.
