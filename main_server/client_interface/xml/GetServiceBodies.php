<?php
/***********************************************************************/
/**     \file   client_interface/xml/index.php

    \brief  This file is a very simple interface that is designed to return
    an XML string, in response to a search.
    In order to use this, you need to call: <ROOT SERVER BASE URI>/client_interface/json/
    with the same parameters that you would send to an advanced search. The results
    will be returned as an XML file.

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
$_GET['switcher'] = 'GetServiceBodies';
require_once(dirname(__FILE__).'/index.php');
