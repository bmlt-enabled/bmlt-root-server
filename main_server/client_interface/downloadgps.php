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
    
    Find out more at: http://bmlt.magshare.org

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
	$point_array = isset ( $http_vars['multipoints'] ) && count ( $http_vars['multipoints'] );
    $gpx = isset ( $http_vars['gpx'] );
	
	if ( !$point_array )
	    {
	    $point_array[] = floatval ( $http_vars['lng'] ).','.floatval ( $http_vars['lat'] ).','.addcslashes ( $http_vars['name'], ',' ).','.addcslashes ( $http_vars['desc'], ',' ).','.addcslashes ( $http_vars['type'], ',' );
	    }
	else
	    {
	    $point_array = $http_vars['multipoints'];
	    }
	
    if ( $gpx )
        {
        $minlng = 361;
        $minlat = 361;
        $maxlng = -361;
        $maxlat = -361;
        
        foreach ( $point_array as $waypoint )
            {
            $waypoint = explode ( ',', $waypoint );
            $lng = floatval ( $waypoint[0] );
            $lat = floatval ( $waypoint[1] );
            
            $minlng = min ( $minlng, $lng );
            $minlat = min ( $minlat, $lat );
            $maxlng = max ( $maxlng, $lng );
            $maxlat = max ( $maxlat, $lat );
            }
        
        header ( "Content-type: application/xml" );
        header ( 'Content-Disposition: attachment; filename="'.$filename.'.gpx"');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<gpx version=\"1.0\" xmlns=\"http://".htmlspecialchars ( trim ( strtolower ( $_SERVER['SERVER_NAME'] ) ) )."\" xmlns:xsn=\"http://www.w3.org/2001/XMLSchema-instance\" xsn:schemaLocation=\"http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd\">";
        echo '<bounds minlat="'.htmlspecialchars ( $minlat ).'" minlon="'.htmlspecialchars ( $minlng ).'" maxlat="'.htmlspecialchars ( $maxlat ).'" maxlon="'.htmlspecialchars ( $maxlng ).'"/>';
        }
    else
        {
        header ( "Content-type: text/csv" );
        header ( 'Content-Disposition: attachment; filename="'.$filename.'.csv"');
        echo "lon,lat,name,desc\n";
        }
    
    reset ( $point_array );
    
	foreach ( $point_array as $waypoint )
	    {
	    $waypoint = str_replace ( '\,', '##-##', $waypoint );
	    $waypoint = explode ( ',', $waypoint );
        $waypoint[0] = floatval ( $waypoint[0] );
        $waypoint[1] = floatval ( $waypoint[1] );
	    $waypoint[2] = str_replace ( '##-##', ',', $waypoint[2] );
	    $waypoint[3] = str_replace ( '##-##', ',', $waypoint[3] );
	    $waypoint[4] = str_replace ( '##-##', ',', $waypoint[4] );
	    $lng = $waypoint[0];
	    $lat = $waypoint[1];
        $name = trim ( $waypoint[2] );
        $desc = trim ( $waypoint[3] );
        $type = trim ( $waypoint[4] );
    
        if ( !$name )
            {
            $name = "NA Meeting";
            }

        if ( !$desc )
            {
            $desc = "NA Meeting";
            }

        if ( !$type )
            {
            $type = "NA Meeting";
            }

        $filename = preg_replace ( "/[^a-zA-Z0-9\-_\.]+/", "_", preg_replace("/[\"']+/","",$name ) );
    
        if ( $gpx )
            {
            echo '<wpt lat="'.htmlspecialchars ( $lat ).'" lon="'.htmlspecialchars ( $lng ).'">';
                echo '<name><![CDATA['.htmlspecialchars ( $name ).']]></name>';
                echo '<desc><![CDATA['.htmlspecialchars ( $desc ).']]></desc>';
                echo '<type><![CDATA['.htmlspecialchars ( $type ).']]></type>';
                echo '<sym>Diamond, Blue</sym>';
            echo '</wpt>';
            }
        else
            {
            echo $lng.','.$lat.',"'.$name.'","'.$desc.'"'."\n";
            }
        }
    
    if ( $gpx )
        {
        echo '</gpx>';
        }
?>
