<?php
/****************************************************************************************//**
* \file ServerInfo.php                                                                      *
* \brief Returns an XML response, containing the schema for the GetServerInfo XML call          *

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
********************************************************************************************/

// The caller can request compression. Not all clients can deal with compressed replies.
if (isset($_GET['compress_xml']) || isset($_POST['compress_xml'])) {
    if (zlib_get_coding_type() === false) {
        ob_start("ob_gzhandler");
    } else {
        header('Content-Type:application/xml; charset=UTF-8');
        ob_start();
    }
} else {
    header('Content-Type:application/xml; charset=UTF-8');
    ob_start();
}
echo "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n"; ?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xmlns:xsn="http://<?php echo $_SERVER['SERVER_NAME'] ?>"
    targetNamespace="http://<?php echo $_SERVER['SERVER_NAME'] ?>"
    elementFormDefault="qualified">
    <xs:element name='serverInfo'>
        <xs:complexType>
            <xs:sequence>
                <xs:element name='row'>
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name='version' type='xs:string'/>
                            <xs:element name='versionInt' type='xs:integer'/>
                            <xs:element name='langs' type='xs:string'/>
                            <xs:element name='nativeLang' type='xs:string'/>
                            <xs:element name='centerLongitude' type='xs:decimal'/>
                            <xs:element name='centerLatitude' type='xs:decimal'/>
                            <xs:element name='centerZoom' type='xs:integer'/>
                            <xs:element minOccurs="0" name='defaultDuration' type='xs:string'/>
                            <xs:element minOccurs="0" name='regionBias' type='xs:string'/>
                            <xs:element minOccurs="0" name='charSet' type='xs:string'/>
                            <xs:element minOccurs="0" name='distanceUnits' type='xs:string'/>
                            <xs:element minOccurs="0" name='semanticAdmin' type='xs:integer'/>
                            <xs:element minOccurs="0" name='emailEnabled' type='xs:integer'/>
                            <xs:element minOccurs="0" name='emailIncludesServiceBodies' type='xs:integer'/>
                            <xs:element minOccurs="0" name='changesPerMeeting' type='xs:integer'/>
                            <xs:element minOccurs="0" name='meeting_states_and_provinces' type='xs:string'/>
                            <xs:element minOccurs="0" name='meeting_counties_and_sub_provinces' type='xs:string'/>
                            <xs:element minOccurs="0" name='available_keys' type='xs:string'/>
                            <xs:element minOccurs="0" name='google_api_key' type='xs:string'/>
                            <xs:element minOccurs="0" name='dbVersion' type='xs:integer'/>
                            <xs:element minOccurs="0" name='dbPrefix' type='xs:string'/>
                            <xs:element minOccurs="0" name='meeting_time_zones_enabled' type='xs:string'/>
                        </xs:sequence>
                        <xs:attribute name='sequence_index' use='required' type='xs:integer'/>
                    </xs:complexType>
                </xs:element>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema><?php ob_end_flush(); ?>
