<?php
/***********************************************************************/
/** \file	format_codes.inc.php
	\brief	The format codes for this language (English)
	
	This sets the global array to the various format type codes, and their descriptions.
	You should keep the types ("FCX"), but change the descriptions to your language.
    
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
	defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

	$comdef_format_types = array (
									/// English text for Format codes.
									"FC1"=>"Mötestypskod",			/**< Mötestyp (Talarmöte, Stegarbetsmöte, etc.)  */
									"FC2"=>"Mötesinfokod",			/**< Location Code (Wheelchair Accessible, Limited Parking, etc.)  */
									"FC3"=>"Närvaro begränsning"	/**< Attendance Restriction (Men Only, Closed, Open, No Children, etc.)  */
									);
?>