/*******************************************************************************************/
/**
    \class  BMLTSemantic
    
    \brief
    
    \param inSuffix A constructor parameter that gives a suffix (for multiple forms).
    \param inAJAXURI The base URI for AJAX callbacks.
*/
/*******************************************************************************************/
function BMLTSemantic ( inSuffix,
                        inAJAXURI
                        )
{
    this.id_suffix = inSuffix;
    this.ajax_base_uri = inAJAXURI;
    this.format_objects = null;
    this.service_body_objects = null;
    
    this.setUpForm();
};

BMLTSemantic.prototype.id_suffix = null;
BMLTSemantic.prototype.ajax_base_uri = null;
BMLTSemantic.prototype.format_objects = null;
BMLTSemantic.prototype.service_body_objects = null;

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
BMLTSemantic.prototype.ajaxRequest = function (     url,
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
    this.fetchFormats();
    this.fetchServiceBodies();
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available formats.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFormats = function ()
{
    var root_server_uri = this.getScopedElement ( 'bmlt_semantic_form_root_server_text_input' );
    
    if ( root_server_uri.value && ((root_server_uri.type == 'hidden') || (root_server_uri.value != root_server_uri.defaultValue)) )
        {
        this.ajaxRequest ( this.ajax_base_uri + '&GetInitialFormats', this.fetchFormatsCallback );
        };
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available Service bodies.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchServiceBodies = function ()
{
    var root_server_uri = this.getScopedElement ( 'bmlt_semantic_form_root_server_text_input' );
    
    if ( root_server_uri.value && ((root_server_uri.type == 'hidden') || (root_server_uri.value != root_server_uri.defaultValue)) )
        {
        this.ajaxRequest ( this.ajax_base_uri + '&GetInitialServiceBodies', this.fetchServiceBodiesCallback );
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
        eval ( 'this.format_objects = ' + inHTTPReqObject.responseText + ';' );
        this.populateFormatsSection();
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
        eval ( 'this.service_body_objects = ' + inHTTPReqObject.responseText + ';' );
        this.populateServiceBodiesSection();
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.populateFormatsSection = function()
{
    if ( this.format_objects && this.format_objects.length )
        {
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.populateServiceBodiesSection = function()
{
    if ( this.service_body_objects && this.service_body_objects.length )
        {
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
    \brief Sets the basic text handler for text items (handles switching classes).
    
    \param inID   The ID that needs to be "scoped."
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setTextHandlers = function ( inID
                                                    )
{
    var textItem = this.getScopedElement ( inID );
    
    textItem.defaultValue = textItem.value;
    textItem.defaultClass = textItem.className;
    textItem.formHandler = this;
    textItem.className += ' bmlt_semantic_form_disabled_text';
    textItem.onchange = function() { this.formHandler.handleTextInput ( this, true ); };
    textItem.onkeyup = function() { this.formHandler.handleTextInput ( this, true ); };
    textItem.onblur = function() { this.formHandler.handleTextInput ( this, false ); };
    textItem.onfocus = function() { this.formHandler.handleTextInput ( this, true ); };
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
            };
        };
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMainSelectChange = function ( inSelect
                                                            )
{
    var main_fieldset_direct_uri_div = this.getScopedElement ( 'bmlt_semantic_form_direct_url_div' );
    var switcher_type_select_naws_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_naws_option' );
    var response_type_select = this.getScopedElement ( 'bmlt_semantic_form_response_type_select' );
    
    if ( inSelect.value == 'SHORTCODE' )
        {
        main_fieldset_direct_uri_div.hide();
        switcher_type_select_naws_option.disable();
        }
    else
        {
        main_fieldset_direct_uri_div.show();
        switcher_type_select_naws_option.enable();
        response_type_select.selectedIndex = 0;
        };
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleSwitcherSelectChange = function ( inSelect
                                                            )
{
    var bmlt_semantic_form_meeting_search_div = this.getScopedElement ( 'bmlt_semantic_form_meeting_search_div' );
    
    if ( inSelect.value == 'GetSearchResults' )
        {
        bmlt_semantic_form_meeting_search_div.show();
        this.fetchFormats();
        this.fetchServiceBodies();
        }
    else
        {
        bmlt_semantic_form_meeting_search_div.hide();
        };
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleResponseSelectChange = function ( inSelect
                                                            )
{
    var switcher_type_select_naws_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_naws_option' );
    
    if ( inSelect.value == 'csv' )
        {
        switcher_type_select_naws_option.enable();
        }
    else
        {
        switcher_type_select_naws_option.disable();
        
        if ( switcher_type_select_naws_option.selected )
            {
            inSelect.selectedIndex = 0;
            }
        };
};

/*******************************************************************************************/
/**
    \brief Initialize the root server form.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setUpForm_RootServerInput = function ()
{
    this.setTextHandlers ( 'bmlt_semantic_form_root_server_text_input' );
};

/*******************************************************************************************/
/**
    \brief Initialize the main fieldset.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setUpForm_MainFieldset = function ()
{
    var main_fieldset_select = this.getScopedElement ( 'bmlt_semantic_form_main_mode_select' );
    main_fieldset_select.formHandler = this;
    main_fieldset_select.onchange = function() { this.formHandler.handleMainSelectChange ( this ) };
    main_fieldset_select.selectedIndex = 0;
    
    var bmlt_semantic_form_switcher_type_select = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' );
    bmlt_semantic_form_switcher_type_select.formHandler = this;
    bmlt_semantic_form_switcher_type_select.onchange = function() { this.formHandler.handleSwitcherSelectChange ( this ) };
    
    var main_fieldset_direct_uri_div = this.getScopedElement ( 'bmlt_semantic_form_direct_url_div' );
    main_fieldset_direct_uri_div.formHandler = this;
    main_fieldset_direct_uri_div.oldDisplay = main_fieldset_direct_uri_div.style.display;
    main_fieldset_direct_uri_div.hide = function() { this.style.display = 'none' };
    main_fieldset_direct_uri_div.show = function() { this.style.display = this.oldDisplay };
    
    var switcher_type_select_naws_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_naws_option' );
    switcher_type_select_naws_option.disable = function() { this.disabled = true; if ( this.selected ) { this.parentNode.selectedIndex = 0 }; };
    switcher_type_select_naws_option.enable = function() { this.disabled = false };
    
    var bmlt_semantic_form_response_type_select = this.getScopedElement ( 'bmlt_semantic_form_response_type_select' );
    bmlt_semantic_form_response_type_select.formHandler = this;
    bmlt_semantic_form_response_type_select.onchange = function() { this.formHandler.handleResponseSelectChange ( this ) };
    
    var bmlt_semantic_form_meeting_search_div = this.getScopedElement ( 'bmlt_semantic_form_meeting_search_div' );
    bmlt_semantic_form_meeting_search_div.formHandler = this;
    bmlt_semantic_form_meeting_search_div.oldDisplay = bmlt_semantic_form_meeting_search_div.style.display;
    bmlt_semantic_form_meeting_search_div.hide = function() { this.style.display = 'none' };
    bmlt_semantic_form_meeting_search_div.show = function() { this.style.display = this.oldDisplay };
};

/*******************************************************************************************/
/**
    \brief Called after all the various setup has been done, and "bakes in" the form.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setUpForm = function ()
{
    this.setUpForm_RootServerInput();
    this.setUpForm_MainFieldset();
    this.reloadFromServer();
};
