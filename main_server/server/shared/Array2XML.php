<?php
function array2xml($array, $ret_string = true, $xml = null)
{
    if (is_null($xml)) {
        $xml = new XMLWriter();
        $xml->openMemory();
    }

    $array_sequence_index = 0;

    foreach ($array as $root => $child) {
        $elementName = is_string($root) ? $root : "row";
        $xml->startElement($elementName);
        if (is_array($child)) {
            $xml->writeAttribute("sequence_index", strval($array_sequence_index++));
            array2xml($child, false, $xml);
        } elseif (isset($child) && $child) {
            $xml->text($child);
        }
        $xml->endElement();
    }

    if ($ret_string) {
        return $xml->outputMemory(true);
    }
}
