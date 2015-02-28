<?php
/***************************************************************************************************************************************/
/**
*/
/***************************************************************************************************************************************/
class bmlt_semantic
{
    protected $_httpVars;
    protected $_bmltRootServerURI;
    protected $_switcher;
    protected $_myURI;
    protected $_myLang;
    protected $_localization;
    protected $_myJSName;
    protected $_version;
    
    /**************************************************************/
    /** \brief  Class function that strips all the BS from a JS or CSS file.
        
        \returns the stripped-down JS.
    */
    /**************************************************************/
    static function strip_script ( $in_filename
                                )
    {
        $ret = null;
        
        if ( !preg_match ( "|/|", $in_filename ) )
            {
            if ( preg_match ( "|.*?\.js$|", $in_filename ) || preg_match ( "|.*?\.css$|", $in_filename ) )
                {
                $in_filename = dirname ( __FILE__ )."/$in_filename";
                $ret = file_get_contents ( $in_filename );
                $ret = preg_replace( "|\/\/.*?[\n\r]|s", "\n", $ret );  // Block comments.
                $ret = preg_replace( "|\/\*(.*?)\*\/|s", "", $ret );    // Line comments.
                $ret = preg_replace( "|[\ \t]+|s", " ", $ret );         // Tabs and spaces.
                $ret = preg_replace( "|\n[\ \t]+|s", "\n", $ret );      // Beginning line tabs and spaces.
                
                if ( !defined ( 'DEBUG' ) ) // If we are in release mode, we strip out all the whitespace (including line endings).
                    {
                    $ret = preg_replace( "|[\s]+|s", " ", $ret );
                    }
                // Leaving the line endings in there allows us to do inline debugging (errors point to lines).
                }
            else
                {
                die ( "FILE MUST BE A .JS or .CSS FILE!" );
                }
            }
        else
            {
            die ( "YOU CANNOT LEAVE THE DIRECTORY!" );
            }
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Class function that calls out to a Web site, using cURL.
                
        \param  in_uri              The URL to be called. Should contain all parameters as if it was a GET. POST will split them off.
        \param  in_post             Set to TRUE (default) if this is a POST call. If FALSE, then it is a GET call.
        \param  in_out_http_status  Optional in/out parameter for returning the HTTP status result.
        
        \throws an exception if there is a critical failure.
        
        \returns the content response
    */
    /**************************************************************/
    static function call_curl (	$in_uri,
                                $in_post = TRUE,
                                &$in_out_http_status = NULL
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
            if ( isset ( $_COOKIE['PHPSESSID'] ) && $_COOKIE['PHPSESSID'] )
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
            
                if ( is_array ( $spli ) && (count ( $spli ) > 1) )
                    {
                    $in_uri = $spli[0];
                    $in_params = $spli[1];
                    // Convert query string into an array using parse_str(). parse_str() will decode values along the way.
                    parse_str($in_params, $temp);
                
                    // Now rebuild the query string using http_build_query(). It will re-encode values along the way.
                    // It will also take original query string params that have no value and appends a "=" to them
                    // thus giving them and empty value.
                    $in_params = http_build_query($temp);
            
                    curl_setopt ( $resource, CURLOPT_POST, TRUE );
                    curl_setopt ( $resource, CURLOPT_POSTFIELDS, $in_params );
                    }
                }
        
            // Set url to call.
            curl_setopt ( $resource, CURLOPT_URL, $in_uri );
        
            // Make curl_exec() function (see below) return requested content as a string (unless call fails).
            curl_setopt ( $resource, CURLOPT_RETURNTRANSFER, TRUE );
        
            // By default, cURL prepends response headers to string returned from call to curl_exec().
            // You can control this with the below setting.
            // Setting it to false will remove headers from beginning of string.
            // If you WANT the headers, see the Yahoo documentation on how to parse with them from the string.
            curl_setopt ( $resource, CURLOPT_HEADER, FALSE );
        
            // Allow  cURL to follow any 'location:' headers (redirection) sent by server (if needed set to true, else false- defaults to false anyway).
            // Disabled, because some servers disable this for security reasons.
//			    curl_setopt ( $resource, CURLOPT_FOLLOWLOCATION, true );
        
            // Set maximum times to allow redirection (use only if needed as per above setting. 3 is sort of arbitrary here).
            curl_setopt ( $resource, CURLOPT_MAXREDIRS, 3 );
        
            // Set connection timeout in seconds (very good idea).
            curl_setopt ( $resource, CURLOPT_CONNECTTIMEOUT, 10 );
        
            // Direct cURL to send request header to server allowing compressed content to be returned and decompressed automatically (use only if needed).
            curl_setopt ( $resource, CURLOPT_ENCODING, 'gzip,deflate' );
            
            // Pretend we're a browser, so that anti-cURL settings don't pooch us.
            curl_setopt ( $resource, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)" ); 
    
            // Execute cURL call and return results in $content variable.
            $content = curl_exec ( $resource );
        
            // Check if curl_exec() call failed (returns false on failure) and handle failure.
            if ( $content === FALSE )
                {
                // Cram as much info into the exception as possible.
//                 throw new Exception ( "curl failure calling $in_uri, ".curl_error ( $resource ).", ".curl_errno ( $resource ) );
                }
            else
                {
                // Do what you want with returned content (e.g. HTML, XML, etc) here or AFTER curl_close() call below as it is stored in the $content variable.
        
                // You MIGHT want to get the HTTP status code returned by server (e.g. 200, 400, 500).
                // If that is the case then this is how to do it.
                if ( isset ( $in_out_http_status ) && (NULL != $in_out_http_status) )
                    {
                    $in_out_http_status = curl_getinfo ( $resource, CURLINFO_HTTP_CODE );
                    }
                }
        
            // Close cURL and free resource.
            curl_close ( $resource );
        
            // Maybe echo $contents of $content variable here.
            if ( $content !== FALSE )
                {
                $ret = $content;
                }
            }
    
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Class constructor.
    
