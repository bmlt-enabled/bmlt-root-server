/*******************************************************************************************/
/**
    \class  BMLTSemanticResult
    
    \brief  This keeps the state of the result of all that stuff going on in the workshop,
            and will compile a resulting URI or shortcode.
*/
/*******************************************************************************************/
function BMLTSemanticResult ( inRootServerURI
                            )
{
    this.root_server_uri = inRootServerURI;
};

BMLTSemanticResult.prototype.switcher = null;       ///< The main "?switcher=" value.
BMLTSemanticResult.prototype.meeting_key = null;    ///< The main "meeting_key=" value.
BMLTSemanticResult.prototype.root_server_uri = null;    ///< The main "meeting_key=" value.

/*******************************************************************************************/
/**
    \class  BMLTSemantic
    
    \brief This is the controlling class for the BMLT interactive semantic workshop.
    
    \param inSuffix A constructor parameter that gives a suffix (for multiple forms).
    \param inAJAXURI The base URI for AJAX callbacks.
    \param inRootServerURI The initial URI for the Root Server.
    \param inVersion The initial version for the Root Server.
*/
/*******************************************************************************************/
function BMLTSemantic ( inSuffix,
                        inAJAXURI,
                        inRootServerURI,
                        inVersion
                        )
{
    this.id_suffix = inSuffix;
    this.ajax_base_uri = inAJAXURI;
    this.state = new BMLTSemanticResult(inRootServerURI);
    this.version = inVersion;
    
    this.setUpForm();
};

BMLTSemantic.prototype.version = null;
BMLTSemantic.prototype.id_suffix = null;
BMLTSemantic.prototype.ajax_base_uri = null;
BMLTSemantic.prototype.format_objects = null;
BMLTSemantic.prototype.field_keys = null;
BMLTSemantic.prototype.field_values = null;
BMLTSemantic.prototype.service_body_objects = null;
BMLTSemantic.prototype.temp_service_body_objects = null;
BMLTSemantic.prototype.state = null;
BMLTSemantic.prototype.hidden_root = null;

