<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Create Crypted Password for BMLT</title>
    </head>
    <body>
        <?php
            if ( isset ( $_REQUEST['input-password'] ) && $_REQUEST['input-password'] ) {
                $salt = $_REQUEST['input-salt'] ? $_REQUEST['input-salt'] : strval(rand ( 1000000000000, 9999999999999 ));
                $_REQUEST['input-salt'] = $salt;
            }
        ?>
        <form action="#" method="GET">
            <pre><label for="input-password">Enter A Password to be Crypted:</label> <input type="text" size="64" name="input-password" id="input-password"<?php if ( isset ( $_REQUEST['input-password'] ) && $_REQUEST['input-password'] ) { echo 'value="'.$_REQUEST['input-password'].'" '; } ?> /></pre>
            <pre><label for="input-salt">Enter A Crypt Salt:</label>             <input type="text" size="64" placeholder="Leave Blank for Random Salt" name="input-salt" id="input-salt"<?php if ( isset ( $_REQUEST['input-salt'] ) && $_REQUEST['input-salt'] ) { echo 'value="'.$_REQUEST['input-salt'].'" '; } ?> /></pre>
            <input type="submit" value="Encrypt" />
        </form>
        <?php
        if ( isset ( $_REQUEST['input-password'] ) && $_REQUEST['input-password'] ) {
            function FullCrypt (
                                $in_string,			///< The string to be encrypted
                                $in_salt=null,		/**< This is the original password, in
                                                        encrypted form (used as a salt).
                                                        If this is provided, the $crypt_method
                                                        parameter is ignored..
                                                    */
                                &$crypt_method=null	/**< Optional. If provided, the encryption
                                                        constant will be returned.
                                                        If the crypt method is requested, this
                                                        should be set to true. The value will
                                                        be replaced by the constant for the
                                                        method used.
                                            
                                                        Values:
                                                            - 'CRYPT_BLOWFISH'
                                                                Use CRYPT_BLOWFISH (If available)
                                                            - 'CRYPT_MD5'
                                                                Use CRYPT_MD5 (If available)
                                                            - 'CRYPT_EXT_DES'
                                                                Use CRYPT_EXT_DES (If available)
                                                            - 'CRYPT_DES'
                                                                Use the default DES algorithm
                                                            - true
                                                                Use the maximum possible algorithm.
                                                            - null (or not provided)
                                                                Use CRYPT_DES, or the provided salt.
                                                
                                                        NOTE: If you provide a specific value,
                                                        and the server cannot provide it,
                                                        CRYPT_DES will be used.
                                                    */
                                )
            {
                $ret = null;
    
                if ( isset ( $in_string ) && $in_string )
                    {
                    // We only do all this stuff if a crypt method was specified, and there was no salt.
                    if ( (null == $in_salt) && (null != $crypt_method) )
                        {
                        // We just drop through if a fixed (and supported) method was requested.
                        if ( ($crypt_method == 'CRYPT_BLOWFISH') && CRYPT_BLOWFISH )
                            {
                            // No-op (for now)
                            }
                        elseif ( ($crypt_method == 'CRYPT_MD5') && CRYPT_MD5 )
                            {
                            // No-op (for now)
                            }
                        elseif ( ($crypt_method == 'CRYPT_EXT_DES') && CRYPT_MD5 )
                            {
                            // No-op (for now)
                            }
                        // A simple value of true, means use the best encryption available.
                        elseif ( (null == $in_salt) && (true === $crypt_method) )
                            {
                            if ( CRYPT_BLOWFISH )
                                {
                                $crypt_method = 'CRYPT_BLOWFISH';
                                }
                            elseif ( CRYPT_MD5 )
                                {
                                $crypt_method = 'CRYPT_MD5';
                                }
                            elseif ( CRYPT_EXT_DES )
                                {
                                $crypt_method = 'CRYPT_EXT_DES';
                                }
                            else
                                {
                                $crypt_method = 'CRYPT_DES';
                                }
                            }
                        elseif ( null == $in_salt )
                            {
                            $crypt_method = 'CRYPT_DES';
                            }
    
                        // Each method is triggered by a different salt length and header. We use numbers
                        // here, just to be simple. It's not such a huge deal what the salt is, so we use
                        // a limited range to ensure proper salt length.
                        switch ( $crypt_method )
                            {
                            case 'CRYPT_BLOWFISH':
                                $salt = "$2$".strval ( rand ( 1000000000000, 9999999999999 ) );
                            break;
                            case 'CRYPT_MD5':
                                $salt = "$1$".strval ( rand ( 100000000, 999999999 ) );
                            break;
                            case 'CRYPT_EXT_DES':
                                $salt = strval ( rand ( 100000000, 999999999 ) );
                            break;
                            case 'CRYPT_DES':
                                $salt = strval ( rand ( 10, 99 ) );
                            break;
                            }
        
                        $in_salt = isset ( $salt ) && $salt ? $salt : $in_string;
                        }
    
                    $in_salt = isset ( $in_salt ) && $in_salt ? $in_salt : $in_string;

                    $ret = crypt ( $in_string, $in_salt );
                    }
        
                return $ret;
            }
        
            $password = $_REQUEST['input-password'];
            $val = TRUE;

            $testCrypt = FullCrypt($password, $salt, $val);

            echo "<hr /><pre>Result:  $testCrypt</pre><hr />";
        }
        ?>
    </body>
</html>
