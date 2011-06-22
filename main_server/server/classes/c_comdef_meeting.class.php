<?php
/***********************************************************************/
/** \file	c_comdef_meeting.class.php
	\brief The file for the c_comdef_meeting class.
    
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

/**
	defines for the visibility field of the data items.
	- Values include:
		- null or 0 if completely visible (default)
		- 1 if visible only to logged-in admins and internal processes
		- 2 if visible only when displayed on a regular Web page or a Mobile page
		- 3 if visible only when displayed on a regular Web page
		- 4 if visible only when displayed on a Mobile page
		- 5 if visible only when printed
*/

define ( '_VISIBILITY_ALL_', 0 );
define ( '_VISIBILITY_NONE_', 1 );
define ( '_VISIBILITY_WEB_MOB_', 2 );
define ( '_VISIBILITY_WEB_', 3 );
define ( '_VISIBILITY_MOB_', 4 );
define ( '_VISIBILITY_PRINT_', 5 );

/***********************************************************************/
/** \class c_comdef_meeting
	\brief	A class to hold a single meeting object.

***********************************************************************/
class c_comdef_meeting extends t_comdef_world_type implements i_comdef_db_stored, i_comdef_serialized, i_comdef_auth
{
	/// This is the data for this meeting.
	private	$_my_meeting_data = null;
	
	/*******************************************************************/
	/** \brief Returns the object, in 3 storable arrays.
	
		This "unwinds" the object's central data, turning it from a single
		compound array, with object references, into 3 standalone arrays.
		
		\returns an array, containing 3 arrays; one for each table.
	*/
	function ReduceToArrays()
	{
		// We first see whether this is a new instance or an existing one. A new instance will have no ID.
		$is_update = ( isset ( $this->_my_meeting_data['id_bigint'] ) && (0 < intval ( $this->_my_meeting_data['id_bigint']) ) );
		
		// We now set up values for the three tables: The main one, the extra data one, and the long data one.
		$main_table_values = array();
		$data_table_values = array();
		$longdata_table_values = array();
		
		// If this is a new meeting, we assign it a meeting ID from the server pool.
		if ( !$is_update )
			{
			$this->_my_meeting_data['id_bigint'] = c_comdef_server::GetNewMeetingID();
			}
		
		// Load the main table first. If it is a new meeting, we need to assign it a new ID.
		$main_table_values['id_bigint'] = $this->_my_meeting_data['id_bigint'];

		if ( isset ( $this->_my_meeting_data['email_contact'] ) )
			{
			$main_table_values['email_contact'] = $this->_my_meeting_data['email_contact'];
			}
		else
			{
			$main_table_values['email_contact'] = null;
			}
		if ( isset ( $this->_my_meeting_data['worldid_mixed'] ) )
			{
			$main_table_values['worldid_mixed'] = $this->_my_meeting_data['worldid_mixed'];
			}
		else
			{
			$main_table_values['worldid_mixed'] = null;
			}
		if ( isset ( $this->_my_meeting_data['service_body_bigint'] ) )
			{
			$main_table_values['service_body_bigint'] = $this->_my_meeting_data['service_body_bigint'];
			}
		else
			{
			$main_table_values['service_body_bigint'] = null;
			}
		if ( isset ( $this->_my_meeting_data['weekday_tinyint'] ) )
			{
			$main_table_values['weekday_tinyint'] = $this->_my_meeting_data['weekday_tinyint'] - 1;
			}
		else
			{
			$main_table_values['weekday_tinyint'] = null;
			}
		if ( isset ( $this->_my_meeting_data['start_time'] ) )
			{
			$main_table_values['start_time'] = $this->_my_meeting_data['start_time'];
			}
		else
			{
			$main_table_values['start_time'] = null;
			}
		if ( isset ( $this->_my_meeting_data['lang_enum'] ) )
			{
			$main_table_values['lang_enum'] = $this->_my_meeting_data['lang_enum'];
			}
		else
			{
			$main_table_values['lang_enum'] = "en"; // Should never happen.
			}
		if ( isset ( $this->_my_meeting_data['duration_time'] ) )
			{
			$main_table_values['duration_time'] = $this->_my_meeting_data['duration_time'];
			}
		else
			{
			$main_table_values['duration_time'] = null;
			}
		if ( isset ( $this->_my_meeting_data['longitude'] ) )
			{
			$main_table_values['longitude'] = $this->_my_meeting_data['longitude'];
			}
		else
			{
			$main_table_values['longitude'] = null;
			}
		if ( isset ( $this->_my_meeting_data['latitude'] ) )
			{
			$main_table_values['latitude'] = $this->_my_meeting_data['latitude'];
			}
		else
			{
			$main_table_values['latitude'] = null;
			}
		if ( isset ( $this->_my_meeting_data['published'] ) )
			{
			$main_table_values['published'] = $this->_my_meeting_data['published'];
			}
		else
			{
			$main_table_values['published'] = 0;
			}
		
		// Now, we "unwind" the formats. Remember that we made the formats into an array, and replaced the values with objects, so we just use the keys here.
		$main_table_values['formats'] = "";
		if ( isset ( $this->_my_meeting_data['formats'] ) && is_array ( $this->_my_meeting_data['formats'] ) && count ( $this->_my_meeting_data['formats'] ) )
			{
			foreach ( $this->_my_meeting_data['formats'] as $key => $value2 )
				{
				if ( $main_table_values['formats'] )
					{
					$main_table_values['formats'] .= ",";
					}
				$main_table_values['formats'] .= $key;
				}
			}
		
		// Okay, that does it for the main table. Time to do the two data tables. The way we do that is very simple: We just measure how many bytes are in the data.
		// Anything over 255 characters in length becomes a member of the longdata table.
		foreach ( $this->_my_meeting_data as $key => $value2 )
			{
			// We ignore the values in the main table.
			switch ( $key )
				{
				case	'published':
				case	'id_bigint':
				case	'worldid_mixed':
				case	'service_body_bigint':
				case	'weekday_tinyint':
				case	'start_time':
				case	'lang_enum':
				case	'duration_time':
				case	'formats':
				case	'longitude':
				case	'latitude':
				case	'email_contact':
				case	'distance_in_km':
				case	'distance_in_miles':
				break;
				
				// Everything else goes into one of the other tables.
				default:
					$data_table_value['data_bigint'] = null;
					unset ( $data_table_value['data_bigint'] );
					$data_table_value['data_double'] = null;
					unset ( $data_table_value['data_double'] );
					$longdata_table_value['data_blob'] = null;
					unset ( $longdata_table_value['data_blob'] );

					if ( isset ( $this->_my_meeting_data[$key]['value'] ) && ( null != $this->_my_meeting_data[$key]['value'] ) )
						{
						$val = null;
						$val_key = null;
						// We reference the correct table for our operation.
						if ( is_int ( $this->_my_meeting_data[$key]['value'] ) )
							{
							$val_key = 'data_bigint';
							$val = intval ( $this->_my_meeting_data[$key]['value'] );
							}
						elseif ( is_float ( $this->_my_meeting_data[$key]['value'] ) )
							{
							$val_key = 'data_double';
							$val = floatval ( $this->_my_meeting_data[$key]['value'] );
							}
						else
							{
							$datalen = strlen ( $this->_my_meeting_data[$key]['value'] );
							if ( $datalen )
								{
								// We use the correct table for our operation.
								if ( $datalen > 255 )
									{
									$longdata_table_value['meetingid_bigint'] = $main_table_values['id_bigint'];
									$longdata_table_value['lang_enum'] = $this->_my_meeting_data['lang_enum'];
									$longdata_table_value['field_prompt'] = $this->_my_meeting_data[$key]['prompt'];
									$longdata_table_value['visibility'] = $this->_my_meeting_data[$key]['visibility'];
									$longdata_table_value['key'] = $key;
									// We reference the data, as it may be pretty long.
									$longdata_table_value['data_blob'] = $this->_my_meeting_data[$key]['value'];
									$val = null;
							        array_push ( $longdata_table_values, $longdata_table_value );
									}
								else
									{
									$val_key = 'data_string';
									$val = stripslashes ( $this->_my_meeting_data[$key]['value'] );
									}
								}
							}
						
						if ( null != $val )
							{
							$data_table_value['meetingid_bigint'] = $main_table_values['id_bigint'];
							$data_table_value['lang_enum'] = $this->_my_meeting_data['lang_enum'];
							$data_table_value['field_prompt'] = $this->_my_meeting_data[$key]['prompt'];
							$data_table_value['visibility'] = $this->_my_meeting_data[$key]['visibility'];
							$data_table_value['key'] = $key;
							$data_table_value[$val_key] = $val;
							array_push ( $data_table_values, $data_table_value );
							}
						}
				break;
				}
			}
		
		$ret_array = array ();
		
		array_push ( $ret_array, $main_table_values );
		array_push ( $ret_array, $data_table_values );
		array_push ( $ret_array, $longdata_table_values );
		
		return $ret_array;
	}
	
