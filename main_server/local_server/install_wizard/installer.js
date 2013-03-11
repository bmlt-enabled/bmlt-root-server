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
    
    /************************************************************************************//**
    *   \brief Shows Page 1 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage1 = function()
    {
        this.installer_wrapper_object.className = 'page_1_wrapper';
    };
    
    /************************************************************************************//**
    *   \brief Shows Page 2 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage2 = function()
    {
        this.installer_wrapper_object.className = 'page_2_wrapper';
    };
    
    /************************************************************************************//**
    *   \brief Shows Page 3 of the installer wizard. It does this by setting the wrapper    *
    *          className to the first page, which uses CSS to hide the other two pages.     *
    ****************************************************************************************/
    this.selectPage3 = function()
    {
        this.installer_wrapper_object.className = 'page_3_wrapper';
    };
    
    this.installer_wrapper_object = document.getElementById ( 'installer_wrapper' );
};