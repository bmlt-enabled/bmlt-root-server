<?php
/***********************************************************************/
/**     \file   client_interface/csv/index.php

    \brief  This file is a very simple interface that is designed to return
    a basic CSV (Comma-Separated Values) string, in response to a search.
    In order to use this, you need to call: <ROOT SERVER BASE URI>/client_interface/csv/
    with the same parameters that you would send to an advanced search. The results
    will be returned as a CSV file.

    This file can be called from other servers.

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

require_once(dirname(__FILE__).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once(dirname(__FILE__).'/../../server/c_comdef_server.class.php');
require_once(dirname(__FILE__).'/../../server/shared/Array2Json.php');
require_once(dirname(__FILE__).'/../../server/shared/Array2XML.php');

/*******************************************************************/
/**
    \brief Queries the local server, and returns processed CSV data

    This requires that the "switcher=" parameter be set in the GET or
    POST parameters:
        - 'GetSearchResults'
            This returns the search results.

    \returns CSV data, with the first row a key header.
*/
function parse_redirect(
    &$server    ///< A reference to an instance of c_comdef_server
) {
    $result = null;
    $http_vars = array_merge_recursive($_GET, $_POST);
    
    $port = $_SERVER['SERVER_PORT'] ;
    // IIS puts "off" in the HTTPS field, so we need to test for that.
    $https = (!empty($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] !== 'off') || ($port == 443)));
    $server_path = $_SERVER['SERVER_NAME'];
    $my_path = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
    $server_path .= trim((($https && ($port != 443)) || (!$https && ($port != 80))) ? ':'.$port : '', '/');
    $http_vars['bmlt_root'] = 'http'.($https ? 's' : '').'://'.$server_path.$my_path;

    $langs = array ( $server->GetLocalLang() );
    $localized_strings = c_comdef_server::GetLocalStrings();
    
    if (isset($http_vars['lang_enum'])) {
        if (!is_array($http_vars['lang_enum'])) {
            $langs = array ( trim($http_vars['lang_enum']) );
        } else {
            $langs = $http_vars['lang_enum'];
        }
    }

    if (!isset($http_vars['switcher'])) {
        $http_vars['switcher'] = '';
    }
    
    switch ($http_vars['switcher']) {
        case 'GetSearchResults':
            $meanLocationData = array();
            $formats_ar = array();
            
            if (isset($http_vars['xml_data'])) {
                $result2 = GetSearchResults($http_vars, $formats_ar, $meanLocationData);
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                
                $blueMeanieXML = '<search_average>';
                $blueMeanieXML .= '<location>';
                $blueMeanieXML .= '<latitude>'.$meanLocationData['search_average']['location']['latitude'].'</latitude>';
                $blueMeanieXML .= '<longitude>'.$meanLocationData['search_average']['location']['longitude'].'</longitude>';
                $blueMeanieXML .= '</location>';
                $blueMeanieXML .= '<radius>';
                $blueMeanieXML .= '<miles>'.$meanLocationData['search_average']['radius']['miles'].'</miles>';
                $blueMeanieXML .= '<kilometers>'.$meanLocationData['search_average']['radius']['kilometers'].'</kilometers>';
                $blueMeanieXML .= '</radius>';
                $blueMeanieXML .= '</search_average>';
                $blueMeanieXML .= '<search_center>';
                $blueMeanieXML .= '<location>';
                $blueMeanieXML .= '<latitude>'.$meanLocationData['search_center']['location']['latitude'].'</latitude>';
                $blueMeanieXML .= '<longitude>'.$meanLocationData['search_center']['location']['longitude'].'</longitude>';
                $blueMeanieXML .= '</location>';
                $blueMeanieXML .= '<radius>';
                $blueMeanieXML .= '<miles>'.$meanLocationData['search_center']['radius']['miles'].'</miles>';
                $blueMeanieXML .= '<kilometers>'.$meanLocationData['search_center']['radius']['kilometers'].'</kilometers>';
                $blueMeanieXML .= '</radius>';
                $blueMeanieXML .= '</search_center>';
                
                if (!isset($http_vars['getMeanLocationData'])) {
                    $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', trim(strtolower($_SERVER['SERVER_NAME'])).(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetSearchResults.php'));
                    $result .= "<meetings xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                    $result .= TranslateToXML($result2);
                    if ((isset($http_vars['get_used_formats']) || isset($http_vars['get_formats_only'])) && $formats_ar && is_array($formats_ar) && count($formats_ar)) {
                        if (isset($http_vars['get_formats_only'])) {
                            $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                            $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', trim(strtolower($_SERVER['SERVER_NAME'])).(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetFormats.php'));
                            $result .= "<formats xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                        } else {
                            $result .= "<formats>";
                        }
                        $result3 = GetFormats($server, $langs, $formats_ar);
                        $result .= TranslateToXML($result3);
                        $result .= "</formats>";
                    }
                    
                    if (!isset($http_vars['get_formats_only'])) {
                        $result .= "<locationInfo>";
                        $result .= $blueMeanieXML;
                        $result .= "</locationInfo>";
                        $result .= "</meetings>";
                    }
                } else {
                    $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', trim(strtolower($_SERVER['SERVER_NAME'])).(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetMeetingLocationInfo.php'));
                    $result .= "<locationInfo xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                    $result .= $blueMeanieXML;
                    $result .= "</locationInfo>";
                }
            } elseif (isset($http_vars['gpx_data'])) {
                $result2 = GetSearchResults($http_vars, $formats_ar);
                $result2 = returnArrayFromCSV(explode("\n", $result2));
                if (is_array($result2) && count($result2)) {
                    $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                    $result .= "<gpx version=\"1.0\" xmlns=\"http://".htmlspecialchars(trim(strtolower($_SERVER['SERVER_NAME'])))."\" xmlns:xsn=\"http://www.w3.org/2001/XMLSchema-instance\" xsn:schemaLocation=\"http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd\">";
                
                    $minlng = 361;
                    $minlat = 361;
                    $maxlng = -361;
                    $maxlat = -361;
                
                    foreach ($result2 as $meeting) {
                        $lng = floatval($meeting['longitude']);
                        $lat = floatval($meeting['latitude']);
                    
                        if ($lng || $lat) {
                            $minlng = min($minlng, $lng);
                            $minlat = min($minlat, $lat);
                            $maxlng = max($maxlng, $lng);
                            $maxlat = max($maxlat, $lat);
                        }
                    }
                
                    $result .= '<bounds minlat="'.htmlspecialchars($minlat).'" minlon="'.htmlspecialchars($minlng).'" maxlat="'.htmlspecialchars($maxlat).'" maxlon="'.htmlspecialchars($maxlng).'"/>';
                
                    foreach ($result2 as $meeting) {
                        $desc = prepareSimpleLine($meeting);
                    
                        $name = c_comdef_htmlspecialchars(trim(stripslashes($meeting['meeting_name'])));
                        if (!$name) {
                            $name = "NA Meeting";
                        }
                        
                        $lng = floatval($meeting['longitude']);
                        $lat = floatval($meeting['latitude']);
                        $type = 'NA Meeting';
                    
                        if ($lng || $lat) {
                            $result .= '<wpt lat="'.htmlspecialchars($lat).'" lon="'.htmlspecialchars($lng).'">';
                                $result .= '<name><![CDATA['.htmlspecialchars($name).']]></name>';
                            if ($desc) {
                                $result .= '<desc><![CDATA['.htmlspecialchars($desc).']]></desc>';
                            }
                            
                                $result .= '<type><![CDATA['.htmlspecialchars($type).']]></type>';
                                $result .= '<sym>Diamond, Blue</sym>';
                            $result .= '</wpt>';
                        }
                    }
                
                    $result .= '</gpx>';
                }
            } elseif (isset($http_vars['kml_data'])) {
                $result2 = GetSearchResults($http_vars, $formats_ar);
                $result2 = returnArrayFromCSV(explode("\n", $result2));
                if (is_array($result2) && count($result2)) {
                    $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                    $result .= '<kml xmlns="http://www.opengis.net/kml/2.2">';
                    $result .= '<Document>';
                
                    foreach ($result2 as $meeting) {
                        $desc = prepareSimpleLine($meeting);
                        $address = prepareSimpleLine($meeting, false);
                    
                        $name = c_comdef_htmlspecialchars(trim(stripslashes($meeting['meeting_name'])));
                        
                        if (!$name) {
                            $name = "NA Meeting";
                        }
                        
                        $lng = floatval($meeting['longitude']);
                        $lat = floatval($meeting['latitude']);
                    
                        if ($lng || $lat) {
                            $result .= '<Placemark>';
                                $result .= '<name>'.htmlspecialchars($name).'</name>';
                                
                            if ($address) {
                                $result .= '<address>'.$address.'</address>';
                            }
                                
                            if ($desc) {
                                $result .= '<description>'.$desc.'</description>';
                            }
                                    
                                $result .= '<Point>';
                                    $result .= '<coordinates>';
                                        $result .=  htmlspecialchars($lng).','.htmlspecialchars($lat).',0';
                                    $result .= '</coordinates>';
                                $result .= '</Point>';
                            $result .= '</Placemark>';
                        }
                    }
                
                    $result .= '</Document>';
                    $result .= '</kml>';
                }
            } elseif (isset($http_vars['poi_data'])) {
                $result2 = GetSearchResults($http_vars, $formats_ar);
                $result2 = returnArrayFromCSV(explode("\n", $result2));
                if (is_array($result2) && count($result2)) {
                    $result = "lon,lat,name,desc\n";
                    foreach ($result2 as $meeting) {
                        $desc = htmlspecialchars_decode(prepareSimpleLine($meeting));
                    
                        $name = trim(stripslashes($meeting['meeting_name']));
                        
                        if (!$name) {
                            $name = "NA Meeting";
                        }
                    
                        $name = addcslashes($name, '"');
                        $desc = addcslashes($desc, '"');
                    
                        $lng = floatval($meeting['longitude']);
                        $lat = floatval($meeting['latitude']);
                    
                        if ($lng || $lat) {
                            $result .= '"'.$lng.'","'.$lat.'","'.$name.'","'.$desc.'"'."\n";
                        }
                    }
                }
            } elseif (isset($http_vars['json_data'])) {
                $result = TranslateToJSON(GetSearchResults($http_vars, $formats_ar, $meanLocationData));
                if ((isset($http_vars['get_used_formats']) || isset($http_vars['get_formats_only']))) {
                    if (isset($http_vars['get_formats_only'])) {
                        $format_list = '[]';
                        if (isset($formats_ar) && is_array($formats_ar) && count($formats_ar)) {
                            $format_list = TranslateToJSON(GetFormats($server, $langs, $formats_ar));
                        }
                        
                        $result = '{"formats":'.$format_list.'}';
                    } else {
                        if (isset($http_vars['appendMeanLocationData'])) {
                            $result = '{"meetings":'.$result.',"formats":'.TranslateToJSON(GetFormats($server, $langs, $formats_ar)).',"locationInfo":'.array2json($meanLocationData).'}';
                        } else {
                            $format_list = '[]';
                            if (isset($formats_ar) && is_array($formats_ar) && count($formats_ar)) {
                                $format_list = TranslateToJSON(GetFormats($server, $langs, $formats_ar));
                            }
                            
                            $result = '{"meetings":'.$result.',"formats":'.$format_list.'}';
                        }
                    }
                } else {
                    if (isset($http_vars['getMeanLocationData']) && is_array($meanLocationData) && count($meanLocationData)) {
                        if (isset($http_vars['appendMeanLocationData'])) {
                            $result = '{"meetings":'.$result.',"locationInfo":'.array2json($meanLocationData).'}';
                        } else {
                            $result = array2json(array ( 'locationInfo' => $meanLocationData ));
                        }
                    }
                }
            } else {
                $result2 = GetSearchResults($http_vars, $formats_ar);

                if (isset($http_vars['get_formats_only'])) {
                    $result2 = GetFormats($server, $langs, $formats_ar);
                    
                    if (!$result2) {
                        $result2 = '[]';
                    }
                }
                
                $result = $result2;
            }
            break;
        
        case 'GetFormats':
            $result2 = GetFormats($server, $langs);
            
            if (isset($http_vars['xml_data'])) {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetFormats.php'));
                $result .= "<formats xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                $result .= TranslateToXML($result2);
                $result .= "</formats>";
            } elseif (isset($http_vars['json_data'])) {
                $result = TranslateToJSON($result2);
                if (!$result) {
                    $result = '[]';
                }
            } else {
                $result = $result2;
            }
            break;
        
        case 'GetServiceBodies':
            $recursive = false;
            if (isset($http_vars['recursive']) && $http_vars['recursive'] == '1') {
                $recursive = true;
            }
            $services = null;
            if (isset($http_vars['services'])) {
                $services = is_array($http_vars['services']) ? $http_vars['services'] : array($http_vars['services']);
            }
            $result2 = GetServiceBodies($server, $services, $recursive);
            
            if (isset($http_vars['xml_data'])) {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetServiceBodies.php'));
                $result .= "<serviceBodies xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                $result .= TranslateToXML($result2);
                $result .= "</serviceBodies>";
            } elseif (isset($http_vars['json_data'])) {
                $result = TranslateToJSON($result2);
                if (!$result) {
                    $result = '[]';
                }
            } else {
                $result = $result2;
            }
            break;
        
        case 'GetChanges':
            $start_date = null;
            $end_date = null;
            $meeting_id = null;
            $service_body_id = null;
            $meetings_only = true;
            
            if (isset($http_vars['start_date'])) {
                $start_date = strtotime(trim($http_vars['start_date']));
            }
            
            if (isset($http_vars['end_date'])) {
                $end_date = strtotime(trim($http_vars['end_date']));
            }
            
            if (isset($http_vars['meeting_id'])) {
                $meeting_id = intval($http_vars['meeting_id']);
            }
            
            if (isset($http_vars['service_body_id'])) {
                $service_body_id = intval($http_vars['service_body_id']);
            }
            
            $result2 = GetChanges($http_vars, $start_date, $end_date, $meeting_id, $service_body_id);
            
            if (isset($http_vars['xml_data'])) {
                $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetChanges.php'));
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><changes xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">".TranslateToXML($result2)."</changes>";
            } elseif (isset($http_vars['json_data'])) {
                $result = TranslateToJSON($result2);
                if (!$result) {
                    $result = '[]';
                }
            } else {
                $result = $result2;
            }
            break;
        
        case 'GetServerInfo':
            $result2 = GetServerInfo();
            if (isset($http_vars['xml_data'])) {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/ServerInfo.php'));
                $result .= "<serverInfo xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                $result .= TranslateToXML($result2);
                $result .= "</serverInfo>";
            } elseif (isset($http_vars['json_data'])) {
                $result = TranslateToJSON($result2);
            } else {
                $result = $result2;
            }
            break;
        
        case 'GetNAWSDump':
            $result = CSVHandleNawsDump($http_vars, $server);
            break;
        
        case 'GetCoverageArea':
            $result2 = GetCoverageArea();
            if (isset($http_vars['xml_data'])) {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetCoverageArea.php'));
                $result .= "<coverageArea xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                $result .= TranslateToXML($result2);
                $result .= "</coverageArea>";
            } elseif (isset($http_vars['json_data'])) {
                $result = TranslateToJSON($result2);
            } else {
                $result = $result2;
            }
            break;
        
        case 'GetFieldKeys':
            $keys = c_comdef_meeting::GetFullTemplate();
            
            if (isset($keys) && is_array($keys) && count($keys)) {
                $result2 = array ('"key","description"');
            
                foreach ($keys as $key) {
                    if (($key['visibility'] != 1) && ($key['key'] != 'published') && ($key['key'] != 'shared_group_id_bigint')) {
                        $result2[] = '"'.$key['key'].'","'.$key['field_prompt'].'"';
                    }
                }
                
                $result2 = implode("\n", $result2);
                
                if (isset($http_vars['xml_data'])) {
                    $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                    $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetFieldKeys.php'));
                    $result .= "<fields xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                    $result .= TranslateToXML($result2);
                    $result .= "</fields>";
                } elseif (isset($http_vars['json_data'])) {
                    $result = TranslateToJSON($result2);
                } else {
                    $result = $result2;
                }
            }
            break;
        
        case 'GetFieldValues':
            $meeting_key = trim($http_vars['meeting_key']);
            $values = c_comdef_meeting::GetAllValuesForKey($meeting_key);
            if (isset($values) && is_array($values) && count($values)) {
                $result2 = array ('"'.$meeting_key.'","ids"');
            
                foreach ($values as $value => $ids) {
                    if (($meeting_key == 'formats') && isset($http_vars['specific_formats']) && trim($http_vars['specific_formats'])) {
                        $targeted_formats = explode(',', trim($http_vars['specific_formats']));
                        if (is_array($targeted_formats) && count($targeted_formats)) {
                            $targeted_formats = array_map(intval, $targeted_formats);
                            $these_formats = explode("\t", $value);
                        
                            if (is_array($these_formats) && count($these_formats)) {
                                $these_formats = array_map(intval, $these_formats);
                                $value = array_intersect($these_formats, $targeted_formats);
                                if (isset($http_vars['all_formats'])) {
                                    $diff = array_diff($targeted_formats, $value);
                                    if (isset($diff) && is_array($diff) && count($diff)) {
                                        continue;
                                    }
                                }
                                    
                                if (!count($value)) {
                                    continue;
                                } else {
                                    $value = implode("\t", $value);
                                }
                            } else {
                                continue;
                            }
                        } else {
                            break;
                        }
                    } elseif ($meeting_key == 'worldid_mixed') {
                        if ($value != 'NULL') {
                            $value = trim($value);
                            $is_olm = (preg_match("/^OLM/", $value) != false);
                            $stripped_id = intval(preg_replace('|[^0-9]*?|', '', $value));
                            if ($stripped_id == 0) {
                                $value = 'NULL';
                            } else {
                                if ($is_olm) {
                                    $value = sprintf("OLM%06d", $stripped_id);
                                } else {
                                    $value = sprintf("G%08d", $stripped_id);
                                }
                            }
                        }
                    }

                    $ids = explode('\t', $ids);
                    $ids = trim(implode("\t", $ids));
                    $result2[] = '"'.$value.'","'.$ids.'"';
                }
                
                $result3 = array();
                
                foreach ($result2 as $resultRow) {
                    list ( $key, $value ) = explode(',', $resultRow);
                    
                    $value = explode("\t", trim($value, '"'));
                    $oldValue = explode("\t", array_key_exists($key, $result3) ? $result3[$key] : "");
                    $value = array_unique(array_merge($value, $oldValue));
                    asort($value);
                    $value = trim(implode("\t", $value));
                    $result3[$key] = $value;
                }
                
                $result2 = array();
                foreach ($result3 as $key => $value) {
                    $key = str_replace('&APOS&', ',', trim($key, '"'));
                    
                    $result2[] = "\"$key\",\"$value\"";
                }
                        
                $result2 = implode("\n", $result2);
            }
            
            if (isset($http_vars['xml_data'])) {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $xsd_uri = 'http://'.htmlspecialchars(str_replace('/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME']).'/GetFieldValues.php'));
                $result .= "<fields xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
                $result .= str_replace("\t", ',', TranslateToXML($result2));
                $result .= "</fields>";
            } elseif (isset($http_vars['json_data'])) {
                $result = TranslateToJSON($result2);
            } else {
                $result = str_replace("\t", ',', $result2);
            }
            break;
                
        default:
            $result = HandleDefault($http_vars);
            break;
    }
    
    return $result;
}

