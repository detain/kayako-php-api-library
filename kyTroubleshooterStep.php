<?php
/**
 * Kayako TroubleshooterStep object.
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * known issues SWIFT-4136, SWIFT-4138
 * @link http://wiki.kayako.com/display/DEV/REST+-+TroubleshooterStep
 * @since Kayako version 4.64.1
 * @package Object\TroubleshooterStep
 *
 */
class kyTroubleshooterStep extends kyObjectBase {

	static protected $controller = '/Troubleshooter/Step';
	static protected $object_xml_name = 'troubleshooterstep';

	/**
	 * Troubleshooterstep status - Draft.
	 *
	 * @var int
	 */
	const STATUS_DRAFT = 1;

	/**
	 * Troubleshooterstep status - Published.
	 *
	 * @var int
	 */
	const STATUS_PUBLISHED = 2;

	/**
	 * Troubleshooterstep item identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Troubleshooterstep category id.
	 * @apiField
	 * @var int
	 */
	protected $category_id;

	/**
	 * Troubleshooterstep category.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $category;

	/**
	 * Creator (staff) identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $staff_id;

	/**
	 * Creator (staff).
	 * @var kyStaff
	 */
	private $staff;

	/**
	 * Editor (staff) identifier.
	 * @apiField required_update=true
	 * @var int
	 */
	protected $edited_staff_id;

	/**
	 * Editor (staff).
	 * @var kyStaff
	 */
	private $edited_staff;

	/**
	 * Troubleshooterstep subject.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $subject;

	/**
	 * Troubleshooterstep contents.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	/**
	 * Troubleshooterstep displayorder
	 * @apiField
	 * @var int
	 */
	protected $display_order;

	/**
	 * Troubleshooterstep allow comments.
	 * @apiField
	 * @var bool
	 */
	protected $allow_comments;

	/**
	 * Troubleshooterstep hasattachments.
	 * @apiField
	 * @var bool
	 */
	protected $has_attachments;

	/**
	 * Troubleshooterstep enable_ticket_redirection
	 * @apiField
	 * @var bool
	 */
	protected $enable_ticket_redirection;

	/**
	 * Troubleshooterstep redirect_departmentid.
	 * @apiField
	 * @var int
	 */
	protected $redirect_departmentid;

	/**
	 * Troubleshooterstep typeid.
	 * @apiField
	 * @var int
	 */
	protected $ticket_typeid;

	/**
	 * Troubleshooterstep priorityid.
	 * @apiField
	 * @var int
	 */
	protected $ticket_priorityid;

	/**
	 * Troubleshooterstep ticketsubject.
	 * @apiField
	 * @var string
	 */
	protected $ticket_subject;

	/**
	 * Troubleshooterstep status.
	 * @apiField
	 * @var int
	 */
	protected $status;

	/**
	 * Troubleshooterstep parentstepidlist.
	 * @apiField
	 * @var int[]
	 */
	protected $parent_stepids_list = array();

	/**
	 * Troubleshooterstep item identifier.
	 * @apiField
	 * @var int[]
	 */
	protected $child_stepids_list = array();

	protected function parseData($data) {
		$this->id = ky_assure_positive_int($data['id']);
		$this->category_id = ky_assure_positive_int($data['categoryid']);
		$this->staff_id = ky_assure_positive_int($data['staffid']);
		$this->subject = ky_assure_string($data['subject']);
		$this->display_order = ky_assure_int($data['displayorder']);
		$this->allow_comments = ky_assure_bool($data['allowcomments']);
		$this->has_attachments = ky_assure_bool($data['hasattachments']);
		$this->redirect_departmentid = ky_assure_positive_int($data['redirectdepartmentid']);
		$this->ticket_typeid = ky_assure_positive_int($data['tickettypeid']);
		$this->ticket_priorityid = ky_assure_int($data['priorityid']);
		$this->contents = ky_assure_string($data['contents']);

		$this->parent_stepids_list = array();
		$this->child_stepids_list = array();

		if (is_array($data['parentsteps'])) {
			if (is_string($data['parentsteps'][0]['id'])) {
				$this->parent_stepids_list[] = ky_assure_positive_int($data['parentsteps'][0]['id']);
			} else {
				foreach ($data['parentsteps'][0]['id'] as $stepid_list) {
					$this->parent_stepids_list[] = ky_assure_positive_int($stepid_list);
				}
			}
		}

		if (is_array($data['childsteps'])) {
			if (is_string($data['childsteps'][0]['id'])) {
				$this->child_stepids_list[] = ky_assure_positive_int($data['childsteps'][0]['id']);
			} else {
				foreach ($data['childsteps'][0]['id'] as $childsteps) {
					$this->child_stepids_list[] = ky_assure_positive_int($childsteps);
				}
			}
		}

	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$this->buildDataNumeric($data, 'categoryid', $this->category_id);
		$this->buildDataString($data, 'subject', $this->subject);
		$this->buildDataString($data, 'contents', $this->contents);

		if ($create) {
			$this->buildDataNumeric($data, 'staffid', $this->staff_id);
		} else {
			$this->buildDataNumeric($data, 'editedstaffid', $this->edited_staff_id);
		}

		$this->buildDataNumeric($data, 'displayorder', $this->display_order);
		$this->buildDataNumeric($data, 'stepstatus', $this->status);
		$this->buildDataBool($data, 'allowcomments', $this->allow_comments);
		$this->buildDataBool($data, 'enableticketredirection', $this->enable_ticket_redirection);

		if ($this->enable_ticket_redirection) {
			$this->buildDataNumeric($data, 'redirectdepartmentid', $this->redirect_departmentid);
			$this->buildDataNumeric($data, 'tickettypeid', $this->ticket_typeid);
			$this->buildDataNumeric($data, 'ticketpriorityid', $this->ticket_priorityid);
			$this->buildDataString($data, 'ticketsubject', $this->ticket_subject);
		}

		$this->buildDataList($data, 'parentstepidlist', $this->parent_stepids_list);

		return $data;
	}

