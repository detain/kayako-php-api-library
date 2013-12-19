<?php
/**
 * Class for custom field definition (properties).
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+CustomField
 * @since Kayako version 4.40.1079
 * @package Object\CustomField
 */
class kyCustomFieldDefinition extends kyObjectBase {

	const TYPE_TEXT = 1;

	const TYPE_TEXTAREA = 2;

	const TYPE_PASSWORD = 3;

	const TYPE_CHECKBOX = 4;

	const TYPE_RADIO = 5;

	const TYPE_SELECT = 6;

	const TYPE_MULTI_SELECT = 7;

	const TYPE_CUSTOM = 8;

	const TYPE_LINKED_SELECT = 9;

	const TYPE_DATE = 10;

	const TYPE_FILE = 11;

	static protected $controller = '/Base/CustomField';

	static protected $object_xml_name = 'customfield';

	protected $read_only = true;

	/**
	 * Field identifier.
	 *
	 * @apiField name=customfieldid
	 * @var int
	 */
	protected $id;

	/**
	 * Field group identifier.
	 *
	 * @apiField name=customfieldgroupid
	 * @var int
	 */
	protected $group_id;

	/**
	 * Field type.
	 *
	 * @apiField name=fieldtype
	 * @var int
	 */
	protected $type;

	/**
	 * Field name.
	 *
	 * @apiField name=fieldname
	 * @var string
	 */
	protected $name;

	/**
	 * Field title.
	 *
	 * @apiField
	 * @var string
	 */
	protected $title;

	/**
	 * Field default value.
	 *
	 * @apiField
	 * @var string
	 */
	protected $default_value;

	/**
	 * Field required flag.
	 *
	 * @apiField
	 * @var bool
	 */
	protected $is_required;

	/**
	 * Field user editable flag.
	 *
	 * @apiField name=usereditable
	 * @var bool
	 */
	protected $is_user_editable;

	/**
	 * Field staff editable flag.
	 *
	 * @apiField name=staffeditable
	 * @var bool
	 */
	protected $is_staff_editable;

	/**
	 * Field PERL regexp for value validation.
	 *
	 * @apiField
	 * @var string
	 */
	protected $regexp_validate;

	/**
	 * Field display order.
	 *
	 * @apiField
	 * @var int
	 */
	protected $display_order;

	/**
	 * Field encryption flag.
	 *
	 * @apiField name=encryptindb
	 * @var bool
	 */
	protected $is_encrypted;

	/**
	 * Field description.
	 *
	 * @apiField
	 * @var string
	 */
	protected $description;

	/**
	 * Field possible options.
	 *
	 * @var kyCustomFieldOption[]
	 */
	private $options = null;

