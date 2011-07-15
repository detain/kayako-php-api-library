<?php
//objects
require_once("kyDepartment.php");
require_once("kyStaff.php");
require_once("kyStaffGroup.php");
require_once("kyTicket.php");
require_once("kyTicketAttachment.php");
require_once("kyTicketNote.php");
require_once("kyTicketPost.php");
require_once("kyTicketPriority.php");
require_once("kyTicketStatus.php");
require_once("kyTicketTimeTrack.php");
require_once("kyTicketType.php");
require_once("kyUser.php");
require_once("kyUserGroup.php");
require_once("kyUserOrganization.php");

//searches
require_once("kySearchTicket.php");

if (!function_exists('ky_xml_to_array')) {
	function ky_xml_to_array($xml, $namespaces = null) {
		$iter = 0;
		$arr = array();

		if (is_string($xml))
			$xml = new SimpleXMLElement($xml);

		if (!($xml instanceof SimpleXMLElement))
			return $arr;

		if ($namespaces === null)
			$namespaces = $xml->getDocNamespaces(true);

		foreach ($xml->attributes() as $attributeName => $attributeValue) {
			$arr["_attributes"][$attributeName] = trim($attributeValue);
		}
		foreach ($namespaces as $namespace_prefix => $namespace_name) {
			foreach ($xml->attributes($namespace_prefix, true) as $attributeName => $attributeValue) {
				$arr["_attributes"][$namespace_prefix.':'.$attributeName] = trim($attributeValue);
			}
		}

		$has_children = false;

		foreach ($xml->children() as $element) {

			$has_children = true;

			$elementName = $element->getName();

			if ($element->children()) {
				$arr[$elementName][] = ky_xml_to_array($element, $namespaces);
			} else {
				$shouldCreateArray = array_key_exists($elementName, $arr) && !is_array($arr[$elementName]);

				if ($shouldCreateArray) {
					$arr[$elementName] = array($arr[$elementName]);
				}

				$shouldAddValueToArray = array_key_exists($elementName, $arr) && is_array($arr[$elementName]);

				if ($shouldAddValueToArray) {
					$arr[$elementName][] = trim($element[0]);
				} else {
					$arr[$elementName] = trim($element[0]);
				}

			}

			$iter++;
		}

		if (!$has_children) {
			$arr['_contents'] = trim($xml[0]);
		}

		return $arr;
	}
}

if (!function_exists('ky_seconds_format')) {
	function ky_seconds_format($seconds) {
		if (!is_numeric($seconds))
			return $seconds;

		$minus = $seconds < 0 ? "-" : "";
		$seconds = abs($seconds);

		$formatted_seconds = str_pad(($seconds % 60), 2, "0", STR_PAD_LEFT);
		$minutes = floor($seconds / 60);
		$formatted_minutes = str_pad(($minutes % 60), 2, "0", STR_PAD_LEFT);
		$formatted_hours = str_pad(floor($minutes / 60), 2, "0", STR_PAD_LEFT);
		return sprintf("%s%s:%s:%s", $minus, $formatted_hours, $formatted_minutes, $formatted_seconds);
	}
}

if (!function_exists('ky_bytes_format')) {
	function ky_bytes_format($bytes) {
	    $unim = array("B","KB","MB","GB","TB","PB");
	    $c = 0;
	    while ($bytes>=1024) {
	        $c++;
	        $bytes = $bytes/1024;
	    }
	    return number_format($bytes,($c ? 2 : 0),",",".")." ".$unim[$c];
	}
}