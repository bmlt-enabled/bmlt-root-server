<?php
/***********************************************************************/
/** \file   c_comdef_service_bodies.class.php
    \brief The file for the c_comdef_service_bodies class.

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

require_once(dirname(__FILE__)."/c_comdef_service_body.class.php");

/***********************************************************************/
/** \class c_comdef_service_bodies
    \brief  A class to hold a collection of c_comdef_service_body objects.

***********************************************************************/
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class c_comdef_service_bodies implements i_comdef_has_parent
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
{
    /// This is the parent (container) object that holds this instance.
    private $_local_id_parent_obj = null;
    
    /*******************************************************************/
    /** We keep a local copy of the simple array, because we can instantly
        access it, as opposed to having to instantiate iterators.
    */
    private $_local_copy_of_array = null;

    public function __construct(
        $in_parent_object,              ///< A reference to the object that "owns" this instance.
        $in_service_body_object_array   ///< An array of references to c_comdef_service_body objects, to be stored as local references.
    ) {
        $this->SetParentObj($in_parent_object);
        
        foreach ($in_service_body_object_array as &$obj) {
            // Who's yer daddy?
            $obj->SetParentObj($this);
        }
        $this->_local_copy_of_array = $in_service_body_object_array;
    }
    
    /*******************************************************************/
    /** \brief Set the parent object of this instance.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function SetParentObj(
        $in_parent_obj  ///< A reference to the parent object.
    ) {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        $this->_local_id_parent_obj = null;
        $this->_local_id_parent_obj = $in_parent_obj;
    }
    
    /*******************************************************************/
    /** \brief Return a reference to the parent object of this instance.

        \returns a reference to the parent instance of the object.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetParentObj()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_local_id_parent_obj;
    }

    /*******************************************************************/
    /** \brief Accessor -Get the service body object array.

        \returns a reference to an array of c_comdef_service_body objects.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function &GetServiceBodiesArray()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_local_copy_of_array;
    }
}
