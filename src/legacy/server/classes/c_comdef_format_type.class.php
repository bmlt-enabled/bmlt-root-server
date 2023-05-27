<?php
/***********************************************************************/
/** \file   c_comdef_format.class.php
    \brief  The class file for the c_comdef_format class.

    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/

defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

require_once(dirname(__FILE__)."/../shared/classes/base_templates.inc.php");

/***********************************************************************/
/** \class c_comdef_format
    \brief A Class for Format Codes

    This class handles the model for the NA Meeting Format Codes. The
    codes are stored one code per language per instance of this class.
    If codes are related (Same code, different languages), then you should
    give them all the same shared ID.
***********************************************************************/
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class c_comdef_format_type
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
{
    /// The Format Key, in Text Form.
    public $_key_string = null;
    
    /// For some reason, used in the new API
    public $_api_enum = null;

    /// The implied order
    public $_position = null;
    
    /*******************************************************************/
    /** \brief The initial setup call for the class. If you send in values,
        the object will set itself up to use them.

    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function __construct(
        $in_parent_obj,                 ///< The object that "owns" this instance.
        $in_key_string = null,            ///< The format Key, as a text string (1-3 Characters).
        $in_api_enum = null,               ///< For some reason, the new API defines a different key
        $in_position = null
    ) {
        $this->SetKey($in_key_string);
        $this->SetApiEnum($in_api_enum);
        $this->SetPosition($in_position);
    }
    
    /*******************************************************************/
    /** \brief Accessor -Sets the format key (the 1-3 letter code that represents the format).
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SetKey(
        $in_key_string  ///< The format Key, as a text string (1-3 Characters)
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_key_string = $in_key_string;
    }
    /*******************************************************************/
    /** \brief Accessor -Returns a reference to the _key_string data member

        @returns The _key_string data member, as a reference.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function &GetApiEnum()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_api_enum;
    }
       
    /*******************************************************************/
    /** \brief Accessor -Sets the format key (the 1-3 letter code that represents the format).
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SetApiEnum(
        $in_string  ///< The format Key, as a text string (1-3 Characters)
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_api_enum = $in_string;
    }
        /** \brief Accessor -Returns a reference to the _key_string data member

        @returns The _key_string data member, as a reference.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function &GetPosition()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_position;
    }
       
    /*******************************************************************/
    /** \brief Accessor -Sets the format key (the 1-3 letter code that represents the format).
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SetPosition(
        $in_string  ///< The format Key, as a text string (1-3 Characters)
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_position = $in_string;
    }
    /*******************************************************************/
    /** \brief Accessor -Returns a reference to the _key_string data member

        @returns The _key_string data member, as a reference.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function &GetKey()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_key_string;
    }
    
    /*******************************************************************/
    /** \brief Returns a storable serialization of the object, as a string.

        This is only used for the changes, as the serialized string may not
        be easily searched.

        \returns an array, containing the object in serialized form.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SerializeObject()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $s_array['key_string'] = $this->GetKey();
        $s_array['api_enum'] = $this->GetApiEnum();
        $s_array['position_int'] = $this->GetPosition();

        return serialize($s_array);
    }
    
    /*******************************************************************/
    /** \brief This takes the serialized data, and instantiates a
        new object from it.

        \returns a new instance of c_comdef_format, set up according to
        the serialized data passed in.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function UnserializeObject(
        $in_parent,      ///< The parent object.
        $serialized_array   ///< An array containing the serialized data.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $s_array = unserialize($serialized_array);
        
        return new c_comdef_format_type(
            $in_parent,
            $s_array['key_string'],
            $s_array['api_enum'],
            $s_array['position_int'],
        );
    }
    
    /*******************************************************************/
    /** \brief Test to see if a user is allowed to edit an instance (change the data).

        \returns true, if the user is allowed to edit, false, otherwise.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function UserCanEdit(
        $in_user_object = null  ///< A reference to a c_comdef_user object, for the user to be validated. If null, or not supplied, the server current user is tested.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $ret = false;
        
        // We load the server user if one wasn't supplied.
        if (null == $in_user_object) {
            $in_user_object = c_comdef_server::GetCurrentUserObj();
        }
        
        // If it isn't a user object, we fail right there.
        if ($in_user_object instanceof c_comdef_user) {
            $in_user_object->RestoreFromDB();   // The reason you do this, is to ensure that the user wasn't modified "live." It's a security precaution.
            // Only the server admin can edit formats.
            if (c_comdef_server::IsUserServerAdmin()) {
                $ret = true;
            }
        }
        
        return $ret;
    }
}
