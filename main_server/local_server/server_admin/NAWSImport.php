<?php

defined('BMLT_EXEC') or die('Cannot Execute Directly');

require_once(__DIR__ . '/../../vendor/autoload.php');

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class NAWSImport
{
    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
    private $nawsExportRows = null;
    private $expectedColumns = array(
        'delete', 'parentname', 'committee', 'committeename', 'arearegion', 'day', 'time', 'place',
        'address', 'city', 'locborough', 'state', 'zip', 'country', 'directions', 'closed', 'wheelchr',
        'format1', 'format2', 'format3', 'format4', 'format5', 'longitude', 'latitude', 'room'
    );
    private $server = null;
    private $areas = array();
    private $deleteIndex = null;
    private $areaNameIndex = null;
    private $areaWorldIdIndex = null;
    private $columnNames = null;
    private $defaultDurationTime = null;

    public function __construct($importFilePath, $defaultDurationTime)
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($importFilePath);
            $spreadsheet = $reader->load($importFilePath);
            $this->nawsExportRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // If the last row is all nulls, remove it
            $lastRow = $this->nawsExportRows[count($this->nawsExportRows)];
            $allNulls = true;
            foreach ($lastRow as $columnValue) {
                if ($columnValue != null) {
                    $allNulls = false;
                    break;
                }
            }
            if ($allNulls) {
                array_pop($this->nawsExportRows);
            }

            $this->columnNames = $this->nawsExportRows[1];
            $this->deleteIndex = array_search('delete', $this->columnNames);
            $this->areaNameIndex = array_search('parentname', $this->columnNames);
            $this->areaWorldIdIndex = array_search('arearegion', $this->columnNames);
        } catch (Exception $e) {
            // TODO throw an appropriate exception
            //$response['importReport'] = $this->my_localized_strings['comdef_server_admin_strings']['server_admin_error_could_not_create_reader'] . $e->getMessage();
            //throw new Exception();
        }

        $this->validateRequiredColumnsExist();
        $this->defaultDurationTime = $defaultDurationTime;
    }

    private function validateRequiredColumnsExist()
    {
        $actualColumnNames = array();
        $missingValues = array();
        foreach ($this->nawsExportRows[1] as $key => $value) {
            if ($value) {
                $this->nawsExportRows[1][$key] = strtolower($value);
            }
        }
        foreach ($this->expectedColumns as $expectedColumnName) {
            $idx = array_search($expectedColumnName, $this->nawsExportRows[1]);
            if (is_bool($idx)) {
                array_push($missingValues, $expectedColumnName);
            } else {
                $actualColumnNames[$idx] = $expectedColumnName;
            }
        }

        if (count($missingValues) > 0) {
            // TODO throw an appropriate exception
            //$response['importReport'] = 'NAWS export is missing required columns: ' . implode(', ', $missingValues);
            //echo array2json($response);
            //ob_end_flush();
            //die();
        }
    }

    public function import()
    {
        set_time_limit(1200); // 20 minutes
        require_once(__DIR__ . '/../../server/c_comdef_server.class.php');
        require_once(__DIR__ . '/../../server/classes/c_comdef_meeting.class.php');
        require_once(__DIR__ . '/../../server/classes/c_comdef_service_body.class.php');
        require_once(__DIR__ . '/../../server/classes/c_comdef_user.class.php');
        require_once(__DIR__ . '/c_comdef_admin_ajax_handler.class.php');
        $this->server = c_comdef_server::MakeServer();
        // TODO do all of this in a transaction
        $this->createServiceBodiesAndUsers();
        $this->createMeetings();
    }

    private function createServiceBodiesAndUsers()
    {
        // Create the service bodies
        $columnNames = null;
        for ($i = 1; $i <= count($this->nawsExportRows); $i++) {
            $row = $this->nawsExportRows[$i];

            if ($i == 1) {
                continue;
            }

            if ($row[$this->deleteIndex] == 'D') {
                continue;
            }

            $areaName = trim($row[$this->areaNameIndex]);
            $areaWorldId = trim($row[$this->areaWorldIdIndex]);
            if (!$areaName) {
                continue;
            }
            $this->areas[$areaWorldId] = $areaName;
        }

        foreach ($this->areas as $areaWorldId => $areaName) {
            $userName = preg_replace("/[^A-Za-z0-9]/", '', $areaName);
            $user = new c_comdef_user(
                null,
                0,
                _USER_LEVEL_SERVICE_BODY_ADMIN,
                '',
                $userName,
                '',
                $this->server->GetLocalLang(),
                $userName,
                'User automatically created for ' . $areaName,
                1
            );
            $user->SetPassword(generateRandomString(30));
            $user->UpdateToDB();

            $serviceBody = new c_comdef_service_body;
            $serviceBody->SetLocalName($areaName);
            $serviceBody->SetWorldID($areaWorldId);
            $serviceBody->SetLocalDescription($areaName);
            $serviceBody->SetPrincipalUserID($user->GetID());
            $serviceBody->SetEditors(array($user->GetID()));
            if (substr($areaWorldId, 0, 2) == 'AR') {
                $serviceBody->SetSBType(c_comdef_service_body__ASC__);
            } else {
                $serviceBody->SetSBType(c_comdef_service_body__RSC__);
            }
            $serviceBody->UpdateToDB();
            $this->areas[$areaWorldId] = $serviceBody;
        }

        reset($this->nawsExportRows);
    }

    private function createMeetings()
    {
        $formats = array();
        foreach ($this->server->GetFormatsObj()->GetFormatsArray()['en'] as $format) {
            if ($format instanceof c_comdef_format) {
                $world_id = $format->GetWorldID();
                $shared_id = $format->GetSharedID();
                if ($world_id && $shared_id) {
                    if (is_array($formats) && array_key_exists($world_id, $formats)) {
                        array_push($formats[$world_id], $shared_id);
                    } else {
                        $formats[$world_id] = array($shared_id);
                    }
                }
            }
        }

        $ajaxHandler = new c_comdef_admin_ajax_handler(null);
        $nawsDays = array(null, 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        // State is not a required column, because it is not always filled out for foreign countries
        $requiredColumns = array('committeename', 'arearegion', 'day', 'time', 'address', 'city');
        for ($i = 1; $i <= count($this->nawsExportRows); $i++) {
            $row = $this->nawsExportRows[$i];
            if ($i == 1) {
                continue;
            }

            if ($row[$this->deleteIndex] == 'D') {
                continue;
            }

            $meetingData = array();
            $meetingData['published'] = true;
            $meetingData['lang_enum'] = $this->server->GetLocalLang();
            $meetingData['duration_time'] = $this->defaultDurationTime;
            $meetingData['format_shared_id_list'] = array();
            $skipMeeting = false;
            foreach ($this->columnNames as $columnIndex => $columnName) {
                $value = trim($row[$columnIndex]);

                // NAWS exports sometimes contain deleted meetings, and will have empty cells
                // for those meetings. Just skip them.
                if (!is_bool(array_search($columnName, $requiredColumns)) && !$value) {
                    $skipMeeting = true;
                    break;
                }

                switch ($columnName) {
                    case 'committee':
                        $meetingData['worldid_mixed'] = $value;
                        break;
                    case 'committeename':
                        $meetingData['meeting_name'] = $value;
                        break;
                    case 'arearegion':
                        $meetingData['service_body_bigint'] = $this->areas[$row[$this->areaWorldIdIndex]]->GetID();
                        break;
                    case 'day':
                        $value = strtolower($value);
                        $value = array_search($value, $nawsDays);
                        if ($value == false) {
                            // TODO throw an appropriate exception
                            //$response['importReport'] = 'Invalid value in column \'' . $columnName . '\'';
                            //throw new Exception();
                        }
                        $meetingData['weekday_tinyint'] = $value;
                        break;
                    case 'time':
                        $time = abs(intval($value));
                        $hours = min(23, $time / 100);
                        $minutes = min(59, ($time - (intval($time / 100) * 100)));
                        $meetingData['start_time'] = sprintf("%d:%02d:00", $hours, $minutes);
                        break;
                    case 'place':
                        $meetingData['location_text'] = $value;
                        break;
                    case 'address':
                        $meetingData['location_street'] = $value;
                        break;
                    case 'city':
                        $meetingData['location_municipality'] = $value;
                        break;
                    case 'locborough':
                        $meetingData['location_neighborhood'] = $value;
                        break;
                    case 'state':
                        $meetingData['location_province'] = $value;
                        break;
                    case 'zip':
                        $meetingData['location_postal_code_1'] = $value;
                        break;
                    case 'country':
                        $meetingData['location_nation'] = $value;
                        break;
                    case 'room':
                    case 'directions':
                        if ($meetingData['location_info']) {
                            if ($value) {
                                if ($columnName == 'directions') {
                                    $meetingData['location_info'] .= ', ' . $value;
                                } else {
                                    $meetingData['location_info'] = $value . ', ' . $meetingData['location_info'];
                                }
                            }
                        } else {
                            $meetingData['location_info'] = $value;
                        }
                        break;
                    case 'wheelchr':
                        $value = strtolower($value);
                        if ($value == 'true' || $value == '1') {
                            $value = $formats['WCHR'];
                            if ($value) {
                                $meetingData['format_shared_id_list'] = array_merge($meetingData['format_shared_id_list'], $value);
                            }
                        }
                        break;
                    case 'closed':
                    case 'format1':
                    case 'format2':
                    case 'format3':
                    case 'format4':
                    case 'format5':
                        $value = $formats[$value];
                        if ($value) {
                            $meetingData['format_shared_id_list'] = array_merge($meetingData['format_shared_id_list'], $value);
                        }
                        break;
                    case 'longitude':
                        $meetingData['longitude'] = $value;
                        break;
                    case 'latitude':
                        $meetingData['latitude'] = $value;
                        break;
                    case 'unpublished':
                        if ($value == '1') {
                            $meetingData['published'] = false;
                        }
                        break;
                }
            }

            if ($skipMeeting) {
                continue;
            }

            $meetingData['format_shared_id_list'] = implode(',', $meetingData['format_shared_id_list']);
            $ajaxHandler->SetMeetingDataValues($meetingData, false);
        }
    }
}
