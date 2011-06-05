<?php
/***********************************************************************/
/** \file	change_desc.inc.php
	\brief	The change description phrases for this language (Spanish)
    
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
    along with this code.  If not, see <http://www.gnu.org/licenses/>.*/
	defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

	$change_type_strings = array (
		'__THE_MEETING_WAS_CHANGED__' => 'The meeting was changed.',
		'__THE_MEETING_WAS_CREATED__' => 'The meeting was created.',
		'__THE_MEETING_WAS_DELETED__' => 'The meeting was deleted.',
		'__THE_MEETING_WAS_ROLLED_BACK__' => 'The meeting was rolled back to a previous version.',
	
		'__THE_FORMAT_WAS_CHANGED__' => 'The format was changed.',
		'__THE_FORMAT_WAS_CREATED__' => 'The format was created.',
		'__THE_FORMAT_WAS_DELETED__' => 'The format was deleted.',
		'__THE_FORMAT_WAS_ROLLED_BACK__' => 'The format was rolled back to a previous version.',
	
		'__THE_SERVICE_BODY_WAS_CHANGED__' => 'The service body was changed.',
		'__THE_SERVICE_BODY_WAS_CREATED__' => 'The service body was created.',
		'__THE_SERVICE_BODY_WAS_DELETED__' => 'The service body was deleted.',
		'__THE_SERVICE_BODY_WAS_ROLLED_BACK__' => 'The service body was rolled back to a previous version.',
	
		'__THE_USER_WAS_CHANGED__' => 'The user was changed.',
		'__THE_USER_WAS_CREATED__' => 'The user was created.',
		'__THE_USER_WAS_DELETED__' => 'The user was deleted.',
		'__THE_USER_WAS_ROLLED_BACK__' => 'The user was rolled back to a previous version.',
	
		'__BY__' => 'by',
		'__FOR__' => 'for'
	);
	
	$detailed_change_strings = array (
		'was_changed_from' => 'was changed from',
		'to' => 'to',
		'was_changed' => 'was changed',
		'was_added_as' => 'was added as',
		'was_deleted' => 'was deleted',
		'was_published' => 'The meeting was published',
		'was_unpublished' => 'The meeting was unpublished',
		'formats_prompt' => 'The meeting format',
		'duration_time' => 'The meeting duration',
		'start_time' => 'The meeting start time',
		'longitude' => 'The meeting longitude',
		'latitude' => 'The meeting latitude',
		'sb_prompt' => 'The meeting changed its Service Body from',
		'id_bigint' => 'The meeting ID',
		'lang_enum' => 'The meeting language',
		'worldid_mixed' => 'The World Services ID',
		'worldid_mixed' => 'The shared Group ID',
		'weekday_tinyint' => 'The day of the week on which the meeting gathers',
		'non_existent_service_body' => 'Service Body No Longer Exists',
	);
	
	defined ( '_END_CHANGE_REPORT' ) or define ( '_END_CHANGE_REPORT', '.' );
?>