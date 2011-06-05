<?php
	/**
		\file satellite_server/pdf_generator/index.php
    
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
	
	Make_List ( );
	
	/**
		\brief This function actually gets the CSV data from the root server, and creates a PDF file from it, using FPDF.
	*/
	function Make_List ( )
		{
		$in_http_vars = array_merge_recursive ( $_GET, $_POST );
		
		$class_name = preg_replace ( "#[\\:/]#",'',$in_http_vars['list_type']).'_napdf';

		if ( file_exists ( dirname ( __FILE__ )."/../pdf_generator/$class_name.class.php" ) )
			{
			require_once ( dirname ( __FILE__ )."/../pdf_generator/$class_name.class.php" );
			$class_instance = new $class_name ( $in_http_vars );

			if ( ($class_instance instanceof $class_name) && method_exists ( $class_instance, 'AssemblePDF' ) && method_exists ( $class_instance, 'OutputPDF' ) )
				{
				if ( $class_instance->AssemblePDF() )
					{
					$class_instance->OutputPDF();
					}
				}
			else
				{
				echo "Cannot instantiate $class_name";
				}
			}
		else
			{
			echo "Cannot find ".dirname ( __FILE__ )."/../$dir$class_name.class.php";
			}
		}
?>
