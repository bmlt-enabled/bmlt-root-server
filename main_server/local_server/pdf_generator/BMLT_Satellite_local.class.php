<?php
/***********************************************************************/
/** 	\file	BMLT_satellite.class.php

	\version 1.5.7

	\brief	This is a class that implements a BMLT Satellite/Client server.
	
	This is a "SINGLETON" pattern class, which means that it allows only
	one instance of the class to be in existence, and all references to the
	class go to that instance.
	
	It handles communications with the root server, and outputs the appropriate
	XHTML through a couple of simple functions.
    
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
/**************************************************************************/
/**
	\class	BMLT_Satellite_local
	
	\brief	This is the implementation of a standalone BMLT satellite server.
	
	It is meant to be instantiated in a simple PHP file, with a few calls to
	the BMLT_Satellite::Execute() function to deliver the XHTML to be displayed.
*/
class BMLT_Satellite_local
	{
	/***********************************************************************/
	/// This is static stuff that comprises the way the class is accessed.
	/***********************************************************************/

	/// Data Members
	static private	$bmlt_instance = null;	///< This will be the only instance of this class.

	/// Functions
	/***********************************************************************/
	/**
		\brief This is how clients will instantiate the BMLT. Either a new
		instance is created, or we get the current one.
	*/
	static function MakeBMLT (	$is_csv = false,		///< If true, then this object will be used for CSV data.
								$in_http_vars = null	///< These contain alternatives to the $_GET and/or $_POST parameters. Default is null.
							)
		{
		// If an instance does not already exist, we instantiate a new one.
		if ( !(self::$bmlt_instance instanceof BMLT_Satellite_local) )
			{
			// When we create a new instance, we load it with our configuration.
			self::$bmlt_instance = new BMLT_Satellite_local ( );
			}
		
		return self::$bmlt_instance;
		}
	
	/**
		\brief This is a function that returns the results of an HTTP call to a URI.
		It is a lot more secure than file_get_contents, but does the same thing.
		
		\returns a string, containing the response. Null if the call fails to get any data.
	*/
	function call_curl ( $in_uri,				///< A string. The URI to call.
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
			// Create a new cURL resource.
			$resource = curl_init();
			
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
			
			// Execute cURL call and return results in $content variable.
			$content = curl_exec ( $resource );
			
			// Check if curl_exec() call failed (returns false on failure) and handle failure.
			if ( $content === false )
				{
				// Cram as much info into the error message as possible.
				die ( '<pre>curl failure calling $in_uri, '.curl_error ( $resource )."\n".curl_errno ( $resource ).'</pre>' );
				}
			else
				{
				// Do what you want with returned content (e.g. HTML, XML, etc) here or AFTER curl_close() call below as it is stored in the $content variable.
			
				// You MIGHT want to get the HTTP status code returned by server (e.g. 200, 400, 500).
				// If that is the case then this is how to do it.
				$http_status = curl_getinfo ($resource, CURLINFO_HTTP_CODE );
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

	/***********************************************************************/
	/// All this stuff is dynamic stuff that applies directly to the instance.
	/***********************************************************************/
	
	/// Data members
	var	$root_server_uri = '';			///< The root server URI, with the API entrypoint added.
	var	$http_vars = '';				///< This is the combined GET and POST HTTP parameters.
	var	$params = '';					///< This is a parameter list that is appended to URIs.
	var	$lang_enum = null;				///< Set this to a desired language (If null, the server decides -null is default).
	
	/// Functions
	/***********************************************************************/
	/**
		\brief	We make the constructor private, so this class isn't instantiated on its own.
	*/
	private function __construct ( )
		{
		
		$this->http_vars = array_merge_recursive ( $_GET, $_POST );
		if ( !isset ( $this->http_vars['advanced_search_mode'] ) || !$this->http_vars['advanced_search_mode'] )
			{
			unset ( $this->http_vars['result_type_advanced'] );
			}

		// This is deliberately hardcoded for security.
		$this->root_server_uri = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/../../client_interface/csv/index.php';
		
		// These are basic settings for a satellite call.
		$this->http_vars['script_name'] = $_SERVER['SCRIPT_NAME'];
		$this->http_vars['satellite'] = $_SERVER['SCRIPT_NAME'];
		$this->http_vars['satellite_standalone'] = 1;
		
		// These are a couple of our config parameters
		$this->http_vars['start_view'] = $this->bmlt_initial_view;
		
		if ( isset ( $this->lang_enum ) && $this->lang_enum )
			{
			$this->http_vars['lang_enum'] = $this->lang_enum;
			}
		
		// We build a parameter list string to append to our cURL calls.
		$this->params = '';
		
		foreach ( $this->http_vars as $key => $value )
			{
			if ( $key != 'switcher' )	// We don't propagate switcher.
				{
				// If the value is an array, we handle it differently.
				if ( is_array ( $value ) )
					{
					foreach ( $value as $val )
						{
						$this->params .= '&'.urlencode ( $key );
						// If a nested array, well, we just join it with commas.
						if ( is_array ( $val ) )
							{
							$val = join ( ",", $val );
							}
						// The key needs the brackets to indicate an array value.
						$this->params .= "%5B%5D=". urlencode ( $val );
						}
					
					// Stop the process here.
					$key = null;
					}
				
				// If we have a key, we add that here.
				if ( $key )
					{
					$this->params .= '&'.urlencode ( $key );
					
					// We only add value if its called for.
					if ( $value )
						{
						$this->params .= "=". urlencode ( $value );
						}
					}
				}
			}
		}
	
	/***********************************************************************/
	/**
		\brief Performs the function necessary to provide the relevant content.
		
		This is the meat of this little class. It needs to be called in order to
		fetch the relevant data from the root server, and output it to the browser.
		
		\returns a string, containing the XHTML to be displayed.
	*/
	function Execute ( $in_phase = 'csv',
						$in_http_vars = null	///< These contain alternatives to the $_GET and/or $_POST parameters. Default is null.
					)
		{
		$content = '';
		
		// If we have special instructions for the object, they are given here.
		if ( is_array ( $in_http_vars ) && count ( $in_http_vars ) )
			{
			if ( !isset ( $in_http_vars['advanced_search_mode'] ) || !$in_http_vars['advanced_search_mode'] )
				{
				unset ( $in_http_vars['result_type_advanced'] );
				}
			
			if ( isset ( $this->lang_enum ) && $this->lang_enum )
				{
				$this->http_vars['lang_enum'] = $this->lang_enum;
				}

			$this->http_vars = $in_http_vars;
			// We build a parameter list string to append to our cURL calls.
			$this->params = '';
			
			foreach ( $this->http_vars as $key => $value )
				{
				if ( $key != 'switcher' )	// We don't propagate switcher..
					{
					// If the value is an array, we handle it differently.
					if ( is_array ( $value ) )
						{
						foreach ( $value as $val )
							{
							$this->params .= '&'.urlencode ( $key );
							// If a nested array, well, we just join it with commas.
							if ( is_array ( $val ) )
								{
								$val = join ( ",", $val );
								}
							// The key needs the brackets to indicate an array value.
							$this->params .= "%5B%5D=". urlencode ( $val );
							}
						
						// Stop the process here.
						$key = null;
						}
					
					// If we have a key, we add that here.
					if ( $key )
						{
						$this->params .= '&'.urlencode ( $key );
						
						// We only add value if its called for.
						if ( $value )
							{
							$this->params .= "=". urlencode ( $value );
							}
						}
					}
				}
			}
		
		switch ( $in_phase )
			{
			case 'csv':		// This is used for the special CSV call. If you don't know what it is, don't worry.
				// We simply call the CSV return directly, with the given parameters.
				$uri = "$this->root_server_uri?switcher=GetSearchResults$this->params";
				$content .= self::call_curl ( $uri );
			break;
			
			case 'csv_formats':		// This is used for the special CSV call. If you don't know what it is, don't worry.
				// We simply call the CSV return directly, with the given parameters.
				$uri = "$this->root_server_uri?switcher=GetFormats$this->params";
				$content .= self::call_curl ( $uri );
			break;
			
			default:
				$content = '';
			break;
			}
		
		return $content;
		}
	};
?>
