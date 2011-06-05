<?php
/***********************************************************************/
/** \file	c_comdef_service_body.class.php
	\brief	The class file for the c_comdef_service_body class.
    
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

require_once ( dirname ( __FILE__ )."/../shared/classes/base_templates.inc.php" );

/***********************************************************************/
/** \class c_comdef_service_body

	\brief A Class for Service Body Objects

	The BMLT doesn't really care that much about the Service role of any
	given Service Body. Indicators of such are provided so that clients
	of the BMLT can keep track of them.
	
	The BMLT is more interested in the hierarchy of a Service Body. If a
	Service Body has an owner or a secondary owner, then any member of
	the owner or secondary owner can edit any meetings in the Service Body,
	and and Service Body Administrators in an owner or secondary owner can
	administer the Service Body itself.

***********************************************************************/
//	These are defines used to specify what type of Service Body this is.

define ( "c_comdef_service_body__GRP__", "GR" );	// Regular Group
define ( "c_comdef_service_body__ASC__", "AS" );	// Area Service Committee
define ( "c_comdef_service_body__RSC__", "RS" );	// Regional Service Committee
define ( "c_comdef_service_body__WSC__", "WS" );	// World Service Committee
define ( "c_comdef_service_body__MAS__", "MA" );	// Metro Area Service Committee
define ( "c_comdef_service_body__ZFM__", "ZF" );	// Zonal Forum
	
class c_comdef_service_body extends t_comdef_world_type implements i_comdef_db_stored, i_comdef_serialized, i_comdef_auth
{
	/// An integer, containing the unique ID of this service body.
	private	$_id_bigint = null;
	/// An integer, with the ID of the principal administrator for this Service Body.
	private	$_principal_user_bigint = null;
	/// A string, containing a CSV list of integers, each an ID for a user that has editor privileges for meetings for this Service Body.
	private	$_editors_string = null;
	/// A string, containing a URI to a KML file with Service Boundaries for the Service Body.
	private	$_kml_file_uri_string = null;
	/// A string, containing a URI to a site that gives further information about the Service Body.
	private	$_uri_string = null;
	/** An enum string, containing the Service Body type.
		It can be one of the following:
			- c_comdef_service_body__GRP__	Individual NA Group
			- c_comdef_service_body__ASC__	Area Service Committee
			- c_comdef_service_body__RSC__	Regional Service Committee
			- c_comdef_service_body__WSC__	World Service Committee
			- c_comdef_service_body__MAS__	Metro Area
			- c_comdef_service_body__ZFM__	Zonal Forum
	*/
	private	$_sb_type = null;
	/// An integer. The ID of the Service Body that "owns" this one.
	private	$_sb_owner = null;
	/// An integer. Some Service Bodies can have "unofficial" "owners" (like Zonal Forums or Metro Areas).
	private	$_sb_owner_2 = null;
	/// A string that contains the meeting contact email address.
	private $_sb_meeting_email = null;
	
