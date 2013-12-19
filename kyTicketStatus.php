<?php
/**
 * Kayako TicketStatus object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketStatus
 * @since Kayako version 4.01.240
 * @package Object\Ticket
 */
class kyTicketStatus extends kyObjectBase {

	const TYPE_PUBLIC = 'public';
	const TYPE_PRIVATE = 'private';

	static protected $controller = '/Tickets/TicketStatus';
	static protected $object_xml_name = 'ticketstatus';
	protected $read_only = true;

	/**
	 * Ticket status identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Ticket status title.
	 * @apiField
	 * @var string
	 */
	protected $title;

	/**
	 * Ticket status display order.
	 * @apiField
	 * @var int
	 */
	protected $display_order;

	/**
	 * Linked department identifier.
	 *
	 * If a ticket status is linked to a department, it will be visible only under the linked department.
	 *
	 * @apiField
	 * @var int
	 */
	protected $department_id;

	/**
	 * Path to icon displayed in GUI for this ticket status.
	 * @apiField
	 * @var string
	 */
	protected $display_icon;

	/**
	 * Type of ticket status.
	 *
	 * @see kyTicketStatus::TYPE constants.
	 *
	 * @apiField
	 * @var string
	 */
	protected $type;

	/**
	 * If tickets with this status are marked as resolved/closed.
	 * @apiField
	 * @var bool
	 */
	protected $mark_as_resolved;

	/**
	 * If ticket count for this status is displayed in the filter ticket tree.
	 * @apiField
	 * @var bool
	 */
	protected $display_count;

	/**
	 * Font color associated with this ticket status in GUI.
	 * @apiField
	 * @var string
	 */
	protected $status_color;

	/**
	 * Background color associated with this ticket status in GUI.
	 *
	 * This color is used for the "General Tab Bar" in Kayako GUI when viewing the ticket.
	 *
	 * @apiField
	 * @var string
	 */
	protected $status_bg_color;

	/**
	 * If enabled, Kayako will automatically clear the due time for a ticket when the ticket status changes to this status.
	 * @apiField
	 * @var bool
	 */
	protected $reset_due_time;

	/**
	 * If enabled, whenever a ticket is changed to this ticket status a survey email will be dispatched to the user asking for rating and comments.
	 * @apiField
	 * @var bool
	 */
	protected $trigger_survey;

	/**
	 * If enabled, a ticket status can be changed to this status only by the selected staff teams.
	 *
	 * @see kyTicketStatus::$staff_group_ids
	 *
	 * @apiField
	 * @var bool
	 */
	protected $staff_visibility_custom;

	/**
	 * Identifiers of staff groups which can change ticket status to this status.
	 * @apiField name=staffgroupid
	 * @var int[]
	 */
	protected $staff_group_ids = array();

	/**
	 * Linked department.
	 * @var kyDepartment
	 */
	private $department = null;

	/**
	 * Staff groups which can change ticket status to this status.
	 * @var kyStaffGroup[]
	 */
	private $staff_groups = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->display_order = intval($data['displayorder']);
		$this->department_id = ky_assure_positive_int($data['departmentid']);
		$this->display_icon = $data['displayicon'];
		$this->type = $data['type'];
		$this->mark_as_resolved = ky_assure_bool($data['markasresolved']);
		$this->display_count = ky_assure_bool($data['displaycount']);
		$this->status_color = $data['statuscolor'];
		$this->status_bg_color = $data['statusbgcolor'];
		$this->reset_due_time = ky_assure_bool($data['resetduetime']);
		$this->trigger_survey = ky_assure_bool($data['triggersurvey']);

