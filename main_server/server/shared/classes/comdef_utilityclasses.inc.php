<?php
/***********************************************************************/
/** \file	comdef_utilityclasses.inc.php
	\brief	Some basic utility classes and functions to be used
	throughout the CoMDEF system.
	
	These classes and functions describe some basic functionality that is
	used throughout the system.
*/
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

require_once ( dirname(dirname ( __FILE__ ))."/spanish_metaphone.php" );
require_once ( dirname(dirname(dirname ( __FILE__ )))."/c_comdef_server.class.php" );

/*******************************************************************/
/** \brief Returns a URL (HTTP) to the main_server directory (or renamed).

    \returns an HTTP URL, with the port (if necessary) and HTTPS (If necessary).
*/
function GetURLToMainServerDirectory(
                                        $inAllowHTTPS = TRUE  ///< If TRUE (default), then the URI will be allowed to use HTTPS. If FALSE, then we explicitly disallow HTTPS.
                                        )
{
    $port = intval ( $_SERVER['SERVER_PORT'] );
    
    if ( isset ( c_comdef_server::$server_local_strings['do_not_force_port'] ) && c_comdef_server::$server_local_strings['do_not_force_port'] )
        {
        $port = NULL;
        }
    
    // IIS puts "off" in the HTTPS field, so we need to test for that.
    $https = ($inAllowHTTPS && (!empty ( $_SERVER['HTTPS'] ) && (($_SERVER['HTTPS'] !== 'off') || ($port == 443)))) ? TRUE : FALSE;
    
    $url_path = $_SERVER['SERVER_NAME'];
    $file_path = str_replace ( '\\', '/', dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) );
    $my_path = str_replace ( '\\', '/', dirname ( $_SERVER['PHP_SELF'] ) );
    $subsequent_path = str_replace ( $file_path, '', $my_path );
    
    // See if we need to add an explicit port to the URI.
    if ( !$https && ($port != 80) )
        {
        $url_path .= ":$port";
        }
    elseif ( $https && ($port != 443) )
        {
        $url_path .= ":$port";
        }
        
    $url_path .= '/'.trim ( $subsequent_path, '/' ).'/';
    $url_path = 'http'.($https ? 's' : '').'://'.$url_path;
    return $url_path;
}

/*******************************************************************/
/** \brief Converts a string into an array of metaphone entities.

	This breaks up the given string into metaphone keys, and returns
	them in an array, so they can be easily compared.
	
	http://us2.php.net/manual/en/function.metaphone.php
	
	\returns An associative array of metaphone keys. Null if none given.
	The value will be the metaphone key, and the key will be the original
	string, converted to lowercase.
*/
function SplitIntoMetaphone (
							$in_string,				///< The string to split up.
							$in_lang_enum = null,	///< This is the code for the desired language. If not given, the server localization will be used.
							$in_literal = false		///< If this is set to true (default is false), then metaphone will not be used.
							)
{
	$ret = null;
	
    $in_string = mb_strtolower ( trim ( $in_string ), 'UTF-8' );
    
	/// If no language is given, we use the server's native language.
	if ( null == $in_lang_enum )
		{
		$in_lang_enum = c_comdef_server::GetServer()->GetLocalLang();
		}
	
    if ( ("en" == $in_lang_enum) || ("es" == $in_lang_enum) )
        {
        $ar = explode ( " ", $in_string );

        if ( is_array ( $ar ) && count ( $ar ) )
            {
            foreach ( $ar as &$string )
                {
                $string = mb_strtolower ( trim ( $string ), 'UTF-8' );
            
                if ( $string )
                    {
                    $mp = $string;
                
                    if ( !$in_literal && ("en" == $in_lang_enum) )
                        {
                        $mp = metaphone ( $mp );
                        }
                    /// We also have a Spanish version of metaphone() available.
                    elseif ( !$in_literal && ("es" == $in_lang_enum) )
                        {
                        $mp = spanish_metaphone ( $mp );
                        }
            
                    $ret[] = $mp;
                    }
                }
            }
        }
    else
        {
        $ret = array ( $in_string );
        }
    
	return $ret;
}

