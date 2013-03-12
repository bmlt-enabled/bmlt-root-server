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
function BMLTInstaller()
{
    var m_installer_wrapper_object = null;
    
    // #mark - 
    // #mark Page Selection Handlers
    // #mark -
    
    /************************************************************************************//**
    *   \brief Shows Page 1 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage1 = function()
    {
        if ( this.installer_wrapper_object.className != 'page_1_wrapper' )
            {
            this.installer_wrapper_object.className = 'page_1_wrapper';
            };
    };
    
    /************************************************************************************//**
    *   \brief Shows Page 2 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage2 = function()
    {
        if ( this.installer_wrapper_object.className != 'page_2_wrapper' )
            {
            this.installer_wrapper_object.className = 'page_2_wrapper';
            };
    };
    
    /************************************************************************************//**
    *   \brief Shows Page 3 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage3 = function()
    {
        if ( this.installer_wrapper_object.className != 'page_3_wrapper' )
            {
            this.installer_wrapper_object.className = 'page_3_wrapper';
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
    
    // #mark - 
    // #mark Main Context
    // #mark -
    
    this.installer_wrapper_object = document.getElementById ( 'installer_wrapper' );
};