/*******************************************************************************************/
/**
    \brief A simple, generic AJAX request function.

    \param url          The URL to be called
    \param callback     A function/lambda/block to be called upon success
                        The callback specified needs to have a signature of:
                            function myAJAXCallback ( inHTTPRequestObject );
                            The extraData parameter will be passed as inHTTPRequestObject.extraData.
    \param method       GET or POST (case-indifferent)
    \param extraData   A "RefCon", or data to be passed unchanged to the callback
    
    \returns a new XMLHTTPRequest object
*/
/*******************************************************************************************/
BMLTSemantic.prototype.ajaxRequest = function ( url,
                                                callback,
                                                method,
                                                extraData
                                                )
{
    /***************************************************************************************/
    /**
        \brief Create a generic XMLHTTPObject.

        This will account for the various flavors imposed by different browsers.

        \returns a new XMLHTTPRequest object.
    */
    /***************************************************************************************/
    function createXMLHTTPObject()
    {
        var XMLHttpArray = [
            function() {return new XMLHttpRequest()},
            function() {return new ActiveXObject ( "Msxml2.XMLHTTP" )},
            function() {return new ActiveXObject ( "Msxml2.XMLHTTP" )},
            function() {return new ActiveXObject ( "Microsoft.XMLHTTP" )}
            ];
            
        var xmlhttp = false;
        
        for ( var i=0; i < XMLHttpArray.length; i++ )
            {
            try
                {
                xmlhttp = XMLHttpArray[i]();
                }
            catch ( e )
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
    
    if ( !method )
        {
        method= 'POST';
        }
    else
        {
        method = method.toUpperCase();
        };
    
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
        
    req.extraData = extraData;
        
    req.open ( method, url, true );
    
    if ( method == "POST" )
        {
        req.setRequestHeader ( "Method", "POST "+url+" HTTP/1.1" );
        req.setRequestHeader ( "Content-Type", "application/x-www-form-urlencoded" );
        };
        
    req.onreadystatechange = function()
        {
        if ( req.readyState != 4 ) return;
        if ( req.status != 200 ) return;
        callback ( req );
        req = null;
        };
    
    req.send ( sVars );
    
    return req;
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available formats.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.reloadFromServer = function ()
{
    this.fetchVersion();
    this.fetchFormats();
    this.fetchServiceBodies();
    this.fetchFieldKeys();
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available formats.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchVersion = function ()
{
    this.ajaxRequest ( this.ajax_base_uri + '&GetVersion', this.fetchVersionCallback, 'post', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available formats.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFormats = function ()
{
    var formatContainer = this.getScopedElement ( 'bmlt_semantic_form_formats_fieldset' );
    
    if ( formatContainer && formatContainer.childNodes )
        {
        for ( var i = formatContainer.childNodes.length; i-- > 0; )
            {
            if ( formatContainer.childNodes[i].className == 'bmlt_checkbox_container' )
                {
                formatContainer.removeChild ( formatContainer.childNodes[i] );
                };
            };
        };
    
    this.ajaxRequest ( this.ajax_base_uri + '&GetInitialFormats', this.fetchFormatsCallback, 'post', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available Service bodies.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchServiceBodies = function ()
{
    var sbContainer = this.getScopedElement ( 'bmlt_semantic_form_sb_fieldset' );

    if ( sbContainer && sbContainer.childNodes )
        {
        for ( var i = sbContainer.childNodes.length; i-- > 0; )
            {
            if ( sbContainer.childNodes[i].className == 'bmlt_sb_dl' )
                {
                sbContainer.removeChild ( sbContainer.childNodes[i] );
                };
            };
        };

    this.ajaxRequest ( this.ajax_base_uri + '&GetInitialServiceBodies', this.fetchServiceBodiesCallback, 'post', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available field keys.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFieldKeys = function ()
{
    this.ajaxRequest ( this.ajax_base_uri + '&GetFieldKeys', this.fetchFieldKeysCallback, 'post', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available field keys.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFieldValues = function ()
{
    this.ajaxRequest ( this.ajax_base_uri + '&GetFieldValues&meeting_key=', this.fetchFieldValuesCallback, 'post', this );
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchVersionCallback = function (inHTTPReqObject
                                                        )
{
    if ( inHTTPReqObject.responseText )
        {
        var context = inHTTPReqObject.extraData;
        eval ( 'context.version = parseInt ( ' + inHTTPReqObject.responseText + ' );' );
        context.validateVersion();
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFormatsCallback = function (inHTTPReqObject
                                                        )
{
    if ( inHTTPReqObject.responseText )
        {
        var context = inHTTPReqObject.extraData;
        eval ( 'context.format_objects = ' + inHTTPReqObject.responseText + ';' );
        context.populateFormatsSection();
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.populateFormatsSection = function()
{
    if ( this.format_objects && this.format_objects.length )
        {
        var formatContainer = this.getScopedElement ( 'bmlt_semantic_form_formats_fieldset' );
            
        for ( var i = 0; i < this.format_objects.length; i++ )
            {
            var formatObject = this.format_objects[i];
            var newContainer = document.createElement ( 'div' );
            newContainer.id = this.getScopedID ( 'bmlt_semantic_form_format_container_div_' + formatObject.id );
            newContainer.className ='bmlt_checkbox_container';
            
            var newCheckbox = document.createElement ( 'input' );
            newCheckbox.type = 'checkbox';
            newCheckbox.id = this.getScopedID ( 'bmlt_semantic_form_format_checkbox_' + formatObject.id );
            newCheckbox.value = formatObject.id;
            newCheckbox.title = formatObject.name_string + ' - ' + formatObject.description_string;
            newCheckbox.className ='bmlt_checkbox_input';
            newContainer.appendChild ( newCheckbox );
            
            var newCheckboxLabel = document.createElement ( 'label' );
            newCheckboxLabel.for = this.getScopedID ( 'bmlt_semantic_form_format_checkbox_' + formatObject.id );
            newCheckboxLabel.id = this.getScopedID ( 'bmlt_semantic_form_format_checkbox_label_' + formatObject.id );
            newCheckboxLabel.className = 'bmlt_checkbox_label';
            newCheckboxLabel.title = formatObject.name_string + ' - ' + formatObject.description_string;
            newCheckboxLabel.appendChild ( document.createTextNode ( formatObject.key_string ) );
            newContainer.appendChild ( newCheckboxLabel );
            
            formatContainer.appendChild ( newContainer );
            };
        
        var breakerBreakerRubberDuck = document.createElement ( 'div' );
        breakerBreakerRubberDuck.className ='clear_both';
        formatContainer.appendChild ( breakerBreakerRubberDuck );
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchServiceBodiesCallback = function (inHTTPReqObject
                                                                )
{
    if ( inHTTPReqObject.responseText )
        {
        var context = inHTTPReqObject.extraData;
        eval ( 'context.temp_service_body_objects = ' + inHTTPReqObject.responseText + ';' );
        context.populateServiceBodiesSection();
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.populateServiceBodiesSection = function()
{
    this.organizeServiceBodies();
    
    if ( this.service_body_objects && this.service_body_objects.length )
        {
        this.createServiceBodyList ( null, this.getScopedElement ( 'bmlt_semantic_form_sb_fieldset' ) );
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.createServiceBodyList = function(inServiceBodyObject,
                                                        inContainerObject
                                                        )
{
    var sb_array = null;
    var id = 0;
    var newListContainer = null;
    
    if ( inServiceBodyObject )
        {
        id = inServiceBodyObject.id;
        
        var checkboxElement = document.createElement ( 'dt' );
        checkboxElement.id = this.getScopedID ( 'bmlt_sb_dt_' + id.toString() );
        checkboxElement.className = 'bmlt_sb_dt';
        this.createServiceBodyCheckbox ( inServiceBodyObject, checkboxElement );
        inContainerObject.appendChild ( checkboxElement );
        
        if ( inServiceBodyObject.childServiceBodies )
            {
            newListContainer = document.createElement ( 'dd' );
            newListContainer.id = this.getScopedID ( 'bmlt_sb_dd_' + id.toString() );
            newListContainer.className = 'bmlt_sb_dd';
            inContainerObject.appendChild ( newListContainer ); 
            sb_array = inServiceBodyObject.childServiceBodies;
            };
        }
    else
        {
        sb_array = this.service_body_objects;
        newListContainer = inContainerObject;
        };

    if ( newListContainer && sb_array && sb_array.length )
        {
        var newSubList = document.createElement ( 'dl' );
        newSubList.id = this.getScopedID ( 'bmlt_sb_dl_' + id.toString() );
        newSubList.className = 'bmlt_sb_dl';
        
        for ( var i = 0; i < sb_array.length; i++ )
            {
            this.createServiceBodyList ( sb_array[i], newSubList );
            };

        newListContainer.appendChild ( newSubList );
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.createServiceBodyCheckbox = function(inServiceBodyObject,
                                                            inContainerObject
                                                            )
{
    var newCheckbox = document.createElement ( 'input' );
    newCheckbox.type = 'checkbox';
    newCheckbox.id = this.getScopedID ( 'bmlt_semantic_form_sb_checkbox_' + inServiceBodyObject.id );
    newCheckbox.value = inServiceBodyObject.id;
    newCheckbox.title = inServiceBodyObject.description;
    newCheckbox.className ='bmlt_checkbox_input';
    inContainerObject.appendChild ( newCheckbox );
    
    var newCheckboxLabel = document.createElement ( 'label' );
    newCheckboxLabel.for = this.getScopedID ( 'bmlt_semantic_form_sb_checkbox_' + inServiceBodyObject.id );
    newCheckboxLabel.id = this.getScopedID ( 'bmlt_semantic_form_sb_checkbox_label_' + inServiceBodyObject.id );
    newCheckboxLabel.className = 'bmlt_checkbox_label';
    newCheckboxLabel.title = inServiceBodyObject.description;
    newCheckboxLabel.appendChild ( document.createTextNode ( inServiceBodyObject.name ) );
    inContainerObject.appendChild ( newCheckboxLabel );
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.organizeServiceBodies = function()
{
    if ( this.temp_service_body_objects && this.temp_service_body_objects.length )
        {
        this.service_body_objects = Array();
        
        for ( var i = 0; i < this.temp_service_body_objects.length; i++ )
            {
            var service_body = this.temp_service_body_objects[i];
            
            if ( parseInt ( service_body.parent_id ) == 0 )
                {
                this.service_body_objects.push ( service_body );
                
                this.getChildServiceBodies ( service_body );
                };
            };
            
        this.temp_service_body_objects = null;
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.getChildServiceBodies = function(inParentObject
                                                        )
{
    if ( this.temp_service_body_objects && this.temp_service_body_objects.length )
        {
        for ( var i = 0; i < this.temp_service_body_objects.length; i++ )
            {
            var service_body = this.temp_service_body_objects[i];
            
            if ( parseInt ( service_body.parent_id ) == parseInt ( inParentObject.id ) )
                {
                if ( !inParentObject.childServiceBodies )
                    {
                    inParentObject.childServiceBodies = Array();
                    };
                service_body.parentServiceBody = inParentObject;
                inParentObject.childServiceBodies.push ( service_body );
                this.getChildServiceBodies ( service_body );
                };
            };
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFieldKeysCallback = function (inHTTPReqObject
                                                        )
{
    if ( inHTTPReqObject.responseText )
        {
        var context = inHTTPReqObject.extraData;
        eval ( 'context.field_keys = ' + inHTTPReqObject.responseText + ';' );
        context.populateFieldSelect();
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.populateFieldSelect = function ()
{
    var mainSelectElement = this.getScopedElement ( 'bmlt_semantic_form_field_main_select' );
    var meetingSelectElement = this.getScopedElement ( 'bmlt_semantic_form_field_select' );
    
    if ( mainSelectElement && mainSelectElement.options )
        {
        for ( var i = (mainSelectElement.options.length - 1); i > 0; i-- )
            {
            mainSelectElement.removeChild ( mainSelectElement.options[i] );
            };
        };
    
    if ( meetingSelectElement && meetingSelectElement.options )
        {
        for ( var i = (meetingSelectElement.options.length - 1); i > 0; i-- )
            {
            meetingSelectElement.removeChild ( meetingSelectElement.options[i] );
            };
        };
    
    for ( var i = 0; i < this.field_keys.length; i++ )
        {
        var newOption = document.createElement ( 'option' );
        newOption.value = this.field_keys[i].key;
        newOption.appendChild ( document.createTextNode ( this.field_keys[i].description ) );
        mainSelectElement.appendChild ( newOption );
        
        newOption = document.createElement ( 'option' );
        newOption.value = this.field_keys[i].key;
        newOption.appendChild ( document.createTextNode ( this.field_keys[i].description ) );
        meetingSelectElement.appendChild ( newOption );
        };
    
    mainSelectElement.selectedIndex = 0;
    meetingSelectElement.selectedIndex = 0;
    this.state.meeting_key = null;
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFieldValuesCallback = function (inHTTPReqObject
                                                        )
{
    if ( inHTTPReqObject.responseText )
        {
        var context = inHTTPReqObject.extraData;
        eval ( 'context.field_values = ' + inHTTPReqObject.responseText + ';' );
        };
};

/*******************************************************************************************/
/**
    \brief Returns an ID that has been "scoped" for this instance.
    
    \param inID   The ID that needs to be "scoped."
    
    \returns the ID with any necessary scope attached.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.getScopedID = function ( inID
                                                )
{
    if ( this.id_suffix )
        {
        inID += this.id_suffix.toString();
        };
        
    return inID;
};

/*******************************************************************************************/
/**
    \brief Returns an element that has been "scoped" for this instance.
    
    \param inID   The ID that needs to be "scoped."
    
    \returns the element requested.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.getScopedElement = function ( inID
                                                    )
{
    return document.getElementById ( this.getScopedID ( inID ) );
};

/*******************************************************************************************/
/**
    \brief This scans the main selectors, and does what is necessary.
    
    \param inItem This is the item that triggered this map.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setUpMainSelectors = function ( inItem
                                            )
{
    var main_fieldset_select = this.getScopedElement ( 'bmlt_semantic_form_main_mode_select' );
    var main_fieldset_direct_uri_div = this.getScopedElement ( 'bmlt_semantic_form_direct_url_div' );
    var response_type_select = this.getScopedElement ( 'bmlt_semantic_form_response_type_select' );
    var switcher_select = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' );
    var switcher_type_select_formats_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_formats_option' );
    var switcher_type_select_sb_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_sb_option' );
    var switcher_type_select_changes_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_changes_option' );
    var switcher_type_select_fieldkey_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_fieldkey_option' );
    var switcher_type_select_fieldval_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_fieldval_option' );
    var switcher_type_select_naws_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_naws_option' );
    var bmlt_semantic_form_meeting_search_div = this.getScopedElement ( 'bmlt_semantic_form_meeting_search_div' );
    var bmlt_semantic_form_changes_div = this.getScopedElement ( 'bmlt_semantic_form_changes_div' );
    var bmlt_semantic_form_main_fields_fieldset = this.getScopedElement ( 'bmlt_semantic_form_main_fields_fieldset' );
    var bmlt_switcher_div_no_options_blurb = this.getScopedElement ( 'bmlt_switcher_div_no_options_blurb' );
    var bmlt_semantic_info_div_download_line = this.getScopedElement ( 'bmlt_semantic_info_div_download_line' );
    var bmlt_semantic_info_div_shortcode_line = this.getScopedElement ( 'bmlt_semantic_info_div_shortcode_line' );
    
    switcher_type_select_formats_option.enable();
    switcher_type_select_sb_option.enable();
    switcher_type_select_changes_option.enable();
    switcher_type_select_fieldkey_option.enable();
    switcher_type_select_fieldval_option.enable();
    switcher_type_select_naws_option.enable();
    bmlt_semantic_info_div_download_line.hide();
    bmlt_semantic_info_div_shortcode_line.hide();

    if ( main_fieldset_select.value == 'DOWNLOAD' )
        {
        bmlt_semantic_info_div_download_line.show();
        
        if ( main_fieldset_direct_uri_div.style.display == 'none' )
            {
            main_fieldset_direct_uri_div.show();
            response_type_select.selectedIndex = 0;
            };
        }
    else
        {
        bmlt_semantic_info_div_shortcode_line.show();
        main_fieldset_direct_uri_div.hide();
        };

    if ( (inItem != response_type_select) && (switcher_select.value == 'GetNAWSDump') && ((response_type_select.value != 'csv') || (main_fieldset_select.value != 'DOWNLOAD')) )
        {
        response_type_select.selectedIndex = 0;
        }

    if ( (inItem != switcher_select) && (switcher_select.value == 'GetNAWSDump') && ((response_type_select.value != 'csv') || (main_fieldset_select.value != 'DOWNLOAD')) )
        {
        switcher_select.selectedIndex = 0;
        }
    else
        {
        if ( (inItem != switcher_select) && (switcher_select.value != 'GetSearchResults') && ((response_type_select.value == 'kml') || (response_type_select.value == 'gpx') || (response_type_select.value == 'poi')) )
            {
            switcher_select.selectedIndex = 0;
            };
        };

    bmlt_switcher_div_no_options_blurb.hide();
    bmlt_semantic_form_changes_div.hide();
    bmlt_semantic_form_main_fields_fieldset.hide();
    bmlt_semantic_form_meeting_search_div.hide();
    
    if ( switcher_select.value == 'GetSearchResults' )
        {
        bmlt_switcher_div_no_options_blurb.hide();
        bmlt_semantic_form_changes_div.hide();
        bmlt_semantic_form_main_fields_fieldset.hide();
        if ( bmlt_semantic_form_meeting_search_div.style.display == 'none' )
            {
            bmlt_semantic_form_meeting_search_div.show();
            this.reloadFromServer();
            };
        }
    else
        {
        bmlt_semantic_form_meeting_search_div.hide();
        if ( switcher_select.value == 'GetChanges' )
            {
            if ( bmlt_semantic_form_changes_div.style.display == 'none' )
                {
                bmlt_semantic_form_changes_div.show();
                };
            }
        else
            {
            if ( switcher_select.value == 'GetFieldValues' )
                {
                if ( bmlt_semantic_form_main_fields_fieldset.style.display == 'none' )
                    {
                    bmlt_semantic_form_main_fields_fieldset.show();
                    this.reloadFromServer();
                    };
                }
            else
                {
                if ( switcher_select.value == 'GetNAWSDump' )
                    {
                    }
                else
                    {
                    bmlt_switcher_div_no_options_blurb.show();
                    };
                };
            };
        };
    
    if ( main_fieldset_select.value == 'DOWNLOAD' )
        {
        if ( response_type_select.value != 'csv' )
            {
            switcher_type_select_naws_option.disable();
            };
        
        if ( (main_fieldset_select.value == 'DOWNLOAD') && ((response_type_select.value == 'kml') || (response_type_select.value == 'gpx') || (response_type_select.value == 'poi')) )
            {
            switcher_type_select_formats_option.disable();
            switcher_type_select_sb_option.disable();
            switcher_type_select_changes_option.disable();
            switcher_type_select_fieldkey_option.disable();
            switcher_type_select_fieldval_option.disable();
            switcher_type_select_naws_option.disable();
            };
        }
    else
        {
        if ( (switcher_select.value != 'GetSearchResults') && (switcher_select.value != 'GetFormats') )
            {
            switcher_select.selectedIndex = 0;
            bmlt_semantic_form_meeting_search_div.show();
            this.reloadFromServer();
            };
        
        switcher_type_select_sb_option.disable();
        switcher_type_select_changes_option.disable();
        switcher_type_select_fieldkey_option.disable();
        switcher_type_select_fieldval_option.disable();
        switcher_type_select_naws_option.disable();
        };
};

/*******************************************************************************************/
/**
    \brief Called when a text item in the form changes.
    
    \param inTextItem   The object that experienced change.
    \param inFocusState If true, then the item is now in focus. If false, it is not in focus.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleTextInput = function ( inTextItem,
                                                    inFocusState
                                                    )
{
    inTextItem.className = inTextItem.defaultClass.toString();
    
    if ( inTextItem.value == inTextItem.defaultValue )
        {
        inTextItem.className += ' bmlt_semantic_form_disabled_text';
        }
    else
        {
        inTextItem.className += ' bmlt_semantic_form_enabled_text';
        };
    
    if ( (inTextItem.value == '') && !inFocusState )
        {
        inTextItem.value = inTextItem.defaultValue;
        }
    else
        {
        if ( (inTextItem.value == inTextItem.defaultValue) && inFocusState )
            {
            inTextItem.value = '';
            }
        else
            {
            var pattern = inTextItem.pattern;
            
            if ( pattern )
                {
                var regex = new RegExp ( pattern );
                if ( (inTextItem.value.length > 0) && !regex.test ( inTextItem.value ) )
                    {
                    if ( inTextItem.value.length == 1 )
                        {
                        inTextItem.previousValue = '';
                        };
                    inTextItem.value = inTextItem.previousValue;
                    };
                };
            };
        };
        
    inTextItem.previousValue = inTextItem.value;
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMainSelectChange = function ( inSelect )
{
    this.setUpMainSelectors ( inSelect );
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleResponseSelectChange = function ( inSelect )
{
    this.setUpMainSelectors ( inSelect );
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleSwitcherSelectChange = function ( inSelect )
{
    this.setUpMainSelectors ( inSelect );
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMainFieldKeySelectChange = function ( inSelect )
{
    this.state.meeting_key = inSelect.value;
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMeetingFieldKeySelectChange = function ( inSelect )
{
    this.state.meeting_key = inSelect.value;
};

/*******************************************************************************************/
/**
    \brief Hides the form if we have a bad root server URI.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.validateVersion = function ()
{
    if ( this.version == 0 )
        {
        var bad_header = this.getScopedElement ( 'bmlt_semantic_badserver_h1' );
        var form = this.getScopedElement ( 'bmlt_semantic_form' );
        
        bad_header.style.display = 'block';
        form.style.display = 'none';
        };
};

/*******************************************************************************************/
/**
    \brief Sets the basic text handler for text items (handles switching classes).
    
    \param inID   The ID that needs to be "scoped."
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setTextHandlers = function ( inID
                                                    )
{
    this.setBasicFunctions ( inID );
    var textItem = this.getScopedElement ( inID );
    
    textItem.className += ' bmlt_semantic_form_disabled_text';
    textItem.onchange = function() { this.formHandler.handleTextInput ( this, true ); };
    textItem.onkeyup = function() { this.formHandler.handleTextInput ( this, true ); };
    textItem.onblur = function() { this.formHandler.handleTextInput ( this, false ); };
    textItem.onfocus = function() { this.formHandler.handleTextInput ( this, true ); };
};

/*******************************************************************************************/
/**
    \brief Sets up simple enable/disable/show/hide functions for a given item.
    
    \param inItemID The ID (unscoped) of the item.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setBasicFunctions = function ( inItemID
                                                    )
{
    var item = this.getScopedElement ( inItemID );
    item.formHandler = this;
    item.oldDisplay = item.style.display;
    if ( item.oldDisplay == 'none' )
        {
        item.oldDisplay = 'block';
        };
    item.defaultValue = item.value;
    item.defaultClass = item.className;
    item.disable = function() { this.disabled = true };
    item.enable = function() { this.disabled = false };
    item.hide = function() { this.style.display = 'none' };
    item.show = function() { this.style.display = this.oldDisplay };
};

/*******************************************************************************************/
/**
    \brief Initialize the main fieldset.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setUpForm_MainFieldset = function ()
{
    this.setBasicFunctions ( 'bmlt_semantic_form_main_mode_select' );
    this.setBasicFunctions ( 'bmlt_semantic_form_direct_url_div' );
    this.setBasicFunctions ( 'bmlt_switcher_div_no_options_blurb' );
    this.setBasicFunctions ( 'bmlt_semantic_form_meeting_search_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_changes_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_main_fields_fieldset' );
    this.setBasicFunctions ( 'bmlt_semantic_form_response_type_select' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_formats_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_sb_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_changes_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_fieldkey_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_fieldval_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_naws_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_response_type_select_kml_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_response_type_select_gpx_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_response_type_select_poi_option' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_download_line' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_shortcode_line' );
    
    this.setTextHandlers ( 'bmlt_semantic_form_changes_from_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_changes_to_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_changes_id_text' );
    
    var main_fieldset_select = this.getScopedElement ( 'bmlt_semantic_form_main_mode_select' );
    main_fieldset_select.onchange = function() { this.formHandler.handleMainSelectChange ( this ) };
    main_fieldset_select.selectedIndex = 0;

    var bmlt_semantic_form_response_type_select = this.getScopedElement ( 'bmlt_semantic_form_response_type_select' );
    bmlt_semantic_form_response_type_select.onchange = function() { this.formHandler.handleResponseSelectChange ( this ) };
    bmlt_semantic_form_response_type_select.selectedIndex = 0;
    
    var bmlt_semantic_form_switcher_type_select = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' );
    bmlt_semantic_form_switcher_type_select.onchange = function() { this.formHandler.handleSwitcherSelectChange ( this ) };
    bmlt_semantic_form_switcher_type_select.selectedIndex = 0;
};

/*******************************************************************************************/
/**
    \brief Called after all the various setup has been done, and "bakes in" the form.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setUpForm = function ()
{
    this.setUpForm_MainFieldset();
    this.reloadFromServer();
};
