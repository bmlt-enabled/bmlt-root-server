<?php
	/**
		\file napdf.class.php
		
		\brief This is the structural implementation of a PDF-printable list. It is meant to be a base for focused lists.
        
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

	require_once ( dirname ( __FILE__ ).'/fpdf16/fpdf.php' );							///< This is the FPDF class, used to create the PDF.
	require_once ( dirname ( __FILE__ ).'/BMLT_Satellite_local.class.php' );	///< This is the class we use to communicate with the root server.
	
	/**
		\brief This class sets up a boilerplate FPDF instance to be used to generate the PDF.
		
		It is a SINGLETON pattern. This should be created in bunches, because the objects can be quite big, and the static elements
		make the key sorting easier.
	*/
	class napdf extends FPDF
	{
		static $fpdf_instance = null;	///< This is a SINGLETON, so we'll be using this as our instance.
		static $sort_order_keys = null;	///< This specifies which keys will be sorted. See the set_sort function for a description of how this works.
		static $week_starts = 1;		///< We assume that the week starts on Sunday. 1 - Sunday, 7 - Saturday. Set this to whatever day the week starts.
		static $sort_callback = 'napdf::sort_meeting_data_callback';		///< Used to sort the CSV data. It can be replaced with a call to a custom sorter.
		
		var	$bmlt_instance = null;	///< We manage an instance of the class used to extract CSV data from the root server.
		var	$meeting_data = null;	///< This will be an array, with the returned meeting data.
		var	$format_data = null;	///< This will be an array, with the returned format data.
		var	$lang_search = array ( 'en' );	///< Set this to the specific language[s], if we are looking for formats in a specific language. Default is 'en'.
		
		/**
			\brief Fetches the CSV data, and loads up our internal data array with all the meeting data. It creates an associative array
			of the meeting data.
			
			This loads the internal $meeting_data nested array, in which each meeting is one row, then an associative array of meeting data values.
		*/
		private function FetchCSV ( $in_http_vars = null	///< These contain alternatives to the $_GET and/or $_POST parameters. Default is null.
									)
			{
			if ( $this->bmlt_instance instanceof BMLT_Satellite_local )
				{
				// First, we get the formats.
				
				if ( !isset ( $in_http_vars ) || !is_array ( $in_http_vars ) || !count ( $in_http_vars ) )
				    {
				    $in_http_vars = array ( 'lang_enum' => $this->lang_search );
				    }
				else
				    {
				    $in_http_vars['lang_enum'] = $this->lang_search;
				    }
				
				$format_params = $in_http_vars;
				$format_params['get_formats_only'] = 1;
				
				$format_data = $this->bmlt_instance->Execute ( 'csv', $format_params );
				// First, we get the formats.

				if ( $format_data )
					{
					$format_data_ar = explode ( "\n", $format_data );
				
					if ( is_array ( $format_data_ar ) && (count ( $format_data_ar ) > 1) )
						{
						$this->format_data = array();
						
						$keys = explode ( '","', $format_data_ar[0] );
						
						for ( $c = 0; $c < count ( $keys ); $c++ )
							{
							$keys[$c] = stripslashes ( preg_replace ( '/^\"|\"$/', '', $keys[$c] ) );
							}
						
						$format_data_ar[0] = null;
						unset ( $format_data_ar[0] );
						
						foreach ( $format_data_ar as $format )
							{
							$format = explode ( '","', $format );
							if ( is_array ( $format ) && (count ( $format ) == count ( $keys )) )
								{
								$fmt = array ();
								$count = 0;
								foreach ( $keys as $key )
									{
									$fmt[$key] = stripslashes ( preg_replace ( '/^\"|\"$/', '', $format[$count++] ) );
									}
								
								$this->format_data[$fmt['id'].'_'.$fmt['lang']] = $fmt;
								}
							}
						}
					}
				// Next, we get the meetings.
				$meeting_data = $this->bmlt_instance->Execute ( 'csv', $in_http_vars );
				
				if ( $meeting_data )
					{
					$meeting_data_ar = explode ( "\n", $meeting_data );
				
					if ( is_array ( $meeting_data_ar ) && (count ( $meeting_data_ar ) > 1) )
						{
						$this->meeting_data = array();
						
						$keys = explode ( '","', $meeting_data_ar[0] );
						
						for ( $c = 0; $c < count ( $keys ); $c++ )
							{
							$keys[$c] = stripslashes ( preg_replace ( '/^\"|\"$/', '', $keys[$c] ) );
							}
						
						$meeting_data_ar[0] = null;
						unset ( $meeting_data_ar[0] );
						
						foreach ( $meeting_data_ar as $meeting )
							{
							$meeting = explode ( '","', $meeting );
							if ( is_array ( $meeting ) && (count ( $meeting ) == count ( $keys )) )
								{
								$mtg = array ();
								$count = 0;
								foreach ( $keys as $key )
									{
									$mtg[$key] = stripslashes ( preg_replace ( '/^\"|\"$/', '', $meeting[$count++] ) );
									}
								
								array_push ( $this->meeting_data, $mtg );
								}
							}
						}
					}
				}
			else
				{
				throw new Exception ( 'No BMLT object!' );
				}
			}
		
		/**
			\brief	This is the object factory function. It will return the new instance of the napdf class.
			
			NOTE: This ALWAYS destroys the original object, so you need to keep that in mind!
			
			\returns A reference to an instance of napdf
		*/
		static function MakeNAPDF (	$in_x,					///< The width of each printed page, in $in_units
									$in_y,					///< The height of each printed page, in $in_units
									$in_http_vars,			///< The various parameters used to dictate the meeting search.
									$in_units = 'in',		/**< The measurement units.
																- 'in' (Inches)
																- 'mm' (Millimeters)
																- 'cm' (Centimeters)
																- 'pt' (Points)
																Default is 'in'.
															*/
									$in_orientation = 'P',	/**< The orientation
																- 'P' (Portrait)
																- 'L' (Landscape)
																Default is 'P'
															*/
									$in_keys = null,		///< Optional. If the sort keys are passed in here, we'll sort the data.
									$in_lang_search = null	///< Optional. An array of language enums, used to extract the correct format codes.
									)
			{
			self::$fpdf_instance = null;
			self::$fpdf_instance = new napdf ( $in_x, $in_y, $in_http_vars, $in_units, $in_orientation, $in_lang_search );
			
			if ( self::$fpdf_instance instanceof napdf )
				{
				if ( (self::$fpdf_instance->bmlt_instance instanceof BMLT_Satellite_local) && is_array ( self::$fpdf_instance->meeting_data ) )
					{
					if ( is_array ( $in_keys ) )
						{
						self::$fpdf_instance->set_sort ( $in_keys );
						}
					}
				else
					{
					self::$fpdf_instance = null;
					}
				}
			else
				{
				self::$fpdf_instance = null;
				}
			
			return self::$fpdf_instance;
			}
		
		/**
			\brief	This is the way you sort the meeting data.
		*/
		function set_sort (	$in_keys	/**<	This is the way we control a sort. It is both very powerful, and non-trivial.
												This is an associative array, and the order of the keys determines sort priority,
												with the first key being the highest priority, and subsequent keys descending in
												importance.
												
												This sort is far more powerful than the basic one used in the root server. It is designed
												to allow you to be very specific in your sort criteria for your printed list.
												
												The value of each key determines the direction of the sort. If it is 0, false or null,
												the sort will be descending (lower to greater). If it is 1 or true, then the sort will
												be ascending.
												
												This function executes the sort, so the meeting_data array is sorted after this function.
										*/
								)
			{
				if ( isset ( $in_keys['week_starts'] ) && ($in_keys['week_starts'] > 0) && ($in_keys['week_starts'] < 8) )	// This little dope fiend move is how we set a non-Sunday week start.
					{
					self::$week_starts = $in_keys['week_starts'];
					}
				
				self::$sort_order_keys = $in_keys;
				
				$callback_func = explode ( "::", self::$sort_callback );
				
				return usort ( $this->meeting_data, $callback_func );
			}
		
		/**
			\brief	This is a static callback function to be used for sorting the multi-dimensional meeting_data
					array. It uses the sort_order_keys array to determine the sort.
					
			\returns an integer. -1 if a < b, 0 if a == b, or 1 if a > b.
		*/
		static function sort_meeting_data_callback (	&$in_a,		///< The first meeting array to compare
														&$in_b		///< The second meeting array to compare
														)
			{
			$ret = 0;
			
			if ( is_array ( $in_a ) && is_array ( $in_b ) && is_array ( napdf::$sort_order_keys ) )
				{
				// We reverse the array, in order to sort from least important to most important.
				$sort_keys = array_reverse ( napdf::$sort_order_keys, true );
				
				foreach ( $sort_keys as $key => $value )
					{
					if ( isset ( $in_a[$key] ) && isset ( $in_b[$key] ) )
						{
						$val_a = trim ( $in_a[$key] );
						$val_b = trim ( $in_b[$key] );

						if ( ('weekday_tinyint' == $key) && (napdf::$week_starts > 1) && (napdf::$week_starts < 8) )
							{
							$val_a -= napdf::$week_starts;

							if ( $val_a < 0 )
								{
								$val_a += 8;
								}
							else
								{
								$val_a += 1;
								}
							
							$val_b -= napdf::$week_starts;
							
							if ( $val_b < 0 )
								{
								$val_b += 8;
								}
							else
								{
								$val_b += 1;
								}
							}

						// We know a few keys already, and we can determine how the sorting goes from there.
						switch ( $key )
							{
							case 'start_time':
							case 'duration_time':
								$val_a = strtotime ( $val_a );
								$val_b = strtotime ( $val_b );
							case 'weekday_tinyint':
							case 'id_bigint':
							case 'shared_group_id_bigint':
							case 'service_body_bigint':
								$val_a = intval ( $val_a );
								$val_b = intval ( $val_b );
							case 'longitude':
							case 'latitude':
								if ( $val_a > $val_b )
									{
									$ret = 1;
									}
								elseif ( $val_b > $val_a )
									{
									$ret = -1;
									}
							break;
							
							default:
								// We ignore blank values
								if ( strlen ( $val_a ) && strlen ( $val_b ) )
									{
									$tmp = strcmp ( strtolower ( $val_a ), strtolower ( $val_b ) );
									
									if ( $tmp != 0 )
										{
										$ret = $tmp;
										}
									}
							break;
							}
						}
					
					if ( !$value )
						{
						$ret = -$ret;
						}
					}
				}
			
			return $ret;
			}
		
		/**
			\brief	The class constructor. Sets up the instance, and reads in the meeting data.
			
			It's private, in order to keep multiple instances from being created. Use MakeNAPDF() to create objects.
		*/
		private function __construct (	$in_x,					///< The width of each printed page, in $in_units
										$in_y,					///< The height of each printed page, in $in_units
										$in_http_vars,			///< The various parameters used to dictate the meeting search.
										$in_units = 'in',		/**< The measurement units.
																	- 'in' (Inches)
																	- 'mm' (Millimeters)
																	- 'cm' (Centimeters)
																	- 'pt' (Points)
																	Default is 'in'.
																*/
										$in_orientation = 'P',	/**< The orientation
																	- 'P' (Portrait)
																	- 'L' (Landscape)
																	Default is 'P'
																*/
									 	$in_lang_search = null	///< An array of language enums, used to extract the correct format codes.
										)
			{
			if ( is_array ( $in_lang_search ) && count ( $in_lang_search ) )
				{
				$this->lang_search = $in_lang_search;
				}
			/* We use the static function to create the SINGLETON. */
			$this->bmlt_instance = BMLT_Satellite_local::MakeBMLT ( true );	// Make a special CSV object.
			
			/* Quick sanity check. */
			if ( !($this->bmlt_instance instanceof BMLT_Satellite_local) )
				{
				throw new Exception ( 'The BMLT object could not be created' );
				}
			$this->FPDF ( $in_orientation, $in_units, array ( $in_x, $in_y ) );
			$this->SetAutoPageBreak ( 0 );
			$this->SetAuthor ( "BMLT" );
			$this->SetCreator ( "BMLT" );
			$this->SetSubject ( "Printable Meeting List" );
			$this->SetTitle ( "Printable Meeting List" );
			$this->FetchCSV ( $in_http_vars );
			// Okay, at this point, we've set up the PDF object, and have done the meeting search. Our meetings are waiting for us to start making the list.
			}
	};
?>
