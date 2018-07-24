<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Create Crypted Password for BMLT</title>
    </head>
    <body>
        <form action="#" method="GET">
            <pre><label for="input-password">Enter A Password to be Crypted:</label> <input type="text" size="64" name="input-password" id="input-password"<?php if ( isset ( $_REQUEST['input-password'] ) && $_REQUEST['input-password'] ) { echo 'value="'.$_REQUEST['input-password'].'" '; } ?> /></pre>
            <input type="submit" value="Encrypt" />
        </form>
        <?php
        if ( isset ( $_REQUEST['input-password'] ) && $_REQUEST['input-password'] ) {
        
            $password = $_REQUEST['input-password'];
            $val = TRUE;

            $testCrypt = password_hash($password, PASSWORD_DEFAULT);

            echo "<hr /><pre>Result:  $testCrypt</pre><hr />";
        }
        ?>
    </body>
</html>
