<?php
/***********************************************************************/
/** \file	c_comdef_user.class.php
	\brief	The class file for the c_comdef_change class.
    
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
require_once ( dirname ( __FILE__ )."/../shared/classes/comdef_utilityclasses.inc.php" );

define ( "_USER_LEVEL_SERVER_ADMIN", 1 );
define ( "_USER_LEVEL_SERVICE_BODY_ADMIN", 2 );
define ( "_USER_LEVEL_EDITOR", 3 );
define ( "_USER_LEVEL_DISABLED", 4 );
define ( "_USER_LEVEL_OBSERVER", 5 );

/***********************************************************************/
/** \class c_comdef_user
	\brief This class handles BMLT users. One instance is created for
	each user on the server.
***********************************************************************/
class c_comdef_user extends t_comdef_local_type implements i_comdef_db_stored, i_comdef_serialized, i_comdef_auth
{
	/// An integer, containing the unique ID of this user.
	private	$_id_bigint = null;
	
	/**
		\brief An integer, containing the user level.
		
			Values:
			- (1)	Server Administrator -There can only be one. The user ID is always 1.
					This administrator can edit all aspects of the server.
			- (2)	Service Body Administrator -There can only be one per Service Body
					A Service Body Administrator can edit all meetings from the Service Body
					that assigns the user as its administrator. This user can also assign
					other Service Body Administrators or Editors as users able to edit
					meetings belonging to the Service Body.
					A Service Body Administrator can also edit Service Body information.
					NOTE: A Service Body Administrator only has extra rights when assigned
					to a specific Service Body. If assigned as an Editor on another Service
					Body, they do not have rights beyond those of an Editor.
			- (3)	Editor -There can be many.
					An Editor is assigned by a Service Body Administrator to edit meetings
					for that Service Body. They can only edit meetings; not users or
					Service Bodies.
					Making a user an editor (or a Service Body Administrator) doesn't
					automatically give them any rights. They must first be assigned to a
					Service Body, either by the Server Administrator (Service Body Administrators),
					or by a Service Body Administrator (Editors).
			- (4)	User Disabled.
			- (5)	Observer -There can be many. This user cannot make changes, but can see visibility 0
					data items in meetings for the Service bodies to which it has been attached..
	*/
	private	$_user_level_tinyint = null;
	
	/// A string, containing the user's email address.
	private $_email_address_string = null;
	
	/// A string, containing the user's login ID.
	private $_login_string = null;
	
	/// A string, containing the user's encrypted password.
	private $_password_string = null;
	
	/// A time date, indicating the last time the user was active. This will be useful for administration.
	private $_last_access = null;
	
