<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/
/**
    \brief This function checks to make sure the database is correct for the current version.
*/
function DB_Connect_and_Upgrade()
{
    global $dbPrefix;

    include(dirname(__FILE__)."/../server/config/get-config.php");

    if (!isset($dbType)) {
        $dbType = 'mysql';
    }

    if (!isset($dbServer)) {
        $dbServer = 'localhost';
    }

    if (!isset($dbPrefix)) {
        $dbPrefix = 'na';
    }

    c_comdef_dbsingleton::init($dbType, $dbServer, $dbName, $dbUser, $dbPassword, 'utf8');

    // Migration code that used to be here was moved to a laravel migration
}
