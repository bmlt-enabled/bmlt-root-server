<?php
/***********************************************************************/
/** 	\file	Array2JSON.php

	\brief	This is an open-source JSON encoder that allows us to support
	older versions of PHP (before the <a href="http://us3.php.net/json_encode">json_encode()</a> function
	was implemented). It uses json_encode() if that function is available.
	
	This is from <a href="http://www.bin-co.com/php/scripts/array2json/">Bin-Co.com</a>.
	
	This crap needs to be included to be aboveboard and legal. You can still re-use the code, but
	you need to make sure that the comments below this are included:
	
	Copyright (c) 2004-2007, Binny V Abraham

	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without modification, are permitted provided
	that the following conditions are met:
	
	*	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	*	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer
		in the documentation and/or other materials provided with the distribution.
	
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
	BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
	PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
	OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
	EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

/*******************************************************************/
/** \brief	Encodes a given associative array into a JSON object string.

	\returns a JSON object, as a string.
*/
function array2json (
					$arr	///< An associative string, to be encoded as JSON.
					)
	{
	if(function_exists('json_encode'))
		{
		return json_encode($arr); //Lastest versions of PHP already has this functionality.
		}
	
	$parts = array();
	$is_list = false;

	//Find out if the given array is a numerical array
	$keys = array_keys($arr);
	$max_length = count($arr)-1;
	if(($keys[0] == 0) and ($keys[$max_length] == $max_length))
		{//See if the first key is 0 and last key is length - 1
		$is_list = true;
		for ($i=0; $i<count($keys); $i++)
			{ //See if each key correspondes to its position
			if($i != $keys[$i])
				{ //A key fails at position check.
				$is_list = false; //It is an associative array.
				break;
				}
			}
		}

	foreach($arr as $key=>$value)
		{
		if(is_array($value))
			{ //Custom handling for arrays
			if($is_list)
				{
				$parts[] = array2json($value); /* :RECURSION: */
				}
			else
				{
				$parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
				}
				
			}
		else
			{
			$str = '';
			if ( !$is_list )
				{
				$str = '"' . $key . '":';
				}

			//Custom handling for multiple data types
			if ( is_numeric($value) )
				{
				$str .= $value; //Numbers
				}
			elseif ( $value === false )
				{
				$str .= 'false'; //The booleans
				}
			elseif ( $value === true )
				{
				$str .= 'true';
				}
			elseif ( isset ($value) && $value )
				{
				$str .= '"' . addslashes($value) . '"'; //All other things
				}
			else
				{
				$str .= '""'; //All other things
				}
			// :TODO: Is there any more datatype we should be in the lookout for? (Object?)

			$parts[] = $str;
			}
		}
	
	$json = implode ( ',', $parts );
	
	if( $is_list )
		{
		return '[' . $json . ']'; //Return numerical JSON
		}
	else
		{
		return '{' . $json . '}'; //Return associative JSON
		}
	}
?>