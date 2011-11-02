<?php
/**
 * Helper functions for PHP client to REST API of Kayako v4 (Kayako Fusion).  
 */

if (!function_exists('ky_xml_to_array')) {
	/**
	 * Transforms XML data to array.
	 * 
	 * @param string $xml XML data.
	 * @param string[] $namespaces List of namespaces to include in parsing or empty to include all namespaces.
	 * @return array
	 */
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
	/**
	 * Outputs seconds in hh:mm:ss format.
	 * 
	 * @param int $seconds Seconds.
	 * @return string
	 */
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
	/**
	 * Outputs formatted bytes.
	 *  
	 * @param int $bytes Bytes.
	 * @return string
	 */
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

if (!function_exists('ky_usort_comparison')) {
	/**
	 * Helper class for sorting array with parametrized callback. 
	 */
	class kyUsort {
		private $callback;
		private $get_method_name;
		private $asc;

	    function __construct($callback, $get_method_name, $asc) {
	        $this->callback = $callback;
	        $this->get_method_name = $get_method_name;
	        $this->asc = $asc;
	    }

	    public function sort($a, $b) {
	        return call_user_func_array($this->callback, array($a, $b, $this->get_method_name, $this->asc));
	    }
	}

	/**
	 * Helper function for sorting array with parametrized callback. 
	 */
	function ky_usort_comparison($callback, $get_method_name, $asc) {
	    $usorter = new kyUsort($callback, $get_method_name, $asc);
	    return array($usorter, "sort");
	}
}
