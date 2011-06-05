<?php
/***********************************************************************/
/** \file	c_comdef_service_bodies.class.php
	\brief The file for the c_comdef_service_bodies class.
    
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

require_once ( dirname ( __FILE__ )."/c_comdef_service_body.class.php" );

/***********************************************************************/
/** \class c_comdef_service_bodies
	\brief	A class to hold a collection of c_comdef_service_body objects.

***********************************************************************/
class c_comdef_service_bodies implements i_comdef_has_parent
{
	/// This is the parent (container) object that holds this instance.
	private	$_local_id_parent_obj = null;
	
	/*******************************************************************/
	/** We keep a local copy of the simple array, because we can instantly
		access it, as opposed to having to instantiate iterators.
	*/
	private $_local_copy_of_array = null;

	function __construct(
						$in_parent_object,				///< A reference to the object that "owns" this instance.
						$in_service_body_object_array	///< An array of references to c_comdef_service_body objects, to be stored as local references.
						)
	{
		$this->SetParentObj ( $in_parent_object );
		
		foreach ( $in_service_body_object_array as &$obj )
			{
			// Who's yer daddy?
			$obj->SetParentObj ( $this );
			}
		$this->_local_copy_of_array = $in_service_body_object_array;
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

	/*******************************************************************/
	/** \brief Accessor -Get the service body object array.
	
		\returns a reference to an array of c_comdef_service_body objects.
	*/
	function &GetServiceBodiesArray()
	{
		return $this->_local_copy_of_array;
	}
};

?>