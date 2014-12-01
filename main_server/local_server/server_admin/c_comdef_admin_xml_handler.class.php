<?php
/*
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
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.
/***********************************************************************************************************//**
    \class c_comdef_admin_xml_handler
    \brief Controls handling of the admin semantic interface.
    
            This class should not even be instantiated unless the user has been authorized, and an authorized seesion
            is in progress.
***************************************************************************************************************/
class c_comdef_admin_xml_handler
{
    var $http_vars;                     ///< This will hold the combined GET and POST parameters for this call.
    var $server;                        ///< The BMLT server model instance.
    
    /********************************************************************************************************//**
    \brief The class constructor.
    ************************************************************************************************************/
    __construct ( $in_http_vars,        ///< The combined GET and POST parameters.
                  $in_server            ///< The BMLT server instance.
                )
    {
        $this->http_vars = $in_http_vars;
        $this->server = $in_server;
    }
    
    /********************************************************************************************************//**
    \brief This is called to process the input and generate the output. It is the "heart" of the class.
    \returns XML to be returned.
    ************************************************************************************************************/
    process_commands()
    {
        $ret = NULL;
        
        return $ret;
    }
};
?>