                This function will exit the script if this is an AJAX
                callback, so you need to keep that in mind.
                
        \param inBaseURI The base URI for the root server.
        \param inHttpVars The HTTP query associative array.
    */
    /**************************************************************/
    function __construct (  $inHttpVars
                            )
    {
        // Get any language
        $this->_myLang = 'en';

        if ( isset ( $inHttpVars['lang'] ) && $inHttpVars['lang'] )
            {
            $this->_myLang = $inHttpVars['lang'];
            unset ( $inHttpVars['lang'] );
            }
    
        // Prevent dope fiending...
        $this->_myLang = trim ( strtolower ( preg_replace ( '|[^a-z0-9A-Z]+|', '', $this->_myLang ) ) );
    
        if ( !file_exists ( dirname ( __FILE__ ) . '/lang/'.$this->_myLang.'.inc.php' ) )
            {
            $this->_myLang = 'en';
            }
    
        include ( dirname ( __FILE__ ) . '/lang/'.$this->_myLang.'.inc.php' );
        
        if ( isset ( $inHttpVars['root_server'] ) )
            {
            $inBaseURI = $inHttpVars['root_server'];
            unset ( $inHttpVars['root_server'] );
            
            // If we have a root server passed in, we set that to our local data member, and remove it from the parameter array.
            if ( isset ( $inBaseURI ) && $inBaseURI )
                {
                if ( !preg_match ( '|^http|', $inBaseURI ) )
                    {
                    $inBaseURI = 'http://'.$inBaseURI;
                    }
        
                $this->_bmltRootServerURI = trim ( $inBaseURI, '/' );
                }

            // Get any switcher.
            if ( isset ( $inHttpVars['switcher'] ) && $inHttpVars['switcher'] )
                {
                $this->_switcher = $inHttpVars['switcher'];
                unset ( $inHttpVars['switcher'] );
                }
        
            // See if we are an AJAX callback.
            $ajaxCall = isset ( $inHttpVars['ajaxCall'] );
            unset ( $inHttpVars['ajaxCall'] );
        
            $this->_httpVars = $inHttpVars;     // Hang onto the rest.

            // Determine our URI for callbacks. Account for unusual ports and HTTPS.
            $https = isset ( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] && (strtolower ( trim ( $_SERVER['HTTPS'] ) ) != 'off'); // IIS puts "off" in this field, so we need to test for that.
            $port = intval ( $_SERVER['SERVER_PORT'] );
            $port = ($https && ($port == 443)) || (!$https && ($port == 80)) ? '' : ':'.$port;
            $url_path = 'http'.($https ? 's' : '').'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['PHP_SELF'];

            $this->_myURI = $url_path;          // This is the base for callbacks.

            // This is the name of our JavaScript object.
            $this->_myJSName = ($this->_bmltRootServerURI ? '_'.preg_replace ( '|[^a-z0-9A-Z_]+|', '', htmlspecialchars ( $this->_bmltRootServerURI ) ) : '');

            if ( $ajaxCall )    // If we are an AJAX callback, then we immediately go there.
                {
                $this->ajax_handler();
                exit(); // GBCW
                }
            }
    }
    
