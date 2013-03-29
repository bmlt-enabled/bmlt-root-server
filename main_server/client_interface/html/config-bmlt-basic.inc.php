<?php
/****************************************************************************************//**
*   This file is part of the Basic Meeting List Toolbox (BMLT).                             *
*                                                                                           *
*   Find out more at: http://bmlt.magshare.org                                              *
*                                                                                           *
*   BMLT is free software: you can redistribute it and/or modify                            *
*   it under the terms of the GNU General Public License as published by                    *
*   the Free Software Foundation, either version 3 of the License, or                       *
*   (at your option) any later version.                                                     *
*                                                                                           *
*   BMLT is distributed in the hope that it will be useful,                                 *
*   but WITHOUT ANY WARRANTY; without even the implied warranty of                          *
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                           *
*   GNU General Public License for more details.                                            *
*                                                                                           *
*   You should have received a copy of the GNU General Public License                       *
*   along with this code.  If not, see <http://www.gnu.org/licenses/>.                      *
********************************************************************************************/

$config_file_path = dirname ( __FILE__ ).'/../../server/config/get-config.php';
$url_path = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/../..';
if ( file_exists ( $config_file_path ) )
    {
    include ( $config_file_path );
    }

require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
require_once ( dirname ( __FILE__ ).'/../../local_server/db_connect.php');

DB_Connect_and_Upgrade ( );

$server = c_comdef_server::MakeServer();

if ( $server )
    {
    $localized_strings = c_comdef_server::GetLocalStrings();
    global $bmlt_basic_configuration;       ///< These are used by the bmlt_basic class. Don't mess with them.
    global $bmlt_basic_configuration_index;

    $bmlt_basic_configuration = array();    ///< The configuration will be held in an array of associative arrays.
    $bmlt_basic_configuration_index = 0;

    $bmlt_basic_configuration[$bmlt_basic_configuration_index] = array (
        'root_server'                   =>          'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/../..', 
        'map_center_latitude'           =>          floatval ( $localized_strings['search_spec_map_center']['latitude'] ),
        'map_center_longitude'          =>          floatval ( $localized_strings['search_spec_map_center']['longitude'] ),
        'map_zoom'                      =>          floatval ( $localized_strings['comdef_server_admin_strings']['meeting_editor_default_zoom'] ),
        'bmlt_initial_view'             =>          'text',
        'distance_units'                =>          $localized_strings['dist_units'],
        'bmlt_location_checked'         =>          1,  /* Set this to 1 if you want the "This is a Location or Postcode" box to be checked on by default.      */
        'bmlt_location_services'        =>          1,  /* Set this to 1 if you want the location ("Find Near Me") services only available for mobile devices.  */
        'theme'                         =>          'BlueAndWhite',
        'grace_period'                  =>          15, /* How many minutes are allowed to go by before a meeting is considered "too late."                     */
        'time_offset'                   =>          0,  /* Generally left at 0 hours. If the server has a different time offset from this, indicate it here.    */
        'id'                            => $bmlt_basic_configuration_index + 1  /* Don't mess with this one. */
    );

    $bmlt_basic_configuration_index++;
    }
?>