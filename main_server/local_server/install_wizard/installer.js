/*
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://bmlt.magshare.org

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
function BMLTInstaller( in_prefs    ///< A JSON object with the initial prefs.
                    )
{
    var m_installer_wrapper_object;
    var m_map_object;
    var m_map_center;
    var m_installer_state;
    var m_ajax_uri;
    var m_ajax_request_in_progress;
    
    // #mark - 
    // #mark Page Selection Handlers
    // #mark -
    
    /************************************************************************************//**
    *   \brief Shows Page 1 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage1 = function()
    {
        if ( this.m_installer_wrapper_object.className != 'page_1_wrapper' )
            {
            this.m_installer_wrapper_object.className = 'page_1_wrapper';
            };
    };
    
    /************************************************************************************//**
    *   \brief Shows Page 2 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage2 = function()
    {
        if ( this.m_installer_wrapper_object.className != 'page_2_wrapper' )
            {
            this.m_installer_wrapper_object.className = 'page_2_wrapper';
            
            if ( !this.m_map_object )
                {
                this.m_map_object = this.createLocationMap ( document.getElementById ('installer_map_display_div') );
                };
            };
    };
    
    /************************************************************************************//**
    *   \brief Shows Page 3 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage3 = function()
    {
        if ( this.m_installer_wrapper_object.className != 'page_3_wrapper' )
            {
            this.m_installer_wrapper_object.className = 'page_3_wrapper';
            this.testForDatabaseSetup();
            };
    };
    
    /************************************************************************************//**
    *   \brief Shows Page 4 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage4 = function()
    {
        if ( this.m_installer_wrapper_object.className != 'page_4_wrapper' )
            {
            this.m_installer_wrapper_object.className = 'page_4_wrapper';
            this.testForDatabaseSetup();
            document.getElementById ( 'file_text_pre' ).innerHTML = this.createFileData();
            };
    };
    
    // #mark - 
    // #mark Text Item Handlers
    // #mark -
    
    /************************************************************************************//**
    *   \brief When a text input (either <input> or <textarea> is initialized, we can set   *
    *          up a default text value that is displayed when the item is empty and not in  *
    *          focus. If we don't send in a specific value, then the current value of the   *
    *          text item is considered to be the default.                                   *
    ****************************************************************************************/
    this.handleTextInputLoad = function(    in_text_item,
                                            in_default_value,
                                            in_size
                                        )
    {
        if ( in_text_item )
            {
            in_text_item.original_value = in_text_item.value;
            
            in_text_item.small = false;
            in_text_item.med = false;
            in_text_item.tiny = false;
            
            if ( in_size )
                {
                if ( in_size == 'tiny' )
                    {
                    in_text_item.tiny = true;
                    }
                else if ( in_size == 'small' )
                    {
                    in_text_item.small = true;
                    }
                else if ( in_size == 'med' )
                    {
                    in_text_item.med = true;
                    };
                };
            
            if ( in_default_value != null )
                {
                in_text_item.defaultValue = in_default_value;
                }
            else
                {
                in_text_item.defaultValue = in_text_item.value;
                };
            
            in_text_item.value = in_text_item.original_value;
            
            if ( !in_text_item.value || (in_text_item.value == in_text_item.defaultValue) )
                {
                in_text_item.value = in_text_item.defaultValue;
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : ''))) + ' bmlt_text_item_dimmed';
                }
            else
                {
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : '')));
                };
            
            in_text_item.onfocus = function () { g_installer_object.handleTextInputFocus(this); };
            in_text_item.onblur = function () { g_installer_object.handleTextInputBlur(this); };
            this.setTextItemClass ( in_text_item, false );
            };
    };
    
    /************************************************************************************//**
    *   \brief This just makes sure that the className is correct.                          *
    ****************************************************************************************/
    this.setTextItemClass = function(   in_text_item,   ///< This is the text item to check.
                                        is_focused      ///< true, if the item is in focus
                                        )
    {
        if ( in_text_item )
            {
            if ( !is_focused && ((in_text_item.value == null) || (in_text_item.value == in_text_item.defaultValue)) )
                {
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : ''))) + ' bmlt_text_item_dimmed';
                }
            else
                {
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : '')));
                };
            };
    };
    
    /************************************************************************************//**
    *   \brief When a text item receives focus, we clear any default text.                  *
    ****************************************************************************************/
    this.handleTextInputFocus = function(   in_text_item
                                        )
    {
        if ( in_text_item )
            {
            if ( in_text_item.value == in_text_item.defaultValue )
                {
                in_text_item.value = '';
                };
            
            this.setTextItemClass ( in_text_item, true );
            };
    };
    
    /************************************************************************************//**
    *   \brief When a text item loses focus, we restore any default text, if the item was   *
    *          left empty.                                                                  *
    ****************************************************************************************/
    this.handleTextInputBlur = function(    in_text_item
                                        )
    {
        if ( in_text_item )
            {
            if ( !in_text_item.value )
                {
                in_text_item.value = in_text_item.defaultValue;
                };
            
            this.setTextItemClass ( in_text_item, false );
            };
    };
    
    /************************************************************************************//**
    *   \brief This creates the map for the location tab.                                   *
    *   \returns the map object.                                                            *
    ****************************************************************************************/
    this.createLocationMap = function(  in_parent_div   // The div element containing this map.
                                    )
    {
        var map_object = null;

        var myOptions = {
                        'center': new google.maps.LatLng ( this.m_installer_state.search_spec_map_center.latitude, this.m_installer_state.search_spec_map_center.longitude ),
                        'zoom': this.m_installer_state.search_spec_map_center.zoom,
                        'mapTypeId': google.maps.MapTypeId.ROADMAP,
                        'mapTypeControlOptions': { 'style': google.maps.MapTypeControlStyle.DROPDOWN_MENU },
                        'zoomControl': true,
                        'mapTypeControl': true,
                        'disableDoubleClickZoom' : true,
                        'draggableCursor': "crosshair",
                        'scaleControl' : true
                        };

        myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.LARGE };

        map_object = new google.maps.Map ( in_parent_div, myOptions );
    
        if ( map_object )
            {
            map_object.setOptions({'scrollwheel': false});   // For some reason, it ignores setting this in the options.
            google.maps.event.addListener ( map_object, 'click', g_installer_object.reactToMapClick );
            
            m_icon_image = new google.maps.MarkerImage ( "./local_server/server_admin/style/images/NACenterMarker.png", new google.maps.Size(21, 36), new google.maps.Point(0,0), new google.maps.Point(11, 36) );
            m_icon_shadow = new google.maps.MarkerImage( "./local_server/server_admin/style/images/NACenterMarkerS.png", new google.maps.Size(43, 36), new google.maps.Point(0,0), new google.maps.Point(11, 36) );

            map_object.main_marker = new google.maps.Marker ({
                                                                'position':     map_object.getCenter(),
                                                                'map':          map_object,
                                                                'icon':         m_icon_image,
                                                                'shadow':       m_icon_shadow,
                                                                'clickable':    false,
                                                                'cursor':       'pointer',
                                                                'draggable':    true
                                                                } );
            
            google.maps.event.addListener ( map_object.main_marker, 'dragend', g_installer_object.reactToMapClick );
            };
            
        return map_object;
    };
    
    /************************************************************************************//**
    *   \brief This is the callback for a map click or drag end.                            *
    ****************************************************************************************/
    this.reactToMapClick = function(  in_gMap_event ///< The Google Maps event
                                    )
    {
        var map_center = in_gMap_event.latLng;
        g_installer_object.m_map_object.panTo ( map_center );
        g_installer_object.m_map_object.main_marker.setPosition ( map_center );
    };
    
    /************************************************************************************//**
    *   \brief 
    ****************************************************************************************/
    this.buttonTestForDatabaseSetup = function()
    {
        var uri = this.m_ajax_uri;
        
        this.gatherInstallerState();
        
        if ( this.m_ajax_request_in_progress )
            {
            this.m_ajax_request_in_progress.abort();
            this.m_ajax_request_in_progress = null;
            };
        
        uri += 'test_comprehensive';
        uri += '&dbType=' + this.m_installer_state.dbType;
        uri += '&dbName=' + this.m_installer_state.dbName;
        uri += '&dbUser=' + this.m_installer_state.dbUser;
        uri += '&dbPassword=' + this.m_installer_state.dbPassword;
        uri += '&dbServer=' + this.m_installer_state.dbServer;
        uri += '&dbPrefix=' + this.m_installer_state.dbPrefix;
        
        var salt = new Date();
        uri += '&salt=' + salt.getTime();
    
        this.m_ajax_request_in_progress = BMLT_Installer_AjaxRequest ( uri, function(in_req) { g_installer_object.buttonTestForDatabaseSetupCallback(in_req); }, 'post' );
    };
    
    /************************************************************************************//**
    *   \brief 
    ****************************************************************************************/
    this.buttonTestForDatabaseSetupCallback = function(   in_http_request
                                                        )
    {
        this.m_ajax_request_in_progress = null;

        if ( in_http_request.responseText )
            {
            eval ( 'var json_object = ' + in_http_request.responseText + ';' );
            
            if ( json_object ) // There is an existing database
                {
                alert ( json_object.message );
                };
            }
        else    // Nothing to report.
            {
            };
    };
    
    /************************************************************************************//**
    *   \brief 
    ****************************************************************************************/
    this.testForDatabaseSetup = function()
    {
        var uri = this.m_ajax_uri;
        
        this.gatherInstallerState();
        
        if ( this.m_ajax_request_in_progress )
            {
            this.m_ajax_request_in_progress.abort();
            this.m_ajax_request_in_progress = null;
            };
        
        uri += 'test';
        uri += '&dbType=' + this.m_installer_state.dbType;
        uri += '&dbName=' + this.m_installer_state.dbName;
        uri += '&dbUser=' + this.m_installer_state.dbUser;
        uri += '&dbPassword=' + this.m_installer_state.dbPassword;
        uri += '&dbServer=' + this.m_installer_state.dbServer;
        uri += '&dbPrefix=' + this.m_installer_state.dbPrefix;
        
        var salt = new Date();
        uri += '&salt=' + salt.getTime();
    
        this.m_ajax_request_in_progress = BMLT_Installer_AjaxRequest ( uri, function(in_req) { g_installer_object.testForDatabaseSetupCallback(in_req); }, 'post' );
    };
    
    /************************************************************************************//**
    *   \brief 
    ****************************************************************************************/
    this.testForDatabaseSetupCallback = function(   in_http_request
                                                )
    {
        this.m_ajax_request_in_progress = null;

        if ( in_http_request.responseText )
            {
            eval ( 'var ret_val = parseInt ( ' + in_http_request.responseText + ', 10 );' );
            document.getElementById ( 'admin_db_items_warning' ).innerHTML = '';
            document.getElementById ( 'admin_pw_warning_div_2' ).className = 'item_hidden';
            
            if ( ret_val == 0 ) // There is an existing database
                {
                document.getElementById ( 'admin_login_stuff_fieldset' ).className = 'item_hidden';
                if ( document.getElementById ( 'database_install_stuff_div' ) )
                    {
                    document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
                    };
                document.getElementById ( 'admin_pw_warning_div_2' ).className = 'extra_text_div red_char';
                document.getElementById ( 'admin_db_items_warning' ).innerHTML = g_db_init_db_set_warning_text;
                }
            else if ( ret_val == -1 )   // No database
                {
                document.getElementById ( 'admin_login_stuff_fieldset' ).className = '';
                if ( document.getElementById ( 'database_install_stuff_div' ) )
                    {
                    document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
                    };
                
                document.getElementById ( 'admin_pw_warning_div' ).innerHTML = '';
                }
            else
                {
                document.getElementById ( 'admin_login_stuff_fieldset' ).className = '';
                if ( document.getElementById ( 'database_install_stuff_div' ) )
                    {
                    document.getElementById ( 'database_install_stuff_div' ).className = '';
                    };
                this.gatherInstallerState ();
                };
            }
        else    // Nothing to report.
            {
            document.getElementById ( 'admin_pw_warning_div' ).innerHTML = '';
            document.getElementById ( 'admin_login_stuff_fieldset' ).className = '';
            if ( document.getElementById ( 'database_install_stuff_div' ) )
                {
                document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
                };
            };
    };
    
    /************************************************************************************//**
    *   \brief 
    ****************************************************************************************/
    this.setUpDatabase = function()
    {
        document.getElementById ( 'bmlt_installer_initialize_ajax_button' ).className = 'item_hidden';
        document.getElementById ( 'bmlt_installer_initialize_ajax_button_throbber_span' ).className = 'bmlt_admin_ajax_button_throbber_span';

        var uri = this.m_ajax_uri;
        
        this.gatherInstallerState();
        
        if ( this.m_ajax_request_in_progress )
            {
            this.m_ajax_request_in_progress.abort();
            this.m_ajax_request_in_progress = null;
            };
        
        uri += 'initialize_db';
        uri += '&dbType=' + this.m_installer_state.dbType;
        uri += '&dbName=' + this.m_installer_state.dbName;
        uri += '&dbUser=' + this.m_installer_state.dbUser;
        uri += '&dbPassword=' + this.m_installer_state.dbPassword;
        uri += '&dbServer=' + this.m_installer_state.dbServer;
        uri += '&dbPrefix=' + this.m_installer_state.dbPrefix;
        
        var admin_login_object = document.getElementById('installer_admin_login_input');
        var admin_password_object = document.getElementById('installer_admin_password_input');

        var admin_login = (admin_login_object.value && (admin_login_object.value != admin_login_object.defaultValue)) ? admin_login_object.value : '';
        var admin_password = (admin_password_object.value && (admin_password_object.value != admin_password_object.defaultValue)) ? admin_password_object.value : '';

        uri += '&admin_login=' + admin_login;
        uri += '&admin_password=' + admin_password;
        
        var salt = new Date();
        uri += '&salt=' + salt.getTime();
        
        this.m_ajax_request_in_progress = BMLT_Installer_AjaxRequest ( uri, function(in_req) { g_installer_object.initializeDatabaseCallback(in_req); }, 'post' );
    };
    
    /************************************************************************************//**
    *   \brief 
    ****************************************************************************************/
    this.initializeDatabaseCallback = function(   in_http_request
                                                )
    {
        this.m_ajax_request_in_progress = null;
        if ( in_http_request.responseText )
            {
            eval ( 'var ret_val = ' + in_http_request.responseText + ';' );
            
            if ( ret_val )
                {
                if ( ret_val.status == true )   // Hide the initialize button upon success.
                    {
                    document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
                    document.getElementById ( 'admin_db_items_warning' ).innerHTML = '';
                    }
                else
                    {
                    if ( ret_val.report )
                        {
                        alert ( ret_val.report );
                        };
                    };
                };
            };
        
        document.getElementById ( 'bmlt_installer_initialize_ajax_button_throbber_span' ).className = 'item_hidden';
        document.getElementById ( 'bmlt_installer_initialize_ajax_button' ).className = 'bmlt_admin_ajax_button';
    };
    
    /************************************************************************************//**
    *   \brief Returns the minimum password length                                          *
    *   \returns an integer, with the password length.                                      *
    ****************************************************************************************/
    this.geMinPasswordLength = function()
    {
        var pw_length_object = document.getElementById ( 'installer_pw_length_select' );
        
        this.m_installer_state.min_pw_len = pw_length_object.options[pw_length_object.selectedIndex].value;
        
        return parseInt ( this.m_installer_state.min_pw_len, 10 );
    };
    
    /************************************************************************************//**
    *   \brief This gathers the installer state.                                            *
    ****************************************************************************************/
    this.gatherInstallerState = function()
    {
        var db_type_object = document.getElementById('installer_db_type_select');
        var db_name_object = document.getElementById('installer_db_name_input');
        var db_user_object = document.getElementById('installer_db_user_input');
        var db_pw_object = document.getElementById('installer_db_pw_input');
        var db_host_object = document.getElementById('installer_db_host_input');
        var db_prefix_object = document.getElementById('installer_db_prefix_input');
        
        var admin_login_object = document.getElementById('installer_admin_login_input');
        var admin_password_object = document.getElementById('installer_admin_password_input');

        this.m_installer_state.dbType = db_type_object.options[db_type_object.selectedIndex].value;
        this.m_installer_state.dbName = (db_name_object.value && (db_name_object.value != db_name_object.defaultValue)) ? db_name_object.value : '';
        this.m_installer_state.dbUser = (db_user_object.value && (db_user_object.value != db_user_object.defaultValue)) ? db_user_object.value : '';
        this.m_installer_state.dbPassword = (db_pw_object.value && (db_pw_object.value != db_pw_object.defaultValue)) ? db_pw_object.value : '';
        this.m_installer_state.dbServer = (db_host_object.value && (db_host_object.value != db_host_object.defaultValue)) ? db_host_object.value : '';
        this.m_installer_state.dbPrefix = (db_prefix_object.value && (db_prefix_object.value != db_prefix_object.defaultValue)) ? db_prefix_object.value : '';
        
        var admin_login = (admin_login_object.value && (admin_login_object.value != admin_login_object.defaultValue)) ? admin_login_object.value : '';
        var admin_password = (admin_password_object.value && (admin_password_object.value != admin_password_object.defaultValue)) ? admin_password_object.value : '';
        
        if ( document.getElementById ( 'database_install_stuff_div' ) )
            {
            if (    !this.m_installer_state.dbType
                ||  !this.m_installer_state.dbName
                ||  !this.m_installer_state.dbUser
                ||  !this.m_installer_state.dbPassword
                ||  !this.m_installer_state.dbServer
                ||  !this.m_installer_state.dbPrefix
                ||  !admin_login
                ||  !admin_password
                ||  (admin_password && (admin_password.length < this.geMinPasswordLength()))
                )
                {
                document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
                if (    !admin_login
                    ||  !admin_password
                    ||  (admin_password && (admin_password.length < this.geMinPasswordLength()))
                    )
                    {
                    document.getElementById ( 'admin_db_items_warning' ).innerHTML = g_db_init_no_pw_warning_text;
                    }
                else
                    {
                    document.getElementById ( 'admin_db_items_warning' ).innerHTML = '';
                    };
                }
            else
                {
                document.getElementById ( 'admin_db_items_warning' ).innerHTML = '';
                };
            };
        
        var min_pw_len = this.geMinPasswordLength();
        
        if ( admin_password && (admin_password.length < min_pw_len) )
            {
            document.getElementById ( 'admin_pw_warning_div' ).innerHTML = sprintf ( g_pw_length_warning_text, min_pw_len );
            }
        else
            {
            document.getElementById ( 'admin_pw_warning_div' ).innerHTML = '';
            };
        
        var search_count_select_object = document.getElementById ( 'search_count_select' );
        
        this.m_installer_state.number_of_meetings_for_auto = search_count_select_object.options[search_count_select_object.selectedIndex].value;
        
        var change_depth_for_meetings_object = document.getElementById ( 'installer_history_select' );
        
        this.m_installer_state.change_depth_for_meetings = change_depth_for_meetings_object.options[change_depth_for_meetings_object.selectedIndex].value;
        
        var language_object = document.getElementById ( 'installer_lang_select' );
        
        this.m_installer_state.comdef_global_language = language_object.options[language_object.selectedIndex].value;
            
        this.geMinPasswordLength();
            
        var region_bias_object = document.getElementById ( 'installer_region_bias_select' );
        
        this.m_installer_state.region_bias = region_bias_object.options[region_bias_object.selectedIndex].value;
        
        if ( this.m_map_object )
            {
            var centerPos = this.m_map_object.main_marker.getPosition();
            
            if ( !this.m_installer_state.search_spec_map_center )
                {
                this.m_installer_state.search_spec_map_center = new Object;
                };
            
            this.m_installer_state.search_spec_map_center.longitude = centerPos.lng();
            this.m_installer_state.search_spec_map_center.latitude = centerPos.lat();
            this.m_installer_state.search_spec_map_center.zoom = this.m_map_object.getZoom();
            };
        
        this.m_installer_state.bmlt_title = document.getElementById ( 'installer_title_input' ).value;
            
        this.m_installer_state.banner_text = document.getElementById ( 'installer_banner_input' ).value;
            
        var distance_units_object = document.getElementById ( 'distance_units_select' );
        
        var duration_time_hours_object = document.getElementById ( 'installer_duration_hour_select' );
        var duration_time_minutes_object = document.getElementById ( 'installer_duration_minutes_select' );
        
        this.m_installer_state.default_duration_time = sprintf ( "%d:%02d:00", parseInt ( duration_time_hours_object.options[duration_time_hours_object.selectedIndex].value, 10 ), parseInt ( duration_time_minutes_object.options[duration_time_minutes_object.selectedIndex].value, 10 ) );
        
        this.m_installer_state.comdef_distance_units = distance_units_object.options[distance_units_object.selectedIndex].value;
        
        this.m_installer_state.default_duration_text = '';
    };
    
    /************************************************************************************//**
    *   \brief  Creates the text for the file                                               *
    *   \returns    The PHP code for the auto-config.inc.php file.                          *
    ****************************************************************************************/
    this.createFileData = function()
    {
        var ret = "&lt;?php\n";
        
        if ( this.m_installer_state && this.m_installer_state.search_spec_map_center )
            {
            ret += "defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.\n";

            ret += "\n\t// These are the settings created by the installer wizard.\n";

            ret += "\n\t\t// Database settings:\n";
            ret += "\t\t$dbType = '" + this.m_installer_state.dbType.replace(/'/g,"\\'") + "'; // This is the PHP PDO driver name for your database.\n";
            ret += "\t\t$dbName = '" + this.m_installer_state.dbName.replace(/'/g,"\\'") + "'; // This is the name of the database.\n";
            ret += "\t\t$dbUser = '" + this.m_installer_state.dbUser.replace(/'/g,"\\'") + "'; // This is the SQL user that is authorized for the above database.\n";
            ret += "\t\t$dbPassword = '" + this.m_installer_state.dbPassword.replace(/'/g,"\\'") + "'; // This is the password for the above authorized user. Make it a big, ugly hairy one. It is powerful, and there is no need to remember it.\n";
            ret += "\t\t$dbServer = '" + this.m_installer_state.dbServer.replace(/'/g,"\\'") + "'; // This is the host/server for accessing the database.\n";
            ret += "\t\t$dbPrefix = '" + this.m_installer_state.dbPrefix.replace(/'/g,"\\'") + "'; // This is a table name prefix that can be used to differentiate tables used by different root server instances that share the same database.\n";

            ret += "\n\t\t// Location and Map settings:\n";
            ret += "\t\t$region_bias = '" + this.m_installer_state.region_bias + "'; // This is a 2-letter code for a 'region bias,' which helps Google Maps to figure out ambiguous search queries.\n";
            ret += "\t\t$search_spec_map_center = array ( 'longitude' => " + parseFloat ( this.m_installer_state.search_spec_map_center.longitude ).toString() + ", 'latitude' => " + parseFloat ( this.m_installer_state.search_spec_map_center.latitude ).toString() + ", 'zoom' => " + parseInt ( this.m_installer_state.search_spec_map_center.zoom, 10 ).toString() + " ); // This is the default map location for new meetings.\n";
            ret += "\t\t$comdef_distance_units = '" + this.m_installer_state.comdef_distance_units + "';\n";

            ret += "\n\t\t// Display settings:\n";
            ret += "\t\t$bmlt_title = '" + this.m_installer_state.bmlt_title.replace(/'/g,"\\'") + "'; // This is the page title and heading for the main administration login page.\n";
            ret += "\t\t$banner_text = '" + this.m_installer_state.banner_text.replace(/'/g,"\\'") + "'; // This is text that is displayed just above the login box on the main login page.\n";

            ret += "\n\t\t// Miscellaneous settings:\n";
            ret += "\t\t$comdef_global_language ='" + this.m_installer_state.comdef_global_language + "'; // This is the 2-letter code for the default root server localization (will default to 'en' -English, if the localization is not available).\n";
            ret += "\t\t$min_pw_len = " + this.m_installer_state.min_pw_len + "; // The minimum number of characters in a user account password for this root server.\n";
            ret += "\t\t$number_of_meetings_for_auto = " + parseInt ( this.m_installer_state.number_of_meetings_for_auto, 10 ) + "; // This is an approximation of the number of meetings to search for in the auto-search feature. The higher the number, the wider the radius.\n";
            ret += "\t\t$change_depth_for_meetings = " + parseInt ( this.m_installer_state.change_depth_for_meetings, 10 ) + "; // This is how many changes should be recorded for each meeting. The higher the number, the larger the database will grow, as this can become quite substantial.\n";
            ret += "\t\t$default_duration_time = '" + this.m_installer_state.default_duration_time + "'; // This is the default duration for meetings that have no duration specified.\n";
            
            ret += "\n\t// These are 'hard-coded,' but can be changed later.\n";
            
            ret += "\n\t\t$time_format = '" + this.m_installer_state.time_format.replace(/'/g,"\\'") + "'; // The PHP date() format for the times displayed.\n";
            ret += "\t\t$change_date_format = '" + this.m_installer_state.change_date_format.replace(/'/g,"\\'") + "'; // The PHP date() format for times/dates displayed in the change records.\n";
            ret += "\t\t$admin_session_name = '" + this.m_installer_state.admin_session_name.replace(/'/g,"\\'") + "'; // This is merely the 'tag' used to identify the BMLT admin session.\n";
            ret += "\n\t\t// This text can be used in certain custom printed lists. It is usually not especially important.\n";
            ret += "\t\tif ( !defined ( '_DEFAULT_DURATION' ) ) define ( '_DEFAULT_DURATION', '" + this.m_installer_state.default_duration.replace(/'/g,"\\'") + "' );\n";
            ret += "\n\t\t// These are used for the NAWS format translation. They are the shared IDs of the wheelchair, open and closed formats.\n";
            ret += "\t\t// If you edit your formats, and change these IDs, please change them here, as well.\n";
            ret += "\t\tif ( !defined ( 'WC_FORMAT' ) ) define ( 'WC_FORMAT', '33' ); // Wheelchair-Accessible\n";
            ret += "\t\tif ( !defined ( 'O_FORMAT' ) ) define ( 'O_FORMAT', '17' ); // Open Meeting\n";
            ret += "\t\tif ( !defined ( 'C_FORMAT' ) ) define ( 'C_FORMAT', '4' ); // Closed Meeting\n";

            ret += "?&gt;\n";
            }
        else
            {
            ret = '';
            };
        
        return ret;
    };

    // #mark - 
    // #mark Main Context
    // #mark -
    
    if ( !this.m_installer_state )
        {
        this.m_installer_state = in_prefs;
        };
    
    this.m_installer_wrapper_object = document.getElementById ( 'installer_wrapper' );
};

// #mark - 
// #mark AJAX Handler
// #mark -

/****************************************************************************************//**
*   \brief A simple, generic AJAX request function.                                         *
*                                                                                           *
*   \returns a new XMLHTTPRequest object.                                                   *
********************************************************************************************/
    
function BMLT_Installer_AjaxRequest (   url,        ///< The URI to be called
                                        callback,   ///< The success callback
                                        method,     ///< The method ('get' or 'post')
                                        extra_data  ///< If supplied, extra data to be delivered to the callback.
                                        )
{
    /************************************************************************************//**
    *   \brief Create a generic XMLHTTPObject.                                              *
    *                                                                                       *
    *   This will account for the various flavors imposed by different browsers.            *
    *                                                                                       *
    *   \returns a new XMLHTTPRequest object.                                               *
    ****************************************************************************************/
    
    function createXMLHTTPObject()
    {
        var XMLHttpArray = [
            function() {return new XMLHttpRequest()},
            function() {return new ActiveXObject("Msxml2.XMLHTTP")},
            function() {return new ActiveXObject("Msxml2.XMLHTTP")},
            function() {return new ActiveXObject("Microsoft.XMLHTTP")}
            ];
            
        var xmlhttp = false;
        
        for ( var i=0; i < XMLHttpArray.length; i++ )
            {
            try
                {
                xmlhttp = XMLHttpArray[i]();
                }
            catch(e)
                {
                continue;
                };
            break;
            };
        
        return xmlhttp;
    };
    
    var req = createXMLHTTPObject();
    req.finalCallback = callback;
    var sVars = null;
    method = method.toString().toUpperCase();
    var drupal_kludge = '';
    
    // Split the URL up, if this is a POST.
    if ( method == "POST" )
        {
        var rmatch = /^([^\?]*)\?(.*)$/.exec ( url );
        url = rmatch[1];
        sVars = rmatch[2];
        // This horrible, horrible kludge, is because Drupal insists on having its q parameter in the GET list only.
        var rmatch_kludge = /(q=admin\/settings\/bmlt)&?(.*)/.exec ( rmatch[2] );
        if ( rmatch_kludge && rmatch_kludge[1] )
            {
            url += '?'+rmatch_kludge[1];
            sVars = rmatch_kludge[2];
            };
        };
    if ( extra_data != null )
        {
        req.extra_data = extra_data;
        };
    req.open ( method, url, true );
    if ( method == "POST" )
        {
        req.setRequestHeader("Method", "POST "+url+" HTTP/1.1");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        };
    req.onreadystatechange = function ( )
        {
        if ( req.readyState != 4 ) return;
        if( req.status != 200 ) return;
        callback ( req, req.extra_data );
        req = null;
        };
    req.send ( sVars );
    
    return req;
};

// #mark - 
// #mark ########## Third-Party Code ##########
// #mark -

/**
sprintf() for JavaScript 0.6

Copyright (c) Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of sprintf() for JavaScript nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Alexandru Marasteanu BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


Changelog:
2007.04.03 - 0.1:
 - initial release
2007.09.11 - 0.2:
 - feature: added argument swapping
2007.09.17 - 0.3:
 - bug fix: no longer throws exception on empty paramenters (Hans Pufal)
2007.10.21 - 0.4:
 - unit test and patch (David Baird)
2010.05.09 - 0.5:
 - bug fix: 0 is now preceeded with a + sign
 - bug fix: the sign was not at the right position on padded results (Kamal Abdali)
 - switched from GPL to BSD license
2010.05.22 - 0.6:
 - reverted to 0.4 and fixed the bug regarding the sign of the number 0
 Note:
 Thanks to Raphael Pigulla <raph (at] n3rd [dot) org> (http://www.n3rd.org/)
 who warned me about a bug in 0.5, I discovered that the last update was
 a regress. I appologize for that.
**/

function sprintf()
{
    function str_repeat(i, m)
    {
        for (var o = []; m > 0; o[--m] = i);
        return o.join('');
    };

    var i = 0, a, f = arguments[i++], o = [], m, p, c, x, s = '';
    
    while (f)
        {
        if (m = /^[^\x25]+/.exec(f))
            {
            o.push(m[0]);
            }
        else if (m = /^\x25{2}/.exec(f))
            {
            o.push('%');
            }
        else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f))
            {
            if (((a = arguments[m[1] || i++]) == null) || (a == undefined))
                {
                throw('Too few arguments.');
                };
            
            if (/[^s]/.test(m[7]) && (typeof(a) != 'number'))
                {
                throw('Expecting number but found ' + typeof(a));
                };
            
            switch (m[7])
                {
                case 'b': a = a.toString(2); break;
                case 'c': a = String.fromCharCode(a); break;
                case 'd': a = parseInt(a,10); break;
                case 'e': a = m[6] ? a.toExponential(m[6]) : a.toExponential(); break;
                case 'f': a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a); break;
                case 'o': a = a.toString(8); break;
                case 's': a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a); break;
                case 'u': a = Math.abs(a); break;
                case 'x': a = a.toString(16); break;
                case 'X': a = a.toString(16).toUpperCase(); break;
                };
            
            a = (/[def]/.test(m[7]) && m[2] && a >= 0 ? '+'+ a : a);
            c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
            x = m[5] - String(a).length - s.length;
            p = m[5] ? str_repeat(c, x) : '';
            o.push(s + (m[4] ? a + p : p + a));
            }
        else
            {
            throw('Huh ?!');
            };
        
        f = f.substring(m[0].length);
        };
    
    return o.join('');
};
