<?php
/***********************************************************************/
/** 	\file	search_results_list_ajax.php

	\brief	This file is called from the AJAX handler, and returns the
	XHTML needed to display the data for one single meeting.

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
    along with this code.  If not, see <http://www.gnu.org/licenses/>.
*/
	define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
	require_once ( dirname ( __FILE__ ).'/search_results_single_meeting.php' );
	$ret = DisplayOneMeeting ( $_GET );
	if ( zlib_get_coding_type() === false )
		{
			ob_start("ob_gzhandler");
		}
		else
		{
			ob_start();
		}

	echo $ret;
	ob_end_flush();
?>