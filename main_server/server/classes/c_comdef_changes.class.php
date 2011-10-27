<?php
/***********************************************************************/
/** \file	c_comdef_changes.class.php
	\brief The file for the c_comdef_changes class.
    
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

require_once ( dirname ( __FILE__ )."/c_comdef_change.class.php" );

/***********************************************************************/
/** \class c_comdef_changes
	\brief	A class to hold a collection of c_comdef_change objects.

***********************************************************************/
class c_comdef_changes implements i_comdef_has_parent
{
	/// A reference to the "parent" object for this instance.
	private	$_local_id_parent_obj = null;
	/// An array of new c_comdef_change objects that comprises the content.
	private	$_changes_objects = null;

	function __construct(
						$in_parent_object,		///< A reference to the object that "owns" this instance.
						$in_change_data			///< An array of data for the changes to be instantiated.
						)
	{
		$this->SetParentObj ( $in_parent_object );
		
		if ( is_array ( $in_change_data ) && count ( $in_change_data ) )
			{
			$count = 0;
			foreach ( $in_change_data as $row )
				{
				$this->_changes_objects[$count] = null;
				$date_ar = explode ( " ", $row['change_date']);
				$date_a = explode ( "-", $date_ar[0] );
				$date_b = explode ( ":", $date_ar[1] );
				$date = mktime ( $date_b[0], $date_b[1], $date_b[2], $date_a[1], $date_a[2], $date_a[0] );
				$this->_changes_objects[$count++] = new c_comdef_change ( $this, $row['change_type_enum'], $row['user_id_bigint'], $row['service_body_id_bigint'], $row['before_object'], $row['after_object'], $row['object_class_string'], $row['before_id_bigint'], $row['after_id_bigint'], $row['before_lang_enum'], $row['after_lang_enum'], $row['id_bigint'], $row['change_name_string'], $row['change_description_text'], $row['lang_enum'], $date );
				}
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor. Get references to the changes objects.
	
		\returns a reference to an array of c_comdef_changes objects.
	*/
	function &GetChangesObjects()
	{
		return $this->_changes_objects;
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