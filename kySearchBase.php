<?php
require_once('kyBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Base class for searching Kayako objects.
 * EXPERIMENTAL.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
abstract class kySearchBase extends kyBase {

	/**
	 * Indicates the name of object element in XML response.
	 * @var string
	 */
	static protected $object_xml_name = null;

	/**
	 * Indicates class name of objects being result of this search helper.
	 * @var string
	 */
	static protected $object_class_name = null;

	/**
	 * Should return list of parameters for GET request.
	 *
	 * @return array
	 */
	abstract protected function buildParameters();

	/**
	 * Performs the search.
	 *
	 * @return self[]
	 */
	public function search() {
		$result = static::_get($this->buildParameters());
		$objects = array();
		if (is_array($result) && count($result) > 0) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new static::$object_class_name($object_data);
			}
		}
		return $objects;
	}
}