/*******************************************************************/
/**
    \brief  This returns a string, with the meeting's daya, time and location data in a simple string.

    \returns    a string, containing the meeting day, time and location summary.
*/
function prepareSimpleLine(
    $meeting,          /**< An associative array of meeting data */
    $withDate = true   /**< If false (default is true), the weekday and time will not be added. */
) {
    $localized_strings = c_comdef_server::GetLocalStrings();

    $location_borough = array_key_exists('location_city_subsection', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_city_subsection']))) : "";
    $location_neighborhood = array_key_exists('location_neighborhood', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_neighborhood']))) : "";
    $location_province = array_key_exists('location_province', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_province']))) : "";
    $location_nation = array_key_exists('location_nation', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_nation']))) : "";
    $location_postal_code_1 = array_key_exists('location_postal_code_1', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_postal_code_1']))) : "";
    $location_text = array_key_exists('location_text', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_text']))) : "";
    $street = array_key_exists('location_street', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_street']))) : "";
    $info = array_key_exists('location_info', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_info']))) : "";
    $town = array_key_exists('location_municipality', $meeting) ? c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_municipality']))) : "";
    $desc = $withDate ? '' : $location_text;
    
    if ($location_borough) {
        $town = $location_borough;
    }
    
    if ($location_province) {
        $town = "$town, $location_province";
    }
    
    if ($location_postal_code_1) {
        $town = "$town, $location_postal_code_1";
    }
    
    if ($location_nation) {
        $town = "$town, $location_nation";
    }
    
    if ($withDate && $location_neighborhood) {
        $town = "$town ($location_neighborhood)";
    }
    
    if ($street) {
        if ($desc) {
            $desc .= ", ";
        }
        $desc .= $street;
    }
    
    if ($town) {
        if ($desc) {
            $desc .= ", ";
        }
        $desc .= $town;
    }
    
    if ($withDate && $info) {
        if ($desc) {
            $desc .= " ($info)";
        } else {
            $desc = $info;
        }
    }
    
    $weekday = intval(trim(stripslashes($meeting['weekday_tinyint'])));
    $time = date($localized_strings['time_format'], strtotime($meeting['start_time']));
    $weekday = $localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_names'][$weekday];
    
    $ret = null;
    
    if ($withDate && $weekday) {
        $ret = $weekday;
    }
        
    if ($withDate && $time) {
        if ($ret) {
            $ret .= ', ';
        }
        
        $ret .= $time;
    }
    
    if ($ret) {
        $ret .= ', ';
    }
    
    $ret .= $desc;
    
    return $ret;
}

/*******************************************************************/
/**
    \brief
*/
function CSVHandleNawsDump(
    $in_http_Vars,  ///< The ID of the Service Body to dump
    $in_server      ///< The Root Server instance
) {
    $sb = $in_server->GetServiceBodyByIDObj(intval($in_http_Vars['sb_id']));
    
    if ($sb) {
        require_once(dirname(__FILE__).'/search_results_csv.php');
        $service_bodies = array ( 'services' => c_comdef_server::GetServiceBodyHierarchyIDs(intval($in_http_Vars['sb_id'])) );
        
        $cc = preg_replace('|[\W]|', '_', strtoupper(trim($sb->GetWorldID())));
        
        if (preg_match('|^_+$|', $cc)) {
            $cc = '';
        }

        $filename = preg_replace('|[\W]|', '_', strtolower(trim($sb->GetLocalName())));
        
        if (preg_match('|^_+$|', $filename)) {
            $filename = '';
        }
        
        $filename .= date('_Y_m_d_H_i_s');
    
        if ($cc) {
            $filename = "$cc"."_$filename";
        }
        
        $sb_array = array("services" => array(), "advanced_published" => 0);
        
        // Make sure we all have NAWS IDs.
        foreach ($service_bodies["services"] as $sbID) {
            $service_body_object = $in_server->GetServiceBodyByIDObj($sbID);
            if ($service_body_object && $service_body_object->GetWorldID()) {
                $sb_array["services"][] = $sbID;
            }
        }
        header("Content-Disposition: attachment; filename=BMLT_$filename.csv");
        return ReturnNAWSFormatCSV($sb_array, $in_server);
    }
}

/*******************************************************************/
/**
    \brief  This returns an associative array from the given CSV, which is an array of lines, and the top line is the field names.

    \returns an associative array. Each main element will be one line, and each line will be an associative array of fields. If a field is not present in the line, it is not included.
*/
function returnArrayFromCSV( $inCSVArray   ///< A array of CSV data, split as lines (each element is a single text line of CSV data). the first line is the header (array keys).
)
{
    $ret = null;
    
    $desc_line = $inCSVArray[0];    // Get the field names.
    $desc_line = explode('","', trim($desc_line, '"'));
    
    for ($index = 1; $index < count($inCSVArray); $index++) {
        $interim_line = explode('","', trim($inCSVArray[$index], '"'));

        if ($interim_line && count($interim_line)) {
            $result = null;
            
            $interim_line = array_combine($desc_line, $interim_line);
            
            foreach ($interim_line as $key => $value) {
                $value = trim($value);
                
                if ($value) {
                    $result[$key] = $value;
                }
            }
            
            if (is_array($result) && count($result)) {
                $ret[] = $result;
            }
        }
    }
        
    return $ret;
}

/*******************************************************************/
/**
    \brief  Calculates the distance, in Km, between two long/lat pairs.
            This uses the Haversine formula.
            Cribbed from here: http://blog.voltampmedia.com/2011/12/17/php-implementation-of-haversine-computation/

    \returns A floating-point, positive number. The distance, in miles.
*/
function calcDistanceInMiles(
    $lat_1,     ///< The latitude of the first point, in degrees.
    $long_1,    ///< The longitude of the first point, in degrees.
    $lat_2,     ///< The latitude of the second point, in degrees.
    $long_2     ///< The longitude of the second point, in degrees.
) {
    $sin_lat = sin(deg2rad($lat_2 - $lat_1) / 2.0);
    $sin2_lat = $sin_lat * $sin_lat;

    $sin_long = sin(deg2rad($long_2 - $long_1) / 2.0);
    $sin2_long = $sin_long * $sin_long;

    $cos_lat_1 = cos($lat_1);
    $cos_lat_2 = cos($lat_2);

    $sqrt = sqrt($sin2_lat + ($cos_lat_1 * $cos_lat_2 * $sin2_long));

    $earth_radius = 3963.1676; // in miles

    $distance = 2.0 * $earth_radius * asin($sqrt);

    return $distance;
}

/*******************************************************************/
/**
    \brief  This returns the search results, in whatever form was requested.

    \returns CSV data, with the first row a key header.
*/
function GetSearchResults(
    $in_http_vars,              ///< The HTTP GET and POST parameters.
    &$formats_ar = null,        ///< This will return the formats used in this search.
    &$meanLocationData = null   ///< This is a passed in receptacle for some location data calculations.
) {
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

    require_once(dirname(__FILE__).'/search_results_csv.php');
    $geocode_results = null;
    $ignore_me = null;
    $meeting_objects = array();
    $result = DisplaySearchResultsCSV($in_http_vars, $ignore_me, $geocode_results, $meeting_objects);
    $locationData = array ( );
        
    if (is_array($meeting_objects) && count($meeting_objects)) {
        foreach ($meeting_objects as $one_meeting) {
            if (isset($in_http_vars['getMeanLocationData']) || (isset($meanLocationData)&& is_array($meanLocationData))) {
                $locationData[] = array ( 'long' => floatval($one_meeting->GetMeetingDataValue('longitude')), 'lat' => floatval($one_meeting->GetMeetingDataValue('latitude')) );
            }
            
            if (is_array($formats_ar)) {
                $formats = $one_meeting->GetMeetingDataValue('formats');

                foreach ($formats as $format) {
                    if ($format && ($format instanceof c_comdef_format)) {
                        $format_shared_id = $format->GetSharedID();
                        $formats_ar[$format_shared_id] = $format;
                    }
                }
            }
        }
    }
    
    if (count($locationData)) {  // If the caller just wants an average location report, then give them that.
        $avgLong = 0.0;
        $avgLat = 0.0;
        $minLong = 1000.0;
        $maxLong = -1000.0;
        $minLat = 1000.0;
        $maxLat = -1000.0;
        
        foreach ($locationData as $location) {
            $avgLong += $location['long'];
            $avgLat += $location['lat'];
            $minLong = min($minLong, $location['long']);
            $maxLong = max($maxLong, $location['long']);
            $minLat = min($minLat, $location['lat']);
            $maxLat = max($maxLat, $location['lat']);
        }
        
        $avgLong = $avgLong / floatVal(count($locationData));
        $avgLat = $avgLat / floatVal(count($locationData));
        $centerLat = ($maxLat + $minLat) / 2.0;
        $centerLong = ($maxLong + $minLong) / 2.0;
        
        $d1 = calcDistanceInMiles($avgLat, $avgLong, $maxLat, $maxLong);
        $d2 = calcDistanceInMiles($avgLat, $avgLong, $minLat, $minLong);
        $d3 = calcDistanceInMiles($avgLat, $avgLong, $minLat, $maxLong);
        $d4 = calcDistanceInMiles($avgLat, $avgLong, $maxLat, $minLong);
        
        $avg_radiusMi = max($d1, $d2, $d3, $d4);
        $avg_radiusKm = $avg_radiusMi * 1.60934;
        
        $hard_radiusMi = calcDistanceInMiles($centerLat, $centerLong, $maxLat, $maxLong);
        $hard_radiusKm = $hard_radiusMi * 1.60934;
        
        if (isset($meanLocationData) && is_array($meanLocationData)) {
            $meanLocationData = array ( 'search_average' => array ( 'location' => array ( 'latitude' => $avgLat, 'longitude' => $avgLong, ), 'radius' => array ( 'miles' => $avg_radiusMi, 'kilometers' => $avg_radiusKm ) ),
                                        'search_center' => array ( 'location' => array ( 'latitude' => $centerLat, 'longitude' => $centerLong, ), 'radius' => array ( 'miles' => $hard_radiusMi, 'kilometers' => $hard_radiusKm ) ));
        }
        
        if (isset($in_http_vars['getMeanLocationData'])) {
            $result = '"average_center_latitude","average_center_longitude","average_radius_mi","average_radius_km","search_center_latitude","search_center_longitude","search_center_radius_mi","search_center_radius_km"'."\n";
            $result .= '"'.$avgLat.'","'.$avgLong.'","'.$avg_radiusMi.'","'.$avg_radiusKm.'","'.$centerLat.'","'.$centerLong.'","'.$hard_radiusMi.'","'.$hard_radiusKm.'"';
        }
    }
    
    if (!isset($in_http_vars['getMeanLocationData']) && isset($in_http_vars['data_field_key']) && $in_http_vars['data_field_key']) {
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
                    if (!$column) {
                        $column = ' ';
                    }
                    if (in_array($keys[$index++], $the_keys)) {
                        array_push($row_columns, $column);
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
/** \brief  Returns a set of two coordinates that define a rectangle
            that encloses all of the meetings.

    \returns a dictionary, with the two coordinates.
*/
function GetCoverageArea()
{
    $result = c_comdef_server::GetCoverageArea();
    $ret = null;
    
    if (isset($result) && is_array($result) && count($result)) {
        $ret = array ( '"nw_corner_longitude","nw_corner_latitude","se_corner_longitude","se_corner_latitude"' );
        $ret[1] = '"'.strval($result["nw_corner"]["longitude"]).'","'.strval($result["nw_corner"]["latitude"]).'","'.strval($result["se_corner"]["longitude"]).'","'.strval($result["se_corner"]["latitude"]).'"';
        $ret = implode("\n", $ret);
    }
        
    return $ret;
}

/*******************************************************************/
/**
    \brief  This returns the complete formats table.

    \returns CSV data, with the first row a key header.
*/
function GetFormats(
    &$server,           ///< A reference to an instance of c_comdef_server
    $in_lang = null,    ///< The language of the formats to be returned.
    $in_formats = null  //< If supplied, an already-fetched array of formats.
) {
    $my_keys = array (  'key_string',
                        'name_string',
                        'description_string',
                        'lang',
                        'id',
                        'world_id',
                        'root_server_uri',
                        'format_type_enum',
                        );
    
    $ret = null;
    
    $formats_obj = $server->GetFormatsObj();
    if ($formats_obj instanceof c_comdef_formats) {
        $langs = $server->GetFormatLangs();
        $used_formats = $server->GetUsedFormatIDs();
        if (isset($in_lang) && is_array($in_lang) && count($in_lang)) {
            $langs2 = array();
            foreach ($in_lang as $key) {
                if (array_key_exists($key, $langs)) {
                    $langs2[$key] = $langs[$key];
                }
            }
            
            $langs = $langs2;
        } elseif (array_key_exists($in_lang, $langs)) {
            $langs = array ( $in_lang => $langs[$in_lang] );
        }

        $ret .= '"'.implode('","', $my_keys)."\"\n";
        foreach ($langs as $key => $value) {
            if ($in_formats) {
                $format_array = $in_formats;
            } else {
                $format_array =  $formats_obj->GetFormatsByLanguage($key);
            }
            
            if (is_array($format_array) && count($format_array)) {
                foreach ($format_array as $format) {
                    if ($format instanceof c_comdef_format) {
                        $localized_format = $server->GetOneFormat($format->GetSharedID(), $key);
                        if ($localized_format instanceof c_comdef_format) {
                            $line = '';
                            foreach ($my_keys as $ky) {
                                if ($line) {
                                    $line .= ',';
                                }
                                
                                $val = '';
                                
                                switch ($ky) {
                                    case 'lang':
                                        $val = $key;
                                        break;
                                    
                                    case 'id':
                                        $val = $localized_format->GetSharedID();
                                        break;
                                    
                                    case 'key_string':
                                        $val = $localized_format->GetKey();
                                        break;
                                    
                                    case 'name_string':
                                        $val = $localized_format->GetLocalName();
                                        break;
                                    
                                    case 'description_string':
                                        $val = $localized_format->GetLocalDescription();
                                        break;
                                    
                                    case 'world_id':
                                        $val = $localized_format->GetWorldID();
                                        break;
                                    
                                    case 'root_server_uri':
                                        $val = dirname(dirname(GetURLToMainServerDirectory(true)));
                                        break;
                                        
                                    case 'format_type_enum':
                                        $val = $localized_format->GetFormatType();
                                        break;
                                }
                                
                                $line .= '"'.str_replace('"', '\"', trim($val)).'"';
                            }
                            
                            if (in_array($localized_format->GetSharedID(), $used_formats)) {
                                $ret .= "$line\n";
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief  This returns the complete Service bodies table.

    \returns CSV data, with the first row a key header.
*/
function GetServiceBodies(
    &$server,            ///< A reference to an instance of c_comdef_server
    $services = null,
    $recursive = false
) {
    $ret = array ();
    $localized_strings = c_comdef_server::GetLocalStrings();

    $ret[0] = '"id","parent_id","name","description","type","url","helpline","world_id"';
    
    if ($localized_strings['include_service_body_email_in_semantic']) {
        $ret[0] .= ',"contact_email"';
    }

    $servicesInclude = array();
    $servicesExclude = array();
    if ($services) {
        foreach ($services as $id) {
            if (substr($id, 0, 1) == "-") {
                array_push($servicesExclude, substr($id, 1));
            } else {
                array_push($servicesInclude, $id);
            }
        }
    }

    if ($recursive) {
        $servicesInclude = array_merge($servicesInclude, GetChildServiceBodies($server, $servicesInclude));
        $servicesExclude = array_merge($servicesExclude, GetChildServiceBodies($server, $servicesExclude));
    }

    try {
        $array_obj = $server->GetServiceBodyArray();
        if (is_array($array_obj) && count($array_obj)) {
            foreach ($array_obj as &$sb) {
                if ($sb instanceof c_comdef_service_body) {
                    if (count($servicesInclude) && !in_array($sb->GetID(), $servicesInclude)) {
                        continue;
                    }
                    if (count($servicesExclude) && in_array($sb->GetID(), $servicesExclude)) {
                        continue;
                    }
                    $row = array();
                    $row[] = $sb->GetID();
                    $row[] = $sb->GetOwnerID();
                    $row[] = $sb->GetLocalName();
                    $description = preg_replace('|[^\S]+?|', " ", $sb->GetLocalDescription());
                    $row[] = $description;
                    $row[] = $sb->GetSBType();
                    $row[] = $sb->GetURI();
                    $row[] = trim($sb->GetHelpline());
                    $row[] = $sb->GetWorldID();
                    if ($localized_strings['include_service_body_email_in_semantic']) {
                        $row[] = trim($sb->GetContactEmail());
                    }
                    $row = '"'.implode('","', $row).'"';
                    $ret[] = $row;
                }
            }
        }
    } catch (Exception $e) {
    }

    return implode("\n", $ret);
}

function GetChildServiceBodies($server, $parents)
{
    $ret = array();
    $children = $parents;
    while (count($children)) {
        $newChildren = array();
        foreach ($server->GetServiceBodyArray() as $serviceBody) {
            if (in_array($serviceBody->GetOwnerID(), $children) && !in_array($serviceBody->GetID(), $ret)) {
                array_push($newChildren, $serviceBody->GetID());
                array_push($ret, $serviceBody->GetID());
            }
        }
        $children = $newChildren;
    }
    return $ret;
}

/*******************************************************************/
/**
    \brief  This returns a line of server info.

    \returns CSV data, with the first row a key header.
*/
function GetServerInfo()
{
    require(dirname(__FILE__).'/../../server/config/get-config.php');
    $ret = '';
    $version_array = GetServerVersion();
    $version_num = (intval($version_array[0]) * 1000000) + (intval($version_array[1]) * 1000) + intval($version_array[2]);
    $version_string = strval($version_array[0]).'.'.strval($version_array[1]).'.'.strval($version_array[2]);
    $lang_array = c_comdef_server::GetServerLangs();
    $lang_string = implode(',', array_keys($lang_array));
    $localStrings = c_comdef_server::GetLocalStrings();
    $default_lang = strval($localStrings['enum']);
    $canAdmin = isset($g_enable_semantic_admin) && $g_enable_semantic_admin ? '1' : '0';
    $centerLongLatZoom = implode(',', $localStrings['search_spec_map_center']);
    $canEmail = isset($g_enable_email_contact) && $g_enable_email_contact ? '1' : '0';
    $includeServiceBodiesOnEmails = isset($include_service_body_admin_on_emails) && $include_service_body_admin_on_emails ? '1' : '0';
    $changeDepth = strVal(intval($change_depth_for_meetings));
    $dbVersion = c_comdef_server::GetDatabaseVersion();
    $availableFields = "";
    $keys = c_comdef_meeting::GetFullTemplate();
    $meeting_time_zones_enabled = $localStrings['meeting_time_zones_enabled'] ? '1' : '0';
    
    foreach ($keys as $key) {
        if (($key['visibility'] != 1) && ($key['key'] != 'published') && ($key['key'] != 'shared_group_id_bigint')) {
            if ($availableFields != "") {
                $availableFields .= ',';
            }
            
            $availableFields .= $key['key'];
        }
    }

    $ret = '"version","versionInt","langs","nativeLang","centerLongitude","centerLatitude","centerZoom","defaultDuration","regionBias","charSet","distanceUnits","semanticAdmin","emailEnabled","emailIncludesServiceBodies","changesPerMeeting","meeting_states_and_provinces","meeting_counties_and_sub_provinces","available_keys","google_api_key","dbVersion","dbPrefix","meeting_time_zones_enabled"'."\n";
    $ret .= '"'.$version_string.'","'.strval($version_num).'","'.$lang_string.'","'.$default_lang.'",';
    $ret .= '"'.strval($localStrings['search_spec_map_center']['longitude']).'","'.strval($localStrings['search_spec_map_center']['latitude']).'",';
    $ret .= '"'.strval($localStrings['search_spec_map_center']['zoom']).'","'.$localStrings['default_duration_time'].'",';
    $ret .= '"'.$localStrings['region_bias'].'","'.$localStrings['charset'].'","'.$localStrings['dist_units'].'","'.$canAdmin.'",';
    $ret .= '"'.$canEmail.'","'.$includeServiceBodiesOnEmails.'","'.$changeDepth.'","'.implode(',', $localStrings['meeting_states_and_provinces']).'","'.implode(',', $localStrings['meeting_counties_and_sub_provinces']).'","'.str_replace('"', '\"', $availableFields).',root_server_uri,format_shared_id_list","'.$localStrings['google_api_key'].'","'.$dbVersion.'","'.$localStrings['dbPrefix'].'","'.$meeting_time_zones_enabled.'"';
    
    return $ret;
}

/*******************************************************************/
/**
    \brief  Returns the server version in an array.

    \returns an array of integers, with [0] being the main version, [1] being the minor version, and [2] being the fix version.
*/
function GetServerVersion()
{
    $ret = array ( 0 );

    $xml = file_get_contents(dirname(dirname(__FILE__)).'/serverInfo.xml');

    if ($xml) {
        $info_file = new DOMDocument;
        if ($info_file instanceof DOMDocument) {
            if (@$info_file->loadXML($xml)) {
                $has_info = $info_file->getElementsByTagName("bmltInfo");
        
                if (($has_info instanceof domnodelist) && $has_info->length) {
                    $nodeVal = $has_info->item(0)->nodeValue;
                    $ret = explode('.', $nodeVal);
                }
            }
        }
    }
    
    if (!isset($ret[1])) {
        $ret[1] = '0';
    }
    
    if (!isset($ret[2])) {
        $ret[1] = '0';
    }
    
    $ret[0] = intval($ret[0]);
    $ret[1] = intval($ret[1]);
    $ret[2] = intval($ret[2]);
    
    return $ret;
}

/*******************************************************************/
/**
    \brief  This returns change records.

    \returns CSV data, with the first row a key header.
*/
function GetChanges(
    $in_http_vars,          ///< The HTTP GET/POST query.
    $in_start_date = null,  ///< Optional. A start date (In PHP time() format). If supplied, then only changes on, or after this date will be returned.
    $in_end_date = null,    ///< Optional. An end date (In PHP time() format). If supplied, then only changes that occurred on, or before this date will be returned.
    $in_meeting_id = null,  ///< Optional. If supplied, an ID for a particular meeting. Only changes for that meeting will be returned.
    $in_sb_id = null        ///< Optional. If supplied, an ID for a particular Service body. Only changes for that Service body will be returned.
) {
    $ret = null;
    
    try {
        $change_objects = c_comdef_server::GetChangesFromIDAndType('c_comdef_meeting', null, $in_start_date, $in_end_date);
        if ($change_objects instanceof c_comdef_changes) {
            $obj_array = $change_objects->GetChangesObjects();
            
            if (is_array($obj_array) && count($obj_array)) {
                set_time_limit(max(30, intval(count($obj_array) / 20))); // Change requests can take a loooong time...
                $localized_strings = c_comdef_server::GetLocalStrings();
                include(dirname(__FILE__).'/../../server/config/get-config.php');
                $ret = '"date_int","date_string","change_type","change_id","meeting_id","meeting_name","user_id","user_name","service_body_id","service_body_name","meeting_exists","details","json_data"'."\n";
                
                // If they specify a Service body, we also look in "child" Service bodies, so we need to produce a flat array of IDs.
                if (isset($in_sb_id) && $in_sb_id) {
                    global $bmlt_array_gather;
                    
                    $bmlt_array_gather = array();
                    
                    /************************************************//**
                    * This little internal function will simply fill    *
                    * the $bmlt_array_gather array with a linear set of *
                    * Service body IDs that can be used for a quick     *
                    * comparison, later on. It is a callback function.  *
                    ****************************************************/
                    function bmlt_at_at(
                        $in_value,
                        $in_key
                    ) {
                        global $bmlt_array_gather;
                        
                        if ($in_value instanceof c_comdef_service_body) {
                            array_push($bmlt_array_gather, $in_value->GetID());
                        }
                    }
                    $tmp = c_comdef_server::GetServer()->GetNestedServiceBodyArray($in_sb_id);
                    array_walk_recursive($tmp, 'bmlt_at_at');
                    
                    if (is_array($bmlt_array_gather) && count($bmlt_array_gather)) {
                        $in_sb_id = $bmlt_array_gather;
                    } else {
                        $in_sb_id = array ( $in_sb_id );
                    }
                }
                    
                foreach ($obj_array as $change) {
                    $change_type = $change->GetChangeType();
                    $date_int = intval($change->GetChangeDate());
                    $change_id = intval($change->GetID());
                    $date_string = date($change_date_format, $date_int);
                    $json_data = '';
                    
                    if ($change instanceof c_comdef_change) {
                        $b_obj = $change->GetBeforeObject();
                        $a_obj = $change->GetAfterObject();
                        $meeting_id = intval($change->GetBeforeObjectID());
                        $sb_a = intval(($a_obj instanceof c_comdef_meeting) ? $a_obj->GetServiceBodyID() : 0);
                        $sb_b = intval(($b_obj instanceof c_comdef_meeting) ? $b_obj->GetServiceBodyID() : 0);
                        $sb_c = intval($change->GetServiceBodyID());
                        
                        if (!$meeting_id) {
                            $meeting_id = intval($change->GetAfterObjectID());
                        }
                        
                        if ((intval($in_meeting_id) && intval($in_meeting_id) == intval($meeting_id)) || !intval($in_meeting_id)) {
                            $meeting_name = '';
                            $user_name = '';
                            
                            if (!is_array($in_sb_id) || !count($in_sb_id) || in_array($sb_a, $in_sb_id) || in_array($sb_b, $in_sb_id) || in_array($sb_c, $in_sb_id)) {
                                $sb_id = (intval($sb_c) ? $sb_c : (intval($sb_b) ? $sb_b : $sb_a));
                                $meeting = (null != $b_obj) ? $b_obj : $a_obj;
                                
                                // Using str_replace, because preg_replace is pretty expensive. However, I don't think this buys us much.
                                if ($b_obj instanceof c_comdef_meeting) {
                                    $meeting_name = str_replace('"', "'", str_replace("\n", " ", str_replace("\r", " ", $b_obj->GetMeetingDataValue('meeting_name'))));
                                } elseif ($a_obj instanceof c_comdef_meeting) {
                                    $meeting_name = str_replace('"', "'", str_replace("\n", " ", str_replace("\r", " ", $a_obj->GetMeetingDataValue('meeting_name'))));
                                }
                                
                                $user_id = intval($change->GetUserID());
                                
                                $user = c_comdef_server::GetUserByIDObj($user_id);
                                
                                if ($user instanceof c_comdef_user) {
                                    $user_name = htmlspecialchars($user->GetLocalName());
                                }
            
                                $sb = c_comdef_server::GetServiceBodyByIDObj($sb_id);
                                
                                if ($sb instanceof c_comdef_service_body) {
                                    $sb_name = htmlspecialchars($sb->GetLocalName());
                                }
                                
                                $meeting_exists = 0;
                                
                                if (c_comdef_server::GetOneMeeting($meeting_id, true)) {
                                    $meeting_exists = 1;
                                }
            
                                $details = '';
                                $desc = $change->DetailedChangeDescription();
                                
                                if ($desc && isset($desc['details']) && is_array($desc['details'])) {
                                    // We need to prevent double-quotes, as they are the string delimiters, so we replace them with single-quotes.
                                    $details = str_replace('"', "'", str_replace("\n", " ", str_replace("\r", " ", implode(" ", $desc['details']))));
                                }
                                
                                $change_line = array();
            
                                if ($date_int) {
                                    $change_line['date_int'] = $date_int;
                                } else {
                                    $change_line['date_int'] = 0;
                                }
                                
                                if ($date_string) {
                                    $change_line['date_string'] = $date_string;
                                } else {
                                    $change_line['date_string'] = '';
                                }
                                
                                if ($change_type) {
                                    $change_line['change_type'] = $change_type;
                                } else {
                                    $change_line['change_type'] = '';
                                }
                                
                                if ($change_id) {
                                    $change_line['change_id'] = $change_id;
                                } else {
                                    $change_line['change_id'] = 0;
                                }
                                
                                if ($meeting_id) {
                                    $change_line['meeting_id'] = $meeting_id;
                                } else {
                                    $change_line['meeting_id'] = 0;
                                }
                                
                                if ($meeting_name) {
                                    $change_line['meeting_name'] = $meeting_name;
                                } else {
                                    $change_line['meeting_name'] = '';
                                }
                                
                                if ($user_id) {
                                    $change_line['user_id'] = $user_id;
                                } else {
                                    $change_line['user_id'] = 0;
                                }
                                
                                if ($user_name) {
                                    $change_line['user_name'] = $user_name;
                                } else {
                                    $change_line['user_name'] = '';
                                }
                                
                                if ($sb_id) {
                                    $change_line['service_body_id'] = $sb_id;
                                } else {
                                    $change_line['service_body_id'] = '';
                                }
                                
                                if ($sb_name) {
                                    $change_line['service_body_name'] = $sb_name;
                                } else {
                                    $change_line['service_body_name'] = '';
                                }
                                
                                $change_line['meeting_exists'] = $meeting_exists;
                                
                                if ($details) {
                                    $change_line['details'] = $details;
                                } else {
                                    $change_line['details'] = '';
                                }
                        
                                $json_data = MakeJSONDataObject($b_obj, 'before');
                                
                                if (($json_data != '') && ($a_obj instanceof c_comdef_meeting)) {
                                    $json_data .= ',';
                                }
                                    
                                $json_data .= MakeJSONDataObject($a_obj, 'after');
                                
                                $change_line['json_data'] = '{'.str_replace('"', '&quot;', $json_data).'}';
                                
                                $ret .= '"'.implode('","', $change_line)."\"\n";
                            }
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
    }

    return $ret;
}

/*******************************************************************/
/**
    \brief Converts a given c_comdef_meeting object to a JSON object string.

    \returns A string, containing the JSON Data. It is blank if no JSON Data.
*/
function MakeJSONDataObject(
    $in_meeting_object, ///< The c_comdef_meeting object to be converted.
    $in_object_name     ///< A name for the returned object.
) {
    $json_data = '';
    
    if ($in_meeting_object instanceof c_comdef_meeting) {
        $keys = $in_meeting_object->GetMeetingDataKeys();
    
        foreach ($keys as $key) {
            if ($key) {
                $value = $in_meeting_object->GetMeetingDataValue($key);
                
                if ($value) {
                    if ($key == 'formats') {
                        $val_temp = array();
                        $values = $value;
                    
                        foreach ($values as $format) {
                            if ($format instanceof c_comdef_format) {
                                $val_temp[] = $format->GetKey();
                            }
                        }
                        
                        $value = $val_temp;
                    }
                
                    if (is_array($value)) {
                        if (count($value)) {
                            for ($c = 0; $c < count($value); $c++) {
                                $val = json_encode(trim($value[$c], '"'));
                                $val = str_replace('&quot;', '"', $val);
                                $val = str_replace('&amp;', '&', $val);
                                $val = trim(preg_replace("|\s+|", ' ', $val), '"');
                                $value[$c] = trim($val, "\\");
                            }
                                
                            if ($json_data) {
                                $json_data .= ',';
                            }
                
                            $json_data .= '"'.$key.'":'.'["'.implode('","', $value).'"]';
                        }
                    } else {
                        $value = trim($value, "\\");
                        $value = preg_replace("|^\"\"|", "\"&quot;", $value);
                        $value = trim(json_encode($value), '"');
                        $value = str_replace('&quot;', '"', $value);
                        $value = str_replace('&amp;', '&', $value);
                        $value = trim(preg_replace("|\s+|", ' ', $value), '\\');
                        $value = trim($value, '"');
                        if ($json_data) {
                            $json_data .= ',';
                        }
                        $json_data .= '"'.$key.'":"'.$value.'"';
                    }
                }
            }
        }
        
        if ($json_data && $in_object_name) {
            $json_data = '"'.$in_object_name.'":{'.$json_data.'}';
        }
    }
    
    return $json_data;
}

/*******************************************************************/
/**
    \brief Handles no command supplied (error)

    \returns English error string (not XML).
*/
function HandleDefault(
    $in_http_vars   ///< The HTTP GET and POST parameters.
) {
    return "You must supply one of the following: 'switcher=GetSearchResults', 'switcher=GetFormats', 'switcher=GetChanges', 'switcher=GetNAWSDump', 'switcher=GetFieldKeys', 'switcher=GetFieldValues' or 'switcher=GetServiceBodies'.";
}

/*******************************************************************/
/**
    \brief Handles no server available (error).

    \returns null;
*/
function HandleNoServer()
{
    return null;
}

/*******************************************************************/
/**
    \brief Translates CSV to JSON.

    \returns a JSON string, with all the data in the CSV.
*/
function TranslateToJSON( $in_csv_data ///< An array of CSV data, with the first element being the field names.
                        )
{
    $temp_keyed_array = array();
    $in_csv_data = explode("\n", $in_csv_data);
    $keys = array_shift($in_csv_data);
    $keys = explode("\",\"", trim($keys, '"'));
    
    foreach ($in_csv_data as $row) {
        if ($row) {
            $line = null;
            $index = 0;
            $row = explode('","', trim($row, '",'));
            foreach ($row as $column) {
                if (isset($column)) {
                    $key = $keys[$index++];
                    $value = str_replace("\t", ',', trim($column));
                    
                    if ($key == "json_data") {
                        $value = trim($value, '"');
                        $value = str_replace('&quot;', '"', $value);
                    }
                    
                    $line[$key] = $value;
                }
            }
            array_push($temp_keyed_array, $line);
        }
    }
    
    $out_json_data = str_replace('\\\\"', '"', array2json($temp_keyed_array)); // HACK ALERT: TranslateToJSON does whacky things with my escaped quotes, so I undo that here.

    return $out_json_data;
}

/*******************************************************************/
/**
    \brief Translates CSV to XML.

    \returns an XML string, with all the data in the CSV.
*/
function TranslateToXML(   $in_csv_data        ///< An array of CSV data, with the first element being the field names.
                        )
{
    $temp_keyed_array = array();
    $in_csv_data = explode("\n", $in_csv_data);
    $keys = array_shift($in_csv_data);
    $keys = rtrim(ltrim($keys, '"'), '",');
    $keys = preg_split('/","/', $keys);
    
    foreach ($in_csv_data as $row) {
        if ($row) {
            $line = null;
            $index = 0;
            $row_t = rtrim(ltrim($row, '"'), '",');
            $row_t = preg_split('/","/', $row_t);
            foreach ($row_t as $column) {
                if (isset($column)) {
                    $line[$keys[$index++]] = trim($column);
                }
            }
            array_push($temp_keyed_array, $line);
        }
    }

    $out_xml_data = array2xml($temp_keyed_array, 'not_used', false);
    // HACK ALERT: Undoing the poopiness done by TranslateToXML.
    $out_xml_data = str_replace("&aamp;quot;", "&quot;", $out_xml_data);
    $out_xml_data = str_replace("&amp;quot;", "&quot;", $out_xml_data);
    $out_xml_data = str_replace("\\&quot;", "&quot;", $out_xml_data);
    $out_xml_data = str_replace("&ququot;", "&quot;", $out_xml_data);

    return $out_xml_data;
}
