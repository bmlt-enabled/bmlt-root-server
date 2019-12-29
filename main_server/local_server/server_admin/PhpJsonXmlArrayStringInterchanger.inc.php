<?php
define("NODE_SEPERATOR", "#*#");
define("LINE_SEPERATOR", "!#=#!");
define("ERROR_STYLE", "color:#fff;background-color:#ff0000;font-weight:bold;padding:5px;margin:5px;");

/**
 * This class interchange String, XML, JSON and Array into each other.
 *
 * @author Rochak Chauhan
 * @package PhpJsonXmlArrayStringInterchanger
 * @version beta
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class PhpJsonXmlArrayStringInterchanger
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
{
    private $errorLog=array();

    /**
     * Function to display last error for debugging purpose
     *
     * @access public
     * @return string
     */
    public function displayLastError()
    {
        $return="No errors were encountered.";
        $c=count($this->errorLog);
        if ($c>0) {
            $i=$c-1;
            $return="<div style='".ERROR_STYLE."'>".$this->errorLog[$i]."</div>";
        }
        echo $return;
    }

    /**
     * Function to display complete error log for debugging purpose
     *
     * @access public
     * @return string
     */
    public function displayErrorLog()
    {
        $return="No errors were encountered.";
        $c=count($this->errorLog);
        if ($c>0) {
            $return="";
            for ($i=0; $i<$c; $i++) {
                $return.="<div style='".ERROR_STYLE."'>".$this->errorLog[$i]."</div>";
            }
        }
        echo $return;
    }

    /**
     * Function to recursivly parse Xml Content
     *
     * @param mixed $ret
     * @access private
     * @return array on success and false on failure
     */
    private function parseXml($ret)
    {
        $return=false;
        if (is_object($ret)) {
            $ret=(array)$ret;
            $this->parseXml($ret);
        }
        if (is_array($ret)) {
            foreach ($ret as $k => $v) {
                if (is_object($v)) {
                    $return[$k]=$this->parseXml($v);
                } else {
                    $return[$k]=$v  ;
                }
            }
        }
        return $return;
    }

    /**
     * Function to convert XML into Array
     *
     * @param string $xmlContent
     * @access public
     * @return array on success and false on failure
     */
    public function convertXmlToArray($xmlContent)
    {
        $return=false;
        $ret=simplexml_load_string($xmlContent);
        if ($ret===false) {
            $this->errorLog[]="Invalid XML content: $xmlContent in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
            return false;
        } else {
            $return=$this->parseXml($ret);
            if ($return===false) {
                $this->errorLog[]="Failed to parse XML content in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
                return false;
            }
        }
        return $return;
    }

    /**
     * Function to recursivly parse Array Content
     *
     * @param mixed $ret
     * @access private
     * @return string(xml) on success and false on failure
     */
    private function parseArray($array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                if (trim($k)=="") {
                    $this->errorLog[]="Array needs to be associative as parameter in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
                    return false;
                } else {
                    if (is_numeric($k)) {
                        $k="nodeValue$k";
                    }
                    if (is_array($v)) {
                        $return.="<$k>".$this->parseArray($v)."</$k>";
                    } else {
                        $return.="<$k>$v</$k>";
                    }
                }
            }
        } else {
            $this->errorLog[]="Invalid array in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
            return false;
        }
        return $return;
    }

    /**
     * Function to convert an associative array into XML
     *
     * @param string $array
     * @access public
     * @return string(xml) on success and false on failure
     */
    public function convertArrayToXML($array)
    {
        $return="<?xml version='1.0' encoding='ISO-8859-1'?><PhpJsonXmlArrayStringInterchanger>";
        $return.=$this->parseArray($array);
        $return.="</PhpJsonXmlArrayStringInterchanger>";
        return $return;
    }

    /**
     * Function to convert an JSON into XML
     *
     * @param string $json
     * @access public
     * @return string(xml) on success and false on failure
     */
    public function convertJsonToXML($json)
    {
        if (!is_string($json)) {
            $this->errorLog[]="The first parameter should to be string in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
            return false;
        }
        $array=json_decode($json, true);
        if ($array===false) {
            $this->errorLog[]="Failed to decode JSON in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
            return false;
        } else {
            return $this->convertArrayToXML($array);
        }
    }

    /**
     * Function to convert an JSON into array
     *
     * @param string $json
     * @access public
     * @return array on success and false on failure
     */
    public function convertJsonToArray($json)
    {
        if (!is_string($json)) {
            $this->errorLog[]="The first parameter should to be string in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
            return false;
        }
        $array=json_decode($json, true);
        if ($array===false) {
            $this->errorLog[]="Failed to decode JSON in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
            return false;
        } else {
            return $array;
        }
    }


    /**
     * Function to parse String and convert it into array
     *
     * @param array $array
     * @access public
     * @return array on success and false on failure
     * @todo refactor  the code from line 205-222  (automate it)
     */
    public function convertStringToArray($string, &$myarray = "")
    {
        $lines = explode(LINE_SEPERATOR, $string);
        foreach ($lines as $value) {
            $items = explode(NODE_SEPERATOR, $value);
            if (sizeof($items) == 2) {
                $myarray[$items[0]] = $items[1];
            } elseif (sizeof($items) == 3) {
                $myarray[$items[0]][$items[1]] = $items[2];
            } elseif (sizeof($items) == 4) {
                $myarray[$items[0]][$items[1]] [$items[2]] = $items[3];
            } elseif (sizeof($items) == 5) {
                $myarray[$items[0]][$items[1]] [$items[2]][$items[3]] = $items[4];
            } elseif (sizeof($items) == 6) {
                $myarray[$items[0]][$items[1]] [$items[2]][$items[3]][$items[4]] = $items[5];
            } elseif (sizeof($items) == 7) {
                $myarray[$items[0]][$items[1]] [$items[2]][$items[3]][$items[4]][$items[5]] = $items[6];
            }
        }
        return $myarray;
    }
    
    /**
     * Function to parse Array and convert it into string
     *
     * @param array $array
     * @access private
     * @return string on success and false on failure
     */
    private function convertArrayToString($myarray, &$output = "", &$parentkey = "")
    {
        if (is_array($myarray)) {
            if (trim($parentkey)=="") {
                $parentkey=LINE_SEPERATOR;
            }
            foreach ($myarray as $key => $value) {
                if (is_array($value)) {
                    $parentkey .= $key.NODE_SEPERATOR;
                    $this->convertArrayToString($value, $output, $parentkey);
                    $parentkey = "";
                } else {
                    $output .= $parentkey.$key.NODE_SEPERATOR.$value.LINE_SEPERATOR;
                }
            }
        } else {
            $this->errorLog[]="Invalid array in function: ".__FUNCTION__." on line: ".__LINE__." in filename= ".__FILE__;
            return false;
        }
        return $output;
    }
    
    /**
     * Function to convert XML into string
     *
     * @param string $xml
     * @return string on success and false on failure
     */
    public function convertXmltoString($xml)
    {
        $array=$this->convertXmlToArray($xml);
        if ($array===false) {
            return false;
        } else {
            return $this->convertArrayToString($array);
        }
    }
}
