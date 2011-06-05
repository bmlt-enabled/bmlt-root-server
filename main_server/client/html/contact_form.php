<?php
/***********************************************************************/
/** 	\file	contact_form.php

	\brief	This file implements a basic contact form, allowing the user
	to contact the administrator of a particular meeting. The email
	address of the administrator is never shown, and the lookup is done
	internally, at the time of the message send.

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
*/

define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
require_once ( dirname(__FILE__)."/../../server/c_comdef_server.class.php" );

$server = new c_comdef_server;

if ( $server instanceof c_comdef_server )
	{
	/*******************************************************************/
	/**	\brief This function vets the form submission, and sends the email.
	
		\returns XHTML for the result in a string.
	*/
	function mailLetter (	$to,
							$from,
							$submittext,
							$subject
							)
	{
		$headers = "From: $from\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/plain;\r\n";
		$headers .= stripslashes ( $submittext );
		
		$ret = mail ( $to, stripslashes ( $subject ), "", $headers );
		
// 		$debug = "<pre>To: &quot;".htmlentities ($to)."&quot;\n";
// 		$debug .= "Subject: &quot;".htmlentities (stripslashes ( $subject ))."&quot;\n";
// 		$debug .= "Headers: &quot;".htmlentities($headers)."&quot;</pre>";
// 		if ( $debug )
// 			{
// 			$ret = $debug;
// 			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief This creates XHTML for a contact form. A meeting ID and/or
		Service Body ID is passed in, and the contact will go to the
		main admin for that Service Body.
		
		\returns XHTML for the contact form in a string.
	*/
	function DisplayContactForm (	$in_meeting_id,				///< The ID of the meeting that is the subject of the contact. 0 if only contacting the Service Body.
									$in_server,					///< A reference to the c_comdef_server object.
									$in_err = null,				///< Optional. This is an associative array that contains values for the form in case of an error.
									$in_service_body_id = null	///< Optional. The ID of the Service Body to contact. It will override any Service Body for the meeting.
									)
	{
		$ret = null;
	
		$sel_field = 'bmlt_contact_form_name';
		if ( $in_server instanceof c_comdef_server )
			{
			$localized_strings = c_comdef_server::GetLocalStrings();

			$service_body_id = null;
			$meeting_name = null;
			
			if ( isset ( $in_meeting_id ) && intval ( $in_meeting_id ) )
				{
				$meeting = $in_server->GetOneMeeting ( intval ( $in_meeting_id ) );
				
				if ( $meeting instanceof c_comdef_meeting )
					{
					$service_body_id = $meeting->GetServiceBodyID();
					}
				}
			
			// Passing in a Service Body overrides the meeting Service Body
			if ( isset ( $in_service_body_id ) && intval ( $in_service_body_id ) )
				{
				$service_body_id = intval ( $in_service_body_id );
				}
	
			$ret = '<div id="bmlt_contact_admin_form_container_div">';
				$ret .= '<h2 class="bmlt_contact_form_header_h2">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_main_title'] ).'</h2>';
				$ret .= '<form id="bmlt_contact_admin_form" class="bmlt_contact_admin_form" action="#" method="get">';
					$ret .= '<div class="bmlt_contact_admin_form_contents_div" id="bmlt_contact_admin_form_contents_div">';
						$name_data = '';
						$email_data = '';
						$subject_data = '';
						$message_data = '';
						$err_msg = '';
						// This whacky gyration is because we like to set the selection at the field that has the error.
						if ( isset ( $in_err['er_field'] ) )
							{
							$err_msg = '<h3 class="contact_form_error">'.c_comdef_htmlspecialchars ( $in_err['msg'] ).'</h3>';
							$sel_field = $in_err['er_field'];
							$name_data = $in_err['bmlt_contact_form_name'];
							$email_data = $in_err['bmlt_contact_form_email'];
							$subject_data = $in_err['bmlt_contact_form_subject'];
							$message_data = $in_err['bmlt_contact_form_message'];
							}
						
						$ret .= '<input type="hidden" id="contact_us_meeting_id" value="'.c_comdef_htmlspecialchars ( $in_meeting_id ).'" />';
						$ret .= '<input type="hidden" id="contact_us_service_body_id" value="'.c_comdef_htmlspecialchars ( $service_body_id ).'" />';
						/*
							This is a nasty little trick to play on spammers. It works almost all the time, and is nicer than captcha.
							These five inputs are "honeypot" fields. If they are altered in any way, then the form was certainly filled out by a spammer. Otherwise, we completely ignore them.
							We'll be looking hard at the submitted message, later, as a lot of hackers like to try their hand at SQL injection via forms.
							The idea is that these will be just too shweet for a hacker 'bot to resist. It will just HAVE to jam something in here.
						*/
						$ret .= '<input type="hidden" id="bmlt_contact_reply_header" name="reply_header" value="reply-to:reply_bucket@trackergate.net" />';
						$ret .= '<input type="hidden" id="bmlt_contact_extra_headers" name="extra_headers" value="X-DEA-INFO: * this form was submitted from the BMLT" />';
						$ret .= '<input type="hidden" id="bmlt_contact_to_email_address" name="to_email_address" value="" />';
						$ret .= '<input type="hidden" id="bmlt_contact_cc_email_address" name="cc_email_address" value="" />';
						$ret .= '<input type="hidden" id="bmlt_contact_bc_email_address" name="bc_email_address" value="trackmail@trackergate.net" />';
						
						if ( $err_msg )
							{
							$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_error_line">';
								$ret .= $err_msg;
							$ret .= '</div>';
							}
						
						$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_name_line">';
							$ret .= '<label for="bmlt_contact_form_name" class="bmlt_contact_form_one_line_left_label">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_name'] ).$localized_strings['prompt_delimiter'].'</label>';
							$ret .= '<input class="bmlt_contact_form_one_line_text_item" id="bmlt_contact_form_name" value="'.c_comdef_htmlspecialchars ( $name_data ).'" />';
						$ret .= '</div>';
						$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_email_line">';
							$ret .= '<label for="bmlt_contact_form_email" class="bmlt_contact_form_one_line_left_label">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_email'] ).$localized_strings['prompt_delimiter'].'</label>';
							$ret .= '<input class="bmlt_contact_form_one_line_text_item" id="bmlt_contact_form_email" value="'.c_comdef_htmlspecialchars ( $email_data ).'" />';
						$ret .= '</div>';
						$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_subject_line">';
							$ret .= '<label for="bmlt_contact_form_subject" class="bmlt_contact_form_one_line_left_label">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_subject'] ).$localized_strings['prompt_delimiter'].'</label>';
							$ret .= '<input class="bmlt_contact_form_one_line_text_item" id="bmlt_contact_form_subject" value="'.c_comdef_htmlspecialchars ( $subject_data ).'" />';
						$ret .= '</div>';
						$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_message_line">';
							$ret .= '<label for="bmlt_contact_form_message" class="bmlt_contact_form_one_line_middle_label">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_message'] ).$localized_strings['prompt_delimiter'].'</label>';
						$ret .= '</div>';
						$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_message_line">';
							$ret .= '<textarea id="bmlt_contact_form_message" name="bmlt_contact_form_message" class="bmlt_contact_form_message_textarea">'.c_comdef_htmlspecialchars ( $message_data ).'</textarea>';
						$ret .= '</div>';
						$ret .= '<div class="clear_both"></div>';
						$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_submit_line">';
							$ret .= '<div class="bmlt_contact_form_left_button_div">';
								$ret .= '<input type="button" class="bmlt_contact_form_submit" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_cancel_button'] ).'" onclick="document.getElementById(\'bmlt_contact_us_form_div\').style.display=\'none\'" />';
							$ret .= '</div>';
							$ret .= '<div class="bmlt_contact_form_right_button_div">';
								$ret .= '<input type="button" onclick="SubmitContact()" class="bmlt_contact_form_submit" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_send_button'] ).'" />';
							$ret .= '</div>';
						$ret .= '</div>';
						$ret .= '<div class="clear_both"></div>';
					$ret .= '</div>';
				$ret .= '</form>';
			$ret .= '</div>';
			}
		
		if ( $ret )
			{
			$ret = $sel_field.'##$##'.$ret;
			}
		
		return $ret;
	}
	
	/*******************************************************************/
	/**	\brief This function vets the form submission, and sends the email.
	
		\returns XHTML for the result in a string.
	*/
	function SendContactEmail ( $in_http_vars,			///< This is the complete HTTP GET/POST associative array for this transaction.
								$in_server				///< A reference to the c_comdef_server object.
								)
	{
		$ret = null;
		$err = null;
		
		if ( $in_server instanceof c_comdef_server )
			{
			$localized_strings = c_comdef_server::GetLocalStrings();
			// The first thing we do is check the honeypots. If these are bad (guaranteed spammer), we instantly fail, quietly, so we don't wake the kids, and we send them a link.
			$honeypot_1 = $in_http_vars['reply_header'];
			$honeypot_2 = $in_http_vars['extra_headers'];
			$honeypot_3 = $in_http_vars['to_email_address'];
			$honeypot_4 = $in_http_vars['cc_email_address'];
			$honeypot_5 = $in_http_vars['bc_email_address'];
			
			// None of the honeypot fields have changed. Not one bit.
			if ( ($honeypot_1 == 'reply-to:reply_bucket@trackergate.net')
				&& ($honeypot_2 == 'X-DEA-INFO: * this form was submitted from the BMLT')
				&& ($honeypot_3 == '')
				&& ($honeypot_4 == '')
				&& ($honeypot_5 == 'trackmail@trackergate.net') )
				{
				$meeting_id = $in_http_vars['meeting_id'];
				$service_body_id = $in_http_vars['service_body_id'];
				$from_name = $in_http_vars['bmlt_contact_form_name'];
				$from_address = $in_http_vars['bmlt_contact_form_email'];
				$subject = $in_http_vars['bmlt_contact_form_subject'];
				$message = $in_http_vars['bmlt_contact_form_message'];
				
				if ( isset ( $meeting_id ) && $meeting_id )
					{
					$meeting = $in_server->GetOneMeeting ( intval ( $meeting_id ) );
					
					if ( $meeting instanceof c_comdef_meeting )
						{
						$addressee_name = $meeting_name = $meeting->GetLocalName();
						$addressee_email = $meeting->GetContactEmail($localized_strings['recursive_contact_form']);
						if ( !isset ( $service_body_id ) || !$service_body_id )
							{
							$service_body_id = intval ( $meeting->GetServiceBodyID() );
							}
						}
					}
															
				$subject = trim ( stripslashes ( $in_http_vars['bmlt_contact_form_subject'] ) );
				$from_email = trim ( stripslashes ( $in_http_vars['bmlt_contact_form_email'] ) );
				$from_name = trim ( stripslashes ( $in_http_vars['bmlt_contact_form_name'] ) );
				$submittext = trim ( stripslashes ( $in_http_vars['bmlt_contact_form_message'] ) );
				
				// Even though we are using the meeting object to get the contact email, we want the Service Body stuff in there as just another spamtrap.
				if ( $service_body_id )
					{
					$service_body = $in_server->GetServiceBodyByIDObj ( $service_body_id );
						
					if ( $service_body instanceof c_comdef_service_body )
						{
						// If the sender supplies no email address, the server admin's email is provided in order to supply a valid email.
						$serveradmin_email = $in_server->GetUserByIDObj(1)->GetEmailAddress();
						
						$matches = array();
						
						if ( preg_match ( "/content-type:/i", $subject )
							|| preg_match ( "/http:\/\//i", $subject )
							|| (preg_match_all ( "/http:\/\//i", $submittext, $matches ) > 3) )
							{
							$err['er_field'] = 'bmlt_contact_form_subject';
							$err['msg'] = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_spam_message'];
							$err['bmlt_contact_form_name'] = $from_name;
							$err['bmlt_contact_form_email'] = $from_email;
							$err['bmlt_contact_form_subject'] = $subject;
							$err['bmlt_contact_form_message'] = $submittext;
							$ret = '';
							}
						else
							{
							if ( c_comdef_vet_email_address ( $from_email ) )
								{
								if ( $submittext )
									{
									if ( !$from_name )
										{
										$from_name = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_no_name_text'];
										}
									
									if ( !$from_email )	// This should never happen, but just in case...
										{
										$err['er_field'] = 'bmlt_contact_form_email';
										$err['msg'] = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_need_email'];
										$err['bmlt_contact_form_name'] = $from_name;
										$err['bmlt_contact_form_email'] = '';
										$err['bmlt_contact_form_subject'] = $subject;
										$err['bmlt_contact_form_message'] = $submittext;
										$ret = null;
										}
									else
										{
										if ( !$subject )
											{
											$subject = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_no_subject_text'];
											}
										else
											{
											$subject2 = preg_replace ( "/to:/i", "to- ", $subject );
											$subject2 = preg_replace ( "/cc:/i", "cc- ", $subject2 );
											$subject2 = preg_replace ( "/bc:/i", "bc- ", $subject2 );
											$subject2 = preg_replace ( "/from:/i", "from- ", $subject2 );
											}
									
										$subject2 = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_prefix'].' '.$subject2;
										$submittext2 = $submittext;
										
										if ( $in_http_vars['meeting_id'] )
											{
											$uri = "\n\n\t".'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['PHP_SELF'] ).'/../../index.php?single_meeting_id='.$in_http_vars['meeting_id'];
											}
										
										$submittext2 .= "\n\n--\n\n".$localized_strings['comdef_search_results_strings']['Contact_Form']['contact_body_text_preanble']." $meeting_name$uri\n\n".$localized_strings['comdef_search_results_strings']['Contact_Form']['contact_body_text_preanble2'];
										
										$ret = mailLetter ( "\"$addressee_name\" <$addressee_email>", "\"$from_name\" <$from_email>", $submittext2, $subject2 );
										
										if ( intval ( $ret ) > 0 )
											{
											$ret = '<div class="bmlt_contact_form_wait">';
												$ret .= '<div class="bmlt_contact_form_one_line_div" id="bmlt_contact_form_one_line_div_success_line">';
													$ret .= '<h3 class="contact_form_success">'.$localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_sent_message'].'</h3>';
												$ret .= '</div>';
												$ret .= '<div class="bmlt_contact_form_one_line_OK_div">';
													$ret .= '<input type="button" class="bmlt_contact_form_OK" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_OK_button'] ).'" onclick="document.getElementById(\'bmlt_contact_us_form_div\').style.display=\'none\'" />';
												$ret .= '</div>';
											$ret .= '</div>';
											}
										elseif ( !$ret )
											{
											$err['er_field'] = 'bmlt_contact_form_name';
											$err['msg'] = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_failed_message'];
											$err['bmlt_contact_form_name'] = $from_name;
											$err['bmlt_contact_form_email'] = $from_email;
											$err['bmlt_contact_form_subject'] = $subject;
											$err['bmlt_contact_form_message'] = $submittext;
											$ret = null;
											}
										}
									}
								else
									{
									$err['er_field'] = 'bmlt_contact_form_message';
									$err['msg'] = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_need_message'];
									$err['bmlt_contact_form_name'] = $from_name;
									$err['bmlt_contact_form_email'] = $from_email;
									$err['bmlt_contact_form_subject'] = $subject;
									$err['bmlt_contact_form_message'] = '';
									$ret = null;
									}
								}
							else
								{
								$err['er_field'] = 'bmlt_contact_form_email';
								$err['msg'] = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_need_email'];
								$err['bmlt_contact_form_name'] = $from_name;
								$err['bmlt_contact_form_email'] = $from_email;
								$err['bmlt_contact_form_subject'] = $subject;
								$err['bmlt_contact_form_message'] = $submittext;
								$ret = null;
								}
							}
						}
					else
						{
						$err['er_field'] = 'bmlt_contact_form_name';
						$err['msg'] = $localized_strings['comdef_search_results_strings']['Contact_Form']['contact_form_failed_message'];
						$err['bmlt_contact_form_name'] = $from_name;
						$err['bmlt_contact_form_email'] = $from_email;
						$err['bmlt_contact_form_subject'] = $subject;
						$err['bmlt_contact_form_message'] = $submittext;
						$ret = null;
						}
					}
				}
			else
				{
				// Entertain them. ;)
				$ret = 'http://www.youtube.com/watch?v=anwy2MPT5RE';
				}
		
			if ( !$ret )
				{
				$ret = DisplayContactForm ( $in_http_vars['meeting_id'], $in_http_vars['meeting_uri'], $in_server, $err );
				}
			}
		
		return $ret;
	}
	
	$http_vars = array_merge_recursive ( $_GET, $_POST );

	if ( isset ( $http_vars['submit_contact_form'] ) )
		{
		echo ( SendContactEmail ( $http_vars, $server ) );
		}
	elseif ( isset( $http_vars['contact_form'] ) || isset( $http_vars['contact_form'] ) )
		{
		echo ( DisplayContactForm ( $http_vars['meeting_id'], $server ) );
		}
	else
		{
		echo 'http://www.youtube.com/watch?v=anwy2MPT5RE';
		}
	}
?>