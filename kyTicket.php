<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion). Compatible with version 4.01.240.
 * Compatible with Kayako version >= 4.01.240.
 *
 * Kayako Ticket object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+Ticket
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicket extends kyObjectBase {

	const FLAG_NONE = 0;
	const FLAG_PURPLE = 1;
	const FLAG_ORANGE = 2;
	const FLAG_GREEN = 3;
	const FLAG_YELLOW = 4;
	const FLAG_RED = 5;
	const FLAG_BLUE = 6;

	const CREATOR_AUTO = 0;
	const CREATOR_STAFF = 1;
	const CREATOR_USER = 2;
	const CREATOR_CLIENT = 2;

	const CREATION_MODE_SUPPORTCENTER = 1;
	const CREATION_MODE_STAFFCP = 2;
	const CREATION_MODE_EMAIL = 3;
	const CREATION_MODE_API = 4;
	const CREATION_MODE_SITEBADGE = 5;

	const CREATION_TYPE_DEFAULT = 1;
	const CREATION_TYPE_PHONE = 2;

	static protected $controller = '/Tickets/Ticket';
	static protected $object_xml_name = 'ticket';

	static private $default_status_id = null;
	static private $default_priority_id = null;
	static private $default_type_id = null;
	static private $auto_create_user = true;

	private $id = null;
	private $flag_type = null;
	private $display_id = null;
	private $department_id = null;
	private $status_id = null;
	private $priority_id = null;
	private $type_id = null;
	private $user_id = null;
	private $user_organization_name = null;
	private $user_organization_id = null;
	private $owner_staff_id = null;
	private $owner_staff_name = null;
	private $full_name = null;
	private $email = null;
	private $last_replier = null;
	private $subject = null;
	private $creation_time = null;
	private $last_activity = null;
	private $last_staff_reply = null;
	private $last_user_reply = null;
	private $sla_plan_id = null;
	private $next_reply_due = null;
	private $resolution_due = null;
	private $replies = null;
	private $ip_address = null;
	private $creator = null;
	private $creation_mode = null;
	private $creation_type = null;
	private $is_escalated = null;
	private $escalation_rule_id = null;
	private $tags = null;
	private $watchers = array();
	private $workflows = array();
	private $notes = null;
	private $time_tracks = null;
	private $posts = null;
	private $attachments = null;
	private $custom_fields_groups = null;

	private $contents = null;
	private $creator_id = null;

	/**
	 * Sets default status, priority and type for newly created tickets.
	 *
	 * @param int $status_id Default ticket status identifier.
	 * @param int $priority_id Default ticket priority identifier.
	 * @param int $type_id Default ticket type identifier.
	 * @param bool $auto_create_user True to automatically create user if none is provided as creator. False otherwise.
	 */
	static public function setDefaults($status_id, $priority_id, $type_id, $auto_create_user = true) {
		self::$default_status_id = $status_id;
		self::$default_priority_id = $priority_id;
		self::$default_type_id = $type_id;
		self::$auto_create_user = $auto_create_user;
	}

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->flag_type = intval($data['_attributes']['flagtype']);
		$this->display_id = $data['displayid'];
		$this->department_id = intval($data['departmentid']);
		$this->status_id = intval($data['statusid']);
		$this->priority_id = intval($data['priorityid']);
		$this->type_id = intval($data['typeid']);
		$this->user_id = intval($data['userid']);
		$this->user_organization_name = $data['userorganization'];
		$this->user_organization_id = intval($data['userorganizationid']);
		$this->owner_staff_id = intval($data['ownerstaffid']);
		if ($this->owner_staff_id === 0)
			$this->owner_staff_id = null;
		$this->owner_staff_name = $data['ownerstaffname'];
		$this->full_name = $data['fullname'];
		$this->email = $data['email'];
		$this->last_replier = $data['lastreplier'];
		$this->subject = $data['subject'];
		$this->creation_time = intval($data['creationtime']) > 0 ? date(self::$datetime_format, $data['creationtime']) : null;
		$this->last_activity = intval($data['lastactivity']) > 0 ? date(self::$datetime_format, $data['lastactivity']) : null;
		$this->last_staff_reply = intval($data['laststaffreply']) > 0 ? date(self::$datetime_format, $data['laststaffreply']) : null;
		$this->last_user_reply = intval($data['lastuserreply']) > 0 ? date(self::$datetime_format, $data['lastuserreply']) : null;
		$this->sla_plan_id = intval($data['slaplanid']);
		$this->next_reply_due = intval($data['nextreplydue']) > 0 ? date(self::$datetime_format, $data['nextreplydue']) : null;
		$this->resolution_due = intval($data['resolutiondue']) > 0 ? date(self::$datetime_format, $data['resolutiondue']) : null;
		$this->replies = intval($data['replies']);
		$this->ip_address = $data['ipaddress'];
		$this->creator = intval($data['creator']);
		$this->creation_mode = intval($data['creationmode']);
		$this->creation_type = intval($data['creationtype']);
		$this->is_escalated = intval($data['isescalated']) === 0 ? false : true;
		$this->escalation_rule_id = intval($data['escalationruleid']);
		$this->tags = $data['tags'];

		if (array_key_exists('watcher', $data)) {
			foreach ($data['watcher'] as $watcher) {
				$this->watchers[] = array('staff_id' => $watcher['_attributes']['staffid'], 'name' => $watcher['_attributes']['name']);
			}
		}

		if (array_key_exists('workflow', $data)) {
			foreach ($data['workflow'] as $workflow) {
				$this->workflows[] = array('id' => $workflow['_attributes']['id'], 'title' => $workflow['_attributes']['title']);
			}
		}

		/**
		 * Notes and time tracks.
		 */
		if (array_key_exists('note', $data)) {
			foreach ($data['note'] as $note_data) {
				/*
				 * Workaround for TimeTrack object - if "timeworked" key is present than it's a time track.
				 */
				if (array_key_exists('timeworked', $note_data['_attributes'])) {
					if ($this->time_tracks === null)
						$this->time_tracks = array();

					$this->time_tracks[] = new kyTicketTimeTrack($note_data);
				} else {
					if ($this->notes === null)
						$this->notes = array();

					$this->notes[] = new kyTicketNote($note_data);
				}
			}
		}

		/**
		 * Posts.
		 */
		if (array_key_exists('posts', $data)) {
			$this->posts = array();
			foreach ($data['posts'][0]['post'] as $post_data) {
				$this->posts[] = new kyTicketPost($post_data);
			}
		}
	}

	protected function buildData($method) {
		$data = array();

		$data['subject'] = $this->subject;
		$data['fullname'] = $this->full_name;
		$data['email'] = $this->email;
		$data['contents'] = $this->contents;
		$data['departmentid'] = $this->department_id;
		$data['ticketstatusid'] = $this->status_id;
		$data['ticketpriorityid'] = $this->priority_id;
		$data['tickettypeid'] = $this->type_id;
		switch ($this->creator) {
			case self::CREATOR_STAFF:
				$data['staffid']  = $this->creator_id;
				break;
			case self::CREATOR_USER:
				$data['userid']  = $this->creator_id;
				break;
			case self::CREATOR_AUTO:
				$data['autouserid'] = true;
				break;
		}

		if ($this->owner_staff_id === null)
			$data['ownerstaffid'] = 0;
		else
			$data['ownerstaffid'] = $this->owner_staff_id;
		$data['type'] = $this->creation_type;

		return $data;
	}

	/**
	 * Searches for tickets based on provided data. You must provide at least one department identifier.
	 *
	 * @param int[] $department_ids Non-empty list of department identifiers.
	 * @param int[] $ticket_status_ids List of ticket status identifiers.
	 * @param int[] $owner_staff_ids List of staff (ticket owners) identifiers.
	 * @param int[] $user_ids List of user (ticket creators) identifiers.
	 * @return kyTicket[]
	 */
	static public function getAll($department_ids, $ticket_status_ids = array(), $owner_staff_ids = array(), $user_ids = array()) {
		$search_parameters = array('ListAll');

		//department
		if (count($department_ids) === 0)
			throw new Exception('You must provide at least one department to search for tickets.');
		$search_parameters[] = implode(',', $department_ids);

		//ticket status
		if (count($ticket_status_ids) > 0)
			$search_parameters[] = implode(',', $ticket_status_ids);
		else
			$search_parameters[] = '-1';

		//owner staff
		if (count($owner_staff_ids) > 0)
			$search_parameters[] = implode(',', $owner_staff_ids);
		else
			$search_parameters[] = '-1';

		//user
		if (count($user_ids) > 0)
			$search_parameters[] = implode(',', $user_ids);
		else
			$search_parameters[] = '-1';

		return parent::getAll($search_parameters);
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Sets the creator of this ticket.
	 *
	 * @param int $type Creator type. One of self::CREATOR_* constants.
	 * @param string $full_name Full name of creator.
	 * @param string $email E-mail of creator.
	 * @param int $id Creator (user of staff) identifier. Not necessary for type CREATOR_AUTO.
	 * @return kyTicket
	 */
	public function setCreator($type, $full_name, $email, $id = null) {
		$this->creator = $type;
		$this->full_name = $full_name;
		$this->email = $email;
		$this->creator_id = $id;
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getFlagType() {
		return $this->flag_type;
	}

	/**
	 *
	 * @return int
	 */
	public function getDisplayId() {
		return $this->display_id;
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
	 * @param int $department_id
	 * @return kyTicket
	 */
	public function setDepartmentId($department_id) {
		$this->department_id = $department_id;
		return $this;
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
	 * @param kyDepartment $department
	 * @return kyTicket
	 */
	public function setDepartment($department) {
		$this->department_id = $department->getId();
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getStatusId() {
		return $this->status_id;
	}

	/**
	 *
	 * @param int $status_id
	 * @return kyTicket
	 */
	public function setStatusId($status_id) {
		$this->status_id = $status_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyTicketStatus
	 */
	public function getStatus() {
		if ($this->status_id === null || $this->status_id <= 0)
			return null;

		return kyTicketStatus::get($this->status_id);
	}

	/**
	 *
	 * @param kyTicketStatus $ticket_status
	 * @return kyTicket
	 */
	public function setStatus($ticket_status) {
		$this->status_id = $ticket_status->getId();
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getPriorityId() {
		return $this->priority_id;
	}

	/**
	 *
	 * @param int $priority_id
	 * @return kyTicket
	 */
	public function setPriorityId($priority_id) {
		$this->priority_id = $priority_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyTicketPriority
	 */
	public function getPriority() {
		if ($this->priority_id === null || $this->priority_id <= 0)
			return null;

		return kyTicketPriority::get($this->priority_id);
	}

	/**
	 *
	 * @param kyTicketPriority $ticket_priority
	 * @return kyTicket
	 */
	public function setPriority($ticket_priority) {
		$this->priority_id = $ticket_priority->getId();
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getTypeId() {
		return $this->type_id;
	}

	/**
	 *
	 * @param int $type_id
	 * @return kyTicket
	 */
	public function setTypeId($type_id) {
		$this->type_id = $type_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyTicketType
	 */
	public function getType() {
		if ($this->type_id === null || $this->type_id <= 0)
			return null;

		return kyTicketType::get($this->type_id);
	}

	/**
	 *
	 * @param kyTicketType $ticket_type
	 * @return kyTicket
	 */
	public function setType($ticket_type) {
		$this->type_id = $ticket_type->getId();
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyUser
	 */
	public function getUser() {
		if ($this->user_id === null || $this->user_id <= 0)
			return null;

		return kyUser::get($this->user_id);
	}

	/**
	 *
	 * @return string
	 */
	public function getUserOrganizationName() {
		return $this->user_organization_name;
	}

	/**
	 *
	 * @return int
	 */
	public function getUserOrganizationId() {
		return $this->user_organization_id;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyUserOrganization
	 */
	public function getUserOrganization() {
		if ($this->user_organization_id === null || $this->user_organization_id <= 0)
			return null;

		return kyUserOrganization::get($this->user_organization_id);
	}

	/**
	 *
	 * @return string
	 */
	public function getOwnerStaffName() {
		return $this->owner_staff_name;
	}

	/**
	 *
	 * @return int
	 */
	public function getOwnerStaffId() {
		return $this->owner_staff_id;
	}

	/**
	 *
	 * @param int $owner_staff_id
	 * @return kyTicket
	 */
	public function setOwnerStaffId($owner_staff_id) {
		$this->owner_staff_id = $owner_staff_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyStaff
	 */
	public function getOwnerStaff() {
		if ($this->owner_staff_id === null || $this->owner_staff_id <= 0)
			return null;

		return kyStaff::get($this->owner_staff_id);
	}

	/**
	 *
	 * @param kyStaff $owner_staff
	 * @return kyTicket
	 */
	public function setOwnerStaff($owner_staff) {
		$this->owner_staff_id = $owner_staff->getId();
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getFullName() {
		return $this->full_name;
	}

	/**
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastReplier() {
		return $this->last_replier;
	}

	/**
	 *
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 *
	 * @param string $subject
	 * @return kyTicket
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getCreationTime() {
		return $this->creation_time;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastActivity() {
		return $this->last_activity;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastStaffReply() {
		return $this->last_staff_reply;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastUserReply() {
		return $this->last_user_reply;
	}

	/**
	 *
	 * @return int
	 */
	public function getSLAPlanId() {
		return $this->sla_plan_id;
	}

	/**
	 *
	 * @return string
	 */
	public function getNextReplyDue() {
		return $this->next_reply_due;
	}

	/**
	 *
	 * @return string
	 */
	public function getResolutionDue() {
		return $this->resolution_due;
	}

	/**
	 *
	 * @return int
	 */
	public function getReplies() {
		return $this->replies;
	}

	/**
	 *
	 * @return string
	 */
	public function getIPAddress() {
		return $this->ip_address;
	}

	/**
	 *
	 * @return int
	 */
	public function getCreatorType() {
		return $this->creator;
	}

	/**
	 *
	 * @return int
	 */
	public function getCreationMode() {
		return $this->creation_mode;
	}

	/**
	 *
	 * @return int
	 */
	public function getCreationType() {
		return $this->creation_type;
	}

	/**
	 *
	 * @param int $creation_type
	 * @return kyTicket
	 */
	public function setCreationType($creation_type) {
		$this->creation_type = $creation_type;
		return $this;
	}

	/**
	 *
	 * @return bool
	 */
	public function getIsEscalated() {
		return $this->is_escalated;
	}

	/**
	 *
	 * @return int
	 */
	public function getEscalationRuleId() {
		return $this->escalation_rule_id;
	}

	/**
	 *
	 * @return string
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Returns tickets watchers.
	 * Format of returned array:
	 * array(
	 *   array(
	 *     'staff_id' => <staff identifier>,
	 *     'name' => '<staff full name>'
	 *   ),
	 *   ...
	 * )
	 *
	 * @return array
	 */
	public function getWatchers() {
		return $this->watchers;
	}

	/**
	 * Returns tickets workflows.
	 * Format of returned array:
	 * array(
	 *   array(
	 *     'id' => <workflow identifier>,
	 *     'title' => '<workflow title>'
	 *   ),
	 *   ...
	 * )
	 *
	 * @return array
	 */
	public function getWorkflows() {
		return $this->workflows;
	}

	/**
	 * Returns list of ticket notes. Result is cached.
	 *
	 * @param bool $force_refresh Use true to refresh notes data.
	 * @return kyTicketNote[]
	 */
	public function getNotes($force_refresh = false) {
		if ($this->notes === null || $force_refresh) {
			$this->notes = kyTicketNote::getAll($this->getId());
		}
		return $this->notes;
	}

	/**
	 * Returns list of ticket time tracks.
	 *
	 * @return kyTicketTimeTrack[]
	 */
	public function getTimeTracks() {
		return $this->time_tracks;
	}

	/**
	 * Returns list of ticket posts. Result is cached.
	 *
	 * @param bool $reload True to reload posts data from server.
	 * @return kyTicketPost[]
	 */
	public function getPosts($reload = false) {
		if ($this->posts === null || $reload) {
			$this->posts = kyTicketPost::getAll($this->getId());
		}
		return $this->posts;
	}

	/**
	 * Returns first post of ticket.
	 *
	 * @return kyTicketPost
	 */
	public function getFirstPost() {
		return reset($this->getPosts());
	}

	/**
	 * Returns list of attachments in all posts of this ticket. Result is cached.
	 *
	 * @param bool $force_refresh Use true to refresh attachments.
	 * @return kyTicketAttachment[]
	 */
	public function getAttachments($force_refresh = false) {
		if ($this->attachments === null || $force_refresh) {
			$this->attachments = kyTicketAttachment::getAll($this->id);
		}
		return $this->attachments;
	}

	/**
	 *
	 * @param string $contents
	 * @return kyTicket
	 */
	public function setContents($contents) {
		$this->contents = $contents;
		return $this;
	}

	/**
	 * Generic method for creating new ticket (without setting creator).
	 *
	 * @param kyDepartment $department Department where new ticket will be created.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return kyTicket
	 */
	static private function createNewGeneric($department, $contents, $subject) {
		$new_ticket = new kyTicket();
		$new_ticket->setStatusId(self::$default_status_id);
		$new_ticket->setPriorityId(self::$default_priority_id);
		$new_ticket->setTypeId(self::$default_type_id);
		$new_ticket->setDepartment($department);
		$new_ticket->setSubject($subject);
		$new_ticket->setContents($contents);
		return $new_ticket;
	}

	/**
	 * Creates new ticket with implicit user or staff as creator.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyDepartment $department Department where new ticket will be created.
	 * @param kyUser|kyStaff $creator Creator (User or Staff) of new ticket.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return kyTicket
	 */
	static public function createNew($department, $creator, $contents, $subject) {
		$new_ticket = self::createNewGeneric($department, $contents, $subject);
		if ($creator instanceOf kyUser) {
			$new_ticket->setCreator(self::CREATOR_USER, $creator->getFullName(), $creator->getEmail(), $creator->getId());
		} elseif ($creator instanceOf kyStaff) {
			$new_ticket->setCreator(self::CREATOR_STAFF, $creator->getFullName(), $creator->getEmail(), $creator->getId());
		}
		return $new_ticket;
	}

	/**
	 * Creates new ticket with creator user automatically created by server using provided name and e-mail.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyDepartment $department Department where new ticket will be created.
	 * @param string $creator_full_name Creator full name.
	 * @param string $creator_email Creator e-mail.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return kyTicket
	 */
	static public function createNewAuto($department, $creator_full_name, $creator_email, $contents, $subject) {
		$new_ticket = self::createNewGeneric($department, $contents, $subject);
		$new_ticket->setCreator(self::CREATOR_AUTO, $creator_full_name, $creator_email);
		return $new_ticket;
	}

	/**
	 * Creates new post in this ticket.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyUser|kyStaff $creator Creator (User or Staff) of new post.
	 * @param string $contents Contents of new post.
	 * @param string $subject Subject of new post (it's not displayed anywhere in Kayako so I don't see why it's required in API hence the default value).
	 * @return kyTicketPost
	 */
	public function newPost($creator, $contents, $subject = 'No subject') {
		return kyTicketPost::createNew($this, $creator, $contents, $subject);
	}
}