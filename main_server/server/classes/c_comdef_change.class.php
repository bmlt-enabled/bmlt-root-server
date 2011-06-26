<?php
/***********************************************************************/
/** \file	c_comdef_change.class.php
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
require_once ( dirname ( __FILE__ )."/c_comdef_user.class.php" );
require_once ( dirname ( __FILE__ )."/c_comdef_format.class.php" );
require_once ( dirname ( __FILE__ )."/c_comdef_meeting.class.php" );
require_once ( dirname ( __FILE__ )."/c_comdef_service_body.class.php" );

/***********************************************************************/
/** \class c_comdef_change
	\brief A Class for Change Record Objects

	This class handles the Model for the way we record changes in the CoMDEF
	architecture.
	A change record has a record of who made the change, when it was made,
	notes on the change, and a "before" and "after" snapshot of the changed
	entity. Meetings, Users, Service bodies or formats can have changes recorded. If a new
	entity is created, the "before" is null. If an entity is deleted, then
	the "after" snapshot is null. Snapshots are serialized objects.
***********************************************************************/
class c_comdef_change extends t_comdef_local_type implements i_comdef_db_stored
{
	/// An integer, containing the unique ID of this change record.
	private	$change_id_bigint = null;
	/// A serialized object (either c_comdef_format or c_comdef_meeting) before the change
	private	$before_object_string = null;
	/// The same object, serialized after the change
	private $after_object_string = null;
	/// The ID of the Before Object
	private	$before_object_id_bigint = null;
	/// The ID of the After Object
	private	$after_object_id_bigint = null;
	/// The Language of the Before Object
	private	$before_object_lang_enum = null;
	/// The Language of the After Object
	private	$after_object_lang_enum = null;
	/// The ID of the user making the change.
	private	$change_user_int = null;
	/// The ID of the Service Body on behalf of which the user is acting.
	private	$change_service_body_int = null;
	/// The date and time (as a time() seconds value) when the change was made.
	private	$change_datetime_int = null;
	/// The name of the object class that has been serialized.
	private	$object_class_string = null;
	/** The type of change this was
		Can be:
			- 'comdef_change_type_new' - New object
			- 'comdef_change_type_delete' - Deleted the object
			- 'comdef_change_type_change' - Changed existing object
			- 'comdef_change_type_rollback' - Rolled existing object back to a previous version
	*/
	private	$change_type_enum = null;
	
