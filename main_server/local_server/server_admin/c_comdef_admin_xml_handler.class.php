<?php
/*
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
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.
/***********************************************************************************************************//**
    \class c_comdef_admin_xml_handler
    \brief Controls handling of the admin semantic interface.
    
            This class should not even be instantiated unless the user has been authorized, and an authorized seesion
            is in progress.
***************************************************************************************************************/
class c_comdef_admin_xml_handler
{
    var $http_vars;                     ///< This will hold the combined GET and POST parameters for this call.
    var $server;                        ///< The BMLT server model instance.
    var $my_localized_strings;          ///< An array of localized strings.
    
    /********************************************************************************************************//**
    \brief The class constructor.
    ************************************************************************************************************/
    function __construct (  $in_http_vars,        ///< The combined GET and POST parameters.
                            $in_server            ///< The BMLT server instance.
                        )
    {
        $this->http_vars = $in_http_vars;
        $this->server = $in_server;
        $this->my_localized_strings = c_comdef_server::GetLocalStrings();
    }
    
    /********************************************************************************************************//**
    \brief This is called to process the input and generate the output. It is the "heart" of the class.
    
    \returns XML to be returned.
    ************************************************************************************************************/
    function process_commands()
    {
        $ret = NULL;
        // We make sure that we are allowed to access this level of functionality.
        // This is "belt and suspenders." We will constantly check user credentials.
        $user_obj = $this->server->GetCurrentUserObj();
        if ( isset ( $user_obj ) && ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($user_obj->GetID() > 1) )
            {
            if ( isset ( $this->http_vars['admin_action'] ) && trim ( $this->http_vars['admin_action'] ) )
                {
                switch ( strtolower ( trim ( $this->http_vars['admin_action'] ) ) )
                    {
                    case 'get_permissions':
                        $ret = $this->process_capabilities_request();
                    break;
                    
                    case 'get_service_body_info':
                        if ( isset ( $this->http_vars['sb_id'] ) && $this->http_vars['sb_id'] )
                            {
                            $ret = $this->process_service_body_info_request();
                            }
                    break;
                
                    default:
                        $ret = '<h1>BAD ADMIN ACTION</h1>';
                    break;
                    }
                }
            else
                {
                $ret = '<h1>BAD ADMIN ACTION</h1>';
                }
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to return Service Body information.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_service_body_info_request()
    {
        $service_body_id = intval ( trim ( $this->http_vars['sb_id'] ) );   // The user needs to specify the BMLT ID of the Service body.
        $ret = '';
        // Belt and suspenders. We need to make sure the user is authorized.
        $user_obj = $this->server->GetCurrentUserObj();
        if ( isset ( $user_obj ) && ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($user_obj->GetID() > 1) )
            {
            $service_body = $this->server->GetServiceBodyByIDObj ( $service_body_id );
            
            if ( isset ( $service_body ) && ($service_body instanceof c_comdef_service_body) )
                {
                // Everyone gets the type, URI, name, description and parent Service body.
                $name = $service_body->GetLocalName();
                $description = $service_body->GetLocalDescription();
                $uri = $service_body->GetURI();
                $type = $service_body->GetSBType();

                $parent_service_body_id = -1;
                $parent_service_body_name = "";
                $parent_service_body_type = "";

                $parent_service_body = $service_body->GetOwnerIDObject();
                
                if ( isset ( $parent_service_body ) && $parent_service_body )
                    {
                    $parent_service_body_id = intval ( $parent_service_body->GetID() );
                    $parent_service_body_name = $parent_service_body->GetLocalName();
                    $parent_service_body_type = $parent_service_body->GetSBType();
                    }
                
                // These need more permission.
                $contact_email = NULL;
                $guest_editors = NULL;
                $meeting_list_editors = array();
                $observers = array();
                $principal_user = NULL;
                
                // See if we have rights to edit this Service body. Just for the heck of it, we check the user level (not really necessary, but belt and suspenders).
                $this_user_can_edit_the_body = ($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) && $service_body->UserCanEdit();
                
                // Service Body Admins (with permission for the body) get more info.
                if ( $this_user_can_edit_the_body )
                    {
                    $contact_email = $service_body->GetContactEmail();
                    $guest_editors = $service_body->GetEditorsAsObjects();
                    foreach ( $guest_editors as $editor )
                        {
                        if ( $service_body->UserCanEditMeetings ( $editor ) )
                            {
                            array_push ( $meeting_list_editors, $editor );
                            }
                        elseif ( $service_body->UserCanObserve ( $editor ) )
                            {
                            array_push ( $observers, $editor );
                            }
                        }
                    $principal_user = $service_body->GetPrincipalUserObj();
                    }
                    
                // At this point, we have all the information we need to build the response XML.
                $ret = '<service_body id="'.c_comdef_htmlspecialchars ( $service_body_id ).'" name="'.c_comdef_htmlspecialchars ( $name ).'" type="'.c_comdef_htmlspecialchars ( $type ).'">';
                    $ret .= '<description>'.c_comdef_htmlspecialchars ( $description ).'</description>';
                    $ret .= '<uri>'.c_comdef_htmlspecialchars ( $uri ).'</uri>';
                    $ret .= '<parent_service_body name="'.c_comdef_htmlspecialchars ( $parent_service_body_name ).'" id="'.intval ( $parent_service_body_id ).'" type="'.c_comdef_htmlspecialchars ( $parent_service_body_type ).'"/>';
                    $ret .= '<service_body_type>'.c_comdef_htmlspecialchars ( $this->my_localized_strings['service_body_types'][$type] ).'</service_body_type>';
                    if ( $this_user_can_edit_the_body )
                        {
                        $ret .= '<contact_email>'.c_comdef_htmlspecialchars ( $contact_email ).'</contact_email>';
                        $ret .= '<principal_user id="'.intval ( $principal_user->GetID() ).'">'.c_comdef_htmlspecialchars ( $principal_user->GetLocalName() ).'</principal_user>';
                        if ( (isset ( $meeting_list_editors ) && is_array ( $meeting_list_editors ) && count ( $meeting_list_editors )) || (isset ( $observers ) && is_array ( $observers ) && count ( $observers )) )
                            {
                            $ret .= '<guest_editors>';
                                if ( isset ( $meeting_list_editors ) && is_array ( $meeting_list_editors ) && count ( $meeting_list_editors ) )
                                    {
                                    $ret .= '<meeting_list_editors>';
                                        foreach ( $meeting_list_editors as $editor )
                                            {
                                            $ret .= '<editor id="'.intval ( $editor->GetID() ).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                            }
                                    $ret .= '</meeting_list_editors>';
                                    }
                                    
                                if ( isset ( $observers ) && is_array ( $observers ) && count ( $observers ) )
                                    {
                                    $ret .= '<observers>';
                                        foreach ( $observers as $editor )
                                            {
                                            $ret .= '<editor id="'.intval ( $editor->GetID() ).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                            }
                                    $ret .= '</observers>';
                                    }
                            $ret .= '</guest_editors>';
                            }
                        }
                $ret .= '</service_body>';
                }
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to report the rights for the logged-in user.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_capabilities_request()
    {
        $ret = '';
        $service_bodies = $this->server->GetServiceBodyArray();
        
        // We will fill these three arrays, depending on the users' rights for a given Service body.
        $my_meeting_observer_service_bodies = array();
        $my_meeting_editor_service_bodies = array();
        $my_editable_service_bodies = array();
        
        $user_obj = $this->server->GetCurrentUserObj();
        if ( isset ( $user_obj ) && ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($user_obj->GetID() > 1) )
            {
            // We cycle through all the Service bodies, and look for ones in which we have permissions.
            // We use the Service body IDs to key them in associative arrays.
            foreach ( $service_bodies as $service_body )
                {
                if ( ($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) && $service_body->UserCanEdit() ) // We are a full Service body editor, with rights to edit the Service body itself (as well as all its meetings).
                    {
                    $my_editable_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                    }
                // Again, we keep checking credentials, over and over again.
                elseif ( (($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || ($user_obj->GetUserLevel() == _USER_LEVEL_OBSERVER)) && $service_body->UserCanEditMeetings() ) // We are a "guest" editor, or an observer (depends on our user level).
                    {
                    if ( $user_obj->GetUserLevel() == _USER_LEVEL_OBSERVER )
                        {
                        $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                        }
                    else
                        {
                        $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                        }
                    }
                }
            // Now, we grant rights to Service bodies that are implicit from other rights (for example, a Service Body Admin can also observe and edit meetings).
            
            // A full Service Body Admin can edit meetings in that Service body.
            foreach ( $my_editable_service_bodies as $service_body )
                {
                $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                }
            
            // An editor (whether an admin or a "guest") also has observe rights.
            foreach ( $my_meeting_editor_service_bodies as $service_body )
                {
                $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                }
            
            // At this point, we have 3 arrays (or fewer), filled with Service bodies that we have rights on. It is entirely possible that only one of them could be filled, and it may only have one member.
            
            // We start to construct the XML filler.
            foreach ( $service_bodies as $service_body )
                {
                // If we can observe, then we have at least one permission for this Service body.
                if ( isset ( $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] ) && $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] )
                    {
                    $ret .= '<service_body id="'.$service_body->GetID().'" name="'.c_comdef_htmlspecialchars ( $service_body->GetLocalName() ).'">';
                        $ret .= '<permission level="observer" />';
                        
                        if ( isset ( $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] ) && $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] )
                            {
                            $ret .= '<permission level="meeting_editor" />';
                            }
                        
                        if ( isset ( $my_editable_service_bodies['sb_'.$service_body->GetID()] ) && $my_editable_service_bodies['sb_'.$service_body->GetID()] )
                            {
                            $ret .= '<permission level="service_body_editor" />';
                            }
                    $ret .= '</service_body>';
                    }
                }
            // Create a proper XML wrapper for the response data.
			$ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<permissions xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] ).GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/AdminPermissions.php\">$ret</permissions>";
            // We now have XML that states the current user's permission levels in all Service bodies.
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
        
        return $ret;
        }
};
?>