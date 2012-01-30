<?php
/**
	\file c_comdef_server.class.php
	
	\brief The main server (Model) class for the Basic Meeting List Toolbox MVC system.
	
	The server is a SINGLETON. There can only be one. Upon instantiation, it creates a bunch of format objects, in its own
	localization. The formats are all read in and kept local, but the meetings are supplied upon demand. The same goes for the
	change tracking.
	
	This series of classes interfaces with the server, using PHP's PDO database abstraction layer} (http://us3.php.net/pdo),
	so this system should work for multiple databases.
    
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

require_once ( dirname ( __FILE__ )."/classes/c_comdef_dbsingleton.class.php" );
require_once ( dirname ( __FILE__ )."/classes/c_comdef_formats.class.php" );
require_once ( dirname ( __FILE__ )."/classes/c_comdef_meetings.class.php" );
require_once ( dirname ( __FILE__ )."/classes/c_comdef_changes.class.php" );
require_once ( dirname ( __FILE__ )."/classes/c_comdef_users.class.php" );
require_once ( dirname ( __FILE__ )."/classes/c_comdef_service_bodies.class.php" );
require_once ( dirname ( __FILE__ )."/shared/classes/base_templates.inc.php" );

/******************************************************************/

/** \brief This class is the main server class. It instantiates a
	PDO database object, and is the starting point for everything
	done and managed by the CoMDEF server system.
*/
class c_comdef_server
{
	///This is the SINGLETON server instance. There can only be one...
	static $server_instance = null;
	/// This contains a cache of the local strings.
	static  $server_local_strings = null;
	
	/// This is the name of the Formats table in the database.
	private	$_format_table_name = null;
	/// This is the name of the Meetings table in the database.
	private	$_meeting_table_name = null;
	/// This is the name of the Changes table in the database.
	private	$_changes_table_name = null;
	/// This is the name of the user table in the database.
	private	$_user_table_name = null;
	/// This is the name of the Service Bodies table in the database.
	private	$_service_bodies_table_name = null;
	/// This is the container for the loaded formats.
	private	$_formats_obj = null;
	/// This is the container for the loaded users.
	private	$_users_obj = null;
	/// This has the IDs of all the Service entities that "own" meetings on the server.
	private $_service_ids = null;
	/// This contains the names of the server languages, in their languages. It is an associative array, based on the language enums.
	private $_server_lang_names = null;
	/// This contains the server namespace, which is used to uniquely identify data from this server. The default is the server URI, with "/CoMDEF" appended.
	private $_server_namespace = null;
	/// This contains the actual Service Body objects as a simple array.
	private $_service_obj_array = null;
	
	/*******************************************************************/
	/**	\brief	This is the factory for the server instantiation.
				It makes sure that only one instance exists.
				
		\returns the Server instance. Either a new one, or the existing one.
	*/
	static function MakeServer()
	{

		if ( !(self::$server_instance instanceof c_comdef_server) )
			{
			self::$server_instance = new c_comdef_server;
			}
		
		return self::$server_instance;
	}
	
	/*******************************************************************/
	/** \returns the Server instance.
	*/
	static function GetServer()
	{
		return self::$server_instance;
	}
	
	/*******************************************************************/
	/** \brief Sets the server instance.
	*/
	static function SetServer( $in_server_instance )
	{
		if ( $in_server_instance != self::GetServer() )
			{
			if ( null !== self::GetServer() )
				{
				self::$server_instance = null;
				}

			self::$server_instance = $in_server_instance;
			}
	}
	
	/*******************************************************************/
	/**	\brief The initial setup call for the class. Part of setting up
		the server is establishing the database connection, and reading
		in all of the formats, which will be available in the
		GetFormatsObj() member function afterwards.
	*/
	function __construct()
	{
		global	$comdef_global_language;
		try
			{
			self::SetServer ( $this );
			include ( dirname ( __FILE__ )."/config/auto-config.inc.php" );
			c_comdef_dbsingleton::init ( $dbType, $dbServer, $dbName, $dbUser, $dbPassword, 'utf8' );
			$this->_format_table_name = $dbPrefix."_comdef_formats";
			$this->_meeting_table_name = $dbPrefix."_comdef_meetings";
			$this->_changes_table_name = $dbPrefix."_comdef_changes";
			$this->_service_bodies_table_name = $dbPrefix."_comdef_service_bodies";
			$this->_user_table_name = $dbPrefix."_comdef_users";

			if ( null !== $serverNamespace )
				{
				$this->_server_namespace = $serverNamespace;
				}
			else
				{
				$this->_server_namespace = "http://".$_SERVER['SERVER_NAME']."/CoMDEF";
				}
			
			// Brute-force protection against selecting a language that isn't supported by the resources at hand.
			if ( !file_exists ( dirname ( __FILE__ )."/config/lang/".$comdef_global_language."/name.txt" ) )
				{
				$comdef_global_language = "en";
				}
			
			$this->_local_type_lang_enum = $comdef_global_language;
			
			$dh = opendir ( dirname ( __FILE__ ).'/config/lang/' );
			
			if ( $dh )
				{
				while ( false !== ($enum = readdir( $dh )) )
					{
					$file_path = dirname ( __FILE__ )."/config/lang/$enum/name.txt";
					if ( file_exists ( $file_path ) )
						{
						$this->_server_lang_names[$enum] = trim ( file_get_contents ( $file_path ) );
						}
					}
					
				closedir ( $dh );
				}
			
			$this->Initialize();
			}
		catch ( Exception $err )
			{
			throw ( $err );
 			}
	}
	
	/*******************************************************************/
	/**	\brief This reads the Formats, Meeting and Service Entity IDs.
	*/
	function Initialize ()
	{
		$this->ReadUsers();
		$this->ReadFormats();
		$this->ReadServiceBodies();
		$this->ReadServiceIDs();
	}
	
	/*******************************************************************/
	/**	\brief Returns the Server Local Language.
	
		\returns a string, with the language enum.
	*/
	function GetLocalLang()
	{
		$lang_enum = $this->_local_type_lang_enum;
		
		if ( !$lang_enum )
			{
			global	$comdef_global_language;
			$lang_enum = $comdef_global_language;
			}
		
		// Should never happen.
		if ( !$lang_enum )
			{
			$lang_enum = "en";
			}
		
		return $lang_enum;
	}
	