/*******************************************************************/
/** \brief Encrypts a string, using the most effective encryption.

	This function will encrypt a string, using the standard PHP
	crypt() function, and, if asked, will use the most secure algorithm
	available on the server. This is a one-way encryption.
	
	NOTE: If you use this in "secure" mode (other than CRYPT_DES), it may
	deliver passwords in a format that cannot be ported to other servers.
	
	**UPDATE** This now uses the PHP password_hash() function. Much better. This also means that you need to check with hash_equals() and password_verify().
	
	\returns a string, which is the given string, encrypted. Use this to compare
	with other strings, or as a salt for future encryptions.
*/
function FullCrypt (
					$in_string,			///< The string to be encrypted
					$in_salt=null,		///< IGNORED
					&$crypt_method=null	///< IGNORED
					)
{
    return password_hash ( $in_string, PASSWORD_DEFAULT );
}

/*******************************************************************/
/** \brief	This function accepts an array of data (or a single element),
	and "cleans it up" in preparation for use as a JSON string.

	\returns a string, containing the "cleaned" array
*/
function json_prepare($data, $escapeSpecialChars = false)
{
	if (is_object($data))
		{
		throw new Exception(__FUNCTION__ . '() objects are not supported by this function');
		}

	if (is_array($data))
		{
		$temp = array();

		reset($data);
		foreach($data as $key => $value) {
			$temp[json_prepare($key, $escapeSpecialChars)] = json_prepare($value, $escapeSpecialChars);
		}

		$data = $temp;
		}
	else
		{
		if (!is_null($data))
			{
			$data = preg_replace ( "|\"|", "\\\"",  $data );
			if ($escapeSpecialChars)
				{
				$data = utf8_encode(c_comdef_htmlspecialchars($data, ENT_QUOTES));
				}
			else
				{
				$data = utf8_encode($data);
				}
			}
		}

	return $data;
}

/**
	\brief This is a function that returns the results of an HTTP call to a URI.
	It is a lot more secure than file_get_contents, but does the same thing.
	
	\returns a string, containing the response. Null if the call fails to get any data.
	
	\throws an exception if the call fails.
*/
function call_curl (	$in_uri,				///< A string. The URI to call.
						$in_post = true,		///< If false, the transaction is a GET, not a POST. Default is true.
						&$http_status = null	///< Optional reference to a string. Returns the HTTP call status.
						)
{
	$ret = null;
	
	// If the curl extension isn't loaded, we try one backdoor thing. Maybe we can use file_get_contents.
	if ( !extension_loaded ( 'curl' ) )
		{
		if ( ini_get ( 'allow_url_fopen' ) )
			{
			$ret = file_get_contents ( $in_uri );
			}
		}
	else
		{
        // This gets the session as a cookie.
        if (isset ( $_COOKIE['PHPSESSID'] ) && $_COOKIE['PHPSESSID'] )
            {
            $strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';

            session_write_close();
            }

        // Create a new cURL resource.
        $resource = curl_init();
        
        if ( isset ( $strCookie ) )
            {
            curl_setopt ( $resource, CURLOPT_COOKIE, $strCookie );
            }
        
		// If we will be POSTing this transaction, we split up the URI.
		if ( $in_post )
			{
			$spli = explode ( "?", $in_uri, 2 );
			
			if ( is_array ( $spli ) && count ( $spli ) )
				{
				$in_uri = $spli[0];
				$in_params = $spli[1];
				// Convert query string into an array using parse_str(). parse_str() will decode values along the way.
				parse_str($in_params, $temp);
				
				// Now rebuild the query string using http_build_query(). It will re-encode values along the way.
				// It will also take original query string params that have no value and appends a "=" to them
				// thus giving them and empty value.
				$in_params = http_build_query($temp);
			
				curl_setopt ( $resource, CURLOPT_POST, true );
				curl_setopt ( $resource, CURLOPT_POSTFIELDS, $in_params );
				}
			}
		
		// Set url to call.
		curl_setopt ( $resource, CURLOPT_URL, $in_uri );
		
		// Make curl_exec() function (see below) return requested content as a string (unless call fails).
		curl_setopt ( $resource, CURLOPT_RETURNTRANSFER, true );
		
		// By default, cURL prepends response headers to string returned from call to curl_exec().
		// You can control this with the below setting.
		// Setting it to false will remove headers from beginning of string.
		// If you WANT the headers, see the Yahoo documentation on how to parse with them from the string.
		curl_setopt ( $resource, CURLOPT_HEADER, false );
		
		// Allow  cURL to follow any 'location:' headers (redirection) sent by server (if needed set to true, else false- defaults to false anyway).
		// Disabled, because some servers disable this for security reasons.
//			curl_setopt ( $resource, CURLOPT_FOLLOWLOCATION, true );
		
		// Set maximum times to allow redirection (use only if needed as per above setting. 3 is sort of arbitrary here).
		curl_setopt ( $resource, CURLOPT_MAXREDIRS, 3 );
		
		// Set connection timeout in seconds (very good idea).
		curl_setopt ( $resource, CURLOPT_CONNECTTIMEOUT, 10 );
		
		// Direct cURL to send request header to server allowing compressed content to be returned and decompressed automatically (use only if needed).
		curl_setopt ( $resource, CURLOPT_ENCODING, 'gzip,deflate' );
			
        // Pretend we're a browser, so that anti-cURL settings don't pooch us.
        curl_setopt ( $resource, CURLOPT_USERAGENT, "cURL Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20130401 Firefox/21.0" ); 

        // Trust meeeee...
        curl_setopt ( $resource, CURLOPT_SSL_VERIFYPEER, FALSE);
    
		// Execute cURL call and return results in $content variable.
		$content = curl_exec ( $resource );
		
		// Check if curl_exec() call failed (returns false on failure) and handle failure.
		if ( $content === false )
			{
			// Cram as much info into the exception as possible.
			throw new Exception ( "curl failure calling $in_uri, ".curl_error ( $resource ).", ".curl_errno ( $resource ) );
			}
		else
			{
			// Do what you want with returned content (e.g. HTML, XML, etc) here or AFTER curl_close() call below as it is stored in the $content variable.
		
			// You MIGHT want to get the HTTP status code returned by server (e.g. 200, 400, 500).
			// If that is the case then this is how to do it.
			$http_status = curl_getinfo ( $resource, CURLINFO_HTTP_CODE );
			}
		
		// Close cURL and free resource.
		curl_close ( $resource );
		
		// Maybe echo $contents of $content variable here.
		if ( $content !== false )
			{
			$ret = $content;
			}
		}
	
	return $ret;
}

