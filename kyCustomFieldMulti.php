<?php
require_once('kyCustomField.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Class for custom field which has multiple values.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyCustomFieldMulti extends kyCustomField {

	protected $values;

	protected function parseData($data) {
		parent::parseData($data);
		//don't use commas when defining possible values, because it will break this - http://dev.kayako.com/browse/SWIFT-1449
		$this->values = explode(', ', $data['_contents']);
	}

	/**
	 * Returns list of values of this custom field.
	 *
	 * @return string[]
	 */
	public function getValues() {
		return $this->values;
	}
}