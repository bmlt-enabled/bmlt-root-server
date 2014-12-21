<?php
/****************************************************************************************//**
* \file GetChanges.php																		*
* \brief Returns an XML response, containing the schema for the switcher=GetChanges XML call			*

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
if ( isset ( $_GET['compress_xml'] ) || isset ( $_POST['compress_xml'] ) )
	{
    if ( zlib_get_coding_type() === false )
        {
        ob_start("ob_gzhandler");
        }
    else
        {
        header ( 'Content-Type:application/xml; charset=UTF-8' );
        ob_start();
        }
	}
else
	{
	header ( 'Content-Type:application/xml; charset=UTF-8' );
	ob_start();
	}
echo "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">"; ?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
	xmlns:xsn="http://<?php echo $_SERVER['SERVER_NAME'] ?>"
	targetNamespace="http://<?php echo $_SERVER['SERVER_NAME'] ?>"
	elementFormDefault="qualified">
    <xs:element name='service_bodies'>
        <xs:complexType>
            <xs:sequence>
                <xs:element maxOccurs='unbounded' ref='service_body'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    <xs:element name='service_body'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='description'/>
                <xs:element ref='uri'/>
                <xs:element ref='parent_service_body'/>
                <xs:element ref='service_body_type'/>
                <xs:element ref='contact_email'/>
                <xs:element ref='principal_user'/>
                <xs:element ref='guest_editors'/>
            </xs:sequence>
            <xs:attribute name='id' use='required' type='xs:integer'/>
            <xs:attribute name='name' use='required'/>
            <xs:attribute name='type' use='required' type='xs:NCName'/>
        </xs:complexType>
    </xs:element>
    <xs:element name='description' type='xs:string'/>
    <xs:element name='uri' type='xs:anyURI'/>
    <xs:element name='parent_service_body'>
        <xs:complexType>
            <xs:attribute name='id' use='required' type='xs:integer'/>
            <xs:attribute name='name' use='required'/>
            <xs:attribute name='type' use='required'/>
        </xs:complexType>
    </xs:element>
    <xs:element name='service_body_type' type='xs:string'/>
    <xs:element name='contact_email' type='xs:string'/>
    <xs:element name='principal_user'>
        <xs:complexType mixed='true'>
            <xs:attribute name='id' use='required' type='xs:integer'/>
        </xs:complexType>
    </xs:element>
    <xs:element name='guest_editors'>
        <xs:complexType>
            <xs:sequence>
                <xs:element minOccurs='0' ref='meeting_list_editors'/>
                <xs:element minOccurs='0' ref='observers'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    <xs:element name='meeting_list_editors'>
        <xs:complexType>
            <xs:sequence>
                <xs:element maxOccurs='unbounded' ref='editor'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    <xs:element name='observers'>
        <xs:complexType>
            <xs:sequence>
                <xs:element ref='editor'/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    <xs:element name='editor'>
        <xs:complexType mixed='true'>
            <xs:attribute name='id' use='required' type='xs:integer'/>
        </xs:complexType>
    </xs:element>
</xs:schema>