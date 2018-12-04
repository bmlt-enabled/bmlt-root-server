<?php
if (file_exists(dirname(__FILE__).'/../../../auto-config.inc.php')) {
    if ((fileperms(dirname(__FILE__).'/../../../auto-config.inc.php') & 0x0002) == 0x0002) {
        die('The auto-config.inc.php file is still writeable! Security requires that it be set to read-only!');
    }
        
    require(dirname(__FILE__).'/../../../auto-config.inc.php');
} elseif (file_exists(dirname(__FILE__).'/../../../test_auto.inc.php')) {
    if ((fileperms(dirname(__FILE__).'/../../../test_auto.inc.php') & 0x0002) == 0x0002) {
        die('The test_auto.inc.php file is still writeable! Security requires that it be set to read-only!');
    }
        
    require(dirname(__FILE__).'/../../../test_auto.inc.php');
} elseif (file_exists(dirname(__FILE__).'/auto-config.inc.php')) {
    $realpath = realpath(dirname(__FILE__).'/../../../');
    $basename = basename(realpath(dirname(__FILE__).'/../../'));
    die(sprintf('The auto-config.inc.php file is in the wrong location! You need to move it out of this directory, and into the %s directory (the same directory as the %s directory)!', $realpath, $basename));
}

if (isset($gKey) && !isset($gkey)) {
    $gkey = $gKey;
}