	/*******************************************************************/
	/** \brief Updates or adds this instance to the database.
		
		\returns true if successful, false, otherwise.
		
		\throws a PDOException if there is a problem.
	*/
	function UpdateToDB(
						$is_rollback = false	///< If true, this is a rollback operation.
						)
	{
		$ret = false;
		
		$user = c_comdef_server::GetCurrentUserObj();
	
		if ( $this->UserCanEdit ( $user ) )
			{
			// We take a snapshot of the service body as it currently sits in the database as a "before" image.
			$before = null;
			$before_id = null;
			$before_lang = null;
			$before_obj =& c_comdef_server::GetServiceBodyByIDObj ( $this->GetID() );
			
			if ( $before_obj instanceof c_comdef_service_body )
				{
				$before_obj = clone $before_obj;
				if ( $before_obj instanceof c_comdef_service_body )
					{
					$before_obj->RestoreFromDB();
					
					$before = $before_obj->SerializeObject();
					$before_id = $before_obj->GetID();
					$before_lang = $before_obj->GetLocalLang();
					$before_obj = null;
					}
				}

			$this->DeleteFromDB_NoRecord();
			
			try
				{
				$update = array();
				if ( $this->_id_bigint )
					{
					array_push ( $update, $this->_id_bigint );
					}
				
				array_push ( $update, $this->_principal_user_bigint );
				array_push ( $update, $this->_editors_string );
				array_push ( $update, $this->_kml_file_uri_string );
				array_push ( $update, $this->_uri_string );
				array_push ( $update, $this->GetLocalName() );
				array_push ( $update, $this->GetLocalDescription() );
				array_push ( $update, $this->GetLocalLang() );
				array_push ( $update, $this->GetWorldID() );
				array_push ( $update, $this->GetSBType() );
				array_push ( $update, $this->GeTOwnerID() );
				array_push ( $update, $this->GeTOwner2ID() );
				array_push ( $update, $this->GetContactEmail() );

				$sql = "INSERT INTO `".c_comdef_server::GetServiceBodiesTableName_obj()."` (";
				if ( $this->_id_bigint )
					{
					$sql .= "`id_bigint`,";
					}
				$sql .= "`principal_user_bigint`,`editors_string`,`kml_file_uri_string`,`uri_string`,`name_string`,`description_string`,`lang_enum`,`worldid_mixed`,`sb_type`,`sb_owner`,`sb_owner_2`,`sb_meeting_email`) VALUES (";
				if ( $this->_id_bigint )
					{
					$sql .= "?,";
					}
				$sql .= "?,?,?,?,?,?,?,?,?,?,?,?)";
				c_comdef_dbsingleton::preparedExec($sql, $update );
				// If this is a new user, then we'll need to fetch the ID.
				if ( !$this->_id_bigint )
					{
					$sql = "SELECT LAST_INSERT_ID()";
					$rows = c_comdef_dbsingleton::preparedQuery($sql);
					if ( is_array ( $rows ) && count ( $rows ) )
						{
						$this->_id_bigint = intval ( $rows[0]['last_insert_id()'] );
						}
					}
				
				$after = $this->SerializeObject();
				$after_id = $this->GetID();
				$after_lang = $this->GetLocalLang();
				$cType = (true == $is_rollback) ? 'comdef_change_type_rollback' : ((null != $before) ? 'comdef_change_type_change' : 'comdef_change_type_new');
				c_comdef_server::AddNewChange ( $user->GetID(), $cType, $after_id, $before, $after, 'c_comdef_service_body', $before_id, $after_id, $before_lang, $after_lang );
					
				$ret = true;
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_service_body::UpdateToDB()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Deletes this instance from the database without creating a change record.
		
		\returns true if successful, false, otherwise.
		
		\throws a PDOException if there is a problem.
	*/
	function DeleteFromDB_NoRecord()
	{
		$ret = false;
		
		if ( $this->UserCanEdit ( ) )
			{
			try
				{
				$sql = "DELETE FROM `".c_comdef_server::GetServiceBodiesTableName_obj()."` WHERE id_bigint=?";
				c_comdef_dbsingleton::preparedExec($sql, array ( $this->GetID() ) );
				$ret = true;
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_service_body::DeleteFromDB_NoRecord()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Deletes this instance from the database, and creates a change record.
		
		\returns true if successful, false, otherwise.
		
		\throws a PDOException if there is a problem.
	*/
	function DeleteFromDB()
	{
		$ret = false;
		
		$user = c_comdef_server::GetCurrentUserObj();
		
		if ( $this->UserCanEdit ( $user ) )
			{
			// We take a snapshot of the service body as it currently sits in the database as a "before" image.
			$before = null;
			$before_id = null;
			$before_lang = null;
			$before_obj = c_comdef_server::GetServiceBodyByIDObj ( $this->GetID() );
			
			if ( $before_obj instanceof c_comdef_service_body )
				{
				$before = $before_obj->SerializeObject();
				$before_id = $before_obj->GetID();
				$before_lang = $before_obj->GetLocalLang();
				$before_obj = null;
				}

			$ret = $this->DeleteFromDB_NoRecord();
			
			if ( $ret )
				{
				c_comdef_server::AddNewChange ( $user->GetID(), 'comdef_change_type_delete', $this->GetID(), $before, null, 'c_comdef_service_body', $before_id, null, $before_lang, null );
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Updates this instance to the current values in the DB
		(replacing current values of the instance).
		
		\throws a PDOException if there is a problem.
	*/
	function RestoreFromDB()
	{
		try
			{
			if ( $this->GetID() )
				{
				$sql = "SELECT * FROM `".c_comdef_server::GetServiceBodiesTableName_obj()."` WHERE id_bigint=? LIMIT 1";
				$rows = c_comdef_dbsingleton::preparedQuery($sql, array ( $this->GetID() ) );
				if ( is_array ( $rows ) && count ( $rows ) )
					{
					$this->_principal_user_bigint = $rows[0]['principal_user_bigint'];
					$this->_editors_string = $rows[0]['editors_string'];
					$this->_kml_file_uri_string = $rows[0]['kml_file_uri_string'];
					$this->_uri_string = $rows[0]['uri_string'];
					$this->SetLocalName($rows[0]['name_string']);
					$this->SetLocalDescription($rows[0]['description_string']);
					$this->SetLocalLang($rows[0]['lang_enum']);
					$this->_sb_type = $rows[0]['sb_type'];
					$this->SetOwnerID ( $rows[0]['sb_owner'] );
					$this->SetOwner2ID ( $rows[0]['sb_owner_2'] );
					$this->SetContactEmail ( $rows[0]['sb_meeting_email'] );
					}
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_service_body::RestoreFromDB()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
	}
	
	/*******************************************************************/
	/**	\brief The initial setup call for the class. If you send in values,
		the object will set itself up to use them.
	
	*/
	function __construct(
						$in_parent_obj = null,			///< A reference to the parent object for this Service Body Object.
						$in_id_bigint = null,				///< An integer, containing the ID of the object. If null or 0, then the object is new.
						$in_principal_user_bigint = null,	///< An integer with the ID of the principal user for this Service Body.
						$in_editors_string = null,		///< A string containing a CSV array of integers with editor user IDs
						$in_kml_file_uri_string = null,	///< A string containing a URI to the KML file that displays this Service Body's boundaries.
						$in_uri_string = null,			///< A string containing a URI to a site that has more information about the Service Body.
						$in_name_string = null,			///< The local name of the Service Body as a string.
						$in_description_string = null,	///< The local description of the Service Body as a string.
						$in_lang_enum = null,			///< A string, with the language code for this Service Body.
						$in_world_id_mixed = null,		///< A string, containing the World ID for this Service Body.
						$in_sb_type = null,				/**< An enum string, containing the Service Body type.
															 It can be one of the following:
																- c_comdef_service_body__GRP__	Individual NA Group
																- c_comdef_service_body__ASC__	Area Service Committee
																- c_comdef_service_body__RSC__	Regional Service Committee
																- c_comdef_service_body__WSC__	World Service Committee
																- c_comdef_service_body__MAS__	Metro Area
																- c_comdef_service_body__ZFM__	Zonal Forum
														*/
						$in_sb_owner = null,			///< An integer. The ID of the Service Body that "owns" this Service Body.
						$in_sb_owner_2 = null,			///< An integer. The ID of the "secondary" Service Body that "owns" this Service Body.
						$in_sb_meeting_email = null		///< A string, containing any email address that is to be used for meeting contacts.
						)
	{
		global	$comdef_global_language;
		
		/// This is the server language.
		if ( null == $in_lang_enum )
			{
			$in_lang_enum = $comdef_global_language;
			}
		
		/// Should never be necessary.
		if ( null == $in_lang_enum )
			{
			$in_lang_enum = "en";
			}
			
		// Set the five inherited values.
		$this->SetParentObj ( $in_parent_obj );
		$this->SetLocalLang ( $in_lang_enum );
		$this->SetLocalName ( $in_name_string );
		$this->SetWorldID ( $in_world_id_mixed );
		$this->SetLocalDescription ( $in_description_string );
		
		// Set the local values.
		$this->_id_bigint = $in_id_bigint;
		$this->_principal_user_bigint = $in_principal_user_bigint;
		$this->_editors_string = $in_editors_string;
		$this->_kml_file_uri_string = $in_kml_file_uri_string;
		$this->_uri_string = $in_uri_string;
		$this->_sb_type = $in_sb_type;
		$this->SetOwnerID ( $in_sb_owner );
		$this->SetOwner2ID ( $in_sb_owner_2 );
		$this->SetContactEmail ( $in_sb_meeting_email );
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the service body ID as an integer.
		
		\returns an integer, containing the service body ID.
	*/
	function GetID()
	{
		return $this->_id_bigint;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the service body ID as an integer.
	*/
	function SetID(
					$in_service_body_id_bigint	///< An integer, with the service body ID.
					)
	{
		$this->_id_bigint = $in_service_body_id_bigint;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the principal user for this service body.
		
		\returns an integer, containing the user ID.
	*/
	function GetPrincipalUserID()
	{
		return $this->_principal_user_bigint;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the principal user for this service body.
		
		\returns a reference to a c_comdef_user object of the principal user.
	*/
	function &GetPrincipalUserObj()
	{
		return c_comdef_server::GetUserByIDObj ( $this->_principal_user_bigint );
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the principal user for this service body.
	*/
	function SetPrincipalUserID(
								$in_principal_user_id_bigint	///< Integer, the principal user ID.
								)
	{
		return $this->_principal_user_bigint = $in_principal_user_id_bigint;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the Service Body Type.
		
		\returns a string, containing the Type.
				 It can be one of the following:
					- c_comdef_service_body__GRP__	Individual NA Group
					- c_comdef_service_body__ASC__	Area Service Committee
					- c_comdef_service_body__RSC__	Regional Service Committee
					- c_comdef_service_body__WSC__	World Service Committee
					- c_comdef_service_body__MAS__	Metro Area
					- c_comdef_service_body__ZFM__	Zonal Forum
	*/
	function GetSBType()
	{
		return $this->_sb_type;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the URI of the KML file for the Service Boundaries.
	*/
	function SetSBType(
						$in_sb_type	/**< A string, containing the Type
										 It can be one of the following:
											- c_comdef_service_body__GRP__	Individual NA Group
											- c_comdef_service_body__ASC__	Area Service Committee
											- c_comdef_service_body__RSC__	Regional Service Committee
											- c_comdef_service_body__WSC__	World Service Committee
											- c_comdef_service_body__MAS__	Metro Area
											- c_comdef_service_body__ZFM__	Zonal Forum
									*/
						)
	{
		$this->_sb_type = $in_sb_type;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the ID of the 'owner' of this object.
		
		\returns an integer, containing the owner ID.
	*/
	function GetOwnerID()
	{
		return $this->_sb_owner;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the ID of the 'secondary owner' of this object.
		
		\returns an integer, containing the owner ID of the secondary "owner."
	*/
	function GetOwner2ID()
	{
		return $this->_sb_owner_2;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the 'owner' of this object as a reference to an object.
		
		\returns a reference to the internal c_comdef_service_body object for the service body. Null if not found.
	*/
	function &GetOwnerIDObject()
	{
		$owner_id = $this->GetOwnerID();
		$ret =& c_comdef_server::GetServiceBodyByIDObj ( $owner_id );
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the 'secondary owner' of this object as a reference to an object.
		
		\returns a reference to the internal c_comdef_service_body object for the service body. Null if not found.
	*/
	function &GetOwner2IDObject()
	{
		return c_comdef_server::GetServiceBodyByIDObj ( $this->GetOwner2ID() );
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the ID of the 'owner' of this object.
	*/
	function SetOwnerID(
						$in_sb_owner	///< An integer, containing the owner of the object.
						)
	{		
		$this->_sb_owner = intval ( $in_sb_owner );
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the ID of the 'secondary owner' of this object.
	*/
	function SetOwner2ID(
						$in_sb_owner	///< An integer, containing the owner of the object.
						)
	{
		$this->_sb_owner_2 = intval ( $in_sb_owner );
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the ID of the 'owner' of this object by sending in an object reference.
	
		This will test, to ensure that a loop will not occur. The function
		will return false if it determines that the hierarchy would result
		in this object being its own ancestor.
	
		\returns true, if successful, false, if the ID would cause a loop.
	*/
	function SetOwnerObj(
						&$in_sb_owner_obj	///< An integer, containing the owner of the object.
						)
	{
		if ( $in_sb_owner_obj instanceof c_comdef_service_body )
			{
			return $this->SetOwnerID ( $in_sb_owner_obj->GetID() );
			}
		
		return false;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the ID of the 'secondary owner' of this object by sending in an object reference.
	
		This will test, to ensure that a loop will not occur. The function
		will return false if it determines that the hierarchy would result
		in this object being its own ancestor.
	
		\returns true, if successful, false, if the ID would cause a loop.
	*/
	function SetOwner2Obj(
						&$in_sb_owner_obj	///< An integer, containing the secondary owner of the object.
						)
	{
		if ( $in_sb_owner_obj instanceof c_comdef_service_body )
			{
			return $this->SetOwner2ID ( $in_sb_owner_obj->GetID() );
			}
		
		return false;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the URI of the KML file for the Service Boundaries.
		
		\returns a string, containing the URI.
	*/
	function GetKMLURI()
	{
		return $this->_kml_file_uri_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the URI of the KML file for the Service Boundaries.
	*/
	function SetKMLURI(
						$in_kml_uri_string	///< A string, containing the URI
						)
	{
		$this->_kml_file_uri_string = $in_kml_uri_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the URI of a site with more information on the Service Body.
		
		\returns a string, containing the URI.
	*/
	function GetURI()
	{
		return $this->_uri_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the URI of a site with more information on the Service Body.
	*/
	function SetURI(
					$in_uri_string	///< A string, containing the URI
					)
	{
		$this->_uri_string = $in_uri_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - set the contact email string.
	*/
	function SetContactEmail( $in_email	///< A string. The email address to be set.
							)
	{
		$this->_sb_meeting_email = $in_email;
	}
	
	/*******************************************************************/
	/** \brief Get the contact email for this Service Body.
				If $in_recursive is false, then it simply looks at this Service Body.
				This first looks at the Service Body Meeting Email. If that is not
				there, it gets the email for the principal admin. If that is not
				there, it percolates to the parent, recursively (if $in_recursive is true ).
		
		\returns a string, which is the contact email for this Service Body
	*/
	function GetContactEmail( $in_recursive = false	///< If this is true, then the function will return a recursive result. Default is false.
							)
	{
		$ret = trim ( $this->_sb_meeting_email );
		
		if ( !$ret && $in_recursive )
			{
			$owner =& $this->GetOwnerIDObject();
			
			if ( $owner instanceof c_comdef_service_body )
				{
				$ret = $owner->GetContactEmail ( $in_recursive );
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns IDs for all the editors.
		
		\returns an array of integers, containing the editor IDs.
	*/
	function GetEditors()
	{
		$ret_array = array();
		
		$editor_ids = explode ( ",", $this->_editors_string );
		
		foreach ( $editor_ids as $id )
			{
			$id = intval ( $id );
			array_push ( $ret_array, $id );
			}
		
		return $ret_array;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns user objects for all the editors.
		
		\returns an array of references to c_comdef_user objects. The associative key for each user will the ID for that user.
	*/
	function GetEditorsAsObjects()
	{
		$ret_array = array();
		
		$editor_ids = $this->GetEditors();
		
		foreach ( $editor_ids as $id )
			{
			$ret_array[$id] =& c_comdef_server::GetUserByIDObj ( $id );
			}
		
		return $ret_array;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns true if the current user is an editor
		for the Service Body, or if the user is an owner.
		
		\returns a boolean. True if the user is an editor or principal user.
	*/
	function IsUserInServiceBody( $in_user_object = null	///< A reference to an instance of c_comdef_user. If null, the current user is checked.
								)
	{
		$ret = false;
		// We load the server user if one wasn't supplied.
		if ( null == $in_user_object )
			{
			$in_user_object = c_comdef_server::GetCurrentUserObj();
			}
		
		// If it isn't a user object, we fail right there.
		if ( $in_user_object instanceof c_comdef_user )
			{
			$in_user_object->RestoreFromDB();	// The reason you do this, is to ensure that the user wasn't modified "live." It's a security precaution.
			$user_id = intval ( $in_user_object->GetID() );
			$in_user_object = null;
			
			if ( intval ( $this->GetPrincipalUserID() ) == $user_id )
				{
				$ret = true;
				}
			else
				{
				$editors = $this->GetEditors();
	
				foreach ( $editors as $editor )
					{
					if ( $user_id == intval ( $editor ) )
						{
						$ret = true;
						break;
						}
					}
				}
			}
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns true if the current user is an editor
		for the Service Body. Checks up the hierarchy recursively.
		
		\returns a boolean. True if the user is in the hierarchy (going up) of the Service Body.
	*/
	function IsUserInServiceBodyHierarchy( $in_user_object = null	///< A reference to an instance of c_comdef_user. If null, the current user is checked.
											)
	{
		$ret = false;
		// We load the server user if one wasn't supplied.
		if ( null == $in_user_object )
			{
			$in_user_object = c_comdef_server::GetCurrentUserObj();
			}
		
		// If it isn't a user object, we fail right there.
		if ( $in_user_object instanceof c_comdef_user )
			{
			$in_user_object->RestoreFromDB();	// The reason you do this, is to ensure that the user wasn't modified "live." It's a security precaution.
			// First, see if the user is in this Service Body.
			
			$ret = $this->IsUserInServiceBody($in_user_object);
			
			if ( !$ret && $this->GetOwnerID() )
				{
				$parent = c_comdef_server::GetServer()->GetServiceBodyByIDObj ( $this->GetOwnerID() );
				// Check the primary "parent" Service Body's hierarchy.
				if ( $parent instanceof c_comdef_service_body )
					{
					if ( $parent->IsUserInServiceBody($in_user_object) )
						{
						$ret = true;
						}
					else
						{
						$ret = $parent->IsUserInServiceBodyHierarchy ( $in_user_object );
						}
					}
				}
			
			if ( !$ret && $this->GetOwner2ID() )
				{
				$parent = c_comdef_server::GetServer()->GetServiceBodyByIDObj ( $this->GetOwner2ID() );
				
				// Check the secondary "parent" Service Body's hierarchy.
				if ( $parent instanceof c_comdef_service_body )
					{
					if ( $parent->IsUserInServiceBody($in_user_object) )
						{
						$ret = true;
						}
					else
						{
						$ret = $parent->IsUserInServiceBodyHierarchy ( $in_user_object );
						}
					}
				}
			}

		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the editors by ID.
	
		\returns the processed string.
	*/
	function SetEditors(
						$in_editor_id_array	///< An array of integers, containing the user IDs.
						)
	{
		foreach ( $in_editor_id_array as &$id )
			{
			// We need valid, non-server admin IDs.
			if ( $id == $this->GetPrincipalUserID() )
				{
				unset ( $id );
				}
			else
				{
				$user = c_comdef_server::GetOneUser ( $id );
				if ( ($user instanceof c_comdef_user) && ($user->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) )
					{
					unset ( $id );
					}
				else
					{
					$id = intval ( $id );	// Just to be tinfoil...
					}
				}
			}
		
		$this->_editors_string = implode ( ",", $in_editor_id_array );
		
		return $this->_editors_string;
	}
		
	/*******************************************************************/
	/** \brief Returns a storable serialization of the object, as a string.
		
		This is only used for the changes, as the serialized string may not
		be easily searched.
		
		\returns a string, containing the table array, in serialized form.
	*/
	function SerializeObject()
	{
		$serialize_array = array(
								$this->_id_bigint,
								$this->_principal_user_bigint,
								$this->_editors_string,
								$this->_kml_file_uri_string,
								$this->_uri_string,
								$this->GetWorldID(),
								$this->GetLocalName(),
								$this->GetLocalDescription(),
								$this->GetLocalLang(),
								$this->_sb_type,
								$this->_sb_owner,
								$this->_sb_owner_2,
								$this->GetContactEmail()
								);
		
		return serialize ( $serialize_array );
	}
	
	/*******************************************************************/
	/** \brief This takes the serialized table, and instantiates a
		new object from it.
		
		\returns a new instance of c_comdef_service_body, set up according to
		the serialized data passed in.
	*/
	static function UnserializeObject( $in_parent,			///< The parent object.
										$serialized_string	///< A string containing the serialized data.
										)
	{
		list (	$_id_bigint,
				$_principal_user_bigint,
				$_editors_string,
				$_kml_file_uri_string,
				$_uri_string,
				$_worldid_mixed,
				$_name_string,
				$_description_string,
				$_lang_enum,
				$sb_type,
				$sb_owner,
				$sb_owner_2,
				$sb_meeting_email ) = unserialize ( $serialized_string );
		
		return new c_comdef_service_body ( $in_parent, $_id_bigint, $_principal_user_bigint, $_editors_string, $_kml_file_uri_string, $_uri_string, $_name_string, $_description_string, $_lang_enum, $_worldid_mixed, $sb_type, $sb_owner, $sb_owner_2, $sb_meeting_email );
	}
	
	/*******************************************************************/
	/** \brief Test to see if a user is allowed to edit an instance (change the data).
		Service Body Administrators that are Editors in parent, or secondary parent
		Service bodies can edit the Service body.
	
		\returns true, if the user is allowed to edit, false, otherwise.
	*/
	function UserCanEdit (
							$in_user_object = null	///< A reference to a c_comdef_user object, for the user to be validated. If null, or not supplied, the server current user is tested.
							)
	{
		$ret = false;

		// We load the server user if one wasn't supplied.
		if ( null == $in_user_object )
			{
			$in_user_object = c_comdef_server::GetCurrentUserObj();
			}
		
		// If it isn't a user object, we fail right there.
		if ( $in_user_object instanceof c_comdef_user )
			{
			$in_user_object->RestoreFromDB();	// The reason you do this, is to ensure that the user wasn't modified "live." It's a security precaution.
			
			// User must be a Service Body Admin
			if ( $in_user_object->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN )
				{
				// If there is an existing object, then we can't make changes unless it's allowed in the existing object.
				$current_obj =& c_comdef_server::GetServiceBodyByIDObj ( $this->GetID() );
				
				$new_obj = false;
				
				// If there is no current object, then we are a new service body. Otherwise, block dope fiends by reloading.
				if ( $current_obj instanceof c_comdef_service_body )
					{
					// We clone, in case changes have been made, and we don't want to screw them up.
					$current_obj = clone $current_obj;
					$current_obj->RestoreFromDB();
					}
				else
					{
					$current_obj =& $this;
					}
				
				if ( $current_obj instanceof c_comdef_service_body )
					{
					if ( intval ( $current_obj->GetPrincipalUserID() ) == intval ( $in_user_object->GetID() ) )
						{
						$ret = true;
						}
					else
						{
						$ret = $this->IsUserInServiceBodyHierarchy($in_user_object );
						}
					}
				}
			elseif ( c_comdef_server::IsUserServerAdmin() )	// The server admin can edit anything.
				{
				$ret = true;
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief	Check to see if a given Service Body ID is anywhere in
		the "direct parent" hierarchy of this Service Body.
	
		\returns a boolean. True if the given Service Body appears in the
		hierarchy above it.
	*/
	function IsOwnedBy ($in_sb_id,	///< The ID of a potential owner.
						$in_direct = false	///< If this is set to true, then only the immediate parent is checked. Default is false.
						)
		{
		$ret = false;
		$server =& c_comdef_server::GetServer();
		
		if ( $server instanceof c_comdef_server )
			{
			$sb_to_check =& $server->GetServiceBodyByIDObj($in_sb_id);
			
			$parent = $this->GetOwnerID();
			
			if ( $parent == $in_sb_id )
				{
				$ret = true;
				}
			elseif ( !$in_direct )
				{
				if ( $parent )
					{
					$sb_to_check =& $server->GetServiceBodyByIDObj($parent);
					
					if ( $sb_to_check instanceof c_comdef_service_body )
						{
						$ret = IsSBRecursive ( $in_sb_id, $sb_to_check->GetID(), $in_direct );
						}
					}
				}
			}
		
		return $ret;
		}
};
?>