	/*******************************************************************/
	/** \brief Updates the DB to the current values of this instance.
		(replacing current values of the DB).
		
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
			// We take a snapshot of the meeting as it currently sits in the database as a "before" image.
			$before = null;
			$before_id = null;
			$before_lang = null;
			$service_body_id = null;
			$before_obj = c_comdef_server::GetServer()->GetOneMeeting ( $this->GetID() );
			
			if ( $before_obj instanceof c_comdef_meeting )
				{
				$service_body_id = $before_obj->GetServiceBodyID();
				$before = $before_obj->SerializeObject();
				$before_id = $before_obj->GetID();
				$before_lang = $before_obj->GetLocalLang();
				$before_obj = null;
				}

			if ( null == $service_body_id )
				{
				$service_body_id = $this->GetServiceBodyID();
				}
			
			try
				{
				// We now set up values for the three tables: The main one, the extra data one, and the long data one.
				list ( $main_table_values, $data_table_values, $longdata_table_values ) = $this->ReduceToArrays();
				// Okay, we have our three rows. Time to send them to the database.
				
				if ( is_array ( $main_table_values )  && count ( $main_table_values ) )
					{
					// The first thing we do is delete the current entry. We'll insert a new one.
					$this->DeleteFromDB_NoRecord();
	
					$first = true;
					$updateSQL = "INSERT INTO `".c_comdef_server::GetMeetingTableName_obj()."_main` (";
					foreach ( $main_table_values as $key => $value )
						{
						if ( !$first )
							{
							$updateSQL .= ",";
							}
						else
							{
							$first = false;
							}
						$updateSQL .= "`$key`";
						}
					$first = true;
					$vals = array();
					$updateSQL .= ") VALUES (";
					foreach ( $main_table_values as $key => $value )
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
						$vals[":$key"] = $value;
						}
					
					$updateSQL .= ")";

					c_comdef_dbsingleton::preparedExec($updateSQL, $vals);
					
					if ( is_array ( $data_table_values )  && count ( $data_table_values ) )
						{
						foreach ( $data_table_values as $data_table_value )
							{
							$first = true;
							$updateSQL = "INSERT INTO `".c_comdef_server::GetMeetingTableName_obj()."_data` (";
							foreach ( $data_table_value as $key => $value )
								{
								if ( !$first )
									{
									$updateSQL .= ",";
									}
								else
									{
									$first = false;
									}
								$updateSQL .= "`$key`";
								}
							$first = true;
							$vals = array();
							$updateSQL .= ") VALUES (";
							foreach ( $data_table_value as $key => $value )
								{
								// Just in case some dork wants to change the meeting ID (BAD idea).
								if ( $key == 'meetingid_bigint' )
									{
									$value = $main_table_values['id_bigint'];
									}
								
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
								$vals[":$key"] = $value;
								}
							
							$updateSQL .= ")";
	
							c_comdef_dbsingleton::preparedExec($updateSQL, $vals);
							}
						}
					
					if ( is_array ( $longdata_table_values )  && count ( $longdata_table_values ) )
						{
						foreach ( $longdata_table_values as $longdata_table_value )
							{
							$first = true;
							$updateSQL = "INSERT INTO `".c_comdef_server::GetMeetingTableName_obj()."_longdata` (";
							foreach ( $longdata_table_value as $key => $value )
								{
								// Just in case some dork wants to change the meeting ID (BAD idea).
								if ( $key == 'meetingid_bigint' )
									{
									$value = $main_table_values['id_bigint'];
									}
								
								if ( !$first )
									{
									$updateSQL .= ",";
									}
								else
									{
									$first = false;
									}
								$updateSQL .= "`$key`";
								}
							$first = true;
							$vals = array();
							$updateSQL .= ") VALUES (";
							foreach ( $longdata_table_value as $key => $value )
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
								$vals[":$key"] = $value;
								}
							
							$updateSQL .= ")";
							
							c_comdef_dbsingleton::preparedExec($updateSQL, $vals);
							}
						}
					$after = $this->SerializeObject();
					$after_id = $this->GetID();
					$after_lang = $this->GetLocalLang();
					$cType = (true == $is_rollback) ? 'comdef_change_type_rollback' : ((null != $before) ? 'comdef_change_type_change' : 'comdef_change_type_new');
					c_comdef_server::AddNewChange ( $user->GetID(), $cType, $service_body_id, $before, $after, 'c_comdef_meeting', $before_id, $after_id, $before_lang, $after_lang );
					$ret = true;
					}
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_meeting::UpdateToDB()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
				}
			}
			
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Deletes this instance from the database.
		
		\returns true if successful, false, otherwise.
		
		\throws a PDOException if there is a problem.
	*/
	function DeleteFromDB_NoRecord()
	{
		$ret = false;
		
		$user = c_comdef_server::GetCurrentUserObj();
		
		if ( $this->UserCanEdit ( $user ) )
			{
			try
				{
				$sql = "DELETE FROM `".c_comdef_server::GetMeetingTableName_obj()."_main` WHERE `id_bigint`=?";
				c_comdef_dbsingleton::preparedExec($sql, array ( $this->_my_meeting_data['id_bigint'] ) );
				$sql = "DELETE FROM `".c_comdef_server::GetMeetingTableName_obj()."_data` WHERE `meetingid_bigint`=?";
				c_comdef_dbsingleton::preparedExec($sql, array ( $this->_my_meeting_data['id_bigint'] ) );
				$sql = "DELETE FROM `".c_comdef_server::GetMeetingTableName_obj()."_longdata` WHERE `meetingid_bigint`=?";
				c_comdef_dbsingleton::preparedExec($sql, array ( $this->_my_meeting_data['id_bigint'] ) );
				$ret = true;
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_meeting::DeleteFromDB()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
				}
			}
	
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Deletes this instance from the database.
		
		\returns true if successful, false, otherwise.
		
		\throws a PDOException if there is a problem.
	*/
	function DeleteFromDB()
	{
		$ret = false;
		
		$user = c_comdef_server::GetCurrentUserObj();
		
		if ( $this->UserCanEdit ( $user ) )
			{
			// We take a snapshot of the meeting as it currently sits in the database as a "before" image.
			$service_body_id = $this->GetServiceBodyID();
			$id = $this->GetID();
			$lang = $this->GetLocalLang();
			$before = $this->SerializeObject();
	
			try
				{
				$ret = $this->DeleteFromDB_NoRecord();
				
				c_comdef_server::AddNewChange ( $user->GetID(), 'comdef_change_type_delete', $service_body_id, $before, '', 'c_comdef_meeting', $id, null, $lang, null );

				$ret = true;
				}
			catch ( Exception $ex )
				{
				global	$_COMDEF_DEBUG;
				
				if ( $_COMDEF_DEBUG )
					{
					echo "Exception Thrown in c_comdef_meeting::DeleteFromDB()!<br />";
					var_dump ( $ex );
					}
				throw ( $ex );
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
			$sql = "SELECT * FROM `".c_comdef_server::GetMeetingTableName_obj()."_main` WHERE id_bigint=? LIMIT 1"; 
			
			$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $this->GetID() ) );
			
			if ( is_array ( $rows ) && count ( $rows ) )
				{
				foreach ( $rows as $row )
					{
					$meeting_row = self::process_meeting_row ( $row );
					$this->_my_meeting_data = null;
					// We set this as our data.
					$this->_my_meeting_data = $meeting_row;
					}
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_meeting::RestoreFromDB()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
	}

	/*******************************************************************/
	/** \brief Return an array of data item keys that are used to build an address.
		
		\returns an array of strings, containing the data item field keys.
		
		\throws an exception if there is a problem.
	*/		
	static function GetAddressDataItemKeys ( $in_list = false	///< If this is true, then the return is for the list view. If false, for the "More Details" screen.
											)
	{
		$ret - null;
		
		$local_strings = c_comdef_server::GetLocalStrings();
		
		// At this point, we have the format strings. We mow parse them to get the keys used for address display.
		// The data item keys are surrounded by sets of double percent signs (%%_key_%%).
		$string_to_parse = $local_strings['comdef_global_more_details_address'];
		
		if ( $in_list )
			{
		    $string_to_parse = $local_strings['comdef_global_list_address'];
			}
		
		$matches = array();
		
		if ( preg_match_all ( '#\%\%(.*?)\%\%#', $string_to_parse, $matches ) )
			{
			$keys = c_comdef_meeting::GetAllMeetingKeys();
			if ( is_array ( $keys ) && count ( $keys ) )
				{
				$ret = array();
				
				while ( $elem = array_shift($matches[1]) )
					{
					if ( in_array ( $elem, $keys ) )
						{
						array_push ( $ret, $elem );
						}
					}
				}
			}

		return $ret;
	}

	/*******************************************************************/
	/** \brief Return an array of data item keys, as well as their prefixes and suffixes, that are used to build an address.
		
		\returns an array of strings, containing the data item field keys.
		
		\throws an exception if there is a problem.
	*/		
	static function GetAddressDataItemBuilder ( $in_list = false	///< If this is true, then the return is for the list view. If false, for the "More Details" screen.
												)
	{
		$ret - null;
		
		$local_strings = c_comdef_server::GetLocalStrings();
		
		// At this point, we have the format strings. We mow parse them to get the keys used for address display.
		// The data item keys are surrounded by sets of double percent signs (%%_key_%%).
		$string_to_parse = $local_strings['comdef_global_more_details_address'];
		
		if ( $in_list )
			{
			$string_to_parse = $local_strings['comdef_global_list_address'];
			}

		$matches = array();
		
		$parse_targets = explode ( '@@', $string_to_parse );
		
		if ( is_array ( $parse_targets ) && count ( $parse_targets ) )
			{
			$ret = array ();
			$keys = c_comdef_meeting::GetAllMeetingKeys();

			if ( is_array ( $keys ) && count ( $keys ) )
				{
				foreach ( $parse_targets as $target )
					{
					$r['prefix'] = preg_replace ( '#(.*?)\%(.*)#', "$1", $target );
					$r['key'] = preg_replace ( '#(.*?)\%\%(.*?)\%\%(.*)#', "$2", $target );
					$r['suffix'] = preg_replace ( '#(.*)\%(.*?)$#', "$2", $target );
					
					if ( in_array ( $r['key'], $keys ) )
						{
						array_push ( $ret, $r );
						}
					}
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Returns an array of strings, containing the keys (table columns)
		used for all meetings (specified in ID 0 table rows).
		
		\returns an array of strings, with the key being the same as the value.
		NOTE: This contains ALL possible keys.
		
		\throws a PDOException if there is a problem.
	*/
	static function GetAllMeetingKeys()
	{
		$ret = null;
		
		try
			{
			// The main table always has these keys.
			$ret['id_bigint'] ='id_bigint';
			$ret['worldid_mixed'] ='worldid_mixed';
			$ret['shared_group_id_bigint'] ='shared_group_id_bigint';
			$ret['service_body_bigint'] ='service_body_bigint';
			$ret['weekday_tinyint'] ='weekday_tinyint';
			$ret['start_time'] ='start_time';
			$ret['duration_time'] ='duration_time';
			$ret['formats'] ='formats';
			$ret['lang_enum'] ='lang_enum';
			$ret['longitude'] ='longitude';
			$ret['latitude'] ='latitude';
			$ret['email_contact'] = 'email_contact';
			$ret['distance_in_km'] = 'distance_in_km';
			$ret['distance_in_miles'] = 'distance_in_miles';
			
			// For the data and longdata tables, the keys can be dynamic, and we create a "0" ID version of them to establish the possibilities.
			$sql = "SELECT * FROM `".c_comdef_server::GetMeetingTableName_obj()."_data` WHERE meetingid_bigint=0"; 
			
			$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( ) );
			
			if ( is_array ( $rows ) && count ( $rows ) )
				{
				foreach ( $rows as $row )
					{
					$key = $row['key'];
					$ret[$key] = $key;
					}
				}
			
			$sql = "SELECT * FROM `".c_comdef_server::GetMeetingTableName_obj()."_longdata` WHERE meetingid_bigint=0"; 
			
			$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( ) );
			
			if ( is_array ( $rows ) && count ( $rows ) )
				{
				foreach ( $rows as $row )
					{
					$key = $row['key'];
					$ret[$key] = $key;
					}
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_meeting::GetAllMeetingKeys()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Returns an array that provides a template for the data table
		values (the optional/additional values).
		
		\returns an array with all of the _data values.
		
		\throws a PDOException if there is a problem.
	*/
	static function GetDataTableTemplate(
										$in_lang_enum = null	///< The language to use. If not given the server default will be used.
										)
	{
		$ret = array();
		
		if ( !$in_lang_enum )
			{
			$in_lang_enum = c_comdef_server::GetServer()->GetLocalLang();
			}
		
		// Should never happen.
		if ( !$in_lang_enum )
			{
			$in_lang_enum = "en";
			}
			
		try
			{
			$sql = "SELECT * FROM `".c_comdef_server::GetMeetingTableName_obj()."_data` WHERE meetingid_bigint=0 AND lang_enum=?"; 
			
			$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $in_lang_enum ) );
			foreach ( $rows as $row )
				{
				$ret[$row['key']]['key'] = $row['key'];
				$ret[$row['key']]['field_prompt'] = $row['field_prompt'];
				$ret[$row['key']]['visibility'] = $row['visibility'];
				$ret[$row['key']]['lang_enum'] = $row['lang_enum'];
				if ( $row['data_string'] )
					{
					$ret[$row['key']]['value'] = $row['data_string'];
					}
				elseif ( $row['data_bigint'] )
					{
					$ret[$row['key']]['value'] = $row['data_bigint'];
					}
				elseif ( $row['data_double'] )
					{
					$ret[$row['key']]['value'] = $row['data_double'];
					}
				else
					{
					$ret[$row['key']]['value'] = null;
					}
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_meeting::GetDataTableTemplate()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Returns an array that provides a template for the long data table
		values (the optional/additional values).
		
		\returns an array with all of the _longdata values.
		
		\throws a PDOException if there is a problem.
	*/
	static function GetLongDataTableTemplate(
											$in_lang_enum = null	///< The language to use. If not given the server default will be used.
											)
	{
		$ret = array();
		
		if ( !$in_lang_enum )
			{
			$in_lang_enum = c_comdef_server::GetServer()->GetLocalLang();
			}
		
		// Should never happen.
		if ( !$in_lang_enum )
			{
			$in_lang_enum = "en";
			}
			
		try
			{
			$sql = "SELECT * FROM `".c_comdef_server::GetMeetingTableName_obj()."_longdata` WHERE meetingid_bigint=0 AND lang_enum=?"; 
			
			$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $in_lang_enum ) );
			foreach ( $rows as $row )
				{
				$ret[$row['key']]['key'] = $row['key'];
				$ret[$row['key']]['field_prompt'] = $row['field_prompt'];
				$ret[$row['key']]['lang_enum'] = $row['lang_enum'];
				$ret[$row['key']]['visibility'] = $row['visibility'];
				if ( $row['data_longtext'] )
					{
					$ret[$row['key']]['value'] = $row['data_longtext'];
					}
				elseif ( $row['data_blob'] )
					{
					$ret[$row['key']]['value'] = $row['data_blob'];
					}
				else
					{
					$ret[$row['key']]['value'] = null;
					}
				}
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_meeting::GetDataTableTemplate()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Constructor
	
		This sets the local ID and the parent object.
	*/
	function __construct(
						$in_parent_obj,
						$inMeetingData
						)
	{
		$this->SetParentObj ( $in_parent_obj );
		$this->_my_meeting_data = $inMeetingData;
		if ( isset ( $this->_my_meeting_data ) && isset ( $this->_my_meeting_data['formats'] ) && is_array ( $this->_my_meeting_data['formats'] ) && count ( $this->_my_meeting_data['formats'] ) )
			{
			uksort ( $this->_my_meeting_data['formats'], array('c_comdef_meeting','format_sorter_preference') );
			}
		
		// Set these inherited characteristics.
		$this->SetLocalLang ( $this->_my_meeting_data['lang_enum'] );
		if ( isset ( $this->_my_meeting_data['meeting_name'] ) && $this->_my_meeting_data['meeting_name'] && $this->_my_meeting_data['meeting_name']['value'] )
			{
			$this->SetLocalName ( $this->_my_meeting_data['meeting_name']['value'] );
			}
	}
	
	/*******************************************************************/
	/**	\brief Returns a reference to the internal meeting data.
	
		\returns a reference to the internal array, containing the meeting data.
	*/
	function &GetMeetingData()
	{
		return $this->_my_meeting_data;
	}
	
	/*******************************************************************/
	/**	\brief Returns a list of the available keys in this meeting.
	
		\returns an array of strings, with each one being a key.
	*/
	function GetMeetingDataKeys()
	{
		$ret = array();
		
		foreach ( $this->_my_meeting_data as $key => &$value )
			{
			array_push ( $ret, $key );
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Returns a reference to the internal meeting data.
	
		\returns a reference to the value of the requested element. Null if the element is not there.
	*/
	function GetMeetingDataValue( $in_key	///< A string. This is the key in the data array.
								)
	{
		$ret = null;
		
		if ( isset ( $this->_my_meeting_data[$in_key] ) )
			{
			if ( is_array ( $this->_my_meeting_data[$in_key] ) && isset ( $this->_my_meeting_data[$in_key]['value'] ) )
				{
				if ( isset ( $this->_my_meeting_data[$in_key]['visibility'] ) && ($this->_my_meeting_data[$in_key]['visibility'] == _VISIBILITY_NONE_) && !$this->UserCanObserve() )
					{
					$ret = null;
					}
				else
					{
					$ret = $this->_my_meeting_data[$in_key]['value'];
					}
				}
			else
				{
				$ret = $this->_my_meeting_data[$in_key];
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Returns the internal meeting data string prompt.
	
		\returns a string. Null if the element is not there.
	*/
	function GetMeetingDataPrompt( $in_key	///< A string. This is the key in the data array.
									)
	{
		$ret = null;
		
		if ( isset ( $this->_my_meeting_data[$in_key] ) )
			{
			$ret = $this->_my_meeting_data[$in_key];
			if ( is_array ( $ret ) && isset ( $ret['prompt'] ) )
				{
				$ret = $ret['prompt'] ;
				}
			else
				{
				$ret = $in_key;
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns true if the meeting data is valid.
		
		\returns a boolean, true if the meeting is valid (has data).
	*/
	function IsValidMeeting()
	{
		list ( $main_table_values, $data_table_values, $longdata_table_values ) = $this->ReduceToArrays();
		
		return ( isset ( $main_table_values['longitude'] ) && isset ( $main_table_values['latitude'] ) && is_array ( $data_table_values ) && (count ( $data_table_values ) > 0));
	}
	
	/*******************************************************************/
	/** \brief Accessor - Reflects the meeting's status as a duplicate of another.
		
		\returns a boolean, true if the meeting is a duplicate.
	*/
	function IsCopy()
	{
		return (isset ( $this->_my_meeting_data['copy'] ) );
	}
	
	/*******************************************************************/
	/** \brief Accessor - Reflects the meeting's status as a duplicate of another.
		
		\returns an integer, if the meeting is a duplicate, it is the ID of the meeting it copies.
	*/
	function IsCopyOf()
	{
		return (intval ( $this->_my_meeting_data['copy'] ) );
	}
	
	/*******************************************************************/
	/** \brief Accessor - Reflects the meeting's published status.
		
		\returns a boolean, true if the meeting is published.
	*/
	function IsPublished()
	{
		return $this->_my_meeting_data['published'];
	}
	
	/*******************************************************************/
	/** \brief Accessor - Sets the meeting's published status.
	*/
	function SetPublished ( $in_published	///< A boolean. True if the meeting is published.
							)
	{
		if ( !$this->IsCopy() )	// Can't publish copies.
			{
			$this->_my_meeting_data['published'] = (intval ( $in_published ) != 0) ? 1 : 0;
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor - Returns the user ID as an integer.
		
		\returns an integer, containing the user ID.
	*/
	function GetID()
	{
		$ret = 0;
		
		if ( isset ( $this->_my_meeting_data['id_bigint'] ) )
			{
			$ret = $this->_my_meeting_data['id_bigint'];
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Set this meeting's ID.
	*/
	function SetMeetingID(
						$in_meeting_id_bigint	///< An integer, with the meeting's new ID.
						)
	{
		$this->_my_meeting_data['id_bigint'] = $in_meeting_id_bigint;
	}
	
	/*******************************************************************/
	/**	\brief Get this meeting's Email Contact Address
	
		\returns a string, which is the contact email.
	*/
	function GetEmailContact()
	{
		return $this->_my_meeting_data['email_contact'];
	}
	
	/*******************************************************************/
	/**	\brief Set this meeting's Email Contact Address
		This "vets" the email address, to ensure it has the appropriate structure.
		It won't set the address if it is not the appropriate structure.
	
		\returns a boolean, which is true if the address was set correctly.
	*/
	function SetEmailContact(	$in_email_contact	///< A string. The contact email address.
								)
	{
		$ret = false;
		
		if ( preg_match ( '#^(?:[a-zA-Z0-9_\'^&amp;/+-])+(?:\.(?:[a-zA-Z0-9_\'^&amp;/+-])+)*@(?:(?:\[?(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))\.){3}(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\]?)|(?:[a-zA-Z0-9-]+\.)+(?:[a-zA-Z]){2,}\.?)$#', $in_email_contact ) )
			{
			$this->_my_meeting_data['email_contact'] = $in_email_contact;
			$ret = true;
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Get this meeting's Service Body ID
	
		\returns an integer, which is the ID of the Service Body for this meeting.
	*/
	function GetServiceBodyID()
	{
		return $this->_my_meeting_data['service_body_bigint'];
	}
	
	/*******************************************************************/
	/**	\brief Set this meeting's Service Body ID
	
		\returns an integer, which is the previous ID of the Service Body for this meeting.
	*/
	function SetServiceBodyID(	$in_service_body_id	///< An integer, the ID of the new Service Body for this meeting.
								)
	{
		$ret = $this->_my_meeting_data['service_body_bigint'];
		
		$this->_my_meeting_data['service_body_bigint'] = $in_service_body_id;
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Get this meeting's Service Body, as a reference to an object.
	
		\returns a reference to an instance of c_comdef_service_body, which is the Service Body object for this meeting.
	*/
	function &GetServiceBodyObj()
	{
		return c_comdef_server::GetServiceBodyByIDObj ( $this->GetServiceBodyID() );
	}
	
	/*******************************************************************/
	/**	\brief Get this meeting's Service Body name, as a string.
	
		\returns a reference to an instance of c_comdef_service_body, which is the Service Body object for this meeting.
	*/
	function GetServiceBodyName()
	{
		$ret = $this->_my_meeting_data['service_body_bigint'];
		$sb =& $this->GetServiceBodyObj();
		
		if ( $sb instanceof c_comdef_service_body )
			{
			$ret = $sb->GetLocalName();
			}
			
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Add a new data field to the object. If the field already
		exists, then the existing field is changed to match the new data.
	
		\returns a bool that is true, if the operation was successful.
	*/
	function AddDataField(
						$in_key_enum,				///< The data field key, which is a string enum for the field.
						$in_field_prompt_string,	///< A string, containing the field prompt.
						$in_value_mixed,			///< The value of the data field, which can be passed by reference.
						$in_lang_enum = null,		///< The language enum. Optional. If not specified, the meeting value will be used.
						$in_visibility = null,		/**< The visibility of the data field. An integer.
														- null or 0 if completely visible (default)
														- 1 if visible only to logged-in admins and internal processes
														- 2 if visible only when displayed on a regular Web page or a Mobile page
														- 3 if visible only when displayed on a regular Web page
														- 4 if visible only when displayed on a Mobile page
														- 5 if visible only when printed
													*/
						$in_force = false			///< If this is set to true, then the value is written, regardless of whether or not it is already set. Default is false.
						)
	{
		// We will not operate on the principal elements.
		if ( ($in_force && !isset ( $this->_my_meeting_data[$in_key_enum] )) || ($in_force && is_array ( $this->_my_meeting_data[$in_key_enum] )) || !is_array ( $this->_my_meeting_data[$in_key_enum] ) )
			{
			if ( !$in_lang_enum )
				{
				$in_lang_enum = $this->_my_meeting_data['lang_enum'];
				}
			$this->_my_meeting_data[$in_key_enum]['key'] = strval ( $in_key_enum );
			$this->_my_meeting_data[$in_key_enum]['prompt'] = $in_field_prompt_string;
			$this->_my_meeting_data[$in_key_enum]['value'] = null;	// Just in case of memory leaks.
			$this->_my_meeting_data[$in_key_enum]['value'] = $in_value_mixed;
			$this->_my_meeting_data[$in_key_enum]['lang_enum'] = $in_lang_enum;
			$this->_my_meeting_data[$in_key_enum]['visibility'] = intval ( $in_visibility );
			return true;
			}
		
		return false;
	}
	
	/*******************************************************************/
	/**	\brief Deletes a data field. Will not delete a principal field.
	
		\returns a bool that is true, if the operation was successful.
	*/
	function DeleteDataField(
						$in_key_enum	///< The data field key, which is a string enum for the field.
						)
	{
		// We will not operate on the principal elements.
		if ( is_array ( $this->_my_meeting_data[$in_key_enum] ) )
			{
			// We do this, just so there's no possibility of memory leaks and hanging references.
			if ( isset ( $this->_my_meeting_data[$in_key_enum]['prompt'] ) )
				{
				$this->_my_meeting_data[$in_key_enum]['prompt'] = null;
				unset ( $this->_my_meeting_data[$in_key_enum]['prompt'] );
				}
			if ( isset ( $this->_my_meeting_data[$in_key_enum]['value'] ) )
				{
				$this->_my_meeting_data[$in_key_enum]['value'] = null;
				unset ( $this->_my_meeting_data[$in_key_enum]['value'] );
				}
			$this->_my_meeting_data[$in_key_enum] = null;
			unset ( $this->_my_meeting_data[$in_key_enum] );
			
			return true;
			}
		
		return false;
	}
	
	/*******************************************************************/
	/**	\brief Get this meeting's Language Enum.
	
		\returns a string, containing the meeting's language.
	*/
	function GetMeetingLang()
	{
		return $this->_my_meeting_data['lang_enum'];
	}
	
	/*******************************************************************/
	/**	\brief Get this meeting's Address in the string format specified for this server.
	
		\returns a string, containing the meeting's address in easily-readable form.
	*/
	function GetMeetingAddressString( $in_list = false	///< If this is true, then the version returned will be for the list display. Default is false (The More Details display).
									)
	{
		$ret = null;
		
		$builder = self::GetAddressDataItemBuilder ( $in_list );	// Get the parsed address format builder.

		if ( is_array ( $builder ) && count ( $builder ) )
			{
			foreach ( $builder as $element_ar )
				{
				if ( trim ( $this->GetMeetingDataValue($element_ar['key']) ) )
					{
					$ret .= $element_ar['prefix'];
					$ret .= trim ( $this->GetMeetingDataValue($element_ar['key']) );
					$ret .= $element_ar['suffix'];
					}
				}
			}
		
		return $ret;
	}

	/*******************************************************************/
	/**	\brief Add a format to the meeting (by code).
	
		Given an integer, representing the format code, it will add it to the
		meeting, and will reference the object for the meeting's language
		for that format.
		
		\returns true if the format was not already there, and false if the format was there.
	*/
	function AddFormat(
						$in_format	///< An integer, containing the format to be added.
						)
	{
		$myData =& $this->GetMeetingData();
		$my_formats =& c_comdef_server::GetServer()->GetFormatsObj();
		
		// If we already have the format, we don't add it, but there's no error.
		if ( !isset ( $myData['formats'][$in_format] ) )
			{
			$myData['formats'][$in_format] = $my_formats->GetFormatBySharedIDCodeAndLanguage ( $in_format, $this->GetMeetingLang() );
			uksort ( $myData['formats'], array('c_comdef_meeting','format_sorter_preference') );
			return true;
			}
		
		return false;
	}
	
	/*******************************************************************/
	/**	\brief Remove a format from the meeting (by code).
	
		Given an integer, representing the format code, it will remove it from the
		meeting.
		
		\returns true if successful, and false if the format was not there.
	*/
	function RemoveFormat(
						$in_format	///< An integer, containing the format to be removed.
						)
	{
		$myData =& $this->GetMeetingData();

		if ( isset ( $myData['formats'][$in_format] ) )
			{
			$myData['formats'][$in_format] = null;	// Make sure we remove the reference to the object.
			unset ( $myData['formats'][$in_format] );
			return true;
			}
		
		return false;
	}
	
	/*******************************************************************/
	/**	\brief Determines which format goes first (used in sorting).
	
		Format 4 (Closed) and format 17 (Open) are always in front. Otherwise, it's a simple numeric sort.
		
		\returns -1 if $a is 4, 17, or less than $b, 0 if they are equal, and 1 if $b is 4, 17 or less than $a
	*/
	static function format_sorter_preference (
								$a,	///< The first value to check
								$b	///< The second value to check
								)
	{
		$a = intval ( $a );
		$b = intval ( $b );
		
		if ( ($a == 4) || ($a == 17) )
			{
			return -1;
			}
		elseif ( ($b == 4) || ($b == 17) )
			{
			return 1;
			}
		elseif ( $a == $b )
			{
			return 0;
			}
		else
			{
			return ( $a < $b ) ? -1 : 1;
			}
	}
	
	/*******************************************************************/
	/**	\brief Determines which format goes first (used in sorting). Very
		simple version, with no preferences.
	
		\returns -1 if $a is less than $b, 0 if they are equal, and 1 if $b is less than $a
	*/
	static function format_sorter_simple (
								$a,	///< The first value to check
								$b	///< The second value to check
								)
	{
		$a = intval ( $a );
		$b = intval ( $b );

		if ( $a == $b )
			{
			return 0;
			}
		else
			{
			return ( $a < $b ) ? -1 : 1;
			}
	}
	
	/*******************************************************************/
	/** \brief Returns a storable serialization of the object, as a string.
		
		This is only used for the changes, as the serialized string may not
		be easily searched.
		
		\returns an array, containing the 3 component strings, each one a
		table array, in serialized form.
	*/
	function SerializeObject()
	{
		list ( $main_table_values, $data_table_values, $longdata_table_values ) = $this->ReduceToArrays();

		$ret = serialize ( array (	'main_table_values' => serialize ( $main_table_values ),
						'data_table_values' => serialize ( $data_table_values ),
						'longdata_table_values' => serialize ( $longdata_table_values ) ) );

		return $ret;
	}
	
	/*******************************************************************/
	/** \brief This takes a serialized object, and instantiates a
		new object from it.
		
		\returns a new instance of c_comdef_meeting, set up according to
		the serialized data passed in.
	*/
	static function UnserializeObject( $in_parent,			///< The parent object.
										$serialized_array	///< String containing 3 sequential arrays, each with the serialized data.
										)
	{
		$serialized_array = unserialize ( $serialized_array );
		$main_table_values = unserialize ( $serialized_array['main_table_values'] );
		$data_table_values = unserialize ( $serialized_array['data_table_values'] );
		$longdata_table_values = unserialize ( $serialized_array['longdata_table_values'] );
		$my_data = self::process_meeting_row ( $main_table_values, $data_table_values, $longdata_table_values );
		
		$new_meeting = new c_comdef_meeting ( $in_parent, $my_data );
		
		return $new_meeting;
	}
	
	/*******************************************************************/
	/** \brief This processes the data retrieved from a single main table meeting.
		It will look up the corollary data in the data and longdata tables,
		and will consolidate it into an atomic array. It will also return
		c_comdef_format objects for the formats in the server language.
		
		\returns an array of data that can be used as the data for the meeting object.
	*/
	static function process_meeting_row (
										$row,					///< One row of meeting data, fresh from the database.
										/// These are used to unserialize the object, which bypasses the database.
										$data_rows = null,		///< Optional. If non-null, this should be an array of data table values
										$longdata_rows = null	///< Optional. If non-null, this should be an array of longdata table values
										)
	{
		try
			{
			// The weekday is kept on the server as zero-based, but we keep it 1-based in our implementation.
			$row['weekday_tinyint'] = $row['weekday_tinyint'] + 1;
			$meeting_id = $row['id_bigint'];
			$meeting_row = $row;
			// However, we do one thing differently: We split the formats array, so it is in multiple elements. That'll make it easier to handle later.
			// We also actually assign a reference to the localized format object itself to the array.
			$meeting_row['formats'] = explode ( ",", $meeting_row['formats'] );
			
			// What we do here is assign the format object to the version for the language of the meeting (not necessarily the server).
			// The formats array will use the format codes as keys, so we can use these to access other localizations.
			$new_formats = array();

			$my_formats = c_comdef_server::GetServer()->GetFormatsObj();
			foreach ( $meeting_row['formats'] as $format_id )
				{
				$new_formats[$format_id] =& $my_formats->GetFormatBySharedIDCodeAndLanguage ( $format_id, $row['lang_enum'] );
				}
			
			$meeting_row['formats'] = $new_formats;
			
			// If the row was not already supplied, we fetch it ourselves.
			if ( null == $data_rows )
				{
				// We do two lookups, because a fancy-ass JOIN takes FOR-EVER. The performance is AWFUL.
				// 99% of the time, the longdata table won't have anything in it, so we shouldn't slow
				// down the regular data table to include a table that barely ever has anything.
				// This has the added advantage of allowing implementations to override the main data, and
				// to allow data in the longdata table to override the regular table.
				
				$sql = "SELECT * FROM `".c_comdef_server::GetMeetingTableName_Obj()."_data` WHERE ".c_comdef_server::GetMeetingTableName_Obj()."_data.meetingid_bigint=?"; 
		
				$data_rows = c_comdef_dbsingleton::preparedQuery ( $sql, array ( $meeting_id ) );
				}
			
			if ( is_array ( $data_rows ) && count ( $data_rows ) )
				{
				foreach ( $data_rows as $data_row )
					{
					$key = $data_row['key'];
					if ( trim ( $key ) )
						{
						if ( isset ( $data_row['data_string'] ) && (null != $data_row['data_string']) )
							{
							$meeting_row[$key]['value'] = stripslashes ( strval ( $data_row['data_string'] ) );
							}
						elseif ( isset ( $data_row['data_bigint'] ) && ( null != $data_row['data_bigint'] ) )
							{
							$meeting_row[$key]['value'] = intval ( $data_row['data_bigint'] );
							}
						elseif ( isset ( $data_row['data_double'] ) && ( null != $data_row['data_double'] ) )
							{
							$meeting_row[$key]['value'] = floatval ( $data_row['data_double'] );
							}
						
						// Only set these if we have data.
						if ( isset ( $meeting_row[$key]['value'] ) )
							{
							$meeting_row[$key]['longdata'] = false;
							$meeting_row[$key]['prompt'] = $data_row['field_prompt'];
							$meeting_row[$key]['lang'] = $data_row['lang_enum'];
							$meeting_row[$key]['visibility'] = $data_row['visibility'];
							}
						}
					}
				}
			
			// If the row was not already supplied, we fetch it ourselves.
			if ( null == $longdata_rows )
				{
				$sql = "SELECT * FROM `".c_comdef_server::GetMeetingTableName_Obj()."_longdata` WHERE ".c_comdef_server::GetMeetingTableName_Obj()."_longdata.meetingid_bigint=?"; 
	
				$longdata_rows = c_comdef_dbsingleton::preparedQuery ( $sql, array ( $meeting_id ) );
				}
			
			if ( is_array ( $longdata_rows ) && count ( $longdata_rows ) )
				{
				foreach ( $longdata_rows as $longdata_row )
					{
					$key = $longdata_row['key'];
					if ( trim ( $key ) && ((null != $longdata_row['data_blob']) || (null != $longdata_row['data_longtext'])) )
						{
						$meeting_row[$key]['longdata'] = true;
						$meeting_row[$key]['value'] = (null != $longdata_row['data_blob']) ? $longdata_row['data_blob'] : stripslashes ( $longdata_row['data_longtext'] );
						$meeting_row[$key]['prompt'] = $longdata_row['field_prompt'];
						$meeting_row[$key]['lang'] = $longdata_row['lang_enum'];
						$meeting_row[$key]['visibility'] = $longdata_row['visibility'];
						}
					}
				}
			
			// At this point, we have all the data for this one meeting, culled from its three tables and aggregated into an array.
			return $meeting_row;
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_meeting::process_meeting_row()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
	}
	
	/*******************************************************************/
	/** \brief Get the contact email for this meeting.
				If $in_recursive is false, then it simply looks at this meeting's Service Body.
		
		\returns a string, which is the contact email for this meeting
	*/
	function GetContactEmail( $in_recursive = false	///< If this is true, then the function will return a recursive result. Default is false.
							)
	{
		$ret = trim ( $this->GetEmailContact() );
		
		if ( !$ret )
			{
			$service_body =& $this->GetServiceBodyObj();
			
			if ( $service_body instanceof c_comdef_service_body )
				{
				$ret = $service_body->GetContactEmail ( $in_recursive );
				}
			}
		
		return $ret;
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
			
			if ( ($in_user_object->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($in_user_object->GetUserLevel() != _USER_LEVEL_DISABLED) && (($in_user_object->GetUserLevel() == _USER_LEVEL_EDITOR) || ($in_user_object->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN)) )
				{
				// If there is an existing object, then we can't make changes unless it's allowed in the existing object.
				$current_obj =& c_comdef_server::GetServer()->GetOneMeeting ( $this->GetID() );
				
				// If there is no current object, then we are a new meeting.
				if ( null == $current_obj )
					{
					$current_obj =& $this;
					}
				else
					{
					// We clone, in case changes have been made, and we don't want to screw them up.
					$current_obj = clone $current_obj;
					// Otherwise, block dope fiends.
					$current_obj->RestoreFromDB();
					}
				
				if ( $current_obj instanceof c_comdef_meeting )
					{
					$my_service_body =& c_comdef_server::GetServiceBodyByIDObj ( $current_obj->GetServiceBodyID() );
					
					if ( $in_user_object->GetUserLevel() == _USER_LEVEL_EDITOR )
						{
						// Regular meeting list editors can't change published objects. They also can only edit meetings in the exact Service Body to which they are assigned.
						$ret = ($my_service_body instanceof c_comdef_service_body) && $my_service_body->IsUserInServiceBody ( $in_user_object ) && !$this->IsPublished();
						}
					elseif ( ($my_service_body instanceof c_comdef_service_body) && $my_service_body->IsUserInServiceBodyHierarchy ( $in_user_object ) )
						{
						$ret = true;
						}
					}
				}
			elseif ( c_comdef_server::IsUserServerAdmin() )
				{
				$ret = true;
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Test to see if a user is allowed to observe an instance (view the data).
	
		\returns true, if the user is allowed to observe, false, otherwise.
	*/
	function UserCanObserve (
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
			
			if ( $in_user_object->GetUserLevel() == _USER_LEVEL_OBSERVER )
				{
				// If there is an existing object, then we can't make changes unless it's allowed in the existing object.
				$current_obj =& c_comdef_server::GetServer()->GetOneMeeting ( $this->GetID() );
				
				// If there is no current object, then we are a new meeting.
				if ( null == $current_obj )
					{
					$current_obj =& $this;
					}
				else
					{
					// We clone, in case changes have been made, and we don't want to screw them up.
					$current_obj = clone $current_obj;
					// Otherwise, block dope fiends.
					$current_obj->RestoreFromDB();
					}
				
				if ( $current_obj instanceof c_comdef_meeting )
					{
					$my_service_body =& c_comdef_server::GetServiceBodyByIDObj ( $current_obj->GetServiceBodyID() );
					
					if ( ($my_service_body instanceof c_comdef_service_body) && $my_service_body->IsUserInServiceBodyHierarchy ( $in_user_object ) )
						{
						$ret = true;
						}
					}
				}
			else
				{
				$ret = $this->UserCanEdit( $in_user_object );	// Editors can observe
				}
			}
		
		return $ret;
	}
};
?>