	/*******************************************************************/
	/**	\brief This is an internal function that reads in all of the
		stored formats, in all provided languages, and instantiates
		local objects for them.
		Access them with the GetFormatsObj() member function afterwards.
	*/
	function ReadFormats()
	{
		$sql = "SELECT * FROM `".self::GetFormatTableName_obj()."` ORDER BY shared_id_bigint, lang_enum"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery($sql);
		
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			$obj_array = array();
			/// Read in all the formats, and instantiate an array of objects.
			foreach ( $rows as $rs )
				{
				/// We aren't allowed to have two formats for the same language, and the same shared ID.
				if ( !isset ( $rs['lang_enum'] ) || !isset ( $obj_array[$rs['lang_enum']] ) || !isset ( $obj_array[$rs['lang_enum']][$rs['shared_id_bigint']] ) || !is_object ( $obj_array[$rs['lang_enum']][$rs['shared_id_bigint']] ) )
					{
					/// We use a combination of the language and the shared ID as the keys, which allows us to sort better.
					$obj_array[$rs['lang_enum']][$rs['shared_id_bigint']] = new c_comdef_format (	$this,
																									$rs['shared_id_bigint'],
																									$rs['format_type_enum'],
																									$rs['key_string'],
																									$rs['icon_blob'],
																									$rs['worldid_mixed'],
																									$rs['lang_enum'],
																									$rs['name_string'],
																									$rs['description_string']
																									);
					}
				}
			
			/// Create our internal container, and give it the array.
			$this->_formats_obj = new c_comdef_formats ( $this, $obj_array );
			}
	}
	
	/*******************************************************************/
	/**	\brief This is an internal function that reads in all of the
		stored users and instantiates local objects for them.
		Access them with the GetUsersObj() member function afterwards.
	*/
	function ReadUsers()
	{
		// First, we make sure we explicitly delete any old ones.
		
		if ( $this->_users_obj instanceof c_comdef_users )
			{
			$ar =& $this->_users_obj->GetUsersArray();
			
			foreach ( $ar as &$u )
				{
				$u = null;
				}
			
			$this->_users_obj = null;
			}
		
		$sql = "SELECT * FROM `".self::GetUserTableName_obj()."` ORDER BY id_bigint"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery($sql);
		
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			$obj_array = array();
			/// Read in all the users, and instantiate an array of objects.
			foreach ( $rows as $row )
				{
				$obj_array[$row['id_bigint']] = new c_comdef_user ( $this,
																	$row['id_bigint'],
																	$row['user_level_tinyint'],
																	$row['email_address_string'],
																	$row['login_string'],
																	$row['password_string'],
																	$row['lang_enum'],
																	$row['name_string'],
																	$row['description_string'],
																	$row['last_access_datetime']
																	);
				}
			
			/// Create our internal container, and give it the array.
			$this->_users_obj = new c_comdef_users ( $this, $obj_array );
			}
	}
	
	/*******************************************************************/
	/**	\brief This is an internal function that reads in all of the
		stored service bodies and instantiates local objects for them.
		Access them with the GetServiceObj() member function afterwards.
	*/
	function ReadServiceBodies()
	{
		$this->_service_obj_array = array();
		
		$sql = "SELECT * FROM `".self::GetServiceBodiesTableName_obj()."` ORDER BY id_bigint"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery($sql);
		
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			/// Read in all the service bodies, and instantiate an array of objects.
			foreach ( $rows as $row )
				{
				array_push ( $this->_service_obj_array, new c_comdef_service_body ( $this,
																					$row['id_bigint'],
																					$row['principal_user_bigint'],
																					$row['editors_string'],
																					$row['kml_file_uri_string'],
																					$row['uri_string'],
																					$row['name_string'],
																					$row['description_string'],
																					$row['lang_enum'],
																					$row['worldid_mixed'],
																					$row['sb_type'],
																					$row['sb_owner'],
																					$row['sb_owner_2'],
																					$row['sb_meeting_email']
																					)
							);
				}
			}
	}
	
	/*******************************************************************/
	/**	\brief This gathers the IDs of all the Service bodies that appear
		in meeting records. It is NOT a dump of the Service Bodies table.
	*/
	function ReadServiceIDs()
	{
		$sql = "SELECT service_body_bigint FROM `".self::GetMeetingTableName_obj()."_main` ORDER BY service_body_bigint"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery($sql);
		
		// Just makes sure that old allocations are explicitly gone.
		$this->_service_ids = null;
		$this->_service_ids = array();
		
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			foreach ( $rows as $rs )
				{
				$key = $rs['service_body_bigint'];
				$value = $key;
				$obj =& self::GetServiceBodyByIDObj ( $key );
				if ( $obj instanceof c_comdef_service_body )
					{
					$name = trim ( $obj->GetLocalName() );
					if ( $name )
						{
						$key = $name;
						}
					}
				$this->_service_ids[$key] = $value;
				}
			}
	}
	
	/*******************************************************************/
	/**	\brief Simply returns a reference to the contained Service Body
		array.
		
		\returns A reference to an array of c_comdef_service_body objects.
	*/
	function &GetServiceBodyArray()
	{
		return $this->_service_obj_array;
	}
	
	/*******************************************************************/
	/**	\brief Returns the Service Body objects in a nested, hierarchical
		array, with "parents" containing "children."
		
		\returns A nested associative array of references to
		c_comdef_service_body objects.
	*/
	function GetServiceBodyArrayHierarchical()
	{
		$ret_array = $this->GetNestedServiceBodyArray ( 0 );
		return $ret_array;
	}
	
	/*******************************************************************/
	/**	\brief 	This reads the Service bodies in hierarchical order, and
				returns them in a multi-dimensional array that reflects
				the hierarchy.
		
		\returns	A multidimensional associative array, containing the
					Service bodies, as references, and structured in a manner
					that reflects the hierarchical arrangement of the Service
					bodies.
					The 'object' element contains a reference to the object itself,
					and the 'dependents' element (if it exists), reflects the
					Service bodies that are "owned" by this one.
	*/
	function GetNestedServiceBodyArray ( $in_id = 0 ///< The ID of the "top" Service body. If not supplied, we start at the top.
										)
	{
		$ret_array = null;
		
		if ( $in_id )
			{
			$sb = $this->GetServiceBodyByIDObj($in_id);
			if ( $sb instanceof c_comdef_service_body )
				{
				$id = $sb->GetID();
				$ret_array['object'] =& $sb;
				}
			}

		foreach ( $this->_service_obj_array as &$sb )
			{
			if ( $sb instanceof c_comdef_service_body )
				{
				$id = $sb->GetID();

				$sb_parent = intval ( $sb->GetOwnerID() );
				
				if ( $sb_parent == $in_id )
					{
					$ret_array['dependents'][$id] = $this->GetNestedServiceBodyArray ( $id );
					}
				}
			}
		
		return $ret_array;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns a reference to the formats container.
		
		\returns A reference to the formats container object.
	*/
	function GetFormatsObj()
	{
		return $this->_formats_obj;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the stored service IDs.
		
		\returns a reference to the array containing all the Service entity IDs.
		NOTE: These are IDs that appear in meetings, and may not reflect those in the Server.
	*/
	function &GetServiceIDs()
	{
		return $this->_service_ids;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the stored service IDs for ALL Service Bodies.
		
		\returns a reference to the array containing all the Service entity IDs. Null if none.
		NOTE: These are ALL IDs that appear (even ones not used for meetings).
	*/
	function &GetAllServiceIDs()
	{
		$ret = null;

		$ar = $this->GetServiceBodyArray();
		
		if ( is_array ( $ar ) && count ( $ar ) )
			{
			foreach ( $ar as &$sb )
				{
				if ( $sb instanceof c_comdef_service_body )
					{
					$key = $sb->GetID();
					$value = $key;
					$name = trim ( $sb->GetLocalName() );
					if ( $name )
						{
						$key = $name;
						}
						
					$ret[$key] = $value;
					}
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief This creates a new meeting that is an exact duplicate of
		the object for the meeting whose ID is passed in. The new meeting
		has a new ID, and is unpublished.
		
		\returns an integer, with the ID of the new meeting. 0 If it fails.
	*/
	static function DuplicateMeetingID ( $in_meeting_id	///< The ID of the meeting to be copied.
										)
	{
		$ret = 0;
		
		$meeting_to_copy =& self::GetOneMeeting ( $in_meeting_id );
		
		if ( $meeting_to_copy instanceof c_comdef_meeting )
			{
			$copy = self::DuplicateMeetingObj ( $meeting_to_copy );
			
			if ( $copy instanceof c_comdef_meeting )
				{
				$ret = $copy->GetID();
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief This creates a new meeting that is an exact duplicate of
		the object passed in. The new meeting has a new ID, and is unpublished.
		
		\returns a reference to a c_comdef_meeting object, representing the new meeting. Null if it fails.
	*/
	static function DuplicateMeetingObj ( $in_meeting_obj	///< A reference to the meeting object to be copied.
										)
	{
		$new_meeting =  null;
		
		if ( $in_meeting_obj instanceof c_comdef_meeting )
			{
			$meeting_data = $in_meeting_obj->GetMeetingData();
			$meeting_data['id_bigint'] = 0;
			$meeting_data['published'] = 0;
			$meeting_data['copy'] = $in_meeting_obj->GetID();
			
			$new_meeting = new c_comdef_meeting ( self::GetServer(), $meeting_data );
			
			if ( $new_meeting instanceof c_comdef_meeting )
				{
				$new_meeting->UpdateToDB();
				}
			}
		
		return $new_meeting;
	}
	
	/*******************************************************************/
	/**	\brief Gets an unused ID from the end of the table and
		returns it. It also sets that ID into the table AUTO_INCREMENT,
		so it can't get re-used.
		
		\returns an integer, containing that ID.
	*/
	static function GetNewMeetingID ( )
	{
		include ( dirname ( __FILE__ )."/config/auto-config.inc.php" );
		
		try
			{
			$sql = "SHOW TABLE STATUS LIKE '".self::GetMeetingTableName_obj()."_main'";
			$rows = c_comdef_dbsingleton::preparedQuery( $sql, array() );
			if ( is_array ( $rows ) && count ( $rows ) )
				{
				$table_row = 'auto_increment';
				
				if ( !isset ( $rows[0][$table_row] ) )
					{
					$table_row = 'Auto_increment';
					}
				
				$max_id = intval ( $rows[0][$table_row] ) + 1;
				}
			
			$sql = "ALTER TABLE `".self::GetMeetingTableName_obj()."_main` AUTO_INCREMENT=". intval ( $max_id );
	
			c_comdef_dbsingleton::preparedExec($sql);
			}
		catch ( Exception $ex )
			{
			global	$_COMDEF_DEBUG;
			
			if ( $_COMDEF_DEBUG )
				{
				echo "Exception Thrown in c_comdef_server::GetNewMeetingID()!<br />";
				var_dump ( $ex );
				}
			throw ( $ex );
			}
		
		return --$max_id;
	}
	
	/*******************************************************************/
	/**	\brief Creates a new, relatively empty meeting in the database,
		with no data fields and minimal information.
		
		\returns the ID of the meeting. Null is it failed.
	*/
	static function AddNewMeeting(
								$in_service_body_bigint,	///< The ID of the Service Body that "owns" this meeting.
								$in_weekday_tinyint,		///< The index of the weekday on which the meeting is held (0 = Sunday, 6 = Saturday).
								$in_start_time_int,			///< The time, in standard PHP Epoch time, at which the meeting starts.
								$in_lang_enum				///< The language for the meeting.
								)
	{
		$id = null;
		
		if ( !$in_lang_enum )
			{
			$in_lang_enum = self::GetServer()->GetLocalLang();
			}
		
		if ( !$in_lang_enum )
			{
			global	$comdef_global_language;
			$in_lang_enum = $comdef_global_language;
			}
		
		// Should never happen.
		if ( !$in_lang_enum )
			{
			$in_lang_enum = "en";
			}

		$meeting_data = array ( 'service_body_bigint'=>$in_service_body_bigint, 'weekday_tinyint'=>$in_weekday_tinyint, 'start_time'=>$in_start_time_int, 'lang_enum'=>$in_lang_enum);
		
		$new_meeting = new c_comdef_meeting ( self::GetServer(), $meeting_data );
		
		if ( $new_meeting instanceof c_comdef_meeting )
			{
			$new_meeting->UpdateToDB();
			$id = $new_meeting->GetID();
			}
		
		return $id;
	}
	
	/*******************************************************************/
	/**	\brief Creates a new Service Body in the Database.
		
		\returns the ID of the Service Body. Null is it failed.
	*/
	static function AddNewServiceBody (
										$in_name_string,				///< The Service Body's Name
										$in_lang_enum,					///< The language for the Service Body
										$in_principal_user_bigint,		///< The ID of the principal Service Body Administrator
										$in_description_string = null,	///< The description of the Service Body (Optional)
										$in_editors_string = null,		///< The IDs of the editors, as a CSV string (Optional)
										$in_uri_string = null,			///< The Service Body Web Site URI (Optional)
										$in_kml_uri_string = null,		///< The URI of a KML file that contains the Service Boundaries of the Service Body (Optional)
										$in_worldid_mixed = null,		///< The World ID (if one is available) (Optional)
										$in_sb_type = null,				/**< An enum string, containing the Service Body type.
																			 It can be one of the following:
																				- 'GR'	Individual NA Group
																				- 'AS'	Area Service Committee
																				- 'RS'	Regional Service Committee
																				- 'MA'	Metro Area
																				- 'ZF'	Zonal Forum
																				- 'WS'	World Service Committee
																		*/
										$in_sb_owner = null				///< An integer. The ID of the Service Body that "owns" this Service Body.
										)
	{
		$id = null;
		
		if ( !$in_lang_enum )
			{
			$in_lang_enum = self::GetServer()->GetLocalLang();
			}
		
		if ( !$in_lang_enum )
			{
			global	$comdef_global_language;
			$in_lang_enum = $comdef_global_language;
			}
		
		// Should never happen.
		if ( !$in_lang_enum )
			{
			$in_lang_enum = "en";
			}

		$service_body = new c_comdef_service_body ( self::GetServer(), null, $in_principal_user_bigint, $in_editors_string, $in_kml_uri_string, $in_uri_string, $in_name_string, $in_description_string, $in_lang_enum, $in_worldid_mixed, $in_sb_type, $in_sb_owner );
		
		if ( $service_body instanceof c_comdef_service_body )
			{
			try
				{
				$service_body->UpdateToDB();
				$id = $service_body->GetID();
				$service_body = null;
				
				self::GetServer()->ReadServiceBodies();
				}
			catch ( Exception $e )	// We just eat the exception and return null.
				{
				$id = null;
				}
			}
		
		return $id;
	}
	
	/*******************************************************************/
	/**	\brief Creates a new user in the Database.
		
		\returns the ID of the user. Null is it failed.
	*/
	static function AddNewUser (
								$in_user_login,					///< The login for this user
								$in_user_unencrypted_password,	///< The unencrypted password for this user
								$in_user_level,					///< The level of this user
								$in_user_email,					///< The email address for this user
								$in_name_string = null,			///< The user's Name (Optional)
								$in_description_string = null,	///< The description of the user (Optional)
								$in_lang_enum = null			///< The language for the user (Optional -If not supplied, the server default will be used)
								)
	{
		$id = null;
		
		if ( !$in_lang_enum )
			{
			$in_lang_enum = self::GetServer()->GetLocalLang();
			}
		
		if ( !$in_lang_enum )
			{
			global	$comdef_global_language;
			$in_lang_enum = $comdef_global_language;
			}
		
		// Should never happen.
		if ( !$in_lang_enum )
			{
			$in_lang_enum = "en";
			}
		
		$encrypted_password = FullCrypt ( trim ( $in_user_unencrypted_password ) );
		
		$user_obj = new c_comdef_user ( self::GetServer(), null, $in_user_level, $in_user_email, $in_user_login, $encrypted_password, $in_lang_enum, $in_name_string, $in_description_string );
		
		if ( $user_obj instanceof c_comdef_user )
			{
			try
				{
				$user_obj->UpdateToDB();
				$id = $user_obj->GetID();
				$user_obj = null;
			
				self::GetServer()->ReadUsers();
				}
			catch ( Exception $e )	// We just eat the exception and return null.
				{
				$id = null;
				}
			}
		
		return $id;
	}
	
	/*******************************************************************/
	/** \brief trims the changes for the given item.
	*/
	static function TrimChanges (
								$in_type,	/**< This is a string that contains the class of the change record.
												 - It can be:
												 	- c_comdef_meeting
												 	- c_comdef_format
												 	- c_comdef_user
												 	- c_comdef_service_body
											*/
								$in_id		//< The ID (an integer) for the item to be "trimmed."
								)
	{
		include ( dirname ( __FILE__ )."/config/auto-config.inc.php" );
		
		$change_limit = 0;
		
		if ( $in_type == 'c_comdef_meeting' )
			{
			$change_limit = $change_depth_for_meetings;
			}
		
		if ( $change_limit )
			{
			// Get rid of oldest change first.
			$changes =& self::GetChangesFromIDAndType( $in_type, $in_id );

			if ( $changes instanceof c_comdef_changes )
				{
				$ch_objs =& $changes->GetChangesObjects();
				
				if ( is_array ( $ch_objs ) )
					{
					$counted = count ( $ch_objs );
					
					while ( $counted-- > $change_limit )
						{
						$ch_objs[$counted]->DeleteFromDB();
						}
					}
				
				$changes = null;
				}
			}
	}
	
	/*******************************************************************/
	/**	\brief Creates a new change record in the Database.
		
		\returns the ID of the user. Null is it failed.
	*/
	static function AddNewChange (
								$in_user_id_bigint,				///< Integer. The ID of the user making the change.
								$in_change_type,				/**< Enum. The type of change.
																	Can be:
																		- 'comdef_change_type_new' - New object
																		- 'comdef_change_type_delete' - Deleted the object
																		- 'comdef_change_type_change' - Changed existing object
																		- 'comdef_change_type_rollback' - Rolled existing object back to a previous version
																*/
								$in_service_body_id_bigint,		///< Integer. The ID of the Service body for which the user was acting.
								$in_before_string,				///< Serialized object string. The "before" object, in serialized form.
								$in_after_string,				///< Serialized object string. The "after" object, in serialized form.
								$in_object_class_string,		///< The class of the objects.
								$in_before_obj_id_bigint,		///< Integer, The ID of the Before Object.
								$in_after_obj_id_bigint,		///< Integer, The ID of the After Object.
								$in_before_obj_lang_enum = null,///< Enum, the language of the Before Object.
								$in_after_obj_lang_enum = null,	///< Enum. The language of the After Object.
								$in_name_string = null,			///< The change's Name (Optional)
								$in_description_string = null,	///< The description of the change (Optional)
								$in_lang_enum = null			///< The language for the change (Optional -If not supplied, the server default will be used)
								)
	{
		$id = null;
		
		if ( !$in_lang_enum )
			{
			$in_lang_enum = self::GetServer()->GetLocalLang();
			}
		
		if ( !$in_lang_enum )
			{
			global	$comdef_global_language;
			$in_lang_enum = $comdef_global_language;
			}
		
		// Should never happen.
		if ( !$in_lang_enum )
			{
			$in_lang_enum = "en";
			}
		
		$change_obj = new c_comdef_change ( self::GetServer(), $in_change_type, $in_user_id_bigint, $in_service_body_id_bigint, $in_before_string, $in_after_string, $in_object_class_string, $in_before_obj_id_bigint, $in_after_obj_id_bigint, $in_before_obj_lang_enum, $in_after_obj_lang_enum, null, $in_name_string, $in_description_string, $in_lang_enum );
		
		if ( $change_obj instanceof c_comdef_change )
			{
			try
				{
				$change_obj->UpdateToDB();

				$id = $change_obj->GetID();
				}
			catch ( Exception $e )	// We just eat the exception and return null.
				{
				$id = null;
				}
			}
		
		$cid = $in_before_obj_id_bigint;
		if ( !$cid )
			{
			$cid = $in_after_obj_id_bigint;
			}
		
		self::TrimChanges ( $in_object_class_string, $cid );
		
		return $id;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the namespace of this server.
		
		\returns A string, with the namespace. The default is the server URI, with "/CoMDEF" appended.
	*/
	static function GetNamespace()
	{
		return self::$server_instance->_server_namespace;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the name of the format table.
		
		\returns A string, containing the name of the format table.
	*/
	static function GetFormatTableName_obj()
	{
		return self::$server_instance->_format_table_name;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the name of the meetings table.
		
		\returns A string, containing the name of the meetings table.
	*/
	static function GetMeetingTableName_obj()
	{
		return self::$server_instance->_meeting_table_name;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the name of the changes table.
		
		\returns A string, containing the name of the changes table.
	*/
	static function GetChangesTableName_obj()
	{
		return self::$server_instance->_changes_table_name;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the name of the service bodies table.
		
		\returns A string, containing the name of the table.
	*/
	static function GetServiceBodiesTableName_obj()
	{
		return self::$server_instance->_service_bodies_table_name;
	}
	
	/*******************************************************************/
	/**	\brief Simply returns the name of the user table.
		
		\returns A string, containing the name of the table.
	*/
	static function GetUserTableName_obj()
	{
		return self::$server_instance->_user_table_name;
	}
	
	/*******************************************************************/
	/**	\brief Get the local readable string for the server languages.
		
		\returns a reference to the array of strings, containing the server languages in human-readable, local form.
	*/
	static function &GetServerLangs()
	{
		return self::GetServer()->_server_lang_names;
	}
	
	/*******************************************************************/
	/**	\brief Get the object list for the server's registered users.
		
		\returns a reference to the internal c_comdef_users object.
	*/
	static function &GetServerUsersObj()
	{
		return self::GetServer()->_users_obj;
	}
	
	/*******************************************************************/
	/**	\brief Get the object for a single user, given an ID
		
		\returns a reference to the internal c_comdef_user object for the user. Null if not found.
	*/
	static function &GetUserByIDObj(
									$in_user_id_bigint	///< An integer, containing the user ID.
									)
	{
		$ret = null;

		if ( 0 < intval ( $in_user_id_bigint ) )
			{
			$users_obj =& self::GetServer()->_users_obj;
			
			if ( $users_obj instanceof c_comdef_users )
				{
				$ret =& $users_obj->GetUserByID ( $in_user_id_bigint );
				}
			}
		
		return $ret;
	}

	/*******************************************************************/
	/** \brief Given a login and password, looks up the user, and returns
		a reference to that user object.
	
		\returns a reference to a c_comdef_user object. Null if none.
	*/
	static function &GetUserByLoginCredentials(
												$in_login,		///< A string. The login ID.
												$in_password	///< A string. the ENCRYPTED password for the user.
												)
	{
		$ret = null;
		
		$users_obj =& self::GetServer()->_users_obj;
		
		if ( $users_obj instanceof c_comdef_users )
			{
			$ret =& $users_obj->GetUserByLoginCredentials ( $in_login, $in_password );
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Get the current logged-in user, as a c_comdef_user instance.
		
		\returns a reference to a c_comdef_user object, containing the user.
		Null if it failed.
	*/
	static function &GetCurrentUserObj($in_is_ajax = false	///< If it's an AJAX handler, this is true.
										)
	{
	include ( dirname ( __FILE__ ).'/config/auto-config.inc.php' );

	$ret = null;
	
	if ( !isset ( $_SESSION ) )
		{
		session_start();
		}
	
	if ( isset ( $_SESSION[$admin_session_name] ) )
		{
		list ( $login_id, $encrypted_password ) = explode ( "\t", $_SESSION[$admin_session_name] );
		$ret =& self::GetUserByLoginCredentials ( $login_id, $encrypted_password );
		}

	return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Find out if the user is a server admin.
		
		\returns a boolean. True if the user is a server admin.
	*/
	static function IsUserServerAdmin(	$in_user_obj = null,	///< A reference to a c_comdef_user object instance. If null, the current user will be checked.
										$in_is_ajax = false		///< If it's an AJAX handler, we don't regenerate the session. Some browsers seem antsy about that.
										)
	{
	$ret = false;
	
	if ( !($in_user_obj instanceof c_comdef_user) )
		{
		$in_user_obj =& self::GetCurrentUserObj($in_is_ajax);
		}
	
	if ( $in_user_obj instanceof c_comdef_user )
		{
		$ret = ($in_user_obj->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN);
		}
	
	return $ret;
	}
	
	/*******************************************************************/
	/** \brief Given a login and password, looks up the user, and returns
		an encrypted password for that user.
	
		\returns a string, with the encrypted password. Null if none.
	*/
	static function GetEncryptedPW ($in_login,		///< A string. The login ID.
									$in_password	///< A string. the UNENCRYPTED password for the user.
									)
	{
	$ret = null;
	
	$users_obj =& self::GetServer()->_users_obj;
	
	if ( $users_obj instanceof c_comdef_users )
		{
		$ret = $users_obj->GetEncryptedPW ( $in_login, $in_password );
		}
		
	return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Get the objects for all users of a certain user level.
		
		\returns an associative array, with references to the c_comdef_user objects for the relevant users as values, and the user IDs as keys. Null if not found.
	*/
	static function &GetUsersByLevelObj(
										$in_user_level_bigint,	///< An integer, containing the user level.
										$in_or_higher = false,	///< A Boolean. Set this to true to get all users of the given level or higher (numerically lower). Default is false, so only users of the exact level are given.
										$in_include_disabled = false	///< A Boolean. Set this to true to allow disabled users to be included.
										)
	{
		$ret_array = null;
			
		$users_obj =& self::GetServer()->_users_obj;
		
		if ( $users_obj instanceof c_comdef_users )
			{
			$user_array =& $users_obj->GetUsersArray();
			
			foreach ( $user_array as &$user_obj )
				{
				if ( ($user_obj->GetUserLevel() > 0) && ($in_include_disabled || ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED)) && (($user_obj->GetUserLevel() == $in_user_level_bigint) || ($in_or_higher && ($user_obj->GetUserLevel() < $in_user_level_bigint))) )
					{
					$ret_array[$user_obj->GetID()] =& $user_obj;
					}
				}
			}
		
		return $ret_array;
	}
	
	/*******************************************************************/
	/**	\brief Get the object for a single service body, given an ID
		
		\returns a reference to the internal c_comdef_service_body object for the service body. Null if not found.
	*/
	static function &GetServiceBodyByIDObj(
											$in_service_body_id_bigint	///< An integer, containing the service body ID.
											)
	{
		$ret = null;
		$server =& self::GetServer();
		if ( $server instanceof c_comdef_server )
			{
			$array_obj =& $server->GetServiceBodyArray();
			if ( is_array ( $array_obj ) && count ( $array_obj ) )
				{
				foreach ( $array_obj as &$sb )
					{
					if ( $sb instanceof c_comdef_service_body )
						{
						$id = $sb->GetID();
						if ( intval ( $in_service_body_id_bigint ) == intval ( $id ) )
							{
							$ret =& $sb;
							}
						}
					}
				}
			}

		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Given an ID and a language for a format, as well as a code,
		returns true if the code does NOT appear in the DB.
		
		\returns true, if the format key is unique for the language.
		
		\throws an exception if the SQL query fails.
	*/
	static function IsFormatKeyUnique (
										$in_key_string,	///< A string. The key for which to search.
										$in_lang_enum	///< The language for the format.
										)
	{
		$sql = "SELECT * FROM `".self::GetFormatTableName_obj()."` WHERE key_string=? AND lang_enum=?"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $in_key_string, $in_lang_enum ) );
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			return false;
			}
		
		return true;
	}
	
	/*******************************************************************/
	/**	\brief Given an ID and a language for a format, it returns one instance.
		
		This will return one c_comdef_format object, with the parent this server
		(Not a c_comdef_formats object).
		
		\returns a new c_comdef_format object.Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetOneFormat (
								$in_id_bigint,	///< The ID of the formatShared  (An integer)
								$in_lang_enum	///< The language for the format.
								)
	{
		$sql = "SELECT * FROM `".self::GetFormatTableName_obj()."` WHERE shared_id_bigint=? AND lang_enum=? LIMIT 1"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $in_id_bigint, $in_lang_enum ) );
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			foreach ( $rows as $rs )
				{
				// We use the static function in the c_comdef_meeting class to process the data for the meeting.
				return new c_comdef_format ( self::GetServer(),
												$rs['shared_id_bigint'],
												$rs['format_type_enum'],
												$rs['key_string'],
												$rs['icon_blob'],
												$rs['worldid_mixed'],
												$rs['lang_enum'],
												$rs['name_string'],
												$rs['description_string']
												);
				}
			}
		
		return null;
	}
	
	/*******************************************************************/
	/**	\brief Given an ID for a meeting, it returns true if the meeting currently exists.
		
		\returns true if the meeting exists; false, otherwise.
		
		\throws an exception if the SQL query fails.
	*/
	static function DoesMeetingExist(
									$in_id_bigint	///< The ID of the meeting (An integer)
									)
	{
		$sql = "SELECT id_bigint FROM `".self::GetMeetingTableName_obj()."_main` WHERE ".self::GetMeetingTableName_obj()."_main.id_bigint=? LIMIT 1"; 
		
		$ret = false;
		
		$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $in_id_bigint ) );
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			$ret = true;
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Given an ID for a meeting, it returns one instance.
		
		This will return one c_comdef_meeting object, with the parent this server
		(Not a c_comdef_meetings object).
		
		\returns a new c_comdef_meeting object.Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetOneMeeting(
								$in_id_bigint,	    ///< The ID of the meeting (An integer)
								$test_only = false  ///< If true, then this function will only return Boolean true or false (true if the meeting exists)
								)
	{
		$sql = "SELECT * FROM `".self::GetMeetingTableName_obj()."_main` WHERE ".self::GetMeetingTableName_obj()."_main.id_bigint=? LIMIT 1"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $in_id_bigint ) );
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			if ( $test_only )
			    {
			    return true;
			    }
			
			foreach ( $rows as $row )
				{
				// We use the static function in the c_comdef_meeting class to process the data for the meeting.
				$meeting_row = c_comdef_meeting::process_meeting_row ( $row );
				// One difference between this type of meeting and others, is that the parent is the server, not a c_comdef_meetngs object.
				return new c_comdef_meeting ( self::GetServer(), $meeting_row );
				}
			}
		
        if ( $test_only )
            {
            return false;
            }
		
		return null;
	}
	
	/*******************************************************************/
	/**	\brief Given an ID for a change, it returns one instance.
		
		This will return one c_comdef_change object, with the parent this server
		(Not a c_comdef_changes object).
		
		\returns a new c_comdef_change object.Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetOneChange(
								$in_id_bigint	///< The ID of the change (An integer)
								)
	{
		$ret = null;
		
		$sql = "SELECT * FROM `".self::GetChangesTableName_obj()."` WHERE id_bigint=? LIMIT 1";
		
		$changes = self::GetServer()->GetChangesFromSQL ( $sql, array ( $in_id_bigint ) );
		
		if ( $changes instanceof c_comdef_changes )
			{
			$c_array =& $changes->GetChangesObjects();
			
			if ( is_array ( $c_array ) && count ( $c_array ) )
				{
				// Just to spike an associative-only array. Silly, I know, but I've had problems in the past. PHP is wacky.
				foreach ( $c_array as $change )
					{
					$ret = $change;
					break;
					}
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Given an ID for a user, it returns one instance.
		
		This will return one c_comdef_user object, with the parent this server
		(Not a c_comdef_users object).
		
		\returns a new c_comdef_user object.Null if it failed.
	*/
	static function GetOneUser (
								$in_id_bigint	///< The ID of the user (An integer)
								)
	{
		$ret = null;
		
		$sql = "SELECT * FROM `".self::GetUserTableName_obj()."` WHERE id_bigint=? LIMIT 1"; 
		
		$rows = c_comdef_dbsingleton::preparedQuery( $sql, array ( $in_id_bigint ) );
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			foreach ( $rows as $row )
				{
				$ret = new c_comdef_user ( $this, $row['id_bigint'],
												$row['user_level_tinyint'],
												$row['email_address_string'],
												$row['login_string'],
												$row['password_string'],
												$row['lang_enum'],
												$row['name_string'],
												$row['description_string'],
												$row['last_access_datetime'] );
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Get a series of meetings, each identified by an ID. This does
		not filter by any of the other major criteria. It is designed to
		facilitate direct access to meeting objects.
		
		\returns a new c_comdef_meetings object, containing the meetings. Null if it failed.
	*/
	static function GetMeetingsByID(
								$in_id_bigint_array	///< The ID of the meetings (An array of integers)
								)
	{
		$sql = "SELECT * FROM `".self::GetMeetingTableName_obj()."_main` WHERE ";
		
		$first = true;
		
		// We don't actually care what the array contains. We're just counting them out.
		foreach ( $in_id_bigint_array as $in_id_bigint )
			{
			if ( !$first )
				{
				$sql .= " OR ";
				}
			else
				{
				$first = false;
				}
			
			$sql .= "(".self::GetMeetingTableName_obj()."_main.id_bigint=?)";
			}
		
		return self::GetMeetingsFromSQL ( $sql, $in_id_bigint_array );
	}
	
	/*******************************************************************/
	/**	\brief Given a set of one or more main criteria, returns a new
		c_comdef_meetings object with instances of those meetings, loaded
		from the database.
		
		This is the big kahuna. Meeting searches will all use this function
		as a fulcrum for their searches.
		
		NOTE TO UPTIGHT PROGRAMMERS: Yeah, it's a big, massive function
		with a gazillion multi-purpose parameters.
		
		Learn to live with it. It works fine, and makes sense for the context.
		I could break it into a bunch of smaller functions, but that would
		increase the complexity and reduce performance.
		
		\returns a new c_comdef_meetings object, containing the meetings.
		Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetMeetings (
								$in_service_body_id_bigint_array = null,	/**< An array of integers. Unlike the formats criteria, these do an OR function, as each record only has
																				 one Service Body ID field, so each selection broadens the search. If there are no IDs selected, or
																				 only NOT IDs have been selected, then all the Service bodies will be searched, with the exception
																				 of any that are selected as NOT (Negative value).
																			*/
								$in_lang_filter_array = null,		/**< If an element of this array is set to a language enum ("en", "es", etc.), it will only return meetings whose
																		 'lang_enum' field is of that value. If the array is null, all languages are searched. If the enum is preceded
																		 by a minus sign (-), then the language is filtered against in the search.
																	*/
								$in_weekday_tinyint_array = null,	/**< The weekday (An array of integer 1-Sunday, 7-Saturday). Optional. If null, all days will be returned.
																		 Each day chosen widens the search. If the weekday is negative, then that is specifically filtered against in
																		 the search.
																	*/
								$in_formats = null,					/**< An array of integers. These are formats. The filtering will be an "AND" filtering, so qualified meetings
																		 must have all of the given formats. If a format is given as a negative number, it is a NOT. Make sure that
																		 you don't have two versions of the same format code, as nothing will be returned.
																	*/
								$in_start_after = null,				///< An epoch time (seconds, as returned by time()), that denotes the earliest starting time allowed.
								$in_start_before = null,			///< An epoch time (seconds, as returned by time()), that denotes the latest starting time allowed.
								$in_min_duration = null,			///< The number of seconds a meeting should last as a minimum.
								$in_max_duration = null,			///< The number of seconds a meeting can last, at most.
								$in_search_rect_array = null,		/**< An array of floating-point numbers, representing longitude and latitude for a rectangle. This is used
																		 to restrict the search to a certain geographic area. It is an associative array:
																		 - ['east'] = longitude of the Eastern side of the rectangle
																		 - ['west'] = longitude of the Western side of the rectangle
																		 - ['north'] = latitude of the Northern side of the rectangle
																		 - ['south'] = latitude of the Southern side of the rectangle
																	*/
								$in_first = null,					/**< A positive integer. This is for paged results. This is the index (0-based) of the first result to be returned.
																		 If $in_num is specified, and this is null, then it is assumed to be 0. If $in_num is null, then this is ignored.
																	*/
								&$in_num = null,						/**< A reference to a positive integer. This is the maximum number of results to return. If null, then there will be
																		 limits placed on the query.
																		 If less than the maximum are returned, this is adjusted to reflect how many were returned.
																		 
																		 NOTE: For non-MySQL and non-Oracle DBs, this may not equal the number of meetings returned in the function
																		 result! This is because, for those databases, filtering by format code needs to be done after the actual
																		 database query.
																		 
																		 This will have the actual value of the number of results in the database query, so you can use this to
																		 walk through the database. If you need the actual number of meetings returned, the best way to do this is
																		 to do a c_comdef_meetings::GetNumMeetings() function on the returned object.
																	*/
								$in_published = 0					/**< Indicates whether or not to search for published meetings. This only counts if the user is logged in.
																			- -1	Search for ONLY unpublished meetings
																			-  0	Search for published and unpublished meetings.
																			-  1	Search for ONLY published meetings.
																	*/
								)
	{
		$previous = false;	// This is used to tell subsequent tests to use AND instead of WHERE
		
		$sql = "SELECT * FROM `".self::GetMeetingTableName_obj()."_main`"; 
		$ar = array ( );
		
		if ( is_array ( $in_service_body_id_bigint_array ) && count ( $in_service_body_id_bigint_array ) )
			{
			$sql .= " WHERE (";
			$previous = true;
			$first = true;
			foreach ( $in_service_body_id_bigint_array as $service_body_id )
				{
				$service_body_id = intval ( $service_body_id );
				$sql_x = "";
				if ( $service_body_id < 0 )
					{
					$service_body_id = abs ( $service_body_id );
					$sql_x = " NOT ";
					
					if ( !$first )
						{
						$sql_x = " AND $sql_x";
						}
					
					$first = true;	// This makes the OR get skipped.
					}

				if ( !$first )
					{
					$sql_x .= " OR ";
					}
				else
					{
					$first = false;
					}
					
				$sql .= "$sql_x(".self::GetMeetingTableName_obj()."_main.service_body_bigint=?)";
				array_push ( $ar, $service_body_id );
				}
			$sql .= ")";
			}
		
		if ( is_array ( $in_lang_filter_array ) && count ( $in_lang_filter_array ) )
			{
			if ( $previous )
				{
				$sql .= " AND ";
				}
			else
				{
				$sql .= " WHERE ";
				$previous = true;
				}
			
			$sql .= "(";
			
			$first = true;
			foreach ( $in_lang_filter_array as $lang )
				{
				$not = preg_match ( "|^\-(.*)|", $lang, $matches );
				
				$sql_x = "";
				if ( $not )
					{
					$lang = $matches[1];
					$sql_x = " NOT ";
					
					if ( !$first )
						{
						$sql_x = " AND $sql_x";
						}
					
					$first = true;	// This makes the OR get skipped.
					}

				if ( !$first )
					{
					$sql_x .= " OR ";
					}
				else
					{
					$first = false;
					}
					
				$sql .= "$sql_x(".self::GetMeetingTableName_obj()."_main.lang_enum=?)";

				array_push ( $ar, $lang );
				}
			$sql .= ")";
			}
		
		if ( is_array ( $in_weekday_tinyint_array ) && count ( $in_weekday_tinyint_array ) )
			{
			$valid = false;
			
			foreach ( $in_weekday_tinyint_array as $weekday )
				{
				if ( intval ( $weekday ) > 0 && intval ( $weekday ) < 8 )
					{
					$valid = true;
					}
				}
			
			if ( $valid )
				{
				if ( $previous )
					{
					$sql .= " AND ";
					}
				else
					{
					$sql .= " WHERE ";
					$previous = true;
					}
				
				$sql .= "(";
				
				$first = true;
				foreach ( $in_weekday_tinyint_array as $weekday )
					{
					$weekday = intval ( $weekday );
					$sql_x = "";
					if ( $weekday < 0 )
						{
						$weekday = abs ( $weekday );
						$sql_x = " NOT ";
						
						if ( !$first )
							{
							$sql_x = " AND $sql_x";
							}
						
						$first = true;	// This makes the OR get skipped.
						}
	
					if ( !$first )
						{
						$sql_x .= " OR ";
						}
					else
						{
						$first = false;
						}
					
					$sql .= "$sql_x(".self::GetMeetingTableName_obj()."_main.weekday_tinyint=?)";
					array_push ( $ar, $weekday-1 );
					}
				$sql .= ")";
				}
			}

		// We explicitly set null entries, because we've seen problems with using nulls.
		if ( null != $in_start_after )
			{
			$in_start_after = date ( "H:i:00", intval ( $in_start_after ) );
			}
		else
			{
			$in_start_after = "00:00:00";
			}
		
		if ( null != $in_start_before )
			{
			$in_start_before = date ( "H:i:00", intval ( $in_start_before ) );
			}
		else
			{
			$in_start_before = "00:00:00";
			}
		
		if ( null != $in_min_duration )
			{
			$in_min_duration = date ( "H:i:00", intval ( $in_min_duration ) );
			}
		else
			{
			$in_min_duration = "00:00:00";
			}
		
		if ( null != $in_max_duration )
			{
			$in_max_duration = date ( "H:i:00", intval ( $in_max_duration ) );
			}
		else
			{
			$in_max_duration = "00:00:00";
			}

		if ( $in_start_after != "00:00:00" )
			{
			if ( $previous )
				{
				$sql .= " AND ";
				}
			else
				{
				$sql .= " WHERE ";
				$previous = true;
				}
			$sql .= self::GetMeetingTableName_obj()."_main.start_time>?";

			array_push ( $ar, $in_start_after );
			}
		
		if ( $in_start_before != "00:00:00" )
			{
			if ( $previous )
				{
				$sql .= " AND ";
				}
			else
				{
				$sql .= " WHERE ";
				$previous = true;
				}
			$sql .= self::GetMeetingTableName_obj()."_main.start_time<?";

			array_push ( $ar, $in_start_before );
			}
		
		if ( $in_min_duration != "00:00:00" )
			{
			if ( $previous )
				{
				$sql .= " AND ";
				}
			else
				{
				$sql .= " WHERE ";
				$previous = true;
				}
			$sql .= self::GetMeetingTableName_obj()."_main.duration_time>=?";
			
			array_push ( $ar, $in_min_duration );
			}
		
		if ( $in_max_duration != "00:00:00" )
			{
			if ( $previous )
				{
				$sql .= " AND ";
				}
			else
				{
				$sql .= " WHERE ";
				$previous = true;
				}
			$sql .= self::GetMeetingTableName_obj()."_main.duration_time<=?";
			
			array_push ( $ar, $in_max_duration );
			}
		
		if ( is_array ( $in_search_rect_array ) && isset ( $in_search_rect_array['east'] ) && isset ( $in_search_rect_array['west'] ) && isset ( $in_search_rect_array['north'] ) && isset ( $in_search_rect_array['south'] ) )
			{
			if ( $previous )
				{
				$sql .= " AND ";
				}
			else
				{
				$sql .= " WHERE ";
				$previous = true;
				}
			
			$east = floatval ( $in_search_rect_array['east'] );
			$west = floatval ( $in_search_rect_array['west'] );
			$north = floatval ( $in_search_rect_array['north'] );
			$south = floatval ( $in_search_rect_array['south'] );
			$sql .= "(";
				if ( $east > $west )
					{
					$sql .= "(longitude >= $west) AND (longitude <= $east)";
					}
				else
					{
					$sql .= "(longitude <= $west) AND (longitude >= $east)";
					}
				
				$sql .= " AND (latitude <= $north) AND (latitude >= $south)";
			$sql .= ")";
			}
		
		if ( c_comdef_server::GetCurrentUserObj() instanceof c_comdef_user )
			{
			// Logged-in users can see both published and unpublished meetings.
			if ( $in_published != 0 )
				{
				if ( $previous )
					{
					$sql .= " AND ";
					}
				else
					{
					$sql .= " WHERE ";
					$previous = true;
					}
				
				if ( $in_published == -1 )
					{
					$sql .= "(published=0)";
					}
				else
					{
					$sql .= "(published=1)";
					}
				}
			}
		else	// If we are not logged in, we can only see published meetings.
			{
			if ( $previous )
				{
				$sql .= " AND ";
				}
			else
				{
				$sql .= " WHERE ";
				$previous = true;
				}
			
			$sql .= "(published=1)";
			}
				
		$ret = null;
		
		if ( is_array ( $in_formats ) && count ( $in_formats ) )
			{
			// MySQL and Oracle allow the REGEXP test, but other DBs don't.
			if ( ('mysql' == c_comdef_dbsingleton::pdoInstance()->getAttribute ( PDO::ATTR_DRIVER_NAME ))
				|| ('oracle' == c_comdef_dbsingleton::pdoInstance()->getAttribute ( PDO::ATTR_DRIVER_NAME )) )
				{
				if ( $previous )
					{
					$sql .= " AND (";
					}
				else
					{
					$sql .= " WHERE (";
					}
				
				$column = self::GetMeetingTableName_obj()."_main.formats";
				
				$first = true;
				
				foreach ( $in_formats as $format )
					{
					$format = trim ( $format );
					
					if ( $format )
						{
						// This is why we don't need to scrub the input. No SQL injection here.
						$format = intval ( $format );
						if ( !$first )
							{
							$sql .= " AND ";
							}
						else
							{
							$first = false;
							}
						
						// This is a test for if the format is a NOT.
						if ( $format < 0 )
							{
							$format = abs ( $format );
							$sql .= "NOT ";
							}
						
						$sql .= "(($column REGEXP";
						
						// Oracle uses REGEXP_LIKE
						if ( 'oracle' == c_comdef_dbsingleton::pdoInstance()->getAttribute ( PDO::ATTR_DRIVER_NAME ) )
							{
							$sql .= "_LIKE";
							}
						
						$sql .= "'(^|,)$format(,|\$)'))";
						}
					}
				
				$sql .= ")";
				}
			else	// Non-MySQL, Non-Oracle servers don't have REGEX in their SQL, so we need to search by hand.
				{
				$sql .= " ORDER BY service_body_bigint, id_bigint";
				if ( intval ( $in_num ) )
					{
					$in_first = intval ( $in_first );
					$in_num = intval ( $in_num );
					$sql .= " LIMIT $in_first, $in_num";
					}
				
				$ret = self::GetMeetingsFromSQL ( $sql, $ar );
				
				if ( $ret && intval ( $in_num ) )
					{
					$in_num = $ret->GetNumMeetings ( );
					}
				elseif ( intval ( $in_num ) )
					{
					$in_num = 0;
					}
				
				$ret = self::ParseMeetingsByFormats ( $ret, $in_formats );
				}
			}
		
		if ( !$ret )
			{
			$sql .= " ORDER BY service_body_bigint, id_bigint";
		
			if ( intval ( $in_num ) )
				{
				$in_first = intval ( $in_first );
				$in_num = intval ( $in_num );
				$sql .= " LIMIT $in_first, $in_num";
				}
			
			$ret = self::GetMeetingsFromSQL ( $sql, $ar );
			
			if ( intval ( $in_num ) )
				{
				$in_num = count ( $ret->GetMeetingObjects() );
				}

			if ( $ret && isset ( $east ) && isset ( $west ) && isset ( $north ) && isset ( $south ) )
				{
				$center_lat = ($north + $south) / 2.0;
				$center_long = ($east + $west) / 2.0;
				$meetings =& $ret->GetMeetingObjects();
				foreach ( $meetings as &$meeting )
					{
					$dist = c_comdef_meetings::GetDistance ( $center_lat, $center_long, $meeting->GetMeetingDataValue('latitude'), $meeting->GetMeetingDataValue('longitude') );
					$meeting->_distance_in_km = $dist;
					$meeting->_distance_in_miles = $dist * 1.609344;
					$meeting->AddDataField ( 'distance_in_km', 'distance_in_km', $meeting->_distance_in_km );
					$meeting->AddDataField ( 'distance_in_miles', 'distance_in_miles', $meeting->_distance_in_miles );
					}
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief Returns a c_comdef_meetings_object, containing all the meetings
		directly "owned" by the Service Body whose ID is submitted.
		
		\returns a new c_comdef_meetings object, containing the meetings.
		Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetMeetingsForAServiceBody (
												$in_sb_id	///< An integer. The ID of the Service Body.
												)
	{
		$sql = "SELECT * FROM `".self::GetMeetingTableName_obj()."_main` WHERE ".self::GetMeetingTableName_obj()."_main.service_body_bigint=? ORDER BY id_bigint";
		
		return self::GetMeetingsFromSQL ( $sql, array ( $in_sb_id ) );
	}
	
	/*******************************************************************/
	/**	\brief Returns a c_comdef_meetings_object, containing all the meetings (Published and unpublished).
		
		\returns a new c_comdef_meetings object, containing the meetings.
		Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetAllMeetings (    &$in_out_numResults,    ///< This must be supplied. It is a pass-by-reference that indicates how many meetings are being returned.
	                                    $in_numResults = null,  ///< This is how many results we want in this call.
	                                    $in_startIndex = null   ///< This is the 0-based starting index
	                                )
	{
	    $in_out_numResults = 0;
	    
	    if ( !isset ( $in_startIndex ) )
	        {
	        $in_startIndex = 0;
	        }
	    
	    if ( isset ( $in_numResults ) )
	        {
	        $in_numResults += $in_startIndex;
	        }
	    else
	        {
	        $in_startIndex = null;
	        }
	    
		$sql = "SELECT * FROM `".self::GetMeetingTableName_obj()."_main`";
		
		if ( $in_startIndex || $in_numResults )
		    {
		    $sql .= ' LIMIT ';
		    
            $sql .= $in_startIndex.', '.$in_numResults;
		    }

		$ret = self::GetMeetingsFromSQL ( $sql );
	 
		if ( $ret instanceof c_comdef_meetings )
		    {
		    $in_out_numResults = $ret->GetNumMeetings();
		    }
		    
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief This is an alternative to the MySQL REGEXP test. It will
		go through all the meetings returned by a broad query, and remove
		any that do not contain all of the given formats.
		
		\returns the passed-in c_comdef_meetings object, containing the remaining meetings. Null if it failed.
	*/
	private static function ParseMeetingsByFormats( $in_meetings,	///< The result of c_comdef_server::GetMeetingsFromSQL()
											$in_formats		///< An array of integers. These are formats. The filtering will be an "AND" filtering, so qualified meetings must have all of the given formats.
											)
	{
		$meetings =& $in_meetings->GetMeetingObjects();
		foreach ( $meetings as &$meeting )
			{
			$data =& $meeting->GetMeetingData();
			$formats = $data['formats'];
			$found = 0;
			foreach ( $formats as $key => $value )
				{
				if ( false !== array_search ( intval ( $key ), $in_formats ) )
					{
					$found++;
					}
				}
			
			// If we didn't find them all, we nuke the meeting.
			if ( $found != count ( $in_formats ) )
				{
				$in_meetings->RemoveMeeting ( $meeting->GetID() );
				}
			}
		
		return ( $in_meetings );
	}

	/*******************************************************************/
	/**	\brief Given an SQL statement and a value array (for PDO prepared
		statements), return a new c_comdef_meetings object, loaded with the
		instances of the meetings that were returned from the query.
		
		\returns a new c_comdef_meetings object, containing the meetings. Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	private static function GetMeetingsFromSQL( $in_sql,				///< The prepared statement SQL query
												$in_value_array = null	///< An array of values for the prepared statement.
												)
	{
		$rows = c_comdef_dbsingleton::preparedQuery( $in_sql, $in_value_array );
		
		$meeting_data = array();
		$this_meetings_object = null;
		
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			foreach ( $rows as $row )
				{
				// We use the static function in the c_comdef_meeting class to process the data for the meeting.
				$meeting_row = c_comdef_meeting::process_meeting_row ( $row );
				// At this point, we have all the data for this one meeting, culled from its three tables and aggregated into an array.
				// Add this to our aggregator array.
				$meeting_data[$row['id_bigint']] = $meeting_row;
				}
		
			// We now instantiate a c_comdef_meetings object, and create our c_comdef_meeting objects.
			$this_meetings_object = new c_comdef_meetings ( self::GetServer(), $meeting_data );
			}
		
		return $this_meetings_object;
	}
	
	/*******************************************************************/
	/**	\brief	Gets a list of all change objects of a certain type, or
		only one, if the change affects a certain class, and an ID is
		given for that class (not the change ID -the ID of the changed
		object).
		
		\returns a new c_comdef_changes object, containing the changes. Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetChangesFromIDAndType(
											$in_type,				/**< The class type for the changes
																		One of these values:
																		- c_comdef_meeting
																		- c_comdef_service_body
																		- c_comdef_user
																		- c_comdef_format
																	*/
											$in_id = null,			///< The ID for the object. If not specified, all changes for the given type will be returned (WARNING: Could be a great many).
											$in_start_date = null,	///< If you specify a start date (In PHP time() format), then only changes on, or after this date will be returned.
											$in_end_date = null		///< If you specify an end date (In PHP time() format), then only changes that occurred on, or before this date will be returned.
											)
	{
		$sql = "SELECT * FROM `".self::GetChangesTableName_obj()."` WHERE";
		
		if ( null != $in_id )
			{
			$in_id = intval ( $in_id );
			$sql .= " ((before_id_bigint=$in_id) OR (after_id_bigint=$in_id)) AND";
			}
			
		if ( intval($in_start_date) )
			{
			$start_date = date ( 'Y-m-d 00:00:00', intval($in_start_date) );
			$sql .= " (change_date>='$start_date') AND";
			}
			
		if ( intval($in_end_date) )
			{
			$end_date = date ( 'Y-m-d 23:59:59', intval($in_end_date) );
			$sql .= " (change_date<='$end_date') AND";
			}
		
		$sql .= " (object_class_string=?) ORDER BY change_date DESC";
		
		return self::GetServer()->GetChangesFromSQL ( $sql, array ( $in_type ) );
	}
	
	/*******************************************************************/
	/**	\brief	This function allows you to get a list of changes by object
		type, and change type (such as all deleted meetings, or all rolled-back
		formats).
		
		\returns a new c_comdef_changes object, containing the changes. Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetChangesFromOTypeAndCType(
											$in_o_type,		/**< The class type for the changes
																One of these values:
																	- c_comdef_meeting
																	- c_comdef_service_body
																	- c_comdef_user
																	- c_comdef_format
															*/
											$in_change_type	/**< The change type.
																Can be:
																	- 'comdef_change_type_new' - New object
																	- 'comdef_change_type_delete' - Deleted the object
																	- 'comdef_change_type_change' - Changed existing object
																	- 'comdef_change_type_rollback' - Rolled existing object back to a previous version
															*/
											)
	{
		$sql = "SELECT * FROM `".self::GetChangesTableName_obj()."` WHERE (object_class_string=?) AND (change_type_enum=?) ORDER BY change_date DESC";
		
		return self::GetServer()->GetChangesFromSQL ( $sql, array ( $in_o_type, $in_change_type ) );
	}

	static function getKmPerLonAtLat($dLatitude)
	{
		return 111.321 * cos(deg2rad ( $dLatitude ));
	}
	
	/*******************************************************************/
	/** \brief This is a static utility function that takes a specified
		radius and center point and calculates a square, in longitude and
		latitude points, that encompasses that radius. This greatly narrows
		the scope of the search, so the radius calculation will simply eliminate
		any meetings that are "in the corners."
		
		\returns an array of floating-point values, in the following form:
			 - ['east'] = longitude of the Eastern side of the rectangle
			 - ['west'] = longitude of the Western side of the rectangle
			 - ['north'] = latitude of the Northern side of the rectangle
			 - ['south'] = latitude of the Southern side of the rectangle
	*/
	static function GetSquareForRadius ($in_radius,				///< A positive floating-point number. The radius, in kilometers.
										$in_long_in_degrees,	///< The longitude needs to be specified in degrees.
										$in_lat_in_degrees		///< The latitude needs to be specified in degrees.
										)
	{
		$loc = null;
		$vert_radius = $in_radius / 111.000;
		$horiz_radius = $in_radius / (111.321 * cos(deg2rad ( $in_lat_in_degrees )));
		
		$loc['east'] = $in_long_in_degrees - $horiz_radius;
		$loc['west'] = $in_long_in_degrees + $horiz_radius;
		$loc['north'] = $in_lat_in_degrees + $vert_radius;
		$loc['south'] = $in_lat_in_degrees - $vert_radius;
		
		return $loc;
	}
	
	/*******************************************************************/
	/**	\brief Return SQL for the given coordinates.
		
		\returns a string, containing the SQL clause.
		
		\throws an exception if the SQL query fails.
	*/
	static function GetRadiusSQL ( $in_search_rect_array	/**< An array of floating-point values.
																	- ['east'] = longitude of the Eastern side of the rectangle
																	- ['west'] = longitude of the Western side of the rectangle
																	- ['north'] = latitude of the Northern side of the rectangle
																	- ['south'] = latitude of the Southern side of the rectangle
															*/
								)
	{
		$east = floatval ( $in_search_rect_array['east'] );
		$west = floatval ( $in_search_rect_array['west'] );
		$north = floatval ( $in_search_rect_array['north'] );
		$south = floatval ( $in_search_rect_array['south'] );
		$sql = " (";
			if ( $east > $west )
				{
				$sql .= "(longitude >= $west) AND (longitude <= $east)";
				}
			else
				{
				$sql .= "(longitude <= $west) AND (longitude >= $east)";
				}
			
			$sql .= " AND (latitude <= $north) AND (latitude >= $south)";
		$sql .= ")";
	
		return $sql;
	}

	/*******************************************************************/
	/**	\brief Find the smallest radius that contains at least the given number of meetings.
		The way this works is that the center is set, and the optimal
		radius is selected in kilometers to deliver that many meetings.
		The radius starts at 25 Km (about 10 miles), and goes up or
		down in 5Km "clicks." Under 5Km, it reduces to 0.5Km "clicks."
		It will not go out more than 100Km.
		
		When it passes the threshold for the number of meetings in the
		square, the radius is selected, and the _search_radius is set
		to the number of Kilometers.
		
		We are not looking for an exact meeting count. It should select the
		first radius that contains AT LEAST the number of meetings requested.
		
		If not enough meetings are found, the radius ends up at 0.
		
		\returns a radius, in Km, for a result. Null if none found.
		
		\throws an exception if the SQL query fails.
	*/
	static function HuntForRadius(
									$in_search_result_count,	///< A positive integer. It specifies the number of meetings to find.
									$in_long_in_degrees,		///< The longitude needs to be specified in degrees.
									$in_lat_in_degrees,			///< The latitude needs to be specified in degrees.
									$in_weekday_tinyint_array	///< An array of weekdays in which to filter for.
									)
	{
		$ret = null;
		$current_radius = 25.0;
		
	    $localized_strings = self::GetLocalStrings();
		$sql1 = "SELECT COUNT(*) FROM `".self::GetMeetingTableName_obj()."_main` WHERE";
		$sql3 = '';
		
		if ( is_array ( $in_weekday_tinyint_array ) && count ( $in_weekday_tinyint_array ) )
			{
			$sql1 .= " (";
			
			$first = true;
			foreach ( $in_weekday_tinyint_array as $weekday )
				{
				$weekday = intval ( $weekday );
				$sql_x = "";
				if ( $weekday < 0 )
					{
					$weekday = abs ( $weekday );
					$sql_x = " NOT ";
					
					if ( !$first )
						{
						$sql_x = " AND $sql_x";
						}
					
					$first = true;	// This makes the OR get skipped.
					}

				if ( !$first )
					{
					$sql_x .= " OR ";
					}
				else
					{
					$first = false;
					}
				
				$sql1 .= "$sql_x(".self::GetMeetingTableName_obj()."_main.weekday_tinyint=".strval ( $weekday - 1 ).")";
				}
			$sql1 .= ") AND (";
			$sql3 = ")";
			}

		$count = 0;

		$was_less = false;
		do
			{
			$current_hunt_value = ($current_radius > 25) ? 5 : (($current_radius > 1) ? 0.5 : .0625);
			
			$square = self::GetSquareForRadius ( $current_radius, $in_long_in_degrees, $in_lat_in_degrees );
			
			$sql2 = self::GetRadiusSQL ( $square );
			
			$sql = $sql1.$sql2.$sql3;
			
			$rows = c_comdef_dbsingleton::preparedQuery( $sql, array() );

			if ( is_array ( $rows ) && count ( $rows ) )
				{
				$count = intval ( $rows[0]["count(*)"] );

				if ( $in_search_result_count > $count  )
					{
					$current_radius += $current_hunt_value;
					$was_less = true;
					}
				else
					{
					if ( $was_less )
						{
						foreach ( $localized_strings['comdef_map_radius_ranges'] as $range )
							{
							$range_comp = $range;
							if ( $localized_strings['dist_units'] == 'mi' )
								{
								$range_comp *= 1.609344;
								$range *= 1.609344;
								}
							
							if ( $current_radius <= $range_comp )
								{
								return $range;
								}
							}
						break;
						}
					else
						{
						$was_less = false;
						$current_radius -= $current_hunt_value;
						}
					}
				}
			else
				{
				break;
				}
			
			} while ( ($current_radius > 0.0625) && ($current_radius < 100) );
		
		return $current_radius;
	}
	
	/*******************************************************************/
	/**	\brief Given an SQL statement and a value array (for DBO prepared
		statements), return a new c_comdef_changes object, loaded with the
		instances of the changes that were returned from the query.
		
		\returns a new c_comdef_changes object, containing the changes. Null if it failed.
		
		\throws an exception if the SQL query fails.
	*/
	function GetChangesFromSQL(
								$in_sql,				///< The prepared statement SQL query
								$in_value_array = null	///< An array of values for the prepared statement.
								)
	{
		$rows = c_comdef_dbsingleton::preparedQuery( $in_sql, $in_value_array );
		
		$this_changes_object = null;
		
		if ( is_array ( $rows ) && count ( $rows ) )
			{
			// We now instantiate a c_comdef_changes object, and create our c_comdef_change objects.
			$this_changes_object = new c_comdef_changes ( $this, $rows );
			}
		
		return $this_changes_object;
	}
	
	/*******************************************************************/
	/**	\brief This gets the appropriate language files, and puts all the
		the strings into an associative array. If a language enum is passed in,
		and the language directory exists, then the strings are loaded from
		that directory. If nothing is passed in, the first place we look is
		in the HTTP query, to see if a 'lang_enum' query is present. If it is there,
		we use that. If not, we use the base server language.
		
		\returns an associative array of local strings. Null if it failed.
			- 'name'							The name of the language, in the language itself.
			- 'enum'							The code for the language.
			- 'weekdays'						An array of weekday names. 0 -> Sunday, 6 -> Saturday
			- 'prompt_delimiter'				The character used to delimit prompts (usually a colon ':').
			- 'comdef_map_radius_ranges'		An array of floating point numbers that indicate the choices for the radius selector (in miles).
			- 'comdef_search_results_strings'	An associative array, with all the various strings used in most displays.
			- 'comdef_search_admin_strings'		An associative array, with strings used only in administration.
			- 'comdef_format_types'				An associative array that maps format classes to their descriptions.
			- 'change_type_strings'				An associative array that maps the types of changes to their descriptions.
			- 'detailed_change_strings'			An associative array that maps detailed descriptions of itemized changes.
			- 'end_change_report'				The character used to end a change report (usually a period '.').
	*/
	static function GetLocalStrings ( $in_lang_enum = null	///< An enumeration string, indicating the language desired. If provided, it overrides all else.
									)
	{
	if ( !is_array ( c_comdef_server::$server_local_strings ) || !count ( c_comdef_server::$server_local_strings ) )
	    {
        // This will create the SINGLETON server if one does not yet exist.
        $server = self::MakeServer();
        
        if ( $server instanceof c_comdef_server )
            {
            $lang_enum = $server->GetLocalLang();
            
            if ( isset ( $_GET['lang_enum'] ) && $_GET['lang_enum'] && file_exists ( dirname ( __FILE__ ).'/config/lang/'.$_GET['lang_enum'] ) )
                {
                $lang_enum = $_GET['lang_enum'];
                }
        
            if ( isset ( $_POST['lang_enum'] ) && $_POST['lang_enum'] && file_exists ( dirname ( __FILE__ ).'/config/lang/'.$_POST['lang_enum'] ) )
                {
                $lang_enum = $_POST['lang_enum'];
                }
            
            if ( $in_lang_enum && file_exists ( dirname ( __FILE__ ).'/config/lang/'.$in_lang_enum ) )
                {
                $lang_enum = $in_lang_enum;
                }
			include ( dirname ( __FILE__ )."/config/comdef-config.inc.php" );
            include ( dirname ( __FILE__ ).'/config/lang/'.$lang_enum.'/search_results_strings.inc.php' );
            include ( dirname ( __FILE__ ).'/config/lang/'.$lang_enum.'/search_admin_strings.inc.php' );
            include ( dirname ( __FILE__ ).'/config/lang/'.$lang_enum.'/change_desc.inc.php' );
            include ( dirname ( __FILE__ ).'/config/lang/'.$lang_enum.'/format_codes.inc.php' );

            global	$comdef_global_more_details_address,	///< This is a format string for the way the address line is displayed in the "more details" screen.
                    $comdef_global_list_address;			///< The same, but for the list.
		
            c_comdef_server::$server_local_strings['name'] = file_get_contents ( dirname ( __FILE__ ).'/config/lang/'.$lang_enum.'/name.txt' );
            c_comdef_server::$server_local_strings['enum'] = $lang_enum;
            c_comdef_server::$server_local_strings['theme'] = $theme;
            c_comdef_server::$server_local_strings['weekdays'] = explode ( ",", file_get_contents ( dirname ( __FILE__ ).'/config/lang/'.$lang_enum.'/weekdays.csv' ) );
            c_comdef_server::$server_local_strings['comdef_map_radius_ranges'] = $comdef_map_radius_ranges;
            c_comdef_server::$server_local_strings['region_bias'] = $region_bias;
            c_comdef_server::$server_local_strings['search_spec_map_center'] = $search_spec_map_center;
            c_comdef_server::$server_local_strings['comdef_search_results_strings'] = $comdef_search_results_strings;
            c_comdef_server::$server_local_strings['comdef_search_admin_strings'] = $comdef_search_admin_strings;
            c_comdef_server::$server_local_strings['comdef_format_types'] = $comdef_format_types;
            c_comdef_server::$server_local_strings['change_type_strings'] = $change_type_strings;
            c_comdef_server::$server_local_strings['detailed_change_strings'] = $detailed_change_strings;
            c_comdef_server::$server_local_strings['prompt_delimiter'] =  defined('__PROMPT_DELIMITER__') ? __PROMPT_DELIMITER__ : ':';
            c_comdef_server::$server_local_strings['end_change_report'] =  defined('_END_CHANGE_REPORT') ? _END_CHANGE_REPORT : '.';
            c_comdef_server::$server_local_strings['charset'] = defined('__HTML_DISPLAY_CHARSET__') ? __HTML_DISPLAY_CHARSET__ : 'UTF-8';
            c_comdef_server::$server_local_strings['show_sb_text'] = (isset ( $comdef_show_sb_desc ) && $comdef_show_sb_desc) ? true : false;
            c_comdef_server::$server_local_strings['default_sort_key'] = $default_sort_key;
            c_comdef_server::$server_local_strings['default_sort_dir'] = $default_sort_dir;
            c_comdef_server::$server_local_strings['page_display_size'] = $page_display_size;
            c_comdef_server::$server_local_strings['results_per_page'] = $results_per_page;
            c_comdef_server::$server_local_strings['time_format'] = $time_format;
            c_comdef_server::$server_local_strings['allow_contact_form'] = $allow_contact_form;
            c_comdef_server::$server_local_strings['recursive_contact_form'] = $recursive_contact_form;
            c_comdef_server::$server_local_strings['sort_depth'] = $sort_depth;
            c_comdef_server::$server_local_strings['default_sorts'] = $default_sorts;
            c_comdef_server::$server_local_strings['number_of_meetings_for_auto'] = $number_of_meetings_for_auto;
            c_comdef_server::$server_local_strings['comdef_global_more_details_address'] = $comdef_global_more_details_address;
            c_comdef_server::$server_local_strings['comdef_global_list_address'] = $comdef_global_list_address;
            
            if ( trim($comdef_distance_units) )
                {
                c_comdef_server::$server_local_strings['dist_units'] = strtolower(trim($comdef_distance_units));
                }
            else
                {
                c_comdef_server::$server_local_strings['dist_units'] = 'mi';
                }
            
            if ( isset ( $default_timezone ) && $default_timezone )
                {
                date_default_timezone_set ( $default_timezone );
                }
            
            ini_set ( 'default_charset', '' );
            }
        }
	
	return c_comdef_server::$server_local_strings;
	}
	
	/*******************************************************************/
	/** \brief Return all the Service Bodies this user is authorized with
	
		\returns an associative array. The key is the ID of the Service Body, and the value is:
			- 'principal' If the user is a principal admin
			- 'editor' If the user is a secondary editor.
			Returns null if the user is not cleared for any Service Body.
	*/
	static function GetUserServiceBodies ( $in_user_id = null	///< The ID of the user. If not provided, the current user is checked.
											)
	{
		$ret = null;
		
		if ( !$in_user_id )
			{
			$in_user_id = self::GetCurrentUserObj()->GetID();
			}
		
		$service_bodies =& c_comdef_server::GetServer()->GetServiceBodyArray();
		
		if ( is_array ( $service_bodies ) && count ( $service_bodies ) )
			{
			foreach ( $service_bodies as &$service_body )
				{
				$is_editor = null;
				if ( $service_body instanceof c_comdef_service_body )
					{
					$editors = $service_body->GetEditors();
					
					if ( is_array ( $editors ) && count ( $editors ) )
						{
						if ( in_array ( $in_user_id, $editors ) )
							{
							$is_editor = 'editor';
							}
						}
					
					if ( $service_body->GetPrincipalUserID() == $in_user_id )
						{
						$is_editor = 'principal';
						}
					}
				
				if ( $is_editor )
					{
					$ret[$service_body->GetID()] = $is_editor;
					}
				}
			}
		
		return $ret;
	}
};
?>