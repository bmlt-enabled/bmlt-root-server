<?php
/***********************************************************************/
/** \file	reports.php

	\brief	Displays a dropdown panel of various reports for admins.

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
	defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

	require_once ( dirname ( __FILE__ ).'/../server/c_comdef_server.class.php' );

/*******************************************************************/
/** \brief	This returns the HTML for the reports area of the admin control panel. It acts as a "wrapper," and imports individual panels.
	
	\returns display-ready HTML for the dropdown panel.
*/
function DisplayReportsDiv ( $in_http_vars	///< The $_GET and $_POST variables, in an associative array.
							)
	{
	$localized_strings = c_comdef_server::GetLocalStrings();

	$ret = "<div id=\"reports_div_container_div_id\" class=\"reports_div_closed\"><a class=\"reports_a\" href=\"javascript:ToggleReportsDiv()\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Reports']['reports_div_title'] ).$localized_strings['prompt_delimiter']."</a></div>";
	$ret .= '<div id="reports_div_id" class="reports_div" style="display:none"></div>';
	
	return $ret;
	}
?>