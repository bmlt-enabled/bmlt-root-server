/*******************************************************************************************/
/**
    \class  BMLTSemanticResult
    
    \brief  This keeps the state of the result of all that stuff going on in the workshop,
            and will compile a resulting URI or shortcode.
*/
/*******************************************************************************************/
function BMLTSemanticResult ( )
{
};

BMLTSemanticResult.prototype.switcher = null;       ///< The main "?switcher=" value.
BMLTSemanticResult.prototype.meeting_key = null;    ///< The main "meeting_key=" value.

/*******************************************************************************************/
/**
    \class  BMLTSemantic
    
    \brief This is the controlling class for the BMLT interactive semantic workshop.
    
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
    this.field_keys = null;
    this.temp_service_body_objects = null;
    this.state = new BMLTSemanticResult();
    
    this.setUpForm();
};

BMLTSemantic.prototype.id_suffix = null;
BMLTSemantic.prototype.ajax_base_uri = null;
BMLTSemantic.prototype.format_objects = null;
BMLTSemantic.prototype.field_keys = null;
BMLTSemantic.prototype.service_body_objects = null;
BMLTSemantic.prototype.temp_service_body_objects = null;
BMLTSemantic.prototype.state = null;

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
    this.fetchFieldKeys();
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
        var formatContainer = this.getScopedElement ( 'bmlt_semantic_form_formats_fieldset' );
        
        if ( formatContainer )
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
        var sbContainer = this.getScopedElement ( 'bmlt_semantic_form_sb_fieldset' );
        
        if ( sbContainer )
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
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available field keys.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFieldKeys = function ()
{
    var root_server_uri = this.getScopedElement ( 'bmlt_semantic_form_root_server_text_input' );
    
    if ( root_server_uri.value && ((root_server_uri.type == 'hidden') || (root_server_uri.value != root_server_uri.defaultValue)) )
        {
        var sbContainer = this.getScopedElement ( 'bmlt_semantic_form_keys_fieldset' );
        
        if ( sbContainer )
            {
            for ( var i = sbContainer.childNodes.length; i-- > 0; )
                {
                if ( sbContainer.childNodes[i].className == 'bmlt_checkbox_container' )
                    {
                    sbContainer.removeChild ( sbContainer.childNodes[i] );
                    };
                };
            };
        
        this.ajaxRequest ( this.ajax_base_uri + '&GetFieldKeys', this.fetchFieldKeysCallback, 'post', this );
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
    var selectElement = this.getScopedElement ( 'bmlt_semantic_form_field_select' );
    for ( var i = selectElement.childNodes.length; i-- > 0; )
        {
        selectElement.removeChild ( sbContainer.childNodes[i] );
        };
    
    for ( var i = 0; i < this.field_keys.length; i++ )
        {
        var newOption = document.createElement ( 'option' );
        newOption.value = this.field_keys[i].key;
        newOption.appendChild ( document.createTextNode ( this.field_keys[i].description ) );
        selectElement.appendChild ( newOption );
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
    \brief This scans all the various settings, and does what is necessary.
    
    \param inItem This is the item that triggered this map.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.mapPage = function ( inItem
                                            )
{
    this.mapMainSelectors(inItem);
};

/*******************************************************************************************/
/**
    \brief This scans the main selectors, and does what is necessary.
    
    \param inItem This is the item that triggered this map.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.mapMainSelectors = function ( inItem
                                            )
{
    var main_fieldset_select = this.getScopedElement ( 'bmlt_semantic_form_main_mode_select' );
    var main_fieldset_direct_uri_div = this.getScopedElement ( 'bmlt_semantic_form_direct_url_div' );
    var response_type_select = this.getScopedElement ( 'bmlt_semantic_form_response_type_select' );
    var switcher_select = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' );
    var switcher_type_select_naws_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_naws_option' );
    var bmlt_semantic_form_meeting_search_div = this.getScopedElement ( 'bmlt_semantic_form_meeting_search_div' );
    
    if ( main_fieldset_select.value == 'DOWNLOAD' )
        {
        if ( main_fieldset_direct_uri_div.style.display == 'none' )
            {
            main_fieldset_direct_uri_div.show();
            response_type_select.selectedIndex = 0;
            };
        }
    else
        {
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

    if ( switcher_select.value == 'GetSearchResults' )
        {
        if ( bmlt_semantic_form_meeting_search_div.style.display == 'none' )
            {
            bmlt_semantic_form_meeting_search_div.show();
            this.reloadFromServer();
            };
        }
    else
        {
        bmlt_semantic_form_meeting_search_div.hide();
        };
    
    if ( (response_type_select.value == 'csv') && (main_fieldset_select.value == 'DOWNLOAD') )
        {
        switcher_type_select_naws_option.enable();
        }
    else
        {
        switcher_type_select_naws_option.disable();
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
    this.mapPage(inSelect);
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
    this.mapPage(inSelect);
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
    this.mapPage(inSelect);
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
    
    var bmlt_semantic_form_response_type_select = this.getScopedElement ( 'bmlt_semantic_form_response_type_select' );
    bmlt_semantic_form_response_type_select.formHandler = this;
    bmlt_semantic_form_response_type_select.onchange = function() { this.formHandler.handleResponseSelectChange ( this ) };
    bmlt_semantic_form_response_type_select.selectedIndex = 0;
    
    var bmlt_semantic_form_switcher_type_select = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' );
    bmlt_semantic_form_switcher_type_select.formHandler = this;
    bmlt_semantic_form_switcher_type_select.onchange = function() { this.formHandler.handleSwitcherSelectChange ( this ) };
    bmlt_semantic_form_switcher_type_select.selectedIndex = 0;
    
    var main_fieldset_direct_uri_div = this.getScopedElement ( 'bmlt_semantic_form_direct_url_div' );
    main_fieldset_direct_uri_div.formHandler = this;
    main_fieldset_direct_uri_div.oldDisplay = main_fieldset_direct_uri_div.style.display;
    main_fieldset_direct_uri_div.hide = function() { this.style.display = 'none' };
    main_fieldset_direct_uri_div.show = function() { this.style.display = this.oldDisplay };
    
    var switcher_type_select_naws_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_naws_option' );
    switcher_type_select_naws_option.disable = function() { this.disabled = true };
    switcher_type_select_naws_option.enable = function() { this.disabled = false };
    
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
