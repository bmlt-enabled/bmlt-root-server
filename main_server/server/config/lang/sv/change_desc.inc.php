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
		'__THE_MEETING_WAS_CHANGED__' => 'Mötesinfo ändrat.',
		'__THE_MEETING_WAS_CREATED__' => 'Nytt möte skapat.',
		'__THE_MEETING_WAS_DELETED__' => 'Mötet är nu kasserat.',
		'__THE_MEETING_WAS_ROLLED_BACK__' => 'Mötets info återställdes till tidigare version.',
	
		'__THE_FORMAT_WAS_CHANGED__' => 'Formatet ändrat.',
		'__THE_FORMAT_WAS_CREATED__' => 'Nytt format skapat.',
		'__THE_FORMAT_WAS_DELETED__' => 'Formatet kasserat..',
		'__THE_FORMAT_WAS_ROLLED_BACK__' => 'Formatet återställdes till tidigare version.',
	
		'__THE_SERVICE_BODY_WAS_CHANGED__' => 'Serviceenhet ändrat.',
		'__THE_SERVICE_BODY_WAS_CREATED__' => 'Serviceenhet skapat.',
		'__THE_SERVICE_BODY_WAS_DELETED__' => 'Serviceenheten är nu kasserat.',
		'__THE_SERVICE_BODY_WAS_ROLLED_BACK__' => 'Serviceenheten återställdes till tidigare version.',
	
		'__THE_USER_WAS_CHANGED__' => 'Användaren ändrad.',
		'__THE_USER_WAS_CREATED__' => 'Användaren skapad.',
		'__THE_USER_WAS_DELETED__' => 'Användaren är nu kasserad',
		'__THE_USER_WAS_ROLLED_BACK__' => 'Användaren återställdes till tidigare version',
	
		'__BY__' => 'av',
		'__FOR__' => 'för'
	);
	
	$detailed_change_strings = array (
		'was_changed_from' => 'blev ändrad från',
		'to' => 'till',
		'was_changed' => 'blev ändrad',
		'was_added_as' => 'blev tillagd som',
		'was_deleted' => 'blev kasserad',
		'was_published' => 'blev publicerat',
		'was_unpublished' => 'mötet är nu opublicerat',
		'formats_prompt' => 'mötesformatet',
		'duration_time' => 'mötestiden',
		'start_time' => 'starttiden',
		'longitude' => 'mötets longitud',
		'latitude' => 'mötets lattitud',
		'sb_prompt' => 'mötets Serviceenhet ändrades från',
		'id_bigint' => 'mötets ID',
		'lang_enum' => 'mötets språk',
		'worldid_mixed' => 'Världsservice ID',
		'worldid_mixed' => 'Delad grupp ID',
		'weekday_tinyint' => 'Dagen i veckan då mötet är',
		'non_existent_service_body' => 'Serviceenheten finns inte längre.',
	);
	
	defined ( '_END_CHANGE_REPORT' ) or define ( '_END_CHANGE_REPORT', '.' );
?>