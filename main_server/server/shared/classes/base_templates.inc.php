<?php
/***********************************************************************/
/** \file	base_templates.inc.php
	\brief	The base templates for all our classes. This implements the
	i_comdef_searchable and i_comdef_db_stored interfaces and the
	t_local_id_class, t_comdef_local_type and t_comdef_world_type abstract classes.
	
	This file contains a number of abstract and utility classes, as well
	as a couple of interfaces.
*/

defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

//require_once ( dirname ( __FILE__ )."/../../config/comdef-config.inc.php" );
require_once ( dirname ( __FILE__ )."/comdef_utilityclasses.inc.php" );

/*******************************************************************/
/**	\interface i_comdef_searchable
	\brief This interface describes some functions that should allow a derived class to be searched.
*/
interface i_comdef_searchable
{
	/// Add a single search criteria to the object's next search.
	function AddSearchCriteria($in_criteria_element_mixed);
	/// Execute a search, using the previously added criteria.
	function ExecuteSearch();
};

/*******************************************************************/
/**	\interface i_comdef_has_parent
	\brief Simply declares an interface for having a "container" object.
*/
interface i_comdef_has_parent
{
	/// Set the parent object of this instance.
	function SetParentObj(
						$in_parent_obj	///< A reference to the parent object.
						);
	/// Return a reference to the parent object of this instance.
	function GetParentObj();
}

/*******************************************************************/
/**	\interface i_comdef_db_stored
	\brief Interface for entities that store themselves in the database.
*/
interface i_comdef_db_stored
{
	/// This causes the object to update the database to its current state.
	function UpdateToDB();
	/// Deletes the instance from the database.
	function DeleteFromDB();
	/// This overwrites the current state of the object from state stored in the database.
	function RestoreFromDB();
	/// Returns a storable serialization of the object, as a string.
};

/*******************************************************************/
/**	\interface i_comdef_serialized
	\brief Interface for entities that can be rendered into serialized form.
*/
interface i_comdef_serialized
{
	/// This returns a string, containing the serialized object state.
	function SerializeObject();
	/// This takes the serialized data, and instantiates a new object from it.
	static function UnserializeObject( $in_parent,		///< The parent object.
										$serialized		///< A string containing the serialized data.
										);
};

/*******************************************************************/
/**	\interface i_comdef_auth
	\brief Interface for entities that authenticate users.
*/
interface i_comdef_auth
{
	/// \brief Test to see if a user is allowed to edit an instance (change the data).
	/// \returns true, if the user is allowed to edit, false, otherwise.
	function UserCanEdit ( $in_user_object = null	///< A reference to a c_comdef_user object, for the user to be validated. If null, or not supplied, the server current user is tested.
							);
};

/*******************************************************************/
/**	\brief This class allows us to assign a unique ID to each of its derived instances.

	We keep a static array of instantiated subclasses. When a new instance
	is created, the array is consulted to assign a new ID. When 
*/
abstract class t_local_id_class implements i_comdef_has_parent
{
	/// This is the parent (container) object that holds this instance.
	private	$_local_id_parent_obj = null;
	
