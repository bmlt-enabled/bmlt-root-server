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
    var $handled_service_body_ids;      ///< This is used to ensure that we respect the hierarchy when doing a hierarchical Service body request.
    
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
        $this->handled_service_body_ids = array();
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
                        $ret = $this->process_service_bodies_info_request();
                    break;
                    
                    case 'get_format_info':
                        $ret = $this->process_format_info();
                    break;
                    
                    case 'get_meetings':
                        $ret = $this->process_meeting_search();
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
    \brief This fulfills a user request to return meeting information from a search.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_meeting_search()
    {
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to return format information.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_format_info()
    {
        $ret = '';
        
        $user_obj = $this->server->GetCurrentUserObj();
        if ( isset ( $user_obj ) && ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($user_obj->GetID() > 1) )
            {
            $format_ids = array();
            
            // See if we are receiving a request for just specific formats, or if we are being asked for all of them.
            if ( isset ( $this->http_vars['format_id'] ) && intval ( trim ( $this->http_vars['format_id'] ) ) )
                {
                $format_ids[] = intval ( trim ( $this->http_vars['format_id'] ) );
                }
            elseif ( isset ( $this->http_vars['format_id'] ) && is_array ( $this->http_vars['format_id'] ) && count ( $this->http_vars['format_id'] ) )
                {
                foreach ( $this->http_vars['format_id'] as $format )
                    {
                    $format_ids[] = intval ( trim ( $format ) );
                    }
                }
            else
                {
                $format_ids = NULL;
                }
            
            $lang = $this->server->GetLocalLang();
            if ( isset ( $this->http_vars['lang'] ) && trim ( $this->http_vars['lang'] ) )
                {
                $lang = strtolower ( trim ( $this->http_vars['lang'] ) );
                }
            
            $returned_formats = array();    // We will be returning our formats in this.
            $format_objects = $this->server->GetFormatsObj()->GetFormatsByLanguage ( $lang ); // Get all the formats (not just the ones used, but ALL of them).
            
            // Filter for requested formats in the requested language.
            foreach ( $format_objects as $format )
                {
                if ( !$format_ids || in_array ( intval ( $format->GetSharedID() ), $format_ids ) )
                    {
                    $returned_formats[] = $format;
                    }
                }
            
            // At this point, we have a complete array of just the format[s] that will be returned. Time to make some XML...
            $index = 0;
            foreach ( $returned_formats as $format )
                {
                $ret .= '<row sequence_index="'.strval ( $index++ ).'">';
                    $ret.= '<key_string>'.c_comdef_htmlspecialchars ( $format->GetKey() ).'</key_string>';
                    $ret.= '<name_string>'.c_comdef_htmlspecialchars ( $format->GetLocalName() ).'</name_string>';
                    $ret.= '<description_string>'.c_comdef_htmlspecialchars ( $format->GetLocalDescription() ).'</description_string>';
                    $ret.= '<lang>'.c_comdef_htmlspecialchars ( $lang ).'</lang>';
                    $ret.= '<id>'.intval ( $format->GetSharedID() ).'</id>';
                    $world_id = trim ( $format->GetWorldID() );
                    if ( isset ( $world_id ) && $world_id )
                        {
                        $ret.= '<world_id>'.c_comdef_htmlspecialchars ( $world_id ).'</world_id>';
                        }
                $ret .= '</row>';
                }
            $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<formats xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/GetFormats.php\">$ret</formats>";
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to return Service Body information for multiple Service bodies.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_service_bodies_info_request()
    {
        $ret = '';
        
        $user_obj = $this->server->GetCurrentUserObj();
        if ( isset ( $user_obj ) && ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($user_obj->GetID() > 1) )
            {
            $service_body_ids = array();
            
            // Look to see if the caller is asking for particular Service bodies.
            if ( isset ( $this->http_vars['sb_id'] ) && $this->http_vars['sb_id'] )
                {
                if ( !is_array ( $this->http_vars['sb_id'] ) )
                    {
                    $service_body_ids[] = intval ( trim ( $this->http_vars['sb_id'] ) );
                    }
                else
                    {
                    foreach ( $this->http_vars['sb_id'] as $id )
                        {
                        if ( intval ( trim ( $id ) ) )
                            {
                            $service_body_ids[] = intval ( trim ( $id ) );
                            }
                        }
                    }
                }
            
            // If we have a request for individual Service bodies, then we just return those ones.
            if ( isset ( $service_body_ids ) && is_array ( $service_body_ids ) && count ( $service_body_ids ) )
                {
                foreach ( $service_body_ids as $id )
                    {
                    $ret .= $this->process_service_body_info_request ( $id );
                    }
                }
            else    // If they are not looking for particular bodies, then we return the whole kit & kaboodle.
                {
                $service_bodies = $this->server->GetServiceBodyArray();
            
                foreach ( $service_bodies as $service_body )
                    {
                    if ( isset ( $this->http_vars['flat'] ) || !$service_body->GetOwnerIDObject() )    // We automatically include top-level Service bodies here, and let ones with parents sort themselves out.
                        {
                        $ret .= $this->process_service_body_info_request ( $service_body->GetID() );
                        }
                    }
                }
        
            $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<service_bodies xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/HierServiceBodies.php\">$ret</service_bodies>";
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
    function process_service_body_info_request (    $in_service_body_id ///< The ID of the Service body being requested.
                                                )
    {
        $ret = '';
        // Belt and suspenders. We need to make sure the user is authorized.
        $user_obj = $this->server->GetCurrentUserObj();
        if ( isset ( $user_obj ) && ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($user_obj->GetID() > 1) )
            {
            if ( !in_array ( $in_service_body_id, $this->handled_service_body_ids ) )
                {
                $this->handled_service_body_ids[] = $in_service_body_id;
                $service_body = $this->server->GetServiceBodyByIDObj ( $in_service_body_id );
            
                if ( isset ( $service_body ) && ($service_body instanceof c_comdef_service_body) )
                    {
                    // Everyone gets the type, URI, name, description and parent Service body.
                    $name = $service_body->GetLocalName();
                    $description = $service_body->GetLocalDescription();
                    $uri = $service_body->GetURI();
                    $type = $service_body->GetSBType();

                    $parent_service_body_id = 0;
                    $parent_service_body_name = "";
                    $parent_service_body_type = "";

                    $parent_service_body = $service_body->GetOwnerIDObject();
                
                    if ( isset ( $parent_service_body ) && $parent_service_body )
                        {
                        $parent_service_body_id = intval ( $parent_service_body->GetID() );
                        $parent_service_body_name = $parent_service_body->GetLocalName();
                        $parent_service_body_type = $parent_service_body->GetSBType();
                        }
                
                    $principal_user = $service_body->GetPrincipalUserObj();
                    $principal_user_id = intval ( $principal_user->GetID() );
                
                    // Scan for our various editors.
                    $guest_editors = $this->server->GetUsersByLevelObj ( _USER_LEVEL_OBSERVER, TRUE );  // Observer or greater.
                    $service_body_editors = array();
                    $meeting_list_editors = array();
                    $observers = array();
                
                    foreach ( $guest_editors as $editor )
                        {
                        if ( $service_body->UserCanEdit ( $editor ) )   // We will have at least one of these, as the principal user needs to be listed.
                            {
                            array_push ( $service_body_editors, $editor );
                            }
                        elseif ( $service_body->UserCanEditMeetings ( $editor ) )
                            {
                            array_push ( $meeting_list_editors, $editor );
                            }
                        elseif ( $service_body->UserCanObserve ( $editor ) )
                            {
                            array_push ( $observers, $editor );
                            }
                        }
                
                    // Scan for direct descendant child Service bodies.
                    $children = array();
                    $service_bodies = $this->server->GetServiceBodyArray();
                
                    foreach ( $service_bodies as $child )
                        {
                        if ( $child->IsOwnedBy ( $in_service_body_id, TRUE ) )
                            {
                            $children[] = $child;
                            }
                        }
                
                    // We check to see which editors are mentioned in this Service body.
                    $guest_editors = $service_body->GetEditors();
                
                    // See if we have rights to edit this Service body. Just for the heck of it, we check the user level (not really necessary, but belt and suspenders).
                    $this_user_can_edit_the_body = ($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) && $service_body->UserCanEdit();
                
                    $contact_email = NULL;
                
                    // Service Body Admins (with permission for the body) get more info.
                    if ( $this_user_can_edit_the_body )
                        {
                        $contact_email = $service_body->GetContactEmail();
                        }
                    
                    // At this point, we have all the information we need to build the response XML.
                    $ret = '<service_body id="'.c_comdef_htmlspecialchars ( $in_service_body_id ).'" name="'.c_comdef_htmlspecialchars ( $name ).'" type="'.c_comdef_htmlspecialchars ( $type ).'">';
                        $ret .= '<service_body_type>'.c_comdef_htmlspecialchars ( $this->my_localized_strings['service_body_types'][$type] ).'</service_body_type>';
                        if ( isset ( $description ) && $description )
                            {
                            $ret .= '<description>'.c_comdef_htmlspecialchars ( $description ).'</description>';
                            }
                        if ( isset ( $uri ) && $uri )
                            {
                            $ret .= '<uri>'.c_comdef_htmlspecialchars ( $uri ).'</uri>';
                            }
                        if ( isset ( $parent_service_body ) && $parent_service_body )
                            {
                            $ret .= '<parent_service_body id="'.intval ( $parent_service_body_id ).'" type="'.c_comdef_htmlspecialchars ( $parent_service_body_type ).'">'.c_comdef_htmlspecialchars ( $parent_service_body_name ).'</parent_service_body>';
                            }
                        if ( $this_user_can_edit_the_body && isset ( $contact_email ) && $contact_email )
                            {
                            $ret .= '<contact_email>'.c_comdef_htmlspecialchars ( $contact_email ).'</contact_email>';
                            }
                    
                        $ret .= '<editors>';
                            if ( isset ( $service_body_editors ) && is_array ( $service_body_editors ) && count ( $service_body_editors ) )
                                {
                                // We will have at least one of these (the principal user).
                                // These are the users that can directly manipulate the Service body.
                                $ret .= '<service_body_editors>';
                                    foreach ( $service_body_editors as $editor )
                                        {
                                        $editor_id = intval ( $editor->GetID() );
                                        $ret .= '<editor id="'.$editor_id.'" type="'.( in_array ( $editor_id, $guest_editors ) ? 'direct' : (( $editor_id == $principal_user_id ) ? 'principal' : 'inherit')).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                        }
                                $ret .= '</service_body_editors>';
                                }
                        
                            // These are users that can't manipulate the Service body, but can edit meetings.
                            if ( isset ( $meeting_list_editors ) && is_array ( $meeting_list_editors ) && count ( $meeting_list_editors ) )
                                {
                                $ret .= '<meeting_list_editors>';
                                    foreach ( $meeting_list_editors as $editor )
                                        {
                                        $editor_id = intval ( $editor->GetID() );
                                        $ret .= '<editor id="'.$editor_id.'" type="'.( in_array ( $editor_id, $guest_editors ) ? 'direct' : (( $editor_id == $principal_user_id ) ? 'principal' : 'inherit' )).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                        }
                                $ret .= '</meeting_list_editors>';
                                }
                        
                            // These are users that can only see hidden fields in meetings.
                            if ( isset ( $observers ) && is_array ( $observers ) && count ( $observers ) )
                                {
                                $ret .= '<observers>';
                                    foreach ( $observers as $editor )
                                        {
                                        $editor_id = intval ( $editor->GetID() );
                                        $ret .= '<editor id="'.$editor_id.'" type="'.( in_array ( $editor_id, $guest_editors ) ? 'direct' : (( $editor_id == $principal_user_id ) ? 'principal' : 'inherit' )).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                        }
                                $ret .= '</observers>';
                                }
                        $ret .= '</editors>';
                
                        // If this is a hierarchical response, we embed the children as XML service_body elements. Otherwise, we list them as simple catalog elements.
                        if ( !isset ( $this->http_vars['flat'] ) && isset ( $children ) && is_array ( $children ) && count ( $children ) )
                            {
                            $ret .= "<service_bodies>";
                                foreach ( $children as $child )
                                    {
                                    $ret .= $this->process_service_body_info_request ( $child->GetID() );
                                    }
                            $ret .= "</service_bodies>";
                            }
                        elseif ( isset ( $children ) && is_array ( $children ) && count ( $children ) )
                            {
                            $ret .= '<children>';
                                foreach ( $children as $child )
                                    {
                                    $ret .= '<child_service_body id="'.intval ( $child->GetID() ).'" type="'.c_comdef_htmlspecialchars ( $child->GetSBType() ).'">'.c_comdef_htmlspecialchars ( $child->GetLocalName() ).'</child_service_body>';
                                    }
                            $ret .= '</children>';
                            }

                    $ret .= '</service_body>';
                    }
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
			$ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<permissions xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/AdminPermissions.php\">$ret</permissions>";
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