		$this->staff_visibility_custom = ky_assure_bool($data['staffvisibilitycustom']);
		if ($this->staff_visibility_custom && is_array($data['staffgroupid'])) {
			foreach ($data['staffgroupid'] as $staff_group_id) {
				$this->staff_group_ids[] = intval($staff_group_id);
			}
		}
	}

	public function toString() {
		return sprintf("%s (type: %s)", $this->getTitle(), $this->getType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns ticket status title.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns ticket status display order.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Returns linked department identifier.
	 *
	 * If a ticket status is linked to a department, it will be visible only under the linked department.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getDepartmentId() {
		return $this->department_id;
	}

	/**
	 * Returns linked department.
	 *
	 * If a ticket status is linked to a department, it will be visible only under the linked department.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyDepartment
	 */
	public function getDepartment($reload = false) {
		if ($this->department !== null && !$reload)
			return $this->department;

		if ($this->department_id === null || $this->department_id <= 0)
			return null;

		$this->department = kyDepartment::get($this->department_id);
		return $this->department;
	}

	/**
	 * Returns path to icon displayed in GUI for this ticket status.
	 * @return string
	 */
	public function getDisplayIcon() {
		return $this->display_icon;
	}

	/**
	 * Returns type of this ticket status.
	 *
	 * @see kyTicketStatus::TYPE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns whether tickets with this status are marked as resolved/closed.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getMarkAsResolved() {
		return $this->mark_as_resolved;
	}

	/**
	 * Returns whether ticket count for this status is displayed in the filter ticket tree.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayCount() {
		return $this->display_count;
	}

	/**
	 * Returns font color associated with this ticket status in GUI.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getStatusColor() {
		return $this->status_color;
	}

	/**
	 * Returns background color associated with this ticket status in GUI.
	 *
	 * This color is used for the "General Tab Bar" in Kayako GUI when viewing the ticket.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getStatusBackgroundColor() {
		return $this->status_bg_color;
	}

	/**
	 * Returns whether Kayako will automatically clear the due time for a ticket when the ticket status changes to this status.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getResetDueTime() {
		return $this->reset_due_time;
	}

	/**
	 * If whether whenever a ticket is changed to this ticket status a survey email will be dispatched to the user asking for rating and comments.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getTriggerSurvey() {
		return $this->trigger_survey;
	}

	/**
	 * Returns whether a ticket status can be changed to this status only by the selected staff teams.
	 * @return bool
	 * @filterBy
	 */
	public function getStaffVisibilityCustom() {
		return $this->staff_visibility_custom;
	}

	/**
	 * Returns list of identifiers of staff groups which can change ticket status to this status.
	 *
	 * @return int[]
	 * @filterBy name=StaffGroupId
	 * @orderBy name=StaffGroupId
	 */
	public function getStaffGroupIds() {
		return $this->staff_group_ids;
	}

	/**
	 * Returns list of staff groups which can change ticket status to this status.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyResultSet
	 */
	public function getStaffGroups($reload = false) {
		foreach ($this->staff_group_ids as $staff_group_id) {
			if (!is_array($this->staff_groups) || !array_key_exists($staff_group_id, $this->staff_groups) || $reload) {
				$this->staff_groups[$staff_group_id] = kyStaffGroup::get($staff_group_id);
			}
		}
		return new kyResultSet(array_values($this->staff_groups));
	}

	/**
	 * Returns whether this ticket status can be set by specified staff group.
	 *
	 * @param kyStaffGroup|int $staff_group Staff group or its identifier.
	 * @return bool
	 * @filterBy
	 */
	public function isVisibleToStaffGroup($staff_group) {
		if ($this->staff_visibility_custom === false)
			return true;

		if ($staff_group instanceof kyStaffGroup) {
			$staff_group_id = $staff_group->getId();
		} else {
			$staff_group_id = intval($staff_group);
		}

		return in_array($staff_group_id, $this->staff_group_ids);
	}

	/**
	 * Returns whether this ticket status is visible under specified department.
	 *
	 * @param kyDepartment|int $department Department or its identifier.
	 * @return bool
	 * @filterBy
	 */
	public function isAvailableInDepartment($department) {
		if ($this->department_id == null)
			return true;

		if ($department instanceof kyDepartment) {
			$department_id = $department->getId();
		} else {
			$department_id = intval($department);
		}

		return $this->department_id === $department_id;
	}
}