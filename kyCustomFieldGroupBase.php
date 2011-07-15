<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Base class for custom field group.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
abstract class kyCustomFieldGroupBase extends kyObjectBase {

	static protected $object_xml_name = 'group';
	protected $read_only = true;

	protected $id;
	protected $title;
	protected $fields;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->title = $data['_attributes']['title'];
		$this->fields = array();
		if (array_key_exists('field', $data)) {
			foreach ($data['field'] as $custom_field_data) {
				$this->fields[] = kyCustomField::createByType($custom_field_data);
			}
		}
	}

	static public function get($id) {
		throw new Exception(sprintf("You can't get single object of type %s.", get_called_class()));
	}

	public function refresh() {
		throw new Exception(sprintf("You can't refresh object of type %s.", get_called_class()));
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of this custom fields group.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns list of custom fields for this group.
	 *
	 * @return kyCustomField[]
	 */
	public function getFields() {
		return $this->fields;
	}
}