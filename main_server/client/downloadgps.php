<?php
/************************************************************************************/
/**
	\file downloadgps.php
	\brief This is a simple file that turns input into a GPS POI (CSV) file.
	
	It outputs data in <a href="http://www.gpsbabel.org/htmldoc-development/fmt_unicsv.html">Universal CSV (GPS) Format</a>.
	
	Input is via the $_GET and/or $_POST facility. This file combines them.
	
	\param lat A floating-point number. The latitude of the point, in degrees.
	\param lng A floating-point number. The longitude of the point, in degrees.
	\param name A string. The name of the point (Keep it short. It is used as a filename).
	\param desc A string. A description of the point.
	
	\returns A CSV (Comma-Separated-Values) file, with the following two lines:
		- Line 1: The header, in Universal CSV form.
		- Line 2: The POIN data for this point.
		
	The returned file is returned with a "text/csv" content-type, which usually results in
	a download, as it is returned as an "attachment," as opposed to inline.

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
	$http_vars = array_merge_recursive ( $_GET, $_POST );
	$lng = floatval ( $http_vars['lng'] );
	$lat = floatval ( $http_vars['lat'] );
	$name = $http_vars['name'];
	$desc = $http_vars['desc'];
	
	if ( !$name )
		{
		$name = "GPS Point of Interest";
		}

	$filename = preg_replace ( "/[^a-zA-Z0-9\-_\.]+/", "_", preg_replace("/[\"']+/","",$name ) );
	
	header ( "Content-type: text/csv" );
	header ( 'Content-Disposition: attachment; filename="'.$filename.'.csv"');
	echo "lon,lat,name,desc\n";
	echo $lng.','.$lat.',"'.$name.'","'.$desc.'"'."\n";
?>
