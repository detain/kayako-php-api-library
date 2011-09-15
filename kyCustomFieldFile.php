<?php
require_once('kyCustomField.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Class for custom field holding file data.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyCustomFieldFile extends kyCustomField {

	protected $filename;
	protected $contents;

	protected function parseData($data) {
		parent::parseData($data);
		$this->filename = $data['_attributes']['filename'];
		$this->contents = base64_decode($data['_contents']);
	}

	/**
	 * Returns filename of the file.
	 *
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * Returns raw contents of the file.
	 *
	 * @return string
	 */
	public function getContents() {
		return $this->contents;
	}
}