/*******************************************************************/
/** \brief Returns the HTML for an "Admin Bar" along the top of the page.
	\returns the HTML for the bar.
*/
function c_comdef_admin_bar( $in_http_vars ///< The aggregation of the $_GET and $_POST superglobals, as an associative array.
							)
	{
	$x_file = $_SERVER ['SCRIPT_NAME'];
	require_once ( dirname ( __FILE__ ).'/../../../server/c_comdef_server.class.php' );
	$ret = '';
	$server = c_comdef_server::MakeServer();
	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();
		include ( dirname ( __FILE__ ).'/../../../server/config/get-config.php' );
			
		$user_obj = $server->GetCurrentUserObj();
		if ( ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_OBSERVER) )
			{
			$left_link = '&nbsp;';
			$middle_link = '&nbsp;';
			
			if ( isset ( $in_http_vars['edit_cp'] )
				|| (isset ( $in_http_vars['do_search'] ) && $in_http_vars['do_search'])
				|| (isset ( $in_http_vars['single_meeting_id'] ) && $in_http_vars['single_meeting_id']) )
				{
				$left_link = '<a href="'.$x_file.'?supports_ajax=yes&amp;lang_enum='.htmlspecialchars ( $lang_enum ).'">';
				$left_link .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['meeting_search'] );
				$left_link .= '</a>';
				}
			
			if ( !isset ( $in_http_vars['edit_cp'] ) )
				{
				$link = '<a href="'.$x_file.'?edit_cp&amp;supports_ajax=yes&amp;lang_enum='.htmlspecialchars ( $lang_enum ).'">';
				$link .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['edit_link'] );
				$link .= '</a>';
				if ( $left_link == '&nbsp;' )
					{
					$left_link = $link;
					}
				else
					{
					$middle_link = $link;
					}
				}
			
			$right_link = '<a href="'.$x_file.'?logout&amp;supports_ajax=yes&amp;lang_enum='.htmlspecialchars ( $lang_enum ).'">';
				$right_link .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['logout'] );
			$right_link .= '</a>';
			
			$info_string = '<div class="bmlt_admin_info_div">';
				$info_string .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['logged_in'] );
				$info_string .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Users'][$user_obj->GetUserLevel()] );
				$info_string .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['login_name'] );
				$info_string .= c_comdef_htmlspecialchars ( $user_obj->GetLogin() );
			$info_string .= '</div>';
				
			$admin_links = '<div class="bmlt_admin_links_div">';
				$admin_links .= '<div class="bmlt_admin_one_link_div">'.$left_link.'</div>';
				$admin_links .= '<div class="bmlt_admin_one_link_div">'.$middle_link.'</div>';
				$admin_links .= '<div class="bmlt_admin_one_link_div">'.$right_link.'</div>';
			$admin_links .= '</div>';
			}
		elseif ( ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() == _USER_LEVEL_OBSERVER) )
			{
			
			$info_string = '<div class="bmlt_admin_info_div">';
				$info_string .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['logged_in'] );
				$info_string .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Users'][$user_obj->GetUserLevel()] );
				$info_string .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['login_name'] );
				$info_string .= c_comdef_htmlspecialchars ( $user_obj->GetLogin() );
			$info_string .= '</div>';
				
			$admin_links = '<div class="bmlt_admin_links_div">';
					if ( isset ( $in_http_vars['edit_cp'] )
						|| (isset ( $in_http_vars['do_search'] ) && $in_http_vars['do_search'])
						|| (isset ( $in_http_vars['single_meeting_id'] ) && $in_http_vars['single_meeting_id']) )
						{
						$admin_links .= '<div class="bmlt_admin_one_link_div">';
							$admin_links .= '<a href="'.$x_file.'?supports_ajax=yes&amp;lang_enum='.htmlspecialchars ( $lang_enum ).'">';
								$admin_links .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['meeting_search'] );
							$admin_links .= '</a>';
						$admin_links .= '</div>';
						$admin_links .= '<div class="bmlt_admin_one_link_div">&nbsp;</div>';
						$admin_links .= '<div class="bmlt_admin_one_link_div">';
							$admin_links .= '<a href="'.$x_file.'?logout&amp;supports_ajax=yes&amp;lang_enum='.htmlspecialchars ( $lang_enum ).'">';
								$admin_links .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['logout'] );
							$admin_links .= '</a>';
						$admin_links .= '</div>';
						}
					else
						{
						$admin_links .= '<div class="bmlt_admin_one_link_div">&nbsp;</div>';
						$admin_links .= '<div class="bmlt_admin_one_link_div">';
							$admin_links .= '<a href="'.$x_file.'?logout&amp;supports_ajax=yes&amp;lang_enum='.htmlspecialchars ( $lang_enum ).'">';
								$admin_links .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['logout'] );
							$admin_links .= '</a>';
						$admin_links .= '</div>';
						$admin_links .= '<div class="bmlt_admin_one_link_div">&nbsp;</div>';
						}
				
			$admin_links .= '</div>';
			}
		else
			{
			$info_string = '<div class="bmlt_admin_info_div">&nbsp;</div>';
			$admin_links = '<div class="bmlt_admin_links_div">';
				$admin_links .= '<div class="bmlt_admin_one_link_div">&nbsp;</div>';
				$admin_links .= '<div class="bmlt_admin_one_link_div"><a class="login_div" href="'.$_SERVER['SCRIPT_NAME'].'?login&amp;supports_ajax=yes&amp;lang_enum='.htmlspecialchars ( $lang_enum ).'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Bar']['login'] ).'</a></div>';
				$admin_links .= '<div class="bmlt_admin_one_link_div">&nbsp;</div>';
			$admin_links .= '</div>';
			}
		
		if ( !isset ( $http_vars['login'] ) && !isset ( $_POST['login'] ) )
			{
			$ret = '<div class="bmlt_admin_strip no_print" id ="bmlt_admin_strip">'.$info_string.$admin_links.'<div class="clear_both"></div></div>';
			}
		}
	
	return $ret;
	}

