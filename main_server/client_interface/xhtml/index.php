<?php
/***********************************************************************/
/** 	\file	client_interface/xhtml/index.php

	\brief	This file calls the main "server access" file, and sends the responses
	back as pure XHTML/JavaScript/CSS.

    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://magshare.org/bmlt

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

define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2Json.php');
require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
require_once ( dirname ( __FILE__ ).'/server_access.php');

$server = c_comdef_server::MakeServer();
$ret = null;

if ( $server instanceof c_comdef_server )
	{
	$ret = parse_redirect ( $server );
	}
else
	{
	$ret = HandleNoServer ( );
	}

// This is commented out, because some browsers have trouble handling GZipped response data. Hopefully, I'll be able to uncomment soon.
// if ( zlib_get_coding_type() === false )
// 	{
// 	ob_start("ob_gzhandler");
// 	}
// else
	{
	ob_start();
	}

$script_head = '<script type="text/javascript">/* <![CDATA[ */';
$script_foot = '/* ]]> */</script>';
$style_head = '<style type="text/css">/* <![CDATA[ */';
$style_foot = '/* ]]> */</style>';
$ret = preg_replace('/<!--(.|\s)*?-->/', '', $ret);
$ret = preg_replace('/\/\*(.|\s)*?\*\//', '', $ret);
$ret = preg_replace( "|\s+\/\/.*|", " ", $ret );
$ret = preg_replace( "/\s+/", " ", $ret );
$ret = preg_replace( "|\<script type=\"text\/javascript\"\>(.*?)\<\/script\>|", "$script_head$1$script_foot", $ret );
$ret = preg_replace( "|\<style type=\"text\/css\"\>(.*?)\<\/style\>|", "$style_head$1$style_foot", $ret );
echo $ret;
ob_end_flush();
?>