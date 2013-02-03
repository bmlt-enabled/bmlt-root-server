<?php
/***********************************************************************/
/** \file	c_comdef_login.php

	\brief	This presents a login dialog to the user, vets the login,
	and establishes a session.
	
	We track admin logins through the use of PHP sessions. This allows the
	login data to remain on the server. It also allows the login data to
	be carried into AJAX calls.
	
	We're pretty anal about checking user credentials. We don't allow any
	changes to happen to the DB unless the user has been vetted at the
	time the DB access is made, but we do a lot of checking along the way.
	
	If you include this file at the top of any file that does admin, it will
	check the session. If the session is not there, it will replace the
	output with a login form, and will continue along the way, once the user
	has logged in. If the session is set, it simply makes sure that the
	session reflects a user that has a system login (it does not check the
	user level), and stays out of the way.
	
	If the user authentication fails, then it does a PHP die(), and scrags
	the whole thing. This prevents execution of any code beyond the bare
	minimum necessary to authenticate.
	
	Cookies and JavaScript (and AJAX) are required to administer the server
	(but not to use it as a regular site visitor). This form checks to see
	if JavaScript is enabled, and if cookies are enabled.
	
	You should link to the c_comdef_login.css file for this form.

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
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

require_once ( dirname ( __FILE__ )."/../server/c_comdef_server.class.php" );
require_once ( dirname ( __FILE__ )."/../server/shared/classes/comdef_utilityclasses.inc.php" );

include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );

$t_server = c_comdef_server::MakeServer();	// We initialize the server.

$lang_enum = $t_server->GetServer()->GetLocalLang();

if ( isset ( $_GET['lang_enum'] ) && $_GET['lang_enum'] )
	{
	$lang_enum = $_GET['lang_enum'];
	}

if ( isset ( $_POST['lang_enum'] ) && $_POST['lang_enum'] )
	{
	$lang_enum = $_POST['lang_enum'];
	}

if ( !isset ( $_SESSION ) )
	{
	session_start();
	}
// See if we are logging in
if ( isset ( $_GET['admin_action'] ) && ($_GET['admin_action'] == 'login') )
	{
	// Belt and suspenders -nuke the stored login.
	$_SESSION[$admin_session_name] = null;
	unset ( $_SESSION[$admin_session_name] );
	// Check to see if the temporary cookie set by the form is there.
	if ( !isset ( $_COOKIE['comdef_test']) ||  !$_COOKIE['comdef_test'] )
		{	
		$localized_strings = c_comdef_server::GetLocalStrings();
		
		c_comdef_LogoutUser();
		
		die ( '<div class="c_comdef_not_auth_container_div"><div class="c_comdef_not_auth_div"><h1 class="c_comdef_not_auth_1">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['cookie'] ).'</h1></div></div></body></html>' );
		}
	
	$login = isset ( $_POST['c_comdef_admin_login'] ) ? $_POST['c_comdef_admin_login'] : null;
	
	// If this is a valid login, we'll get an encrypted password back.
	$enc_password = isset ( $_POST['c_comdef_admin_password'] ) ? $t_server->GetEncryptedPW ( $login, trim ( $_POST['c_comdef_admin_password'] ) ) : null;
	if ( null != $enc_password )
		{
		$_SESSION[$admin_session_name] = "$login\t$enc_password";
		}
	else
		{
		// Otherwise, we just check to make sure this is a kosher user.
		$user_obj = $t_server->GetCurrentUserObj();
		if ( !($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) )
			{
			// Get the display strings.
			$localized_strings = c_comdef_server::GetLocalStrings();
		
			c_comdef_LogoutUser();

			// If the login is invalid, we terminate the whole kit and kaboodle, and inform the user they are persona non grata.
			die ( '<h2 class="c_comdef_not_auth_3">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['not_auth_3'] ).'</h2>'.c_comdef_LoginForm($server).'</body></html>' );
			}
		}
	
	// Make sure these get wiped and deleted.
	$_POST['admin_action'] = null;
	$_POST['c_comdef_admin_login'] = null;
	$_POST['c_comdef_admin_password'] = null;
	// Shouldn't have GET, but what the hell...
	$_GET['admin_action'] = null;
	$_GET['c_comdef_admin_login'] = null;
	$_GET['c_comdef_admin_password'] = null;
	// Belt and suspenders -we set them to naught, then unset them.
	unset ( $_POST['admin_action'] );
	unset ( $_POST['c_comdef_admin_login'] );
	unset ( $_POST['c_comdef_admin_password'] );
	unset ( $_GET['admin_action'] );
	unset ( $_GET['c_comdef_admin_login'] );
	unset ( $_GET['c_comdef_admin_password'] );
	}
	
// See if a session has been started, or a login was attempted.
if ( isset ( $_SESSION[$admin_session_name] ) )
	{
	// We double-check, and see if the user is valid.
	$user_obj = $t_server->GetCurrentUserObj();
	if ( !($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) )
		{
		// Get the display strings.
		$localized_strings = c_comdef_server::GetLocalStrings();

		// If the login is invalid, we terminate the whole kit and kaboodle, and inform the user they are persona non grata.
		die ( '<div class="c_comdef_not_auth_container_div"><div class="c_comdef_not_auth_div"><h1 class="c_comdef_not_auth_1">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['not_auth_1'] ).'</h1><h2 class="c_comdef_not_auth_2">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['not_auth_2'] ).'</h2></div></div></body></html>' );
		}
	}
else
	{
	echo c_comdef_LoginForm($t_server);
	}

$t_server = null;
		
/*******************************************************************/
/** \brief	Returns HTML for the login form. If the user is not logged
	in, then they get the form. Otherwise, the login is processed, or
	the user is vetted.

	\returns a string, containing the form HTML.
*/
function c_comdef_LoginForm(	&$in_server	///< A reference to an instance of c_comdef_server
								)
	{	
	include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );

	$http_vars = array_merge ( $_GET, $_POST );
	
	$localized_strings = c_comdef_server::GetLocalStrings();

	$ret = '<div class="c_comdef_admin_login_form_container_div">';
		// If there is no JavaScript, then this message is displayed, and the form will not be revealed.
		$ret .= '<noscript><h1>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['noscript'] ).'</h1></noscript>';
		$ret .= '<form method="post" class="c_comdef_admin_login_form" id="c_comdef_admin_login_form" action="'.c_comdef_htmlspecialchars ( $_SERVER['SCRIPT_NAME'] ).'?supports_ajax=yes&amp;admin_action=login';
			foreach ( $http_vars as $key => $value )
				{
				switch ( $key )
					{
					// Skip these.
					case	'c_comdef_admin_login':
					case	'c_comdef_admin_password':
					case	'admin_action':
					case	'supports_ajax':
					case	'login':
					break;
					
					default:
						// Arrays need to be concatenated strings.
						if ( is_array ( $value ) )
							{
							$value = join ( ",", $value );
							}
	
						$ret .= '&amp;'.c_comdef_htmlspecialchars ( $key ).'='.c_comdef_htmlspecialchars ( $value );
					break;
					}
				}
		$ret .= '">';	// Only the login will go through post.
			$ret .= '<div style="display:none" id="c_comdef_admin_login_form_inner_container_div" class="c_comdef_admin_login_form_inner_container_div">';
				$ret .= '<div class="c_comdef_admin_login_form_line_div">';
				$ret .= '<div class="c_comdef_admin_login_form_prompt">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['title'] ).'</div>';
					$ret .= '<label for="c_comdef_admin_login">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['login'] ).$localized_strings['prompt_delimiter'].'</label>';
					$ret .= '<input id="c_comdef_admin_login" type="text" name="c_comdef_admin_login" value="" />';
				$ret .= '</div>';
				$ret .= '<div class="c_comdef_admin_login_form_line_div">';
					$ret .= '<label for="c_comdef_admin_password">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['password'] ).$localized_strings['prompt_delimiter'].'</label>';
					$ret .= '<input type="password" id="c_comdef_admin_password" name="c_comdef_admin_password" value="" />';
				$ret .= '</div>';
				$ret .= '<div class="c_comdef_admin_login_form_submit_div">';
					$ret .= '<input type="submit" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Login_Form']['button'] ).'" />';
				$ret .= '</div>';
			$ret .= '</div>';
		// This is how we check for JavaScript availability and enabled cookies (Cookies are required for sessions).
		// We reveal the form using JavaScript (It stays invisible if no JS), and we set a transitory cookie with JS to be read upon login.
		$ret .= '</form>
		<script type="text/javascript">
			document.getElementById(\'c_comdef_admin_login_form_inner_container_div\').style.display=\'block\';
			document.getElementById(\'c_comdef_admin_login\').focus();
			document.cookie=\'comdef_test=test\';
		</script>';
	$ret .= '</div>';
	
	return $ret;
	}
?>