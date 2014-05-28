<?php
/***********************************************************************/
/** 	\file	contact.php

	\brief	This file is a very simple interface for contacts related to meetings.
	        Only 3 inputs are provided: The meeting ID (an integer),
	        the from address (a string), and the message (a string).
	        This comes via GET, not POST.
	        
	        There is never any writing to the database (security). The database is only checked for the contact info.
	        
	        This file makes sure that email contacts are allowed, then does some basic
	        spam-checking. It will send an email to whatever contact is associated
	        with a meeting.
	        
	        The contacts are tiered in this manner:
	            - If a contact is provided for the meeting itself (email_contact field, or contact_email_1), then that contact is used.
	            - If there are multiple contacts using the default contact structure (contact_email_1, contact_email_2), then we will send to both of them.
	            - If no individual contacts are provided for a meeting, then we will use the email contact for the Service body for that meeting.
	            - If no Service body contact is provided, then the email will be sent to the Server Administrator.
	            - If no email contacts are provided anywhere, the email will not be sent.
	            
	        A simple integer response is returned. 1, if the email was successfully sent, 0 if email contacts are disallowed, -1, if no email contacts are provided, and -2 if the email was flagged as spam.
            
            If the meeting ID is 0 (or there is no input), then the message text and from are ignored, and this is considered a test to see if email is supported. A response of 1 is yes, 0, otherwise.
            
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
*/
/***********************************************************************/
/** \brief This analyzes an input string for obvious spam signatures (mostly checking for URLs).
           This is VERY basic, but it will catch 99% of the usual spam types.

    \returns a Boolean. TRUE if the message appears to be spam.
*/
function analyzeMessageContent ( $inMessage ///< The message as a text string.
                                )
{
    $ret = FALSE;
    
    $matches = array();
    
    // Start by searching for URIs.
    // A URI is 2 or more alpha characters, followed by a colon, followed by one (or more) forward-slash, followed by more text.
    $count = preg_match ( "|[a-z]{2}\:\/+?[a-z\.\-]|", strtolower ( $inMessage ), &$matches );
    
    // If we got a URI, then we look at it a bit closer.
    if ( $count && is_array ( $matches ) && count ( $matches ) )
        {
        if ( $count > 2 )   // More that two is auto-spam.
            {
            $ret = TRUE;
            }
        else
            {
            }
        }
    
    return $ret;
}

/***********************************************************************/
/*                             MAIN CONTEXT                            */
/***********************************************************************/

$ret = 0;   // We start off assuming that email contact is disabled.
$meeting_id = 0;

if ( isset ( $_GET['meeting_id'] ) )
    {
    $meeting_id = intval ( $_GET['meeting_id'] );
    }

if ( isset ( $_GET['message_text'] ) )
    {
    $message_text = $_GET['message_text'];
    }

if ( isset ( $_GET['from_address'] ) )
    {
    $from_address = $_GET['from_address'];
    }

$isspam = isset ( $_GET['to_address'] ) || isset ( $_GET['cc_address'] ) || isset ( $_GET['bc_address'] ) || isset ( $_GET['to'] ) || isset ( $_GET['TO'] ) || isset ( $_GET['To'] ) || isset ( $_GET['Cc'] ) || isset ( $_GET['cc'] ) || isset ( $_GET['BC'] ) || isset ( $_GET['bc'] ) || isset ( $_GET['Bc'] );

if ( !$isspam )
    {
    $isspam = (0 = strpos ( "\r", strtolower ( $from_address ) )) && (0 = strpos ( "\n", strtolower ( $from_address ) )) && (0 = strpos ( ";", strtolower ( $from_address ) )) && (0 = strpos ( "to:", strtolower ( $from_address ) )) && (0 = strpos ( "cc:", strtolower ( $from_address ) )) && (0 = strpos ( "bc:", strtolower ( $from_address ) ));
    
    if ( !$isspam )
        {
        $isspam = analyzeMessageContent ( $message_text );
        
        if ( !$isspam )
            {
            define ( 'BMLT_EXEC', 1 );

            // We check to make sure that we are supporting the capability.
            require_once ( dirname ( dirname ( dirname ( __FILE__ )  ) ).'/auto-config.inc.php');

            if ( $g_enable_email_contact && $meeting_id )
                {
                require_once ( dirname ( dirname ( __FILE__ ) ).'/server/c_comdef_server.class.php');
                $server = c_comdef_server::MakeServer();
    
                if ( $server instanceof c_comdef_server )
                    {
                    $email_contact = NULL;  // This will contain our meeting email contact.
        
                    $meeting_object = c_comdef_server::GetOneMeeting ( $meeting_id );
        
                    if ( $meeting_object instanceof c_comdef_meeting )  // We must have a valid meeting.
                        {
                        }
                    }
                }

            // If this is just a test, we respond with the capability.
            if ( 0 ==  $meeting_id )
                {
                $ret = $g_enable_email_contact ? 1 : 0;
                }
            }
        else
            {
            $ret = -2;
            }
        }
    else
        {
        $ret = -2;
        }
    }
else
    {
    $ret = -2;
    }

echo intval ( $ret );
?>