    /**************************************************************/
    /** \brief  Query the server for its version.
                This requires that the _bmltRootServerURI data member be valid.
    
        \returns an integer that will be MMMmmmfff (M = Major Version, m = Minor Version, f = Fix Version).
    */
    /**************************************************************/
    function get_server_version()
    {
        $ret = 0;
        
        if ( $this->_bmltRootServerURI )
            {
            $error = NULL;
        
            $uri = $this->_bmltRootServerURI.'/client_interface/serverInfo.xml';
            $xml = self::call_curl ( $uri, $error );

            if ( !$error && $xml )
                {
                $info_file = new DOMDocument;
                if ( $info_file instanceof DOMDocument )
                    {
                    if ( @$info_file->loadXML ( $xml ) )
                        {
                        $has_info = $info_file->getElementsByTagName ( "bmltInfo" );
                
                        if ( ($has_info instanceof domnodelist) && $has_info->length )
                            {
                            $nodeVal = $has_info->item ( 0 )->nodeValue;
                            $ret = explode ( '.', $nodeVal );
                        
                            if ( !isset ( $ret[1] ) )
                                {
                                $ret[1] = 0;
                                }
                        
                            if ( !isset ( $ret[2] ) )
                                {
                                $ret[2] = 0;
                                }
                        
                            $ret = (intval ( $ret[0] ) * 1000000) + (intval ( $ret[1] ) * 1000) + intval ( $ret[2] );
                            }
                        }
                    }
                }
            }
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Handles AJAX callbacks.
    
                This assumes that the $this->_httpVars data member
                is valid.
                
                This funtion is called automatically upon instantiation.
    */
    /**************************************************************/
    function ajax_handler()
    {
        if ( isset ( $this->_bmltRootServerURI ) && $this->_bmltRootServerURI )
            {
            if ( isset ( $this->_httpVars['GetInitialFormats'] ) )
                {
                echo ( self::call_curl ( $this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFormats' ) );
                }
            elseif ( isset ( $this->_httpVars['GetInitialServiceBodies'] ) )
                {
                echo ( self::call_curl ( $this->_bmltRootServerURI.'/client_interface/json/?switcher=GetServiceBodies' ) );
                }
            elseif ( isset ( $this->_httpVars['GetFieldKeys'] ) )
                {
                echo ( self::call_curl ( $this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFieldKeys' ) );
                }
            elseif ( isset ( $this->_httpVars['GetFieldValues'] ) )
                {
                echo ( self::call_curl ( $this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFieldValues&meeting_key='.$this->_httpVars['meeting_key'] ) );
                }
            elseif ( isset ( $this->_httpVars['GetVersion'] ) )
                {
                echo ( $this->get_server_version() );
                }
            }
    }
    
    /**************************************************************/
    /** \brief  Localizes a string token.
    
        \param in_string the token to be localized.
        
        \returns the localized string for the token.
    */
    /**************************************************************/
    function localize_string( $in_string
                            )
    {
        return htmlspecialchars ( isset ( $this->_localization[$in_string] ) ? $this->_localization[$in_string] : $in_string );
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page.
        
        \returns the HTML for the page.
    */
    /**************************************************************/
    function get_wizard_page_html()
    {
        $ret = '';
        
        $version = $this->get_server_version();
        
        if ( $version > 2000000 )
            {
            $ret .= '<h1 id="bmlt_semantic_badserver_h1'.htmlspecialchars ( $this->_myJSName ).'" style="display:none">'.$this->localize_string ( 'need_good_url' ).'</h1>';
        
            $ret .= '<div id="bmlt_semantic_info_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_info_div">';
            $ret .= '<div id="bmlt_semantic_info_div_root_url_line'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_info_line">';
            $ret .= '<span class="info_label">'.$this->localize_string ( 'root_url_label' ).'</span><span class="info_value"><a href="'.$this->_bmltRootServerURI.'" target="_blank">'.htmlspecialchars ( $this->_bmltRootServerURI ).'</a></span>';
            $ret .= '</div>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_info_div_download_line'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_info_line">';
            $ret .= '<span class="info_label">'.$this->localize_string ( 'result_url_label' ).'</span><span class="info_value"><span id="bmlt_semantic_info_div_url_active_span'.htmlspecialchars ( $this->_myJSName ).'"></span><span id="bmlt_semantic_info_div_url_Invalid_span'.htmlspecialchars ( $this->_myJSName ).'">'.$this->localize_string ( 'result_invalid_text' ).'</span></span>';
            $ret .= '</div>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_info_div_shortcode_line'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_info_line" style="display:none">';
            $ret .= '<span class="info_label">'.$this->localize_string ( 'result_shortcode_label' ).'</span><span class="info_value"><span id="bmlt_semantic_info_div_shortcode_active_span'.htmlspecialchars ( $this->_myJSName ).'"></span><span id="bmlt_semantic_info_div_shortcode_Invalid_span'.htmlspecialchars ( $this->_myJSName ).'">'.$this->localize_string ( 'result_invalid_text' ).'</span></span>';
            $ret .= '</div>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '<div class="clear_both"></div>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';

            $ret .= '<form id="bmlt_semantic_form'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form" action="'.htmlspecialchars ( $this->_myURI ).'" method="POST">';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_form_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_div">';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
            $ret .= $this->get_wizard_page_main_fieldset_html();
        
            // Add the JavaScript to the form.
            $ret .= '<script type="text/javascript">';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= bmlt_semantic::strip_script ( 'bmlt_semantic.js' );
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';

            $ret .= 'var bmlt_semantic_js_object'.$this->_myJSName.' = new BMLTSemantic ( \''.$this->_myJSName.'\', \''.$this->_myURI.'?ajaxCall&root_server='.urlencode ( $this->_bmltRootServerURI ).'\', \''.$this->_bmltRootServerURI.'\', '.intval ( $version ).' );';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '</script>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
            // Add the scoped CSS.
            $ret .= '<style type="text/css" scoped>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= bmlt_semantic::strip_script ( 'bmlt_semantic.css' );
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '</style>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '</form>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            }
        else
            {
            $ret = '<form id="enter_server_url_form" class="enter_server_url_form" action="" method="get">';
            $ret .= '<div id="enter_server_url_form_div" class="enter_server_url_form_div">';
            $ret .= '<style type="text/css" scoped>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= bmlt_semantic::strip_script ( 'bmlt_semantic.css' );
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '</style>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '<label id="enter_server_url_form_div_label" class="enter_server_url_form_div_label" for="enter_server_url_form_div_url_input">'.$this->localize_string ( 'enter_url_label' ).'</label>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '<input type="text" size="64" id="enter_server_url_form_div_url_input" class="enter_server_url_form_div_url_input" defaultValue="Enter A URL" name="root_server" />';
            $ret .= '<input type="submit" value="Submit" />';
            $ret .= '</div>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            $ret .= '</form>';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            }
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page main fieldset.
        
        \returns the HTML for the fieldset.
    */
    /**************************************************************/
    function get_wizard_page_main_fieldset_html()
    {
        $ret = '<fieldset id="bmlt_semantic_form_main_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_main_fieldset"><legend id="bmlt_semantic_form_main_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_main_fieldset_legend">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= $this->get_wizard_page_main_select_html();
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        $ret .= $this->get_wizard_page_direct_url_html();
        $ret .= $this->get_wizard_page_switcher_fieldset_html();
        
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_main_select_html()
    {
        $ret = '<label id="bmlt_semantic_form_main_mode_select_label'.htmlspecialchars ( $this->_myJSName ).'" for="bmlt_semantic_form_main_mode_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_main_mode_select_label">'.$this->localize_string ( 'select_option_text_prompt' ).'</label>';
        $ret .= '<select id="bmlt_semantic_form_main_mode_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_main_mode_select">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<option value="DOWNLOAD" selected="selected">'.$this->localize_string ( 'select_option_text_direct_url' ).'</option>';
        $ret .= '<option value="SHORTCODE">'.$this->localize_string ( 'select_option_text_cms_simple' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_direct_url_html()
    {
        $ret = '<div id="bmlt_semantic_form_direct_url_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_direct_url_div">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= $this->get_wizard_page_response_type_select_html();
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_response_type_select_html()
    {
        $ret = '<label id="bmlt_semantic_form_response_type_select_label'.htmlspecialchars ( $this->_myJSName ).'" for="bmlt_semantic_form_response_type_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_response_type_select_label">'.$this->localize_string ( 'response_type_selector_prompt' ).'</label>';
        $ret .= '<select id="bmlt_semantic_form_response_type_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_response_type_select">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<option value="csv" selected="selected">'.$this->localize_string ( 'response_type_selector_type_csv' ).'</option>';
        $ret .= '<option value="xml">'.$this->localize_string ( 'response_type_selector_type_xml' ).'</option>';
        $ret .= '<option value="json">'.$this->localize_string ( 'response_type_selector_type_json' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_response_type_select_kml_option'.htmlspecialchars ( $this->_myJSName ).'" value="kml">'.$this->localize_string ( 'response_type_selector_type_kml' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_response_type_select_gpx_option'.htmlspecialchars ( $this->_myJSName ).'" value="gpx">'.$this->localize_string ( 'response_type_selector_type_gpx' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_response_type_select_poi_option'.htmlspecialchars ( $this->_myJSName ).'" value="poi">'.$this->localize_string ( 'response_type_selector_type_poi' ).'</option>';
        $ret .= '<option value="simple-block">'.$this->localize_string ( 'response_type_selector_type_simple_block' ).'</option>';
        $ret .= '<option value="simple">'.$this->localize_string ( 'response_type_selector_type_simple_table' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page switcher fieldset.
        
        \returns the HTML for the fieldset.
    */
    /**************************************************************/
    function get_wizard_page_switcher_fieldset_html()
    {
        $ret = '<fieldset id="bmlt_semantic_form_switcher_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_switcher_fieldset"><legend id="bmlt_semantic_form_switcher_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_switcher_fieldset_legend">';
        $ret .= $this->get_wizard_page_switcher_type_select_html();
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_div_no_options_blurb'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div" style="display:none">'.$this->localize_string ( 'no_addl_options' ).'</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_naws_dump_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_switcher_naws_dump_div" style="display:none">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars ( $this->_myJSName ).'.handleNAWSDumpSelectChange(this)';
        $ret .= '<select id="bmlt_switcher_naws_dump_sb_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_switcher_naws_dump_sb_select" onchange="'.htmlspecialchars ( $function_string ).'">';
        $ret .= '<option value="" disabled="disabled" selected="selected">'.$this->localize_string ( 'defaultSBSelect' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= $this->get_wizard_page_meeting_search_html();     
        $ret .= $this->get_wizard_page_changes_html(); 
        $ret .= $this->get_wizard_page_fields_html();    
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_changes_html()
    {
        $ret = '<div id="bmlt_semantic_form_changes_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_div" style="display:none">';
        $ret .= '<div id="bmlt_semantic_form_changes_blurb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string ( 'date_format1' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'date_format2' ).'</p>';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_from_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_line">';
        $ret .= '<label title="'.$this->localize_string ( 'date_format1' ).'" for="bmlt_semantic_form_changes_from_text'.htmlspecialchars ( $this->_myJSName ).'" id="bmlt_semantic_form_changes_from_label'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_from_label">';
        $ret .= $this->localize_string ( 'changes_from' );
        $ret .= '</label>';
        $ret .= '<input type="text" pattern="^[0-9\-]+$" title="'.$this->localize_string ( 'date_format1' ).'" id="bmlt_semantic_form_changes_from_text'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_date_text" value="'.$this->localize_string ( 'default_date' ).'" maxlength="10" />';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_to_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_line">';
        $ret .= '<label title="'.$this->localize_string ( 'date_format1' ).'" for="bmlt_semantic_form_changes_to_text'.htmlspecialchars ( $this->_myJSName ).'" id="bmlt_semantic_form_changes_to_label'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_to_label">';
        $ret .= $this->localize_string ( 'changes_to' );
        $ret .= '</label>';
        $ret .= '<input type="text" pattern="^[0-9\-]+$" title="'.$this->localize_string ( 'date_format1' ).'" id="bmlt_semantic_form_changes_to_text'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_date_text" value="'.$this->localize_string ( 'default_date' ).'" maxlength="10" />';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_meeting_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_line">';
        $ret .= '<label title="'.$this->localize_string ( 'meeting_id_changes_tooltip' ).'" for="bmlt_semantic_form_changes_id_text'.htmlspecialchars ( $this->_myJSName ).'" id="bmlt_semantic_form_changes_id_label'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_id_label">';
        $ret .= $this->localize_string ( 'meeting_id_changes' );
        $ret .= '</label>';
        $ret .= '<input type="text" pattern="^[0-9]+$" title="'.$this->localize_string ( 'meeting_id_changes_tooltip' ).'" id="bmlt_semantic_form_changes_id_text'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_id_text" value="" maxlength="6" />';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_sb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_line">';
        $ret .= '<label for="bmlt_switcher_changes_sb_select'.htmlspecialchars ( $this->_myJSName ).'" id="bmlt_semantic_form_changes_sb_id_label'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_changes_sb_id_label">';
        $ret .= $this->localize_string ( 'sb_id_changes' );
        $ret .= '</label>';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars ( $this->_myJSName ).'.handleChangesSBSelectChange(this)';
        $ret .= '<select id="bmlt_switcher_changes_sb_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_switcher_changes_sb_select" onchange="'.htmlspecialchars ( $function_string ).'">';
        $ret .= '<option value="" selected="selected">'.$this->localize_string ( 'defaultChangeSBSelect' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div class="clear_both"></div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_fields_html()
    {
        $ret = '<fieldset id="bmlt_semantic_form_main_fields_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_main_fields_fieldset" style="display:none"><legend id="bmlt_semantic_form_main_fields_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_main_fields_fieldset_legend">';
        $ret .= $this->get_wizard_page_field_select_html ( 'main_' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '<div id="bmlt_semantic_form_main_fields_fieldset_contents_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_main_fields_fieldset_contents_div" style="display:block">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_field_value_div_no_options_blurb'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div" style="display:none">'.$this->localize_string ( 'no_addl_options' ).'</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_field_value_div_no_selected_formats_blurb'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div" style="display:none">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<p>'.$this->localize_string ( 'no_selected_formats_blurb' ).'</p>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<p>'.$this->localize_string ( 'or_note' ).'</p>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_field_value_div_formats'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_switcher_field_value_div_formats" style="display:none"></div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_meeting_search_html()
    {
        $ret = '<div id="bmlt_semantic_form_meeting_search_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_meeting_search_div">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<fieldset id="bmlt_semantic_form_weekday_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_weekday_fieldset"><legend id="bmlt_semantic_form_weekday_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_weekday_fieldset_legend">';
        $ret .= $this->localize_string ( 'weekday_section_legend' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note1' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note2' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'or_note' ).'</p>';
        $ret .= '</div>';
        $iStart = intval ( $this->localize_string ( 'startDay' ) );
        for ( $i = 0; $i < 7; $i++ )
            {
            $day_int = $iStart + $i;
            if ( $day_int > 7 )
                {
                $day_int = 1;
                }
            $name = $this->localize_string ( 'weekday'.$day_int );
            $value = $day_int;
            
            $ret .= $this->make_checkbox_html ( $name, 'bmlt_semantic_form_weekday_checkbox_'.$value, FALSE, $value, 'handleWeekdayCheckbox' );
            }
        $ret .= '<div class="clear_both"></div>';
        $ret .= '</fieldset>';
        $ret .= '<fieldset id="bmlt_semantic_form_weekday_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_weekday_fieldset"><legend id="bmlt_semantic_form_weekday_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_weekday_fieldset_legend">';
        $ret .= $this->localize_string ( 'weekday_section_negative_legend' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note3' ).'</p>';
        $ret .= '</div>';
        $iStart = intval ( $this->localize_string ( 'startDay' ) );
        for ( $i = 0; $i < 7; $i++ )
            {
            $day_int = $iStart + $i;
            if ( $day_int > 7 )
                {
                $day_int = 1;
                }
            $name = $this->localize_string ( 'weekday'.$day_int );
            $value = $day_int;
            
            $ret .= $this->make_checkbox_html ( $name, 'bmlt_semantic_form_un_weekday_checkbox_'.$value, FALSE, $value, 'handleWeekdayCheckbox' );
            }
        $ret .= '<div class="clear_both"></div>';
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';  
        $ret .= '<fieldset id="bmlt_semantic_form_formats_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset"><legend id="bmlt_semantic_form_formats_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset_legend">';
        $ret .= $this->localize_string ( 'format_section_legend' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note1' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note2' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'and_note' ).'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_formats_fieldset_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset_div"></div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<fieldset id="bmlt_semantic_form_un_formats_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset"><legend id="bmlt_semantic_form_un_formats_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset_legend">';
        $ret .= $this->localize_string ( 'un_format_section_legend' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note1' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note2' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'and_note' ).'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_un_formats_fieldset_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset_div"></div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<fieldset id="bmlt_semantic_form_keys_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_keys_fieldset"><legend id="bmlt_semantic_form_keys_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_keys_fieldset_legend">';
        $ret .= $this->get_wizard_page_field_select_html();
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '<div id="bmlt_semantic_form_fields_blurb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note1' ).'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_meeting_fields_fieldset_contents_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_meeting_fields_fieldset_contents_div" style="display:none">';
        $ret .= $this->get_wizard_page_field_value_select_html();
        $ret .= '</div>';
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<fieldset id="bmlt_semantic_form_sb_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_sb_fieldset"><legend id="bmlt_semantic_form_sb_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_sb_fieldset_legend">';
        $ret .= $this->localize_string ( 'service_bodies_section_legend' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note1' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'all_unselected_note2' ).'</p>';
        $ret .= '<p>'.$this->localize_string ( 'or_note' ).'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_sb_fieldset_div'.htmlspecialchars ( $this->_myJSName ).'"></div>';
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_field_select_html( $inID = ''
                                                )
    {
        $ret = '<label id="bmlt_semantic_form_field_'.htmlspecialchars ( $inID ).'select_label'.htmlspecialchars ( $this->_myJSName ).'" for="bmlt_semantic_form_field_'.htmlspecialchars ( $inID ).'select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_field_select_label">'.$this->localize_string ( 'keys_section_label' ).'</label>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars ( $this->_myJSName ).'.handleFieldKeySelectChange(this)';
        $ret .= '<select id="bmlt_semantic_form_field_'.htmlspecialchars ( $inID ).'select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_field_select" onchange="'.htmlspecialchars ( $function_string ).'">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '<option value="" selected="selected"';
        if ( $inID )
            {
            $ret .= ' disabled="disabled"';
            }
        $ret .= '>'.$this->localize_string ( $inID ? 'defaultFieldSelect' : 'defaultMeetingFieldSelect' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_field_value_select_html()
    {
        $ret = '<label id="bmlt_semantic_form_value_select_label'.htmlspecialchars ( $this->_myJSName ).'" for="bmlt_semantic_form_value_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_value_select_label">'.$this->localize_string ( 'values_section_label' ).'</label>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars ( $this->_myJSName ).'.fieldValueChosen(this)';
        $ret .= '<select id="bmlt_semantic_form_value_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_value_select" onchange="'.htmlspecialchars ( $function_string ).'">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '<option value="" selected="selected" disabled="disabled">'.$this->localize_string ( 'defaultPresetValueSelect' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<input type="text" id="bmlt_semantic_form_value_text'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_value_text" value="'.$this->localize_string ( 'value_prompt_text_item' ).'" />';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function make_checkbox_html (   $in_label_text,
                                    $in_base_id,
                                    $in_checked = FALSE,
                                    $in_value = NULL,
                                    $in_onChange = NULL
                                )
    {
        $ret = '<div id="'.htmlspecialchars ( $in_base_id.'_container_div'.$this->_myJSName ).'" class="bmlt_weekday_checkbox_container">';
        $ret .= '<input type="checkbox" id="'.htmlspecialchars ( $in_base_id.$this->_myJSName ).'" class="bmlt_checkbox_input"';
        
        if ( $in_checked )
            {
            $ret .= ' checked="checked"';
            }
        
        if ( $in_value )
            {
            $ret .= ' value="'.htmlspecialchars ( $in_value ).'"';
            }
            
        if ( $in_onChange )
            {
            $function_string = 'bmlt_semantic_js_object'.htmlspecialchars ( $this->_myJSName ).'.'.$in_onChange.'(this)';
            $ret .= ' onchange="'.$function_string.'"';
            }
        
        $ret .= ' /><label for="'.htmlspecialchars ( $in_base_id.$this->_myJSName ).'" id="'.htmlspecialchars ( $in_base_id.'_label'.$this->_myJSName ).'" class="bmlt_checkbox_label">'.htmlspecialchars ( $in_label_text ).'</label>';
        $ret .= '</div>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  
        
        \returns the HTML.
    */
    /**************************************************************/
    function get_wizard_page_switcher_type_select_html()
    {
        $ret = '<label id="bmlt_semantic_form_switcher_type_select_label'.htmlspecialchars ( $this->_myJSName ).'" for="bmlt_semantic_form_switcher_type_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_switcher_type_select_label">'.$this->localize_string ( 'switcher_type_selector_prompt' ).'</label>';
        $ret .= '<select id="bmlt_semantic_form_switcher_type_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_switcher_type_select">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<option value="GetSearchResults" selected="selected">'.$this->localize_string ( 'switcher_type_selector_results' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_formats_option'.htmlspecialchars ( $this->_myJSName ).'" value="GetFormats">'.$this->localize_string ( 'switcher_type_selector_formats' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_sb_option'.htmlspecialchars ( $this->_myJSName ).'" value="GetServiceBodies">'.$this->localize_string ( 'switcher_type_selector_sb' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_changes_option'.htmlspecialchars ( $this->_myJSName ).'" value="GetChanges">'.$this->localize_string ( 'switcher_type_selector_changes' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_fieldkey_option'.htmlspecialchars ( $this->_myJSName ).'" value="GetFieldKeys">'.$this->localize_string ( 'switcher_type_selector_field_keys' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_fieldval_option'.htmlspecialchars ( $this->_myJSName ).'" value="GetFieldValues">'.$this->localize_string ( 'switcher_type_selector_field_values' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_naws_option'.htmlspecialchars ( $this->_myJSName ).'" value="GetNAWSDump">'.$this->localize_string ( 'switcher_type_selector_naws' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
};

