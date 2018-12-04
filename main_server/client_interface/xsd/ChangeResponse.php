<?php
/****************************************************************************************//**
* \file ChangeResponse.php                                                                  *
* \brief Returns an XML response, containing the schema for the semantic editing XML call   *

    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: http://bmlt.magshare.org

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
<xs:schema xmlns:xs='http://www.w3.org/2001/XMLSchema' xmlns:ns1='http://<?php echo $_SERVER['SERVER_NAME'] ?>' elementFormDefault='qualified' targetNamespace='http://<?php echo $_SERVER['SERVER_NAME'] ?>'>
    <xs:element name='changeResponse'>
        <xs:complexType>
            <xs:sequence>
                <xs:choice>
                    <xs:element ref='ns1:changeMeeting'/>
                    <xs:element ref='ns1:newMeeting'/>
                </xs:choice>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='changeMeeting'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='ns1:field'/>
            </xs:sequence>
            <xs:attribute name='id' use='required' type='xs:integer'/>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='newMeeting'>
        <xs:complexType>
            <xs:sequence>
                <xs:element maxOccurs='unbounded' ref='ns1:field'/>
            </xs:sequence>
            <xs:attribute name='id' use='required' type='xs:integer'/>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='field'>
        <xs:complexType>
            <xs:sequence>
                <xs:element name='oldValue' type='xs:string'/>
                <xs:element name='newValue' type='xs:string'/>
            </xs:sequence>
            <xs:attribute name='key' use='required' type='xs:NCName'/>
        </xs:complexType>
    </xs:element>
    
</xs:schema><?php ob_end_flush(); ?>