	public function toString() {
		return sprintf("%s (Contents : %s)", $this->getSubject(), substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''));
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns subject of the Troubleshooterstep item.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets subject of the Troubleshooterstep item.
	 *
	 * @param string $subject Subject of the Troubleshooterstep item.
	 * @return kyTroubleshooterStep
	 */
	public function setSubject($subject) {
		$this->subject = ky_assure_string($subject);
		return $this;
	}

	/**
	 * Returns status of the Troubleshooterstep item.
	 *
	 * @see kyTroubleshooterStep::STATUS constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Returns whether this Troubleshooterstep has attachments.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getHasAttachments() {
		return $this->has_attachments;
	}

	/**
	 * Sets status of the Troubleshooterstep item.
	 *
	 * @see kyTroubleshooterStep::STATUS constants.
	 *
	 * @param int $status Status of the Troubleshooterstep item.
	 * @return kyTroubleshooterStep
	 */
	public function setStatus($status) {
		$this->status = ky_assure_constant($status, $this, 'STATUS');
		return $this;
	}

	/**
	 * Returns contents of the Troubleshooterstep item.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of the Troubleshooterstep item. Can contain HTML tags.
	 *
	 * @param string $contents Contents of the Troubleshooterstep item.
	 * @return kyTroubleshooterStep
	 */
	public function setContents($contents) {
		$this->contents = ky_assure_string($contents);
		return $this;
	}

	/**
	 * Returns display order of this Troubleshooterstep item.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Sets the displayorder of this Troubleshooterstep item.
	 * @param $displayorder
	 *
	 * @return $this
	 */
	public function setDisplayOrder($displayorder) {
		$this->display_order = ky_assure_int($displayorder);
		return $this;
	}

	/**
	 * Returns whether clients are permitted to comment on this Troubleshooterstep item.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getAllowComments() {
		return $this->allow_comments;
	}

	/**
	 * Sets whether clients are permitted to comment on this Troubleshooterstep item.
	 *
	 * @param bool $allow_comments True to allow clients to comment on this Troubleshooterstep item.
	 * @return kyTroubleshooterStep
	 */
	public function setAllowComments($allow_comments) {
		$this->allow_comments = ky_assure_bool($allow_comments);
		return $this;
	}

	/**
	 * Returns type of this Troubleshooterstep item.
	 *
	 * @see kyTicketType::TYPE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->ticket_typeid;
	}

	/**
	 * sets the type of this Troubleshooterstep item.
	 * @param $type
	 *
	 * @return $this
	 */
	public function setType($type) {
		$this->ticket_typeid = ky_assure_int($type);
		return $this;
	}

	/**
	 * Returns priority of this Troubleshooterstep item.
	 *
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getPriority() {
		return $this->ticket_priorityid;
	}

	/**
	 * Sets the priority of this Troubleshooterstep item.
	 * @param $priority
	 *
	 * @return $this
	 */
	public function setPriority($priority) {
		$this->ticket_priorityid = ky_assure_int($priority);
		return $this;
	}

	/**
	 * Returns ticketsubject of this Troubleshooterstep item.
	 *
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTicketSubject() {
		return $this->ticket_subject;
	}

	/**
	 * Sets ticketsubject of this Troubleshooterstep item.
	 *
	 * @param string $ticketsubject subject of the ticket.
	 * @return kyTroubleshooterStep
	 */
	public function setTicketSubject($ticketsubject) {
		$this->ticket_subject = ky_assure_string($ticketsubject);
		return $this;
	}

