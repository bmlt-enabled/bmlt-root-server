<?php
/***********************************************************************/
/**     \file   Array2JSON.php

    \brief  This is an open-source JSON encoder that allows us to support
    older versions of PHP (before the <a href="http://us3.php.net/json_encode">json_encode()</a> function
    was implemented). It uses json_encode() if that function is available.

    This is from <a href="http://www.bin-co.com/php/scripts/array2json/">Bin-Co.com</a>.

    This crap needs to be included to be aboveboard and legal. You can still re-use the code, but
    you need to make sure that the comments below this are included:

    Copyright (c) 2004-2007, Binny V Abraham

    All rights reserved.

    Redistribution and use in source and binary forms, with or without modification, are permitted provided
    that the following conditions are met:

    *   Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    *   Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer
        in the documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
    BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
    IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
    PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
    OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
    EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

/*******************************************************************/
/** \brief  Encodes a given associative array into a JSON object string.

    \returns a JSON object, as a string.
*/
function array2json(
    $arr    ///< An associative string, to be encoded as JSON.
) {
    $parts = array();
    // See if we are an associative array.
    $is_list = (count(array_filter(array_keys($arr), 'is_numeric')) == count($arr)) && (array_keys($arr) === range(0, count($arr) - 1));

    foreach ($arr as $key => $value) {
        if (is_array($value) && count($value)) { //Custom handling for arrays
            if ($is_list) {
                $parts[] = array2json($value); /* :RECURSION: */
            } else {
                $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
            }
        } else {
            $str = '';
            if ($key === 'json_data') {  // JSON data is passed through without any vetting or modification (so it can easily bork the stream).
                $str = '"'.$key.'":'.$value;
            } else {
                if (!$is_list) {
                    $str = '"' . $key . '":';
                }

                //Custom handling for multiple data types
                if (isset($value)) {
                    if (is_integer($value)) {
                        $str .= $value;
                    } elseif (is_bool($value)) {
                        $str .= $value ? 'true': 'false';
                    } else {
                        $str .= '"' . trim(json_encode(str_replace('"', '&quot;', $value)), '"') . '"'; //All other things
                    }
                } else {
                    $str .= '""'; //All other things
                }
            }
            
            // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

            $parts[] = $str;
        }
    }
    
    $json = implode(',', $parts);
    
    if ($is_list) {
        return '[' . $json . ']'; //Return numerical JSON
    } else {
        return '{' . $json . '}'; //Return associative JSON
    }
}
