<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako TicketType object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicketType extends kyObjectBase {

	const TYPE_PUBLIC = 'public';
	const TYPE_PRIVATE = 'private';

	static protected $controller = '/Tickets/TicketType';
	static protected $object_xml_name = 'tickettype';
	static protected $read_only = true;

	private $id = null;
	private $title = null;
	private $display_order = null;
	private $department_id = null;
	private $display_icon = null;
	private $type = null;
	private $user_visibility_custom = null;
	private $user_group_ids = array();

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->display_order = intval($data['displayorder']);
		$this->department_id = intval($data['departmentid']);
		$this->display_icon = $data['displayicon'];
		$this->type = $data['type'];
		$this->user_visibility_custom = intval($data['uservisibilitycustom']) === 0 ? false : true;
		if ($this->user_visibility_custom && is_array($data['usergroupid'])) {
			foreach ($data['usergroupid'] as $user_group_id) {
				$this->user_group_ids[] = intval($user_group_id);
			}
		}
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return int
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 *
	 * @return int
	 */
	public function getDepartmentId() {
		return $this->department_id;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyDepartment
	 */
	public function getDepartment() {
		if ($this->department_id === null || $this->department_id <= 0)
			return null;

		return kyDepartment::get($this->department_id);
	}

	/**
	 *
	 * @return string
	 */
	public function getDisplayIcon() {
		return $this->display_icon;
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @return bool
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 *
	 * @return int[]
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyUserGroup[]
	 */
	public function getUserGroups() {
		$user_groups = array();
		foreach ($this->user_group_ids as $user_group_id) {
			$user_groups[] = kyUserGroup::get($user_group_id);
		}
		return $user_groups;
	}
}