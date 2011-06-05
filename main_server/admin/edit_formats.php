<?php
/***********************************************************************/
/** \file	edit_formats.php

	\brief	Displays a form for editing formats.

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

	require_once ( dirname ( __FILE__ ).'/../server/c_comdef_server.class.php' );
		
/*******************************************************************/
/** \brief	Gets an array, with all IDs, in all languages.
	The idea behind this is to ensure that every available format
	is represented in the list, even if it is not for the language
	being edited.

	\returns an associative array, with all the IDs as keys, sorted,
	and all the language enums as values.
*/
function GetAllIDs ($in_lang_enum,				///< An enum, for the lanuage selected as primary.
					$in_sort_by = 'shared_id'	///< A string. This tells the function how to sort the returned array. Default is by ID.
					)
{
	$ret = null;
	
	// What we do here is kinda odd. We start off by loading the array with the IDs of the server language,
	// then we "fill in the blanks" with other languages. Finally, we fill in the ones our language covers.
	$lang_enum_server = c_comdef_server::GetServer()->GetLocalLang();

	// This wacky exercise is to collate all the shared IDs, across all languages, and sort them.
	$all_formats =& c_comdef_server::GetServer()->GetFormatsObj();
	
	if ( $all_formats instanceof c_comdef_formats )
		{
		$ret1 = null;
		$formats_array =& $all_formats->GetFormatsArray();

		// First, we load the array with all the base server formats.
		foreach ( $formats_array[$lang_enum_server] as &$format )
			{
			$id = intval ( $format->GetSharedID() );
			$extra = array ( 'id'=> $id, 'lang'=> $lang_enum_server, 'key'=>$format->GetKey(), 'type'=>$format->GetFormatType() );
			$ret1[$id] = $extra;
			}

		// Next, we override the array with all the current language formats.
		foreach ( $formats_array[$in_lang_enum] as &$format )
			{
			$id = intval ( $format->GetSharedID() );
			$extra = array ( 'id'=> $id, 'lang'=> $in_lang_enum, 'key'=>$format->GetKey(), 'type'=>$format->GetFormatType() );
			$ret1[$id] = $extra;
			}

		// For all the languages that are not the server or the primary, we see if any are declared that aren't on the server (Shouldn't happen).
		foreach ( $formats_array as $key => &$one_lang )
			{
			if ( ($key != $in_lang_enum) && ($key != $lang_enum_server) )
				{
				foreach ( $one_lang as &$format )
					{
					$id = intval ( $format->GetSharedID() );
					if ( !isset ( $ret1[$id] ) )
						{
						$extra = array ( 'id'=> $id, 'lang'=> $key, 'key'=>$format->GetKey(), 'type'=>$format->GetFormatType() );
						$ret1[$id] = $extra;
						}
					}
				}
			}
		
		$ret = array ();
		
		foreach ( $ret1 as $extra )
			{
			array_push ( $ret, $extra );
			}
		
		// Fixed a bug pointed out by MG on 12/19/2010. The function callbacks needed to be quoted.
		switch ( $in_sort_by )
			{
			case	'format_key':
				usort ( $ret, 'usort_by_key' );
			break;
			
			case	'format_type':
				usort ( $ret, 'usort_by_type' );
			break;
			
			default:
				usort ( $ret, 'usort_by_id' );
			break;
			}
		}

	return $ret;
}

/*******************************************************************/
/** \brief	Sorter callback for formats

	\returns an integer -1, 0 1, depending on how they compare
*/
function usort_by_id ( $a,
						$b
						)
{
	return ( intval ( $a['id'] ) > intval ( $b['id'] ) ) ? 1 : (( intval ( $a['id'] ) < intval ( $b['id'] ) ) ? -1 : 0);
}

/*******************************************************************/
/** \brief	Sorter callback for formats

	\returns an integer -1, 0 1, depending on how they compare
*/
function usort_by_key ( $a,
						$b
						)
{
	$ret = strcmp ( $a['key'], $b['key'] );
	if ( !$ret )
		{
		$ret = ( intval ( $a['id'] ) > intval ( $b['id'] ) ) ? 1 : (( intval ( $a['id'] ) < intval ( $b['id'] ) ) ? -1 : 0);
		}
	
	return $ret;
}

/*******************************************************************/
/** \brief	Sorter callback for formats

	\returns an integer -1, 0 1, depending on how they compare
*/
function usort_by_type ( $a,
						$b
						)
{
	$ret = strcmp ( $a['type'], $b['type'] );
	
	if ( !$ret )
		{
		$ret = strcmp ( $a['key'], $b['key'] );
		}
	
	if ( !$ret )
		{
		$ret = ( intval ( $a['id'] ) > intval ( $b['id'] ) ) ? 1 : (( intval ( $a['id'] ) < intval ( $b['id'] ) ) ? -1 : 0);
		}
	
	return $ret;
}

