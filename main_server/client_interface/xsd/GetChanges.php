<?php
/****************************************************************************************//**
* \file GetLangs.php																		*
* \brief Returns an XML response, containing the schema for the GetLangs XML call			*

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
	<xs:element name="changes">
		<xs:complexType>
			<xs:sequence>
				<xs:element name="row" minOccurs="0" maxOccurs="unbounded">
					<xs:complexType mixed="true">
						<xs:sequence>
							<xs:element minOccurs="0" maxOccurs="1" name="date_int" type="xs:integer"/>
							<xs:element minOccurs="0" maxOccurs="1" name="date_string" type="xs:string"/>
							<xs:element minOccurs="0" maxOccurs="1" name="change_type" type="xs:NCName"/>
							<xs:element minOccurs="0" maxOccurs="1" name="meeting_id" type="xs:integer"/>
							<xs:element minOccurs="0" maxOccurs="1" name="meeting_name" type="xs:string"/>
							<xs:element minOccurs="0" maxOccurs="1" name="user_id" type="xs:integer"/>
							<xs:element minOccurs="0" maxOccurs="1" name="user_name" type="xs:string"/>
							<xs:element minOccurs="0" maxOccurs="1" name="service_body_id" type="xs:integer"/>
							<xs:element minOccurs="0" maxOccurs="1" name="service_body_name" type="xs:string"/>
                            <xs:element name="meeting_exists" minOccurs="0" maxOccurs="1">
                                <xs:simpleType>
                                    <xs:restriction base="xs:integer">
                                        <xs:enumeration value="0"/>
                                        <xs:enumeration value="1"/>
                                    </xs:restriction>
                                </xs:simpleType>
                            </xs:element>
							<xs:element minOccurs="0" maxOccurs="1" name="details" type="xs:string"/>
						</xs:sequence>
                        <xs:attribute name="id" use="required" type="xs:integer"/>
					</xs:complexType>
				</xs:element>
			</xs:sequence>
		</xs:complexType>
	</xs:element>
</xs:schema>
<?php ob_end_flush(); ?>
