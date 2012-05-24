<?php
/***********************************************************************/
/**	\file	c_comdef_meeting_search_manager.class.php

	\brief	The class file for the c_comdef_meeting_search_manager class.
	
	This class is designed to manage the specification and result delivery
	of searches.

	This is a generic meeting search manager that is designed to be subclassed for
	specific implementations (such as for HTTP user agents or RSS feeds).
	
	Some search criteria are fed directly to the SQL, while others are run on the
	results of the SQL search. It is transparent to the caller.
	
	This class is meant to be a complete interface to the BMLT server subsystem.
	It's a Controller class.
	
	As a user (searcher, not administrator) of the system, this is the only class
	you'll need to really know about. You can use it to access other, more specific
	classes, but all you need to do is instantiate one of these, and it will take care
	of setting up the server and all the database interactions.
	
	The way this class works is that a set of data members are in the object that are
	set to specify search criteria. A number of functions are provided to help the
	user to specify criteria.
	
	Once the criteria are set, a search is triggered, and this class will present the
	results as references to objects.
	
	The default is a "wide open" search for all meetings.
	
	\version 0.93

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

require_once ( dirname ( __FILE__ )."/../server/c_comdef_server.class.php" );

/// A class to control the basic common functionality of all meeting searches.
class c_comdef_meeting_search_manager
{
	/// These fields are used to specify the search criteria.
	protected	$_formats = null;				/**< An array of integers. These are formats. The filtering will be an "AND" filtering, so qualified meetings
													must have all of the given formats. The key is the format's shared ID, and the value will be one of these:
														- -1	NOT (exclude all meetings that contain this format from the search).
														-  0	No preference (This format will not be a consideration in the search).
														-  1	Return meetings that contain this format.
														
													Default is 0 for all. If no formats are specified (either 1 or -1), then formats will not be a consideration
													in the meeting search. If any format is specified as 1, then ONLY meetings with the given format will be
													considered in the search, and you must explicitly set any other formats you wish found.
												*/
	protected	$_service_bodies = null;		/**< An array of integers. The key is the ID for a Service Body, and the value is one of these:
														- -1	NOT (exclude all meetings that contain this Service Body from the search).
														-  0	No preference (This Service Body will not be a consideration in the search).
														-  1	Return meetings that contain this Service Body.
														
													Default is 0 for all. If no Service Bodies are specified (either 1 or -1), then Service Bodies will not be a
													consideration in the meeting search. If any Service Body is specified as 1, then ONLY meetings with the given
													Service Body will be considered in the search, and you must explicitly set any other Service Bodies you wish found.
												*/
	protected	$_languages = null;				/**< An array of integers. The key is the language enum, and the value is as follows:
														- -1	NOT (exclude all meetings that have this language from the search).
														-  0	No preference (This language will not be a consideration in the search).
														-  1	Return meetings that have this language.
														
													Default is 0 for all. If no languages are specified (either 1 or -1), then language will not be a
													consideration in the meeting search. If any language is specified as 1, then ONLY meetings with the given
													language will be considered in the search, and you must explicitly set any other language you wish found.
												*/
	protected	$_weekdays = null;				/**< An array of integers. The key is the weekday (1 = Sunday, 7 = Saturday), and the value is as follows:
														- -1	NOT (exclude all meetings that occur on this weekday from the search).
														-  0	No preference (This weekday will not be a consideration in the search).
														-  1	Return meetings that occur on this weekday.
														
													Default is 0 for all. If no weekdays are specified (either 1 or -1), then the weekday will not be a
													consideration in the meeting search. If any weekday is specified as 1, then ONLY meetings that occur on
													the given weekday will be considered in the search, and you must explicitly set any other weekday you wish found.
												*/
	
	/// These specify the start time and duration of the meeting. The start time can be specified as a "window."
	protected	$_start_after = null;			///< An epoch time (seconds, as returned by time()), that denotes the earliest starting time allowed.
	protected	$_start_before = null;			///< An epoch time (seconds, as returned by time()), that denotes the latest starting time allowed.
	protected	$_min_duration = null;			///< The number of seconds a meeting should last as a minimum.
	protected	$_max_duration = null;			///< The number of seconds a meeting can last, at most.
	
	/// These specify the search radius (We store it in kilometers).
	protected	$_search_radius = null;			///< If this is not null, it needs to be a positive, floating-point number, indicating the radius, in Kilometers.
	protected	$_search_radius_count = null;	///< If this is not null, it needs to be a positive integer, indicating the number of meetings to find automatically.
	protected	$_search_center_long = null;	///< If $_search_radius is not null, this needs to be a floating-point number that indicates the longitude, in degrees, of the search center.
	protected	$_search_center_lat = null;		///< If $_search_radius is not null, this needs to be a floating-point number that indicates the latitude, in degrees, of the search center.

	/// We allow a broad string search that goes through all the text items. In English and Spanish, it uses metaphone, which is a "sounds like" phonetic search.
	protected	$_search_string = null;			///< A string to be located from within the results. This search is done after all of the previous ones. This is applied at the end.
	protected	$_search_string_all_words = false;	///< A Boolean. If this is true, then all of the words in a phrase must be present.
	protected	$_search_string_literal = false;	///< A Boolean. If this is true, then the spelling must be literal.

	/// This allows you to filter out a particular value for a key
	protected	$_meeting_key = null;			/**< A string. This is the xact name of the key to match.
													NOTE: As of Version 1.5, this can be an array of strings (it can still be a single string). The array should contain the names of string fields.
												*/
	protected	$_meeting_key_value = null;		/**< A string. The value to match.
													NOTE: As of Version 1.5, this is matched with a metaphone match, as well as the RegEx match.
												*/
	protected	$_meeting_key_match_case = false;	/**< If true, the case must match. Default is false.
														NOTE: As of Version 1.5, setting this to TRUE also stops the metaphone search.
													*/
	protected	$_meeting_key_contains = true;	///< If this is false, then the string must be complete. Default is true (contains).
	
	/// This contains a list of IDs of individual meetings. If it is set, then all other search parameters are ignored.
	protected	$_meeting_id_array = null;		///< An array of positive integers. The Ids of meetings to find. If this is set, all other search criteria are ignored.
	
	/// This indicates whether the search should include, exclude, or focus on "published" meetings.
	protected	$_published_search = 0;			/**< This only counts if the searcher is a logged-in admin. The value can be:
														- -1	Search for ONLY unpublished meetings
														-  0	Search for published and unpublished meetings.
														-  1	Search for ONLY published meetings.
												*/
	protected	$_sort_search_by_distance = false;	///< If this is true, then the search results may be sorted by distance from the geo center.
	
	/// This contains the search results.
	protected	$_search_results = null;		///< A c_comdef_meetings object. If this is null, a new search is performed. This contains the entire search results.
	
	private		$sort_array = null;				///< This contains an array of strings that represent the sort keys.
	private 	$sort_desc = false;				///< This is set to true if the sort is a reverse sort.
	private		$sort_depth = 3;				///< An integer. This is how far back a staged sort goes. Default is 3. 0 is forever.
	
	/// This refers to portions of a larger search (pages).
	private 	$_results_per_page = 0;			///< The number of meetings to list per page.
	private		$_pageno = 0;					///< An integer. The page number represented by this object. If $_results_per_page is 0, this is ignored.
	
	/// If this isn't the root, then this will be a reference to the root.
	private		$_my_root = null;				///< A reference to an instance of c_comdef_meeting_search_manager -The root object.
	
	private		$_my_server = null;				///< A reference to a c_comdef_server object. This is the server to be used for the search.
	
	/*******************************************************************/
	/** \brief Constructor.
	*/
	function __construct( $in_results_per_page = 0,								///< An integer that defines how many results per page you want to see. 0 (default) is all in one page.
						  c_comdef_meeting_search_manager &$in_parent = null,	///< A reference to an existing c_comdef_meeting_search_manager object.
						  c_comdef_meetings &$in_search_results = null,			///< A reference to some pre-parsed search results.
						  $in_pageno = 0										///< An integer. The page of the main search this is from.
						)
	{
		// If this is a page of results, we set up the object to reference the root.
		if ( $in_parent instanceof c_comdef_meeting_search_manager )
			{
			$this->_my_root =& $in_parent;
			// These all reference the root object's values.
			$this->_formats =& $in_parent->_formats;
			$this->_service_bodies =& $in_parent->_service_bodies;
			$this->_languages =& $in_parent->_languages;
			$this->_weekdays =& $in_parent->_weekdays;
			$this->_start_after =& $in_parent->_start_after;
			$this->_start_before =& $in_parent->_start_before;
			$this->_min_duration =& $in_parent->_min_duration;
			$this->_max_duration =& $in_parent->_max_duration;
			$this->_search_radius =& $in_parent->_search_radius;
			$this->_search_center_long =& $in_parent->_search_center_long;
			$this->_search_center_lat =& $in_parent->_search_center_lat;
			$this->_search_string =& $in_parent->_search_string;
			$this->_meeting_id_array =& $in_parent->_meeting_id_array;
			$this->_published_search =& $in_parent->_published_search;
			
			$this->_my_server =& $in_parent->_my_server;
			
			// These may get changed by this instance.
			$this->sort_array = $in_parent->sort_array;	
			$this->sort_desc = $in_parent->sort_desc;
			
			// These are passed in and set at construction
			$this->_pageno = $in_pageno;
			$this->_search_results = $in_search_results;
			}
		else	// If we are the root object, we start clean.
			{
			// See if the caller has requested a number of results per page.
			if ( $in_results_per_page )
				{
				$this->_results_per_page = $in_results_per_page;
				}
			
			$this->_my_server = c_comdef_server::MakeServer();	// We initialize the server.
			
			$this->SetFormats();						// We set the formats array.
			$this->SetServiceBodies();					// We set the Service Bodies array.
			$this->SetLanguages();						// We set the Languages array.
			
			// Set up the weekday array (1 = Sunday, 7 = Saturday ).
			$this->_weekdays = null;
			for ( $wd = 1; $wd < 8; $wd++ )
				{
				$this->_weekdays[$wd] = 0;
				}
				
			// This is the default sort.
			$this->sort_array = array ( "lang_enum", "weekday_tinyint", "start_time", "id_bigint" );
			
			// We have no search parameters or results at this point.
			}
	}
	
	/*******************************************************************/
	/** \brief Sets an internal array of integers, containing the Shared IDs for
		all available formats on the server. They are initialized to 0 (neutral).
	*/
	function SetFormats()
	{
		$this->_formats = null;
		
		$formats = $this->GetAvailableFormats();
		
		// Basic error checking.
		if ( is_array ( $formats ) && count ( $formats ) )
			{
			// We look in the server's local language (in case there are no other translations).
			$my_lang = $this->_my_server->GetLocalLang();
			
			$er = $formats[$my_lang];
			
			if ( is_array ( $er ) && count ( $er ) )
				{
				foreach ( $er as $key => $value )	// We ignore the value.
					{
					$this->_formats[$key] = 0;
					}
				}
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get a reference to the $_formats field.
		
		\returns a reference to the $_formats field, which is an array of integers.
		The key is the format's shared ID, and the value will be one of these:
			- -1	NOT (exclude all meetings that contain this format from the search).
			-  0	No preference (This format will not be a consideration in the search).
			-  1	Return meetings that contain this format.
			
		Default is 0 for all. If no formats are specified (either 1 or -1), then formats will not be a consideration
		in the meeting search. If any format is specified as 1, then ONLY meetings with the given format will be
		considered in the search, and you must explicitly set any other formats you wish found.
	*/
	function &GetFormats()
	{
		return $this->_formats;
	}
	
	/*******************************************************************/
	/** \brief Sets a key/value search.
	*/
	function SetKeyValueSearch(
								$in_meeting_key,					///< A string. This is the xact name of the key to match.
								$in_meeting_key_value,				///< A string. The value to match.
								$in_meeting_key_match_case = false,	///< If true, the case must match. Default is false.
								$in_meeting_key_contains = true		///< If this is false, then the string must be complete. Default is true (contains).
								)
	{
		$this->_meeting_key = $in_meeting_key;
		$this->_meeting_key_value = $in_meeting_key_value;
		$this->_meeting_key_match_case = $in_meeting_key_match_case;
		$this->_meeting_key_contains = $in_meeting_key_contains;
	}
	
	/*******************************************************************/
	/** \brief Sets the sort by distance flag.
	*/
	function SetSortByDistance ( $in_sort_search_by_distance = false	///< A Boolean. False is default.
								)
	{
		$this->_sort_search_by_distance = ($in_sort_search_by_distance != false);
	}
	
	/*******************************************************************/
	/** \brief Sets an internal array of integers, containing the IDs for
		all available service bodies on the server. They are initialized
		to 0 (neutral).
	*/
	function SetServiceBodies()
	{
		$this->_service_bodies = null;
		
		// Basic error checking.
		if ( $this->_my_server instanceof c_comdef_server )
			{
			// Start by getting the service bodies aggregator object.
			$bodies =& $this->_my_server->GetServiceBodyArray();
			if ( is_array ( $bodies ) && count ( $bodies ) )
				{
				foreach ( $bodies as $key => &$value )
					{
					$this->_service_bodies[$key] = 0;
					}
				}
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the number of results per page.
	
		\returns an integer.
	*/
	function GetResultsPerPage()
	{
		$ret = 0;
		
		// This is kept in the root object.
		if ( !$this->_my_root )
			{
			$ret = $this->_results_per_page;
			}
		elseif ( $this->GetRootObject() instanceof c_comdef_meeting_search_manager )
			{
			$ret = $this->GetRootObject()->GetResultsPerPage();
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Set the number of results per page.
		This will only work on the root object.
		
		\returns a boolean. If the operation was successful, it is true. False otherwise.
	*/
	function SetResultsPerPage( $in_results_per_page	///< A positive integer. If it is 0, then all results will be returned in one page.
								)
	{
		$ret = false;
		
		if ( !$this->_my_root )
			{
			$this->_results_per_page = $in_results_per_page;
			$ret = true;
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the page number of this set.
	
		\returns an integer.
	*/
	function GetPageNo()
	{
		return $this->_pageno;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the index (1 - based) of the first meeting in this page.
	
		\returns an integer.
	*/
	function GetFirstIndexInPage()
	{
		return ($this->GetResultsPerPage() * ($this->GetPageNo() - 1)) + 1;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the index (1 - based) of the last meeting in this page.
	
		\returns an integer.
	*/
	function GetLastIndexInPage()
	{
		return ($this->GetFirstIndexInPage() + $this->GetNumberOfResultsInThisPage()) - 1;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the page number of this set.
	
		\returns an integer.
	*/
	function GetNumberOfPages()
	{
		$ret = 0;
		
		$respp = $this->GetResultsPerPage();
		$resnum = $this->GetNumberOfResults();
		
		if ( $respp > 0 )
			{
			$ret = intval ( ($resnum + ($respp - 1)) / $respp );
			}
		elseif ( $resnum > 0 )
			{
			$ret = 1;
			}
			
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the total number of meetings found.
		This gives the TOTAL number found, not just the subset in this page.
	
		\returns an integer.
	*/
	function GetNumberOfResults()
	{
		$ret = 0;
		
		if ( !$this->_my_root && $this->_search_results instanceof c_comdef_meetings )
			{
			$ret = $this->_search_results->GetNumMeetings();
			}
		elseif ( $this->_my_root instanceof c_comdef_meeting_search_manager )
			{
			$ret = $this->_my_root->GetNumberOfResults();
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the number of meetings in this page.
	
		\returns an integer.
	*/
	function GetNumberOfResultsInThisPage()
	{
		$ret = 0;
		
		if ( $this->_search_results instanceof c_comdef_meetings )
			{
			$ret = $this->_search_results->GetNumMeetings();
			}

		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get the number of meetings in this page.
	
		This system allows each page to be treated independently, like allowing
		different sorts without re-sorting the entire search.
		
		This object needs to be the "root" object to return a page.
	
		\returns a new instance (not a reference) to a c_comdef_meeting_search_manager
		object, containing a subset of the meetings to fill this one page.
	*/
	function &GetPageOfResults( $in_page_no = 1	///< A positive integer. This should be 1 to $this->GetNumberOfPages() (1-based)
							)
	{
		if ( $in_page_no < 1 )	// Can't be less than 1.
			{
			$in_page_no = 1;
			}
		
		if ( $in_page_no > $this->GetNumberOfPages() )	// Can't be greater than the last page.
			{
			$in_page_no = $this->GetNumberOfPages();
			}
		
		$ret = null;
		
		// We get pages from the root at all times.
		if ( !$this->_my_root && $this->_search_results instanceof c_comdef_meetings )
			{
			// This is the starting index for our page of results.
			$main_array = $this->_search_results->GetMeetingObjects();
			$ret_array = array();
			$min = $this->GetResultsPerPage() * ($in_page_no - 1);
			if ( intval ( $this->GetResultsPerPage() ) > 0 )
				{
				$max = min ( count ( $main_array ), $min + $this->GetResultsPerPage() );
				}
			else
				{
				$max = $this->GetNumberOfResults ( );
				}
			
			for ( $index = $min; $index < $max; $index++ )
				{
				if ( $main_array[$index] instanceof c_comdef_meeting )
					{
					$data =& $main_array[$index]->GetMeetingData();
					if ( is_array ( $data ) && count ( $data ) )
						{
						$ret_array[$data['id_bigint']] =& $main_array[$index];
						}
					}
				}
			
			if ( count ( $ret_array ) )
				{
				$resultsObj = new c_comdef_meetings ( $this->_search_results->GetParentObj(), $ret_array );
			
				if ( $resultsObj instanceof c_comdef_meetings )
					{
					$ret = new c_comdef_meeting_search_manager ( 0, $this, $resultsObj, $in_page_no );
					}
				}
			}
		elseif ( $this->_my_root instanceof c_comdef_meeting_search_manager )
			{
			$ret =& $this->_my_root->GetPageOfResults($in_page_no);
			}

		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get a reference to the $_service_bodies field.
	
		\returns a reference to the $_service_bodies field.
		The key is the Service Body ID. The value is as follows:
			- -1	NOT (exclude all meetings that contain this Service Body from the search).
			-  0	No preference (This Service Body will not be a consideration in the search).
			-  1	Return meetings that contain this Service Body.
			
		Default is 0 for all. If no Service Bodies are specified (either 1 or -1), then Service Bodies will not be a
		consideration in the meeting search. If any Service Body is specified as 1, then ONLY meetings with the given
		Service Body will be considered in the search, and you must explicitly set any other Service Bodies you wish found.
	*/
	function &GetServiceBodies()
	{
		return $this->_service_bodies;
	}
	
	/*******************************************************************/
	/** \brief Sets up an internal array of languages.
		The array uses the language enum as the key, and the -1->0->1
		form as the selector.
	*/
	function SetLanguages()
	{
		$this->_languages = null;
		
		// Basic error checking.
		if ( $this->_my_server instanceof c_comdef_server )
			{
			$langs = $this->_my_server->GetServerLangs();
			
			if ( is_array ( $langs ) && count ( $langs ) )
				{
				foreach ( $langs as $key => $value )
					{
					$this->_languages[$key] = 0;
					}
				}
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get a reference to the $_languages field.
	
		\returns a reference to the $_languages field.
		The key is the language enum, and the value is as follows:
			- -1	NOT (exclude all meetings that have this language from the search).
			-  0	No preference (This language will not be a consideration in the search).
			-  1	Return meetings that have this language.
			
		Default is 0 for all. If no languages are specified (either 1 or -1), then language will not be a
		consideration in the meeting search. If any language is specified as 1, then ONLY meetings with the given
		language will be considered in the search, and you must explicitly set any other language you wish found.
	*/
	function &GetLanguages()
	{
		return $this->_languages;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get a reference to the $_weekdays field.

		\returns a reference to the $_weekdays field.
		The key is the weekday (1 = Sunday, 7 = Saturday), and the value is as follows:
			- -1	NOT (exclude all meetings that occur on this weekday from the search).
			-  0	No preference (This weekday will not be a consideration in the search).
			-  1	Return meetings that occur on this weekday.
			
		Default is 0 for all. If no weekdays are specified (either 1 or -1), then the weekday will not be a
		consideration in the meeting search. If any weekday is specified as 1, then ONLY meetings that occur on
		the given weekday will be considered in the search, and you must explicitly set any other weekday you wish found.
	*/
	function &GetWeekdays()
	{
		return $this->_weekdays;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get a reference to the c_comdef_server object.
	
		\returns a reference to the c_comdef_server object instantiated by
		this object.
	*/
	function &GetServer()
	{
		return $this->_my_server;
	}
	
	/*******************************************************************/
	/** \brief Accessor -Get a reference to the root object.
	
		\returns a reference to the c_comdef_meeting_search_manager object.
		If this is the root object, it will return a reference to itself.
	*/
	function &GetRootObject()
	{
		$ret = null;
		
		if ( $this->_my_root instanceof c_comdef_meeting_search_manager )
			{
			$ret =& $this->_my_root;
			}
		else
			{
			$ret =& $this;
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Set a search radius and center, with the Radius in miles.
	*/
	function SetSearchRadiusAndCenterInMiles(	$in_search_radius_in_miles,	///< The radius as specified in miles, not Km.
												$in_long_in_degrees,		///< The longitude needs to be specified in degrees.
												$in_lat_in_degrees			///< The latitude needs to be specified in degrees.
												)
	{
		$this->SetSearchRadiusAndCenterInKm ( 1.609344 * $in_search_radius_in_miles, $in_long_in_degrees, $in_lat_in_degrees );
	}
	
	/*******************************************************************/
	/** \brief Set a search radius and center, with the radius in Km.
	*/
	function SetSearchRadiusAndCenterInKm(	$in_search_radius_in_km,	///< The radius needs to be specified in kilometers, not miles.
											$in_long_in_degrees,		///< The longitude needs to be specified in degrees.
											$in_lat_in_degrees			///< The latitude needs to be specified in degrees.
											)
	{
		$this->_search_radius = $in_search_radius_in_km;
		$this->_search_center_long = $in_long_in_degrees;
		$this->_search_center_lat = $in_lat_in_degrees;
	}
	
	/*******************************************************************/
	/** \brief Set a search center, and a count for an auto radius hunt.
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
	*/
	function SetSearchRadiusAndCenterAuto (	$in_search_result_count,	///< A positive integer. It specifies the number of meetings to find.
											$in_long_in_degrees,		///< The longitude needs to be specified in degrees.
											$in_lat_in_degrees			///< The latitude needs to be specified in degrees.
											)
	{
		$this->_search_radius_count = $in_search_result_count;
		$this->_search_radius = 0;
		$this->_search_center_long = $in_long_in_degrees;
		$this->_search_center_lat = $in_lat_in_degrees;
	}

	/*******************************************************************/
	/** \brief Get the search radius.
	
		\returns a floating-point number. If the $in_miles parameter is false,
		the returned value is kilometers. If it is true, the returned value
		is in miles.
	*/
	function GetRadius ( $in_miles = false	///< A boolean. If true, the returned value will be in miles. Otherwise, it is returned in kilometers.
						)
	{
		return  $in_miles ? ($this->_search_radius / 1.609344) : $this->_search_radius;
	}

	/*******************************************************************/
	/** \brief Get the longitude.
	
		\returns a floating-point number. The return is in degrees.
	*/
	function GetLongitude()
	{
		return  $this->_search_center_long;
	}

	/*******************************************************************/
	/** \brief Get the latitude.
	
		\returns a floating-point number. The return is in degrees.
	*/
	function GetLatitude()
	{
		return  $this->_search_center_lat;
	}

	/*******************************************************************/
	/** \brief Get the search for published value
	
		\returns an integer.
				- -1	Search for ONLY unpublished meetings
				-  0	Search for published and unpublished meetings.
				-  1	Search for ONLY published meetings.
	*/
	function GetPublished ()
	{
		return  $this->_published_search;
	}

	/*******************************************************************/
	/** \brief Sets the search for published value
	*/
	function SetPublished ( $in_published_search	/**< The value to set. It can be:
														- -1	Search for ONLY unpublished meetings
														-  0	Search for published and unpublished meetings.
														-  1	Search for ONLY published meetings.
													*/
							)
	{
		$this->_published_search = $in_published_search;
	}

	/*******************************************************************/
	/** \brief Set a start time window.
	*/
	function SetStartTime ( $in_starts_after,			///< An epoch time, defining when the meeting should start (or after)
							$in_starts_before = null	///< If defined, the meeting must start no later than this.
							)
	{
		$this->_start_after = $in_starts_after;
		
		// We don't let this be less than, or equal to, the start time.
		if ( (null != $in_starts_after) && (null != $in_starts_before)
			&& (intval ( $in_starts_after ) >= intval ( $in_starts_before )) )
			{
			$in_starts_before = null;
			}
		
		$this->_start_before = $in_starts_before;
	}
	
	/*******************************************************************/
	/** \brief Get the "starts after" value
		
		\returns an integer, containing the epoch time for the value.
	*/
	function GetStartTime_Min ()
	{
		return $this->_start_after;
	}
	
	/*******************************************************************/
	/** \brief Get the "starts before" value
		
		\returns an integer, containing the epoch time for the value.
	*/
	function GetStartTime_Max ()
	{
		return $this->_start_before;
	}
	
	/*******************************************************************/
	/** \brief Set a duration time window.
	*/
	function SetDuration ( $in_max_duration,			///< An epoch time, defining the maximum duration of a meeting.
							$in_min_duration = null		///< If defined, the minimum duration of the meeting.
							)
	{
		$this->_max_duration = $in_max_duration;
		
		// We don't let this be less than, or equal to, the start time.
		if ( ((null != $in_max_duration) && (null != $in_min_duration)) && intval ( $in_min_duration ) >= intval ( $in_max_duration ) )
			{
			$in_min_duration = null;
			}

		$this->_min_duration = $in_min_duration;
	}
	
	/*******************************************************************/
	/** \brief Get the Maximum Duration value
		
		\returns an integer, containing the epoch time for the value.
	*/
	function GetDuration_Max ()
	{
		return $this->_max_duration;
	}
	
	/*******************************************************************/
	/** \brief Get the Minimum Duration value
		
		\returns an integer, containing the epoch time for the value.
	*/
	function GetDuration_Min ()
	{
		return $this->_min_duration;
	}
	
	/*******************************************************************/
	/** \brief Set a search string.
	*/
	function SetSearchString ( $in_search_string,		///< A string. This is a string for which to search.
								$in_all_words = false,	///< A Boolean. If this is true, then all of the words in a phrase must be present.
								$in_literal = false		///< A Boolean. If this is true, then the spelling must be literal.
							)
	{
		$this->_search_string = $in_search_string;
		$this->_search_string_all_words = $in_all_words;
		$this->_search_string_literal = $in_literal;
	}

	/*******************************************************************/
	/** \brief Get the current search string.
		
		\returns a reference to the search string.
	*/
	function &GetSearchString ()
	{
		return $this->_search_string;
	}
	
	/*******************************************************************/
	/** \brief Set the current search string.
	*/
	function SetMeetingIDArray ( $in_meeting_id_array	///< An array of positive integers. These are the IDs of individual meetings to find.
								)
	{
		$this->_meeting_id_array = $in_meeting_id_array;
	}
	
	/*******************************************************************/
	/** \brief Get the current search string.
		
		\returns a reference to the meeting ID array.
	*/
	function &GetMeetingIDArray ()
	{
		return $this->_meeting_id_array;
	}
	
	/*******************************************************************/
	/** \brief Get the current search string "all words" flag.
	*/
	function StringSearchForAllWords ()
	{
		return $this->_search_string_all_words;
	}
	
	/*******************************************************************/
	/** \brief Get the current search string literal flag.
	*/
	function StringSearchIsLiteral ()
	{
		return $this->_search_string_literal;
	}
	
	/// These functions will help the caller to get information from the server. You can use them to build a search form.
	
	/*******************************************************************/
	/** \brief Returns an array of language enums and names.
		
		\returns a reference to an array of strings, containing the server
		languages in human-readable, local form. The key will be the enum,
		and the value will be the name of the language.
	*/
	function &GetAvailableLanguages()
	{
		$ret = null;
		
		// Basic error checking.
		if ( $this->_my_server instanceof c_comdef_server )
			{
			$ret =& $this->_my_server->GetServerLangs();
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Returns an array of references to c_comdef_service_body objects.
	
		This returns ALL Service bodies; whether or not they contain meetings.
	
		\returns a reference to an array of c_comdef_service_body objects.
		The key will be the ID of the Service body, and the value will be a reference
		to the object. Null, if the function fails for any reason.
		
		This will reference the actual objects controlled by the server.
	*/
	function &GetAvailableServiceBodies()
	{
		$ret = null;
		
		// Basic error checking.
		if ( $this->_my_server instanceof c_comdef_server )
			{
			$ret =& $this->_my_server->GetServiceBodyArray();
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Returns a multi-dimensional array of references to c_comdef_format objects.
	
		This returns ALL formats; whether or not they are used in meetings.
	
		\returns a reference to a multi-dimensional array of c_comdef_format objects.
		The key will be the language enum, and the value will be another array,
		which will have its key as the Shared ID of the format, and the value will be
		a reference to the object. Null, if the function fails for any reason.
		
		This will reference the actual objects controlled by the server.
	*/
	function &GetAvailableFormats()
	{
		$ret = null;
		
		// Basic error checking.
		if ( $this->_my_server instanceof c_comdef_server )
			{
			// Start by getting the format aggregator object.
			$formats_obj =& $this->_my_server->GetFormatsObj();
			
			if ( $formats_obj instanceof c_comdef_formats )
				{
				$ret =& $formats_obj->GetFormatsArray();
				}
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Returns an array of all possible field keys to be used for sorting.
	
		\returns an array of strings, with the key being the same as the value.
		NOTE: This contains ALL possible keys, including ones that may not be
		used in the found set.
	*/
	static function GetAllAvailableSortKeys()
	{
		return c_comdef_meeting::GetAllMeetingKeys();
	}

	/// These functions deal with the search itself.
	
	/*******************************************************************/
	/** \brief Returns an array of field keys to be used for sorting.
		This function is not static, and searches the found set for keys.
		As a result, it takes longer.
	
		\returns an array of strings, with the key being the same as the value.
		NOTE: This contains only the keys used in this found set.
		
		As a result, this will return null until after a search has been performed.
	*/
	function GetSpecificSortKeys()
	{
		$ret = null;
		
		if ( $this->_search_results instanceof c_comdef_meetings )
			{
			$ret = $this->_search_results->GetMeetingKeys();
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/** \brief Executes a new search. It will always force a new search.
		
		\returns an integer that indicates the number of meetings found.
	*/
	function DoSearch()
	{
		$this->GetSearchResults_Obj(true);
		
		return $this->GetNumberOfResults();
	}
	
	/*******************************************************************/
	/** \brief Returns a reference to the c_comdef_meetings object that
		contains the results of the search. If this is null, or if the
		$in_new_search parameter is set to true, the search will be
		executed, otherwise, this just returns a reference to the existing
		search results.
		
		\returns a reference to the internal $_search_results field (an
		instance of c_comdef_search_results).
	*/
	function &GetSearchResults_Obj( $in_new_search = false	///< If this is set to true, the search is done anew.
									)
	{
		// See if we need to make a new search. Only the root can do a new search.
		if ( (null == $this->_my_root) && (true == $in_new_search) || (null == $this->_search_results) )
			{
			$this->_search_results = null;

			// Basic error checking.
			if ( $this->_my_server instanceof c_comdef_server )
				{
				// If we have an ID array, then we skip everything else, and just search for those IDs.
				if ( is_array ( $this->_meeting_id_array ) && count ( $this->_meeting_id_array ) )
					{
					$this->_search_results = c_comdef_server::GetMeetingsByID ( $this->_meeting_id_array );
					
					if ( $this->_search_results instanceof c_comdef_meetings )
						{
						$this->_search_results->RemoveInvalidMeetings();

						// Force a new sort.
						$this->SortMeetingObjects();
						}
					}
				else
					{
					// We start by specifying a search using the main criteria. We need to interpret the current state into search criteria.
					
					// Set up our Service Bodies Array.
					$service_bodies = null;
					
					if ( is_array ( $this->_service_bodies ) && count ( $this->_service_bodies ) )
						{
						$service_bodies = array();
						foreach ( $this->_service_bodies as $key => $value )
							{
							// If the value of the Service Body is 1 or -1, we add it to the list.
							if ( abs ( $value ) == 1 )
								{
								array_push ( $service_bodies, intval ( $key ) * $value );
								}
							}
						}
					
					// Set up our weekday array.
					$weekdays = null;
					
					if ( is_array ( $this->_weekdays ) && count ( $this->_weekdays ) )
						{
						$weekdays = array();
						foreach ( $this->_weekdays as $key => $value )
							{
							// If the value of the Weekday is 1 or -1, we add it to the list.
							if ( abs ( $value ) == 1 )
								{
								array_push ( $weekdays, intval ( $key ) * $value );
								}
							}
						}
					
					// Set up our formats array.
					$formats = null;
					
					if ( is_array ( $this->_formats ) && count ( $this->_formats ) )
						{
						$formats = array();
						foreach ( $this->_formats as $key => $value )
							{
							// If the value of the format is 1 or -1, we add it to the list.
							if ( abs ( $value ) == 1 )
								{
								array_push ( $formats, intval ( $key ) * $value );
								}
							}
						}
					
					// Set up our languages array.
					$languages = null;
					
					if ( is_array ( $this->_languages ) && count ( $this->_languages ) )
						{
						$languages = array();
						foreach ( $this->_languages as $key => $value )
							{
							// If the value of the format is 1 or -1, we add it to the list.
							if ( abs ( $value ) == 1 )
								{
								array_push ( $languages, ($value == -1) ? "-$key" : $key );
								}
							}
						}

					// If we will specify a search radius, we specify a restricted area for the search.
					$search_rect = $this->GetSquareForRadius($weekdays);
					
					// Do the main database search first.
					$null_me = null;

					$this->_search_results = c_comdef_server::GetMeetings (	$service_bodies,
																			$languages,
																			$weekdays,
																			$formats,
																			$this->_start_after,
																			$this->_start_before,
																			$this->_min_duration,
																			$this->_max_duration,
																			$search_rect,
																			null,
																			$null_me,
																			$this->_published_search
																			);
					if ( isset ( $this->_search_results ) && $this->_search_results && ($this->_search_results instanceof c_comdef_meetings) )
						{
						$this->_search_results->RemoveInvalidMeetings();

						// Force a new sort.
						$this->SortMeetingObjects();
	
						// These are two "post-database" searches that we do.
						if ( null != $this->_search_radius )
							{
							$this->_search_results = $this->_search_results->GetMeetingsByDistance ( $this->_search_center_long, $this->_search_center_lat, $this->_search_radius, true, $this->_sort_search_by_distance );
							}
							
						if ( null != $this->_search_string )
							{
							$_search_results = $this->_search_results->GetMeetingsByString ( $this->_search_string, null, $this->_search_string_all_words, $this->_search_string_literal  );
							$this->_search_results = null;
							$this->_search_results = $_search_results;
							}
							
						if ( $this->_meeting_key )
							{
							$key_array = (is_array ( $this->_meeting_key ) && count ( $this->_meeting_key )) ? $this->_meeting_key : array ( $this->_meeting_key );
							$_search_results = $this->_search_results->GetMeetingsByKeyValue ( $key_array, $this->_meeting_key_value, $this->_meeting_key_contains, $this->_meeting_key_match_case );
							$this->_search_results = null;
							$this->_search_results = $_search_results;
							}
						}
					}
				}
			}
		
		return $this->_search_results;
	}
	
	/*******************************************************************/
	/** \brief Clears the search results, so the next access will redo the search.
	*/
	function ClearSearch()
	{
		// Only the root can clear its search results.
		if ( null == $this->_my_root )
			{
			$this->_search_results = null;
			}
	}
	
	/*******************************************************************/
	/** \brief This will return the search results as an array of c_comdef_meeting
		objects.
		
		\returns a reference to an array of references to c_comdef_meeting objects.
	*/
	function &GetSearchResultsAsArray()
	{
		$ret = null;
		
		if ( $this->GetSearchResults_Obj () instanceof c_comdef_meetings )
			{
			$s_array =& $this->GetSearchResults_Obj ()->GetMeetingObjects();
			
			if ( is_array ( $s_array ) && count ( $s_array ) )
				{
				$ret =& $s_array;
				}
			}
		
		return $ret;
	}
	
	/// These are sorting functions.
	
	/*******************************************************************/
	/** \brief Simply clears out the sort array, so the search is unsorted.
	*/
	function ClearSort ()
	{
		// Only the root can do this.
		if ( null == $this->_my_root )
			{
			$this->sort_array = null;
			}
	}
	
	/*******************************************************************/
	/** \brief Accessor. Set the maximum number of sorts to maintain.
		If the current number is more than the new value, then we remove any
		beyond that.
	*/
	function SetSortDepth ( $in_new_depth = 0	///< A positive integer. If nothing is provided, we set to endless (0).
							)
	{
		// Only the root can do this.
		if ( null == $this->_my_root )
			{
			$this->sort_depth = $in_new_depth;
			if ( 0 < $this->sort_depth )	// Only if a depth is defined.
				{
				while ( count ( $this->sort_array ) > $this->sort_depth )
					{
					array_shift ( $this->sort_array );	// Take off the bottommost one.
					}
				}
			
			// Force a new sort, based on the new depth.
			$this->SortMeetingObjects();
			}
	}
	
	/*******************************************************************/
	/** \brief Set a new "top" sort priority.
	
		The prior "top" sort priority will be made secondary. If the key
		was previously "lower" in the sort priority, it is removed from there.
		
		It will trigger a sort after the new key has been established.
		
		This allows a very "natural" type of sorting, where the user goes
		from one field to another.
		
		This will always reset the sort direction to the given one (or will
		reset to ascending). We could save the direction with each column,
		but that is likely to result in a confusing mess. It's a better idea
		to just apply the same direction to every column.
	*/
	function SetTopSortPriority ( $in_new_top_sort_key,	///< A string. This should be the database table column name for the new "top" sort key.
								$in_desc = false			///< If this is set to true, the sort will be highest to lowest. Default is false.
								)
	{
		// Only the root can do this.
		if ( null == $this->_my_root )
			{
			// We first remove any previous mention of this key.
			for ( $i = 0; $i < count ( $this->sort_array ); $i++ )
				{
				if ( $this->sort_array[$i] == $in_new_top_sort_key )
					{
					unset ( $this->sort_array[$i] );
					break;
					}
				}
			
			// The new key goes in as the first item.
			if ( !is_array ( $this->sort_array ) || (1 > count ( $this->sort_array )) )
				{
				$this->sort_array = array( $in_new_top_sort_key );
				}
			else
				{
				// We first see if we are at the limit for sort depth.
				
				if ( 0 < $this->sort_depth )	// Only if a depth is defined.
					{
					while ( count ( $this->sort_array ) >= $this->sort_depth )
						{
						array_shift ( $this->sort_array );	// Take off the bottommost one.
						}
					}
				
				array_unshift ( $this->sort_array, $in_new_top_sort_key );
				}
			
			// Make sure the new array direction is recorded.
			$this->sort_desc = $in_desc;
			
			// Force a new sort.
			$this->SortMeetingObjects();
			}
	}
	
	/*******************************************************************/
	/** \brief Sets up the sort array and direction. This replaces the
		entire array and direction. If the array is longer than the
		maximum number of sort keys, only that number of keys are used.
		
		It will reset the sort direction, so you need to explicitly indicate
		the sort direction if you want it descending, as opposed to ascending.
	*/
	function SetSort (	$in_sort_fields_array = null,	/**< An array of strings. The array will deliniate the sort order, by field name.
															 Array element [0] will be the highest priority, and it will descend from there.
															 If this is not specified, the sort will be cleared.
														*/
						$in_desc = false,				///< If this is set to true, the sort will be highest to lowest. Default is false.
						$in_max_sort_keys = 0			///< A positive integer, specifying a new maximum sort depth. If it is not specified, the max will not be changed.
						)
	{
		// Only the root can do this.
		if ( null == $this->_my_root )
			{
			if ( false != $in_desc )
				{
				$in_desc = true;
				}
			
			$this->sort_desc = $in_desc;

			if ( 0 < $in_max_sort_keys )
				{
				$this->sort_depth = $in_max_sort_keys;
				}
				
			if ( null != $in_sort_fields_array )
				{
				$max = min ( $this->sort_depth, count ( $in_sort_fields_array ) );
				
				if ( $max > 0 )
					{
					$this->sort_array = array_slice ( $in_sort_fields_array, 0, $max );
					}
				else
					{
					$this->sort_array = null;
					}
				}
			}
	}
	
	/*******************************************************************/
	/** \brief Sorts the meetings.
		This will apply a sort, dependent upon the given fields.
		The given array contains the field names (SQL columns and keys)
		for the data to be sorted.
		
		If you don't specify any parameters, the ones from the last sort
		will be used.
	*/
	function SortMeetingObjects ( $in_sort_fields_array = null,	/**< An array of strings. The array will deliniate the sort order, by field name.
																	 Array element [0] will be the highest priority, and it will descend from there.
																*/
								$in_desc = null					///< If this is set to true, the sort will be highest to lowest. Default is false.
								)
	{
		// Only the root can do this.
		if ( null == $this->_my_root )
			{
			if ( null != $this->_search_results )
				{
				if ( null != $in_sort_fields_array )
					{
					$this->sort_array = $in_sort_fields_array;
					}
				else
					{
					$in_sort_fields_array = $this->sort_array;
					}
				
				if ( null != $in_desc )
					{
					$this->sort_desc = $in_desc;
					}
				else
					{
					$in_desc = $this->sort_desc;
					}
	
				if ( is_array ( $in_sort_fields_array ) && count ( $in_sort_fields_array ) )
					{
					// This is simply a "pass-through" to the object we have on hand.
					$this->_search_results->SortMeetingObjects ( $in_sort_fields_array, $in_desc, $this->_sort_search_by_distance );
					}
				}
			}
	}

	/// These are various utility functions.
	
	/*******************************************************************/
	/** \brief This is an internal utility function that takes a specified
		radius and center point and calculates a square, in longitude and
		latitude points, that encompasses that radius. This greatly narrows
		the scope of the search, so the radius calculation will simply eliminate
		any meetings that are "in the corners."
		
		If the setting is for auto-radius, the auto-radius is first resolved, then
		the main radius value is set. Remember that auto-radius is for all meetings,
		all days of the week. It is really a "density test," as opposed to an
		accurate selector.
		
		\returns an array of floating-point values, in the following form:
			 - ['east'] = longitude of the Eastern side of the rectangle
			 - ['west'] = longitude of the Western side of the rectangle
			 - ['north'] = latitude of the Northern side of the rectangle
			 - ['south'] = latitude of the Southern side of the rectangle
	*/
	function GetSquareForRadius ( $in_weekday_tinyint_array	///< An array of weekdays in which to filter for.
								)
	{
		$loc = null;
		
		if ( $this->_search_radius_count )
			{
			$this->_search_radius = c_comdef_server::HuntForRadius ( $this->_search_radius_count, $this->_search_center_long, $this->_search_center_lat, $in_weekday_tinyint_array );
			$this->_search_radius_count = null;
			}

		if ( $this->_search_radius > 0 )
			{
			$loc = c_comdef_server::GetSquareForRadius ( $this->_search_radius, $this->_search_center_long, $this->_search_center_lat );
			}
		
		return $loc;
	}
	
	/// These are static functions for directly accessing meetings via ID.
	
	/*******************************************************************/
	/** \brief This is a static utility function that will return one single
		instance of c_comdef_meeting, based upon the ID of that meeting.
		
		\returns a new (not reference) c_comdef_meeting instance. Null if it fails.
	*/
	static function GetSingleMeetingByID ( $in_id	///< An integer. The ID of the meeting.
										)
	{
		return c_comdef_server::GetOneMeeting ( $in_id );
	}
	
	/*******************************************************************/
	/** \brief This is a static utility function that will return multiple
		instances of c_comdef_meeting, based upon the IDs of the meetings.
		It will create an array to hold these instances
		
		NOTE: This is NOT the same as the c_comdef_server::GetMeetingsByID()
		function, as that function returns an instance of c_comdef_meetings.
		
		\returns an array of new (not reference) c_comdef_meeting instances.
		Null if it fails.
	*/
	static function GetMultipleMeetingsByID ( $in_id_array	///< An array of integers. The IDs of the meetings.
											)
	{
		$ret = null;
		
		if ( is_array ( $in_id_array ) && count ( $in_id_array ) )
			{
			foreach ( $in_id_array as $id )
				{
				$ret[$id] = c_comdef_search_manager::GetSingleMeetingByID ( $id );
				}
			}
		
		return $ret;
	}
};

?>