	/*******************************************************************/
	/**	\brief Sets the object's "parent" (Container) object, as a reference.
	*/
	function SetParentObj(
						$in_parent_obj	///< A reference to the parent object.
						)
	{
		global	$_COMDEF_DEBUG;
		
		/// We check for recursion here. We go up to a hundred levels deep (should never get close to that).
		$next_obj = $in_parent_obj;
		if ( true == $_COMDEF_DEBUG )
			{
			echo "[t_local_id_class::SetParentObj] Starting Parent Object:<pre>";
			var_dump ( $next_obj );
			echo "</pre><br/>";
			}
		$count = 100;	/// Prevent looping forever, in case a recursed object is presented.
		
		while ( $count-- && is_object ( $next_obj ) )
			{
			if ( $next_obj === $this )
				{
				throw ( new Exception ( "(t_local_id_class::SetParentObj) Recursion!" ) );
				}
			
			if ( is_object ( $next_obj ) && method_exists ( $next_obj, 'GetParentObj' ) )
				{
				$next_obj = $next_obj->GetParentObj();
				if ( true == $_COMDEF_DEBUG )
					{
					echo "[t_local_id_class::SetParentObj] Next Parent Object:<pre>";
					var_dump ( $next_obj );
					echo "</pre><br/>";
					}
				}
			else
				{
				$next_obj = $in_parent_obj;
				break;
				}
			}
		
		if ( !$count )
			{
			throw ( new Exception ( "(t_local_id_class::SetParentObj) Recursion Count Exceeded!" ) );
			}
		
		if ( true == $_COMDEF_DEBUG )
			{
			echo "[t_local_id_class::SetParentObj] Ending Parent Object:<pre>";
			var_dump ( $next_obj );
			echo "</pre><br/>";
			}

		if ( is_object ( $in_parent_obj ) )
			{
			$this->_local_id_parent_obj = null;
			$this->_local_id_parent_obj = $in_parent_obj;
			}
		elseif ( null == $in_parent_obj )
			{
			$this->_local_id_parent_obj = null;
			}
	}
	
	/*******************************************************************/
	/**	\brief Returns a reference to the object's container.
	
		\returns A reference to the object's parent object.
	*/
	function GetParentObj()
	{
		return $this->_local_id_parent_obj;
	}
};

/*******************************************************************/
/**	\brief Allows us to specify a language to the instance (example: English or French)
	
	This class also allows us to specify a name for the instance, as a string, as
	well as a description.
*/
abstract class t_comdef_local_type extends t_local_id_class
{
	private	$_local_type_lang_enum = null;
	private	$_local_type_name_string = null;
	private	$_local_type_desc_string = null;
	
	/*******************************************************************/
	/**	\brief Sets the language.
	*/
	function SetLocalLang(
						$in_lang_enum=null	/**< An enum, indicating the language
												- Defaults to Global Language
												- as a last resort, English ("en").
											*/
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

		$this->_local_type_lang_enum = null;
		$this->_local_type_lang_enum = $in_lang_enum;
	}
	
	/*******************************************************************/
	/**	\brief Returns a reference to the local language setting.
	
		\returns A reference to the _local_type_lang_enum data member
	*/
	function GetLocalLang()
	{
		return $this->_local_type_lang_enum;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Sets the _local_type_name_string data member.
	*/
	function SetLocalName(
						$in_name_string	///< The name of this object, as a string.
						)
	{
		$this->_local_type_name_string = null;
		$this->_local_type_name_string = $in_name_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Returns a reference to the _local_type_name_string data member.
	
		\returns A reference to the _local_type_name_string data member.
	*/
	function GetLocalName()
	{
		return $this->_local_type_name_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Sets the _local_type_desc_string data member.
	*/
	function SetLocalDescription(
								$in_description_string	///< The description as a string up to 4000 characters long.
								)
	{
		$this->_local_type_desc_string = null;
		$this->_local_type_desc_string = $in_description_string;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Returns a reference to the _local_type_desc_string data member.
	
		\returns A reference to the _local_type_desc_string data member.
	*/
	function GetLocalDescription()
	{
		return $this->_local_type_desc_string;
	}
};

/*******************************************************************/
/**	\brief A very simple class that allows whatever format NAWS will use as an ID to be assigned to the object.
*/
abstract class t_comdef_world_type extends t_comdef_local_type
{
	/// This is where the ID is stored.
	private	$_world_type_worldid_mixed = null;
	
	/*******************************************************************/
	/**	\brief	Accessor -Sets the _world_type_worldid_mixed data member.
	*/
	function SetWorldID(
						$in_worldid_mixed	///< The World (NAWS) ID, as a reference.
						)
	{
		$this->_world_type_worldid_mixed = null;
		$this->_world_type_worldid_mixed = $in_worldid_mixed;
	}
	
	/*******************************************************************/
	/**	\brief Accessor -Returns a reference to the _world_type_worldid_mixed data member.
	
		\returns A reference to the _world_type_worldid_mixed data member.
	*/
	function GetWorldID()
	{
		return $this->_world_type_worldid_mixed;
	}
};

?>