	/*******************************************************************/
	/** \brief Updates or adds this instance to the database.
		
		\throws a PDOException if there is a problem.
	*/
	function UpdateToDB()
	{
		$ar = array();
		try
			{
			// We will delete any previous entries.
			$this->DeleteFromDB();
			
			$sql = "INSERT INTO `".c_comdef_server::GetChangesTableName_obj()."` (";
			if ( $this->change_id_bigint)
				{
				$sql .= "`id_bigint`,";
				}
			$sql .= "`user_id_bigint`,`service_body_id_bigint`,`lang_enum`,`object_class_string`,`change_name_string`,`change_description_text`,`before_object`,`after_object`,   `before_id_bigint`, `before_lang_enum`, `after_id_bigint`, `after_lang_enum`, `change_type_enum`) VALUES (";
			if ( $this->change_id_bigint )
				{
				$sql .= "?,";
				array_push ( $ar, $this->change_id_bigint );
				}
			$sql .= "?,?,?,?,?,?,?,?,?,?,?,?,?)";
			array_push ( $ar, $this->change_user_int );
			array_push ( $ar, $this->change_service_body_int );
			array_push ( $ar, $this->GetLocalLang() );
			array_push ( $ar, $this->object_class_string );
			array_push ( $ar, $this->GetLocalName() );
			array_push ( $ar, $this->GetLocalDescription() );
			array_push ( $ar, $this->before_object_string );
			array_push ( $ar, $this->after_object_string );
			array_push ( $ar, $this->before_object_id_bigint );
			array_push ( $ar, $this->before_object_lang_enum );
			array_push ( $ar, $this->after_object_id_bigint );
			array_push ( $ar, $this->after_object_lang_enum );
			array_push ( $ar, $this->change_type_enum );

			c_comdef_dbsingleton::preparedExec($sql, $ar );
			// If this is a new user, then we'll need to fetch the ID.
			if ( !$this->change_id_bigint )
				{
				$sql = "SELECT LAST_INSERT_ID()";
				$rows = c_comdef_dbsingleton::preparedQuery($sql);
				if ( is_array ( $rows ) && count ( $rows ) )
					{
					$this->change_id_bigint = intval ( $rows[0]['last_insert_id()'] );
					}
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_change::UpdateToDB()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
	}
	
	/*******************************************************************/
	/** \brief Deletes this instance from the database.
		
		\throws a PDOException if there is a problem.
	*/
	function DeleteFromDB()
	{
		try
			{
			$sql = "DELETE FROM `".c_comdef_server::GetChangesTableName_obj()."` WHERE id_bigint=?";
			c_comdef_dbsingleton::preparedExec($sql, array ( $this->GetID() ) );
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_change::DeleteFromDB()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
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
			$sql = "SELECT * FROM `".c_comdef_server::GetChangesTableName_obj()."` WHERE id_bigint=? LIMIT 1"; 
			
			$rows = c_comdef_dbsingleton::preparedQuery($sql, array ( $this->change_id_bigint ) );
			
			if ( is_array ( $rows ) && count ( $rows ) )
				{
				$this->change_user_int = $rows[0]['user_id_bigint'];
				$this->change_service_body_int = $rows[0]['service_body_id_bigint'];
				$this->change_datetime_int = $rows[0]['change_date'];
				$this->object_class_string = $rows[0]['object_class_string'];
				$this->before_object_string = $rows[0]['before_object'];
				$this->after_object_string = $rows[0]['after_object'];
				$this->SetLocalName($rows[0]['change_name_string']);
				$this->SetLocalDescription($rows[0]['change_description_text']);
				$this->SetLocalLang($rows[0]['lang_enum']);
				$this->before_object_id_bigint = $rows[0]['before_id_bigint'];
				$this->before_object_lang_enum = $rows[0]['before_lang_enum'];
				$this->after_object_id_bigint = $rows[0]['after_id_bigint'];
				$this->after_object_lang_enum = $rows[0]['after_lang_enum'];
				$this->change_type_enum = $rows[0]['change_type_enum'];
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_change::RestoreFromDB()!<br />";
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
						$in_parent_obj,							///< A reference to the parent object for this change object (usually an instance of c_comdef_changes)
						$in_change_type_enum,					/**< Enum. The type of change.
																	Can be:
																		- 'comdef_change_type_new' - New object
																		- 'comdef_change_type_delete' - Deleted the object
																		- 'comdef_change_type_change' - Changed existing object
																		- 'comdef_change_type_rollback' - Rolled existing object back to a previous version
																*/
						$in_user_id_int,						///< Integer. The ID of the user that made this change.
						$in_service_body_id_bigint,				///< Integer. The Service Body the user was acting for.
						$in_before_string,						///< String (serialized object of either c_comdef_format or c_comdef_meeting). The object as it was immediately BEFORE the change.
						$in_after_string,						///< String (serialized object of either c_comdef_format or c_comdef_meeting). The object as it was immediately AFTER the change.
						$in_object_class_string,				///< A string, containing the class of the object[s] being passed in as serialized.
						$in_before_obj_id_bigint,				///< Integer, The ID of the Before Object.
						$in_after_obj_id_bigint,				///< Integer, The ID of the After Object.
						$in_before_obj_lang_enum = null,		///< Enum, the language of the Before Object.
						$in_after_obj_lang_enum = null,			///< Enum. The language of the After Object.
						$in_change_id_int = null,				///< Integer. The unique ID of this change record. If left null, the DB interaction will assign one.
						$in_change_name_string = null,			///< String. A brief header (1-line description) of the change (optional).
						$in_change_description_string = null,	///< String. A more detailed description of the change (optional).
						$in_lang_enum = null,					///< Enum. The language to record the change as. If null, the server or global language will be used.
						$in_datetime_int = null					///< Integer containing the UNIX epoch (time() value) of the datetime the change was made. If null, the current time will be used.
						)
	{
		/// This is the server language.
		if ( null == $in_lang_enum )
			{
			$in_lang_enum = c_comdef_server::GetServer()->GetLocalLang();
			}
		
		/// This is the global language.
		if ( null == $in_lang_enum )
			{
			global	$comdef_global_language;
		
			$in_lang_enum = $comdef_global_language;
			}
		
		/// Should never be necessary.
		if ( null == $in_lang_enum )
			{
			$in_lang_enum = "en";
			}
		
		if ( null == $in_datetime_int )
			{
			$in_datetime_int = time();
			}
		
		/// If no languages are given for the input objects, we use the main language.
		if ( null == $in_before_obj_lang_enum )
			{
			$in_before_obj_lang_enum = $in_lang_enum;
			}
		
		if ( null == $in_after_obj_lang_enum )
			{
			$in_after_obj_lang_enum = $in_before_obj_lang_enum;
			}
		
		$this->change_id_bigint = $in_change_id_int;
		$this->change_user_int = $in_user_id_int;
		$this->change_service_body_int = $in_service_body_id_bigint;
		$this->change_datetime_int = $in_datetime_int;
		$this->object_class_string = $in_object_class_string;
		$this->before_object_string = $in_before_string;
		$this->after_object_string = $in_after_string;
		$this->SetLocalName( $in_change_name_string );
		$this->SetLocalDescription( $in_change_description_string );
		$this->SetLocalLang( $in_lang_enum );
		$this->before_object_id_bigint = $in_before_obj_id_bigint;
		$this->before_object_lang_enum = $in_before_obj_lang_enum;
		$this->after_object_id_bigint = $in_after_obj_id_bigint;
		$this->after_object_lang_enum = $in_after_obj_lang_enum;
		$this->change_type_enum = $in_change_type_enum;
	}
	
	/*******************************************************************/
	/**	\brief Accessor. Get the Object Class of this change record.
		
		\returns a string. The object classname.
	*/
	function GetObjectClass()
	{
		return $this->object_class_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor. Get the ID of this change record.
		
		\returns an integer. The ID of the change record.
	*/
	function GetID()
	{
		return $this->change_id_bigint;
	}
	
	/*******************************************************************/
	/**	\brief Accessor. Get the type of this change record.
		
		\returns an enum. The type of change.
			Can be:
				- 'comdef_change_type_new' - New object
				- 'comdef_change_type_delete' - Deleted the object
				- 'comdef_change_type_change' - Changed existing object
				- 'comdef_change_type_rollback' - Rolled existing object back to a previous version
	*/
	function GetChangeType()
	{
		return $this->change_type_enum;
	}
	
	/*******************************************************************/
	/**	\brief Accessor. Get the ID of the user that created this change record.
		
		\returns an integer. The ID of the user.
	*/
	function GetUserID()
	{
		return $this->change_user_int;
	}
	
	/*******************************************************************/
	/**	\brief Accessor. Get the ID of the Service Body that was represented
		by the user that created this change record.
		
		\returns an integer. The ID of the Service Body.
	*/
	function GetServiceBodyID()
	{
		return $this->change_service_body_int;
	}

	/*******************************************************************/
	/**	\brief Accessor. Return the ID of the "before" object.
		
		\returns an integer.
	*/
	function GetBeforeObjectID()
	{
		return $this->before_object_id_bigint;
	}

	/*******************************************************************/
	/**	\brief Accessor. Return the ID of the "after" object.
		
		\returns an integer.
	*/
	function GetAfterObjectID()
	{
		return $this->after_object_id_bigint;
	}

	/*******************************************************************/
	/**	\brief Accessor. Return the date/time the change was made.
		
		\returns an integer. The UNIX epoch time (time() return) of the change.
	*/
	function GetChangeDate()
	{
		return $this->change_datetime_int;
	}
	
	/*******************************************************************/
	/**	\brief Accessor. Return the before object in serialized form.
		
		\returns a binary string, containing the serialized data.
	*/
	function GetBeforeSerialized()
	{
		return $this->before_object_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor. Return the after object in serialized form.
		
		\returns a binary string, containing the serialized data.
	*/
	function GetAfterSerialized()
	{
		return $this->after_object_string;
	}
	
	/*******************************************************************/
	/**	\brief Return the before object in object form.
		
		\returns a new c_comdef_meeting, c_comdef_user, c_comdef_service_body or c_comdef_format object. Null if failed or there is no object.
	*/
	function GetBeforeObject(
							$in_parent_object = null	///< A reference to the parent object for the new object. If null, the server will be used.
							)
	{
		$ret = null;
		
		if ( null == $in_parent_object )
			{
			$in_parent_object = c_comdef_server::GetServer();
			}
			
		if ( null != $this->before_object_string )
			{
			switch ( $this->object_class_string )
				{
				case 'c_comdef_meeting':
					$ret = c_comdef_meeting::UnserializeObject ( $in_parent_object, $this->before_object_string );
					$current = c_comdef_server::GetOneMeeting ( $ret->GetID() );
					if ( null == $current )
						{
						// Restoring deleted meetings always unpublishes them before restoring them.
						$ret->SetPublished ( 0 );
						}
				break;
				
				case 'c_comdef_format':
					$ret = c_comdef_format::UnserializeObject ( $in_parent_object, $this->before_object_string );
				break;
				
				case 'c_comdef_user':
					$ret = c_comdef_user::UnserializeObject ( $in_parent_object, $this->before_object_string );
				break;
				
				case 'c_comdef_service_body':
					$ret = c_comdef_user::UnserializeObject ( $in_parent_object, $this->before_object_string );
				break;
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Return the after object in object form.
		
		\returns a new c_comdef_meeting, c_comdef_user, c_comdef_service_body or c_comdef_format object. Null if failed or there is no object.
	*/
	function GetAfterObject(
							$in_parent_object = null	///< A reference to the parent object for the new object. If null, the server will be used.
							)
	{
		$ret = null;
		
		if ( null == $in_parent_object )
			{
			$in_parent_object = c_comdef_server::GetServer();
			}
		
		if ( null != $this->after_object_string )
			{
			switch ( $this->object_class_string )
				{
				case 'c_comdef_meeting':
					$ret = c_comdef_meeting::UnserializeObject ( $in_parent_object, $this->after_object_string );
				break;
				
				case 'c_comdef_format':
					$ret = c_comdef_format::UnserializeObject ( $in_parent_object, $this->after_object_string );
				break;
				
				case 'c_comdef_user':
					$ret = c_comdef_user::UnserializeObject ( $in_parent_object, $this->after_object_string );
				break;
				
				case 'c_comdef_service_body':
					$ret = c_comdef_user::UnserializeObject ( $in_parent_object, $this->after_object_string );
				break;
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Rolls the subject of this change to the "before" version.
		
		This will only work if there is a "before," and if the current
		user is cleared to edit both the before and current versions of the
		item.
		
		\returns true, if successful. False, otherwise.
	*/
	function Rollback()
	{
		$ret = false;
		
		// Start off by getting the prior version of the object.
		$before = $this->GetBeforeObject();
		
		$current = false;
		
		switch ( $this->object_class_string )
			{
			case 'c_comdef_meeting':
				$id = $before->GetID();
				$current = c_comdef_server::GetOneMeeting ( $id );
			break;
			
			case 'c_comdef_format':
				$id = $before->GetSharedID();
				$lang = $before->GetLocalLang();
				$current = c_comdef_server::GetOneMeeting ( $id, $lang );
			break;
			
			case 'c_comdef_user':
				$id = $before->GetID();
				$current = c_comdef_server::GetOneUser ( $id );
			break;
			
			case 'c_comdef_service_body':
				$id = $before->GetID();
				$current = c_comdef_server::GetOneServiceBody ( $id );
			break;
			}
		
		// We can't do a rollback unless we pass both these bars.
		if ( (null != $before) && ($before->UserCanEdit()) )
			{
			// Either there is no current (the object was deleted), or the user is allowed to mess with the current version.
			if ( (null == $current) || ((null != $current) && ($current->UserCanEdit())) )
				{
				$ret = $before->UpdateToDB(true);
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Return a string that describes the change.
		
		\returns a string array, describing the change, in fairly natural language.
	*/
	function DescribeChange()
	{
		$ret = "";
		
		// We build a macro, based upon the various parameters.
		$macro = "__THE_";
		switch ( $this->object_class_string )
			{
			case 'c_comdef_meeting':
				$macro .= "MEETING_";
			break;
			
			case 'c_comdef_format':
				$macro .= "FORMAT_";
			break;
			
			case 'c_comdef_user':
				$macro .= "USER_";
			break;
			
			case 'c_comdef_service_body':
				$macro .= "SERVICE_BODY_";
			break;
			}
		
		switch ( $this->change_type_enum )
			{
			case 'comdef_change_type_new':
				$macro .= "WAS_CREATED__";
			break;
			
			case 'comdef_change_type_delete':
				$macro .= "WAS_DELETED__";
			break;
			
			case 'comdef_change_type_change':
				$macro .= "WAS_CHANGED__";
			break;
			
			case 'comdef_change_type_rollback':
				$macro .= "WAS_ROLLED_BACK__";
			break;
			}
		
		if ( $macro != "__THE_" )
			{
			$localized_strings = c_comdef_server::GetLocalStrings();

			// This is a rather general description of the change.
			$ret['change_desc'] = $localized_strings['change_type_strings'][$macro];
			if ( $this->GetLocalDescription() )
				{
				$ret['description'] = $this->GetLocalDescription();
				}
			if ( $this->GetLocalName() )
				{
				$ret['name'] = $this->GetLocalName();
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Return an array of strings that describes the change in
				a fairly detailed manner.
		
		\returns an associative array, containing an item-by-item change description. Null if no description.
				- 'overall' (required) Contains the same description as returned in DescribeChange()
				- 'details' (optional) Contains an array of individual change items.
	*/
	function DetailedChangeDescription()
	{
		// We load the defines with the change phrases.
			
		$ret = null;
		
		$localized_strings = c_comdef_server::GetLocalStrings();

		switch ( $this->object_class_string )
			{
			// So far, the only type we are supporting is a meeting. The other types get generic answers.
			case 'c_comdef_meeting':
				switch ( $this->change_type_enum )
					{
					case 'comdef_change_type_new':
					case 'comdef_change_type_delete':
					case 'comdef_change_type_rollback':
						$ret['overall'] = $this->DescribeChange();
						$ret['details'] = $ret['overall'];
					break;
					
					// We only detail a change. The others get the generic ones.
					case 'comdef_change_type_change':
						$ret['overall'] = $this->DescribeChange();
						// The first thing that we do, is we get an instance of the before, and an instance of the after.
						$before = $this->GetBeforeObject();
						$after = $this->GetAfterObject();
						// We then get pointers to the meeting data for each.
						if ( ($before instanceof c_comdef_meeting) && ($after instanceof c_comdef_meeting) )
							{
							$before_data =& $before->GetMeetingData(); 
							$after_data =& $after->GetMeetingData();
							
							// Make sure we're kosher.
							if ( is_array ( $before_data ) && is_array ( $after_data ) )
								{
								// Get all the keys, for both arrays. This way, we sort through even deleted and added items.
								$key_array = null;
								
								// We do it this way, because we don't want repeats. array_merge is a bit of a "blunt instrument."
								foreach ( $before_data as $key => $value )
									{
									$key_array[$key] = 1;
									}
								
								foreach ( $after_data as $key => $value )
									{
									$key_array[$key] = 1;
									}
								
								if ( is_array ( $key_array ) )
									{
									$key_array = array_keys ( $key_array );

									// Okay, now we have an array of every key used in both objects. Time to look for changes.
									foreach ( $key_array as $array_key )
										{
										$before_value = null;
										$after_value = null;
										$before_value = isset ( $before_data[$array_key] ) ? $before_data[$array_key] : null;
										// This is a security measure. If the reader can't see BOTH the before and after, we show them neither.
										if ( isset ( $before_value['visibility'] ) && ($before_value['visibility'] == _VISIBILITY_NONE_) && (!$after->UserCanObserve() || !$before->UserCanObserve()) )
											{
											$before_value['value'] == $localized_strings['comdef_search_results_strings']['hidden_value'];
											}

										$after_value = isset ( $after_data[$array_key] ) ? $after_data[$array_key] : null;
										if ( isset ( $after_value['visibility'] ) && ($after_value['visibility'] == _VISIBILITY_NONE_) && (!$after->UserCanObserve() || !$before->UserCanObserve()) )
											{
											$after_value['value'] == $localized_strings['comdef_search_results_strings']['hidden_value'];
											}
										$prompt = $array_key;
										$change_string = null;
										
										if ( is_array ( $before_value ) )
											{
											if ( isset ( $before_value['prompt'] ) )
												{
												$prompt = $before_value['prompt'];
												}
											if ( isset ( $before_value['value'] ) )
												{
												$before_value = $before_value['value'];
												}
											}
											
										if ( is_array ( $after_value ) )
											{
											if ( isset ( $after_value['prompt'] ) )
												{
												$prompt = $after_value['prompt'];
												}
											if ( isset ( $after_value['value'] ) )
												{
												$after_value = $after_value['value'];
												}
											}
										
										if ( $prompt && (isset ( $before_value ) || isset ( $after_value )) )
											{
											if ( isset ( $before_value ) && !isset ( $after_value ) )	// Just deleted.
												{
												switch ( $array_key )
													{
													case	'published':
														$change_string = c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_unpublished'] ).$localized_strings['end_change_report'];
													break;
													
													default:
														$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_deleted'] ).$localized_strings['end_change_report'];
													break;
													}
												}
											elseif ( !isset ( $before_value ) && isset ( $after_value ) )	// Just added.
												{
												switch ( $array_key )
													{
													case	'email_contact':    // For security reasons, we don't display email conact changes as anything but "changed."
														$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed'] ).$localized_strings['end_change_report'];
													break;
													
													case	'published':
														if ( intval($after_value) != 0 )
															{
															$change_string = c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_published'] ).$localized_strings['end_change_report'];
															}
													break;
													
													// With these two values, we turn them into military time values and compare from there.
													case	'start_time':
													case	'duration_time':
														$prompt = $localized_strings['detailed_change_strings'][$array_key];
														$after_value_ar = explode ( ':', $after_value );
														$after_value = (intval ( $after_value_ar[0] ) * 100) + intval ( $after_value_ar[1] );
													
													default:
														$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_added_as'] ).' &quot;'.c_comdef_htmlspecialchars ( $after_value ).'&quot; '.$localized_strings['end_change_report'];
													break;
													}
												}
											else	// If it wasn't deleted or added, we need to dig a bit deeper to find the changes.
												{
												// Array values require even more digging.
												if ( is_array ( $before_value ) && is_array ( $after_value ) )
													{
													switch ( $array_key )
														{
														case	'formats':
															$before_keys = array();
															$after_keys = array();
															
															foreach ( $before_value as &$a_format_obj )
																{
																if ( $a_format_obj instanceof c_comdef_format )
																	{
																	array_push ( $before_keys, $a_format_obj->GetKey() );
																	}
																}
															
															foreach ( $after_value as &$a_format_obj )
																{
																if ( $a_format_obj instanceof c_comdef_format )
																	{
																	array_push ( $after_keys, $a_format_obj->GetKey() );
																	}
																}
															
															asort ( $before_keys );
															asort ( $after_keys );
															
															$before_value = join ( ', ', $before_keys );
															$after_value = join ( ', ', $after_keys );
															
															if ( $before_value != $after_value )
																{
																$change_string = c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['formats_prompt'] ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed_from'] ).' &quot;'.c_comdef_htmlspecialchars ( $before_value ).'&quot; '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['to'] ).' &quot;'.c_comdef_htmlspecialchars ( $after_value ).'&quot;'.$localized_strings['end_change_report'];
																}
														break;
																
														default:
															$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed'] ).$localized_strings['end_change_report'];
														break;
														}
													}
												else	// Otherwise, we simply compare the values to see if they are different.
													{
													// If the value changed, we create a record of the change.
													if ( $before_value != $after_value )
														{
														switch ( $array_key )
															{
                                                            case	'email_contact':    // For security reasons, we don't display email conact changes as anything but "changed."
                                                                $change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed'] ).$localized_strings['end_change_report'];
                                                            break;
													
															case	'published':
																if ( intval ( $before_value ) != intval ( $after_value ) )
																	{
																	$change_string = c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings'][((intval($after_value) != 0) ? 'was_published' : 'was_unpublished')] ).$localized_strings['end_change_report'];
																	}
															break;
															
															case	'weekday_tinyint':
																$prompt = $localized_strings['detailed_change_strings'][$array_key];
																$before_value = $localized_strings['weekdays'][max ( 0, intval ( $before_value ) -1 )];
																$after_value = $localized_strings['weekdays'][max ( 0, intval ( $after_value ) -1 )];
																$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed_from'] ).' '.c_comdef_htmlspecialchars ( $before_value ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['to'] ).' '.c_comdef_htmlspecialchars ( $after_value ).$localized_strings['end_change_report'];
															break;
															
															case	'service_body_bigint':
																$from_sb =& c_comdef_server::GetServer()->GetServiceBodyByIDObj ( intval ( $before_value ) );
																$to_sb =& c_comdef_server::GetServer()->GetServiceBodyByIDObj ( intval ( $after_value ) );
																if ( $from_sb instanceof c_comdef_service_body )
																	{
																	$before_value = $from_sb->GetLocalName();
																	}
																else
																	{
																	$before_value = $localized_strings['detailed_change_strings']['non_existent_service_body'];
																	}
																
																if ( $to_sb instanceof c_comdef_service_body )
																	{
																	$after_value = $to_sb->GetLocalName();
																	}
																else
																	{
																	$before_value = $localized_strings['detailed_change_strings']['non_existent_service_body'];
																	}

																$prompt = $localized_strings['detailed_change_strings']['sb_prompt'];
																$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $before_value ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['to'] ).' '.c_comdef_htmlspecialchars ( $after_value ).$localized_strings['end_change_report'];
															break;
															
															case	'lang_enum':
																$prompt = $localized_strings['detailed_change_strings'][$array_key];
																$file_path = dirname ( __FILE__ )."/../config/lang/$before_value/name.txt";
																
																if ( file_exists ( $file_path ) )
																	{
																	$before_value = trim ( file_get_contents ( $file_path ) );
																	}
																
																$file_path = dirname ( __FILE__ )."/../config/lang/$after_value/name.txt";
																
																if ( file_exists ( $file_path ) )
																	{
																	$before_value = trim ( file_get_contents ( $file_path ) );
																	}
																$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed_from'] ).' &quot;'.c_comdef_htmlspecialchars ( $before_value ).'&quot; '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['to'] ).' &quot;'.c_comdef_htmlspecialchars ( $after_value ).'&quot;'.$localized_strings['end_change_report'];
															break;

															case	'longitude':
															case	'latitude':
																$before_value = floatval ( $before_value );
																$after_value = floatval ( $after_value );
															case	'worldid_mixed':
															case	'shared_group_id_bigint':
																$prompt = $localized_strings['detailed_change_strings'][$array_key];
																$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed_from'] ).' &quot;'.c_comdef_htmlspecialchars ( $before_value ).'&quot; '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['to'] ).' &quot;'.c_comdef_htmlspecialchars ( $after_value ).'&quot;'.$localized_strings['end_change_report'];
															break;
															
															// With these two values, we turn them into military time values and compare from there.
															case	'start_time':
															case	'duration_time':
																$before_value = explode ( ':', $before_value );
																$after_value = explode ( ':', $after_value );
																$before_value = strval ( intval ( $before_value[0] ) ).':'.(intval ( $before_value[1] ) < 10 ? '0' : '').strval ( intval ( $before_value[1] ) );
																$after_value = strval ( intval ( $after_value[0] ) ).':'.(intval ( $after_value[1] ) < 10 ? '0' : '').strval ( intval ( $after_value[1] ) );
																
																if ( $before_value == $after_value )
																	{
																	break;
																	}
															case	'id_bigint':
																$prompt = $localized_strings['detailed_change_strings'][$array_key];
															default:
																$change_string = c_comdef_htmlspecialchars ( $prompt ).' '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['was_changed_from'] ).' &quot;'.c_comdef_htmlspecialchars ( $before_value ).'&quot; '.c_comdef_htmlspecialchars ( $localized_strings['detailed_change_strings']['to'] ).' &quot;'.c_comdef_htmlspecialchars ( $after_value ).'&quot;'.$localized_strings['end_change_report'];
															break;
															}
														}
													}
												}
											
											if ( $change_string )
												{
												if ( !isset ( $ret['details'] ) || !is_array ( $ret['details'] ) )
													{
													$ret['details'] = array();
													}
												
												array_push ( $ret['details'], $change_string );
												}
											}
										}
									}
								}
							}
					break;
					}
			break;

			case 'c_comdef_format':
			case 'c_comdef_user':
			case 'c_comdef_service_body':
				$ret['overall'] = $this->DescribeChange();
				$ret['details'] = $ret['overall'];
			break;
			}
		
		return $ret;
	}
};
?>