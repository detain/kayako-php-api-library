<?php
/**
 * Class for custom fields options.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+CustomField
 * @since Kayako version 4.40.1079
 * @package Object\CustomField
 */
class kyCustomFieldOption extends kyObjectBase {

	static protected $controller = '/Base/CustomField/ListOptions';
	static protected $object_xml_name = 'option';
	protected $read_only = true;

	/**
	 * Field option identifier.
	 * @apiField name=customfieldoptionid
	 * @var int
	 */
	protected $id;

	/**
	 * Custom field identifier.
	 * @apiField name=customfieldid
	 * @var int
	 */
	protected $field_id;

	/**
	 * Field option value.
	 * @apiField name=optionvalue
	 * @var string
	 */
	protected $value;

	/**
	 * Display order.
	 * @apiField
	 * @var int
	 */
	protected $display_order;

	/**
	 * Is this option selected by default.
	 * @apiField
	 * @var bool
	 */
	protected $is_selected;

	/**
	 * Parent field option identifier (for linked selects).
	 * @apiField name=parentcustomfieldoptionid
	 * @var int
	 */
	protected $parent_option_id;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['customfieldoptionid']);
		$this->field_id = intval($data['_attributes']['customfieldid']);
		$this->value = $data['_attributes']['optionvalue'];
		$this->display_order = $data['_attributes']['displayorder'];
		$this->is_selected = intval($data['_attributes']['isselected']) === 0 ? false : true;
		$this->parent_option_id = intval($data['_attributes']['parentcustomfieldoptionid']);
		if ($this->parent_option_id === 0)
			$this->parent_option_id = null;
	}

	static public function get($id) {
		throw new BadMethodCallException(sprintf("You can't get single object of type %s.", get_called_class()));
	}

	public function refresh() {
		throw new BadMethodCallException(sprintf("You can't refresh object of type %s.", get_called_class()));
	}

	public function toString() {
		return sprintf("%s (selected by default: %s)", $this->getValue(), $this->getIsSelected() ? "yes" : "no");
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns field id.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getFieldId() {
		return $this->field_id;
	}

	/**
	 * Returns option value.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Returns option display order.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Returns whether this option is selected by default.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsSelected() {
		return $this->is_selected;
	}

	/**
	 * Returns parent option id (for linked selects).
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getParentOptionId() {
		return $this->parent_option_id;
	}
}