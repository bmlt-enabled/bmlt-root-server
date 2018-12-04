<?php
/****************************************************************************************//**
* \file GetFieldValues.php                                                                  *
* \brief Returns an XML response, containing the schema for the Standard field values call  *

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
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified"
    xmlns:xsn="http://<?php echo $_SERVER['SERVER_NAME'] ?>"
    targetNamespace="http://<?php echo $_SERVER['SERVER_NAME'] ?>">
    <xs:element name='fields'>
        <xs:complexType>
            <xs:sequence>
                <xs:element name='row' maxOccurs="unbounded">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name='location_municipality' type='xs:string'/>
                            <xs:element name='ids' type='xs:string'/>
                        </xs:sequence>
                        <xs:attribute name='sequence_index' use='required' type='xs:integer'/>
                    </xs:complexType>
                </xs:element>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema><?php ob_end_flush(); ?>

