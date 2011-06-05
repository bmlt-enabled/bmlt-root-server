<?php
/***********************************************************************/
/** \file	change_desc.inc.php
	\brief	The change description phrases for this language (English)
    
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
		'__THE_MEETING_WAS_CHANGED__' => 'Das Meeting wurde geändert.',
		'__THE_MEETING_WAS_CREATED__' => 'Das Meeting wurde erstellt.',
		'__THE_MEETING_WAS_DELETED__' => 'Das Meeting wurde gelöscht.',
		'__THE_MEETING_WAS_ROLLED_BACK__' => 'Das Meeting wurde auf eine frühere Version zurückgesetzt.',
	
		'__THE_FORMAT_WAS_CHANGED__' => 'Das Format wurde geändert.',
		'__THE_FORMAT_WAS_CREATED__' => 'Das Format wurde erstellt.',
		'__THE_FORMAT_WAS_DELETED__' => 'Das Format wurde gelöscht.',
		'__THE_FORMAT_WAS_ROLLED_BACK__' => 'Das Format wurde auf eine frühere Version zurückgesetzt.',
	
		'__THE_SERVICE_BODY_WAS_CHANGED__' => 'Der Service-Body wurde geändert.',
		'__THE_SERVICE_BODY_WAS_CREATED__' => 'Der Service-Body wurde erstellet.',
		'__THE_SERVICE_BODY_WAS_DELETED__' => 'Der Service-Body wurde gelöscht.',
		'__THE_SERVICE_BODY_WAS_ROLLED_BACK__' => 'Der Service-Body wurde auf eine frühere Version zurückgesetzt.',
	
		'__THE_USER_WAS_CHANGED__' => 'Der Benutzer wurde geändert.',
		'__THE_USER_WAS_CREATED__' => 'Der Benutzer wurde erstellt.',
		'__THE_USER_WAS_DELETED__' => 'Der Benutzer wurde gelöscht.',
		'__THE_USER_WAS_ROLLED_BACK__' => 'Der Benutzer wurde auf eine frühere Version zurückgesetzt.',
	
		'__BY__' => 'durch',
		'__FOR__' => 'f¸r'
	);
	
	$detailed_change_strings = array (
		'was_changed_from' => 'wurde geändert von',
		'to' => 'auf',
		'was_changed' => 'wurde geändert',
		'was_added_as' => 'wurde hinzugef¸gt',
		'was_deleted' => 'wurde gelöscht',
		'was_published' => 'Das Meeting wurde veröffentlicht',
		'was_unpublished' => 'Das Meeting ist nicht mehr veröffentlicht',
		'formats_prompt' => 'Meetingsformat',
		'duration_time' => 'Meetingsdauer',
		'start_time' => 'Meetings-Startzeit',
		'longitude' => 'Meetings-L‰ngengrad',
		'latitude' => 'Meetings-Breitengrad',
		'sb_prompt' => 'Das Meeting änderte ihren Service-Body',
		'id_bigint' => 'Meetings-ID',
		'lang_enum' => 'Meetings-Sprache',
		'worldid_mixed' => 'NAWS-ID',
		'worldid_mixed' => 'Gemeinsame Gruppen-ID',
		'weekday_tinyint' => 'Der Tag der Woche, an dem das Meeting stattfindet',
		'non_existent_service_body' => 'Service-Body nicht mehr vorhanden',
	);
	
	defined ( '_END_CHANGE_REPORT' ) or define ( '_END_CHANGE_REPORT', '.' );
?>