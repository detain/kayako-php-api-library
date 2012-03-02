<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako Department object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+Department
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyDepartment extends kyObjectBase {

	/**
	 * Module a department can be associated with - Tickets.
	 *
	 * @var string
	 */
	const MODULE_TICKETS = 'tickets';

	/**
	 * Module a department can be associated with - Livechat.
	 *
	 * @var string
	 */
	const MODULE_LIVECHAT = 'livechat';

	/**
	 * Type of department - public.
	 *
	 * @var string
	 */
	const TYPE_PUBLIC = 'public';

	/**
	 * Type of department - private.
	 *
	 * @var string
	 */
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

	private $parent_department = null;
	private $user_groups = null;

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
			$this->user_group_ids = array();
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

		//TODO: check if required parameters are present

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

	public function toString() {
		return sprintf("%s (type: %s, module: %s)", $this->getTitle(), $this->getType(), $this->getModule());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of the department.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the department.
	 *
	 * @param string $title Title of the department.
	 * @return kyDepartment
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Return type of the department - one of kyDepartment::TYPE_* constants.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets type of the department.
	 *
	 * @param string $type Type of the department - one of kyDepartment::TYPE_* constants.
	 * @return kyDepartment
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Returns module the department is associated with - one of kyDepartment::MODULE_* constants.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Sets module the department will be associated with.
	 *
	 * @param string $module Module the department will be associated with - one of kyDepartment::MODULE_* constants.
	 * @return kyDepartment
	 */
	public function setModule($module) {
		$this->module = $module;
		return $this;
	}

	/**
	 * Returns display order of the department.
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Sets display order of the department.
	 *
	 * @param int $display_order A positive integer that the helpdesk will use to sort departments when displaying them (ascending).
	 * @return kyDepartment
	 */
	public function setDisplayOrder($display_order) {
		$this->display_order = $display_order;
		return $this;
	}

	/**
	 * Returns identifier of parent department for this department.
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getParentDepartmentId() {
		return $this->parent_department_id;
	}

	/**
	 * Sets the identifier of parent department for this department.
	 * Invalidates parent department cache.
	 *
	 * @param int $parent_department_id Identifier of department that will be the parent for this department.
	 * @return kyDepartment
	 */
	public function setParentDepartmentId($parent_department_id) {
		$this->parent_department_id = $parent_department_id;
		$this->parent_department = null;
		return $this;
	}

	/**
	 * Returns department object that is the parent for this department.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyDepartment
	 */
	public function getParentDepartment($reload = false) {
		if ($this->parent_department !== null && !$reload)
			return $this->parent_department;

		if ($this->parent_department_id === null || $this->parent_department_id <= 0)
			return null;

		$this->parent_department = kyDepartment::get($this->parent_department_id);
		return $this->parent_department;
	}

	/**
	 * Sets parent department for this department.
	 *
	 * @param kyDepartment $parent_department Department object that will be the parent for this department.
	 * @return kyDepartment
	 */
	public function setParentDepartment(kyDepartment $parent_department) {
		if ($parent_department === null) {
			$this->parent_department_id = $this->parent_department = null;
		} else {
			$this->parent_department_id = $parent_department->getId();
			$this->parent_department = $parent_department;
		}
		return $this;
	}

	/**
	 * Returns true to indicate that visibility of this department is restricted to particular user groups.
	 * Use getUserGroupIds to get their identifiers or getUserGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 * Sets wheter to restrict visibility of this department to particular user groups.
	 * Use setUserGroupIds to set these groups using identifiers or addUserGroup to set them using objects.
	 * Automatically clears user groups when set to false.
	 *
	 * @param bool $user_visibility_custom True to restrict visibility of this department to particular user groups. False otherwise.
	 * @return kyDepartment
	 */
	public function setUserVisibilityCustom($user_visibility_custom) {
		$this->user_visibility_custom = $user_visibility_custom;
		if ($this->user_visibility_custom === false) {
			$this->user_group_ids = array();
			$this->user_groups = null;
		}
		return $this;
	}

	/**
	 * Returns identifiers of user groups that can be assigned to this department.
	 *
	 * @return array
	 * @filterBy(UserGroupId)
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 * Sets user groups that can be assigned to this department using their identifiers.
	 *
	 * @param int[] $user_group_ids Identifiers of user groups that can be assigned to this department.
	 * @return kyDepartment
	 */
	public function setUserGroupIds($user_group_ids) {
		$this->user_group_ids = $user_group_ids;
		return $this;
	}

	/**
	 * Returns user groups that can be assigned to this department.
	 * Result is cached until the end of script.
	 *
	 * @return kyResultSet
	 */
	public function getUserGroups($reload = false) {
		$user_groups = array();
		foreach ($this->user_group_ids as $user_group_id) {
			if (!is_array($this->user_groups) || !array_key_exists($user_group_id, $this->user_groups) || $reload)
				$this->user_groups[$user_group_id] = kyUserGroup::get($user_group_id);
			$user_groups[] = $this->user_groups[$user_group_id];
		}
		return new kyResultSet($user_groups);
	}

	/**
	 * Add user group to the list of groups that can be assigned to this department.
	 * Automatically sets custom user visibility flag to True.
	 *
	 * @param kyUserGroup $user_group User group that can be assigned to this department.
	 * @param bool $clear Clear the list before adding.
	 * @return kyDepartment
	 */
	public function addUserGroup(kyUserGroup $user_group, $clear = false) {
		if ($clear) {
			$this->user_group = array();
			$this->user_group_ids = array();
		}

		//do nothing if it's already present
		if (in_array($user_group->getId(), $this->user_group_ids))
			return $this;

		$this->user_group_ids[] = $user_group->getId();
		$this->user_visibility_custom = true;

		return $this;
	}

	/**
	 * Creates new department.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $title Title of new department.
	 * @param string $type Type of new department - one of kyDepartment::TYPE_* constants.
	 * @param string $module Module of new department - one of kyDepartment::MODULE_* constants.
	 * @return kyDepartment
	 */
	static public function createNew($title, $type = self::TYPE_PUBLIC, $module = self::MODULE_TICKETS) {
		$new_department = new kyDepartment();
		$new_department->setTitle($title);
		$new_department->setType($type);
		$new_department->setModule($module);
		return $new_department;
	}

	/**
	 * Creates new subdepartment in this department. Module of new department will be the same as parent department's module.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $title Title of new department.
	 * @param string $type Type of new department - one of kyDepartment::TYPE_* constants.
	 * @return kyDepartment
	 */
	public function newSubdepartment($title, $type = self::TYPE_PUBLIC) {
		$new_department = kyDepartment::createNew($title, $type, $this->getModule());
		$new_department->setParentDepartment($this);
		return $new_department;
	}

	/**
	 * Creates new ticket in this department with creator user automatically created by server using provided name and e-mail.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $creator_full_name Creator full name.
	 * @param string $creator_email Creator e-mail.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return kyTicket
	 */
	public function newTicketAuto($creator_full_name, $creator_email, $contents, $subject) {
		return kyTicket::createNewAuto($this, $creator_full_name, $creator_email, $contents, $subject);
	}
}