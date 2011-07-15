<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Kayako Department object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+Department
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
 */
class kyDepartment extends kyObjectBase {

	const MODULE_TICKETS = 'tickets';
	const MODULE_LIVECHAT = 'livechat';

	const TYPE_PUBLIC = 'public';
	const TYPE_PRIVATE = 'private';

	static protected $controller = '/Base/Department';
	static protected $object_xml_name = 'department';

	private $id = null;
	private $title = null;
	private $type = null;
	private $module = null;
	private $display_order = null;
	private $parent_department_id = null;
	private $user_visibility_custom = false;
	private $user_group_ids = array();

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->type = $data['type'];
		$this->module = $data['module'];
		$this->display_order = intval($data['displayorder']);
		$this->parent_department_id = intval($data['parentdepartmentid']);
		if ($this->parent_department_id <= 0)
			$this->parent_department_id = null;
		$this->user_visibility_custom = intval($data['uservisibilitycustom']) === 0 ? false : true;
		if ($this->user_visibility_custom && is_array($data['usergroups'])) {
			if (is_string($data['usergroups'][0]['id'])) {
				$this->user_group_ids[] = intval($data['usergroups'][0]['id']);
			} else {
				foreach ($data['usergroups'][0]['id'] as $user_group_id) {
					$this->user_group_ids[] = intval($user_group_id);
				}
			}
		}
	}

	protected function buildData($method) {
		$data = array();

		$data['title'] = $this->title;
		$data['type'] = $this->type;
		$data['module'] = $this->module;

		if (is_numeric($this->display_order))
			$data['displayorder'] = $this->display_order;

		if (is_numeric($this->parent_department_id))
			$data['parentdepartmentid'] = $this->parent_department_id;

		$data['uservisibilitycustom'] = $this->user_visibility_custom ? 1 : 0;

		if ($this->user_visibility_custom) {
			$data['usergroupid'] = $this->user_group_ids;
		}

		return $data;
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
	 * @param string $title
	 * @return kyDepartment
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
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
	 * @param string $type
	 * @return kyDepartment
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 *
	 * @param string $module
	 * @return kyDepartment
	 */
	public function setModule($module) {
		$this->module = $module;
		return $this;
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
	 * @param int $display_order
	 * @return kyDepartment
	 */
	public function setDisplayOrder($display_order) {
		$this->display_order = $display_order;
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getParentDeparmentId() {
		return $this->parent_department_id;
	}

	/**
	 *
	 * @param int $parent_department_id
	 * @return kyDepartment
	 */
	public function setParentDepartmentId($parent_department_id) {
		$this->parent_department_id = $parent_department_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyDepartment
	 */
	public function getParentDeparment() {
		if ($this->parent_department_id === null || $this->parent_department_id <= 0)
			return null;

		return kyDepartment::get($this->parent_department_id);
	}

	/**
	 *
	 * @param kyDepartment $parent_department
	 * @return kyDepartment
	 */
	public function setParentDepartment($parent_department) {
		$this->parent_department_id = $parent_department->getId();
		return $this;
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
	 * @param bool $user_visibility_custom
	 * @return kyDepartment
	 */
	public function setUserVisibilityCustom($user_visibility_custom) {
		$this->user_visibility_custom = $user_visibility_custom;
		return $this;
	}

	/**
	 *
	 * @return array
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 *
	 * @param int[] $user_group_ids
	 * @return kyDepartment
	 */
	public function setUserGroupIds($user_group_ids) {
		$this->user_group_ids = $user_group_ids;
		return $this;
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

	/**
	 *
	 * @param kyUserGroup $user_group
	 * @param bool $clear Clear the list before adding.
	 * @return kyDepartment
	 */
	public function addUserGroup($user_group, $clear = false) {
		if ($clear)
			$this->user_group_ids = array();
		$this->user_group_ids[] = $user_group->getId();
		return $this;
	}
}