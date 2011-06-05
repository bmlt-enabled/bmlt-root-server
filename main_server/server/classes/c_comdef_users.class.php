<?php
/***********************************************************************/
/** \file	c_comdef_users.class.php
	\brief The file for the c_comdef_users class.
    
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

require_once ( dirname ( __FILE__ )."/c_comdef_user.class.php" );

/***********************************************************************/
/** \class c_comdef_users
	\brief	A class to hold a collection of c_comdef_users objects.

***********************************************************************/
class c_comdef_users implements i_comdef_has_parent
{
	/// This is the parent (container) object that holds this instance.
	private	$_local_id_parent_obj = null;
	
	/*******************************************************************/
	/** We keep a local copy of the simple array, because we can instantly
		access it, as opposed to having to instantiate iterators.
	*/
	private $_local_copy_of_array = null;

	function __construct(
						$in_parent_object,		///< A reference to the object that "owns" this instance.
						$in_user_object_array	///< An array of references to c_comdef_user objects, to be stored as local references.
						)
	{
		$this->SetParentObj ( $in_parent_object );
		
		foreach ( $in_user_object_array as &$obj )
			{
			// Who's yer daddy?
			$obj->SetParentObj ( $this );
			}

		$this->_local_copy_of_array = $in_user_object_array;
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
	/** \brief Accessor -Get the user object array.
	
		\returns a reference to an array of c_comdef_user objects.
	*/
	function &GetUsersArray()
	{
		return $this->_local_copy_of_array;
	}

	/*******************************************************************/
	/** \brief Accessor -Get the user object for one user, given an ID.
	
		\returns a reference to a c_comdef_user object. Null if none.
	*/
	function &GetUserByID(
						$in_user_id	///< An integer -The ID of the user.
						)
	{
		$ret = null;
		
		foreach ( $this->_local_copy_of_array as &$user )
			{
			if ( $in_user_id == $user->GetID() )
				{
				$ret =& $user;
				break;
				}
			}
		
		return $ret;
	}

	/*******************************************************************/
	/** \brief Given a login and password, looks up the user, and returns
		an encrypted password for that user.
	
		\returns a string, with the encrypted password. Null if none.
	*/
	function &GetEncryptedPW(	$in_login,		///< A string. The login ID.
								$in_password	///< A string. the UNENCRYPTED password for the user.
								)
	{
		$ret = null;
		
		foreach ( $this->_local_copy_of_array as &$user )
			{
			if ( ($in_login == $user->GetLogin()) && (FullCrypt ( $in_password, $user->GetPassword() ) == $user->GetPassword()) )
				{
				$ret = FullCrypt ( $in_password, $user->GetPassword() );
				break;
				}
			}
		
		return $ret;
	}

	/*******************************************************************/
	/** \brief Given a login and password, looks up the user, and returns
		a reference to that user object.
	
		\returns a reference to a c_comdef_user object. Null if none.
	*/
	function &GetUserByLoginCredentials(
										$in_login,		///< A string. The login ID.
										$in_password	///< A string. the ENCRYPTED password for the user.
										)
	{
		$ret = null;
		
		foreach ( $this->_local_copy_of_array as &$user )
			{
			if ( ($in_login == $user->GetLogin()) && ($in_password == $user->GetPassword()) )
				{
				$ret =& $user;
				break;
				}
			}
		
		return $ret;
	}

	/*******************************************************************/
	/** \brief Accessor -Get the user object for the Server Admin (User ID 1).
	
		\returns a reference to a c_comdef_user object for the server admin.
	*/
	function &GetServerAdminObj()
	{
		return $this->_local_copy_of_array[1];
	}

	/*******************************************************************/
	/** \brief Add a user object to the end of the array.
	*/
	function AddUser(	&$in_user	///< A reference to the user to be added.
						)
	{
		if ( $in_user instanceof c_comdef_user )
			{
			$in_user->SetParentObj ( $this );
			array_push ( $this->_local_copy_of_array, $in_user );
			}
	}
};
?>