<?php
/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Base class for custom fields.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyCustomField {

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

	protected $id;
	protected $type;
	protected $title;
	protected $value;

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
	 * Should use passed data to fill object properties.
	 *
	 * @param array $data Object data from XML response.
	 */
	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->type = intval($data['_attributes']['type']);
		$this->title = $data['_attributes']['title'];
		$this->value = $data['_contents'];
	}

	/**
	 * Creates proper class based on type of custom field.
	 *
	 * @param array $data Object data from XML response.
	 * @return kyCustomField
	 */
	static public function createByType($data) {
		switch ($data['_attributes']['type']) {
			case self::TYPE_TEXT:
			case self::TYPE_TEXTAREA:
			case self::TYPE_PASSWORD:
			case self::TYPE_RADIO:
			case self::TYPE_SELECT:
			case self::TYPE_CUSTOM:
			case self::TYPE_LINKED_SELECT:
				return new kyCustomField($data);
			case self::TYPE_CHECKBOX:
			case self::TYPE_MULTI_SELECT:
				return new kyCustomFieldMulti($data);
			case self::TYPE_DATE:
				return new kyCustomFieldDate($data);
			case self::TYPE_FILE:
				return new kyCustomFieldFile($data);
		}
	}

	/**
	 * Returns custom field identifier.
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Returns type of this custom field - one of kyCustomFieldBase::TYPE_ constants.
	 *
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns title of this custom field.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns raw value of this custom field.
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
}