	/**
	 * Cache for all field definitions.
	 *
	 * @var kyCustomFieldDefinition[]
	 */
	static private $definitions = null;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['customfieldid']);
		$this->group_id = intval($data['_attributes']['customfieldgroupid']);
		$this->type = intval($data['_attributes']['fieldtype']);
		$this->name = $data['_attributes']['fieldname'];
		$this->title = $data['_attributes']['title'];
		$this->default_value = $data['_attributes']['defaultvalue'];
		$this->is_required = intval($data['_attributes']['isrequired']) === 0 ? false : true;
		$this->is_user_editable = intval($data['_attributes']['usereditable']) === 0 ? false : true;
		$this->is_staff_editable = intval($data['_attributes']['staffeditable']) === 0 ? false : true;
		$this->regexp_validate = $data['_attributes']['regexpvalidate'];
		$this->display_order = $data['_attributes']['displayorder'];
		$this->is_encrypted = intval($data['_attributes']['encryptindb']) === 0 ? false : true;
		$this->description = $data['_attributes']['description'];
	}

	/**
	 * Fetches field definitions from server.
	 * Caches the result - call kyCustomFieldDefinition::clearCache() to clear the cache.
	 *
	 * @param array $search_parameters Optional. Additional search parameters.
	 * @return kyResultSet
	 */
	static public function getAll($search_parameters = array()) {
		if (self::$definitions === null) {
			self::$definitions = parent::getAll($search_parameters);
		}

		return self::$definitions;
	}

	/**
	 * Clears custom field definitions cache.
	 */
	static public function clearCache() {
		self::$definitions = null;
	}

	static public function get($id) {
		throw new BadMethodCallException(sprintf("You can't get single object of type %s.", get_called_class()));
	}

	public function refresh() {
		throw new BadMethodCallException(sprintf("You can't refresh object of type %s.", get_called_class()));
	}

	public function toString() {
		$class = new ReflectionClass(get_class($this));
		$constants = array_flip($class->getConstants());
		return sprintf("%s (type: %s, required: %s)", $this->getTitle(), strtolower(str_replace(array("TYPE_", "_"), array("", " "), $constants[$this->getType()])), $this->getIsRequired() ? "yes" : "no");
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of this custom fields group.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns identifier of custom field group that current field belongs to.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getGroupId() {
		return $this->group_id;
	}

	/**
	 * Returns field type (one of self::TYPE_ constants).
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns field name.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns field default value.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDefaultValue() {
		switch ($this->type) {
			case self::TYPE_TEXT:
			case self::TYPE_TEXTAREA:
			case self::TYPE_PASSWORD:
				return $this->default_value;
				break;

			case self::TYPE_DATE:
				return date(kyConfig::get()->getDateFormat(), $this->default_value);
				break;

			default:
				return null;
				break;
		}
	}

	/**
	 * Returns whether field is required.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsRequired() {
		return $this->is_required;
	}

	/**
	 * Returns whether field is user editable.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsUserEditable() {
		return $this->is_user_editable;
	}

	/**
	 * Returns whether field is staff editable.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsStaffEditable() {
		return $this->is_staff_editable;
	}

	/**
	 * Returns regexp to validate field value.
	 *
	 * @return string
	 */
	public function getRegexpValidate() {
		return $this->regexp_validate;
	}

	/**
	 * Returns field display order.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Returns whether field is encrypted in database.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getIsEncrypted() {
		return $this->is_encrypted;
	}

	/**
	 * Returns field description.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Returns field's possible options or empty list.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyResultSet
	 */
	public function getOptions($reload = false) {
		if ($this->options !== null && !$reload)
			return $this->options;

		switch ($this->type) {
			case self::TYPE_CHECKBOX:
			case self::TYPE_LINKED_SELECT:
			case self::TYPE_MULTI_SELECT:
			case self::TYPE_RADIO:
			case self::TYPE_SELECT:
				$this->options = kyCustomFieldOption::getAll($this->getId(true));
				break;
			default:
				$this->options = new kyResultSet(array(), 'kyCustomFieldOption');
		}

		return $this->options;
	}

	/**
	 * Returns field options which has the provided value.
	 *
	 * @param string $value Value to search for.
	 * @return kyCustomFieldOption
	 */
	public function getOptionByValue($value) {
		foreach ($this->getOptions() as $field_option) {
			/* @var $field_option kyCustomFieldOption */
			if ($field_option->getValue() == $value)
				return $field_option;
		}

		return null;
	}

	/**
	 * Returns field options which has the provided identifier.
	 *
	 * @param int $id Identifier to search for.
	 * @return kyCustomFieldOption
	 */
	public function getOptionById($id) {
		foreach ($this->getOptions() as $field_option) {
			/* @var $field_option kyCustomFieldOption */
			if ($field_option->getId() == $id)
				return $field_option;
		}

		return null;
	}

	/**
	 * Returns options selected by default.
	 *
	 * @return kyResultSet
	 */
	public function getDefaultOptions() {
		switch ($this->type) {
			case self::TYPE_CHECKBOX:
			case self::TYPE_LINKED_SELECT:
			case self::TYPE_MULTI_SELECT:
			case self::TYPE_RADIO:
			case self::TYPE_SELECT:
				/** @noinspection PhpUndefinedMethodInspection */
				return $this->getOptions()->filterByIsSelected();
			default:
				return new kyResultSet(array(), 'kyCustomFieldOption');
		}
	}
}