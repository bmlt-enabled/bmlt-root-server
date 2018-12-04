<?php
/***********************************************************************/
/**     \file   client_interface/poi/index.php

    \brief  This file is a very simple interface that is designed to return
    a POI CSV (Comma-Separated Values) string, in response to a search.
    In order to use this, you need to call: <ROOT SERVER BASE URI>/client_interface/poi/
    with the same parameters that you would send to an advanced search. The results
    will be returned as an POI CSV file.

    This file can be called from other servers.

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

defined('BMLT_EXEC') or define('BMLT_EXEC', true); // This is a security verifier. Keeps files from being executed outside of the context
require_once(dirname(__FILE__).'/../csv/csv.php');

try {
    $server = c_comdef_server::MakeServer();
    $ret = null;
    
    if ($server instanceof c_comdef_server) {
        $_GET['switcher'] = 'GetSearchResults';
        unset($_GET['xml_data']);
        unset($_GET['gpx_data']);
        unset($_GET['kml_data']);
        $_GET['poi_data'] = true;
        $_GET['data_field_key'] = 'longitude,latitude,location_city_subsection,location_neighborhood,location_province,location_nation,location_postal_code_1,location_text,location_street,location_info,location_municipality,meeting_name,weekday_tinyint,start_time';
        $ret = parse_redirect($server);
        // The caller can request compression. Not all clients can deal with compressed replies.
        if (isset($_GET['compress_output']) || isset($_POST['compress_output'])) {
            if (zlib_get_coding_type() === false) {
                ob_start("ob_gzhandler");
            } else {
                header('Content-Type:text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="SearchResultsPOI.csv"');
                ob_start();
            }
        } else {
            header('Content-Type:text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="SearchResultsPOI.csv"');
            ob_start();
        }
        
        echo $ret;
        ob_end_flush();
    } else {
        echo HandleNoServer();
    }
} catch (Exception $e) {
    echo HandleNoServer();
}
