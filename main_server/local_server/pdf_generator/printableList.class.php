<?php
/**
	\file printableList.class.php
	
	\brief This file contains the exported interface for subclasses
    
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
/**
	This is the interface, describing the exported functions.
*/
require_once ( dirname ( __FILE__ ).'/napdf.class.php' );
define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
require_once ( dirname ( __FILE__ ).'/../../server/config/auto-config.inc.php' );

interface IPrintableList
{
	function AssemblePDF ();
	function OutputPDF ();
};

/**
	This is the base class for use by specialized printing classes.
*/
class printableList
{
	var	$page_x = 8.5;		///< The width, in inches, of each page
	var	$page_y = 11;		///< The height, in inches, of each page.
	var	$units = 'in';		///< The measurement units (inches)
	var	$orientation = 'P';	///< The orientation (portrait)
	/// These are the sort keys, for sorting the meetings before display
	var $sort_keys = array ();
	/// These are the parameters that we send over to the root server, in order to get our meetings.
	var $out_http_vars = array ();
	/// This contains the instance of napdf that we use to extract our data from the server, and to hold onto it.
	var	$napdf_instance = null;
	var $font = 'Times';	///< The font we'll use
	var $font_size = 9;		///< The font size we'll use
	
	/**
		\brief	The constructor for this class does a lot. It creates the instance of the napdf class, gets the data from the
		server, then sorts it. When the constructor is done, the data is ready to be assembled into a PDF.
		
		If the napdf object does not successfully get data from the server, then it is set to null.
	*/
	protected function __construct ( $in_http_vars,	///< The HTTP parameters we'd like to send to the server.
									 $in_lang_search = null	///< An array of language enums, used to extract the correct format codes.
									)
	{
		$napdf_instance = napdf::MakeNAPDF ( $this->page_x, $this->page_y, $this->out_http_vars, $this->units, $this->orientation, $this->sort_keys, $in_lang_search );
		if ( $napdf_instance instanceof napdf )
			{
            $this->napdf_instance =& $napdf_instance;
			}
	}
};
?>