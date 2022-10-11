<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.
require_once(dirname(__FILE__).'/../../server/c_comdef_server.class.php');
require_once(dirname(__FILE__).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once(dirname(__FILE__).'/../../server/shared/Array2Json.php');
require_once(dirname(__FILE__).'/../../server/shared/Array2XML.php');
require_once(dirname(__FILE__).'/../../client_interface/csv/search_results_csv.php');
require_once(dirname(__FILE__).'/PhpJsonXmlArrayStringInterchanger.inc.php');
require_once(dirname(__FILE__) . '/../../server/shared/classes/VenueType.php');

/***********************************************************************************************************//**
    \class c_comdef_admin_main_console
    \brief Controls display of the main BMLT administration console.
***************************************************************************************************************/
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class c_comdef_admin_ajax_handler
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
{
    public $my_localized_strings;          ///< This will contain the localized strings and whatnot for display.
    public $my_server;                     ///< This hold the server object.
    public $my_user;                       ///< This holds the instance of the logged-in user.
    public $my_http_vars;                  ///< Contains the HTTP vars sent in.

    /*******************************************************************************************************//**
    \brief
    ***********************************************************************************************************/
    public function __construct(
        $in_http_vars   ///< The HTTP transaction parameters
    ) {
        $this->my_http_vars = $in_http_vars;
        $this->my_localized_strings = c_comdef_server::GetLocalStrings();
        $this->my_server = c_comdef_server::MakeServer();
        $this->my_user = $this->my_server->GetCurrentUserObj();

        // We check this every chance that we get.
        if (!$this->my_user || ($this->my_user->GetUserLevel() == _USER_LEVEL_DISABLED)) {
            die('NOT AUTHORIZED');
        }
    }

    /*******************************************************************************************************//**
    \brief
    \returns
    ***********************************************************************************************************/
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function parse_ajax_call()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $returned_text = '';

        $account_changed = false;

        if (isset($this->my_http_vars['set_format_change']) && $this->my_http_vars['set_format_change']) {
            $this->HandleFormatChange($this->my_http_vars['set_format_change']);
        }
        if (isset($this->my_http_vars['delete_format']) && $this->my_http_vars['delete_format']) {
            $this->HandleDeleteFormat($this->my_http_vars['delete_format']);
        } elseif (isset($this->my_http_vars['create_new_user']) && $this->my_http_vars['create_new_user']) {
            $this->HandleUserCreate($this->my_http_vars['create_new_user']);
        } elseif (isset($this->my_http_vars['set_user_change']) && $this->my_http_vars['set_user_change']) {
            $this->HandleUserChange($this->my_http_vars['set_user_change']);
        } elseif (isset($this->my_http_vars['delete_user']) && $this->my_http_vars['delete_user']) {
            $this->HandleDeleteUser($this->my_http_vars['delete_user'], isset($this->my_http_vars['permanently']));
        } elseif (isset($this->my_http_vars['create_new_service_body']) && $this->my_http_vars['create_new_service_body']) {
            $this->HandleServiceBodyCreate($this->my_http_vars['create_new_service_body']);
        } elseif (isset($this->my_http_vars['set_service_body_change']) && $this->my_http_vars['set_service_body_change']) {
            $this->HandleServiceBodyChange($this->my_http_vars['set_service_body_change']);
        } elseif (isset($this->my_http_vars['delete_service_body']) && $this->my_http_vars['delete_service_body']) {
            $this->HandleDeleteServiceBody($this->my_http_vars['delete_service_body'], isset($this->my_http_vars['permanently']));
        } elseif (isset($this->my_http_vars['set_meeting_change']) && $this->my_http_vars['set_meeting_change']) {
            $this->HandleMeetingUpdate($this->my_http_vars['set_meeting_change']);
        } elseif (isset($this->my_http_vars['delete_meeting']) && $this->my_http_vars['delete_meeting']) {
            $returned_text = $this->HandleDeleteMeeting($this->my_http_vars['delete_meeting']);
        } elseif (isset($this->my_http_vars['get_meeting_history']) && $this->my_http_vars['get_meeting_history']) {
            $returned_text = $this->GetMeetingHistory($this->my_http_vars['get_meeting_history']);
        } elseif (isset($this->my_http_vars['do_meeting_search'])) {
            $used_formats = array();
            $returned_text = $this->TranslateToJSON($this->GetSearchResults($this->my_http_vars, $used_formats));
            header('Content-Type:application/json; charset=UTF-8');
        } elseif (isset($this->my_http_vars['do_update_world_ids'])) {
            $returned_text = $this->HandleMeetingWorldIDsUpdate();
        } elseif (isset($this->my_http_vars['do_naws_import'])) {
            $returned_text = $this->HandleNAWSImport();
        } else {
            $this->HandleAccountChange();
        }

        return  $returned_text;
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleNAWSImport()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (!c_comdef_server::IsUserServerAdmin(null, true)) {
            return 'NOT AUTHORIZED';
        }

        $ret = array(
            'success' => false,
            'errors' => null,
            'report' => array(
                'num_service_bodies_created' => 0,
                'num_users_created' => 0,
                'num_meetings_created' => 0
            )
        );

        if (empty($_FILES)) {
            $ret['errors'] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_no_files_uploaded'];
            return json_encode($ret);
        }

        require_once(__DIR__.'/NAWSImport.php');
        require_once(__DIR__.'/NAWSImportServiceBodiesExistException.php');
        require_once(__DIR__.'/NAWSImportMeetingsExistException.php');

        try {
            $nawsImport = new NAWSImport($_FILES['thefile']['tmp_name'], $this->my_http_vars['initialValueForPublished'] == 'TRUE');
            $nawsImport->import(true);
        } catch (NAWSImportServiceBodiesExistException $e) {
            $ret['errors'] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_service_bodies_already_exist'] . implode(', ', $e->getWorldIds());
            return json_encode($ret);
        } catch (NAWSImportMeetingsExistException $e) {
            $ret['errors'] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_meetings_already_exist'] . implode(', ', $e->getWorldIds());
            return json_encode($ret);
        } catch (Exception $e) {
            $ret['errors'] = $e->getMessage();
            return json_encode($ret);
        }

        $ret['success'] = true;
        $ret['report']['num_service_bodies_created'] = $nawsImport->getNumServiceBodiesCreated();
        $ret['report']['num_users_created'] = $nawsImport->getNumUsersCreated();
        $ret['report']['num_meetings_created'] = $nawsImport->getNumMeetingsCreated();

        return json_encode($ret);
    }

    /* Method to handle updating World ID codes.  These are provided in a spreadsheet, usually sent by NAWS, with new world_id
    codes to be entered.  The only thing that will be changed doing this are world_id codes for meetings, nothing else.
    In addition to new codes, NAWS can also provide a new world_id 'deleted'.  This is for meetings that are already
    deleted and so noted by NAWS.  The code that produces a NAWS export file knows it can skip deleted meetings with
    this special world_id, just as it skips deleted meetings with a blank world_id.  (Otherwise NAWS keeps seeing these
    deleted meetings on the export file.)  The method will only update meetings that the current logged-in user has
    permission to edit.  For deleted meetings, the method checks that the current logged-in user could edit the meeting
    if it were restored.  The server admin can edit any meeting.  Note that deleted meetings exist only in the changes
    table of the database, not the meetings table; so to mark them, the code temporarily restores them, changes the world_ids,
    and deletes them again.  Here is the logic for handling the various cases of meetings in the spreadsheet read in.
    These are all based on the bmlt_id for the meeting.
        * if the meeting is in the meetings table in the database:
            + if user has edit rights to this meeting:
                - if the world_id is changing, update the world_id, and report it as 'updated'
                - if the world_id remains the same, don't change it, and report it as 'not_updated'
            + if user doesn't have edit rights to this meeting, don't change it, and report it as 'problem'
        * if the meeting is not in the meetings table in the database but is in the changes table:
            + if the new world_id is 'deleted':
                - if user had edit rights to this meeting:
                    - if the world_id wasn't already 'deleted', change it to 'deleted', and report it as 'marked'
                    - if the world_id was already 'deleted', don't change it, and report it as 'not_updated'
                - if user didn't have edit rights to this meeting, don't change it, and report it as 'problem'
            + if the new world_id anything else, report it as 'problem'
        * if the meeting is neither in the meetings table nor in the changes table, report it as 'problem'
    The results of doing the updates are returned in a json-encoded array.  */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleMeetingWorldIDsUpdate()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        // $ret holds the results from updating the world_ids.
        // TODO sometime: it would be better to encapsulate this functionality in a class MeetingWorldIDsUpdateResult.
        $ret = array(
            'success' => false,
            'errors' => array(),
            'report' => array(
                'updated' => array(),
                'not_updated' => array(),
                'marked' => array(),
                'problem' => array()
            )
        );

        $isServerAdmin = c_comdef_server::IsUserServerAdmin(null, true);
        if (!$isServerAdmin && !c_comdef_server::IsUserServiceBodyAdmin(null, true)) {
            return 'NOT AUTHORIZED';
        }

        if (empty($_FILES)) {
            $ret['errors'][] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_no_files_uploaded'];
            return json_encode($ret);
        }

        require_once(__DIR__ .'/../../../vendor/autoload.php');

        $file = $_FILES['thefile'];
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file['tmp_name']);
            $spreadsheet = $reader->load($file['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        } catch (Exception $e) {
            $ret['errors'][] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_could_not_create_reader'] . $e->getMessage();
            return json_encode($ret);
        }

        // $userServiceBodyIDs is an array of service body IDs of meetings that the current user is allowed to edit.
        // $userServiceBodyIDs is empty if the user is the serveradmin and can edit any meeting.
        $userServiceBodyIDs = [];
        if (!$isServerAdmin) {
            $ids = c_comdef_server::GetUserServiceBodies();
            if (is_array($ids)) {
                $userServiceBodyIDs = array_keys($ids);
            }
        }

        $bmltIdx = "";
        $worldIdx = "";
        $meetingMap = array();
        for ($i = 1; $i <= count($rows); $i++) {
            $row = $rows[$i];
            if ($i == 1) {
                foreach ($row as $key => $value) {
                    if ($value == "bmlt_id") {
                        $bmltIdx = $key;
                    } elseif ($value == "Committee") {
                        $worldIdx = $key;
                    }
                    if ($bmltIdx && $worldIdx) {
                        break;
                    }
                }

                if (!$bmltIdx || !$worldIdx) {
                    if (!$bmltIdx) {
                        $ret['errors'][] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_required_spreadsheet_column'] . "bmlt_id";
                    }
                    if (!$worldIdx) {
                        $ret['errors'][] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_required_spreadsheet_column'] . "Committee";
                    }
                    return json_encode($ret);
                }

                continue;
            }

            $bmltId = trim(strval($row[$bmltIdx]));
            $worldId = trim($row[$worldIdx]);
            if (empty($bmltId) && empty($worldId)) {
                continue;
            } elseif (!is_numeric($bmltId)) {
                $ret['errors'][] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_bmlt_id_not_integer'] . $bmltId;
            } else {
                $meetingMap[$bmltId] = $worldId;
            }
        }

        if (empty($meetingMap)) {
            $ret['errors'][] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_no_world_ids_updated'];
        }

        if (!empty($ret['errors'])) {
            return json_encode($ret);
        }

        // Attempt to save some memory, as many servers will be memory restricted
        unset($rows);
        unset($spreadsheet);
        unset($reader);

        $json_tool = new PhpJsonXmlArrayStringInterchanger;
        $used_formats = array();
        $meetings = $this->GetSearchResults(array('meeting_ids' => array_keys($meetingMap)), $used_formats);
        $meetings = $this->TranslateToJSON($meetings);
        $meetings = $json_tool->convertJsonToArray($meetings, true);
        $map = array();
        foreach ($meetings as $meeting) {
            $bmltId = strval($meeting['id_bigint']);
            $map[$bmltId] = $meeting;
        }
        $meetings = $map;

        c_comdef_dbsingleton::beginTransaction();
        try {
            foreach ($meetingMap as $bmltId => $newWorldId) {
                if (!array_key_exists($bmltId, $meetings)) {
                    $this->UpdatePossiblyDeletedMeeting($bmltId, $newWorldId, $ret, $isServerAdmin, $userServiceBodyIDs);
                    continue;
                }
                $meeting = $meetings[$bmltId];
                if (!$isServerAdmin && !in_array(intval($meeting['service_body_bigint']), $userServiceBodyIDs)) {
                    $ret['report']['problem'][] = $bmltId;
                    continue;
                }
                $oldWorldId = $meeting['worldid_mixed'];
                if ($oldWorldId == $newWorldId) {
                    $ret['report']['not_updated'][] = $bmltId;
                    continue;
                }
                $meeting['worldid_mixed'] = $newWorldId;
                $this->SetMeetingDataValues($meeting, false);
                $ret['report']['updated'][] = $bmltId;
            }
        } catch (Exception $e) {
            c_comdef_dbsingleton::rollBack();
            throw $e;
        }
        c_comdef_dbsingleton::commit();

        $ret['success'] = empty($ret['errors']);
        return json_encode($ret);
    }

    /* Helper method for HandleMeetingWorldIDsUpdate for updating deleted meetings.  These meetings exist only in the
    changes table, if at all.  The normal use for this method is to set the world_id to 'deleted', which is used by NAWS
    to indicate that they've recorded this deletion.  The NAWS export facility doesn't export meetings whose world_id is
    'deleted', so that NAWS doesn't have to keep looking at the same deleted meeting over and over.  If the world_id was
    already 'deleted' or if we're trying to set it to its current world_id, report no change.  This method doesn't try
    to be to be clever about working around problems: if something is wrong, it just reports the meeting as a problem
    meeting and doesn't make any changes.  Obscure case: it's also OK to update the world_id of a deleted meeting to a
    different world_id as well, not just to 'deleted'.  This is useful for avoiding a synchronization problem that would
    arise if a new meeting is deleted before its NAWS code can be uploaded.  (The sequence of events in that case would
    be a meeting is created with no world_id, gets picked up in a NAWS export, and then gets deleted, still with no
    world_id recorded.  Then if NAWS sees the new meeting in the export and enters it in their database, it will get a
    world_id on the next import.  This will set the world_id on the next import.  Then the next export will include it
    as deleted, since it now has a world_id, so that NAWS knows that the meeting with that world_id no longer exists.
    As a final wrinkle, you could also change the world_id of a deleted meeting that already had a world_id.
    There doesn't seem to be a use case for this, but it would not be a problem to do so.  In that case, there is a
    "feature" of the NAWS export code that causes 2 entries in the spreadsheet for the deleted meeting, one for each
    world_id under which it was deleted.  I'm going to declare that this is in fact a feature rather than a bug, since
    it's probably good if NAWS sees both world_ids in case something weird is going on.  Once it's set to 'deleted' that
    meeting will no longer appear.) */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    private function UpdatePossiblyDeletedMeeting($bmltId, $newWorldId, &$ret, $isServerAdmin, $userServiceBodyIDs)
    {
        $change_objects = c_comdef_server::GetChangesFromIDAndType('c_comdef_meeting', $bmltId);
        if ($change_objects instanceof c_comdef_changes) {
            $obj_array = $change_objects->GetChangesObjects();
            if (is_array($obj_array)) {
                // The changes are returned in reverse chronological order.  Ideally the last change would be a delete; but it seems
                // that there can be race conditions in which there are several changes with the same time stamp (but only one of these
                // will be a delete).  So get the most recent delete, rather than just $obj_array[0]
                foreach ($obj_array as $change) {
                    if ($change instanceof c_comdef_change && $change->GetChangeType() === 'comdef_change_type_delete' && !$change->GetAfterObject()) {
                        $meeting = $change->GetBeforeObject();
                        if (!$isServerAdmin && !in_array(intval($meeting->GetServiceBodyID()), $userServiceBodyIDs)) {
                            // Meeting was under some other service body that this user isn't allowed to change.
                            $ret['report']['problem'][] = $bmltId;
                        } elseif ($newWorldId === $meeting->GetWorldID()) {
                            // We aren't trying to change its world_id.
                            $ret['report']['not_updated'][] = $bmltId;
                        } else {
                            // Found a deleted meeting whose world_id should be changed (could be to 'deleted' or to another world_id).  Do that.
                            $meeting->SetWorldID($newWorldId);
                            $meeting->UpdateToDB();
                            $meeting->DeleteFromDB();
                            if (strtolower($newWorldId) == 'deleted') {
                                $ret['report']['marked'][] = $bmltId;
                            } else {
                                $ret['report']['updated'][] = $bmltId;
                            }
                        }
                        return;
                    }
                }
            }
        }
        // meeting not found in the changes table
        $ret['report']['problem'][] = $bmltId;
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleAccountChange()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $response_text = array();

        $t_user = isset($this->my_http_vars['target_user']) ? intval($this->my_http_vars['target_user']) : 0;

        if ((intval($this->my_user->GetID()) == $t_user) && isset($this->my_http_vars['account_email_value'])) {
            $this->my_user->SetEmailAddress($this->my_http_vars['account_email_value']);
            $success = $this->my_user->UpdateToDB();
            $response_text['EMAIL_CHANGED'] = ($success ? true : false);
        }

        if ((intval($this->my_user->GetID()) == $t_user) && isset($this->my_http_vars['account_description_value'])) {
            $this->my_user->SetLocalDescription($this->my_http_vars['account_description_value']);
            $success = $this->my_user->UpdateToDB();
            $response_text['DESCRIPTION_CHANGED'] = ($success ? true : false);
        }

        $login = $this->my_user->GetLogin();
        $login_changed = false;
        $password = (isset($this->my_http_vars['account_password_value']) ? $this->my_http_vars['account_password_value'] : '');

        if ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) {
            if ((intval($this->my_user->GetID()) == $t_user) && isset($this->my_http_vars['account_name_value'])) {
                $this->my_user->SetLocalName($this->my_http_vars['account_name_value']);
                $success = $this->my_user->UpdateToDB();
                $response_text['NAME_CHANGED'] = ($success ? true : false);
            }

            if ((intval($this->my_user->GetID()) == $t_user) && isset($this->my_http_vars['account_login_value'])) {
                $login = $this->my_http_vars['account_login_value'];
                $login_changed = true;
            }
        } else {
            unset($this->my_http_vars['account_login_value']);
        }

        if ((intval($this->my_user->GetID()) == $t_user) && (isset($this->my_http_vars['account_login_value']) || isset($this->my_http_vars['account_password_value']))) {
            $success = $this->my_user->UpdateToDB(false, $login, $password);
            $response_text['PASSWORD_CHANGED'] = ($success ? true : false);
            if ($login_changed) {
                $response_text['LOGIN_CHANGED'] = ($success ? true : false);
            }
        }

        if (is_array($response_text) && count($response_text)) {
            header('Content-Type:application/json; charset=UTF-8');
            echo ( array2json(array ( 'ACCOUNT_CHANGED' => $response_text )));
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleFormatChange(
        $in_new_format_data     ///< A JSON string with the new format data.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            $json_tool = new PhpJsonXmlArrayStringInterchanger;

            $the_processed_formats = $json_tool->convertJsonToArray($in_new_format_data, true);

            $the_changed_formats = array();
            foreach ($the_processed_formats as $the_format) {
                if (trim($the_format['key']) || trim($the_format['name']) || trim($the_format['description'])) {
                    $the_changed_formats[$the_format['lang_key']] = $the_format;
                }
            }

            $the_objects_to_be_changed = array();

            $ret_data = '';
            $shared_id = '';
            $format_type = 'FC1';

            // The first thing that we do, is go through the incoming data, and make sure that we create or modify c_comdef_format objects to match the input.
            foreach ($the_changed_formats as $format_data) {
                if ($format_data) {
                    foreach ($format_data as &$data_val) {  // This removes double-slashes, added by the JSON encoding.
                        $data_val = str_replace('\\\\', '\\', $data_val);
                    }

                    if (!$shared_id) {
                        $shared_id = intval($format_data['shared_id']);
                        $format_type = $format_data['type'];
                    } else {
                        if ($shared_id != intval($format_data['shared_id'])) {  // This should never happen.
                            $the_objects_to_be_changed = null;
                            break;
                        }
                    }

                    $lang_key = $format_data['lang_key'];

                    $server_format = null;

                    if ($format_data['shared_id']) {
                        $this->my_server->GetOneFormat($format_data['shared_id'], $format_data['lang_key']);
                    }
                    if (!($server_format instanceof c_comdef_format)) {
                        $parent = null;
                        $server_format = new c_comdef_format($parent, $format_data['shared_id'], $format_type, $format_data['key'], null, null, $format_data['lang_key'], $format_data['name'], $format_data['description']);
                    } else {
                        $server_format->SetKey($format_data['key']);
                        $server_format->SetLocalName($format_data['name']);
                        $server_format->SetLocalDescription($format_data['description']);
                    }

                    if (isset($format_data['worldid_mixed']) && $format_data['worldid_mixed']) {
                        $server_format->SetWorldID($format_data['worldid_mixed']);
                    }

                    array_push($the_objects_to_be_changed, $server_format);
                }
            }

            $the_changed_objects = array();

            if ($the_objects_to_be_changed && is_array($the_objects_to_be_changed) && count($the_objects_to_be_changed)) {
                $new_shared_id = 0;
                $langs = $this->my_server->GetFormatLangs();

                foreach ($the_objects_to_be_changed as $one_format) {
                    if (!(($one_format instanceof c_comdef_format) && $one_format->UpdateToDB())) {
                        $the_objects_to_be_changed = null;
                        $ret_data = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_change_fail_text']);
                        break;
                    }

                    if (!$one_format->GetSharedID()) {
                        $one_format->SetSharedID($new_shared_id);
                    }

                    $saved_format_object = array (
                                                'shared_id' => $one_format->GetSharedID(),
                                                'lang_key' => $one_format->GetLocalLang(),
                                                'lang_name' => $langs[$one_format->GetLocalLang()],
                                                'key' => $one_format->GetKey(),
                                                'name' => $one_format->GetLocalName(),
                                                'description' => $one_format->GetLocalDescription(),
                                                'type' => $one_format->GetFormatType(),
                                                'worldid_mixed' => $one_format->GetWorldID()
                                                );

                    $new_shared_id = $saved_format_object['shared_id'];

                    $the_changed_objects[$one_format->GetLocalLang()] = $saved_format_object;
                }

                // Now, we go through the server's formats, and delete any that aren't reflected in the incoming data.
                foreach ($langs as $lang_key => $lang_name) {
                    $server_format = $this->my_server->GetOneFormat($shared_id, $lang_key);

                    if ($server_format && !$the_changed_formats[$lang_key]) {
                        $server_format->DeleteFromDB();
                    }
                }
            } else {
                $ret_data = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_change_fail_text']);
            }

            header('Content-Type:application/json; charset=UTF-8');
            if ($ret_data) {
                echo "{'success':false,'report':'$ret_data'}";
            } else {
                echo "{'success':true,'report':".array2json($the_changed_objects)."}";
            }
        } else {
            echo 'NOT AUTHORIZED';
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleDeleteFormat(
        $in_format_shared_id    ///< The shared ID of the formats to delete.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            $ret_data = '';

            $langs = $this->my_server->GetFormatLangs();

            foreach ($langs as $lang_key => $lang_name) {
                $server_format = $this->my_server->GetOneFormat($in_format_shared_id, $lang_key);

                if ($server_format instanceof c_comdef_format) {
                    $server_format->DeleteFromDB();
                }
            }

            header('Content-Type:application/json; charset=UTF-8');
            if ($ret_data) {
                echo "{'success':false,'report':'$ret_data'}";
            } else {
                echo "{'success':true,'report':$in_format_shared_id}";
            }
        } else {
            echo 'NOT AUTHORIZED';
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleUserCreate(
        $in_user_data   ///< A JSON object, containing the new User data.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            $json_tool = new PhpJsonXmlArrayStringInterchanger;

            $the_new_user = $json_tool->convertJsonToArray($in_user_data, true);

            if (is_array($the_new_user) && count($the_new_user)) {
                $id = $the_new_user[0];
                $login = $the_new_user[1];
                $name = $the_new_user[2];
                $description = $the_new_user[3];
                $email = $the_new_user[4];
                $user_level = intval($the_new_user[5]);
                $password = trim($the_new_user[6]);
                $user_owner = intval($the_new_user[7]);

                $user_owner_user = $this->my_server->GetUserByIDObj($user_owner);
                if (is_null($user_owner_user) || $user_owner_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) {
                    $user_owner = -1;
                }

                if (!$this->my_server->GetUserByLogin($login)) {
                    $user_to_create = new c_comdef_user(null, 0, $user_level, $email, $login, "", $this->my_server->GetLocalLang(), $name, $description, $user_owner, null);

                    if ($user_to_create instanceof c_comdef_user) {
                        if ($password) {
                            $user_to_create->SetNewPassword($password);
                        }

                        if ($user_to_create->UpdateToDB()) {
                            // Get whatever ID was assigned to this User.
                            $the_new_user[0] = intval($user_to_create->GetID());
                            header('Content-Type:application/json; charset=UTF-8');
                            echo "{'success':true,'user':".array2json($the_new_user)."}";
                        } else {
                            $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_create_fail_text']);
                            header('Content-Type:application/json; charset=UTF-8');
                            echo "{'success':false,'report':'$err_string'}";
                        }
                    } else {
                        $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_create_fail_text']);
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':false,'report':'$err_string'}";
                    }
                } else {
                    $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_create_fail_already_exists']);
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{'success':false,'report':'$err_string'}";
                }
            } else {
                $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_create_fail_text']);
                header('Content-Type:application/json; charset=UTF-8');
                echo "{'success':false,'report':'$err_string'}";
            }
        } else {
            echo 'NOT AUTHORIZED';
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleUserChange(
        $in_user_data   ///< A JSON object, containing the new User data.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $isServerAdmin = c_comdef_server::IsUserServerAdmin(null, true);
        $isServiceBodyAdmin = c_comdef_server::IsUserServiceBodyAdmin(null, true);
        if ($isServerAdmin || $isServiceBodyAdmin) {
            $json_tool = new PhpJsonXmlArrayStringInterchanger;

            $the_changed_user = $json_tool->convertJsonToArray($in_user_data, true);

            if (is_array($the_changed_user) && count($the_changed_user)) {
                $id = $the_changed_user[0];
                $login = $the_changed_user[1];
                $name = $the_changed_user[2];
                $description = $the_changed_user[3];
                $email = $the_changed_user[4];
                $user_level = intval($the_changed_user[5]);
                $password = trim($the_changed_user[6]);
                $user_owner = intval($the_changed_user[7]);
                $user_to_change = $this->my_server->GetUserByIDObj($id);

                $user_owner_user = $this->my_server->GetUserByIDObj($user_owner);
                if (is_null($user_owner_user) || $user_owner_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) {
                    $user_owner = -1;
                }

                if ($user_to_change instanceof c_comdef_user) {
                    // Don't allow service body admins to make changes to users they don't own
                    if ($isServiceBodyAdmin && $user_to_change->GetOwnerID() != c_comdef_server::GetCurrentUserObj()->GetID()) {
                        echo 'NOT AUTHORIZED';
                        return;
                    }

                    $user_to_change->SetLogin($login);
                    $user_to_change->SetLocalName($name);
                    $user_to_change->SetLocalDescription($description);
                    $user_to_change->SetEmailAddress($email);
                    // Only allow server admins to set user level and user owner
                    if ($isServerAdmin) {
                        $user_to_change->SetUserLevel($user_level);
                        $user_to_change->SetOwnerID($user_owner);
                    }

                    if ($password) {
                        if (!$user_to_change->SetNewPassword($password)) {
                            $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_fail_cant_update_text']);
                            header('Content-Type:application/json; charset=UTF-8');
                            echo "{\"success\":false,\"report\":\"$err_string\"}";
                            return;
                        }
                    }

                    if ($user_to_change->UpdateToDB()) {
                        header('Content-Type:application/json; charset=UTF-8');
                        echo '{"success":true,"user":'.array2json($the_changed_user)."}";
                    } else {
                        $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_fail_cant_update_text']);
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{\"success\":false,\"report\":\"$err_string\"}";
                    }
                } else {
                    $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_fail_cant_find_sb_text']);
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{\"success\":false,\"report\":\"$err_string\"}";
                }
            } else {
                $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_fail_no_data_text']);
                header('Content-Type:application/json; charset=UTF-8');
                echo "{\"success\":false,\"report\":\"$err_string\"}";
            }
        } else {
            echo 'NOT AUTHORIZED';
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleDeleteUser(
        $in_user_id,    ///< The ID of the user to be deleted.
        $in_delete_permanently = false
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_delete_fail_text']);
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            try {
                $user_to_delete = $this->my_server->GetUserByIDObj($in_user_id);

                if ($user_to_delete instanceof c_comdef_user) {
                    if ($user_to_delete->DeleteFromDB()) {
                        $user_to_delete->ResetChildUsers();
                        if ($in_delete_permanently) {
                            $this->DeleteUserChanges($in_user_id);
                        }

                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':true,'report':'$in_user_id'}";
                    } else {
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':false,'report':'$ierr_string'}";
                    }
                } else {
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{'success':false,'report':'$ierr_string'}";
                }
            } catch (Exception $e) {
                header('Content-Type:application/json; charset=UTF-8');
                echo "{'success':false,'report':'$ierr_string'}";
            }
        } else {
            echo 'NOT AUTHORIZED';
        }
    }

    /*******************************************************************/
    /**
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function DeleteUserChanges($in_user_id)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            $changes = $this->my_server->GetChangesFromIDAndType('c_comdef_user', $in_user_id);

            if ($changes instanceof c_comdef_changes) {
                $obj_array = $changes->GetChangesObjects();

                if (is_array($obj_array) && count($obj_array)) {
                    foreach ($obj_array as $change) {
                        $change->DeleteFromDB();
                    }
                }
            }
        }
    }

    /*******************************************************************/
    /**
        \brief  This handles updating an existing Service body.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleServiceBodyChange(
        $in_service_body_data    ///< A JSON object, containing the new Service Body data.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $json_tool = new PhpJsonXmlArrayStringInterchanger;

        $the_new_service_body = $json_tool->convertJsonToArray($in_service_body_data, true);

        if (is_array($the_new_service_body) && count($the_new_service_body)) {
            $id = $the_new_service_body[0];
            $parent_service_body_id = $the_new_service_body[1];
            $name = $the_new_service_body[2];
            $description = $the_new_service_body[3];
            $main_user_id = $the_new_service_body[4];
            $editor_ids = explode(',', $the_new_service_body[5]);
            $email = $the_new_service_body[6];
            $uri = $the_new_service_body[7];
            $helpline = $the_new_service_body[8];
            $type = $the_new_service_body[9];
            $worldid = $the_new_service_body[12];

            $sb_to_change = $this->my_server->GetServiceBodyByIDObj($id);

            if ($sb_to_change instanceof c_comdef_service_body) {
                $sb_to_change->SetOwnerID($parent_service_body_id);
                $sb_to_change->SetLocalName($name);
                $description = preg_replace('|[^\S]+?|', " ", $description);
                $sb_to_change->SetLocalDescription($description);
                $sb_to_change->SetPrincipalUserID($main_user_id);
                $sb_to_change->SetEditors($editor_ids);
                $sb_to_change->SetContactEmail($email);
                $sb_to_change->SetURI($uri);
                $sb_to_change->SetHelpline($helpline);
                $sb_to_change->SetSBType($type);
                $sb_to_change->SetWorldID($worldid);

                if ($sb_to_change->UpdateToDB()) {
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{'success':true,'service_body':".array2json($the_new_service_body)."}";
                } else {
                    $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_cant_update_text']);
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{'success':false,'report':'$err_string'}";
                }
            } else {
                $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_cant_find_sb_text']);
                header('Content-Type:application/json; charset=UTF-8');
                echo "{'success':false,'report':'$err_string'}";
            }
        } else {
            $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_no_data_text']);
            header('Content-Type:application/json; charset=UTF-8');
            echo "{'success':false,'report':'$err_string'}";
        }
    }

    /*******************************************************************/
    /**
        \brief  This handles creating a new Service body.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleServiceBodyCreate(
        $in_service_body_data    ///< A JSON object, containing the new Service Body data.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            $json_tool = new PhpJsonXmlArrayStringInterchanger;

            $the_new_service_body = $json_tool->convertJsonToArray($in_service_body_data, true);

            if (is_array($the_new_service_body) && count($the_new_service_body)) {
                $id = $the_new_service_body[0];
                $parent_service_body_id = $the_new_service_body[1];
                $name = $the_new_service_body[2];
                $description = $the_new_service_body[3];
                $main_user_id = $the_new_service_body[4];
                $editor_ids = explode(',', $the_new_service_body[5]);
                $email = $the_new_service_body[6];
                $uri = $the_new_service_body[7];
                $helpline = $the_new_service_body[8];
                $type = $the_new_service_body[9];
                $worldid = $the_new_service_body[12];

                $sb_to_create = new c_comdef_service_body;

                if ($sb_to_create instanceof c_comdef_service_body) {
                    $sb_to_create->SetOwnerID($parent_service_body_id);
                    $sb_to_create->SetLocalName($name);
                    $sb_to_create->SetLocalDescription($description);
                    $sb_to_create->SetPrincipalUserID($main_user_id);
                    $sb_to_create->SetEditors($editor_ids);
                    $sb_to_create->SetContactEmail($email);
                    $sb_to_create->SetURI($uri);
                    $sb_to_create->SetHelpline($helpline);
                    $sb_to_create->SetSBType($type);
                    $sb_to_create->SetWorldID($worldid);

                    if ($sb_to_create->UpdateToDB()) {
                        // Get whatever ID was assigned to this Service Body.
                        $the_new_service_body[0] = $sb_to_create->GetID();
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':true,'service_body':".array2json($the_new_service_body)."}";
                    } else {
                        $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_cant_update_text']);
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':false,'report':'$err_string'}";
                    }
                } else {
                    $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_cant_find_sb_text']);
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{'success':false,'report':'$err_string'}";
                }
            } else {
                $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_no_data_text']);
                header('Content-Type:application/json; charset=UTF-8');
                echo "{'success':false,'report':'$err_string'}";
            }
        } else {
            echo 'NOT AUTHORIZED';
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleDeleteServiceBody(
        $in_sb_id,
        $in_delete_permanently = false
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_delete_fail_text']);

        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            try {
                $service_body = $this->my_server->GetServiceBodyByIDObj($in_sb_id);

                if ($service_body instanceof c_comdef_service_body) {
                    if ($service_body->DeleteFromDB()) {
                        if ($in_delete_permanently) {
                            $this->DeleteServiceBodyChanges($in_sb_id);
                        }

                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':true, 'id':$in_sb_id}";
                    } else {
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':false,'report':'$err_string'}";
                    }
                } else {
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{'success':false,'report':'$err_string'}";
                }
            } catch (Exception $e) {
                header('Content-Type:application/json; charset=UTF-8');
                echo "{'success':false,'report':'$err_string'}";
            }
        } else {
            echo 'NOT AUTHORIZED';
        }
    }

    /*******************************************************************/
    /**
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function DeleteServiceBodyChanges($in_sb_id)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            $changes = $this->my_server->GetChangesFromIDAndType('c_comdef_service_body', $in_sb_id);

            if ($changes instanceof c_comdef_changes) {
                $obj_array = $changes->GetChangesObjects();

                if (is_array($obj_array) && count($obj_array)) {
                    foreach ($obj_array as $change) {
                        $change->DeleteFromDB();
                    }
                }
            }
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetMeetingHistory($in_meeting_id)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

        $ret = [];
        $changes = $this->my_server->GetChangesFromIDAndType('c_comdef_meeting', $in_meeting_id);

        if ($changes instanceof c_comdef_changes) {
            $obj_array = $changes->GetChangesObjects();

            if (is_array($obj_array) && count($obj_array)) {
                $index = 0;
                foreach ($obj_array as $change) {
                    $ret[$index]['id'] = intval($change->GetID());
                    $ret[$index]['user'] = $this->my_server->GetUserByIDObj($change->GetUserID())->GetLocalName();
                    $ret[$index]['description'] = $change->DetailedChangeDescription()['details'];
                    $ret[$index]['date'] = date('g:i A, F j Y', $change->GetChangeDate());
                    $index++;
                }
            }
        }

        return json_encode($ret);
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleDeleteMeeting(
        $in_meeting_id
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        try {
            $meeting = $this->my_server->GetOneMeeting($in_meeting_id);

            if ($meeting instanceof c_comdef_meeting) {
                if ($meeting->UserCanEdit()) {
                    if ($meeting->DeleteFromDB()) {
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':true,'report':'$in_meeting_id'}";
                    } else {
                        header('Content-Type:application/json; charset=UTF-8');
                        echo "{'success':false,'report':'$in_meeting_id'}";
                    }
                } else {
                    header('Content-Type:application/json; charset=UTF-8');
                    echo "{'success':false,'report':'$in_meeting_id'}";
                }
            } else {
                header('Content-Type:application/json; charset=UTF-8');
                echo "{'success':false,'report':'$in_meeting_id'}";
            }
        } catch (Exception $e) {
            header('Content-Type:application/json; charset=UTF-8');
            echo "{'success':false,'report':'$in_meeting_id'}";
        }
    }

    /*******************************************************************/
    /**
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function DeleteMeetingChanges($in_meeting_id)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (c_comdef_server::IsUserServerAdmin(null, true)) {
            $changes = $this->my_server->GetChangesFromIDAndType('c_comdef_meeting', $in_meeting_id);

            if ($changes instanceof c_comdef_changes) {
                $obj_array = $changes->GetChangesObjects();

                if (is_array($obj_array) && count($obj_array)) {
                    foreach ($obj_array as $change) {
                        $change->DeleteFromDB();
                    }
                }
            }
        }
    }

    /*******************************************************************/
    /**
        \brief  This handles updating an existing meeting, or adding a new one.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function HandleMeetingUpdate(
        $in_meeting_data    ///< A JSON object, containing the new meeting data.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $json_tool = new PhpJsonXmlArrayStringInterchanger;

        $the_new_meeting = $json_tool->convertJsonToArray($in_meeting_data, true);

        if (is_array($the_new_meeting) && count($the_new_meeting)) {
            c_comdef_dbsingleton::beginTransaction();
            try {
                $this->SetMeetingDataValues($the_new_meeting);
            } catch (Exception $e) {
                c_comdef_dbsingleton::rollback();
                throw $e;
            }
            c_comdef_dbsingleton::commit();
        }
    }

    /*******************************************************************/
    /**
        \brief
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SetMeetingDataValues($in_meeting_data, $print_result = true)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        try {
            if ($in_meeting_data['id_bigint'] ?? false) {
                $meeting = $this->my_server->GetOneMeeting($in_meeting_data['id_bigint']);
            } else {
                $data = array ( 'service_body_bigint' => intval($in_meeting_data['service_body_bigint']),
                                'weekday_tinyint' => intval($in_meeting_data['weekday_tinyint']),
                                'start_time' => $in_meeting_data['start_time'],
                                'lang_enum' => (isset($in_meeting_data['lang_enum']) && $in_meeting_data['lang_enum']) ? $in_meeting_data['lang_enum'] : $this->my_server->GetLocalLang()
                                );
                $meeting = new c_comdef_meeting($this->my_server, $data);
            }

            if ($meeting instanceof c_comdef_meeting) {
                // Security precaution: We check the session to make sure that the user is authorized for this meeting.
                if ($meeting->UserCanEdit()) {
                    if (array_key_exists('id_bigint', $in_meeting_data)) {
                        $result_data = array ( 'meeting_id' => $in_meeting_data['id_bigint'] );
                    } else {
                        $result_data = [];
                    }
                    $data =& $meeting->GetMeetingData();

                    // We prepare the "template" array. These are the data values for meeting 0 in the two tables.
                    // We will use them to provide default visibility values. Only the server admin can override these.
                    // This is where we get a list of the available "optional" fields to put in a popup for adding a new one.
                    $template_data = c_comdef_meeting::GetDataTableTemplate();
                    $template_longdata = c_comdef_meeting::GetLongDataTableTemplate();

                    // We merge the two tables (data and longdata).
                    if (is_array($template_data) && count($template_data) && is_array($template_longdata) && count($template_longdata)) {
                        $template_data = array_merge($template_data, $template_longdata);
                    }

                    foreach ($in_meeting_data as $key => $value) {
                        if ($key == 'formats') {
                            continue;
                        }

                        if ($key == 'format_shared_id_list') {
                            $vals = array();
                            $value = explode(",", $value);
                            $lang = $this->my_server->GetLocalLang();
                            foreach ($value as $sharedID) {
                                $sharedID = intval($sharedID);
                                $object = c_comdef_server::GetServer()->GetFormatsObj()->GetFormatBySharedIDCodeAndLanguage($sharedID, $lang);
                                if ($object) {
                                    $vals[$sharedID] = $object;
                                }
                            }
                            uksort($vals, array ( 'c_comdef_meeting','format_sorter_simple' ));
                            $value = $vals;
                            $key = 'formats';
                        }

                        switch ($key) {
                            case 'zoom':
                            case 'distance_in_km':       // These are ignored.
                            case 'distance_in_miles':
                                break;

                            // These are the "fixed" or "core" data values.
                            case 'worldid_mixed':
                            case 'start_time':
                            case 'lang_enum':
                            case 'duration_time':
                            case 'time_zone':
                                $data[$key] = $value;
                                break;

                            case 'formats':
                                $data[$key] = $value;
                                $data["venue_type"] = $this->GetVenueTypeForFormats($value);
                                break;

                            case 'longitude':
                            case 'latitude':
                                $data[$key] = floatval($value);
                                break;

                            case 'id_bigint':
                            case 'service_body_bigint':
                            case 'weekday_tinyint':
                                $data[$key] = intval($value);
                                break;

                            case 'email_contact':
                                $value = trim($value);
                                if ($value) {
                                    if (c_comdef_vet_email_address($value)) {
                                        $data[$key] = $value;
                                    } else {
                                        $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['email_format_bad']);
                                        header('Content-Type:application/json; charset=UTF-8');
                                        die("{'error':true,'type':'email_format_bad','report':'$err_string','id':'".$in_meeting_data['id_bigint']."'}");
                                    }
                                } else {
                                    $data[$key] = $value;
                                }
                                break;

                            // We only accept a 1 or a 0.
                            case 'published':
                                // Meeting list editors can't publish meetings.
                                if (c_comdef_server::GetCurrentUserObj(true)->GetUserLevel() != _USER_LEVEL_EDITOR) {
                                    $data[$key] = $value ? 1 : 0;
                                }
                                break;

                            case 'root_server_uri':
                            case 'venue_type':
                                break;  // This should just be a calculated field, so don't save it

                            // These are the various "optional" fields.
                            default:
                                if (isset($data[$key])) {
                                    $data[$key]['meetingid_bigint'] = $in_meeting_data['id_bigint'];
                                    $data[$key]['value'] = $value;
                                } else {
                                    $template_field_prompt = array_key_exists($key, $template_data) ? $template_data[$key]['field_prompt'] : null;
                                    $template_visibility = array_key_exists($key, $template_data) ? $template_data[$key]['visibility'] : null;
                                    $result_data['new_data']['key'] = $key;
                                    $result_data['new_data']['field_prompt'] = $template_field_prompt;
                                    $result_data['new_data']['value'] = $value;
                                    $meeting->AddDataField($key, $template_field_prompt, $value, null, intval($template_visibility));
                                }
                                break;
                        }
                    }
                    if ($meeting->UpdateToDB()) {
                        $used_formats = array();
                        $result = $this->TranslateToJSON($this->GetSearchResults(array ( 'meeting_ids' => array ( $meeting->GetID() ) ), $used_formats));
                        if ($print_result) {
                            header('Content-Type:application/json; charset=UTF-8');
                            echo $result;
                        } else {
                            return $result;
                        }
                    } else {
                        $in_meeting_data['id_bigint'] = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id']).$in_meeting_data['id_bigint'];
                        $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_auth_failure']);
                        $result = "{'error':true,'type':'auth_failure','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                        if ($print_result) {
                            header('Content-Type:application/json; charset=UTF-8');
                            echo $result;
                        } else {
                            return $result;
                        }
                    }
                } else {
                    $in_meeting_data['id_bigint'] = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id']).$in_meeting_data['id_bigint'];
                    $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_auth_failure']);
                    $result = "{'error':true,'type':'auth_failure','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                    if ($print_result) {
                        header('Content-Type:application/json; charset=UTF-8');
                        echo $result;
                    } else {
                        return $result;
                    }
                }
            } else {
                $in_meeting_data['id_bigint'] = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id']).$in_meeting_data['id_bigint'];
                $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_object_not_found']);
                $result = "{'error':true,'type':'object_not_found','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                if ($print_result) {
                    header('Content-Type:application/json; charset=UTF-8');
                    echo $result;
                } else {
                    return $result;
                }
            }
        } catch (Exception $e) {
            $in_meeting_data['id_bigint'] = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id']).$in_meeting_data['id_bigint'];
            $err_string = json_prepare($this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_object_not_changed']);
            $result = "{'error':true,'type':'object_not_changed','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
            if ($print_result) {
                header('Content-Type:application/json; charset=UTF-8');
                echo $result;
            } else {
                return $result;
            }
        }
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    private function GetVenueTypeForFormats($formatsArray)
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        // Get the format ID for each of the venue type formats
        $vmFormatId = 0;
        $hyFormatId = 0;
        $tcFormatId = 0;
        foreach ($this->my_server->GetFormatsArray()["en"] as $format) {
            if ($format->GetKey() == "VM") {
                $vmFormatId = $format->GetSharedID();
            } else if ($format->GetKey() == "HY") {
                $hyFormatId = $format->GetSharedID();
            } else if ($format->GetKey() == "TC") {
                $tcFormatId = $format->GetSharedID();
            }
            if ($vmFormatId && $hyFormatId && $tcFormatId) {
                break;
            }
        }

        // if (!$vmFormatId || !$hyFormatId || !$tcFormatId) {
            // I think this can only happen if someone manually messed with their database, and
            // I am not really sure what we should do in this situation.
        //}

        // Set helper bools indicating whether or not the formats array has these formats
        $hasVM = false;
        $hasHY = false;
        $hasTC = false;
        foreach ($formatsArray as $format) {
            if ($format->GetSharedID() == $vmFormatId) {
                $hasVM = true;
            } else if ($format->GetSharedID() == $hyFormatId) {
                $hasHY = true;
            } else if ($format->GetSharedID() == $tcFormatId) {
                $hasTC = true;
            }
            if ($hasVM && $hasHY && $hasTC) {
                break;
            }
        }

        // This logic is copied from setFormatCheckboxes in server_admin_javascript.js
        if (!$hasVM && !$hasTC && $hasHY) {
            return VenueType::HYBRID;
        } else if ($hasVM && $hasTC && !$hasHY) {
            // This is Virtual TC in the UI, but that doesn't seem like a useful designation for filtering,
            // so we are just going to set it to virtual. I suspect virtual TC as a venue type will go away
            // eventually.
            return VenueType::VIRTUAL;
        } else if ($hasVM && !$hasTC && !$hasHY) {
            return VenueType::VIRTUAL;
        } else {
            return VenueType::IN_PERSON;
        }
    }

    /*******************************************************************/
    /**
        \brief  This returns the search results, in whatever form was requested.

        \returns CSV data, with the first row a key header.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetSearchResults(
        $in_http_vars,  ///< The HTTP GET and POST parameters.
        &$formats_ar    ///< This will return the formats used in this search.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        if (!( isset($in_http_vars['geo_width']) && $in_http_vars['geo_width'] ) && isset($in_http_vars['bmlt_search_type']) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset($in_http_vars['advanced_radius']) && isset($in_http_vars['advanced_mapmode']) && $in_http_vars['advanced_mapmode'] && ( floatval($in_http_vars['advanced_radius'] != 0.0) ) && isset($in_http_vars['lat_val']) &&  isset($in_http_vars['long_val']) && ( (floatval($in_http_vars['lat_val']) != 0.0) || (floatval($in_http_vars['long_val']) != 0.0) )) {
            $in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
        } elseif (!( isset($in_http_vars['geo_width']) && $in_http_vars['geo_width'] ) && isset($in_http_vars['bmlt_search_type']) && ($in_http_vars['bmlt_search_type'] == 'advanced')) {
            $in_http_vars['lat_val'] = null;
            $in_http_vars['long_val'] = null;
        } elseif (!isset($in_http_vars['geo_loc']) || $in_http_vars['geo_loc'] != 'yes') {
            if (!isset($in_http_vars['geo_width'])) {
                $in_http_vars['geo_width'] = 0;
            }
        }

        $geocode_results = null;
        $ignore_me = null;
        $meeting_objects = array();

        $result = DisplaySearchResultsCSV($in_http_vars, $ignore_me, $geocode_results, $meeting_objects, true, true);

        if (isset($meeting_objects) &&  is_array($meeting_objects) && count($meeting_objects) && isset($formats_ar) && is_array($formats_ar)) {
            foreach ($meeting_objects as $one_meeting) {
                $formats = $one_meeting->GetMeetingDataValue('formats');

                foreach ($formats as $format) {
                    if ($format && ($format instanceof c_comdef_format)) {
                        $format_shared_id = $format->GetSharedID();
                        $formats_ar[$format_shared_id] = $format;
                    }
                }
            }
        }

        if (isset($in_http_vars['data_field_key']) && $in_http_vars['data_field_key']) {
            // At this point, we have everything in a CSV. We separate out just the field we want.
            $temp_keyed_array = array();
            $result = explode("\n", $result);
            $keys = array_shift($result);
            $keys = explode("\",\"", trim($keys, '"'));
            $the_keys = explode(',', $in_http_vars['data_field_key']);

            $result2 = array();
            foreach ($result as $row) {
                if ($row) {
                    $index = 0;
                    $row = explode('","', trim($row, '",'));
                    $row_columns = array();
                    foreach ($row as $column) {
                        if (isset($column)) {
                            if (in_array($keys[$index++], $the_keys)) {
                                array_push($row_columns, $column);
                            }
                        }
                    }
                    $result2[$row[0]] = '"'.implode('","', $row_columns).'"';
                }
            }

            $the_keys = array_intersect($keys, $the_keys);
            $result = '"'.implode('","', $the_keys)."\"\n".implode("\n", $result2);
        }

        return $result;
    }

    /*******************************************************************/
    /**
        \brief Translates CSV to JSON.

        \returns a JSON string, with all the data in the CSV.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function TranslateToJSON(
        $in_csv_data ///< An array of CSV data, with the first element being the field names.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $temp_keyed_array = array();
        $in_csv_data = explode("\n", $in_csv_data);
        $keys = array_shift($in_csv_data);
        $keys = explode("\",\"", trim($keys, '"'));

        foreach ($in_csv_data as $row) {
            if ($row) {
                $line = null;
                $index = 0;
                $row = trim($row);
                if (substr($row, 0, 1) == '"') { // Strip first double quote
                    $row = substr($row, 1, strlen($row) - 1);
                }
                if (substr($row, strlen($row) - 1, 1) == ',') { // Strip last comma, just in case
                        $row = substr($row, 0, strlen($row) - 1);
                }
                if (substr($row, strlen($row) - 1, 1) == '"') { // Strip last double quote
                    $row = substr($row, 0, strlen($row) - 1);
                }
                $row = explode('","', $row);
                foreach ($row as $column) {
                    if (isset($column)) {
                        $line[$keys[$index++]] = $column;
                    }
                }
                array_push($temp_keyed_array, $line);
            }
        }

        $out_json_data = array2json($temp_keyed_array);

        return $out_json_data;
    }
}

$handler = new c_comdef_admin_ajax_handler($http_vars);

$ret = 'ERROR';

if ($handler instanceof c_comdef_admin_ajax_handler) {
    $ret = $handler->parse_ajax_call();
}

echo $ret;