/*******************************************************************/
/**	\brief This function creates a displayable string.

	\returns The "cleaned" string
*/
function c_comdef_htmlspecialchars ( $in_string )
{
    $local_strings = c_comdef_server::GetLocalStrings();
	$ret = htmlspecialchars ( $in_string, null, $local_strings['charset'] );
	
	return $ret;
}

/*******************************************************************/
/**	\brief This function vets the email address for proper form.

	\returns true if the email address has a valid format.
*/
function c_comdef_vet_email_address ( $in_address )
{
	$ret = false;
	if ( isset ( $in_address ) && is_string ( $in_address ) && trim ( $in_address ) && preg_match ( '#^(?:[a-zA-Z0-9_\'^&amp;/+-])+(?:\.(?:[a-zA-Z0-9_\'^&amp;/+-])+)*@(?:(?:\[?(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))\.){3}(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\]?)|(?:[a-zA-Z0-9-]+\.)+(?:[a-zA-Z]){2,}\.?)$#', $in_address ) )
		{
		$ret = true;
		}
	
	return $ret;
}

/*******************************************************************/
/** \brief	This function can be called to terminate the session.
*/
function c_comdef_LogoutUser()
	{
	if ( !isset ( $_SESSION ) )
		{
		session_start();
		}
	
	// Wipe out the session variables
	$_SESSION = array();
	
	// Eat the session cookie.
	if ( isset ( $_COOKIE[session_name()] ) )
		{
		setcookie ( session_name(), '', time() - 42000, '/' );
		}
	
	// Bye, now...
    session_write_close();  // Close the session.
	}

