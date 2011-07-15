<?php
require_once('kyBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Base class for getting, creating, updating and deleting Kayako objects.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
abstract class kyObjectBase extends kyBase {

	/**
	 * Indicates the name of object element in XML response. Override in descending classes.
	 * @var string
	 */
	static protected $object_xml_name = null;

	/**
	 * Controls if the object can be created/updated/deleted. Override in descending classes.
	 * @var bool
	 */
	protected $read_only = false;

	/**
	 * Cache for available filter methods.
	 * Format:
	 * array(
	 *  '<class name>' => array(
	 * 		'<filter method name>' => '<get method name>',
	 * 		...
	 * 	),
	 * 	...
	 * )
	 * @var string[]
	 */
	static protected $_filter_methods = array();

	/**
	 * Cache for available order methods.
	 * Format:
	 * array(
	 *  '<class name>' => array(
	 * 		'<order method name>' => '<get method name>',
	 * 		...
	 * 	),
	 * 	...
	 * )
	 * @var string[]
	 */
	static protected $_order_methods = array();

	/**
	 * Prefix for filter methods.
	 * @var string
	 */
	const FILTER_PREFIX = "filterBy";

	/**
	 * Prefix for order methods.
	 * @var string
	 */
	const ORDER_PREFIX = "orderBy";

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
	 * Should return short (one line) description of the object (it's title, name, etc.).
	 *
	 * @return string
	 */
	abstract public function toString();

	/**
	 * Fetches objects from server.
	 *
	 * @param array $search_parameters Optional. Additional search parameters.
	 * @return kyResultSet
	 */
	static public function getAll($search_parameters = array()) {
		$result = static::_get($search_parameters);
		$objects = array();
		if (array_key_exists(static::$object_xml_name, $result)) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new static($object_data);
			}
		}
		return new kyResultSet($objects);
	}

	/**
	 * Fetches the object from server.
	 *
	 * @param int|array $id Object identifier or list of identifiers (ex. ticket identifier and ticket post identifier when fetching TicketPost).
	 * @return self
	 */
	static public function get($id) {
		if (!is_array($id))
			$id = array($id);
		$result = static::_get($id);
		if (count($result) === 0)
			return null;
		return new static($result[static::$object_xml_name][0]);
	}

	/**
	 * Refreshes the object data from server.
	 *
	 * @return self
	 */
	public function refresh() {
		$result = static::_get($this->getId(true));

		/**
		 * Clear all object properties.
		 */
   	    foreach ($this as $key => $value) {
           	$this->$key = null;
       	}

		$this->parseData($result[static::$object_xml_name][0]);
		return $this;
	}

	/**
	 * Creates an object on the server and refreshes its local data.
	 *
	 * @return self
	 */
	public function create() {
		if ($this->read_only)
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
		if ($this->read_only)
			throw new Exception(sprintf("You can't update objects of type %s.", get_called_class()));

		$result = static::_put($this->getId(true), $this->buildData(self::METHOD_PUT));
		$this->parseData($result[static::$object_xml_name][0]);
		return $this;
	}

	/**
	 * Deletes the object on the server.
	 */
	public function delete() {
		if ($this->read_only)
			throw new Exception(sprintf("You can't delete object of type %s.", get_called_class()));

		static::_delete($this->getId(true));
	}

	/**
	 * Returns list of available filter methods for use in result sets with objects of this type.
	 * Optionaly you can return get method names used to filter objects.
	 *
	 * @param bool $filter_names_only True (default) to return array('filterByXXX', 'filterByYYY', ...). False to return array('filterByXXX' => 'getXXX', 'filterByYYY' => 'YYY', ...).
	 * @return array
	 */
	static public function getAvailableFilterMethods($filter_names_only = true) {
		$class_name = get_called_class();
		if (!array_key_exists($class_name, self::$_filter_methods)) {
			$filter_methods = array();

			//get public methods in the class and search for @fitlerBy(filter_method_name) in doc comment
			$class = new ReflectionClass($class_name);
			foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				$get_method_name = $method->getName();
				$method_comment = $method->getDocComment();
				$result = preg_match('/@filterBy\((.*)\)/', $method_comment, $matches);
				if ($result === 1 && count($matches) === 2) {
					if (strlen($matches[1]) > 0) {
						$filter_method_name = $matches[1];
					} else {
						$filter_method_name = preg_replace('/^get/', '', $get_method_name);
					}
					$filter_methods[self::FILTER_PREFIX . $filter_method_name] = $get_method_name;
				}
			}

			self::$_filter_methods[$class_name] = $filter_methods;
		}

		return $filter_names_only ? array_keys(self::$_filter_methods[$class_name]) : self::$_filter_methods[$class_name];
	}

	/**
	 * Returns list of available order methods for use in result sets with objects of this type.
	 * Optionaly you can return get method names used to order objects.
	 *
	 * @param bool $order_names_only True (default) to return array('orderByXXX', 'orderByYYY', ...). False to return array('orderByXXX' => 'getXXX', 'orderByYYY' => 'YYY', ...).
	 * @return array
	 */
	static public function getAvailableOrderMethods($order_names_only = true) {
		$class_name = get_called_class();
		if (!array_key_exists($class_name, self::$_order_methods)) {
			$order_methods = array();

			//get public methods in the class and search for @orderBy(order_method_name) in doc comment
			$class = new ReflectionClass($class_name);
			foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				$get_method_name = $method->getName();
				$method_comment = $method->getDocComment();
				$result = preg_match('/@orderBy\((.*)\)/', $method_comment, $matches);
				if ($result === 1 && count($matches) === 2) {
					if (strlen($matches[1]) > 0) {
						$order_method_name = $matches[1];
					} else {
						$order_method_name = preg_replace('/^get/', '', $get_method_name);
					}
					$order_methods[self::ORDER_PREFIX . $order_method_name] = $get_method_name;
				}
			}

			self::$_order_methods[$class_name] = $order_methods;
		}

		return $order_names_only ? array_keys(self::$_order_methods[$class_name]) : self::$_order_methods[$class_name];
	}

	/**
	 * Returns object description with it's type and identifier.
	 * Calls toString() method to get the object description.
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf("%s (id: %s): %s", get_class($this), implode(', ', $this->getId(true)), $this->toString());
	}
}