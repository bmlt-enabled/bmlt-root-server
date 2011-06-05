<?php
/***********************************************************************/
/** \file	c_comdef_format.class.php
	\brief	The class file for the c_comdef_format class.
    
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
/** \class c_comdef_format
	\brief A Class for Format Codes

	This class handles the model for the NA Meeting Format Codes. The
	codes are stored one code per language per instance of this class.
	If codes are related (Same code, different languages), then you should
	give them all the same shared ID.
***********************************************************************/
class c_comdef_format extends t_comdef_world_type implements i_comdef_db_stored, i_comdef_serialized, i_comdef_auth
{
	///	The shared ID (for multiple instances of the same format code).
	var	$_shared_id_bigint = null;
	
	/** \brief This is the type of format
		- An enumerated string value, representing the classification of the format:
			- "FC1" -Meeting Format (Speaker, Book Study, etc.)
			- "FC2" -Location Code (Wheelchair Accessible, Limited Parking, etc.)
			- "FC3" -Attendance Restriction (Men Only, Closed, Open, No Children, etc.)  */
	var	$_format_type_string = null;
	
	/// The Format Key, in Text Form.
	var	$_key_string = null;
	
	/// The Format Icon, as a 64-pixel-square Icon, in GIF format.
	var	$_icon_blob = null;
	
	/// This will be an array of the format types (localized).
	var $_format_codes = null;
	