	/**
	 * Returns the staff user, the creator of this Troubleshooterstep item.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyStaff
	 */
	public function getStaff($reload = false) {
		if ($this->staff !== null && !$reload)
			return $this->staff;

		if ($this->staff_id === null)
			return null;

		$this->staff = kyStaff::get($this->staff_id);
		return $this->staff;
	}

	/**
	 * Sets staff user, the creator of this Troubleshooterstep item.
	 *
	 * @param kyStaff $staff Staff user.
	 * @return kyTroubleshooterStep
	 */
	public function setStaff($staff) {
		$this->staff = ky_assure_object($staff, 'kyStaff');
		$this->staff_id = $this->staff !== null ? $this->staff->getId() : null;
		return $this;
	}

	/**
	 * Sets the category of the Troubleshooterstep
	 *
	 * @param kyTroubleshootercategory $categoryid category.
	 * @return kyTroubleshootercategory
	 */
	public function setCategory($categoryid) {
		$this->category = ky_assure_object($categoryid, 'kyTroubleshooterCategory');
		$this->category_id = $this->category !== null ? $this->category->getId() : null;
		return $this;
	}

	/**
	 * Sets the parameter enableTicketRedirection of this Troubleshooterstep item.
	 *
	 * @param bool $enab_ticket_redirect True to allow ticket creation.
	 * @return kyTroubleshooterStep
	 */
	public function setEnableTicketRedirection($enab_ticket_redirect) {
		$this->enable_ticket_redirection = ky_assure_bool($enab_ticket_redirect);
		return $this;
	}

	/**
	 * Returns TicketRedirectDepartmentId of this Troubleshooterstep type.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTicketRedirectDepartmentId() {
		return $this->ticket_subject;
	}

	/**
	 * Sets the parameter TicketRedirectDepartmentId of this Troubleshooterstep item.
	 *
	 * @param int $ticket_redirect_deptid departmentid of this Troubleshooterstep item.
	 * @return kyTroubleshooterStep
	 */
	public function setTicketRedirectDepartmentId($ticket_redirect_deptid) {
		$this->redirect_departmentid = ky_assure_string($ticket_redirect_deptid);
		return $this;
	}

	/**
	 * Returns parent stepid list which are linked to this troubleshooterstep item.
	 *
	 * @return array
	 * @filterBy name=StaffGroupId
	 */
	public function getParentstepIds() {
		return $this->parent_stepids_list;
	}

	/**
	 * Sets parentstepidlist (using their identifiers) that this troubleshooterstep item will be visible to.
	 *
	 * @param int[] $parent_stepids Identifiers of parent_stepids that this troubleshooterstep item will be visible to.
	 * @return kyTroubleshooterStep
	 */
	public function setParentstepIds($parent_stepids) {
		//normalization to array
		if (!is_array($parent_stepids)) {
			if (is_numeric($parent_stepids)) {
				$parent_stepids = array($parent_stepids);
			} else {
				$parent_stepids = array();
			}
		}

		//normalization to positive integer values
		$this->parent_stepids_list = array();
		foreach ($parent_stepids as $parent_step_id) {
			$parent_step_id = ky_assure_positive_int($parent_step_id);
			if ($parent_step_id === null)
				continue;

			$this->parent_stepids_list[] = $parent_step_id;
		}

		return $this;
	}

	/**
	 * Sets identifier of staff user, the editor of this troubleshooterstep item update.
	 *
	 * @param int $staff_id Staff user identifier.
	 * @return kyTroubleshooterStep
	 */
	public function setEditedStaffId($staff_id) {
		$this->edited_staff_id = ky_assure_positive_int($staff_id);
		$this->edited_staff = null;
		return $this;
	}

	/**
	 * Sets staff user, the editor of this troubleshooterstep item update.
	 *
	 * @param kyStaff $staff Staff user.
	 * @return kyTroubleshooterStep
	 */
	public function setEditedStaff($staff) {
		$this->edited_staff = ky_assure_object($staff, 'kyStaff');
		$this->edited_staff_id = $this->edited_staff !== null ? $this->edited_staff->getId() : null;
		return $this;
	}

	/**
	 * Creates a troubleshooterstep item.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $category Category of troubleshooterstep item.
	 * @param string $subject Subject of troubleshooterstep item.
	 * @param string $contents Contents of troubleshooterstep item.
	 * @param kyStaff $staff Author (staff) of troubleshooterstep item.
	 *
	 *@return kyTroubleshooterStep
	 */
	static public function createNew($category, $subject, $contents, kyStaff $staff) {
		$new_troubleshooterstep_item = new kyTroubleshooterStep();
		$new_troubleshooterstep_item->setCategory($category);
		$new_troubleshooterstep_item->setSubject($subject);
		$new_troubleshooterstep_item->setContents($contents);
		$new_troubleshooterstep_item->setStaff($staff);
		return $new_troubleshooterstep_item;
	}

}