<?php
/***********************************************************************/
/** \file	c_comdef_formats.class.php
	\brief	The class file for the c_comdef_formats class.
    
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

defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

/// Include the format class.
require_once ( dirname ( __FILE__ )."/c_comdef_format.class.php" );
require_once ( dirname ( __FILE__ )."/../shared/classes/comdef_utilityclasses.inc.php" );

/***********************************************************************/
/** \class c_comdef_formats
	\brief A Class for Aggregating Format Codes

	This class acts as an array aggregator for c_comdef_format objects.
	It is a class unto itself, because we want to have a couple of
	additional filtering options (like the ability to get a bunch of
	formats that share a _shared_id_bigint code).
	
	The formats are kept in a simple two-dimensional array:
	
	\code
		$_local_copy_of_array = array[$lang_enum][$shared_id_bigint]
	\endcode
	
	The combination of shared ID and language needs to be unique. This is how
	we match formats to languages. The same shared ID is used throughout
	the system, and we use the localization to return its key and description
	in the appropriate language.
***********************************************************************/
class c_comdef_formats implements i_comdef_has_parent
{
	/// This is the parent (container) object that holds this instance.
	private	$_local_id_parent_obj = null;
	
	/*******************************************************************/
	/** We keep a local copy of the simple array, because we can instantly
		access it, as opposed to having to instantiate iterators.
	*/
	private $_local_copy_of_array = null;

	/*******************************************************************/
	/** \brief Constructor. Sets the parent object.
	*/
	function __construct (
							$in_parent_obj,
							$in_array
							)
	{
		$this->SetParentObj ( $in_parent_obj );
		$this->_local_copy_of_array = $in_array;
	}

	/*******************************************************************/
	/** \brief Accessor -Get the format object array.
	
		\returns a reference to an array of c_comdef_format objects.
	*/
	function &GetFormatsArray()
	{
		return $this->_local_copy_of_array;
	}
	
	/*******************************************************************/
	/** \brief Return a reference to a single object, by shared ID and
		language.
		
		You do not need to provide a language, in which case, the server's
		local language is used.
	
		\returns A reference to the single selected object.
	*/
	function GetFormatBySharedIDCodeAndLanguage (
											$in_shared_id_bigint,	///< This is the shared ID code.
											$in_lang_enum = null	///< This is the code for the desired language. If not given, the server localization will be used.
											)
	{
		/// If no language is given, we use the server's native language.
		if ( null == $in_lang_enum )
			{
			$in_lang_enum = $this->GetParentObj()->GetLocalLang();
			}
		
		if ( !$in_lang_enum )
			{
			$in_lang_enum = c_comdef_server::GetServer()->GetLocalLang();
			}
		
		// Should never happen.
		if ( !$in_lang_enum )
			{
			$in_lang_enum = "en";
			}
		
		if ( isset ( $this->_local_copy_of_array[$in_lang_enum][$in_shared_id_bigint] ) )
			{
			return $this->_local_copy_of_array[$in_lang_enum][$in_shared_id_bigint];
			}
	}
	
	/*******************************************************************/
	/** \brief Return all the formats that share a given shared ID code.
	
		\returns A simple array of references to the objects for the formats
		in all languages for the given shared ID. Null if no formats fit the language.
	*/
	function GetFormatsBySharedIDCode (
										$in_shared_id_bigint	///< This is the shared ID code.
									)
	{
		$ret = null;

		foreach ( $this->_local_copy_of_array as $lang )
			{
			foreach ( $lang as $id )
				{
				if ( $id->GetSharedID() == $in_shared_id_bigint )
					{
					$ret[$id->GetLocalLang()] = $id;
					}
				}
			}

		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Return all the formats that share a given language.
		
		You do not need to provide a language, in which case, the server's
		local language is used.
	
		\returns A simple array of references to the objects for the formats
		in the given language. Null if no formats fit the language.
	*/
	function GetFormatsByLanguage (
									$in_lang_enum = null	///< This is the code for the desired language. If not given, the server localization will be used.
									)
	{
		$ret = null;
		
		/// If no language is given, we use the server's native language.
		if ( null == $in_lang_enum )
			{
			$in_lang_enum = $this->GetParentObj()->GetLocalLang();
			}
			
		if ( is_array ( $this->_local_copy_of_array[$in_lang_enum] ) )
			{
			$ret = $this->_local_copy_of_array[$in_lang_enum];
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Return all the formats that contain the given string.
	
		If the language is English or Spanish, this does a metaphone search of all
		all the strings in the "en" or "es" formats. If it is any other language, then
		a simple string search is performed.
		
		\returns A simple array of references to the objects for the formats
		in all languages for the given string. Null if no formats fit.
	*/
	function GetFormatsByString (
								$in_string,				///< This is the string to search for.
								$in_lang_enum = null	///< This is the code for the desired language. If not given, the server localization will be used.
								)
	{
		$ret = null;
		
		/// If no language is given, we use the server's native language.
		if ( null == $in_lang_enum )
			{
			$in_lang_enum = $this->GetParentObj()->GetLocalLang();
			}
		
		/// We force the search to happen in lowercase. This is a very basic search.
		$in_string = strtolower ( $in_string );

		/// If we will use metaphone, we convert our search criteria to metaphone keys.
		$in_string_comp = SplitIntoMetaphone ( $in_string, $in_lang_enum );
		
		$count = 0;
		$iterating_target = $this->GetFormatsByLanguage ( $in_lang_enum );
		foreach ( $iterating_target as $id )
			{
			$found = false;
			
			/// We will look in each of the text fields for our string.
			$string = $id->GetKey();

			$comp = SplitIntoMetaphone ( $string, $in_lang_enum );
		
			foreach ( $in_string_comp as $test )
				{
				if ( array_search ( $test, $comp ) )
					{
					$found = true;
					break;
					}
				}
			
			if ( !$found )
				{
				$string = $id->GetLocalName();
	
				$comp = SplitIntoMetaphone ( $string, $in_lang_enum );
			
				foreach ( $in_string_comp as $test )
					{
					if ( array_search ( $test, $comp ) )
						{
						$found = true;
						break;
						}
					}
				}
			
			if ( !$found )
				{
				$string = $id->GetLocalDescription();
	
				$comp = SplitIntoMetaphone ( $string, $in_lang_enum );
			
				foreach ( $in_string_comp as $test )
					{
					if ( array_search ( $test, $comp ) )
						{
						$found = true;
						break;
						}
					}
				}
			
			if ( $found )
				{
				$ret[$count++] = $id;
				}
			}

		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Set the parent object of this instance.
	*/
	function SetParentObj(
						$in_parent_obj	///< A reference to the parent object.
						)
	{
		$this->_local_id_parent_obj = null;
		$this->_local_id_parent_obj = $in_parent_obj;
	}
	
	/*******************************************************************/
	/** \brief Return a reference to the parent object of this instance.
	
		\returns a reference to the parent instance of the object.
	*/
	function GetParentObj()
	{
		return $this->_local_id_parent_obj;
	}
};
?>