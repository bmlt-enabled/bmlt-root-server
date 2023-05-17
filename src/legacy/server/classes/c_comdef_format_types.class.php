<?php
/***********************************************************************/
/** \file   c_comdef_formats.class.php
    \brief  The class file for the c_comdef_formats class.

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

/// Include the format class.
require_once(dirname(__FILE__)."/c_comdef_format_type.class.php");
require_once(dirname(__FILE__)."/../shared/classes/comdef_utilityclasses.inc.php");

/***********************************************************************/
/** \class c_comdef_format_types
    \brief A Class for Aggregating Format Codes

    This class acts as an array aggregator for c_comdef_format objects.
    It is a class unto itself, because we want to have a couple of
    additional filtering options (like the ability to get a bunch of
    formats that share a _shared_id_bigint code).

    The formats are kept in a simple two-dimensional array:

    \code
        $_local_copy_of_array = array[$lang_enum][$shared_id_bigint]
    \endcode

    The combination of shared ID and language needs to be unique. This is how
    we match formats to languages. The same shared ID is used throughout
    the system, and we use the localization to return its key and description
    in the appropriate language.
***********************************************************************/
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class c_comdef_format_types implements i_comdef_has_parent
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

    /*******************************************************************/
    /** \brief Constructor. Sets the parent object.
    */
    public function __construct(
        $in_parent_obj,
        $in_array
    ) {
        $this->SetParentObj($in_parent_obj);
        $this->_local_copy_of_array = $in_array;
    }

    /*******************************************************************/
    /** \brief Accessor -Get the format object array.

        \returns a reference to an array of c_comdef_format objects.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetFormatTypesArray()
    {
        // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        return $this->_local_copy_of_array;
    }
    
    /*******************************************************************/
    /** \brief Return a reference to a single object, by format key and
        language.

        You do not need to provide a language, in which case, the server's
        local language is used.

        \returns A reference to the single selected object.
    */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function GetFormatTypeByKey(
        $in_format_key,         ///< This is the shared ID code.
    ) {
        foreach ($this->_local_copy_of_array as &$format) {
            if ($in_format_key == $format->GetKey()) {
                return $format;
            };
        }
        
        return null;
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
}
