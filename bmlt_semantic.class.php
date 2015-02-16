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
                throw new Exception ( "curl failure calling $in_uri, ".curl_error ( $resource ).", ".curl_errno ( $resource ) );
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
    function __construct (  $inBaseURI,
                            $inHttpVars
                            )
    {
        // If we have a root server passed in, we set that to our local data member, and remove it from the parameter array.
        if ( isset ( $inBaseURI ) && $inBaseURI )
            {
            $this->_bmltRootServerURI = trim ( $inBaseURI, '/' );
            }
        
        // Get any switcher.
        if ( isset ( $inHttpVars['switcher'] ) && $inHttpVars['switcher'] )
            {
            $this->_switcher = $inHttpVars['switcher'];
            unset ( $inHttpVars['switcher'] );
            }
        
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
        
        // See if we are an AJAX callback.
        $ajaxCall = isset ( $inHttpVars['ajaxCall'] );
        unset ( $inHttpVars['ajaxCall'] );
        
        // Determine our URI for callbacks. Account for unusual ports and HTTPS.
        $https = isset ( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] && (strtolower ( trim ( $_SERVER['HTTPS'] ) ) != 'off'); // IIS puts "off" in this field, so we need to test for that.
    
        $port = intval ( $_SERVER['SERVER_PORT'] );
    
        $port = ($https && ($port == 443)) || (!$https && ($port == 80)) ? '' : ':'.$port;
        
        $url_path = 'http'.($https ? 's' : '').'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['PHP_SELF'];

        $this->_myURI = $url_path;      // This is the base for callbacks.
        
        $this->_httpVars = $inHttpVars; // Hang onto the rest.

        // This is the name of our JavaScript object.
        $this->_myJSName = ($this->_bmltRootServerURI ? '_'.preg_replace ( '|[^a-z0-9A-Z_]+|', '', htmlspecialchars ( $this->_bmltRootServerURI ) ) : '');

        if ( $ajaxCall )    // If we are an AJAX callback, then we immediately go there.
            {
            $this->ajax_handler();
            exit(); // GBCW
            }
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
                echo ( bmlt_semantic::call_curl ( $this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFormats' ) );
                }
            elseif ( isset ( $this->_httpVars['GetInitialServiceBodies'] ) )
                {
                echo ( bmlt_semantic::call_curl ( $this->_bmltRootServerURI.'/client_interface/json/?switcher=GetServiceBodies' ) );
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
        return htmlspecialchars ( $this->_localization[$in_string] );
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page.
        
        \returns the HTML for the page.
    */
    /**************************************************************/
    function get_wizard_page_html()
    {
        $ret = '<form id="bmlt_semantic_form'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form" action="'.htmlspecialchars ( $this->_myURI ).'" method="POST">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= $this->get_root_server_element();
        
        $ret .= $this->get_wizard_page_main_fieldset_html();
        
        // Add the JavaScript to the form.
        $ret .= '<script type="text/javascript">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= bmlt_semantic::strip_script ( 'bmlt_semantic.js' );
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= 'var bmlt_semantic_js_object'.$this->_myJSName.' = new BMLTSemantic ( \''.$this->_myJSName.'\', \''.$this->_myURI.'?ajaxCall\' );';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</script>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        // Add the scoped CSS.
        $ret .= '<style type="text/css" scoped>';
        $ret .= bmlt_semantic::strip_script ( 'bmlt_semantic.css' );
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</style>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</form>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
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
        $ret .= '<option value="kml">'.$this->localize_string ( 'response_type_selector_type_kml' ).'</option>';
        $ret .= '<option value="gpx">'.$this->localize_string ( 'response_type_selector_type_gpx' ).'</option>';
        $ret .= '<option value="poi">'.$this->localize_string ( 'response_type_selector_type_poi' ).'</option>';
        $ret .= '<option value="simple">'.$this->localize_string ( 'response_type_selector_type_simple' ).'</option>';
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
        $ret .= $this->get_wizard_page_meeting_search_html();     
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
            }
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';  
        $ret .= '<fieldset id="bmlt_semantic_form_sb_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_sb_fieldset"><legend id="bmlt_semantic_form_sb_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_sb_fieldset_legend">';
        $ret .= $this->localize_string ( 'service_bodies_section_legend' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '</fieldset>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
        $ret .= '<fieldset id="bmlt_semantic_form_formats_fieldset'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset"><legend id="bmlt_semantic_form_formats_fieldset_legend'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_formats_fieldset_legend">';
        $ret .= $this->localize_string ( 'format_section_legend' );
        $ret .= '</legend>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';   
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
    function get_wizard_page_switcher_type_select_html()
    {
        $ret = '<label id="bmlt_semantic_form_switcher_type_select_label'.htmlspecialchars ( $this->_myJSName ).'" for="bmlt_semantic_form_switcher_type_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_switcher_type_select_label">'.$this->localize_string ( 'switcher_type_selector_prompt' ).'</label>';
        $ret .= '<select id="bmlt_semantic_form_switcher_type_select'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_switcher_type_select">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<option value="GetSearchResults" selected="selected">'.$this->localize_string ( 'switcher_type_selector_results' ).'</option>';
        $ret .= '<option value="GetFormats">'.$this->localize_string ( 'switcher_type_selector_formats' ).'</option>';
        $ret .= '<option value="GetServiceBodies">'.$this->localize_string ( 'switcher_type_selector_sb' ).'</option>';
        $ret .= '<option value="GetChanges">'.$this->localize_string ( 'switcher_type_selector_changes' ).'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_naws_option'.htmlspecialchars ( $this->_myJSName ).'" value="GetNAWSDump">'.$this->localize_string ( 'switcher_type_selector_naws' ).'</option>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the root server URI (either a hidden element, or a text element).
        
        \returns the HTML for the element.
    */
    /**************************************************************/
    function get_root_server_element()
    {
        $ret = '<div id="bmlt_semantic_form_root_server_text_input_div'.htmlspecialchars ( $this->_myJSName ).'" class="bmlt_semantic_form_root_server_text_input_div">';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';
        $ret .= '<input id="bmlt_semantic_form_root_server_text_input'.htmlspecialchars ( $this->_myJSName ).'" type="'.($this->_bmltRootServerURI ? 'hidden' : 'text').'" value="';
        
        if ( $this->_bmltRootServerURI )
            {
            $ret .= htmlspecialchars ( $this->_bmltRootServerURI );
            }
        else
            {
            $ret .= $this->localize_string ( 'root_server_prompt_text_item' );
            }
        
        if ( !$this->_bmltRootServerURI )
            {
            $ret .= '" class="bmlt_semantic_form_root_server_text_input';
            }
        
        $ret .= '" />';
        $ret .= defined ( 'DEBUG' ) ? "\n" : '';

        if ( !$this->_bmltRootServerURI )
            {
            $ret .= '<input type="button" id="bmlt_semantic_form_root_server_refresh_button'.htmlspecialchars ( $this->_myJSName ).'" value="'.$this->localize_string ( 'root_server_button_title' ).'" class="bmlt_semantic_form_root_server_refresh_button" onclick="bmlt_semantic_js_object'.$this->_myJSName.'.reloadFromServer()" />';
            $ret .= defined ( 'DEBUG' ) ? "\n" : '';
            }
        
        $ret .= '</div>';
        
        return $ret;
    }
};

