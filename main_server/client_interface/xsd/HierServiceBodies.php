<?php
/****************************************************************************************//**
* \file HierServiceBodies.php                                                                   *
* \brief Returns an XML response, containing the schema for the Admin Service body info call.   *

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
    <xs:element name='service_bodies'>
        <xs:complexType>
            <xs:sequence>
                <xs:element minOccurs='1' maxOccurs='unbounded' ref='ns1:service_body'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='service_body'>
        <xs:complexType>
            <xs:sequence>
                <xs:element name='service_body_type' type='xs:string' minOccurs='1' maxOccurs='1'/>
                <xs:element name='description' type='xs:string' minOccurs='0' maxOccurs='1'/>
                <xs:element name='uri' type='xs:anyURI' minOccurs='0' maxOccurs='1'/>
                <xs:element name='helpline' type='xs:string' minOccurs='0' maxOccurs='1'/>
                <xs:element ref='ns1:parent_service_body' minOccurs='0' maxOccurs='1'/>
                <xs:element name='contact_email' type='xs:string' minOccurs='0' maxOccurs='1'/>
                <xs:element ref='ns1:editors' minOccurs='1' maxOccurs='1'/>
                <xs:choice>
                    <xs:element ref='ns1:children' minOccurs='0' maxOccurs='1'/>
                    <xs:element ref='ns1:service_bodies' minOccurs='0' maxOccurs='1'/>
                </xs:choice>
            </xs:sequence>
            <xs:attribute name='id' use='required' type='xs:short'/>
            <xs:attribute name='name' use='required' type='xs:string'/>
            <xs:attribute name='type' use='required' type='xs:string'/>
        </xs:complexType>
    </xs:element>

    <xs:element name='editors'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='ns1:service_body_editors' minOccurs='1' maxOccurs='1'/>
                <xs:element ref='ns1:meeting_list_editors' minOccurs='0' maxOccurs='1'/>
                <xs:element ref='ns1:observers' minOccurs='0' maxOccurs='1'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='service_body_editors'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='ns1:editor' minOccurs='1' maxOccurs='unbounded'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='meeting_list_editors'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='ns1:editor' minOccurs='1' maxOccurs='unbounded'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>

    <xs:element name='children'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='ns1:child_service_body' minOccurs='1' maxOccurs='unbounded'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='observers'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='ns1:editor' minOccurs='1' maxOccurs='unbounded'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='editor'>
        <xs:complexType>
            <xs:attribute name='id' use='required' type='xs:short'/>
            <xs:attribute name='admin_type' use='required' type='xs:string'/>
            <xs:attribute name='admin_name' use='required' type='xs:string'/>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='parent_service_body'>
        <xs:complexType>
            <xs:simpleContent>
                <xs:restriction base="xs:anyType">
                    <xs:simpleType>
                        <xs:restriction base="xs:string">
                            <xs:minLength value="1" />
                        </xs:restriction>
                    </xs:simpleType>
                    <xs:attribute name='id' use='required' type='xs:short'/>
                    <xs:attribute name='type' use='required' type='xs:string'/>
                </xs:restriction>
            </xs:simpleContent>
        </xs:complexType>
    </xs:element>
    
    <xs:element name='child_service_body'>
        <xs:complexType>
            <xs:simpleContent>
                <xs:restriction base="xs:anyType">
                    <xs:simpleType>
                        <xs:restriction base="xs:string">
                            <xs:minLength value="1" />
                        </xs:restriction>
                    </xs:simpleType>
                    <xs:attribute name='id' use='required' type='xs:short'/>
                    <xs:attribute name='type' use='required' type='xs:string'/>
                </xs:restriction>
            </xs:simpleContent>
        </xs:complexType>
    </xs:element>
</xs:schema><?php ob_end_flush(); ?>