	/*******************************************************************/
	/** \brief Updates the DB to the current values of this instance.
		(replacing current values of the DB).
		
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
			// We take a snapshot of the format as it currently sits in the database as a "before" image.
			$before = null;
			$before_id = null;
			$before_lang = null;
			$before_obj = c_comdef_server::GetOneFormat ( $this->GetSharedID(), $this->GetLocalLang() );
			
			if ( $before_obj instanceof c_comdef_format )
				{
				$before = $before_obj->SerializeObject();
				$before_id = $before_obj->GetSharedID();
				$before_lang = $before_obj->GetLocalLang();
				$before_obj = null;
				}
			
			try
				{
				$this->DeleteFromDB_NoRecord();
				
				/// Set the values for this instance, to be stored in the database.
				$record = array();
				$record['shared_id_bigint'] = intval ( $this->GetSharedID() );
				$record['format_type_enum'] = $this->GetFormatType();
				$record['key_string'] = $this->GetKey();
				$record['icon_blob'] = $this->GetIcon();
				$record['worldid_mixed'] = $this->GetWorldID();
				$record['lang_enum'] = $this->GetLocalLang();
				$record['name_string'] = $this->GetLocalName();
				$record['description_string'] = $this->GetLocalDescription();
	
				$first = true;	///< This is used to keep track of the first iteration, to prevent a comma before it.
				$vals = array();	///< This is the prepared statement value array.

				/// Create a new entry.
				$updateSQL = "INSERT INTO `".c_comdef_server::GetFormatTableName_obj()."` (";
				while ( list ( $key, $value ) = each ( $record ) )
					{
					if ( !$first )
						{
						$updateSQL .= ",";
						}
					else
						{
						$first = false;
						}
					$updateSQL .= "`".$key."`";
					}
				
				reset ( $record );
				
				$updateSQL .= ") VALUES (";

				$first = true;
				while ( list ( $key, $value ) = each ( $record ) )
					{
					if ( !$first )
						{
						$updateSQL .= ",";
						}
					else
						{
						$first = false;
						}
					/// We give the prepared statement a token, that will be filled by a value.
					$updateSQL .= ":$key";
					/// We give the value by declaring an associative array element with the token name.
					$vals[':'.$key] = $value;
					}
				$updateSQL .= ")";
				c_comdef_dbsingleton::preparedExec($updateSQL,$vals);
				
				$after = $this->SerializeObject();
				$after_id = $this->GetSharedID();
				$after_lang = $this->GetLocalLang();
				
				$cType = (true == $is_rollback) ? 'comdef_change_type_rollback' : ((null != $before) ? 'comdef_change_type_change' : 'comdef_change_type_new');
				
				c_comdef_server::AddNewChange ( $user->GetID(), $cType, null, $before, $after, 'c_comdef_format', $before_id, $after_id, $before_lang, $after_lang );
				
				$ret = true;
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_format::UpdateToDB()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Deletes this instance from the database without recording it.
		
		\returns true, if the delete was successful. False, otherwise.
		
		\throws a PDOException if there is a problem.
	*/
	function DeleteFromDB_NoRecord()
	{
		$ret = false;
		
		if ( $this->UserCanEdit() )
			{
			try
				{
				$sql = "DELETE FROM `".c_comdef_server::GetFormatTableName_obj()."` WHERE shared_id_bigint=? AND lang_enum=?";
				c_comdef_dbsingleton::preparedExec($sql, array ( $this->GetSharedID(), $this->GetLocalLang() ) );
				$ret = true;
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_format::DeleteFromDB_NoRecord()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Deletes this instance from the database.
		
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
			$before_obj = c_comdef_server::GetOneFormat ( $this->GetSharedID(), $this->GetLocalLang() );
			
			if ( $before_obj instanceof c_comdef_format )
				{
				$before = $before_obj->SerializeObject();
				$before_id = $before_obj->GetSharedID();
				$before_lang = $before_obj->GetLocalLang();
				$before_obj = null;
				}

			$ret = $this->DeleteFromDB_NoRecord();
			
			if ( $ret )
				{
				c_comdef_server::AddNewChange ( $user->GetID(), 'comdef_change_type_delete', $this->GetSharedID(), $before, null, 'c_comdef_format', $before_id, null, $before_lang, null );
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
			$sql = "SELECT * FROM `".c_comdef_server::GetFormatTableName_obj()."` WHERE shared_id_bigint=? AND lang_enum=?"; 
			
			$vals = array ( intval ( $this->GetLocalID(), $this->GetLocalLang() ) );
			
			$rows = c_comdef_dbsingleton::preparedQuery($sql,$vals);
			
			if ( is_array ( $rows ) && count ( $rows ) )
				{
				$this->SetSharedID($rs['shared_id_bigint'] );
				$this->SetFormatType($rs['format_type_enum'] );
				$this->SetKey($rs['key_string'] );
				$this->SetIcon($rs['icon_blob'] );
				$this->SetWorldID($rs['worldid_mixed'] );
				$this->SetLocalLang($rs['lang_enum'] );
				$this->SetLocalName($rs['name_string'] );
				$this->SetLocalDescription($rs['description_string'] );
				}
			else
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_format::RestoreFromDB()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_format::RestoreFromDB()!<br />";
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
							&$in_parent_obj,				///< The object that "owns" this instance.
							$in_shared_id_bigint=null,		///< The shared ID (If this is the same as other formats -used for different languages).
							$in_format_type_string=null,	/**<	\brief The format code type
																	- An enumerated string value, representing the classification of the format:
																		- "FC1" -Meeting Format (Speaker, Book Study, etc.)
																		- "FC2" -Location Code (Wheelchair Accessible, Limited Parking, etc.)
																		- "FC3" -Attendance Restriction (Men Only, Closed, Open, No Children, etc.)  */
							$in_key_string=null,			///< The format Key, as a text string (1-3 Characters).
							$in_icon_blob=null,				///< The format icon, as 64-pixel-square GIF image data.
							
							/// The t_comdef_world_type Class:
							$in_worldid_mixed=null,			///< The NAWS ID for this format (can be the same as other format IDs)
							
							/// The t_comdef_local_type Class
							$in_lang_enum=null,				///< The language to be used for this instance (if null, the server language is used).
							$in_name_string=null,			///< The name of this instance
							$in_description_string=null		///< A verbose description
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
		
		$this->SetSharedID($in_shared_id_bigint);
		$this->SetFormatType($in_format_type_string);
		$this->SetKey($in_key_string);
		$this->SetIcon($in_icon_blob);
		
		$this->SetParentObj($in_parent_obj);
		
		$this->SetLocalLang($in_lang_enum);
		$this->SetLocalName($in_name_string);
		$this->SetLocalDescription($in_description_string);
		
		$this->SetWorldID($in_worldid_mixed);

		/// This gets us the format codes, with their localized descriptions.
		$file_path = dirname ( __FILE__ ).'/../config/lang/'.$in_lang_enum.'/format_codes.inc.php';
		
		require ( $file_path );

		$this->SetFormatCodes ( $comdef_format_types );
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Set the shared ID
	
	The shared ID may be the same for a number of different instances. It is how
	we link different language versions of the same format together.
	*/
	function SetSharedID(
						$in_shared_id_bigint	///< The shared ID (If this is the same as other formats -used for different languages).
						)
	{
		$this->_shared_id_bigint = null;
		$this->_shared_id_bigint = $in_shared_id_bigint;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Get a reference to the shared ID.
	
		@returns The _shared_id_bigint data member, as a reference.
	*/
	function GetSharedID()
	{
		return $this->_shared_id_bigint;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Sets the format type code.
	*/
	function SetFormatType(
							$in_format_type_string /**<	- An enumerated string value, representing the classification of the format:
										- "FC1" -Meeting Format (Speaker, Book Study, etc.)
										- "FC2" -Location Code (Wheelchair Accessible, Limited Parking, etc.)
										- "FC3" -Attendance Restriction (Men Only, Closed, Open, No Children, etc.)
										*/
							)
	{
		$this->_format_type_string = null;
		$this->_format_type_string = $in_format_type_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Returns a reference to the format type code.
	
		@returns The _format_type_string data member, as a reference.
	*/
	function &GetFormatType()
	{
		return $this->_format_type_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Sets the format key (the 1-3 letter code that represents the format).
	*/
	function SetKey(
					$in_key_string	///< The format Key, as a text string (1-3 Characters)
					)
	{
		$this->_key_string = null;
		$this->_key_string = $in_key_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Returns a reference to the _key_string data member
	
		@returns The _key_string data member, as a reference.
	*/
	function &GetKey()
	{
		return $this->_key_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Sets a 64-pixel-square image (as a GIF), to be used to indicate the format.
	*/
	function SetIcon(
					$in_icon_blob	///< The GIF image, as a stream of binary data.
					)
	{
		$this->_icon_blob = null;
		$this->_icon_blob = $in_icon_blob;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Returns a reference to the _icon_blob data member
	
		@returns The _icon_blob data member, as a reference.
	*/
	function &GetIcon()
	{
		return $this->_icon_blob;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Sets a brief description string for the format, in whatever language has been selected.
	*/
	function SetFormatCodes(
						$in_format_code_array	///< An associative array, with the format codes.
						)
	{
		$this->_format_codes = null;
		$this->_format_codes = $in_format_code_array;	///< Make sure we copy it.
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Returns a reference to the _format_codes data member
	
		@returns The _format_codes data member, as a reference.
	*/
	function &GetFormatCodes()
	{
		return $this->_format_codes;
	}
	
	/*******************************************************************/
	/** \brief Returns a storable serialization of the object, as a string.
		
		This is only used for the changes, as the serialized string may not
		be easily searched.
		
		\returns an array, containing the object in serialized form.
	*/
	function SerializeObject()
	{
		$s_array['shared_id_bigint'] = $this->GetSharedID();
		$s_array['format_type_string'] = $this->GetFormatType();
		$s_array['key_string'] = $this->GetKey();
		$s_array['icon_blob'] = $this->GetIcon();
		$s_array['worldid_mixed'] = $this->GetWorldID();
		$s_array['lang_enum'] = $this->GetLocalLang();
		$s_array['name_string'] = $this->GetLocalName();
		$s_array['description_string'] = $this->GetLocalDescription();
		
		return serialize ( $s_array );
	}
	
	/*******************************************************************/
	/** \brief This takes the serialized data, and instantiates a
		new object from it.
		
		\returns a new instance of c_comdef_format, set up according to
		the serialized data passed in.
	*/
	static function UnserializeObject( $in_parent,		///< The parent object.
									$serialized_array	///< An array containing the serialized data.
									)
	{
		$s_array = unserialize ( $serialized_array );
		
		return new c_comdef_format ( $in_parent, $s_array['shared_id_bigint'], $s_array['format_type_string'],
									$s_array['key_string'], $s_array['icon_blob'], $s_array['worldid_mixed'],
									$s_array['lang_enum'], $s_array['name_string'], $s_array['description_string'] );
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
			$in_user_object = c_comdef_server::GetCurrentUserObj();
			}
		
		// If it isn't a user object, we fail right there.
		if ( $in_user_object instanceof c_comdef_user )
			{
			$in_user_object->RestoreFromDB();	// The reason you do this, is to ensure that the user wasn't modified "live." It's a security precaution.
			// Only the server admin can edit formats.
			if ( c_comdef_server::IsUserServerAdmin() )
				{
				$ret = true;
				}
			}
		
		return $ret;
	}
};
?>