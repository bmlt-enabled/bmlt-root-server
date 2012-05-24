<?php
/****************************************************************************************//**
* \file GetSearchResults.php																*
* \brief Returns an XML response, containing the schema for the GetSearchResults XML call.	*
* The schema is adaptive, and will form itself to the data structure of the root server.	*

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

define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
$file_dir = str_replace ( '/client_interface/xsd', '', dirname ( __FILE__ ) ).'/server/c_comdef_server.class.php';
require_once ( $file_dir );
$server = c_comdef_server::MakeServer();
$ret = null;

if ( $server instanceof c_comdef_server )
	{
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
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
	xmlns:xsn="http://<?php echo $_SERVER['SERVER_NAME'] ?>"
	targetNamespace="http://<?php echo $_SERVER['SERVER_NAME'] ?>"
	elementFormDefault="qualified">
	<xs:element name="meetings">
		<xs:complexType>
			<xs:sequence>
				<xs:element maxOccurs="unbounded" name="row">
					<xs:complexType mixed="true">
						<xs:sequence id="data_item_names">
<?php
							$keys = c_comdef_meeting::GetAllMeetingKeys();
							
							foreach ( $keys as $key )
								{
								echo "\t\t\t\t\t\t\t<xs:element minOccurs=\"0\" maxOccurs=\"1\" name=\"".htmlspecialchars ( $key )."\" type=\"xs:";
								switch ( $key )
									{
									case	'weekday_tinyint':
									case	'service_body_bigint':
									case	'shared_group_id_bigint':
									case	'id_bigint':
										echo "integer";
									break;
									
									case	'start_time':
									case	'duration_time':
										echo "time";
									break;
									
									case	'longitude':
									case	'latitude':
									case	'distance_in_km':
									case	'distance_in_miles':
										echo "decimal";
									break;
									
									default:
										echo "string";
									break;
									}
								
								echo "\"/>\n";
								}
?>
						</xs:sequence>
                        <xs:attribute name="id" use="required" type="xs:integer"/>
					</xs:complexType>
				</xs:element>
                <xs:element name="formats" minOccurs="0">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="row" maxOccurs="unbounded">
                                <xs:complexType>
                                    <xs:sequence>
                                        <xs:element name="key_string" type="xs:NCName"/>
                                        <xs:element name="name_string" type="xs:string"/>
                                        <xs:element name="description_string" type="xs:string"/>
                                        <xs:element name="lang" type="xs:NCName"/>
                                        <xs:element name="id" type="xs:integer"/>
                                    </xs:sequence>
                                    <xs:attribute name="id" use="required" type="xs:integer"/>
                                </xs:complexType>
                            </xs:element>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
			</xs:sequence>
		</xs:complexType>
	</xs:element>
</xs:schema><?php
	ob_end_flush();
	}
else
	{
	echo ( 'No Server' );
	}
?>
