<?php
require_once('kyCustomField.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Class for custom field holding date.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyCustomFieldDate extends kyCustomField {

	protected $date;

	protected function parseData($data) {
		parent::parseData($data);
		$this->date = strtotime($data['_contents']);
	}

	/**
	 * Returns field value as formatted date.
	 *
	 * @param string $format Output format of the date.
	 * @return string
	 */
	public function getDate($format = 'Y-m-d') {
		return date($format, $this->date);
	}
}