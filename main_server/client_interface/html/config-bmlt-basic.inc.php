<?php
/****************************************************************************************//**
*   \file   config-bmlt-basic.inc.php                                                       *
*                                                                                           *
*   \brief  This file contains the basic configuration directives for the standalone client.*
*   \version 3.0                                                                            *
*                                                                                           *
*   This file comes with the sample set for the Greater New York Region BMLT Server, which  *
*   is run by the same people that designed the BMLT, so it can be considered "Home Field." *
*                                                                                           *
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

/*************************************************************************************************************************************************************
*############################################################# DON'T CHANGE BELOW THIS LINE #################################################################*
*VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV*
*************************************************************************************************************************************************************/

global $bmlt_basic_configuration;       ///< These are used by the bmlt_basic class. Don't mess with them.
global $bmlt_basic_configuration_index;

$bmlt_basic_configuration = array();    ///< The configuration will be held in an array of associative arrays.
$bmlt_basic_configuration_index = 0;

$bmlt_basic_configuration[$bmlt_basic_configuration_index] = array (

/*************************************************************************************************************************************************************
*^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^*
*############################################################# DON'T CHANGE ABOVE THIS LINE #################################################################*
*************************************************************************************************************************************************************/

/*************************************************************************************************************************************************************
*################################################################# CHANGE BELOW THIS LINE ###################################################################*
*VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV*
**************************************************************************************************************************************************************

/*************************************************************************************************************************************************************
*                                                                                                                                                            *
* Make sure that each of the lines below ends with a comma (,). The script will fail, otherwise.                                                             *
*                                                                                                                                                            *
**************************************************************************************************************************************************************
*                                                                       SETTINGS                                                                             *
*                                                                                                                                                            *
* These control the settings used by the displayed shortcodes. It is possible to have more than one setting, but that is beyond the scope of this simple     *
* example.                                                                                                                                                   *
**************************************************************************************************************************************************************
*   SETTING NAME (Don't Change)                     SETTING VALUE (You can change these)                                                                     *
*************************************************************************************************************************************************************/

    /********************************************************************************************************************************************************/
    /** This is the root server URL. The root server must be a minimum version 1.10.3 in order for the '[[BMLT]]' shortcode to work.                        */
    /** This affects all shortcodes.                                                                                                                        */
    /** The URL to put in here is displayed at the top of the root server main screen. Copy that, and add it here.                                          */
    /********************************************************************************************************************************************************/
    
    'root_server'                   =>          'http://bmlt.newyorkna.org/main_server', 
    
    /********************************************************************************************************************************************************/
    /** This tells the map in the '[[BMLT]]', '[[BMLT_MOBILE]]' and '[[BMLT_MAP]]' shortcodes where to set the map when the satellite is initialized.       */
    /********************************************************************************************************************************************************/
    
    'map_center_latitude'           =>          40.780281,
    'map_center_longitude'          =>          -73.965497,
    'map_zoom'                      =>          12,
    
    /********************************************************************************************************************************************************/
    /** This controls which view is displayed when the satellite first shows up. This only affects the '[[BMLT]]' shortcode.                                */
    /** Can be 'map', 'text', 'advanced_map' or 'advanced_text'                                                                                             */
    /********************************************************************************************************************************************************/
    
    'bmlt_initial_view'             =>          'map',

    /********************************************************************************************************************************************************/
    /** In the More Options ('[[BMLT]]') map display, the popup can show a radius. This controls the units used for that radius.                            */
    /** The '[[BMLT_MAP]]' and '[[BMLT_MOBILE]]' displays also shows distances, and this affects the units used for those.                                  */
    /** Can be 'mi' or 'km'.                                                                                                                                */
    /********************************************************************************************************************************************************/
    
    'distance_units'                =>          'mi',
    
    /********************************************************************************************************************************************************/
    /** These affect how a couple of basic services appear. The first one is the "location" checkbox. The other controls the three "quick search" buttons.  */
    /** These only affect the '[[BMLT]]' shortcode.                                                                                                         */
    /********************************************************************************************************************************************************/
    
    'bmlt_location_checked'         =>          0,  /* Set this to 1 if you want the "This is a Location or Postcode" box to be checked on by default.      */
    'bmlt_location_services'        =>          0,  /* Set this to 1 if you want the location ("Find Near Me") services only available for mobile devices.  */
    
    /********************************************************************************************************************************************************/
    /** This selects the styling theme to be used for display. If you create your own, then set the directory name for that here.                           */
    /** This affects the '[[BMLT]]', '[[BMLT_MOBILE]]' and '[[BMLT_MAP]]' shortcodes.                                                                       */
    /** Can be 'default', 'BlueAndRed', 'BlueAndWhite', 'GNYR', 'GreenAndGold' or 'GreyAndMaroon'.                                                          */
    /********************************************************************************************************************************************************/
    
    'theme'                         =>          'BlueAndWhite',
    
    /********************************************************************************************************************************************************/
    /** These are optional. Most folks will leave them at these values. They are used by the '[[BMLT]]', '[[BMLT_MOBILE]]' and '[[BMLT_MAP]]' shortcodes.   */
    /********************************************************************************************************************************************************/
    
    'grace_period'                  =>          15, /* How many minutes are allowed to go by before a meeting is considered "too late."                     */
    'time_offset'                   =>          0,  /* Generally left at 0 hours. If the server has a different time offset from this, indicate it here.    */

/*************************************************************************************************************************************************************
*^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^*
*################################################################ CHANGE ABOVE THIS LINE ####################################################################*
*************************************************************************************************************************************************************/

/*************************************************************************************************************************************************************
*############################################################# DON'T CHANGE BELOW THIS LINE #################################################################*
*VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV*
*************************************************************************************************************************************************************/
    
    /// This is used internally. Leave it alone. It is not supposed to end with a comma.
    
    'id'                            => $bmlt_basic_configuration_index + 1  /* Don't mess with this one. */

);

$bmlt_basic_configuration_index++;  // Leave this alone, too.

/*************************************************************************************************************************************************************
*^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^*
*############################################################# DON'T CHANGE ABOVE THIS LINE #################################################################*
*************************************************************************************************************************************************************/
?>