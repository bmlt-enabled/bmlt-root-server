/*******************************************************************************************/
/**
    \class  BMLTSemanticResult
    
    \brief  This keeps the state of the result of all that stuff going on in the workshop,
            and will compile a resulting URI or shortcode.
            
        This file is part of the Basic Meeting List Toolbox (BMLT).

        Find out more at: http://bmlt.magshare.org

        BMLT is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as
        published by the Free Software Foundation, either version 3
        of the License, or (at your option) any later version.

        BMLT is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
        See the GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this code.  If not, see <http://www.gnu.org/licenses/>.
        
        Version: 1.0.10
*/
/*******************************************************************************************/
function BMLTSemanticResult (   inRootServerURI,
                                inOwner
                            )
{
    this.root_server_uri = inRootServerURI;
    this.owner = inOwner;

};

BMLTSemanticResult.prototype.owner = null;              ///< The object that "owns" this.
BMLTSemanticResult.prototype.switcher = null;           ///< The main "?switcher=" value.
BMLTSemanticResult.prototype.meeting_key = null;        ///< The main "meeting_key=" value.
BMLTSemanticResult.prototype.meeting_key_value = null;  ///< The value selected by the field select.
BMLTSemanticResult.prototype.root_server_uri = null;    ///< The main Root Server URI.
BMLTSemanticResult.prototype.services = null;           ///< The selected Service bodies. This is a CSV string of integer IDs.
BMLTSemanticResult.prototype.formats = null;            ///< The selected formats. This is a CSV string of integer IDs.
BMLTSemanticResult.prototype.weekdays = null;           ///< The selected weekdays (1-7). This is a CSV string of integer IDs.
BMLTSemanticResult.prototype.sb_id = null;              ///< This contains the Service body used for the NAWS dump.
BMLTSemanticResult.prototype.change_start = null;       ///< This will be the start date for getting changes.
BMLTSemanticResult.prototype.change_end = null;         ///< This will be the end date for getting changes.
BMLTSemanticResult.prototype.change_id = null;          ///< This will be the meeting ID for changes.
BMLTSemanticResult.prototype.change_sb_id = null;       ///< This will be the Service body ID for changes.
BMLTSemanticResult.prototype.searchText = null;         ///< The text to search for in meetings.
BMLTSemanticResult.prototype.searchTextModifier = null; ///< Any modifier for the text search.
BMLTSemanticResult.prototype.searchTextRadius = null;   ///< A possible radius for the text (if location).
BMLTSemanticResult.prototype.searchMapRadius = -10;     ///< A radius for the map. Default is auto-search 10 meetings.
BMLTSemanticResult.prototype.searchLongitude = null;    ///< If using the map, the longitude.
BMLTSemanticResult.prototype.searchLatitude = null;     ///< If using the map, the latitude.
BMLTSemanticResult.prototype.compiled_params = null;    ///< This will contain the temporary compiled parameters.
BMLTSemanticResult.prototype.fields = Array();          ///< This will hold any specific fields to be returned.
BMLTSemanticResult.prototype.sorts = null;              ///< This holds an array of objects that will indicate a chosen sort. The object schema will be: {"key":STRING,"order",INTEGER}
BMLTSemanticResult.prototype.weekdayHeader = null;      ///< This will be set to nonzero if the BMLT_SIMPLE result will be separated by weekday.
BMLTSemanticResult.prototype.startTimeMin = null;       ///< This will have an array, with the minimum start time. Hours will be element 0, minutes, element 1.
BMLTSemanticResult.prototype.startTimeMax = null;       ///< This will have an array, with the maximum start time. Hours will be element 0, minutes, element 1.
BMLTSemanticResult.prototype.durationMin = null;        ///< This will have an array, with the minimum duration. Hours will be element 0, minutes, element 1.
BMLTSemanticResult.prototype.durationMax = null;        ///< This will have an array, with the minimum duration. Hours will be element 0, minutes, element 1.
BMLTSemanticResult.prototype.valid = null;              ///< This will be non-null if the compiled result is valid (only after compile()).

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemanticResult.prototype.compile = function()
{
    this.compiled_params = 'switcher=' + this.switcher;
    this.valid = null;
    
    switch ( this.switcher )
        {
        case 'GetSearchResults':
            this.compileSearchResults();
            break;
            
        case 'GetChanges':
            this.compileChanges();
            break;
            
        case 'GetFieldValues':
            this.compileFieldValues();
            break;
            
        case 'GetNAWSDump':
            this.compileNAWSDump();
            break;
            
        case 'GetFormats':
            this.compileFormats();
            break;
        
        default:
            this.valid = true;
            break;
        };
        
    return this.compiled_params;
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemanticResult.prototype.compileFormats = function()
{
    var formatLangSelect = this.owner.getScopedElement ( 'bmlt_semantic_formats_lang_select' );

    if ( formatLangSelect && formatLangSelect.value )
        {
        this.compiled_params += '&lang_enum=' + formatLangSelect.value;
        };
    
    this.valid = true;
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemanticResult.prototype.compileSearchResults = function()
{
    var responseTypeSelect = this.owner.getScopedElement ( 'bmlt_semantic_form_response_type_select' );
    var mainSelect = this.owner.getScopedElement ( 'bmlt_semantic_form_main_mode_select' );
    var getUsedCheckbox = this.owner.getScopedElement ( 'bmlt_semantic_form_used_formats_checkbox' );
    
    if ( (responseTypeSelect.value == 'xml') || (responseTypeSelect.value == 'json') )
        {
        if ( getUsedCheckbox && getUsedCheckbox.checked )
            {
            var getOnlyUsedCheckbox = this.owner.getScopedElement ( 'bmlt_semantic_form_just_used_formats_checkbox' );
        
            this.compiled_params += '&get_used_formats=1';
        
            if ( getOnlyUsedCheckbox && getOnlyUsedCheckbox.checked )
                {
                this.compiled_params += '&get_formats_only=1';
                };
            };
        }
    else
        {
        if ( (responseTypeSelect.value == 'simple-block') || (responseTypeSelect.value == 'simple') )
            {
            if ( getUsedCheckbox.checked )
                {
                this.compiled_params = 'switcher=GetFormats';
                };
            };
        };
    
    if ( this.services && this.services.length )
        {
        if ( this.services.length > 1 )
            {
            for ( i = 0; i < this.services.length; i++ )
                {
                this.compiled_params += '&services[]=' + parseInt ( this.services[i] );
                };
            }
        else
            {
            this.compiled_params += '&services=' + parseInt ( this.services );
            };
        };
    
    if ( this.weekdays )
        {
        weekdays = this.weekdays.split ( ',' );
        
        if ( weekdays.length > 1 )
            {
            for ( i = 0; i < weekdays.length; i++ )
                {
                this.compiled_params += '&weekdays[]=' + parseInt ( weekdays[i] );
                };
            }
        else
            {
            this.compiled_params += '&weekdays=' + parseInt ( this.weekdays );
            };
        };
    
    if ( this.formats )
        {
        var formats_array = this.formats.split ( ',' );;
    
        if ( formats_array && formats_array.length )
            {
            if ( formats_array.length > 1 )
                {
                for ( i = 0; i < formats_array.length; i++ )
                    {
                    var format = parseInt ( formats_array[i] );
                
                    if ( format )
                        {
                        this.compiled_params += '&formats[]=' + format.toString();
                        };
                    };
                }
            else
                {
                this.compiled_params += '&formats=' + parseInt ( formats_array[0] ).toString();
                };
            };
        };
    
    if ( this.meeting_key && this.meeting_key_value )
        {
        this.compiled_params += '&meeting_key=' + this.meeting_key;
        this.compiled_params += '&meeting_key_value=' + escape ( this.meeting_key_value );
        };
    
    if ( this.searchText )
        {
        this.compiled_params += '&SearchString=' + escape ( this.searchText );
        if ( this.searchTextModifier )
            {
            this.compiled_params += '&' + this.searchTextModifier;
            if ( this.searchTextRadius )
                {
                this.compiled_params += '&SearchStringRadius=' + parseFloat ( this.searchTextRadius );
                };
            };
        };
    
    if ( this.searchMapRadius )
        {
        var radiusUnitsSelect = this.owner.getScopedElement ( 'bmlt_semantic_form_map_search_text_radius_units' );
        
        if ( radiusUnitsSelect && radiusUnitsSelect.value )
            {
            this.compiled_params += '&' + radiusUnitsSelect.value + '=' + escape ( this.searchMapRadius );
            this.compiled_params += '&long_val=' + escape ( this.searchLongitude );
            this.compiled_params += '&lat_val=' + escape ( this.searchLatitude );
            };
        };
    
    if ( this.fields.length > 0 )
        {
        this.compiled_params += '&data_field_key=' + this.fields.join ( ',' );
        };
        
    if ( this.sorts && this.sorts.length )
        {
        var sortKeys = Array ();
        
        for ( var i = 0; i < this.sorts.length; i++ )
            {
            sortKeys.push ( this.sorts[i].key );
            };
        
        this.compiled_params += '&sort_keys=' + sortKeys.join ( ',' );
        };
    
    if ( this.weekdayHeader && (mainSelect.value == 'SHORTCODE') )
        {
        this.compiled_params += '&weekday_header=1';
        };
    
    if ( this.startTimeMin && (this.startTimeMin[0] || this.startTimeMin[1]) )
        {
        if ( this.startTimeMin[0] )
            {
            this.compiled_params += '&StartsAfterH=' + this.startTimeMin[0].toString();
            };
        if ( this.startTimeMin[1] )
            {
            this.compiled_params += '&StartsAfterM=' + this.startTimeMin[1].toString();
            };
        };
    
    if ( this.startTimeMax && (this.startTimeMax[0] || this.startTimeMax[1]) )
        {
        if ( this.startTimeMax[0] )
            {
            this.compiled_params += '&StartsBeforeH=' + this.startTimeMax[0].toString();
            };
        if ( this.startTimeMax[1] )
            {
            this.compiled_params += '&StartsBeforeM=' + this.startTimeMax[1].toString();
            };
        };
    
    if ( this.durationMin && (this.durationMin[0] || this.durationMin[1]) )
        {
        if ( this.durationMin[0] )
            {
            this.compiled_params += '&MinDurationH=' + this.durationMin[0].toString();
            };
        if ( this.durationMin[1] )
            {
            this.compiled_params += '&MinDurationM=' + this.durationMin[1].toString();
            };
        };
    
    if ( this.durationMax && (this.durationMax[0] || this.durationMax[1]) )
        {
        if ( this.durationMax[0] )
            {
            this.compiled_params += '&MaxDurationH=' + this.durationMax[0].toString();
            };
        if ( this.durationMax[1] )
            {
            this.compiled_params += '&MaxDurationM=' + this.durationMax[1].toString();
            };
        };
    
    this.valid = true;
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemanticResult.prototype.compileChanges = function()
{
    if ( this.change_start )
        {
        this.compiled_params += '&start_date=' + this.change_start;
        };
    
    if ( this.change_end )
        {
        this.compiled_params += '&end_date=' + this.change_end;
        };
    
    if ( this.change_id )
        {
        this.compiled_params += '&meeting_id=' + this.change_id;
        };
    
    if ( this.change_sb_id )
        {
        this.compiled_params += '&service_body_id=' + this.change_sb_id;
        };
    
    this.valid = true;
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemanticResult.prototype.compileFieldValues = function()
{
    if ( this.meeting_key )
        {
        this.compiled_params += '&meeting_key=' + escape ( this.meeting_key );
    
        if ( (this.meeting_key == 'formats') && this.formats )
            {
            this.compiled_params += '&specific_formats=' + this.formats;
            };
    
        this.valid = true;
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemanticResult.prototype.compileNAWSDump = function()
{
    if ( this.sb_id )
        {
        this.compiled_params += '&sb_id=' + parseInt ( this.sb_id );
        this.valid = true;
        };
};

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
    this.state = new BMLTSemanticResult(inRootServerURI,this);
    this.version = inVersion;
    
    this.setUpForm();
};

BMLTSemantic.prototype.version = null;
BMLTSemantic.prototype.id_suffix = null;
BMLTSemantic.prototype.ajax_base_uri = null;
BMLTSemantic.prototype.format_objects = null;
BMLTSemantic.prototype.languages = null;
BMLTSemantic.prototype.field_keys = null;
BMLTSemantic.prototype.field_values = null;
BMLTSemantic.prototype.service_body_objects = null;
BMLTSemantic.prototype.temp_service_body_objects = null;
BMLTSemantic.prototype.state = null;
BMLTSemantic.prototype.mapObject = null;
BMLTSemantic.prototype.current_lat = 34.23592;
BMLTSemantic.prototype.current_lng = -118.563659;
BMLTSemantic.prototype.current_zoom = 11;
BMLTSemantic.prototype.serverInfo = null;

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
    this.state.formats = null;
    this.state.meeting_key = null;
    this.state.meeting_key_value = null;
    this.state.services = null;
    this.state.weekdays = null;
    this.state.sb_id = null;
    this.state.change_start = null;
    this.state.change_end = null;
    this.state.change_id = null;
    this.state.compiled_params = null;
    this.state.searchText = null;
    this.state.searchTextModifier = null;
    this.state.searchTextRadius = null;
    this.state.searchMapRadius = null;
    this.state.sorts = null;

    this.format_objects = null;
    this.languages = null;
    this.service_body_objects = null;
    this.field_keys = null;
    this.field_values = null;
    this.temp_service_body_objects = null;
    
    this.fetchFormats();
    this.fetchLangs();
    this.fetchServiceBodies();
    this.fetchFieldKeys();
    this.clearWeekdays();
    this.clearTextSearchItems();
    this.clearSorts();
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available formats.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchVersion = function ()
{
    this.ajaxRequest ( this.ajax_base_uri + '&GetVersion', this.fetchVersionCallback, 'get', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the server information.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchServerInfo = function ()
{
    this.serverInfo = null;
    if ( this.version >= 2006020 )
        {
        this.ajaxRequest ( this.ajax_base_uri + '&GetServerInfo', this.fetchServerInfoCallback, 'get', this );
        };
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available formats.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFormats = function ()
{
    this.state.formats = null;
    this.state.unformats = null;
    
    this.ajaxRequest ( this.ajax_base_uri + '&GetInitialFormats', this.fetchFormatsCallback, 'get', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available languages.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchLangs = function ()
{
    this.languages = null;
    if ( this.version >= 2006020 )
        {
        this.ajaxRequest ( this.ajax_base_uri + '&GetLangs', this.fetchLangsCallback, 'get', this );
        };
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available Service bodies.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchServiceBodies = function ()
{
    this.state.services = null;
    this.state.sb_id = null;
    
    this.ajaxRequest ( this.ajax_base_uri + '&GetInitialServiceBodies', this.fetchServiceBodiesCallback, 'get', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available field keys.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFieldKeys = function ()
{
    this.getScopedElement ( 'bmlt_switcher_field_value_div_formats' ).innerHTML = '';
    this.getScopedElement ( 'bmlt_switcher_field_value_div_no_selected_formats_blurb' ).hide();
    this.getScopedElement ( 'bmlt_semantic_form_meeting_fields_fieldset_contents_div' ).hide();
    this.ajaxRequest ( this.ajax_base_uri + '&GetFieldKeys', this.fetchFieldKeysCallback, 'get', this );
};

/*******************************************************************************************/
/**
    \brief Sets up and performs an AJAX call to fetch the available field keys.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchFieldValues = function ()
{
    this.getScopedElement ( 'bmlt_semantic_form_meeting_fields_fieldset_contents_div' ).hide();
    var key = this.state.meeting_key.toString();
    var url = this.ajax_base_uri + '&GetFieldValues&meeting_key=' + key;
    this.ajaxRequest ( url, this.fetchFieldValuesCallback, 'get', this );
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
        context.refreshURI();
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchServerInfoCallback = function (inHTTPReqObject
                                                            )
{
    if ( inHTTPReqObject.responseText )
        {
        var context = inHTTPReqObject.extraData;
        eval ( 'context.serverInfo = ' + inHTTPReqObject.responseText + '[0];' );
        context.current_lat = parseFloat ( context.serverInfo.centerLatitude );
        context.current_lng = parseFloat ( context.serverInfo.centerLongitude );
        context.current_zoom = parseInt ( context.serverInfo.centerZoom, 10 );
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

        if ( context.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' ).value == 'GetFieldValues' )
            {
            context.populateFormatsSection(context.getScopedElement ( 'bmlt_switcher_field_value_div_formats' ), false );
            }
        else
            {
            context.populateFormatsSection(context.getScopedElement ( 'bmlt_semantic_form_formats_fieldset_div' ), false );
            context.populateFormatsSection(context.getScopedElement ( 'bmlt_semantic_form_not_formats_fieldset_div' ), true );
            };
        };
};

/*******************************************************************************************/
/**
    \brief The response.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fetchLangsCallback = function ( inHTTPReqObject
                                                        )
{
    if ( inHTTPReqObject.responseText )
        {
        var context = inHTTPReqObject.extraData;
        eval ( 'var serverInfo = ' + inHTTPReqObject.responseText + ';' );
        context.languages = serverInfo[0].langs.toString().split ( ',' );
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.populateFormatsSection = function(   formatContainer,
                                                            unformat
                                                        )
{
    formatContainer.innerHTML = '';
    if ( this.format_objects && this.format_objects.length )
        {
        for ( var i = 0; i < this.format_objects.length; i++ )
            {
            var formatObject = this.format_objects[i];
            var newContainer = document.createElement ( 'div' );
            newContainer.id = this.getScopedID ( formatContainer.id + '_' + formatObject.id );
            newContainer.className ='bmlt_checkbox_container';
            
            var newCheckbox = document.createElement ( 'input' );
            newCheckbox.type = 'checkbox';
            newCheckbox.formatObject = formatObject;
            newCheckbox.id = this.getScopedID ( formatContainer.id + '_checkbox_' + formatObject.id );
            newCheckbox.value = unformat ? -parseInt ( formatObject.id ) : parseInt ( formatObject.id );
            newCheckbox.formHandler = this;
            
            newCheckbox.onchange = function(){ this.formHandler.handleFormatCheckbox ( this ) };
            
            newCheckbox.title = formatObject.name_string + ' - ' + formatObject.description_string;
            newCheckbox.className ='bmlt_checkbox_input';
            newContainer.appendChild ( newCheckbox );
            
            var newCheckboxLabel = document.createElement ( 'label' );
            newCheckboxLabel.htmlFor = newCheckbox.id;
            newCheckboxLabel.id = this.getScopedID ( formatContainer.id + '_label_' + formatObject.id );
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
    this.refreshURI();
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
    var sb_select1 = this.getScopedElement ( 'bmlt_switcher_naws_dump_sb_select' );
    var sb_select2 = this.getScopedElement ( 'bmlt_switcher_changes_sb_select' );
    var yesMen  = this.getScopedElement ( 'bmlt_semantic_form_sb_fieldset_div' );
    var noMam  = this.getScopedElement ( 'bmlt_semantic_form_sb_not_fieldset_div' );
    var yesMen_container  = this.getScopedElement ( 'bmlt_semantic_form_sb_fieldset' );
    var noMam_container  = this.getScopedElement ( 'bmlt_semantic_form_sb_not_fieldset' );
    
    for ( var i = sb_select1.options.length - 1; i > 0; i-- )
        {
        sb_select1.removeChild ( sb_select1.options[i] );
        };
    
    for ( var i = sb_select2.options.length - 1; i > 0; i-- )
        {
        sb_select2.removeChild ( sb_select2.options[i] );
        };
    
    for ( var i = 0; i < this.temp_service_body_objects.length; i++ )
        {
        var sb = this.temp_service_body_objects[i];
        var newOption = document.createElement ( 'option' );
        newOption.value = sb.id;
        newOption.appendChild ( document.createTextNode ( sb.name ) );
        sb_select1.appendChild ( newOption );
        var newOption = document.createElement ( 'option' );
        newOption.value = sb.id;
        newOption.appendChild ( document.createTextNode ( sb.name ) );
        sb_select2.appendChild ( newOption );
        };
    
    if ( this.temp_service_body_objects.length < 2 )
        {
        yesMen_container.hide();
        noMam_container.hide();
        }
    else
        {
        yesMen_container.show();
        noMam_container.show();
        };
    
    this.state.services = null;
    this.organizeServiceBodies();
    
    if ( this.service_body_objects && this.service_body_objects.length )
        {
        this.createServiceBodyList ( null, yesMen, false );
        this.createServiceBodyList ( null, noMam, true );
        };
    
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.createServiceBodyList = function(inServiceBodyObject,
                                                        inContainerObject,
                                                        inMinus
                                                        )
{
    if ( inContainerObject )
        {
        var sb_array = null;
        var id = 0;
        var newListContainer = null;
        var not_extra = inMinus ? 'not_' : '';
    
        if ( inServiceBodyObject )
            {
            id = inServiceBodyObject.id;
        
            var checkboxElement = document.createElement ( 'dt' );
            checkboxElement.id = this.getScopedID ( 'bmlt_sb_dt_' + not_extra + id.toString() );
            checkboxElement.className = 'bmlt_sb_dt';
            this.createServiceBodyCheckbox ( inServiceBodyObject, checkboxElement, inMinus );
            inContainerObject.appendChild ( checkboxElement );
        
            if ( inServiceBodyObject.childServiceBodies )
                {
                newListContainer = document.createElement ( 'dd' );
                newListContainer.id = this.getScopedID ( 'bmlt_sb_dd_' + not_extra + id.toString() );
                newListContainer.className = 'bmlt_sb_dd';
                inContainerObject.appendChild ( newListContainer ); 
                sb_array = inServiceBodyObject.childServiceBodies;
                };
            }
        else
            {
            sb_array = this.service_body_objects;
            newListContainer = inContainerObject;
            newListContainer.innerHTML = '';
            };

        if ( newListContainer && sb_array && sb_array.length )
            {
            var newSubList = document.createElement ( 'dl' );
            newSubList.id = this.getScopedID ( 'bmlt_sb_dl_' + not_extra + id.toString() );
            newSubList.className = 'bmlt_sb_dl';
        
            for ( var i = 0; i < sb_array.length; i++ )
                {
                this.createServiceBodyList ( sb_array[i], newSubList, inMinus );
                };

            newListContainer.appendChild ( newSubList );
            };
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.createServiceBodyCheckbox = function (   inServiceBodyObject,
                                                                inContainerObject,
                                                                inMinus
                                                            )
{
    var not_extra = inMinus ? 'not_' : '';

    var newCheckbox = document.createElement ( 'input' );
    newCheckbox.type = 'checkbox';
    newCheckbox.id = this.getScopedID ( 'bmlt_semantic_form_sb_checkbox_' + not_extra + inServiceBodyObject.id );
    newCheckbox.value = (inMinus ? -1 : 1) * parseInt ( inServiceBodyObject.id );
    newCheckbox.title = inServiceBodyObject.description;
    newCheckbox.className ='bmlt_checkbox_input';
    newCheckbox.formHandler = this;
    newCheckbox.serviceBody = inServiceBodyObject;
    newCheckbox.onchange = function() { this.formHandler.handleServiceBodyCheck(this) };
    inContainerObject.appendChild ( newCheckbox );
    
    var newCheckboxLabel = document.createElement ( 'label' );
    newCheckboxLabel.htmlFor = newCheckbox.id;
    newCheckboxLabel.id = this.getScopedID ( 'bmlt_semantic_form_sb_checkbox_label_' + not_extra + inServiceBodyObject.id );
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
        context.setAllSortFieldFunctions();
        context.setAllSortFieldState();
        context.clearSorts();
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
        var key = this.field_keys[i].key;
        
        var newOption = document.createElement ( 'option' );
        newOption.value = key;
        newOption.appendChild ( document.createTextNode ( this.field_keys[i].description ) );
        mainSelectElement.appendChild ( newOption );
        
        if ( (key != 'formats') && (key != 'weekday_tinyint') && (key != 'service_body_bigint') && (key != 'id_bigint') && (key != 'longitude') && (key != 'latitude') )
            {
            newOption = document.createElement ( 'option' );
            newOption.value = key;
            newOption.appendChild ( document.createTextNode ( this.field_keys[i].description ) );
            meetingSelectElement.appendChild ( newOption );
            };
        };
    
    mainSelectElement.selectedIndex = 0;
    meetingSelectElement.selectedIndex = 0;
    this.refreshURI();
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
        
        context.field_values.sort ( function(a,b){
                                                eval ( 'var textA = a.' + context.state.meeting_key.toString() + '.toString();var textB = b.' + context.state.meeting_key.toString() + '.toString();' );
                                                ret = 0;
                                                if ( textA != 'NULL' )
                                                    {
                                                    if ( textB == 'NULL' )
                                                        {
                                                        ret = 1;
                                                        }
                                                    else
                                                        {
                                                        eval ( 'var numA = parseFloat ( a.' + context.state.meeting_key + ' );var numB = parseFloat ( b.' + context.state.meeting_key + ' );' );
                                                        if ( !isNaN ( numA ) && !isNaN ( numB ) &&  numA > numB )
                                                            {
                                                            ret = 1;
                                                            }
                                                        else
                                                            {
                                                            if ( !isNaN ( numA ) && !isNaN ( numB ) &&  numB > numA )
                                                                {
                                                                ret = -1;
                                                                }
                                                            else
                                                                {
                                                                eval ( 'var intA = parseInt ( a.' + context.state.meeting_key + ' );var intB = parseInt ( b.' + context.state.meeting_key + ' );' );
                                                                if ( intA > intB )
                                                                    {
                                                                    ret = 1;
                                                                    }
                                                                else
                                                                    {
                                                                    if ( intB > intA )
                                                                        {
                                                                        ret = -1;
                                                                        }
                                                                    else
                                                                        {
                                                                        if ( textA > textB )
                                                                            {
                                                                            ret = 1;
                                                                            }
                                                                        else
                                                                            {
                                                                            if ( textB > textA )
                                                                                {
                                                                                ret = -1;
                                                                                };
                                                                            };
                                                                        };
                                                                    };
                                                                };
                                                            };
                                                        };
                                                    };
                                                
                                                return ret;
                                            }
                                        );
        
        context.updateFieldValuesPopup();
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.updateFieldValuesPopup = function ()
{
    var select_object = this.getScopedElement ( 'bmlt_semantic_form_value_select' );

    for ( var i = select_object.options.length - 1; i > 0; i-- )
        {
        select_object.removeChild ( select_object.options[i] );
        };
    
    if ( this.field_values && this.field_values.length )
        {
        for ( var i = 0; i < this.field_values.length; i++ )
            {
            var value_object = this.field_values[i];
            
            eval ( 'var value_text = value_object.' + this.state.meeting_key.toString() + '.toString();' );
            if ( value_text != 'NULL' )
                {
                var newOption = document.createElement ( 'option' );
                newOption.value = value_text;
                newOption.appendChild ( document.createTextNode ( value_text ) );
                select_object.appendChild ( newOption );
                };
            };
        
        this.getScopedElement ( 'bmlt_semantic_form_meeting_fields_fieldset_contents_div' ).show();
        };
    
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.fieldValueChosen = function ( inSelect
                                                    )
{
    this.getScopedElement ( 'bmlt_semantic_form_value_text' ).value = inSelect.value;
    this.getScopedElement ( 'bmlt_semantic_form_value_text' ).onchange ( this.getScopedElement ( 'bmlt_semantic_form_value_text' ) );
    this.getScopedElement ( 'bmlt_semantic_form_value_text' ).focus();
    this.refreshURI();
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
    
    if ( inTextItem.defaultValue && (inTextItem.value == inTextItem.defaultValue) )
        {
        inTextItem.className += ' bmlt_semantic_form_disabled_text';
        }
    else
        {
        inTextItem.className += ' bmlt_semantic_form_enabled_text';
        };
    
    if ( (inTextItem.value == '') && !inFocusState )
        {
        if ( inTextItem.defaultValue )
            {
            inTextItem.value = inTextItem.defaultValue;
            };
            
        inTextItem.className += ' bmlt_semantic_form_disabled_text';
        }
    else
        {
        if ( inTextItem.defaultValue && (inTextItem.value == inTextItem.defaultValue) && inFocusState )
            {
            inTextItem.value = '';
            inTextItem.className += ' bmlt_semantic_form_enabled_text';
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
    
    if ( inTextItem.additionalHandler )
        {
        inTextItem.additionalHandler();
        };
    
    this.refreshURI();
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
    this.reloadFromServer();
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
    this.reloadFromServer();
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
    this.reloadFromServer();
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleFieldKeySelectChange = function ( inSelect )
{
    var key = inSelect.value;
    this.state.meeting_key = key;
    
    if ( inSelect.id == this.getScopedID ( 'bmlt_semantic_form_field_select' ) )
        {
        this.getScopedElement ( 'bmlt_semantic_form_value_text' ).value = this.getScopedElement ( 'bmlt_semantic_form_value_text' ).defaultValue;
        this.getScopedElement ( 'bmlt_semantic_form_value_text' ).focus();
        this.fetchFieldValues();
        };

    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleFieldKeyValueSelectChange = function ( inSelect )
{
    this.state.meeting_key_value = inSelect.value;
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
    
    \param inCheckbox   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleWeekdayHeaderChange = function ( inCheckbox )
{
    this.state.weekdayHeader = inCheckbox.checked;
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleServiceBodyCheck = function ( inCheckbox )
{
    this.updateServiceBodies ( inCheckbox );
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
    
    \param inSelect   The object that experienced change.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.updateServiceBodies = function ( inCheckboxObject )
{
    var service_body_object = inCheckboxObject.serviceBody;
    var sb_id = parseInt ( inCheckboxObject.value );
    var childBodies = service_body_object.childServiceBodies;
    
    var found = false;
    
    if ( this.state.services && this.state.services.length )
        {
        for ( var i = 0; i < this.state.services.length; i++ )
            {
            var serviceID = parseInt ( this.state.services[i] );
        
            if ( Math.abs ( serviceID ) == Math.abs ( sb_id ) )
                {
                if ( inCheckboxObject.checked )
                    {
                    this.state.services[i] = parseInt ( inCheckboxObject.value );
                    found = true;
                    }
                else
                    {
                    if ( serviceID == sb_id )
                        {
                        this.state.services.splice ( i, 1 );
                        found = true;
                        };
                    };
                break;
                };
            };
        }
    else
        {
        this.state.services = Array();
        };
    
    if ( !found && inCheckboxObject.checked )
        {
        this.state.services.push ( parseInt ( inCheckboxObject.value ) );
        };
    
    if ( inCheckboxObject.checked )
        {
        var not_extra = (parseInt ( inCheckboxObject.value ) > 0) ? 'not_' : '';
        this.getScopedElement ( 'bmlt_semantic_form_sb_checkbox_' + not_extra + Math.abs ( sb_id ) ).checked = false;
        };
    
    if ( childBodies )
        {
        for ( var i = 0; i < childBodies.length; i++ )
            {
            var child = childBodies[i];
            var not_extra = sb_id < 0 ? 'not_' : '';
            var cb_element = this.getScopedElement ( 'bmlt_semantic_form_sb_checkbox_' + not_extra + parseInt ( childBodies[i].id ) );
            
            cb_element.checked = inCheckboxObject.checked;
            
            this.updateServiceBodies ( cb_element );
            };
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleSortFieldChange = function ( inOptionObject )
{
    var sort = new Object();
    
    sort.key = inOptionObject.fieldKey.toString();
    sort.order = parseInt ( inOptionObject.value );
    
    var selectObject = inOptionObject.parentNode;
    selectObject.className = selectObject.defaultClass;

    if ( !this.state.sorts )
        {
        this.state.sorts = new Array ( sort );
        }
    else
        {
        var found = false;
        
        for ( var i = 0; i < this.state.sorts.length; i++ )
            {
            if ( this.state.sorts[i].key == sort.key )
                {
                found = true;
                if ( sort.order > 0 )
                    {
                    this.state.sorts[i].order = sort.order;
                    }
                else
                    {
                    this.state.sorts.splice ( i, 1 );
                    };
                    
                break;
                };
            };
        
        if ( !found )
            {
            this.state.sorts.push ( sort );
            };
        };
    
    var sortFunc = function ( inA, inB )
        {
            var ret = 0;
            
            if ( inA.order < inB.order )
                {
                ret = -1;
                }
            else
                {
                if ( inB.order < inA.order )
                    {
                    ret = 1;
                    };
                };
                
            return ret;
        };
    
    this.state.sorts.sort ( sortFunc );
    
    if ( this.state.sorts.length == 1 )
        {
        if ( this.state.sorts[0].order == 0 )
            {
            this.state.sorts = null;
            }
        else
            {
            this.state.sorts[0].order = 1;
            };
        }
    else
        {
        if ( this.state.sorts.length > 1 )
            {
            this.state.sorts[0].order = 1;
            for ( var i = 1; i < this.state.sorts.length; i++ )
                {
                this.state.sorts[i].order = (this.state.sorts[i - 1].order + 1);
                };
            };
        };
    
    this.setAllSortFieldState ();
    
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleFormatCheckbox = function ( inCheckboxObject )
{
    var id = parseInt ( inCheckboxObject.value );
    var id_abs = Math.abs ( id );
    var checked = inCheckboxObject.checked;
    var formatsArray = Array();
    var yes_container_id = this.getScopedID ( 'bmlt_semantic_form_formats_fieldset_div' );
    var not_container_id = this.getScopedID ( 'bmlt_semantic_form_not_formats_fieldset_div' );
    var yes_id = this.getScopedID ( yes_container_id + '_checkbox_' + id_abs );
    var no_id = this.getScopedID ( not_container_id + '_checkbox_' + id_abs );
    
    if ( this.state.formats )
        {
        if ( inCheckboxObject.checked )
            {
            if ( inCheckboxObject.id == yes_id )
                {
                document.getElementById ( no_id ).checked = false;
                }
            else
                {
                if ( inCheckboxObject.id == no_id )
                    {
                    document.getElementById ( yes_id ).checked = false;
                    };
                };
            };
        
        var formatsArrayTemp = this.state.formats.split(',');

        this.state.formats = null;
        
        for ( var i = 0; i < formatsArrayTemp.length; i++ )
            {
            if ( parseInt ( formatsArrayTemp[i] ) && (Math.abs ( parseInt ( formatsArrayTemp[i] ) ) != id_abs) )
                {
                formatsArray.push ( parseInt ( formatsArrayTemp[i] ) );
                };
            };
        };

    if ( checked )
        {
        formatsArray.push ( parseInt ( id ) );
        };
    
    formatsArray = formatsArray.sort ( function ( a, b ) { return Math.abs ( a ) > Math.abs ( b ); } );
    
    this.state.formats = formatsArray.join ( ',' );
    
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleWeekdayCheckbox = function ( inCheckboxObject )
{
    this.scanWeekdays ( inCheckboxObject.checked ? parseInt ( inCheckboxObject.value ) : 0 );
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.scanWeekdays = function ( value )
{
    this.state.weekdays = null;
    var abs = Math.abs ( parseInt ( value ) );
    
    if ( value > 0 )
        {
        this.getScopedElement ( 'bmlt_semantic_form_not_weekday_checkbox_' + abs ).checked = false;
        }
    else
        {
        if ( value < 0 )
            {
            this.getScopedElement ( 'bmlt_semantic_form_weekday_checkbox_' + abs ).checked = false;
            };
        };
    
    for ( var i = 1; i < 8; i++ )
        {
        if ( this.getScopedElement ( 'bmlt_semantic_form_weekday_checkbox_' + i ).checked )
            {
            
            if ( this.state.weekdays )
                {
                this.state.weekdays += ',' + i.toString();
                }
            else
                {
                this.state.weekdays = i.toString();
                };
            };
        
        if ( this.getScopedElement ( 'bmlt_semantic_form_not_weekday_checkbox_' + i ).checked )
            {
            
            if ( this.state.weekdays )
                {
                this.state.weekdays += ',' + (-i).toString();
                }
            else
                {
                this.state.weekdays = (-i).toString();
                };
            };
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.clearWeekdays = function ( )
{
    this.state.weekdays = null;

    for ( var i = 1; i < 8; i++ )
        {
        this.getScopedElement ( 'bmlt_semantic_form_weekday_checkbox_' + i ).checked = false;
        this.getScopedElement ( 'bmlt_semantic_form_not_weekday_checkbox_' + i ).checked = false;
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.clearSorts = function ( )
{
    if ( this.field_keys )
        {
        for ( var i = 0; i < this.field_keys.length; i++ )
            {
            var selectObject = this.getScopedElement ( this.getSortItemID ( this.field_keys[i].key.toString() ) );
            
            if ( selectObject )
                {
                selectObject.selectedIndex = 0;
                selectObject.className = selectObject.defaultClass;
                };
            };
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleUsedFormatsChange = function ( inElement
                                                            )
{
    var getUsedCheckbox = inElement.formHandler.getScopedElement ( 'bmlt_semantic_form_used_formats_checkbox' );
    var getOnlyUsedCheckbox = inElement.formHandler.getScopedElement ( 'bmlt_semantic_form_just_used_formats_checkbox' );
    
    if ( getUsedCheckbox && getOnlyUsedCheckbox )
        {
        if ( !getUsedCheckbox.checked )
            {
            getOnlyUsedCheckbox.checked = false;
            getOnlyUsedCheckbox.disable();
            }
        else
            {
            getOnlyUsedCheckbox.enable();
            };
        };
    
    inElement.formHandler.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.clearTextSearchItems = function ( )
{
    this.state.searchText = null;
    this.state.searchTextModifier = null;
    this.state.searchTextRadius = null;
    
    var bmlt_semantic_form_text_search_text = this.getScopedElement ( 'bmlt_semantic_form_text_search_text' );
    var bmlt_semantic_form_text_search_select = this.getScopedElement ( 'bmlt_semantic_form_text_search_select' );
    var bmlt_semantic_form_text_search_text_radius = this.getScopedElement ( 'bmlt_semantic_form_text_search_text_radius' );
    
    bmlt_semantic_form_text_search_text.value = bmlt_semantic_form_text_search_text.defaultValue;
    bmlt_semantic_form_text_search_text.className = bmlt_semantic_form_text_search_text.defaultClass.toString() + ' bmlt_semantic_form_disabled_text';
    bmlt_semantic_form_text_search_select.selectedIndex = 0;
    bmlt_semantic_form_text_search_text_radius.value = '';
    
    this.handleTextSearchText();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleNAWSDumpSelectChange = function ( inSelect )
{
    this.state.sb_id = parseInt ( inSelect.value );
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleChangesSBSelectChange = function ( inSelect )
{
    this.state.change_sb_id = parseInt ( inSelect.value );
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleValueText = function (inTextItem
                                                    )
{
    this.state.meeting_key_value = (inTextItem.value != inTextItem.defaultValue) ? inTextItem.value : null;
    this.getScopedElement ( 'bmlt_semantic_form_value_select' ).selectedIndex = 0;
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleChangeText = function ()
{
    var start_date = this.getScopedElement ( 'bmlt_semantic_form_changes_from_text' );
    var end_date = this.getScopedElement ( 'bmlt_semantic_form_changes_to_text' );
    var meeting_id = this.getScopedElement ( 'bmlt_semantic_form_changes_id_text' );
    
    this.state.change_start = (start_date.value && (start_date.value != start_date.defaultValue)) ? start_date.value : null;
    this.state.change_end = (end_date.value && (end_date.value != end_date.defaultValue)) ? end_date.value : null;
    this.state.change_id = (meeting_id.value && (meeting_id.value != meeting_id.defaultValue)) ? meeting_id.value : null;

    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleStartText = function ( inTextItem
                                                    )
{
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleDurationText = function ( inTextItem
                                                        )
{
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMapCheckboxChange = function ( inCheckbox
                                                            )
{
    var mapSection = this.getScopedElement ( 'bmlt_semantic_form_map_wrapper_div' );
    
    if ( inCheckbox.checked )
        {
        mapSection.show ( );
        }
    else
        {
        mapSection.hide ( );
        };

    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleFormatsLangSelectChange = function ( inSelect )
{
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleSpecificFieldChange = function ( inCheckbox )
{
    var key = inCheckbox.value;
    var oldFields = Array();
    
    for ( var i = 0; i < this.state.fields.length; i++ )
        {
        var oldKey = this.state.fields[i];
        if ( oldKey != key )
            {
            oldFields.push ( oldKey );
            };
        };
    
    if ( inCheckbox.checked )
        {
        oldFields.push ( key );
        };
    
    this.state.fields = oldFields;
    
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleTextSearchText = function ()
{
    var bmlt_semantic_form_text_search_text = this.getScopedElement ( 'bmlt_semantic_form_text_search_text' );
    var bmlt_semantic_form_text_search_select = this.getScopedElement ( 'bmlt_semantic_form_text_search_select' );
    var bmlt_semantic_form_text_search_text_radius = this.getScopedElement ( 'bmlt_semantic_form_text_search_text_radius' );
    
    this.state.searchText = null;
    this.state.searchTextModifier = null;
    this.state.searchTextRadius = null;

    this.getScopedElement ( 'text_search_radius_input_div' ).hide();
    bmlt_semantic_form_text_search_select.disable();
    
    if ( bmlt_semantic_form_text_search_text.value && (bmlt_semantic_form_text_search_text.value != bmlt_semantic_form_text_search_text.defaultValue) )
        {
        bmlt_semantic_form_text_search_select.enable();
        this.state.searchText = bmlt_semantic_form_text_search_text.value;
        this.state.searchTextModifier = bmlt_semantic_form_text_search_select.value;
        if ( bmlt_semantic_form_text_search_select.value == 'StringSearchIsAnAddress=1' )
            {
            this.getScopedElement ( 'text_search_radius_input_div' ).show();
            var radius = parseFloat ( bmlt_semantic_form_text_search_text_radius.value );
            
            if ( radius < 0 )
                {
                radius = parseInt ( radius );
                if ( parseFloat ( radius ) != parseFloat ( bmlt_semantic_form_text_search_text_radius.value ) )
                    {
                    bmlt_semantic_form_text_search_text_radius.value = radius;
                    };
                };
            
            this.state.searchTextRadius = radius;
            };
        }
    else
        {
        bmlt_semantic_form_text_search_text_radius.value = '';
        };
    
    this.refreshURI();
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
        }
    else
        {
        if ( this.version < 2006015 )
            {
            this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_fieldkey_option' ).disable();
            this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_fieldval_option' ).disable();
            };
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
    
    if ( textItem )
        {
        textItem.className = textItem.defaultClass + ' bmlt_semantic_form_disabled_text';
    
        var oldOnChange = textItem.onchange;
    
        textItem.onchange = function() { this.formHandler.handleTextInput ( this, true ); };
        textItem.onkeyup = function() { this.formHandler.handleTextInput ( this, true ); };
        textItem.onblur = function() { this.formHandler.handleTextInput ( this, false ); };
        textItem.onfocus = function() { this.formHandler.handleTextInput ( this, true ); };
    
        if ( oldOnChange )
            {
            textItem.additionalHandler = oldOnChange;
            };
        };
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
    
    if ( item )
        {
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
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.getSortItemID = function ( inKey, inIndex )
{
    var baseID = 'bmlt_semantic_form_field_sort_select_' + inKey.toString();
    
    if ( null != inIndex )
        {
        baseID += '_' + parseInt ( inIndex ).toString();
        };

    return baseID;
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setSortFieldFunctions = function ( inKey )
{
    this.setBasicFunctions ( this.getSortItemID ( inKey, null ) );
    
    for ( var i = 0; i <= this.field_keys.length; i++ )
        {
        var sortItemOptionID = this.getSortItemID ( inKey, i );
        this.setBasicFunctions ( sortItemOptionID );
        var sortItem = this.getScopedElement ( sortItemOptionID );
        if ( sortItem )
            {
            sortItem.fieldKey = inKey;
            };
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setAllSortFieldFunctions = function ( )
{
    for ( var i = 0; i < this.field_keys.length; i++ )
        {
        this.setSortFieldFunctions ( this.field_keys[i].key.toString() );
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setSortFieldState = function ( inKey, inMaxNum )
{
    inMaxNum++;
    for ( var i = 1; i <= this.field_keys.length; i++ )
        {
        var sortItemOptionID = this.getSortItemID ( inKey, i );
        var optionElement = this.getScopedElement ( sortItemOptionID );
        
        if ( optionElement )
            {
            var sortItemSelect = optionElement.parentNode;

            if ( sortItemSelect )
                {
                if ( (sortItemSelect.selectedIndex == 0) && (optionElement.value == inMaxNum) )
                    {
                    optionElement.enable();
                    }
                else
                    {
                    optionElement.disable();
                    };
                };
            };
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setAllSortFieldState = function ( )
{
    var maxNum = 0;
    
    if ( this.state.sorts && this.state.sorts.length )
        {
        maxNum = this.state.sorts[this.state.sorts.length - 1].order;
    
        for ( var i = 0; i < this.field_keys.length; i++ )
            {
            var key = this.field_keys[i].key.toString();
            var selectID = this.getSortItemID ( key );
            var selectObject = this.getScopedElement ( selectID );
            selectObject.className = selectObject.defaultClass;
            for ( var c = 0; c < this.state.sorts.length; c++ )
                {
                var sortObject = this.state.sorts[c];
                
                if ( sortObject.key == key )
                    {
                    selectObject.selectedIndex = sortObject.order;
                    if ( sortObject.order > 0 )
                        {
                        selectObject.className = selectObject.defaultClass + ' sortSelectHighlight';
                        };
                    };
                };
            };
        };
    
    for ( var i = 0; i < this.field_keys.length; i++ )
        {
        this.setSortFieldState ( this.field_keys[i].key.toString(), maxNum );
        };
};

/*******************************************************************************************/
/**
    \brief Sets up the map display for the instance.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.setUpMap = function ( )
{
    var mapDiv = this.getScopedElement ( 'bmlt_semantic_form_map_div' );
    this.mapObject = null;
    mapDiv.innerHTML = '';
    
    var switcher_select = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' );
    
    if ( switcher_select.value == 'GetSearchResults' )
        {
        var position = new google.maps.LatLng ( this.current_lat, this.current_lng );
        var myOptions = {
                        'center': position,
                        'zoom': this.current_zoom,
                        'mapTypeId': google.maps.MapTypeId.ROADMAP,
                        'mapTypeControlOptions': { 'style': google.maps.MapTypeControlStyle.DROPDOWN_MENU },
                        'zoomControl': true,
                        'mapTypeControl': true,
                        'disableDoubleClickZoom' : true,
                        'draggableCursor': "crosshair",
                        'scaleControl' : true,
                        'cursor':  'default',
                        'scrollwheel': false
                        };

        myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.LARGE };

        this.mapObject = new google.maps.Map ( mapDiv, myOptions );
    
        if ( this.mapObject )
            {
            this.mapObject.map_marker = new google.maps.Marker (
                                                                {
                                                                'position':     position,
                                                                'map':		    this.mapObject,
                                                                'clickable':	false,
                                                                'draggable':    true
                                                                } );
            var theContext = this;
            this.mapObject.map_marker.formHandler = this;
            
            google.maps.event.addListener ( this.mapObject.map_marker, 'dragstart', function ( in_event ) { this.formHandler.hideRadiusCircle(); } );
            google.maps.event.addListener ( this.mapObject.map_marker, 'dragend', function ( in_event ) { BMLTSemantic.prototype.mapDragEnd ( in_event, theContext ); } );
            google.maps.event.addListener ( this.mapObject, 'click', function ( in_event ) { BMLTSemantic.prototype.mapClicked ( in_event, theContext ); } );
            google.maps.event.addListener ( this.mapObject, 'zoom_changed', function ( in_event ) { BMLTSemantic.prototype.mapZoomChanged ( in_event, theContext ); } );
            };
            
        var longText = this.getScopedElement ( 'bmlt_semantic_form_map_search_longitude_text' );
        var latText = this.getScopedElement ( 'bmlt_semantic_form_map_search_latitude_text' );
        
        longText.value = this.current_lng.toString();
        latText.value = this.current_lat.toString();
        };
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMapSearchText = function ()
{
    var bmlt_semantic_form_map_search_text_radius = this.getScopedElement ( 'bmlt_semantic_form_map_search_text_radius' );
    
    this.state.searchMapRadius = null;
    
    if ( bmlt_semantic_form_map_search_text_radius.value && (bmlt_semantic_form_map_search_text_radius.value != bmlt_semantic_form_map_search_text_radius.defaultValue) )
        {
        bmlt_semantic_form_map_search_text_radius.enable();
        var radius = parseFloat ( bmlt_semantic_form_map_search_text_radius.value );
        
        if ( radius < 0 )
            {
            radius = parseInt ( radius );
            if ( parseFloat ( radius ) != parseFloat ( bmlt_semantic_form_map_search_text_radius.value ) )
                {
                bmlt_semantic_form_map_search_text_radius.value = radius;
                };
            };
        
        this.state.searchMapRadius = radius;
        }
    else
        {
        bmlt_semantic_form_map_search_text_radius.value = '';
        };
    
    this.refreshURI();
};
                                                            
/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMapLongLatChange = function ( inTextItem
                                                            )
{
    var longText = this.getScopedElement ( 'bmlt_semantic_form_map_search_longitude_text' );
    var latText = this.getScopedElement ( 'bmlt_semantic_form_map_search_latitude_text' );
    var position = new google.maps.LatLng ( parseFloat ( latText.value ), parseFloat ( longText.value ) );

	this.mapObject.panTo ( position );
	this.mapObject.map_marker.setPosition ( position );
	this.current_lng = parseFloat ( parseFloat ( longText.value ) );
	this.current_lat = parseFloat ( parseFloat ( latText.value ) );
    
    this.refreshURI();
};
                                                            
/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.handleMapRadiusUnitsChange = function ( inSelect
                                                                )
{
    this.refreshURI();
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.hideRadiusCircle = function()
{
    if ( this.mapObject && this.mapObject.radiusCircle )
        {
        this.mapObject.radiusCircle.setMap ( null );
        this.mapObject.radiusCircle = null;
        };
};

/*******************************************************************************************/
/**
    \brief
*/
/*******************************************************************************************/
BMLTSemantic.prototype.createRadiusCircle = function()
{
    this.hideRadiusCircle();
    
    var radius = this.getScopedElement ( 'bmlt_semantic_form_map_search_text_radius' ).value;
    
    if ( radius > 0 )
        {
        radius *= ((this.getScopedElement ( 'bmlt_semantic_form_map_search_text_radius_units' ).value == 'geo_width') ? 1.60934 : 1.0) * 1000;
        
        var circleOptions = {
            strokeOpacity: 0,
            fillColor: '#000000',
            fillOpacity: 0.25,
            map: this.mapObject,
            clickable: false,
            center: new google.maps.LatLng ( parseFloat ( this.current_lat ), parseFloat ( this.current_lng ) ),
            radius: radius
            };
    
        // Add the circle for this city to the map.
        this.mapObject.radiusCircle = new google.maps.Circle ( circleOptions );
        };
};

/*******************************************************************************************/
/**
    \brief Reacts to a click in the map.
    
    \param inEvent The click event
    \param inContext The object that triggered the event.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.mapClicked = function (  inEvent,
                                                inContext
                                                )
{
	inContext.mapObject.panTo ( inEvent.latLng );
	inContext.mapObject.map_marker.setPosition ( inEvent.latLng );
	inContext.current_lng = parseFloat ( inEvent.latLng.lng() );
	inContext.current_lat = parseFloat ( inEvent.latLng.lat() );
    var longText = inContext.getScopedElement ( 'bmlt_semantic_form_map_search_longitude_text' );
    var latText = inContext.getScopedElement ( 'bmlt_semantic_form_map_search_latitude_text' );
    longText.value = inContext.current_lng.toString();
    latText.value = inContext.current_lat.toString();
    
	inContext.refreshURI();
};

/*******************************************************************************************/
/**
    \brief Reacts to a drag in the map ending.
    
    \param inEvent The drag event
    \param inContext The object that triggered the event.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.mapDragEnd = function (  inEvent,
                                                inContext
                                                )
{
	inContext.current_lng = parseFloat ( inEvent.latLng.lng() );
	inContext.current_lat = parseFloat ( inEvent.latLng.lat() );
	inContext.mapObject.panTo ( inEvent.latLng );
    var longText = inContext.getScopedElement ( 'bmlt_semantic_form_map_search_longitude_text' );
    var latText = inContext.getScopedElement ( 'bmlt_semantic_form_map_search_latitude_text' );
    longText.value = inEvent.latLng.lng().toString();
    latText.value = inEvent.latLng.lat().toString();
    
	inContext.refreshURI();
};

/*******************************************************************************************/
/**
    \brief Reacts to the map zoom changing.
    
    \param inEvent The drag event
    \param inContext The object that triggered the event.
*/
/*******************************************************************************************/
BMLTSemantic.prototype.mapZoomChanged = function (  inEvent,
                                                    inContext
                                                )
{
	inContext.current_zoom = inContext.mapObject.getZoom();
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
    this.setBasicFunctions ( 'bmlt_switcher_naws_dump_div' );
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
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_schema_option' );
    this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_server_langs_option' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_download_line' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_shortcode_line' );
    this.setBasicFunctions ( 'bmlt_semantic_form_main_fields_fieldset_contents_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_meeting_fields_fieldset_contents_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_value_select' );
    this.setBasicFunctions ( 'bmlt_switcher_field_value_div_no_options_blurb' );
    this.setBasicFunctions ( 'bmlt_switcher_field_value_div_formats' );
    this.setBasicFunctions ( 'bmlt_switcher_field_value_div_no_selected_formats_blurb' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_url_Invalid_span' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_shortcode_Invalid_span' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_url_active_span' );
    this.setBasicFunctions ( 'bmlt_semantic_info_div_shortcode_active_span' );
    this.setBasicFunctions ( 'bmlt_switcher_changes_sb_select' );
    this.setBasicFunctions ( 'bmlt_semantic_form_text_search_select' );
    this.setBasicFunctions ( 'text_search_radius_input_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_map_wrapper_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_map_search_text_radius_units' );
    this.setBasicFunctions ( 'bmlt_semantic_form_map_search_longitude_text' );
    this.setBasicFunctions ( 'bmlt_semantic_form_map_search_latitude_text' );
    this.setBasicFunctions ( 'bmlt_semantic_form_schema_select_fieldset' );
    this.setBasicFunctions ( 'bmlt_semantic_form_used_formats_checkbox' );
    this.setBasicFunctions ( 'bmlt_semantic_form_just_used_formats_checkbox' );
    this.setBasicFunctions ( 'bmlt_semantic_form_used_formats_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_just_used_formats_checkbox_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_formats_fieldset_contents_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_weekday_header_checkbox_div' );
    this.setBasicFunctions ( 'bmlt_semantic_form_start_time_min_text' );
    this.setBasicFunctions ( 'bmlt_semantic_form_start_time_max_text' );
    this.setBasicFunctions ( 'bmlt_semantic_form_duration_min_text' );
    this.setBasicFunctions ( 'bmlt_semantic_form_duration_max_text' );
    this.setBasicFunctions ( 'bmlt_semantic_form_sb_fieldset' );
    this.setBasicFunctions ( 'bmlt_semantic_form_sb_not_fieldset' );
    
    if ( this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_server_info_option' ) )
        {
        this.setBasicFunctions ( 'bmlt_semantic_form_switcher_type_select_server_info_option' );
        };

    for ( var i = 1; i < 8; i++ )
        {
        this.setBasicFunctions ( 'bmlt_semantic_form_weekday_checkbox_' + i );
        };
    
    this.setTextHandlers ( 'bmlt_semantic_form_changes_from_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_changes_to_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_changes_id_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_value_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_text_search_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_text_search_text_radius' );
    this.setTextHandlers ( 'bmlt_semantic_form_map_search_text_radius' );
    this.setTextHandlers ( 'bmlt_semantic_form_map_search_longitude_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_map_search_latitude_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_start_time_min_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_start_time_max_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_duration_min_text' );
    this.setTextHandlers ( 'bmlt_semantic_form_duration_max_text' );
    
    this.getScopedElement ( 'bmlt_semantic_form_start_time_min_text' ).additionalHandler = function () { this.formHandler.handleStartText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_start_time_max_text' ).additionalHandler = function () { this.formHandler.handleStartText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_duration_min_text' ).additionalHandler = function () { this.formHandler.handleDurationText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_duration_max_text' ).additionalHandler = function () { this.formHandler.handleDurationText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_changes_from_text' ).additionalHandler = function () { this.formHandler.handleChangeText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_changes_to_text' ).additionalHandler = function () { this.formHandler.handleChangeText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_changes_id_text' ).additionalHandler = function () { this.formHandler.handleChangeText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_value_text' ).additionalHandler = function () { this.formHandler.handleValueText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_value_text' ).additionalHandler = function () { this.formHandler.handleValueText ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_text_search_text' ).additionalHandler = function () { this.formHandler.handleTextSearchText() };
    this.getScopedElement ( 'bmlt_semantic_form_text_search_text_radius' ).additionalHandler = function () { this.formHandler.handleTextSearchText() };
    this.getScopedElement ( 'bmlt_semantic_form_map_search_text_radius' ).additionalHandler = function () { this.formHandler.handleMapSearchText() };
    this.getScopedElement ( 'bmlt_semantic_form_map_search_longitude_text' ).additionalHandler = function () { this.formHandler.handleMapLongLatChange ( this ) };
    this.getScopedElement ( 'bmlt_semantic_form_map_search_latitude_text' ).additionalHandler = function () { this.formHandler.handleMapLongLatChange ( this ) };
    
    this.getScopedElement ( 'bmlt_semantic_form_map_wrapper_div' ).hide = function() { this.style.display = 'none'; this.formHandler.mapObject = null; };
    this.getScopedElement ( 'bmlt_semantic_form_map_wrapper_div' ).show = function() { this.style.display = this.oldDisplay; this.formHandler.setUpMap(); };
    
    this.getScopedElement ( 'bmlt_semantic_form_map_search_text_radius' ).value = this.state.searchMapRadius;
    
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
    var bmlt_switcher_div_no_options_blurb = this.getScopedElement ( 'bmlt_switcher_div_no_options_blurb' );
    var bmlt_switcher_naws_dump_div = this.getScopedElement ( 'bmlt_switcher_naws_dump_div' );
    var bmlt_semantic_info_div_download_line = this.getScopedElement ( 'bmlt_semantic_info_div_download_line' );
    var bmlt_semantic_info_div_shortcode_line = this.getScopedElement ( 'bmlt_semantic_info_div_shortcode_line' );
    var bmlt_semantic_form_main_fields_fieldset = this.getScopedElement ( 'bmlt_semantic_form_main_fields_fieldset' );
    var bmlt_semantic_form_field_main_select = this.getScopedElement ( 'bmlt_semantic_form_field_main_select' );
    var text_search_radius_input_div = this.getScopedElement ( 'text_search_radius_input_div' );
    var map_search_radius_input_div = this.getScopedElement ( 'map_search_radius_input_div' );
    var bmlt_semantic_form_text_search_select = this.getScopedElement ( 'bmlt_semantic_form_text_search_select' );
    var bmlt_switcher_field_value_div_formats = this.getScopedElement ( 'bmlt_switcher_field_value_div_formats' );
    var bmlt_switcher_field_value_div_no_selected_formats_blurb = this.getScopedElement ( 'bmlt_switcher_field_value_div_no_selected_formats_blurb' );
    var bmlt_semantic_form_meeting_fields_fieldset_contents_div = this.getScopedElement ( 'bmlt_semantic_form_meeting_fields_fieldset_contents_div' );
    var bmlt_semantic_form_map_wrapper_div = this.getScopedElement ( 'bmlt_semantic_form_map_wrapper_div' );
    var bmlt_semantic_form_map_checkbox = this.getScopedElement ( 'bmlt_semantic_form_map_checkbox' );
    var bmlt_semantic_form_schema_select_fieldset = this.getScopedElement ( 'bmlt_semantic_form_schema_select_fieldset' );
    var bmlt_semantic_form_schema_select = this.getScopedElement ( 'bmlt_semantic_form_schema_select' );
    var switcher_type_select_schema_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_schema_option' );
    var switcher_type_select_server_langs_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_server_langs_option' );
    var switcher_type_select_server_info_option = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select_server_info_option' );
    var getUsedCheckbox = this.getScopedElement ( 'bmlt_semantic_form_used_formats_checkbox' );
    var getOnlyUsedCheckbox = this.getScopedElement ( 'bmlt_semantic_form_just_used_formats_checkbox' );
    var bmlt_semantic_form_used_formats_div = this.getScopedElement ( 'bmlt_semantic_form_used_formats_div' );
    var bmlt_semantic_form_just_used_formats_checkbox_div = this.getScopedElement ( 'bmlt_semantic_form_just_used_formats_checkbox_div' );
    var bmlt_semantic_form_formats_fieldset_contents_div = this.getScopedElement ( 'bmlt_semantic_form_formats_fieldset_contents_div' );
    var bmlt_semantic_form_weekday_header_checkbox_div = this.getScopedElement ( 'bmlt_semantic_form_weekday_header_checkbox_div' );

    switcher_type_select_formats_option.enable();
    switcher_type_select_sb_option.enable();
    switcher_type_select_changes_option.enable();
    switcher_type_select_schema_option.disable();
    switcher_type_select_server_langs_option.disable();
    
    if ( switcher_type_select_server_info_option )
        {
        switcher_type_select_server_info_option.disable();
        };
    
    bmlt_semantic_form_map_checkbox.checked = false;
    bmlt_semantic_form_map_wrapper_div.hide();
    
    if ( this.version >= 2006015 )
        {
        switcher_type_select_fieldkey_option.enable();
        switcher_type_select_fieldval_option.enable();
        };
    
    switcher_type_select_naws_option.enable();
    
    if ( (inItem == switcher_select) && (switcher_select.value == 'GetFieldValues') )
        {
        bmlt_semantic_form_field_main_select.selectedIndex = 0;
        bmlt_semantic_form_field_main_select.onchange(bmlt_semantic_form_field_main_select);
        };
    
    bmlt_semantic_info_div_download_line.hide();
    bmlt_semantic_info_div_shortcode_line.hide();

    if ( main_fieldset_select.value == 'DOWNLOAD' )
        {
        bmlt_semantic_info_div_download_line.show();
        
        if ( main_fieldset_direct_uri_div.style.display == 'none' )
            {
            main_fieldset_direct_uri_div.show();
            response_type_select.selectedIndex = 0;
            bmlt_switcher_field_value_div_formats.innerHTML = '';
            bmlt_switcher_field_value_div_no_selected_formats_blurb.hide();
            bmlt_semantic_form_meeting_fields_fieldset_contents_div.hide();
            };
        }
    else
        {
        if ( (switcher_select.value != 'GetSearchResults') && (switcher_select.value != 'GetFormats') )
            {
            switcher_select.selectedIndex = 0;
            };
        
        bmlt_semantic_info_div_shortcode_line.show();
        main_fieldset_direct_uri_div.hide();
        };

    if ( (switcher_select.value == 'GetLangs') && ((response_type_select.value != 'xml') && !((response_type_select.value == 'json') && (this.version >= 2007005))) )
        {
        switcher_select.selectedIndex = 0;
        };
    
    if ( (inItem != response_type_select) && (switcher_select.value == 'GetNAWSDump') && ((response_type_select.value != 'csv') || (main_fieldset_select.value != 'DOWNLOAD')) )
        {
        response_type_select.selectedIndex = 0;
        bmlt_switcher_field_value_div_formats.innerHTML = '';
        bmlt_switcher_field_value_div_no_selected_formats_blurb.hide();
        bmlt_semantic_form_meeting_fields_fieldset_contents_div.hide();
        };

    if ( (inItem != switcher_select) && (switcher_select.value == 'GetNAWSDump') && ((response_type_select.value != 'csv') || (main_fieldset_select.value != 'DOWNLOAD')) )
        {
        switcher_select.selectedIndex = 0;
        }
    else
        {
        if ( (inItem != switcher_select) && (switcher_select.value != 'GetSearchResults') && ((response_type_select.value == 'kml') || (response_type_select.value == 'gpx') || (response_type_select.value == 'poi')) )
            {
            switcher_select.selectedIndex = 0;
            }
        else
            {
            if ( (inItem != switcher_select) && (switcher_select.value != 'GetSearchResults') && (switcher_select.value != 'GetFormats') && (response_type_select.value == 'simple') )
                {
                switcher_select.selectedIndex = 0;
                };
            };
        };

    bmlt_switcher_naws_dump_div.hide();
    bmlt_switcher_div_no_options_blurb.hide();
    bmlt_semantic_form_changes_div.hide();
    bmlt_semantic_form_main_fields_fieldset.hide();
    bmlt_semantic_form_meeting_search_div.hide();
    text_search_radius_input_div.hide();
    bmlt_semantic_form_schema_select_fieldset.hide();
    bmlt_semantic_form_used_formats_div.hide();
    bmlt_semantic_form_just_used_formats_checkbox_div.hide();
    bmlt_semantic_form_formats_fieldset_contents_div.hide();
    
    if ( (switcher_select.value == 'GetSearchResults') && (main_fieldset_select.value != 'DOWNLOAD') )
        {
        bmlt_semantic_form_weekday_header_checkbox_div.show();
        }
    else
        {
        bmlt_semantic_form_weekday_header_checkbox_div.hide();
        };
        
    if ( switcher_select.value == 'GetSearchResults' )
        {
        bmlt_semantic_form_meeting_search_div.show();
        if ( bmlt_semantic_form_text_search_select.value == 'StringSearchIsAnAddress=1' )
            {
            text_search_radius_input_div.show();
            };
        
        if ( (response_type_select.value == 'xml') || (response_type_select.value == 'json') )
            {
            bmlt_semantic_form_used_formats_div.show();
            bmlt_semantic_form_just_used_formats_checkbox_div.show();
            if ( getUsedCheckbox && getOnlyUsedCheckbox )
                {
                if ( !getUsedCheckbox.checked )
                    {
                    getOnlyUsedCheckbox.checked = false;
                    getOnlyUsedCheckbox.disable();
                    }
                else
                    {
                    getOnlyUsedCheckbox.enable();
                    };
                };
            }
        else
            {
            if ( (response_type_select.value == 'simple-block') || (response_type_select.value == 'simple') )
                {
                bmlt_semantic_form_used_formats_div.show();
                };
            };
        }
    else
        {
        getUsedCheckbox.checked = false;
        getOnlyUsedCheckbox.checked = false;
        getOnlyUsedCheckbox.disable();
        
        if ( switcher_select.value == 'GetChanges' )
            {
            bmlt_semantic_form_changes_div.show();
            }
        else
            {
            if ( switcher_select.value == 'GetFieldValues' )
                {
                bmlt_semantic_form_main_fields_fieldset.show();
                this.fetchFieldKeys();
                }
            else
                {
                if ( switcher_select.value == 'GetNAWSDump' )
                    {
                    bmlt_switcher_naws_dump_div.show();
                    this.fetchServiceBodies();
                    }
                else
                    {
                    if ( switcher_select.value == 'GetFormats' )
                        {
                        bmlt_semantic_form_formats_fieldset_contents_div.show();
                        }
                    else
                        {
                        if ( switcher_select.value == 'XMLSchema' )
                            {
                            bmlt_semantic_form_schema_select_fieldset.show();
                            }
                        else
                            {
                            bmlt_switcher_div_no_options_blurb.show();
                            };
                        };
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
        
        if ( switcher_type_select_server_info_option && (main_fieldset_select.value == 'DOWNLOAD') && ((response_type_select.value == 'csv') || (response_type_select.value == 'xml') || (response_type_select.value == 'json')) )
            {
            switcher_type_select_server_info_option.enable();
            };
        
        if ( (main_fieldset_select.value == 'DOWNLOAD') && ((response_type_select.value == 'kml') || (response_type_select.value == 'gpx') || (response_type_select.value == 'poi')) )
            {
            switcher_type_select_formats_option.disable();
            switcher_type_select_sb_option.disable();
            switcher_type_select_changes_option.disable();
            switcher_type_select_fieldkey_option.disable();
            switcher_type_select_fieldval_option.disable();
            switcher_type_select_naws_option.disable();
            }
        else
            {
            if ( (main_fieldset_select.value == 'DOWNLOAD') && ((response_type_select.value == 'simple') || (response_type_select.value == 'simple-block')) )
                {
                switcher_type_select_sb_option.disable();
                switcher_type_select_changes_option.disable();
                switcher_type_select_fieldkey_option.disable();
                switcher_type_select_fieldval_option.disable();
                switcher_type_select_naws_option.disable();
                };
            };
        }
    else
        {
        if ( (switcher_select.value != 'GetSearchResults') && (switcher_select.value != 'GetFormats') )
            {
            switcher_select.selectedIndex = 0;
            bmlt_semantic_form_meeting_search_div.show();
            };
        
        switcher_type_select_sb_option.disable();
        switcher_type_select_changes_option.disable();
        switcher_type_select_fieldkey_option.disable();
        switcher_type_select_fieldval_option.disable();
        switcher_type_select_naws_option.disable();
        };
        
    if ( (main_fieldset_select.value == 'DOWNLOAD') && (response_type_select.value == 'xml') )
        {
        switcher_type_select_schema_option.enable();
        switcher_type_select_server_langs_option.enable();
        }
    else
        {
        if ( (main_fieldset_select.value == 'DOWNLOAD') && ((response_type_select.value == 'json') && (this.version >= 2007005)) )
            {
            switcher_type_select_server_langs_option.enable();
            }
        else
            {
            if ( switcher_select.value == 'XMLSchema' )
                {
                switcher_select.selectedIndex = 0;
                bmlt_semantic_form_meeting_search_div.show();
                };
            };
        };
    
    this.refreshURI();
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
    this.fetchServerInfo();
};

/*******************************************************************************************/
/**
    \brief 
*/
/*******************************************************************************************/
BMLTSemantic.prototype.refreshURI = function ()
{
    var uri_invalid = this.getScopedElement ( 'bmlt_semantic_info_div_url_Invalid_span' );
    var shortcode_invalid = this.getScopedElement ( 'bmlt_semantic_info_div_shortcode_Invalid_span' );
    var uri_active = this.getScopedElement ( 'bmlt_semantic_info_div_url_active_span' );
    var shortcode_active = this.getScopedElement ( 'bmlt_semantic_info_div_shortcode_active_span' );
    var type = this.getScopedElement ( 'bmlt_semantic_form_response_type_select' ).value;
    var bmlt_semantic_form_text_search_select = this.getScopedElement ( 'bmlt_semantic_form_text_search_select' );
    var mainSelectElement = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' );
    var typeSelectElement = this.getScopedElement ( 'bmlt_semantic_form_field_main_select' );
    var blurbDiv = this.getScopedElement ( 'bmlt_switcher_field_value_div_no_options_blurb' );
    var formatsDiv = this.getScopedElement ( 'bmlt_switcher_field_value_div_formats' );
    var formatsBlurbDiv = this.getScopedElement ( 'bmlt_switcher_field_value_div_no_selected_formats_blurb' );
    var useMap = this.getScopedElement ( 'bmlt_semantic_form_map_checkbox' );
    var mapRadius = this.getScopedElement ( 'bmlt_semantic_form_map_search_text_radius' );
    var startMin = this.getScopedElement ( 'bmlt_semantic_form_start_time_min_text' );
    var startMax = this.getScopedElement ( 'bmlt_semantic_form_start_time_max_text' );
    var durationMin = this.getScopedElement ( 'bmlt_semantic_form_duration_min_text' );
    var durationMax = this.getScopedElement ( 'bmlt_semantic_form_duration_max_text' );
    
    this.state.startTimeMin = null;
    this.state.startTimeMax = null;
    this.state.durationMin = null;
    this.state.durationMax = null;
    
    if ( startMin && startMin.value )
        {
        var time = startMin.value.toString().split( ':' );
        time[0] = Math.abs ( parseInt ( time[0] ) );
        if ( time[0] > 23 )
            {
            time[0] = 23;
            };
            
        if ( time[1] )
            {
            time[1] = Math.abs ( parseInt ( time[1] ) );
            if ( time[1] > 59 )
                {
                time[1] = 59;
                };
            };
        
        if ( !time[1] )
            {
            time[1] = 0;
            };
        
        this.state.startTimeMin = time;
        };
    
    if ( startMax && startMax.value )
        {
        var time = startMax.value.toString().split( ':' );

        time[0] = Math.abs ( parseInt ( time[0] ) );
        if ( time[0] > 23 )
            {
            time[0] = 23;
            };
            
        if ( time[1] )
            {
            time[1] = Math.abs ( parseInt ( time[1] ) );
            if ( time[1] > 59 )
                {
                time[1] = 59;
                };
            };
        
        if ( !time[1] )
            {
            time[1] = 0;
            };
        
        this.state.startTimeMax = time;
        };
         
    if ( durationMin && durationMin.value )
        {
        var time = durationMin.value.toString().split( ':' );

        time[0] = Math.abs ( parseInt ( time[0] ) );
        if ( time[0] > 23 )
            {
            time[0] = 23;
            };
            
        if ( time[1] )
            {
            time[1] = Math.abs ( parseInt ( time[1] ) );
            if ( time[1] > 59 )
                {
                time[1] = 59;
                };
            };
        
        if ( !time[1] )
            {
            time[1] = 0;
            };
        
        this.state.durationMin = time;
        };
    
    if ( durationMax && durationMax.value )
        {
        var time = durationMax.value.toString().split( ':' );

        time[0] = Math.abs ( parseInt ( time[0] ) );
        if ( time[0] > 23 )
            {
            time[0] = 23;
            };
            
        if ( time[1] )
            {
            time[1] = Math.abs ( parseInt ( time[1] ) );
            if ( time[1] > 59 )
                {
                time[1] = 59;
                };
            };
        
        if ( !time[1] )
            {
            time[1] = 0;
            };
        
        this.state.durationMax = time;
        };
    
    if ( useMap && useMap.checked )
        {
        this.state.searchMapRadius = parseFloat ( mapRadius.value );
        this.state.searchLongitude = parseFloat ( this.current_lng );
        this.state.searchLatitude = parseFloat ( this.current_lat );
        this.createRadiusCircle();
        }
    else
        {
        this.state.searchMapRadius = 0;
        this.state.searchLongitude = 0;
        this.state.searchLatitude = 0;
        };
    
    if ( (mainSelectElement.value != 'GetFieldValues') || (typeSelectElement.value != 'formats') )
        {
        blurbDiv.show();
        formatsDiv.hide();
        formatsBlurbDiv.hide();
        }
    else
        {
        blurbDiv.hide();
        formatsDiv.show();
        formatsBlurbDiv.show();
        };

    this.state.switcher = this.getScopedElement ( 'bmlt_semantic_form_switcher_type_select' ).value;
    
    if ( (this.state.switcher != 'XMLSchema') && (this.state.switcher != 'GetLangs') )
        {
        var compiled_arguments = this.state.compile();
        };
    
    if ( (this.state.switcher == 'GetLangs') && (type == 'xml') )
        {
        uri = this.state.root_server_uri + '/client_interface/xml/GetLangs.php';
        var url_string = '<a target="_blank" href="' + uri + '">' + uri + '</a>';
        uri_active.innerHTML = url_string;
        uri_invalid.hide();
        uri_active.show();
        }
    else
        {
        if ( (this.state.switcher == 'GetLangs') && (type == 'json') )
            {
            uri = this.state.root_server_uri + '/client_interface/json/GetLangs.php';
            var url_string = '<a target="_blank" href="' + uri + '">' + uri + '</a>';
            uri_active.innerHTML = url_string;
            uri_invalid.hide();
            uri_active.show();
            }
        else
            {
            if ( (this.state.switcher == 'XMLSchema') || this.state.valid )
                {
                if ( this.getScopedElement ( 'bmlt_semantic_form_main_mode_select' ).value == 'DOWNLOAD' )
                    {
                    var extra_sauce = '';
                    if ( type == 'simple-block' )
                        {
                        type = 'simple';
                        extra_sauce = '&block_mode=1'
                        };
            
                    var uri = '';
            
                    if ( this.state.switcher == 'XMLSchema' )
                        {
                        var schemaSelect = this.getScopedElement ( 'bmlt_semantic_form_schema_select' );
                        if ( schemaSelect )
                            {
                            var schemaType = this.getScopedElement ( 'bmlt_semantic_form_schema_select' ).value;
                
                            uri = this.state.root_server_uri + '/client_interface/xsd/' + schemaType + '.php';
                            };
                        }
                    else
                        {
                        uri = this.state.root_server_uri + '/client_interface/' + type + '/?' + compiled_arguments + extra_sauce;
                        };
            
                    var url_string = '<a target="_blank" href="' + uri + '">' + uri + '</a>';
                    uri_active.innerHTML = url_string;
                    uri_invalid.hide();
                    uri_active.show();
                    }
                else
                    {
                    var shortcode_string = '[[BMLT_SIMPLE(' + compiled_arguments + ')]]';
                    shortcode_active.innerHTML = shortcode_string;
                    shortcode_invalid.hide();
                    shortcode_active.show();
                    };
                }
            else
                {
                uri_invalid.show();
                uri_active.hide();
                shortcode_invalid.show();
                shortcode_active.hide();
                };
            };
        };
};
