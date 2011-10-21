<?php
/****************************************************************************************//**
* \file GetServiceBodies.php																*
* \brief Returns an XML response, containing all the Service Body IDs and names.			*

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
$file_dir = str_replace ( '/client_interface/xml', '', dirname ( __FILE__ ) ).'/server/c_comdef_server.class.php';
require_once ( $file_dir );
$server = c_comdef_server::MakeServer();

if ( $server instanceof c_comdef_server )
	{
	$service_body_array = $server->GetServiceBodyArrayHierarchical();
				
	if ( is_array ( $service_body_array ) && count ( $service_body_array ) )
		{
		$service_body_array = $service_body_array['dependents'];
		
		if ( is_array ( $service_body_array ) && count ( $service_body_array ) )
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
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
			$xsd_uri = 'http://'.htmlspecialchars ( str_replace ( '/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/GetServiceBodies.php' ) );
			echo "<serviceBodies xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsn=\"http://www.w3.org/2001/XMLSchema-instance\" xsn:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
			OutputServiceBody ( $service_body_array );
			echo "</serviceBodies>";
			ob_end_flush();
			}
		else
			{
			echo ( 'No Service Bodies In Dependents List' );
			}
		}
	else
		{
		echo ( 'No Service Bodies' );
		}
	}
else
	{
	echo ( 'No Server' );
	}
	
/****************************************************************************************//**
* \brief Returns an XML response, containing one Service Body ID and name. Will also act as	*
* a recursive function, and will look for nested Service bodies.							*
* \returns A string, the XML for the Service body, and any nested bodies.					*
********************************************************************************************/
function OutputServiceBody (	$in_service_body_array	///< An array of c_comdef_service_body objects, in hierarchical fashion.
							)
{
	foreach ( $in_service_body_array as $service_body_ar )
		{
		if ( isset ( $service_body_ar['object'] ) && ($service_body_ar['object'] instanceof c_comdef_service_body) )
			{
			echo '<serviceBody id="'.intval ( $service_body_ar['object']->GetID() ).'"';
			echo ' sb_name="'.htmlspecialchars ( $service_body_ar['object']->GetLocalName() ).'"';
			echo ' sb_type="'.htmlspecialchars ( $service_body_ar['object']->GetSBType() ).'"';
			
			if ( $service_body_ar['object']->GetLocalDescription() )
			    {
			    echo ' sb_desc="'.htmlspecialchars ( $service_body_ar['object']->GetLocalDescription() ).'"';
			    }
			
			if ( $service_body_ar['object']->GetURI() )
			    {
			    echo ' sb_uri="'.htmlspecialchars ( $service_body_ar['object']->GetURI() ).'"';
			    }
			
			if ( $service_body_ar['object']->GetKMLURI() )
			    {
			    echo ' sb_kmluri="'.htmlspecialchars ( $service_body_ar['object']->GetKMLURI() ).'"';
			    }
			
			if ( isset ( $service_body_ar['dependents'] ) )
				{
				echo ">";
				OutputServiceBody ( $service_body_ar['dependents'] );
				echo "</serviceBody>";
				}
			else
				{
				echo "/>";
				}
			}
		}
}
?>

