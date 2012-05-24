<?php
/**
* \brief array2xml() will convert any given array into a XML structure.
*
* \version:     1.0
*
* \author:		Marcus Carver © 2008
*
* Email:       marcuscarver@gmail.com
*
* Link:        http://marcuscarver.blogspot.com/
*
* Arguments :  $array      - The array you wish to convert into a XML structure.
*              $name       - The name you wish to enclose the array in, the 'parent' tag for XML.
*              $beginning  - INTERNAL USE... DO NOT USE!
*
* \return:      Gives a string output in a XML structure
*
* Use:         echo array2xml($products,'products');
*              die;
    
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

function array2xml(	$array,				///< The input array
					$name='array',		///< The name that you want as the root element for the XML output
					$beginning = true	///< Used for the recursive parser. Not for external use.
					)
{
	global $nested, $s_array2xml_index;
	
	$output = '';
	
	if ($beginning)
	{
		$output = '<' . htmlspecialchars($name) . '>';
		$s_array2xml_index = 0;
		$nested = 0;
	}
	
	// This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
	$ArrayNumberPrefix = 'row';
	
	foreach ($array as $root => $child)
		{
		if (is_array($child))
			{
			$output .= '<' . (is_string($root) ? htmlspecialchars($root) : $ArrayNumberPrefix) . ' id="'.strval ( intval ($s_array2xml_index++) ).'">';
			$nested++;
			$output .= array2xml($child, null, null, false);
			$nested--;
			$output .= '</' . (is_string($root) ? htmlspecialchars($root) : $ArrayNumberPrefix) . '>';
			}
		elseif ( isset ( $child ) && $child )
			{
			$output .= '<' . (is_string($root) ? htmlspecialchars($root) : $ArrayNumberPrefix . htmlspecialchars($root)) . '>' . htmlspecialchars($child) . '</' . (is_string($root) ? htmlspecialchars($root) : $ArrayNumberPrefix) . '>';
			}
		else
			{
// Commented out, because we will simply not add empty elements (for now). This is to save bandwidth.
//			$output .= '<' . (is_string($root) ? htmlspecialchars($root) : $ArrayNumberPrefix . htmlspecialchars($root)) . '/>';
			}
		}
	
	if ($beginning) $output .= '</' . htmlspecialchars($name) . '>';
	
	return $output;
}
?>