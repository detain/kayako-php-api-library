<?php
require_once('kyBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Base class for getting, creating, updating and deleting Kayako objects.
 *
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
 */
abstract class kyObjectBase extends kyBase {

	/**
	 * Indicates the name of object element in XML response.
	 * @var string
	 */
	static protected $object_xml_name = null;

	/**
	 * Controls if the object can be created/updated/deleted.
	 * @var bool
	 */
	static protected $read_only = false;

	/**
	 * Default constructor.
	 *
	 * @param array $data Object data from XML response converted into array.
	 */
	function __construct($data = null) {
		parent::__construct();
		if ($data !== null)
			$this->parseData($data);
	}

	/**
	 * Should use passed data to fill object properties.
	 *
	 * @param array $data Object data from XML response.
	 */
	abstract protected function parseData($data);

	/**
	 * Should build the array of object data for creating or updating the object.
	 * Values must be set in format accepted by REST API.
	 *
	 * @param string $method Indicates if the result will be used to create (POST) or update (PUT) an object.
	 * @return array
	 */
	protected function buildData($method) {
		return array();
	}

	/**
	 * Should return object identifier or complete list of identifiers as needed by API to identify the object (ex. ticket identifier and ticket post identifier in case of TicketPost).
	 *
	 * @param bool $complete True to return complete list of identifiers as needed by API to identify the object.
	 * @return int|array
	 */
	abstract public function getId($complete = false);

	/**
	 * Fetches objects from server.
	 *
	 * @param array $search_parameters Optional. Additional search parameters.
	 * @return self[]
	 */
	static public function getAll($search_parameters = array()) {
		$class_name = get_called_class();
		$result = static::_get($search_parameters);
		$objects = array();
		if (array_key_exists(static::$object_xml_name, $result)) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new $class_name($object_data);
			}
		}
		return $objects;
	}

	/**
	 * Fetches the object from server.
	 *
	 * @param int|array $id Object identifier or list of identifiers (ex. ticket identifier and ticket post identifier when fetching TicketPost).
	 * @return self
	 */
	static public function get($id) {
		$class_name = get_called_class();
		if (!is_array($id))
			$id = array($id);
		$result = static::_get($id);
		if (count($result) === 0)
			return null;
		return new $class_name($result[static::$object_xml_name][0]);
	}

	/**
	 * Refreshes the object data from server.
	 *
	 * @return self
	 */
	public function refresh() {
		$result = static::_get($this->getId(true));
		$this->parseData($result[static::$object_xml_name][0]);
		return $this;
	}

	/**
	 * Creates an object on the server and refreshes its local data.
	 *
	 * @return self
	 */
	public function create() {
		if (static::$read_only)
			throw new Exception(sprintf("You can't create new objects of type %s.", get_called_class()));

		$result = static::_post(array(), $this->buildData(self::METHOD_POST));
		$this->parseData($result[static::$object_xml_name][0]);
		return $this;
	}

	/**
	 * Updates the object on the server and refreshes its local data.
	 *
	 * @return self
	 */
	public function update() {
		if (static::$read_only)
			throw new Exception(sprintf("You can't update objects of type %s.", get_called_class()));

		$result = static::_put($this->getId(true), $this->buildData(self::METHOD_PUT));
		$this->parseData($result[static::$object_xml_name][0]);
		return $this;
	}

	/**
	 * Deletes the object on the server.
	 */
	public function delete() {
		if (static::$read_only)
			throw new Exception(sprintf("You can't delete object of type %s.", get_called_class()));

		static::_delete($this->getId(true));
	}
}