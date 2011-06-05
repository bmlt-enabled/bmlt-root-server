<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://magshare.org/bmlt

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this code.  If not, see <http://www.gnu.org/licenses/>.*/
global $g_weekday_names;

$g_weekday_names = array ( "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" );

define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context

defined ( "_DEFAULT_DURATION") or define ( "_DEFAULT_DURATION", "N.A. Meetings are usually 60 minutes long (an hour), unless otherwise indicated." );
defined ( "_PDF_AUTHOR") or define ( "_PDF_AUTHOR", "Basic Meeting List Toolbox" );
defined ( "_PDF_CREATOR") or define ( "_PDF_CREATOR", "http://magshare.org/welcome-to-magshare/bmlt-the-basic-meeting-list-toolbox/" );
defined ( "_PDF_CONTD") or define ( "_PDF_CONTD", " (Continued)" );
defined ( "_PDF_LEGEND_HEADER") or define ( "_PDF_LEGEND_HEADER", "Meeting Format Codes" );
defined ( "_PDF_PAGE") or define ( "_PDF_PAGE", "Page" );
?>