/*******************************************************************/
/** \brief	This returns a form, used to edit formats.

	\returns a string, containing the HTML for the form
*/
function DisplayFormatsForEdit (
								$in_http_vars	///< An associative array that is a blend of the $_POST and $_GET arrays.
								)
{
	$ret = null;
	
	$server = c_comdef_server::MakeServer();

	if ( $server instanceof c_comdef_server )
		{
		$lang_enum = c_comdef_server::GetServer()->GetLocalLang();

		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
		
		if ( isset ( $in_http_vars['lang_enum'] ) && $in_http_vars['lang_enum'] )
			{
			$lang_enum = $in_http_vars['lang_enum'];
			}
		
		if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
			{
			$script_name = preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] );
			}
		else
			{
			$script_name = $in_http_vars['script_name'];
			}
		
		if ( !isset ( $in_http_vars['comdef_format_sort_select'] ) || !$in_http_vars['comdef_format_sort_select'] )
			{
			if ( isset ( $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'] ) )
				{
				$in_http_vars['comdef_format_sort_select'] = $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'];
				}
			else
				{
				$in_http_vars['comdef_format_sort_select'] = 'shared_id';
				}
			}
		
		$_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'] = $in_http_vars['comdef_format_sort_select'];
		setcookie ( BMLT_COOKIE.'_meeting_edit_format_key', $in_http_vars['comdef_format_sort_select'], time() + (60 * 60 * 24 * 366), '/' );
		
		$localized_strings = c_comdef_server::GetLocalStrings();
		
		$cur_user =& c_comdef_server::GetCurrentUserObj();
		
		// Only server administrators can edit formats. You can also specify that any Service Body admin can edit them (inadvisable).
		if ( ($cur_user instanceof c_comdef_user) && (c_comdef_server::IsUserServerAdmin()
			|| (($cur_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) && $any_service_body_admin_can_edit_formats)) )
			{
			$language = c_comdef_server::GetServerLangs();
			$script_uri = $_SERVER['SCRIPT_NAME'].'?';
			
			$first = true;
			foreach ( $in_http_vars as $key => $value )
				{
				if ( ($key != 'lang_enum') && ($key != 'open_formats') )
					{
					if ( !$first )
						{
						$script_uri .= '&amp;';
						}
					else
						{
						$first = false;
						}
					
					$script_uri .= $key.'='.$value;
					}
				}
			
			if ( !$first )
				{
				$script_uri .= '&';
				}
			
			$ret = "<div id=\"formats_container_div\" class=\"formats_div_".(isset ( $in_http_vars['open_formats']) ? 'open' : 'closed')."\"><a href=\"javascript:ToggleFormatsDiv()\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['FormatsDiv'] ).$localized_strings['prompt_delimiter']."</a>";
				$ret .= '<form class="edit_format_form" action="#" method="post" onsubmit="return false">';
					$ret .= '<div id="edit_formats_div" class="edit_format_div" style="display:'.(isset ( $in_http_vars['open_formats']) ? 'block' : 'none').'">';
						$ret .= '<div class="format_sort_div">';
							$ret .= '<label for="comdef_format_sort_select">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_select_label'] ) .'</label>';
							$ret .= '<select id="comdef_format_sort_select" name="comdef_format_sort_select" onchange="ReSortFormats(\''.$script_uri.'\')">';
								$ret .= '<option value="shared_id"';
								if ( $in_http_vars['comdef_format_sort_select'] == 'shared_id' )
									{
									$ret .= ' selected="selected"';
									}
								$ret .= '>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_option_id'] ) .'</option>';
								$ret .= '<option value="format_key"';
								if ( $in_http_vars['comdef_format_sort_select'] == 'format_key' )
									{
									$ret .= ' selected="selected"';
									}
								$ret .= '>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_option_key'] ) .'</option>';
								$ret .= '<option value="format_type"';
								if ( $in_http_vars['comdef_format_sort_select'] == 'format_type' )
									{
									$ret .= ' selected="selected"';
									}
								$ret .= '>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_option_type'] ) .'</option>';
							$ret .= '</select>';
						$ret .= '</div>';
						// In order to change the language, we create a popup as the legend for the fieldset, and append the value to a copy of the
						// call URI. This will be a GET transaction.
						$ret .= '<fieldset id="edit_format_lang_fieldset" class="edit_format_lang_fieldset">';
							$ret .= '<legend class="edit_format_lang_legend">';
								$ret .= '<label for="edit_formats_lang_enum" style="display:none">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['language'] ) .'</label><select id="edit_formats_lang_enum" onchange="javascript:ReSortFormats(\''.$script_uri.'\')">';
								$lang_enum_server = c_comdef_server::GetServer()->GetLocalLang();
								foreach ( $language as $key => $value )
									{
									$ret .= '<option value="'.c_comdef_htmlspecialchars ( $key ).'"';
									if ( $key == $lang_enum )
										{
										$ret .= ' selected="selected"';
										}
									$ret .= '>'.c_comdef_htmlspecialchars ( trim ( $value ) );
									if ( $key == $lang_enum_server )
										{
										$ret .= ' ('.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['native_lang'] ).')';
										}
									$ret .= '</option>';
									}
								$ret .= '</select>';
							$ret .= '</legend>';
							$ret .= '<div id="c_comdef_edit_format_format_list_div" class="c_comdef_edit_format_format_list">';
								$id_array = GetAllIDs ( $lang_enum, $in_http_vars['comdef_format_sort_select'] );
								$last_id = 0;
								foreach ( $id_array as $extra )
									{
									$id = $extra['id'];
									$lang = $extra['lang'];
									$one_format = c_comdef_server::GetOneFormat ( $id, $lang );
									if ( $one_format instanceof c_comdef_format )
										{
										$langs =& c_comdef_server::GetServer()->GetServerLangs();
										
										$ret .= '<fieldset class="edit_one_format_fieldset';
										
										if ( $lang == $lang_enum )
											{
											$ret .= ' native_lang_format_fieldset';
											}
										else
											{
											$ret .= ' inherited_lang_format_fieldset';
											}
										
										$ret .= '" id="edit_one_format_fieldset_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">';
											$ret .= '<legend id="edit_one_format_legend_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" class="edit_one_format_legend">'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() );
											if ( $lang != $lang_enum )
												{
												$ret .=' ('.c_comdef_htmlspecialchars ( $langs[$one_format->GetLocalLang()] ).')';
												}
											$ret .= '</legend>';
											$ret .= '<div class="edit_format_fields_div">';
												$ret .= '<div class="edit_format_value_div" id="edit_format_value_div_type_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">';
													$ret .= '<label id="format_type_label_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" for="format_type_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['type_label'] ).$localized_strings['prompt_delimiter'].'</label>';
													$ret .= '<select class="format_type_select" id="format_type_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" onchange="EnableFormatChangeButton('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).')">';
													foreach ( $localized_strings['comdef_format_types'] as $key => $value )
														{
														$ret .= '<option value="'.c_comdef_htmlspecialchars ( $key ).'"';
														if ( $key == $one_format->GetFormatType() )
															{
															$ret .= ' selected="selected"';
															}
														$ret .= '>';
														$ret .= c_comdef_htmlspecialchars ( $value );
														$ret .= '</option>';
														}
													$ret .= '</select>';
												$ret .= '</div>';
												$ret .= '<div class="edit_format_value_div" id="edit_format_value_div_key_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">';
													$ret .= '<label id="format_key_label_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" for="format_key_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['key_label'] ).$localized_strings['prompt_delimiter'].'</label>';
													$ret .= '<input type="text" class="edit_one_format_key" name="format_key_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" id="format_key_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" value="'.c_comdef_htmlspecialchars ( $one_format->GetKey() ).'" size="5" onchange="EnableFormatChangeButton('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).')" onkeyup="EnableFormatChangeButton('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).')"/>';
												$ret .= '</div>';
												$ret .= '<div class="edit_format_value_div" id="edit_format_value_div_name_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">';
													$ret .= '<label id="format_name_label_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" for="format_name_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['name_label'] ).$localized_strings['prompt_delimiter'].'</label>';
													$ret .= '<input type="text" class="edit_one_format_name" name="format_name_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" id="format_name_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" value="'.c_comdef_htmlspecialchars ( $one_format->GetLocalName() ).'" size="32" onchange="EnableFormatChangeButton('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).')" onkeyup="EnableFormatChangeButton('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).')" />';
												$ret .= '</div>';
												$ret .= '<div class="edit_format_value_div" id="edit_format_value_div_desc_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">';
													$ret .= '<label id="format_description_label_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" for="format_description_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['description_label'] ).$localized_strings['prompt_delimiter'].'</label>';
													$ret .= '<textarea class="edit_one_format_description_textarea" name="format_description_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" id="format_description_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" cols="64" rows="2" onchange="EnableFormatChangeButton('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).')" onkeyup="EnableFormatChangeButton('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).')">';
													$ret .= c_comdef_htmlspecialchars ( $one_format->GetLocalDescription() );
													$ret .= '</textarea>';
												$ret .= '</div>';
											$ret .= '</div>';
											$ret .= '<div class="edit_format_buttons_div">';
												if ( $lang == $lang_enum )
													{
													$ret .= '<div class="edit_format_button_div">';
														$ret .= '<input type="button" id="format_del_button_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" class="format_delete_button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['delete_button'] ).'" onclick="DeleteFormat('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).',\''.c_comdef_htmlspecialchars ( $one_format->GetLocalLang() ).'\',\''.c_comdef_htmlspecialchars ( $_SERVER['SCRIPT_NAME'].'?edit_cp&' ).'\')" />';
													$ret .= '</div>';
													$ret .= '<div class="edit_format_button_div">';
														$ret .= '<input disabled="disabled" type="button" id="format_ch_button_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" class="format_change_button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['change_button'] ).'" onclick="ChangeFormat('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).',\''.c_comdef_htmlspecialchars ( $one_format->GetLocalLang() ).'\')" />';
													$ret .= '</div>';
													}
												else
													{
													$ret .= '<div class="edit_format_button_div">';
														$ret .= '<input type="button" id="format_copy_button_'.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).'" class="format_copy_button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['copy_button'] ).'" onclick="CopyFormat('.c_comdef_htmlspecialchars ( $one_format->GetSharedID() ).',\''.c_comdef_htmlspecialchars ( $lang ).'\',\''.c_comdef_htmlspecialchars ( $lang_enum ).'\')" />';
													$ret .= '</div>';
													}
											$ret .= '<div class="clear_both"></div></div>';
										$ret .= '<div class="clear_both"></div></fieldset>';
										}
									$last_id = max($last_id,$id);
									}
								$last_id++;
								$ret .= '<fieldset class="edit_one_format_fieldset new_format_fieldset" id="edit_one_format_fieldset_'.$last_id.'">';
									$ret .= '<legend id="edit_one_format_legend_'.$last_id.'" class="edit_one_format_legend">'.$last_id;
									$ret .=' ('.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['new_format'] ).')';
									$ret .= '</legend>';
									$ret .= '<div class="edit_format_fields_div">';
										$ret .= '<div class="edit_format_value_div">';
											$ret .= '<label id="format_type_label_'.$last_id.'" for="format_type_'.$last_id.'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['type_label'] ).$localized_strings['prompt_delimiter'].'</label>';
											$ret .= '<select class="format_type_select" id="format_type_'.$last_id.'">';
											foreach ( $localized_strings['comdef_format_types'] as $key => $value )
												{
												$ret .= '<option value="'.c_comdef_htmlspecialchars ( $key ).'">'.c_comdef_htmlspecialchars ( $value ).'</option>';
												}
											$ret .= '</select>';
										$ret .= '</div>';
										$ret .= '<div class="edit_format_value_div">';
											$ret .= '<label id="format_key_label_'.$last_id.'" for="format_key_'.$last_id.'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['key_label'] ).$localized_strings['prompt_delimiter'].'</label>';
											$ret .= '<input type="text" class="edit_one_format_key" name="format_key_'.$last_id.'" id="format_key_'.$last_id.'" size="5" />';
										$ret .= '</div>';
										$ret .= '<div class="edit_format_value_div">';
											$ret .= '<label id="format_name_label_'.$last_id.'" for="format_name_'.$last_id.'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['name_label'] ).$localized_strings['prompt_delimiter'].'</label>';
											$ret .= '<input type="text" class="edit_one_format_name" name="format_name_'.$last_id.'" id="format_name_'.$last_id.'" size="32" />';
										$ret .= '</div>';
										$ret .= '<div class="edit_format_value_div">';
											$ret .= '<label id="format_description_label_'.$last_id.'" for="format_description_'.$last_id.'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['description_label'] ).$localized_strings['prompt_delimiter'].'</label>';
											$ret .= '<textarea class="edit_one_format_description_textarea" name="format_description_'.$last_id.'" id="format_description_'.$last_id.'" cols="64" rows="2"></textarea>';
										$ret .= '</div>';
									$ret .= '</div>';
									$ret .= '<div class="edit_format_buttons_div">';
										$ret .= '<div class="edit_format_button_div">';
											$ret .= '<input type="button" id="format_copy_button_'.$last_id.'" class="format_copy_button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['add_button'] ).'" onclick="CopyFormat('.$last_id.',null,\''.c_comdef_htmlspecialchars ( $lang_enum ).'\',\''.c_comdef_htmlspecialchars ( $_SERVER['SCRIPT_NAME'].'?edit_cp&' ).'\')" />';
										$ret .= '</div>';
									$ret .= '<div class="clear_both"></div></div>';
								$ret .= '<div class="clear_both"></div></fieldset>';
							$ret .= '</div>';
						$ret .= '<div class="clear_both"></div></fieldset>';
					$ret .= '</div>';
				$ret .= '</form>';
			$ret .= '</div>';
			}
		}
	
	return $ret;
}
?>