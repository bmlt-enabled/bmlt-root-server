<?php
/***************************************************************************************************************************************/
/**
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
/***************************************************************************************************************************************/

define('__VERSION__', '1.3.3');

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class bmlt_semantic
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
{
    protected $_httpVars;
    protected $_bmltRootServerURI;
    protected $_switcher;
    protected $_myURI;
    protected $_myLang;
    protected $_localization;
    protected $_myJSName;
    protected $_langs;
    protected $_version;
    protected $_keys;
    protected $_apiKey;
    
    /**************************************************************/
    /** \brief  Class function that strips all the BS from a JS or CSS file.

        \returns the stripped-down JS.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function strip_script($in_filename)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = null;
        
        if (!preg_match("|/|", $in_filename)) {
            if (preg_match("|.*?\.js$|", $in_filename) || preg_match("|.*?\.css$|", $in_filename)) {
                $ret = file_get_contents(dirname(__FILE__)."/$in_filename");
                if (!defined('DEBUG')) { // If we are in release mode, we strip out all the comments and whitespace (including line endings).
                    $ret = preg_replace("|\/\/.*?[\n\r]|s", "\n", $ret);  // Block comments.
                    $ret = preg_replace("|\/\*(.*?)\*\/|s", "", $ret);    // Line comments.
                    $ret = preg_replace("|[\ \t]+|s", " ", $ret);         // Tabs and spaces.
                    $ret = preg_replace("|\n[\ \t\n\r]+|s", "\n", $ret);  // Beginning line tabs and spaces.
                    $ret = preg_replace("|[\s]+|s", " ", $ret);           // All whitespace, including line endings, replaced by a single space.
                }
            } else {
                die("FILE MUST BE A .JS or .CSS FILE!");
            }
        } else {
            die("YOU CANNOT LEAVE THE DIRECTORY!");
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
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function call_curl(
        $in_uri,
        &$in_out_http_status = null
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = null;
    
        // If the curl extension isn't loaded, we try one backdoor thing. Maybe we can use file_get_contents.
        if (!extension_loaded('curl')) {
            if (ini_get('allow_url_fopen')) {
                $ret = file_get_contents($in_uri);
            }
        } else {
            // This gets the session as a cookie.
            if (isset($_COOKIE['PHPSESSID']) && $_COOKIE['PHPSESSID']) {
                $strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';

                session_write_close();
            }

            // Create a new cURL resource.
            $resource = curl_init();
        
            if (isset($strCookie)) {
                curl_setopt($resource, CURLOPT_COOKIE, $strCookie);
            }
        
            // Set url to call.
            curl_setopt($resource, CURLOPT_URL, $in_uri);
        
            // Make curl_exec() function (see below) return requested content as a string (unless call fails).
            curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
        
            // By default, cURL prepends response headers to string returned from call to curl_exec().
            // You can control this with the below setting.
            // Setting it to false will remove headers from beginning of string.
            // If you WANT the headers, see the Yahoo documentation on how to parse with them from the string.
            curl_setopt($resource, CURLOPT_HEADER, false);
        
            // Set maximum times to allow redirection (use only if needed as per above setting. 3 is sort of arbitrary here).
            curl_setopt($resource, CURLOPT_MAXREDIRS, 3);
        
            // Set connection timeout in seconds (very good idea).
            curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 10);
        
            // Direct cURL to send request header to server allowing compressed content to be returned and decompressed automatically (use only if needed).
            curl_setopt($resource, CURLOPT_ENCODING, 'gzip,deflate');
            
            // Pretend we're a browser, so that anti-cURL settings don't pooch us.
            curl_setopt($resource, CURLOPT_USERAGENT, "cURL Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20130401 Firefox/21.0");
            
            // Trust meeeee...
            curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, false);
            
            // Execute cURL call and return results in $content variable.
            $content = curl_exec($resource);
        
            // Check if curl_exec() call failed (returns false on failure) and handle failure.
            if ($content !== false) {
                // Do what you want with returned content (e.g. HTML, XML, etc) here or AFTER curl_close() call below as it is stored in the $content variable.
        
                // You MIGHT want to get the HTTP status code returned by server (e.g. 200, 400, 500).
                // If that is the case then this is how to do it.
                if (isset($in_out_http_status) && (null != $in_out_http_status)) {
                    $in_out_http_status = curl_getinfo($resource, CURLINFO_HTTP_CODE);
                }
            }
        
            // Close cURL and free resource.
            curl_close($resource);
        
            // Maybe echo $contents of $content variable here.
            if ($content !== false) {
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
    public function __construct($inHttpVars)
    {
        // Get any language
        $this->_myLang = 'en';

        if (isset($inHttpVars['lang']) && $inHttpVars['lang']) {
            $this->_myLang = $inHttpVars['lang'];
            unset($inHttpVars['lang']);
        }

        if (isset($inHttpVars['google_api_key']) && $inHttpVars['google_api_key']) {
            $this->_apiKey = $inHttpVars['google_api_key'];
            unset($inHttpVars['google_api_key']);
        }
    
        // Prevent dope fiending...
        $this->_myLang = trim(strtolower(preg_replace('|[^a-z0-9A-Z]+|', '', $this->_myLang)));
    
        if (!file_exists(dirname(__FILE__) . '/lang/'.$this->_myLang.'.inc.php')) {
            $this->_myLang = 'en';
        }
        
        include(dirname(__FILE__) . '/lang/'.$this->_myLang.'.inc.php');
        if (isset($inHttpVars['root_server'])) {
            $this->_bmltRootServerURI = trim($inHttpVars['root_server'], '/');
            $this->_myURI = $this->_bmltRootServerURI.'/semantic/index.php'; // This is the base for AJAX callbacks.
            unset($inHttpVars['root_server']);

            // Get any switcher.
            if (isset($inHttpVars['switcher']) && $inHttpVars['switcher']) {
                $this->_switcher = $inHttpVars['switcher'];
                unset($inHttpVars['switcher']);
            }
        
            // See if we are an AJAX callback.
            $ajaxCall = isset($inHttpVars['ajaxCall']);
            unset($inHttpVars['ajaxCall']);
        
            $this->_httpVars = $inHttpVars;     // Hang onto the rest.

            // This is the name of our JavaScript object.
            $this->_myJSName = ($this->_bmltRootServerURI ? '_'.preg_replace('|[^a-z0-9A-Z_]+|', '', htmlspecialchars($this->_bmltRootServerURI)) : '');

            if ($ajaxCall) {    // If we are an AJAX callback, then we immediately go there.
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
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_server_version()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = array();
        
        if ($this->_bmltRootServerURI) {
            $error = null;
        
            $uri = $this->_bmltRootServerURI.'/client_interface/serverInfo.xml';
            $xml = self::call_curl($uri, $error);

            if (!$error && $xml) {
                $info_file = new DOMDocument;
                if ($info_file instanceof DOMDocument) {
                    if (@$info_file->loadXML($xml)) {
                        $has_info = $info_file->getElementsByTagName("bmltInfo");
                
                        if (($has_info instanceof domnodelist) && $has_info->length) {
                            $nodeVal = $has_info->item(0)->nodeValue;
                            $ret = explode('.', $nodeVal);
                        
                            if (!isset($ret[1])) {
                                $ret[1] = 0;
                            }
                        
                            if (!isset($ret[2])) {
                                $ret[2] = 0;
                            }
                        
                            $this->_version = (intval($ret[0]) * 1000000) + (intval($ret[1]) * 1000) + intval($ret[2]);
                        }
                    }
                }
            }
        }
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_server_langs()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = array ( );
        
        if ($this->_bmltRootServerURI) {
            $error = null;
        
            $uri = $this->_bmltRootServerURI.'/client_interface/xml/GetLangs.php';
            $xml = self::call_curl($uri, $error);

            if (!$error && $xml) {
                $info_file = new DOMDocument;
                if ($info_file instanceof DOMDocument) {
                    if (@$info_file->loadXML($xml)) {
                        $languages = $info_file->getElementsByTagName("language");
                
                        if (($languages instanceof domnodelist) && $languages->length) {
                            for ($index = 0; $index < $languages->length; $index++) {
                                $language = $languages->item($index);
                                $attributes = $language->attributes;
                                $key = $attributes->getNamedItem("key")->nodeValue;
                                $name = $language->nodeValue;
                                
                                $ret[$key] = $name;
                            }
                        }
                    }
                }
            }
        }
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_server_keys()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if ($this->_keys) {
            $ret = $this->_keys;
        } elseif ($this->_bmltRootServerURI) {
            $csv_data = self::call_curl($this->_bmltRootServerURI.'/client_interface/csv/?switcher=GetFieldKeys');
            $fp = fopen("php://memory", "r+");
            fputs($fp, $csv_data);
            rewind($fp);
            $first = true;
            while (($line = fgetcsv($fp)) !== false) {
                if ($first) {
                    $first = false;
                    continue;
                }

                if (count($line) == 2) {
                    $key = $line[0];
                    $name = $line[1];
                    $this->_keys[$key] = $name;
                }
            }
        }
        
        return $this->_keys;
    }
    
    /**************************************************************/
    /** \brief  Handles AJAX callbacks.

                This assumes that the $this->_httpVars data member
                is valid.

                This funtion is called automatically upon instantiation.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function ajax_handler()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (isset($this->_bmltRootServerURI) && $this->_bmltRootServerURI) {
            if (isset($this->_httpVars['GetInitialFormats'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFormats') );
            } elseif (isset($this->_httpVars['GetInitialServiceBodies'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetServiceBodies') );
            } elseif (isset($this->_httpVars['GetFieldKeys'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFieldKeys') );
            } elseif (isset($this->_httpVars['GetFieldValues'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFieldValues&meeting_key='.$this->_httpVars['meeting_key']) );
            } elseif (isset($this->_httpVars['GetFieldValues'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetFieldValues&meeting_key='.$this->_httpVars['meeting_key']) );
            } elseif (isset($this->_httpVars['GetLangs'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetServerInfo') );
            } elseif (isset($this->_httpVars['GetServerInfo'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetServerInfo') );
            } elseif (isset($this->_httpVars['GetCoverageArea'])) {
                echo ( self::call_curl($this->_bmltRootServerURI.'/client_interface/json/?switcher=GetCoverageArea') );
            }
        }
    }
    
    /**************************************************************/
    /** \brief  Localizes a string token.

        \param in_string the token to be localized.

        \returns the localized string for the token.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function localize_string($in_string)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return htmlspecialchars(isset($this->_localization[$in_string]) ? $this->_localization[$in_string] : $in_string);
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page.

        \returns the HTML for the page.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '';
        
        $this->get_server_version();
        $version = $this->_version;
        $this->_langs = $this->get_server_langs();
        $this->_keys = $this->get_server_keys();
        $ret .= '<div id="bmlt_semantic'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic">';
        $ret .= defined('DEBUG') ? "\n" : '';
        // Add the scoped CSS.
        $ret .= '<style type="text/css" scoped>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= bmlt_semantic::strip_script('bmlt_semantic.css');
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</style>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<noscript>'.$this->localize_string('javascript_noscript').'</noscript>';
        $ret .= '<h1 id="bmlt_semantic_badserver_h1'.htmlspecialchars($this->_myJSName).'" style="display:none">'.$this->localize_string('need_good_url').'</h1>';
        $ret .= '<h1 id="bmlt_semantic_header_h1'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_header_h1">'.$this->localize_string('title_of_page').'</h1>';
            
        if ($version >= 2006015) {
            $ret .= '<div id="bmlt_main_blurb'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div bmlt_main_blurb">';
            $ret .= '<p>'.$this->localize_string('main_blurb1').'</p>';
            $ret .= '<p>'.$this->localize_string('main_blurb2').'</p>';
            $ret .= '<p>'.$this->localize_string('main_blurb3').'</p>';
            $ret .= '<p>'.$this->localize_string('main_blurb4').'</p>';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_header'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_header">';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_info_div_download_line'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_info_line bmlt_semantic_info_div_download_line">';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_info_div_result_url_wrapper'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_info_div_result_url_wrapper">';
            $ret .= '<span class="info_label">'.$this->localize_string('result_url_label').'</span><span class="info_value"><span id="bmlt_semantic_info_div_url_active_span'.htmlspecialchars($this->_myJSName).'"></span><span id="bmlt_semantic_info_div_url_Invalid_span'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('result_invalid_text').'</span></span>';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_info_div_shortcode_line'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_info_line bmlt_semantic_info_div_shortcode_line" style="display:none">';
            $ret .= '<div id="bmlt_semantic_info_div_shortcode_wrapper'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_info_div_shortcode_wrapper">';
            $ret .= '<span class="info_label">'.$this->localize_string('result_shortcode_label').'</span><span class="info_value"><span id="bmlt_semantic_info_div_shortcode_active_span'.htmlspecialchars($this->_myJSName).'"></span><span id="bmlt_semantic_info_div_shortcode_Invalid_span'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('result_invalid_text').'</span></span>';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<div class="clear_both"></div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';

            $ret .= '<form id="bmlt_semantic_form'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form" action="'.htmlspecialchars($this->_myURI).'" method="get">';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<div id="bmlt_semantic_form_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_div">';
            $ret .= defined('DEBUG') ? "\n" : '';
        
            $ret .= $this->get_wizard_page_main_fieldset_html();
        
            // Add the JavaScript to the form.
            $ret .= '<script type="text/javascript">';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= bmlt_semantic::strip_script('bmlt_semantic.js');
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= 'var bmlt_semantic_js_object'.$this->_myJSName.' = new BMLTSemantic ( \''.$this->_myJSName.'\', \''.$this->_myURI.'?ajaxCall&root_server='.urlencode($this->_bmltRootServerURI).'\', \''.$this->_bmltRootServerURI.'\', '.intval($version).' );';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</script>';
            $ret .= defined('DEBUG') ? "\n" : '';
        
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            
            $ret .= '</form>';
            $ret .= defined('DEBUG') ? "\n" : '';
                        
            $ret .= '<div id="bmlt_semantic_footer'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_footer">';
            
            $ret .= '<div id="bmlt_semantic_info_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_info_div">';
            
            $ret .= $this->footerDiv();
            
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
        } else {
            if ($this->_bmltRootServerURI) {
                if ($version < 2006015) {
                    $ret .= '<h2 id="server_url_invalid_note_h2">'.$this->localize_string('need_higher_version').'</h2>';
                } else {
                    $ret .= '<h2 id="server_url_invalid_note_h2">'.$this->localize_string('need_good_url').'</h2>';
                }
            }
            
            $ret .= '<form id="enter_server_url_form" class="enter_server_url_form" action="'.$_SERVER['PHP_SELF'].'" method="get">';
            $ret .= '<div id="enter_server_url_form_div" class="enter_server_url_form_div">';
            $ret .= '<style type="text/css" scoped>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= bmlt_semantic::strip_script('bmlt_semantic.css');
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</style>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<label id="enter_server_url_form_div_label" class="enter_server_url_form_div_label" for="enter_server_url_form_div_url_input">'.$this->localize_string('enter_url_label').'</label>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<input type="text" size="64" id="enter_server_url_form_div_url_input" class="enter_server_url_form_div_url_input" defaultValue="Enter A URL" name="root_server" />';
            $ret .= '<input type="submit" class="formEntrySubmit" value="'.$this->localize_string('submit_button_name').'" />';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</form>';
            $ret .= defined('DEBUG') ? "\n" : '';
            
            $ret .= $this->footerDiv();
        }
        
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page footer.

        \returns the HTML for the div.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function footerDiv()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<div id="bmlt_semantic_info_div_root_url_line'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_info_line bmlt_semantic_footer">';
        $ret .= '<div id="bmlt_semantic_version_wrapper'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_version_wrapper"><span class="info_label">'.$this->localize_string('version_label').'</span><span class="info_value">'.__VERSION__.'</span></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        if ($this->_bmltRootServerURI) {
            $v_array = $this->get_server_version();
            $ret .= '<div id="bmlt_semantic_info_div_root_url_wrapper'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_info_div_root_url_wrapper"><span class="info_label">'.$this->localize_string('root_url_label').'</span><span class="info_value"><a href="'.$this->_bmltRootServerURI.'" target="_blank">'.htmlspecialchars($this->_bmltRootServerURI).'</a> ('.$v_array[0].'.'.$v_array[1].'.'.$v_array[2].')</span></div>';
            $ret .= defined('DEBUG') ? "\n" : '';
        }

        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= htmlspecialchars_decode($this->localize_string('explanation_suffix'));
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page main fieldset.

        \returns the HTML for the fieldset.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_main_fieldset_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<fieldset id="bmlt_semantic_form_main_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_main_fieldset"><legend id="bmlt_semantic_form_main_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_main_fieldset_legend">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= $this->get_wizard_page_main_select_html();
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div class="block_mode_checkbox_div" id="block_mode_checkbox_div'.htmlspecialchars($this->_myJSName).'" style="display:none">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleBlockCheckboxChange(this)';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<input type="checkbox" id="block_mode_checkbox'.htmlspecialchars($this->_myJSName).'" class="block_mode_checkbox" onchange="'.$function_string.'" />';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label for="block_mode_checkbox'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('block_mode_checkbox_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= $this->get_wizard_page_direct_url_html();
        $ret .= $this->get_wizard_page_switcher_fieldset_html();
        
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_main_select_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<label id="bmlt_semantic_form_main_mode_select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_main_mode_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_main_mode_select_label">'.$this->localize_string('select_option_text_prompt').'</label>';
        $ret .= '<select id="bmlt_semantic_form_main_mode_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_main_mode_select">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<option value="DOWNLOAD" selected="selected">'.$this->localize_string('select_option_text_direct_url').'</option>';
        $ret .= '<option value="SHORTCODE_SIMPLE">'.$this->localize_string('select_option_text_cms_simple').'</option>';
        if ($this->_version >= 2007007) {
            $ret .= '<option value="SHORTCODE_TABLE">'.$this->localize_string('select_option_text_cms_table').'</option>';
        }
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_direct_url_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<div id="bmlt_semantic_form_direct_url_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_direct_url_div">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= $this->get_wizard_page_response_type_select_html();
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_response_type_select_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<label id="bmlt_semantic_form_response_type_select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_response_type_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_response_type_select_label">'.$this->localize_string('response_type_selector_prompt').'</label>';
        $ret .= '<select id="bmlt_semantic_form_response_type_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_response_type_select">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<option value="csv" selected="selected">'.$this->localize_string('response_type_selector_type_csv').'</option>';
        $ret .= '<option value="xml">'.$this->localize_string('response_type_selector_type_xml').'</option>';
        $ret .= '<option value="json">'.$this->localize_string('response_type_selector_type_json').'</option>';
        $ret .= '<option id="bmlt_semantic_form_response_type_select_kml_option'.htmlspecialchars($this->_myJSName).'" value="kml">'.$this->localize_string('response_type_selector_type_kml').'</option>';
        $ret .= '<option id="bmlt_semantic_form_response_type_select_gpx_option'.htmlspecialchars($this->_myJSName).'" value="gpx">'.$this->localize_string('response_type_selector_type_gpx').'</option>';
        $ret .= '<option id="bmlt_semantic_form_response_type_select_poi_option'.htmlspecialchars($this->_myJSName).'" value="poi">'.$this->localize_string('response_type_selector_type_poi').'</option>';
        $ret .= '<option value="simple-block">'.$this->localize_string('response_type_selector_type_simple_block').'</option>';
        $ret .= '<option value="simple">'.$this->localize_string('response_type_selector_type_simple_table').'</option>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief  Outputs the HTML for the wizard page switcher fieldset.

        \returns the HTML for the fieldset.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_switcher_fieldset_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<fieldset id="bmlt_semantic_form_switcher_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_switcher_fieldset"><legend id="bmlt_semantic_form_switcher_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_switcher_fieldset_legend">';
        $ret .= $this->get_wizard_page_switcher_type_select_html();
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_div_no_options_blurb'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div" style="display:none">'.$this->localize_string('no_addl_options').'</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_naws_dump_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_switcher_naws_dump_div" style="display:none">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleNAWSDumpSelectChange(this)';
        $ret .= '<select id="bmlt_switcher_naws_dump_sb_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_switcher_naws_dump_sb_select" onchange="'.htmlspecialchars($function_string).'">';
        $ret .= '<option value="" disabled="disabled" selected="selected">'.$this->localize_string('defaultSBSelect').'</option>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= $this->get_wizard_page_meeting_search_html();
        $ret .= $this->get_wizard_page_changes_html();
        $ret .= $this->get_wizard_page_fields_html();
        $ret .= $this->get_wizard_page_schema_select_html();
        $ret .= $this->get_wizard_page_formats_html();
        $ret .= $this->get_wizard_page_coverage_area_html();
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_changes_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<div id="bmlt_semantic_form_changes_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_div" style="display:none">';
        $ret .= '<div id="bmlt_semantic_form_changes_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('date_format1').'</p>';
        $ret .= '<p>'.$this->localize_string('date_format2').'</p>';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_from_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_line">';
        $ret .= '<label title="'.$this->localize_string('date_format1').'" for="bmlt_semantic_form_changes_from_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_changes_from_label'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_from_label">';
        $ret .= $this->localize_string('changes_from');
        $ret .= '</label>';
        $ret .= '<input type="text" pattern="^[0-9\-]+$" title="'.$this->localize_string('date_format1').'" id="bmlt_semantic_form_changes_from_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_date_text" value="'.$this->localize_string('default_date').'" maxlength="10" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_to_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_line">';
        $ret .= '<label title="'.$this->localize_string('date_format1').'" for="bmlt_semantic_form_changes_to_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_changes_to_label'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_to_label">';
        $ret .= $this->localize_string('changes_to');
        $ret .= '</label>';
        $ret .= '<input type="text" pattern="^[0-9\-]+$" title="'.$this->localize_string('date_format1').'" id="bmlt_semantic_form_changes_to_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_date_text" value="'.$this->localize_string('default_date').'" maxlength="10" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_meeting_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_line">';
        $ret .= '<label title="'.$this->localize_string('meeting_id_changes_tooltip').'" for="bmlt_semantic_form_changes_id_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_changes_id_label'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_id_label">';
        $ret .= $this->localize_string('meeting_id_changes');
        $ret .= '</label>';
        $ret .= '<input type="text" pattern="^[0-9]+$" title="'.$this->localize_string('meeting_id_changes_tooltip').'" id="bmlt_semantic_form_changes_id_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_id_text" value="" maxlength="6" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_changes_sb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_line">';
        $ret .= '<label for="bmlt_switcher_changes_sb_select'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_changes_sb_id_label'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_changes_sb_id_label">';
        $ret .= $this->localize_string('sb_id_changes');
        $ret .= '</label>';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleChangesSBSelectChange(this)';
        $ret .= '<select id="bmlt_switcher_changes_sb_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_switcher_changes_sb_select" onchange="'.htmlspecialchars($function_string).'">';
        $ret .= '<option value="" selected="selected">'.$this->localize_string('defaultChangeSBSelect').'</option>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div class="clear_both"></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_fields_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<fieldset id="bmlt_semantic_form_main_fields_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_main_fields_fieldset" style="display:none"><legend id="bmlt_semantic_form_main_fields_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_main_fields_fieldset_legend">';
        $ret .= $this->get_wizard_page_field_select_html('main_');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_main_fields_fieldset_contents_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_main_fields_fieldset_contents_div" style="display:block">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_field_value_div_no_options_blurb'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div" style="display:none">'.$this->localize_string('no_addl_options').'</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_field_value_div_no_selected_formats_blurb'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div" style="display:none">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<p>'.$this->localize_string('no_selected_formats_blurb').'</p>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<p>'.$this->localize_string('or_note').'</p>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_switcher_field_value_div_formats'.htmlspecialchars($this->_myJSName).'" class="bmlt_switcher_field_value_div_formats" style="display:none"></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_formats_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<div id="bmlt_semantic_form_formats_fieldset_contents_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_formats_fieldset_contents_div" style="display:none">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label id="bmlt_semantic_formats_lang_select_label'.htmlspecialchars($this->_myJSName).'select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_formats_lang_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_formats_lang_select_label">'.$this->localize_string('formats_lang_section_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleFormatsLangSelectChange(this)';
        $ret .= '<select id="bmlt_semantic_formats_lang_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_formats_lang_select" onchange="'.htmlspecialchars($function_string).'">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<option value="" selected="selected">'.$this->localize_string('formats_lang_section_option').'</option>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        foreach ($this->_langs as $key => $name) {
            $ret .= '<option value="'.$key.'">'.htmlspecialchars($name).'</option>';
            $ret .= defined('DEBUG') ? "\n" : '';
        }

        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_meeting_search_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<div id="bmlt_semantic_form_meeting_search_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_meeting_search_div">';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<div id="bmlt_semantic_form_weekday_header_checkbox_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_weekday_header_checkbox_div" style="display:none">';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleWeekdayHeaderChange(this)';
        $ret .= '<input type="checkbox" id="bmlt_semantic_form_weekday_header_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_weekday_header_checkbox" onchange="'.$function_string.'" />';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label id="bmlt_semantic_form_weekday_header_checkbox_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_weekday_header_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_weekday_header_checkbox_label">'.$this->localize_string('weekday_header_checkbox_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<div id="bmlt_semantic_form_used_formats_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_used_formats_div" style="display:none">';
        
        $ret .= '<div id="bmlt_semantic_form_used_formats_checkbox_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_used_formats_checkbox_div">';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleUsedFormatsChange(this)';
        $ret .= '<input type="checkbox" id="bmlt_semantic_form_used_formats_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_used_formats_checkbox" onchange="'.$function_string.'" />';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label id="bmlt_semantic_form_used_formats_checkbox_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_used_formats_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_used_formats_checkbox_label">'.$this->localize_string('used_formats_checkbox_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<div id="bmlt_semantic_form_just_used_formats_checkbox_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_just_used_formats_checkbox_div">';
        $ret .= '<input type="checkbox" id="bmlt_semantic_form_just_used_formats_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_just_used_formats_checkbox" disabled="disabled" onchange="'.$function_string.'" />';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label id="bmlt_semantic_form_just_used_formats_checkbox_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_just_used_formats_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_used_formats_checkbox_label">'.$this->localize_string('used_formats_only_checkbox_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_weekday_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_weekday_fieldset"><legend id="bmlt_semantic_form_weekday_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_weekday_fieldset_legend">';
        $ret .= $this->localize_string('weekday_section_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';

        $ret .= '<div id="bmlt_semantic_form_weekday_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('all_unselected_note1').'</p>';
        $ret .= '<p>'.$this->localize_string('all_unselected_note2').'</p>';
        $ret .= '<p>'.$this->localize_string('or_note').'</p>';
        $ret .= '</div>';
        $iStart = intval($this->localize_string('startDay'));
        for ($i = 0; $i < 7; $i++) {
            $day_int = $iStart + $i;
            if ($day_int > 7) {
                $day_int = 1;
            }
            $name = $this->localize_string('weekday'.$day_int);
            $value = $day_int;
            
            $ret .= $this->make_checkbox_html($name, 'bmlt_semantic_form_weekday_checkbox_'.$value, false, $value, 'handleWeekdayCheckbox');
        }
        $ret .= '<div class="clear_both"></div>';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_not_weekday_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_weekday_fieldset"><legend id="bmlt_semantic_form_not_weekday_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_weekday_fieldset_legend">';
        $ret .= $this->localize_string('weekday_section_negative_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_not_weekday_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('all_unselected_note3').'</p>';
        $ret .= '</div>';
        $iStart = intval($this->localize_string('startDay'));
        for ($i = 0; $i < 7; $i++) {
            $day_int = $iStart + $i;
            if ($day_int > 7) {
                $day_int = 1;
            }
            $name = $this->localize_string('weekday'.$day_int);
            $value = -$day_int;
            
            $ret .= $this->make_checkbox_html($name, 'bmlt_semantic_form_not_weekday_checkbox_'.abs($value), false, $value, 'handleWeekdayCheckbox');
        }
        $ret .= '<div class="clear_both"></div>';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_formats_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_formats_fieldset"><legend id="bmlt_semantic_form_formats_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_formats_fieldset_legend">';
        $ret .= $this->localize_string('format_section_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('all_unselected_note1').'</p>';
        $ret .= '<p>'.$this->localize_string('all_unselected_note2').'</p>';
        $ret .= '<p>'.$this->localize_string('configurable_operator_note').'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_formats_fieldset_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_formats_fieldset_div"></div>';
        $ret .= '<div style="display: block; padding-top: 25px">';
        $ret .= '<label id="bmlt_semantic_form_formats_comparison_operator_text_label_'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('formats_comparison_operator').'</label>';
        $ret .= '<input type="radio" name="formats_comparison_operator" id="formats_comparison_operator_radio_and" value="AND" onchange="bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleFormatsComparisonOperatorRadioButton(this)" checked>';
        $ret .= '<label>AND</label>';
        $ret .= '<input type="radio" name="formats_comparison_operator" id="formats_comparison_operator_radio_or" value="OR" onchange="bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleFormatsComparisonOperatorRadioButton(this)">';
        $ret .= '<label>OR</label>';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_un_formats_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_formats_fieldset"><legend id="bmlt_semantic_form_un_formats_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_formats_fieldset_legend">';
        $ret .= $this->localize_string('un_format_section_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('all_unselected_note1').'</p>';
        $ret .= '<p>'.$this->localize_string('all_unselected_note2').'</p>';
        $ret .= '<p>'.$this->localize_string('and_note').'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_not_formats_fieldset_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_formats_fieldset_div"></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_keys_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_keys_fieldset"><legend id="bmlt_semantic_form_keys_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_keys_fieldset_legend">';
        $ret .= $this->get_wizard_page_field_select_html();
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_fields_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('all_unselected_note1').'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_meeting_fields_fieldset_contents_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_meeting_fields_fieldset_contents_div" style="display:none">';
        $ret .= $this->get_wizard_page_field_value_select_html();
        $ret .= '</div>';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';

        $ret .= '<fieldset id="bmlt_semantic_form_sb_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_sb_fieldset"><legend id="bmlt_semantic_form_sb_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_sb_fieldset_legend">';
        $ret .= $this->localize_string('service_bodies_section_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('all_unselected_note1').'</p>';
        $ret .= '<p>'.$this->localize_string('all_unselected_note2').'</p>';
        $ret .= '<p>'.$this->localize_string('or_note').'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_sb_fieldset_div'.htmlspecialchars($this->_myJSName).'"></div>';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';

        $ret .= '<fieldset id="bmlt_semantic_form_sb_not_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_sb_fieldset"><legend id="bmlt_semantic_form_sb_not_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_sb_fieldset_legend">';
        $ret .= $this->localize_string('service_bodies_not_section_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_not_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('all_unselected_note1').'</p>';
        $ret .= '<p>'.$this->localize_string('all_unselected_note2').'</p>';
        $ret .= '<p>'.$this->localize_string('or_note').'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_sb_not_fieldset_div'.htmlspecialchars($this->_myJSName).'"></div>';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';

        $ret .= '<fieldset id="bmlt_semantic_form_text_search_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_text_search_fieldset"><legend id="bmlt_semantic_form_text_search_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_text_search_fieldset_legend">';
        $ret .= $this->localize_string('text_search_section_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('text_note1').'</p>';
        $ret .= '</div>';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleTextSearchText(this)';
        $ret .= '<div id="bmlt_semantic_form_text_search_div'.htmlspecialchars($this->_myJSName).'">';
        $ret .= '<label for="bmlt_semantic_form_text_search_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_text_search_text_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('text_search_label').'</label>';
        $ret .= '<input type="text" id="bmlt_semantic_form_text_search_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_text_search_text" value="'.$this->localize_string('value_prompt_text_item').'" />';
        $ret .= '<select id="bmlt_semantic_form_text_search_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_text_search_select" onchange="'.$function_string.'" disabled="disabled">';
        $ret .= '<option value="" selected="selected">'.$this->localize_string('text_search_type_select_option_0').'</option>';
        $ret .= '<option value="SearchStringAll=1">'.$this->localize_string('text_search_type_select_option_1').'</option>';
        $ret .= '<option value="SearchStringExact=1">'.$this->localize_string('text_search_type_select_option_2').'</option>';
        $ret .= '<option value="StringSearchIsAnAddress=1">'.$this->localize_string('text_search_type_select_option_3').'</option>';
        $ret .= '</select>';
        $ret .= '<div id="text_search_radius_input_div'.htmlspecialchars($this->_myJSName).'" class="text_search_radius_input_div" style="display:none">';
        $ret .= '<label for="bmlt_semantic_form_text_search_text_radius'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_text_search_text_radius_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('text_search_radius_label').'</label>';
        $ret .= '<input type="text" id="bmlt_semantic_form_text_search_text_radius'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_text_search_radius_text" />';
        $ret .= '<label for="bmlt_semantic_form_text_search_text_radius'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_text_search_text_radius_units_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('text_search_radius_units_label').'</label>';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_start_time_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_start_time_fieldset"><legend id="bmlt_semantic_form_start_time_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_start_time_fieldset_legend">';
        $ret .= $this->localize_string('start_time_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_start_time_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('start_time_blurb').'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_start_time_fieldset_min_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_start_time_fieldset_div">';
        $ret .= '<label for="bmlt_semantic_form_start_time_min_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_start_time_min_text_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('start_time_min_label').'</label>';
        $ret .= '<input pattern="^[0-9\:]{0,5}$" type="text" id="bmlt_semantic_form_start_time_min_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_time_text" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_start_time_fieldset_max_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_start_time_fieldset_div">';
        $ret .= '<label for="bmlt_semantic_form_start_time_max_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_start_time_max_text_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('start_time_max_label').'</label>';
        $ret .= '<input pattern="^[0-9\:]{0,5}$" type="text" id="bmlt_semantic_form_start_time_max_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_time_text" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_end_time_fieldset_max_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_end_time_fieldset_div"';
        
        if ($this->_version < 2008008) {
            $ret .= ' style="display:none"';
        }

        $ret .= '>';
        $ret .= '<label for="bmlt_semantic_form_end_time_max_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_end_time_max_text_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('end_time_max_label').'</label>';
        $ret .= '<input pattern="^[0-9\:]{0,5}$" type="text" id="bmlt_semantic_form_end_time_max_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_time_text" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_duration_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_duration_fieldset"><legend id="bmlt_semantic_form_duration_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_duration_fieldset_legend">';
        $ret .= $this->localize_string('duration_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_duration_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('duration_blurb').'</p>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_semantic_form_duration_fieldset_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_duration_fieldset_div">';
        $ret .= '<div id="bmlt_semantic_form_duration_fieldset_min_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_duration_fieldset_div">';
        $ret .= '<label for="bmlt_semantic_form_duration_min_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_duration_min_text_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('duration_min_label').'</label>';
        $ret .= '<input pattern="^[0-9\:]{0,5}$" type="text" id="bmlt_semantic_form_duration_min_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_time_text" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_duration_fieldset_max_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_duration_fieldset_div">';
        $ret .= '<label for="bmlt_semantic_form_duration_max_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_duration_max_text_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('duration_max_label').'</label>';
        $ret .= '<input pattern="^[0-9\:]{0,5}$" type="text" id="bmlt_semantic_form_duration_max_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_time_text" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<fieldset id="bmlt_semantic_form_map_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_fieldset"><legend id="bmlt_semantic_form_map_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_fieldset_legend">';
        $ret .= $this->localize_string('map_search_section_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<div id="bmlt_semantic_form_map_checkbox_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_checkbox_div">';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleMapCheckboxChange(this)';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<input type="checkbox" id="bmlt_semantic_form_map_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_checkbox" onchange="'.$function_string.'" />';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label id="bmlt_semantic_form_map_checkbox_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_map_checkbox'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_checkbox_label">'.$this->localize_string('map_search_checkbox_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
         
        $ret .= '<div id="bmlt_semantic_form_map_wrapper_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_wrapper_div" style="display:none">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<script type="text/javascript" src="https://maps.google.com/maps/api/js?key='.$this->_apiKey.'"></script>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<div id="bmlt_semantic_form_map_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_div"></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<div id="map_search_longlat_input_div'.htmlspecialchars($this->_myJSName).'" class="map_search_longlat_input_div">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label for="bmlt_semantic_form_map_search_longitude_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_map_search_text_longitude_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('text_search_longitude_label').'</label>';
        $ret .= '<input value="" type="text" pattern="^[0-9\.\-]+$" id="bmlt_semantic_form_map_search_longitude_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_search_longitude_text" />';
        $ret .= '<label for="bmlt_semantic_form_map_search_latitude_text'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_map_search_text_latitude_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('text_search_latitude_label').'</label>';
        $ret .= '<input value="" type="text" pattern="^[0-9\.\-]+$" id="bmlt_semantic_form_map_search_latitude_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_search_latitude_text" />';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '<div id="map_search_radius_input_div'.htmlspecialchars($this->_myJSName).'" class="map_search_radius_input_div">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= '<p>'.$this->localize_string('radius_note1').'</p>';
        $ret .= '<p>'.$this->localize_string('radius_note2').'</p>';
        $ret .= '<p>'.$this->localize_string('radius_note3').'</p>';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label for="bmlt_semantic_form_map_search_text_radius'.htmlspecialchars($this->_myJSName).'" id="bmlt_semantic_form_map_search_text_radius_label'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('text_search_radius_label').'</label>';
        $ret .= '<input type="text" id="bmlt_semantic_form_map_search_text_radius'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_search_radius_text" />';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleMapRadiusUnitsChange(this)';
        $ret .= '<select id="bmlt_semantic_form_map_search_text_radius_units'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_map_search_text_radius_units" onchange="'.$function_string.'">';
        $ret .= '<option value="geo_width" selected="selected">'.$this->localize_string('text_map_radius_units_miles').'</option>';
        $ret .= '<option value="geo_width_km">'.$this->localize_string('text_map_radius_units_km').'</option>';
        $ret .= '</select>';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $ret .= $this->get_wizard_page_specific_fields_html();
        
        $ret .= $this->get_wizard_page_sort_fields_html();
        
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_specific_fields_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<fieldset id="bmlt_semantic_form_specific_fields_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_specific_fields_fieldset"><legend id="bmlt_semantic_form_specific_fields_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_specific_fields_fieldset_legend">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= $this->localize_string('specific_fields_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div"><p>'.$this->localize_string('specific_fields_blurb').'</p></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleSpecificFieldChange(this)';
        
        foreach ($this->_keys as $key => $name) {
            $ret .= '<div id="bmlt_semantic_form_field_key_checkbox_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_key_checkbox_div">';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<input type="checkbox" id="bmlt_semantic_form_field_key_checkbox_'.htmlspecialchars($key).'_'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_key_checkbox" value="'.htmlspecialchars($key).'" onchange="'.$function_string.'" />';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<label id="bmlt_semantic_form_field_key_checkbox_label_'.htmlspecialchars($key).'_'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_field_key_checkbox_'.htmlspecialchars($key).'_'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_key_checkbox_label">'.htmlspecialchars($name).'</label>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
        };
        
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_sort_fields_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<fieldset id="bmlt_semantic_form_sort_fields_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_sort_fields_fieldset"><legend id="bmlt_semantic_form_sort_fields_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_sort_fields_fieldset_legend">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= $this->localize_string('sort_fields_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_sb_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div"><p>'.$this->localize_string('sort_fields_blurb').'</p></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleSortFieldChange(this.options[this.selectedIndex])';
        
        foreach ($this->_keys as $key => $name) {
            $ret .= '<div id="bmlt_semantic_form_field_key_sort_field_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_key_sort_field_div">';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<label id="bmlt_semantic_form_field_select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_field_sort_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_select_label">'.htmlspecialchars($name).'</label>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '<select id="bmlt_semantic_form_field_sort_select_'.$key.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_sort_select" onchange="'.$function_string.'">';
            $ret .= defined('DEBUG') ? "\n" : '';
            
            $ret .= '<option value="0" selected="selected" id="bmlt_semantic_form_field_sort_select_'.$key.'_0'.htmlspecialchars($this->_myJSName).'">'.$this->localize_string('sort_fields_no_sort_option').'</option>';
            for ($i = 1; $i <= count($this->_keys); $i++) {
                $ret .= '<option value="'.$i.'" id="bmlt_semantic_form_field_sort_select_'.$key.'_'.$i.htmlspecialchars($this->_myJSName).'">'.$i.'</option>';
                $ret .= defined('DEBUG') ? "\n" : '';
            };
            
            $ret .= '</select>';
            $ret .= defined('DEBUG') ? "\n" : '';
            $ret .= '</div>';
            $ret .= defined('DEBUG') ? "\n" : '';
        };
        
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_coverage_area_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<fieldset id="bmlt_semantic_coverage_area_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_coverage_area_fieldset" style="display:none"><legend id="bmlt_semantic_coverage_area_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_coverage_area_fieldset_legend">';
        $ret .= $this->localize_string('coverage_area_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_coverage_area_fieldset_map_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_coverage_area_fieldset_map_div"></div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_field_select_html($inID = '')
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<label id="bmlt_semantic_form_field_'.htmlspecialchars($inID).'select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_field_'.htmlspecialchars($inID).'select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_select_label">'.$this->localize_string('keys_section_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.handleFieldKeySelectChange(this)';
        $ret .= '<select id="bmlt_semantic_form_field_'.htmlspecialchars($inID).'select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_field_select" onchange="'.htmlspecialchars($function_string).'">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<option value="" selected="selected"';
        if ($inID) {
            $ret .= ' disabled="disabled"';
        }
        $ret .= '>'.$this->localize_string($inID ? 'defaultFieldSelect' : 'defaultMeetingFieldSelect').'</option>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_field_value_select_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<label id="bmlt_semantic_form_value_select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_value_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_value_select_label">'.$this->localize_string('values_section_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.fieldValueChosen(this)';
        $ret .= '<select id="bmlt_semantic_form_value_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_value_select" onchange="'.htmlspecialchars($function_string).'">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<option value="" selected="selected" disabled="disabled">'.$this->localize_string('defaultPresetValueSelect').'</option>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<input type="text" id="bmlt_semantic_form_value_text'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_value_text" value="'.$this->localize_string('value_prompt_text_item').'" />';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function make_checkbox_html(
        $in_label_text,
        $in_base_id,
        $in_checked = false,
        $in_value = null,
        $in_onChange = null
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<div id="'.htmlspecialchars($in_base_id.'_container_div'.$this->_myJSName).'" class="bmlt_weekday_checkbox_container">';
        $ret .= '<input type="checkbox" id="'.htmlspecialchars($in_base_id.$this->_myJSName).'" class="bmlt_checkbox_input"';
        
        if ($in_checked) {
            $ret .= ' checked="checked"';
        }
        
        if ($in_value) {
            $ret .= ' value="'.htmlspecialchars($in_value).'"';
        }
            
        if ($in_onChange) {
            $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.'.$in_onChange.'(this)';
            $ret .= ' onchange="'.$function_string.'"';
        }
        
        $ret .= ' /><label for="'.htmlspecialchars($in_base_id.$this->_myJSName).'" id="'.htmlspecialchars($in_base_id.'_label'.$this->_myJSName).'" class="bmlt_checkbox_label">'.htmlspecialchars($in_label_text).'</label>';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_schema_select_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<fieldset id="bmlt_semantic_form_schema_select_fieldset'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_schema_select_fieldset" style="display:none"><legend id="bmlt_semantic_form_schema_select_fieldset_legend'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_schema_select_fieldset_legend">';
        $ret .= $this->localize_string('schema_type_selector_legend');
        $ret .= '</legend>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<div id="bmlt_semantic_form_schema_select_blurb_div'.htmlspecialchars($this->_myJSName).'" class="bmlt_workshop_blurb_note_div">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<p>'.$this->_localization['schema_type_selector_blurb1'].'</p>';
        $ret .= '<p>'.$this->localize_string('schema_type_selector_blurb2').'</p>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</div>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<label id="bmlt_semantic_form_schema_select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_schema_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_schema_select_label">'.$this->localize_string('schema_type_selector_label').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $function_string = 'bmlt_semantic_js_object'.htmlspecialchars($this->_myJSName).'.refreshURI()';
        $ret .= '<select id="bmlt_semantic_form_schema_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_schema_select" onchange="'.$function_string.'">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<option value="GetSearchResults" selected="selected">'.$this->localize_string('schema_type_selector_results').'</option>';
        $ret .= '<option value="GetMeetingLocationInfo">'.$this->localize_string('schema_type_selector_location_info').'</option>';
        $ret .= '<option value="GetFieldKeys">'.$this->localize_string('schema_type_selector_field_keys').'</option>';
        $ret .= '<option value="GetFieldValues">'.$this->localize_string('schema_type_selector_field_values').'</option>';
        $ret .= '<option value="AdminPermissions">'.$this->localize_string('schema_type_selector_admin_permissions').'</option>';
        $ret .= '<option value="ChangeResponse">'.$this->localize_string('schema_type_selector_change_response').'</option>';
        $ret .= '<option value="DeletedMeeting">'.$this->localize_string('schema_type_selector_deleted_meeting').'</option>';
        $ret .= '<option value="FieldTemplates">'.$this->localize_string('schema_type_selector_field_templates').'</option>';
        $ret .= '<option value="GetChanges">'.$this->localize_string('schema_type_selector_changes').'</option>';
        $ret .= '<option value="GetFormats">'.$this->localize_string('schema_type_selector_formats').'</option>';
        $ret .= '<option value="GetLangs">'.$this->localize_string('schema_type_selector_langs').'</option>';
        $ret .= '<option value="GetServiceBodies">'.$this->localize_string('schema_type_selector_service_bodies').'</option>';
        $ret .= '<option value="HierServiceBodies">'.$this->localize_string('schema_type_selector_hier_service_bodies').'</option>';
        $ret .= '<option value="ServerInfo">'.$this->localize_string('schema_type_selector_hier_server_info').'</option>';
        $ret .= '<option value="GetCoverageArea">'.$this->localize_string('schema_type_selector_coverage_area').'</option>';
        $ret .= '<option value="UserInfo">'.$this->localize_string('schema_type_selector_get_user_info').'</option>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</fieldset>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
    
    /**************************************************************/
    /** \brief

        \returns the HTML.
    */
    /**************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_wizard_page_switcher_type_select_html()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = '<label id="bmlt_semantic_form_switcher_type_select_label'.htmlspecialchars($this->_myJSName).'" for="bmlt_semantic_form_switcher_type_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_switcher_type_select_label">'.$this->localize_string('switcher_type_selector_prompt').'</label>';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<select id="bmlt_semantic_form_switcher_type_select'.htmlspecialchars($this->_myJSName).'" class="bmlt_semantic_form_switcher_type_select">';
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '<option value="GetSearchResults" selected="selected">'.$this->localize_string('switcher_type_selector_results').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_formats_option'.htmlspecialchars($this->_myJSName).'" value="GetFormats">'.$this->localize_string('switcher_type_selector_formats').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_sb_option'.htmlspecialchars($this->_myJSName).'" value="GetServiceBodies">'.$this->localize_string('switcher_type_selector_sb').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_changes_option'.htmlspecialchars($this->_myJSName).'" value="GetChanges">'.$this->localize_string('switcher_type_selector_changes').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_fieldkey_option'.htmlspecialchars($this->_myJSName).'" value="GetFieldKeys">'.$this->localize_string('switcher_type_selector_field_keys').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_fieldval_option'.htmlspecialchars($this->_myJSName).'" value="GetFieldValues">'.$this->localize_string('switcher_type_selector_field_values').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_naws_option'.htmlspecialchars($this->_myJSName).'" value="GetNAWSDump">'.$this->localize_string('switcher_type_selector_naws').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_schema_option'.htmlspecialchars($this->_myJSName).'" disabled="disabled" value="XMLSchema">'.$this->localize_string('switcher_type_selector_schema').'</option>';
        $ret .= '<option id="bmlt_semantic_form_switcher_type_select_server_langs_option'.htmlspecialchars($this->_myJSName).'" disabled="disabled" value="GetLangs">'.$this->localize_string('switcher_type_selector_server_langs').'</option>';
        
        $this->get_server_version();
        $version = $this->_version;
        if ($version >= 2006020) {
            $ret .= '<option id="bmlt_semantic_form_switcher_type_select_server_info_option'.htmlspecialchars($this->_myJSName).'" value="GetServerInfo">'.$this->localize_string('switcher_type_selector_server_info').'</option>';
        }
        
        if ($version >= 2008016) {
            $ret .= '<option id="bmlt_semantic_form_switcher_type_select_coverage_area_option'.htmlspecialchars($this->_myJSName).'" value="GetCoverageArea">'.$this->localize_string('switcher_type_selector_coverage_area').'</option>';
        }
        
        $ret .= defined('DEBUG') ? "\n" : '';
        $ret .= '</select>';
        $ret .= defined('DEBUG') ? "\n" : '';
        
        return $ret;
    }
}
