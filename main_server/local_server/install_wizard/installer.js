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
function BMLTInstaller( in_map_center   ///< The JSON object containing the map center.
                        )
{
    var m_installer_wrapper_object;
    var m_map_object;
    var m_map_center;
    var m_top_dir_path;
    var m_main_dir_basename;
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
                        'center': new google.maps.LatLng ( this.m_map_center.latitude, this.m_map_center.longitude ),
                        'zoom': this.m_map_center.zoom,
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
            
            if ( ret_val == 0 )
                {
                document.getElementById ( 'admin_login_stuff_div' ).className = 'item_hidden';
                document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
                }
            else if ( ret_val == -1 )
                {
                document.getElementById ( 'admin_login_stuff_div' ).className = '';
                document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
                }
            else
                {
                document.getElementById ( 'admin_login_stuff_div' ).className = '';
                document.getElementById ( 'database_install_stuff_div' ).className = '';
                };
            }
        else
            {
            document.getElementById ( 'admin_login_stuff_div' ).className = '';
            document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
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
                if ( ret_val.status )   // Hide the initialize button upon success.
                    {
                    document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
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

        this.m_installer_state = null;
        this.m_installer_state = new Object;
        
        this.m_installer_state.dbType = db_type_object.options[db_type_object.selectedIndex].value;
        this.m_installer_state.dbName = (db_name_object.value && (db_name_object.value != db_name_object.defaultValue)) ? db_name_object.value : '';
        this.m_installer_state.dbUser = (db_user_object.value && (db_user_object.value != db_user_object.defaultValue)) ? db_user_object.value : '';
        this.m_installer_state.dbPassword = (db_pw_object.value && (db_pw_object.value != db_pw_object.defaultValue)) ? db_pw_object.value : '';
        this.m_installer_state.dbServer = (db_host_object.value && (db_host_object.value != db_host_object.defaultValue)) ? db_host_object.value : '';
        this.m_installer_state.dbPrefix = (db_prefix_object.value && (db_prefix_object.value != db_prefix_object.defaultValue)) ? db_prefix_object.value : '';
        
        var admin_login = (admin_login_object.value && (admin_login_object.value != admin_login_object.defaultValue)) ? admin_login_object.value : '';
        var admin_password = (admin_password_object.value && (admin_password_object.value != admin_password_object.defaultValue)) ? admin_password_object.value : '';
        
        if (    !this.m_installer_state.dbType
            ||  !this.m_installer_state.dbName
            ||  !this.m_installer_state.dbUser
            ||  !this.m_installer_state.dbPassword
            ||  !this.m_installer_state.dbServer
            ||  !this.m_installer_state.dbPrefix
            ||  !admin_login
            ||  !admin_password
            )
            {
            document.getElementById ( 'database_install_stuff_div' ).className = 'item_hidden';
            }
        else
            {
            document.getElementById ( 'database_install_stuff_div' ).className = '';
            };
    };
    
    // #mark - 
    // #mark Main Context
    // #mark -
    
    this.m_map_center = in_map_center;
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
