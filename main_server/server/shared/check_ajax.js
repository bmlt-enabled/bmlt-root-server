/*
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://magshare.org/bmlt

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
if ( SupportsAjax() )
	{
	if ( getURLParam('supports_ajax') != 'yes' )
		{
		href = window.location.href;
		
		href = href.replace ( /supports_ajax=([^\&]*)\&?/g, '' );
		href = href.replace ( /\&+/g, '\&' );
		
		var	ex = '?';
		if ( href.indexOf("?") > 0 )
			{
			ex = '&';
			
			if ( href.match(/\&$/) )
				{
				ex ='';
				}
			};
		
		var lang_enum = getURLParam ( 'lang_enum' );
		if ( lang_enum )
			{
			ex += 'lang_enum='+lang_enum+'&';
			};
		
		window.location.href = href+ex+'supports_ajax=yes';
		};
	};

function getURLParam(strParamName)
{
	var strReturn = "";
	var strHref = window.location.href;
	if ( strHref.indexOf("?") > -1 )
		{
		var strQueryString = strHref.substr(strHref.indexOf("?")).toLowerCase();
		var aQueryString = strQueryString.split("&");
		for ( var iParam = 0; iParam < aQueryString.length; iParam++ )
			{
			if ( aQueryString[iParam].indexOf(strParamName.toLowerCase() + "=") > -1 )
				{
				var aParam = aQueryString[iParam].split("=");
				strReturn = aParam[1];
				break;
				};
			};
		};

return unescape(strReturn);
};
