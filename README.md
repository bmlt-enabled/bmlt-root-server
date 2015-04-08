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
** Version 1.0a5 **

- TBD

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
