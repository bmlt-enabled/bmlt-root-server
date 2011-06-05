<?php
/***********************************************************************/
/** \file	format_sorter_ajax.php

	\brief	This is an AJAX handler that creates the HTML for the list of formats
	to be displayed in the meeting editor. It allows them to be sorted and
	rearranged without the window requiring a refresh.
	
	It will generate the HTML for the formats checkbox array.

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
define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context

require_once ( dirname ( __FILE__ ).'/../server/c_comdef_server.class.php' );
require_once ( dirname ( __FILE__ )."/../server/shared/classes/comdef_utilityclasses.inc.php" );
	
session_start();
$server = c_comdef_server::MakeServer();

if ( $server instanceof c_comdef_server )
	{		
	$meeting_id = intval ( $_GET['meeting_id'] );
	if ( !$meeting_id )
		{
		$meeting_id = intval ( $_POST['meeting_id'] );
		}

	$def_formats = $_GET['def_formats'];
	if ( !$def_formats )
		{
		$def_formats = $_POST['def_formats'];
		}

	$def_formats = explode ( ',', $def_formats );
	
	$sel_formats = $_GET['formats'];
	if ( !$sel_formats )
		{
		$sel_formats = $_POST['formats'];
		}

	$sel_formats = explode ( ',', $sel_formats );
	
	$in_sort_by = $_GET['sort_formats_by'];
	if ( !$in_sort_by )
		{
		$in_sort_by = $_POST['sort_formats_by'];
		}
	
	if ( !$in_sort_by )
		{
		if ( isset ( $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'] ) )
			{
			$in_sort_by = $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'];
			}
		else
			{
			$in_sort_by = 'format_key';
			}
		}

	$lang_enum = c_comdef_server::GetServer()->GetLocalLang();
	include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
	
	if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
		{
		$script_name = preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] );
		}
	else
		{
		$script_name = $in_http_vars['script_name'];
		}

	include ( dirname ( __FILE__ )."/../server/config/lang/$lang_enum/format_codes.inc.php" );
	
	// This wacky exercise is to collate all the shared IDs, across all languages, and sort them.
	$all_formats =& c_comdef_server::GetServer()->GetFormatsObj();

	if ( $all_formats instanceof c_comdef_formats )
		{
		$format_array = null;
		$formats_array =& $all_formats->GetFormatsArray();

		if ( is_array ( $formats_array ) && count ( $formats_array ) )
			{
			if ( is_array ( $formats_array[$lang_enum_server] ) && count ( $formats_array[$lang_enum_server] ) )
				{
				// First, we load the array with all the base server formats.
				foreach ( $formats_array[$lang_enum_server] as &$format )
					{
					$id = intval ( $format->GetSharedID() );
					$format_array[$id] = array ( 'id'=> $id, 'lang'=> $lang_enum_server, 'key'=>$format->GetKey(), 'type'=>$format->GetFormatType(), 'name'=>$format->GetLocalName(), 'desc'=>$format->GetLocalDescription() );
					}
				}
			
			// For all the languages that are not the server or the primary, we see if any are declared that aren't on the server (Shouldn't happen).
			foreach ( $formats_array as $key => &$one_lang )
				{
				if ( ($key != $in_lang_enum) && ($key != $lang_enum_server) )
					{
					foreach ( $one_lang as &$format )
						{
						$id = intval ( $format->GetSharedID() );
						if ( !isset ( $format_array[$id] ) )
							{
							$format_array[$id] = array ( 'id'=> $id, 'lang'=> $key, 'key'=>$format->GetKey(), 'type'=>$format->GetFormatType(), 'name'=>$format->GetLocalName(), 'desc'=>$format->GetLocalDescription() );
							}
						}
					}
				}
			}
		
		switch ( $in_sort_by )
			{
			case	'shared_id':
				usort ( $format_array, usort_by_id );
			break;
			
			case	'format_type':
				usort ( $format_array, usort_by_type );
			break;
			
			default:
				usort ( $format_array, usort_by_key );
			break;
			}
		
		$prefs['format_sort'] = $in_sort_by;
		
		$_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'] = $in_sort_by;
		setcookie ( BMLT_COOKIE.'_meeting_edit_format_key', $in_sort_by, time() + (60 * 60 * 24 * 366), '/' );
		
		$ret = '';
		
		$format_switch = false;
		$last_type = '';
		
		if ( is_array ( $format_array ) && count ( $format_array ) )
			{
			foreach ( $format_array as $format_ar_elem )
				{
				$id = intval ( $format_ar_elem['id'] );
				$key = $format_ar_elem['key'];
				$type = $format_ar_elem['type'];
				$name = $format_ar_elem['name'];
				$desc = $format_ar_elem['desc'];
				if ( ($in_sort_by == 'format_type') && ($last_type != $type) )
					{
					if ( $format_switch )
						{
						$ret .= '</fieldset>';
						$format_switch = false;
						}
					$ret .= '<fieldset class="format_type_fieldset">';
					$ret .= '<legend class="format_type_legend">&nbsp;';
						$ret .= c_comdef_htmlspecialchars ( $comdef_format_types[$type] );
					$ret .= '&nbsp;</legend>';
					$last_type = $type;
					$format_switch = true;
					}
				
				$ret .= "<div class=\"meeting_value_format_div";
				if ( in_array ( $id, $def_formats ) )
					{
					$ret .= ' default_format';
					}
					
				$ret .= "\"><input type=\"checkbox\" onclick=\"EnableMeetingChangeButton('meeting_$meeting_id', false)\" onchange=\"EnableMeetingChangeButton('meeting_$meeting_id', false)\" value=\"\" id=\"meeting_$meeting_id"."_format_$id\"";
				if ( in_array ( $id, $sel_formats ) )
					{
					$ret .= ' checked="checked"';
					}
				$ret .= " />";
				$ret .= "<abbr title=\"".c_comdef_htmlspecialchars ( $desc )."\"><label class=\"checkbox_label\" for=\"meeting_$meeting_id"."_format_$id\">";
					if ( in_array ( $id, $sel_formats ) )
						{
						$ret .= "";
						}
					$ret .= '<span class="format_check_key">'.c_comdef_htmlspecialchars ( $key ).'</span> (<span class="format_check_name">'.c_comdef_htmlspecialchars ( $name ).'</span>)';
				$ret .= "</label></abbr>";
				$ret .= "</div>";
				}
			}
		
		if ( $format_switch )
			{
			$ret .= '</fieldset>';
			}
				
		echo ( $ret);
		}
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
?>