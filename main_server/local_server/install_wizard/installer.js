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
            google.maps.event.addListener ( map_object, 'click', function(in_event) { alert ( 'CLICK' ) } );
            };
            
        return map_object;
    };
    
    // #mark - 
    // #mark Main Context
    // #mark -
    
    this.m_map_center = in_map_center;
    this.m_installer_wrapper_object = document.getElementById ( 'installer_wrapper' );
};