	/*******************************************************************/
	/** \brief Updates or adds this instance to the database.
		
		\returns true if successful, false, otherwise.
		
		\throws a PDOException if there is a problem.
	*/
	function UpdateToDB(
						$is_rollback = false,	///< If true, this is a rollback operation.
						$new_login = null,		///< This is a new login (Due to the self-checking, we can't set our own login before this). If it is null, no new password will be set.
						$new_pass = null		///< This is a new password (Due to the self-checking, we can't set our own pass before this). If it is null, no new password will be set.
						)
	{
		$ret = false;
			
		$cur_user =& c_comdef_server::GetCurrentUserObj();
		
		if ( $cur_user instanceof c_comdef_user )
			{
			if ( $this->UserCanEdit ( $cur_user ) )
				{
				// We take a snapshot of the user as it currently sits in the database as a "before" image.
				$before = null;
				$before_id = null;
				$before_lang = null;
				$before_obj = c_comdef_server::GetOneUser ( $this->GetID() );
				
				if ( $before_obj instanceof c_comdef_user )
					{
					$before_obj_clone = clone $before_obj;
					$before_obj_clone->RestoreFromDB();
					$before = $before_obj_clone->SerializeObject();
					$before_id = $before_obj_clone->GetID();
					$before_lang = $before_obj_clone->GetLocalLang();
					$before_obj_clone = null;
					}
	
				$this->DeleteFromDB_NoRecord();
				
				try
					{
					$update = array();
					if ( $this->_id_bigint )
						{
						array_push ( $update, $this->_id_bigint );
						}
					
					array_push ( $update, $this->_user_level_tinyint );
					array_push ( $update, $this->_email_address_string );
					
					if ( null != $new_login )
						{
						$this->SetLogin($new_login);
						}

					array_push ( $update, $this->_login_string );
					
					if ( null != $new_pass )
						{
						$this->SetNewPassword($new_pass);
						}

					array_push ( $update, $this->GetPassword() );
					array_push ( $update, date ( "Y-m-d H:i:s", $this->_last_access ) );
					array_push ( $update, $this->GetLocalName() );
					array_push ( $update, $this->GetLocalDescription() );
					array_push ( $update, $this->GetLocalLang() );
					
					$sql = "INSERT INTO `".c_comdef_server::GetUserTableName_obj()."` (";
					if ( $this->_id_bigint )
						{
						$sql .= "`id_bigint`,";
						}
					$sql .= "`user_level_tinyint`,`email_address_string`,`login_string`,`password_string`,`last_access_datetime`,`name_string`,`description_string`,`lang_enum`) VALUES (";
					if ( $this->_id_bigint )
						{
						$sql .= "?,";
						}
					$sql .= "?,?,?,?,?,?,?,?)";
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
					c_comdef_server::AddNewChange ( $cur_user->GetID(), $cType, null, $before, $after, 'c_comdef_user', $before_id, $after_id, $before_lang, $after_lang );
					$ret = true;
					}
				catch ( Exception $ex )
					{
					global	$_COMDEF_DEBUG;
					
					if ( $_COMDEF_DEBUG )
						{
						echo "Exception Thrown in c_comdef_user::UpdateToDB()!<br />";
						var_dump ( $ex );
						}
					throw ( $ex );
					}
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
				$sql = "DELETE FROM `".c_comdef_server::GetUserTableName_obj()."` WHERE id_bigint=?";
				c_comdef_dbsingleton::preparedExec($sql, array ( $this->GetID() ) );
				$ret = true;
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_user::DeleteFromDB()!<br />";
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
			// We take a snapshot of the user as it currently sits in the database as a "before" image.
			$before = null;
			$before_id = null;
			$before_lang = null;
			$before_obj = c_comdef_server::GetOneUser ( $this->GetID() );
			
			if ( $before_obj instanceof c_comdef_user )
				{
				$before = $before_obj->SerializeObject();
				$before_id = $before_obj->GetID();
				$before_lang = $before_obj->GetLocalLang();
				$before_obj = null;
				}

			$ret = $this->DeleteFromDB_NoRecord();
			
			if ( $ret )
				{
				c_comdef_server::AddNewChange ( $user->GetID(), 'comdef_change_type_delete', $this->GetID(), $before, null, 'c_comdef_user', $before_id, null, $before_lang, null );
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
				$sql = "SELECT * FROM `".c_comdef_server::GetUserTableName_obj()."` WHERE id_bigint=? LIMIT 1";
				$rows = c_comdef_dbsingleton::preparedQuery($sql, array ( $this->GetID() ) );
				if ( is_array ( $rows ) && count ( $rows ) )
					{
					$this->_user_level_tinyint = $rows[0]['user_level_tinyint'];
					$this->_email_address_string = $rows[0]['email_address_string'];
					$this->_login_string = $rows[0]['login_string'];
					$this->_password_string = $rows[0]['password_string'];
					$time = explode ( " ", $rows[0]['last_access_datetime'] );
					$t0 = explode ( "-", $time[0] );
					$t1 = explode ( ":", $time[1] );
					$this->_last_access = mktime ( $t1[0], $t1[1], $t1[2], $t0[1], $t0[2], $t0[0] );
					$this->SetLocalName($rows[0]['name_string']);
					$this->SetLocalDescription($rows[0]['description_string']);
					$this->SetLocalLang($rows[0]['lang_enum']);
					}
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_user::RestoreFromDB()!<br />";
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
						$in_parent_obj,				///< A reference to the parent object for this object
						$in_id_bigint,				///< Integer. The ID of the user
						$in_user_level_tinyint,		/**< \brief The user level (an integer).
							
														Values:
														- (1)	Server Administrator -There can only be one. The user ID is always 1.
																This administrator can edit all aspects of the server.
														- (2)	Service Body Administrator -There can only be one per Service Body
																A Service Body Administrator can edit all meetings from the Service Body
																that assigns the user as its administrator. This user can also assign
																other Service Body Administrators or Editors as users able to edit
																meetings belonging to the Service Body.
																A Service Body Administrator can also edit Service Body information.
																NOTE: A Service Body Administrator only has extra rights when assigned
																to a specific Service Body. If assigned as an Editor on another Service
																Body, they do not have rights beyond those of an Editor.
														- (3)	Editor -There can be many.
																An Editor is assigned by a Service Body Administrator to edit meetings
																for that Service Body. They can only edit meetings; not users or
																Service Bodies.
																Making a user an editor (or a Service Body Administrator) doesn't
																automatically give them any rights. They must first be assigned to a
																Service Body, either by the Server Administrator (Service Body Administrators),
																or by a Service Body Administrator (Editors).
														- (4)	User Disabled.
														- (5)	Observer -There can be many. This user cannot make changes, but can see visibility 0
																data items in meetings for the Service bodies to which it has been attached..
													*/
						$in_email_address_string,	///< A string, containing the user's email address.
						$in_login_string,			///< A string, containing the user's login.
						$in_password_string,		///< A string, containing the user's password, in encrypted form.
						$in_lang_enum,				///< An enum/string, with the user's language.
						$in_name_string,			///< A string, containing the readble name for the user.
						$in_description_string,		///< A string, containing a description of the user.
						$in_last_access = null		///< An epoch time, indicating the last access of this user (Optional).
						)
	{
		// Set the four inherited values.
		$this->SetParentObj ( $in_parent_obj );
		$this->SetLocalLang ( $in_lang_enum );
		$this->SetLocalName ( $in_name_string );
		$this->SetLocalDescription ( $in_description_string );
		
		// Set the local values.
		$this->_id_bigint = $in_id_bigint;
		$this->_user_level_tinyint = $in_user_level_tinyint;
		$this->_email_address_string = $in_email_address_string;
		$this->_login_string = $in_login_string;
		$this->_password_string = $in_password_string;
		$this->_last_access = $in_last_access;
	}
	
	/*******************************************************************/
	/** \brief Returns true if the user is enabled (levels 1-3)
		
		\returns a Boolean. true if enabled, false if not.
	*/
	function IsEnabled()
	{
		return ($this->_user_level_tinyint > 0) && ($this->_user_level_tinyint != _USER_LEVEL_DISABLED);
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the user ID as an integer.
		
		\returns an integer, containing the user ID.
	*/
	function GetID()
	{
		return $this->_id_bigint;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the user ID as an integer.
	*/
	function SetID(
					$in_user_id_bigint	///< An integer, with the user ID.
					)
	{
		$this->_id_bigint = $in_user_id_bigint;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the user level as an integer.
		
		\returns an integer, containing the user level.
			Values:
			- (1)	Server Administrator -There can only be one. The user ID is always 1.
					This administrator can edit all aspects of the server.
			- (2)	Service Body Administrator -There can only be one per Service Body
					A Service Body Administrator can edit all meetings from the Service Body
					that assigns the user as its administrator. This user can also assign
					other Service Body Administrators or Editors as users able to edit
					meetings belonging to the Service Body.
					A Service Body Administrator can also edit Service Body information.
					NOTE: A Service Body Administrator only has extra rights when assigned
					to a specific Service Body. If assigned as an Editor on another Service
					Body, they do not have rights beyond those of an Editor.
			- (3)	Editor -There can be many.
					An Editor is assigned by a Service Body Administrator to edit meetings
					for that Service Body. They can only edit meetings; not users or
					Service Bodies.
					Making a user an editor (or a Service Body Administrator) doesn't
					automatically give them any rights. They must first be assigned to a
					Service Body, either by the Server Administrator (Service Body Administrators),
					or by a Service Body Administrator (Editors).
			- (4)	User Disabled.
			- (5)	Observer -User can see private data in meetings for which it is authorized.
	*/
	function GetUserLevel()
	{
		// We reload ourselves from the database first, just to avoid shenanigans...
		$this->RestoreFromDB();
		return $this->_user_level_tinyint;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the user level.
		Attempts to set the user level to 1 for users other than User 1 will fail.
	
		\returns true if successful, false otherwise.
	*/
	function SetUserLevel(
							$in_user_level_tinyint	/**< \brief The user level (an integer).
							
														Values:
														- (1)	Server Administrator -There can only be one. The user ID is always 1.
																This administrator can edit all aspects of the server.
														- (2)	Service Body Administrator -There can only be one per Service Body
																A Service Body Administrator can edit all meetings from the Service Body
																that assigns the user as its administrator. This user can also assign
																other Service Body Administrators or Editors as users able to edit
																meetings belonging to the Service Body.
																A Service Body Administrator can also edit Service Body information.
																NOTE: A Service Body Administrator only has extra rights when assigned
																to a specific Service Body. If assigned as an Editor on another Service
																Body, they do not have rights beyond those of an Editor.
														- (3)	Editor -There can be many.
																An Editor is assigned by a Service Body Administrator to edit meetings
																for that Service Body. They can only edit meetings; not users or
																Service Bodies.
																Making a user an editor (or a Service Body Administrator) doesn't
																automatically give them any rights. They must first be assigned to a
																Service Body, either by the Server Administrator (Service Body Administrators),
																or by a Service Body Administrator (Editors).
														- (4)	User Disabled.
														- (5)	Observer -User can see private data in meetings for which it is authorized.
													*/
						)
	{
		if ( ($this->_user_level_tinyint == 1) && ($this->_id_bigint > 1) )
			{
			return false;
			}
		else
			{
			$this->_user_level_tinyint = $in_user_level_tinyint;
			return true;
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the user email address.
		
		\returns a string, containing the user email address.
	*/
	function GetEmailAddress()
	{
		return $this->_email_address_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the user email address.
	*/
	function SetEmailAddress(
							$in_email_address_string	///< A string, containing the user's email address.
							)
	{
		$this->_email_address_string = $in_email_address_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the user login.
		
		\returns a string, containing the user login.
	*/
	function GetLogin()
	{
		return $this->_login_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the userlogin.
	
		\returns true if successful, and false if not.
	*/
	function SetLogin(
						$in_login_string	///< A string, containing the user's login.
						)
	{
		$ret = false;
		
		if ( $in_login_string )
			{
			$users_obj = c_comdef_server::GetServer()->GetServerUsersObj();
			
			// We are not allowed to select a login that is already in use. The comparison
			// is case-insensitive.
			if ( $users_obj instanceof c_comdef_users )
				{
				$obj_array = $users_obj->GetUsersArray();
				
				if ( is_array ( $obj_array ) )
					{
					$ret = true;
					
					foreach ( $obj_array as $one_user )
						{
						// We don't worry if this is our own object.
						if ( $one_user->GetID() != $this->GetID() )
							{
							if ( strtolower ( $one_user->GetLogin() ) == strtolower ( $in_login_string ) )
								{
								$ret = false;
								break;
								}
							}
						}
					
					// If we went through without a match, we change the login.
					if ( $ret )
						{
						$this->_login_string = $in_login_string;
						}
					}
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief See if this is the given user by login and password.
	
		The login is case-insensitive, but the password is not.
		
		\returns true, if so, false if not.
	*/
	function IsUser(
					$in_login_string,		///< A string, containing the user's login.
					$in_password_string,	///< A string, containing the user's password, in encrypted form, or unencrypted, if $in_pw_raw is true.
					$in_pw_raw = false		///< A Boolean, true if the password has not been encrypted.
					)
	{
		$login_match = (strcasecmp ( $in_login_string, $this->GetLogin() ) == 0);
		
		// See if we need to encrypt the password.
		if ( $in_pw_raw )
			{
			$in_password_string = FullCrypt ( $in_password_string );
			}
		
		$password_match = (strcmp( $this->GetPassword(), $in_password_string ) == 0);
		
		return $login_match && $password_match;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the user password, in encrypted form.
		
		\returns a string, containing the user password, as an encrypted hash.
	*/
	function GetPassword()
	{
		return $this->_password_string;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the password, as an encrypted string.
	*/
	function SetPassword(
						$in_password_string	///< A string, containing the user's password, in encrypted form.
						)
	{
		if ( trim ( $in_password_string ) )
			{
			$this->_password_string = trim ( $in_password_string );
			}
		else
			{
			return null;
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the password, encrypting it.
	
		\returns a string, containing the encrypted password. Returns null if none was provided.
	*/
	function SetNewPassword(
						$in_password_unencrypted_string	///< A string, containing the user's password, in unencrypted form.
						)
	{
		if ( trim ( $in_password_unencrypted_string ) )
			{
			$this->SetPassword ( FullCrypt ( trim ( $in_password_unencrypted_string ) ), $this->GetPassword() );

			return $this->GetPassword();
			}
		else
			{
			return null;
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor - Gets the last access time.
	
		\returns an epoch time that contains the last access time.
	*/
	function GetLastAccess ( )
	{
		return $this->_last_access;
	}
	
	/*******************************************************************/
	/** \brief Simply sets the last access time to now.
	*/
	function SetLastAccess (
							$in_time = null	///< An epoch time. If not provided, now is used.
							)
	{
		$this->_last_access = (null != $in_time) ? $in_time : time();
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
								$this->_user_level_tinyint,
								$this->_email_address_string,
								$this->_login_string,
								$this->_password_string,
								$this->_last_access,
								$this->GetLocalName(),
								$this->GetLocalDescription(),
								$this->GetLocalLang()
								);
		
		return serialize ( $serialize_array );
	}
	
	/*******************************************************************/
	/** \brief This takes the serialized table, and instantiates a
		new object from it.
		
		\returns a new instance of c_comdef_user, set up according to
		the serialized data passed in.
	*/
	static function UnserializeObject(
									$in_parent,			///< The parent object.
									$serialized_string	///< A string containing the serialized data.
									)
	{
		list ( 	$_id_bigint,
				$_user_level_tinyint,
				$_email_address_string,
				$_login_string,
				$_password_string,
				$_last_access,
				$_local_name,
				$_local_description,
				$_local_lang ) = unserialize ( $serialized_string );
				
		return new c_comdef_user ( $in_parent, $_id_bigint, $_user_level_tinyint, $_email_address_string, $_login_string, $_password_string, $_local_lang, $_local_name, $_local_description, $_last_access );
	}
	
	/*******************************************************************/
	/** \brief Test to see if a user is allowed to edit an instance (change the data).
	
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
			$in_user_object =& c_comdef_server::GetCurrentUserObj();
			}
		
		// We clone, in case changes have been made, and we don't want to screw them up.
		$in_user_clone = clone $in_user_object;

		// If it isn't a user object, we fail right there.
		if ( $in_user_clone instanceof c_comdef_user )
			{
			$in_user_clone->RestoreFromDB();	// The reason you do this, is to ensure that the user wasn't modified "live." It's a security precaution.
			// Only the server admin can edit users. However, users can edit themselves.
			if ( ($in_user_clone->GetUserLevel() != _USER_LEVEL_DISABLED) && ($in_user_clone->GetUserLevel() != _USER_LEVEL_OBSERVER) && (($in_user_clone->GetID() == $this->GetID()) || c_comdef_server::IsUserServerAdmin()) )
				{
				$ret = true;
				}
			
			$in_user_clone = null;
			}
		
		return $ret;
	}
};
?>