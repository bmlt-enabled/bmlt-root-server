<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT for more details.

    You should have received a copy of the MIT along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/
ob_start();
define('__DEBUG_MODE__', 1); // Uncomment to make the CSS and JavaScript easier to trace (and less efficient).
session_start();
define('BMLT_EXEC', 1);
include(dirname(__FILE__).'/local_server/index.php');
ob_end_flush();
