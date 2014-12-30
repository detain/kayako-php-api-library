<?php
/**
 * Base class for getting, creating, updating and deleting Kayako objects.
 *
 * All objects interacting with Kayako REST API should extend this class and
 * define:
 * * Kayako controller
 * * XML element name holding object's data in REST response
 * @see kyObjectBase::$controller
 * @see kyObjectBase::$object_xml_name
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+API
 * @package Object\Base
 */
abstract class kyObjectBase {

	/**
	 * Data key for storing files to send as multipart/form-data.
	 * @var string
	 */
	const FILES_DATA_NAME = '_files';

	/**
	 * Default Kayako controller used to operate on this objects. Override in descending classes.
	 * @var string
	 */
	static protected $controller = null;

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
	 * 		...repeat for every filtering enabled method...
	 * 	),
	 * 	...repeat for every object class...
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
	 * 		...repeat for every ordering enabled method...
	 * 	),
	 * 	...repeat for every object class...
	 * )
	 * @var string[]
	 */
	static protected $_order_methods = array();

	/**
	 * Cache for API fields.
	 * Format:
	 * array(
	 * 	'<class name>' => array(
	 * 		'<api field name>' => array(
	 * 			'property' => '<class property holding field value>',
	 * 			'description' => '<description of the field>',
	 * 			'getter' => '<value getter method or null if write-only>',
	 * 			'setter' => '<value setter method or null if read-only>',
	 * 			'required_create' => <present and true if this field is required for creating the object>,
	 * 			'required_update' => <present and true if this field is required for updating the object>
	 * 		),
	 * 		...repeat for every class api field...
	 * 	),
	 * 	...repeat for every object class...
	 * )
	 * @var array
	 */
	static private $_api_fields = null;

	/**
	 * Default constructor.
	 *
	 * @param array $data Object data from XML response converted into array.
	 */
	function __construct($data = null) {
		if ($data !== null)
			$this->parseData($data);
	}

	/**
	 * Creates new object. Compatible with method chaining.
	 */
	static public function createNew() {
		return new static();
	}

	/**
	 * Returns object controller.
	 *
	 * @return string
	 */
	static public function getController() {
		return static::$controller;
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
	 * @param bool $create Indicates if the result will be used to create (true) or update (false) an object.
	 * @return array
	 */
	public function buildData($create) {
		$this->checkRequiredAPIFields($create);
		return array();
	}

	/**
	 * Adds numeric field to object data array only if its value is proper number.
	 *
	 * @param array $data Data used to create or update the object.
	 * @param string $field_name Field name.
	 * @param mixed $field_value Field value.
	 */
	protected function buildDataNumeric(&$data, $field_name, $field_value) {
		if (is_numeric($field_value))
			$data[$field_name] = $field_value;
	}

	/**
	 * Adds string field to object data array only if its value is non-empty.
	 *
	 * @param array $data Data used to create or update the object.
	 * @param string $field_name Field name.
	 * @param mixed $field_value Field value.
	 */
	protected function buildDataString(&$data, $field_name, $field_value) {
		if (strlen($field_value) > 0)
			$data[$field_name] = $field_value;
	}

	/**
	 * Adds boolean field to object data array only if its value is non-empty.
	 *
	 * @param array $data Data used to create or update the object.
	 * @param string $field_name Field name.
	 * @param mixed $field_value Field value.
	 */
	protected function buildDataBool(&$data, $field_name, $field_value) {
		if ($field_value !== null)
			$data[$field_name] = $field_value ? 1 : 0;
	}

	/**
	 * Adds array field to object data array only if its value is proper array and is non-empty.
	 *
	 * @param array $data Data used to create or update the object.
	 * @param string $field_name Field name.
	 * @param mixed $field_value Field value.
	 */
	protected function buildDataList(&$data, $field_name, $field_value) {
		if (is_array($field_value) && count($field_value) > 0)
			$data[$field_name] = implode(',', $field_value);
	}

	/**
	 * Returns whether the object is new and not yet saved on the server.
	 *
	 * @return bool
	 */
	public function isNew() {
		return $this->getId() === null;
	}

	/**
	 * Returns whether this object is read only.
	 *
	 * @return bool
	 */
	public function isReadOnly() {
		return $this->read_only;
	}

	/**
	 * Sets whether this object is read only.
	 *
	 * @param bool $read_only Read only flag.
	 * @return bool
	 */
	public function setReadOnly($read_only) {
		$this->read_only = $read_only;
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
	 * Returns REST client.
	 *
	 * @return kyRESTClientInterface
	 */
	static protected function getRESTClient() {
		return kyConfig::get()->getRESTClient();
	}

	/**
	 * Fetches objects from server.
	 *
	 * @param array $search_parameters Optional. Additional search parameters.
	 * @return kyResultSet
	 */
	static public function getAll($search_parameters = array()) {
		$result = self::getRESTClient()->get(static::$controller, $search_parameters);
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
	 * @return kyObjectBase
	 */
	static public function get($id) {
		if (!is_array($id))
			$id = array($id);
		$result = self::getRESTClient()->get(static::$controller, $id);
		if (count($result) === 0)
			return null;
		return new static($result[static::$object_xml_name][0]);
	}

	/**
	 * Refreshes the object data from server.
	 *
	 * @throws BadMethodCallException
	 * @return kyObjectBase
	 */
	public function refresh() {
		if ($this->isNew())
			throw new BadMethodCallException("Object is not yet saved on server. Save it before refreshing.");

		$result = self::getRESTClient()->get(static::$controller, $this->getId(true));

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
	 * @throws BadMethodCallException
	 * @throws kyException
	 * @return kyObjectBase
	 */
	public function create() {
		if ($this->read_only)
			throw new BadMethodCallException(sprintf("You can't create new objects of type %s.", get_called_class()));

		$result = self::getRESTClient()->post(static::$controller, array(), $this->buildData(true));

		if (count($result) === 0)
			throw new kyException("No data returned by the server after creating the object.");

		$this->parseData($result[static::$object_xml_name][0]);
		return $this;
	}

	/**
	 * Updates the object on the server and refreshes its local data.
	 *
	 * @throws BadMethodCallException
	 * @throws kyException
	 * @return kyObjectBase
	 */
	public function update() {
		if ($this->read_only)
			throw new BadMethodCallException(sprintf("You can't update objects of type %s.", get_called_class()));

		if ($this->isNew())
			throw new BadMethodCallException(sprintf("You can't update object before it was created. Create it first.", get_called_class()));

		$result = self::getRESTClient()->put(static::$controller, $this->getId(true), $this->buildData(false));

		if (count($result) === 0)
			throw new kyException("No data returned by the server after updating the object.");

		$this->parseData($result[static::$object_xml_name][0]);
		return $this;
	}

	/**
	 * Saves (creates or updates) the object to the server.
	 *
	 * @return kyObjectBase
	 */
	public function save() {
		if ($this->isNew()) {
			return $this->create();
		} else {
			return $this->update();
		}
	}

	/**
	 * Deletes the object on the server.
	 */
	public function delete() {
		if ($this->read_only)
			throw new BadMethodCallException(sprintf("You can't delete object of type %s.", get_called_class()));

		self::getRESTClient()->delete(static::$controller, $this->getId(true));
	}

	/**
	 * Builds API fields list.
	 *
	 * Scans protected and private properties of called class, searches for
	 * @apiField [name=field name]{0,1} [alias=field name alias]* [accessor=setter/getter name]{0,1} [getter=getter name]{0,1} [setter=setter name]{0,1} [required_create=true if field if required when creating object]{0,1} [required_update=true if field if required when udpating object]{0,1} [required=true if field if required when creating or updating object]{0,1}
	 * and builds API field list with property name, description, setter and getter method names, and required flags.
	 * @see kyObjectBase::$_api_fields
	 */
	static private function initAPIFieldsAccessors() {
		$classname = get_called_class();

		if (self::$_api_fields === null || !array_key_exists($classname, self::$_api_fields)) {
			self::$_api_fields[$classname] = array();
			$class = new ReflectionClass($classname);
			foreach ($class->getProperties(ReflectionProperty::IS_PROTECTED|ReflectionProperty::IS_PUBLIC) as $property) {
				/* @var $property ReflectionProperty */
				$comment = $property->getDocComment();
				$comment_lines = explode("\n", $comment);
				$short_description = trim(next($comment_lines), " *\t\n\r");

				$parameters = ky_get_tag_parameters($comment, 'apiField');
				if ($parameters === false)
					continue;

				$api_field = null;
				$accessor = null;
				$setter = null;
				$getter = null;
				if (array_key_exists('name', $parameters)) {
					$api_field = $parameters['name'];
				}

				if (strlen($api_field) === 0) {
					$api_field = str_replace('_', '', $property->getName());
				}

				if (array_key_exists($api_field, self::$_api_fields[$classname]))
					continue;

				if (array_key_exists('accessor', $parameters)) {
					$accessor = $parameters['accessor'];
					$getter = sprintf('get%s', $accessor);
					$setter = sprintf('set%s', $accessor);
				}

				if (array_key_exists('getter', $parameters)) {
					$getter = $parameters['getter'];
				}

				if (array_key_exists('setter', $parameters)) {
					$setter = $parameters['setter'];
				}

				if (strlen($getter) === 0 && strlen($setter) === 0 ) {
					$name_parts = explode('_', $property->getName());
					foreach ($name_parts as $name_part) {
						$accessor .= ucfirst($name_part);
					}
					$getter = sprintf('get%s', $accessor);
					$setter = sprintf('set%s', $accessor);
				}

				if (!method_exists($classname, $setter)) {
					$setter = null;
				}

				if (!method_exists($classname, $getter)) {
					$getter = null;
				}

				if (strlen($getter) === 0 && strlen($setter) === 0)
					continue;

				$required_create = (array_key_exists('required', $parameters) && $parameters['required'] === 'true') || (array_key_exists('required_create', $parameters) && $parameters['required_create'] === 'true');
				$required_update = (array_key_exists('required', $parameters) && $parameters['required'] === 'true') || (array_key_exists('required_update', $parameters) && $parameters['required_update'] === 'true');

				$aliases = array();
				if (array_key_exists('alias', $parameters)) {
					if (!is_array($parameters['alias'])) {
						$aliases = array($parameters['alias']);
					} else {
						$aliases = $parameters['alias'];
					}
				}

				self::$_api_fields[$classname][$api_field] = array(
						'property' => $property->getName(),
						'description' => $short_description,
						'setter' => $setter,
						'getter' => $getter,
						'required_create' => $required_create,
						'required_update' => $required_update,
						'aliases' => $aliases
					);
			}
		}
	}

	/**
	 * Returns array of API fields.
	 *
	 * Format of returned array:
	 * <pre>
	 * array(
	 * 	'<api field name>' => Field description. (getter: <getter name>, setter: <setter name>),
	 * 	...repeat...
	 * )
	 * </pre>
	 *
	 * @return string[]
	 */
	static public function getAPIFields() {
		static::initAPIFieldsAccessors();
		$classname = get_called_class();

		$api_fields = array();
		foreach (self::$_api_fields[$classname] as $api_field => $api_field_parameters) {
			$required = array();
			if ($api_field_parameters['required_create'])
				$required[] = 'create';
			if ($api_field_parameters['required_update'])
				$required[] = 'update';

			$api_fields[$api_field] = sprintf("%s (getter: %s, setter: %s, required: %s, aliases: %s)",
					strlen($api_field_parameters['description']) > 0 ? $api_field_parameters['description'] : 'no description',
					strlen($api_field_parameters['getter']) > 0 ? $api_field_parameters['getter'] : 'no getter',
					strlen($api_field_parameters['setter']) > 0 ? $api_field_parameters['setter'] : 'no setter',
					count($required) > 0 ? implode(', ', $required) : 'no',
					count($api_field_parameters['aliases']) > 0 ? implode(', ', $api_field_parameters['aliases']) : 'none'
				);
		}

		return $api_fields;
	}

	/**
	 * Returns list of required API fields for objects of this class.
	 *
	 * @param bool $create True when object will be created. False when object will be updated.
	 * @return string[]
	 */
	static public function getRequiredAPIFields($create) {
		static::initAPIFieldsAccessors();
		$classname = get_called_class();

		$required_fields = array();
		foreach (self::$_api_fields[$classname] as $api_field => $api_field_parameters) {
			if (($create && $api_field_parameters['required_create'] === false) || (!$create && $api_field_parameters['required_update'] === false))
				continue;

			$required_fields[] = $api_field;
		}

		return $required_fields;
	}

	/**
	 * Checks wheter this object has all required fields set.
	 *
	 * @param bool $create True when object will be created. False when object will be updated.
	 * @param bool $throw_exception True to throw an exception on missing fields. False to return list of missing fields or true when there are none.
	 * @throws kyException When there are missing field values and $throw_exception is true.
	 * @return string[]|bool List of missing API fields or true when there are none.
	 */
	public function checkRequiredAPIFields($create, $throw_exception = true) {
		$classname = get_class($this);
		/** @noinspection PhpUndefinedMethodInspection */
		$classname::initAPIFieldsAccessors();

		$missing_fields = array();
		foreach (self::$_api_fields[$classname] as $api_field => $api_field_parameters) {
			if (($create && $api_field_parameters['required_create'] === false) || (!$create && $api_field_parameters['required_update'] === false))
				continue;

			$property = $api_field_parameters['property'];
			$property_value = $this->$property;
			if ($property_value === null || (is_array($property_value) && count($property_value) === 0)) {
				$missing_fields[] = $api_field;
			}
		}

		if (count($missing_fields) > 0) {
			if ($throw_exception)
				throw new kyException(sprintf("Values for API fields '%s' is required for this operation to complete.", implode(', ', $missing_fields)));

			return $missing_fields;
		}

		return true;
	}

	/**
	 * Returns API field value.
	 *
	 * Returns API field value based on API field name used by Kayako.
	 * @link http://wiki.kayako.com/display/DEV/REST+API+Reference
	 *
	 * @param string $api_field_name API field name.
	 * @return mixed
	 */
	public function __get($api_field_name) {
		static::initAPIFieldsAccessors();
		$classname = get_class($this);

		foreach (self::$_api_fields[$classname] as $api_field => $api_field_parameters) {
			if ($api_field !== $api_field_name && !in_array($api_field_name, $api_field_parameters['aliases']))
				continue;

			$api_field_getter = $api_field_parameters['getter'];
			if ($api_field_getter !== null) {
				return $this->$api_field_getter();
			} else {
				return null;
			}
		}

		trigger_error(sprintf('Undefined property: %s::$%s', $classname, $api_field_name), E_USER_NOTICE);
		return null;
	}

	/**
	 * Sets API field value.
	 *
	 * Sets API field value based on API field name used by Kayako.
	 * @link http://wiki.kayako.com/display/DEV/REST+API+Reference
	 *
	 * @param string $api_field_name API field name.
	 * @param mixed $value API field value.
	 * @return mixed
	 */
	public function __set($api_field_name, $value) {
		static::initAPIFieldsAccessors();
		$classname = get_class($this);

		foreach (self::$_api_fields[$classname] as $api_field => $api_field_parameters) {
			if ($api_field !== $api_field_name && !in_array($api_field_name, $api_field_parameters['aliases']))
				continue;

			$api_field_setter = $api_field_parameters['setter'];
			if ($api_field_setter !== null) {
				$this->$api_field_setter($value);
			} else {
				trigger_error(sprintf('Read-only property: %s::$%s', $classname, $api_field_name), E_USER_NOTICE);
			}
			return;
		}

		trigger_error(sprintf('Undefined property: %s::$%s', $classname, $api_field_name), E_USER_NOTICE);
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

			//get public methods in the class and search for @filterBy name=filter_name in doc comment
			$class = new ReflectionClass($class_name);
			foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				/** @var $method ReflectionMethod */
				$get_method_name = $method->getName();
				$method_comment = $method->getDocComment();
				$parameters = ky_get_tag_parameters($method_comment, 'filterBy');
				if ($parameters === false)
					continue;

				if (array_key_exists('name', $parameters)) {
					$filter_method_name = $parameters['name'];
				} else {
					$filter_method_name = preg_replace('/^get/', '', $get_method_name);
				}
				$filter_methods[kyResultSet::FILTER_PREFIX . $filter_method_name] = $get_method_name;
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

			//get public methods in the class and search for @orderBy order_method_name in doc comment
			$class = new ReflectionClass($class_name);
			foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				/** @var $method ReflectionMethod */
				$get_method_name = $method->getName();
				$method_comment = $method->getDocComment();
				$parameters = ky_get_tag_parameters($method_comment, 'orderBy');
				if ($parameters === false)
					continue;

				if (array_key_exists('name', $parameters)) {
					$order_method_name = $parameters['name'];
				} else {
					$order_method_name = preg_replace('/^get/', '', $get_method_name);
				}
				$order_methods[kyResultSet::ORDER_PREFIX . $order_method_name] = $get_method_name;
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
		return sprintf("%s (id: %s): %s\n", get_class($this), implode(', ', $this->getId(true)), $this->toString());
	}
}