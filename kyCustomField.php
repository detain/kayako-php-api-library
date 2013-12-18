<?php
/**
 * Class for custom field with text value and base class for other types of custom fields.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 * @package Object\CustomField
 */
class kyCustomField extends kyObjectBase {

	/**
	 * Field identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Field type.
	 * @apiField
	 * @var int
	 */
	protected $type;

	/**
	 * Field name.
	 * @apiField
	 * @var string
	 */
	protected $name;

	/**
	 * Field title.
	 * @apiField
	 * @var string
	 */
	protected $title;

	/**
	 * Field value.
	 * @apiField name=value getter=getRawValue setter=setValue
	 * @var string
	 */
	protected $raw_value;

	/**
	 * Custom field group this field belongs to.
	 * @var kyCustomFieldGroupBase
	 */
	protected $custom_field_group;

	/**
	 * Cache for field definition.
	 * @var kyCustomFieldDefinition
	 */
	protected $definition = null;

	/**
	 * Default constructor.
	 *
	 * @param kyCustomFieldGroupBase $custom_field_group Custom field group this field belongs to.
	 * @param array $data Object data from XML response converted into array.
	 */
	function __construct($custom_field_group, $data = null) {
		parent::__construct($data);
		$this->custom_field_group = $custom_field_group;
	}

	/**
	 * Creates proper class based on type of custom field.
	 *
	 * @param kyCustomFieldGroupBase $custom_field_group Custom field group this field belongs to.
	 * @param array $data Object data from XML response.
	 * @throws DomainException
	 * @return kyCustomField
	 */
	static public function createByType($custom_field_group, $data) {
		switch ($data['_attributes']['type']) {
			case kyCustomFieldDefinition::TYPE_TEXT:
			case kyCustomFieldDefinition::TYPE_TEXTAREA:
			case kyCustomFieldDefinition::TYPE_PASSWORD:
			case kyCustomFieldDefinition::TYPE_CUSTOM:
				return new kyCustomField($custom_field_group, $data);
			case kyCustomFieldDefinition::TYPE_RADIO:
			case kyCustomFieldDefinition::TYPE_SELECT:
				return new kyCustomFieldSelect($custom_field_group, $data);
			case kyCustomFieldDefinition::TYPE_LINKED_SELECT:
				return new kyCustomFieldLinkedSelect($custom_field_group, $data);
			case kyCustomFieldDefinition::TYPE_CHECKBOX:
			case kyCustomFieldDefinition::TYPE_MULTI_SELECT:
				return new kyCustomFieldMultiSelect($custom_field_group, $data);
			case kyCustomFieldDefinition::TYPE_DATE:
				return new kyCustomFieldDate($custom_field_group, $data);
			case kyCustomFieldDefinition::TYPE_FILE:
				return new kyCustomFieldFile($custom_field_group, $data);
		}
		throw new DomainException("Unknown custom field type.");
	}

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->name = $data['_attributes']['name'];
		$this->type = intval($data['_attributes']['type']);
		$this->title = $data['_attributes']['title'];
		$this->raw_value = $data['_contents'];
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data[$this->name] = $this->raw_value;

		return $data;
	}

	static public function get($id) {
		throw new BadMethodCallException(sprintf("You can't get single object of type %s.", get_called_class()));
	}

	static public function getAll($search_parameters = array()) {
		throw new BadMethodCallException(sprintf("You can't get all objects of type %s this way. Use kyCustomFieldGroupBase extending classes getAll method instead or relevant methods of objects extending kyObjectWithCustomFieldsBase class.", get_called_class()));
	}

	public function create() {
		throw new BadMethodCallException(sprintf("You can't create objects of type %s.", get_called_class()));
	}

	public function update() {
		throw new BadMethodCallException(sprintf("You can't update single custom fields of type %s. Use updateCustomFields method of objects extending kyObjectWithCustomFieldsBase class.", get_called_class()));
	}

	public function delete() {
		throw new BadMethodCallException(sprintf("You can't delete objects of type %s.", get_called_class()));
	}

	public function refresh() {
		throw new BadMethodCallException(sprintf("You can't refresh objects of type %s.", get_called_class()));
	}

	public function __toString() {
		return sprintf("%s (id: %s, name: %s): %s\n", get_class($this), implode(', ', $this->getId(true)), $this->getName(), $this->toString());
	}

	public function toString() {
		return sprintf("%s = %s", $this->getTitle(), $this->getRawValue());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns type of this custom field - one of kyCustomFieldBase::TYPE_ constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns name of this custom field.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns title of this custom field.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns raw text value of this custom field.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getRawValue() {
		return $this->raw_value;
	}

	/**
	 * Returns value of this custom field.
	 * Method is overloaded in descendant classes and return value interpretation depends on field type.
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->raw_value;
	}

	/**
	 * Sets the value of this custom field.
	 * Method is overloaded in descendant classes and value interpretation depends on field type.
	 *
	 * @param string $value Value.
	 * @return kyCustomField
	 */
	public function setValue($value) {
		$this->raw_value = ky_assure_string($value);
		return $this;
	}

	/**
	 * Returns field definition.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyCustomFieldDefinition
	 */
	public function getDefinition($reload = false) {
		if ($this->definition !== null && !$reload)
			return $this->definition;

		/** @noinspection PhpUndefinedMethodInspection */
		$this->definition = kyCustomFieldDefinition::getAll()->filterByName($this->getName())->first();
		return $this->definition;
	}

	/**
	 * Returns field option with provided identifier or value.
	 * Returns null if option was not found.
	 *
	 * @param mixed $value Identifier of option OR value of option OR option.
	 * @return kyCustomFieldOption
	 */
	public function getOption($value) {
		if (is_numeric($value)) { //value is option identifier
			return $this->getDefinition()->getOptionById($value);
		} elseif (is_string($value)) { //value is option value
			return $this->getDefinition()->getOptionByValue($value);
		} elseif ($value instanceof kyCustomFieldOption) { //value is option itself
			return $value;
		}
		return null;
	}
}