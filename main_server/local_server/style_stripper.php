<?php
/***********************************************************************/
/** 	\file	style_stripper.php

	\brief	This file reads in a CSS file, and optimizes it by stripping
	out comments and whitespace. It will also try to GZ compress the output
	using the standard OB functions. It can make a HUGE difference in size.
	
	The way it works is that you call it from the <link/> element (don't
	specify a "type" attribute), and give it a GET parameter of filename,
	which will equal the file path to the CSS file.
	
	For security purposes, the file must always be a ".css" file, and you can't
	go out of the directory in which this file is located.

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
	$pathname = $_GET['filename'];
	if ( !preg_match ( "|/|", $pathname ) )
		{
		if ( preg_match ( "|.*?\.css$|", $pathname ) )
			{
			$pathname = dirname ( __FILE__ )."/$pathname";
			$opt = file_get_contents ( $pathname );
			$opt = preg_replace( "|\/\*.*?\*\/|s", "", $opt );
			$opt = preg_replace( "|\t+|s", " ", $opt );
			$opt = preg_replace( "| +|s", " ", $opt );
			$opt = preg_replace( "|[\n\r]+|s", "\n", $opt );
			$opt = preg_replace( "|\n+|s", "\n", $opt );
			$opt = preg_replace( "|\n+|", " ", $opt );
			header ( "Content-type: text/css" );	
			if ( zlib_get_coding_type() === false )
				{
					ob_start("ob_gzhandler");
				}
				else
				{
					ob_start();
				}

				echo $opt;
			ob_end_flush();
			}
		else
			{
			echo "FILE MUST BE A .CSS FILE!";
			}
		}
	else
		{
		echo "YOU CANNOT LEAVE THE DIRECTORY!";
		}
?>