/*******************************************************************/
/**
 * \class bdfProfiler
 * \brief Execution time/memory profiling class
 *
 * This is a modification of <a href="http://solarphp.com">the Solar_Debug_Timer class</a>
 * \author Paul M. Jones <pmjones@solarphp.com>
 *
 * Singleton class accessed via static methods
 *
 * Example:
 * \code
 *	bdfProfiler::mark('Start');
 *	bdfProfiler::mark('End');
 *	echo bdfProfiler::html_output();
 * \endcode
 */
class bdfProfiler
	{
	private static $marks = array();	///< This is an array that holds the markers.

	/**
	* \brief Private constructor prevents direct creation of object
	*/
	private function __construct()
		{
		}

	/**
	* \brief Marks a time
	*/
	public static function mark( $msg = null	///< Optional string. A message to be displayed for this marker.
							)
		{
		// If possible, we see how the memory is being used.
		if ( function_exists ( 'memory_get_usage' ) )
			{
			$mem = memory_get_usage();
			}
		else
			{
			$mem = '-';
			}
		array_push(self::$marks, array('time' => microtime(true), 'memory' => $mem, 'msg' => $msg));
		}

	/**
	* \brief Returns profiling information as an array
	*
	* \returns an array of associative arrays that contain the profiling information. Each sub-array represents the information from one marker.
	*	- 'num' An integer. The marker ID number
	*	- 'diff' A floating-point number. The number of seconds (at a millisecond resolution) between the last marker and this one.
	*	- 'total' A floating-point number. The total number of seconds (at a millisecond resolution) since the start marker.
	*	- 'memory' If possible, the memory consumption at the marker's point in execution.
	*	- 'msg' Any text message that was associated with the marker.
	*/
	public static function profile()
		{
		$diff = 0;
		$prev = 0;
		$total = 0;
		$result = array();

		foreach ( self::$marks as $k => $v )
			{
			if ($prev > 0)
				{
				$diff = $v['time'] - $prev;
				}

			$total = $total + $diff;

			$result[] = array ( 'num' => $k, 'diff' => $diff, 'total' => $total, 'memory' => $v['memory'], 'msg' => $v['msg'] );

			$prev = $v['time'];
			}

		return $result;
		}

	/**
	* \brief Return formatted profile information
	*
	* \returns a string, with a series of tab-delimited lines; each containing the profile information for a marker.
	*/
	public static function output ( $in_html = false	///< If true, the output is html. Default is false.
								)
		{
		if ( $in_html )
			{
			$output = self::html_output ( );
			}
		else
			{
			$output = sprintf ("%-3.3s\t%10.10s\t%10.10s\t%10.10s\t%-500.500s\n", 'Num', 'Diff/sec', 'Total/sec', 'Memory', 'Msg');
			$output .= sprintf ("%-'-3.3s\t%'-10.10s\t%'-10.10s\t%'-10.10s\t%-'-500.500s\n", '', '', '', '', '');
	
			$profile = self::profile();
	
			foreach ($profile as $value)
				{
				$output .= sprintf ( "%3.3s\t%10F\t%10F\t%10d\t%-500.500s\n", $value['num'], $value['diff'], $value['total'], $value['memory'], $value['msg']);
				}
			}
	
		return $output;
		}

	/**
	* \brief Return formatted profile information in HTML form
	*
	* \returns a string, containing an HTML table element with a series of rows; each containing the profile information for a marker. The table's class is "bdfProfiler_table".
	*/
	public static function html_output ( )
		{
		$output = '<table class="bdfProfiler_table" cellpadding="0" cellspacing="0" summary="Profiling Information Report">';
		$output .= '<thead><tr><td>Num</td><td>Diff/sec</td><td>Total/sec</td><td>Memory</td><td>Msg</td></tr></thead><tbody>';

		$profile = self::profile();

		foreach ($profile as $value)
			{
			$output .= sprintf ( "<tr><td>%3.3s</td><td>%10F</td><td>%10F</td><td>%10d</td><td>%-500.500s</td></tr>", $value['num'], $value['diff'], $value['total'], $value['memory'], $value['msg']);
			}
		
		$output .= '</tbody></table>';
		
		return $output;
		}
	};
?>