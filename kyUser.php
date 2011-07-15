<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Kayako User object.
 *
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
 */
class kyUser extends kyObjectBase {

	const ROLE_USER = 'user';
	const ROLE_MANAGER = 'manager';

	static protected $controller = '/Base/User';
	static protected $object_xml_name = 'user';

	private $id = null;
	private $user_group_id = null;
	private $user_role = self::ROLE_USER;
	private $user_organization_id = null;
	private $user_expiry = null;
	private $full_name = null;
	private $email = array();
	private $designation = null;
	private $phone = null;
	private $dateline = null;
	private $last_visit = null;
	private $is_enabled = false;
	private $timezone = 'GMT';
	private $enable_dst = false;
	private $sla_plan_id = null;
	private $sla_plan_expiry = null;
	private $password = null;
	private $send_welcome_email = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->user_group_id = intval($data['usergroupid']);
		$this->user_role = $data['userrole'];
		$this->user_organization_id = intval($data['userorganizationid']);
		$this->user_expiry = intval($data['userexpiry']) > 0 ? date(self::$datetime_format, $data['userexpiry']) : null;
		$this->full_name = $data['fullname'];
		$this->email = $data['email'];
		if (is_string($this->email))
			$this->email = array($this->email);
		$this->designation = $data['designation'];
		$this->phone = $data['phone'];
		$this->dateline = date(self::$datetime_format, $data['dateline']);
		$this->last_visit = intval($data['lastvisit']) > 0 ? date(self::$datetime_format, $data['lastvisit']) : null;
		$this->is_enabled = intval($data['isenabled']) === 0 ? false : true;
		$this->timezone = $data['timezone'];
		$this->enable_dst = intval($data['enabledst']) === 0 ? false : true;
		$this->sla_plan_id = intval($data['slaplanid']);
		$this->sla_plan_expiry = intval($data['slaplanexpiry']) > 0 ? date(self::$datetime_format, $data['slaplanexpiry']) : null;
	}

	protected function buildData($method) {
		$data = array();

		$data['usergroupid'] = $this->user_group_id;
		$data['userrole'] = $this->user_role;
		$data['userorganizationid'] = $this->user_organization_id;
		$data['userexpiry'] = 0;
		if ($this->user_expiry !== null)
			$data['userexpiry'] = strtotime($this->user_expiry);
		$data['fullname'] = $this->full_name;
		$data['email'] = $this->email;
		$data['designation'] = $this->designation;
		$data['phone'] = $this->phone;
		$data['isenabled'] = $this->is_enabled ? 1 : 0;

		$data['timezone'] = $this->timezone;
		$data['enabledst'] = $this->enable_dst ? 1 : 0;
		$data['slaplanid'] = $this->sla_plan_id;
		$data['slaplanexpiry'] = 0;
		if ($this->sla_plan_expiry !== null)
			$this->sla_plan_expiry = strtotime($data['slaplanexpiry']);

		if (strlen($this->password) > 0)
			$data['password'] = $this->password;
		if ($this->send_welcome_email !== null)
			$data['sendwelcomeemail'] = $this->send_welcome_email ? 1 : 0;

		return $data;
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 *
	 * @return int
	 */
	public function getUserGroupId() {
		return $this->user_group_id;
	}

	/**
	 *
	 * @param int $user_group_id
	 * @return kyUser
	 */
	public function setUserGroupId($user_group_id) {
		$this->user_group_id = $user_group_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyUserGroup
	 */
	public function getUserGroup() {
		if ($this->user_group_id === null || $this->user_group_id <= 0)
			return null;

		return kyUserGroup::get($this->user_group_id);
	}

	/**
	 *
	 * @param UserGroup $user_group
	 * @return kyUser
	 */
	public function setUserGroup($user_group) {
		$this->user_group_id = $user_group->getId();
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getUserRole() {
		return $this->user_role;
	}

	/**
	 *
	 * @param string $user_role User role. One of self::ROLE_* constants.
	 * @return kyUser
	 */
	public function setUserRole($user_role) {
		$this->user_role = $user_role;
		return $this;
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
	 * @param int $user_organization_id
	 * @return kyUser
	 */
	public function setUserOrganizationId($user_organization_id) {
		$this->user_organization_id = $user_organization_id;
		return $this;
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
	 * @param UserOrganization $user_organization
	 * @return kyUser
	 */
	public function setUserOrganization($user_organization) {
		$this->user_organization_id = $user_organization->getId();
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getUserExpiry() {
		return $this->user_expiry;
	}

	/**
	 *
	 * @param string $user_expiry
	 * @return kyUser
	 */
	public function setUserExpiry($user_expiry) {
		$this->user_expiry = $user_expiry;
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
	 * @param string $full_name
	 * @return kyUser
	 */
	public function setFullName($full_name) {
		$this->full_name = $full_name;
		return $this;
	}

	/**
	 * Returns first email from the list of user emails.
	 *
	 * @return string
	 */
	public function getEmail() {
		return reset($this->email);
	}

	/**
	 * Returns list of user emails.
	 *
	 * @return string
	 */
	public function getEmails() {
		return $this->email;
	}

	/**
	 * Adds an email.
	 *
	 * @param string $email E-mail address.
	 * @param bool $clear Clear the list before adding.
	 */
	public function addEmail($email, $clear = false) {
		if ($clear)
			$this->email = array();
		$this->email[] = $email;
	}

	/**
	 *
	 * @return string
	 */
	public function getDesignation() {
		return $this->designation;
	}

	/**
	 *
	 * @param string $designation
	 * @return kyUser
	 */
	public function setDesignation($designation) {
		$this->designation = $designation;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 *
	 * @param string $phone
	 * @return kyUser
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getDateline() {
		return $this->dateline;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastVisit() {
		return $this->last_visit;
	}

	/**
	 *
	 * @return bool
	 */
	public function getIsEnabled() {
		return $this->is_enabled;
	}

	/**
	 *
	 * @param bool $is_enabled
	 * @return kyUser
	 */
	public function setIsEnabled($is_enabled) {
		$this->is_enabled = $is_enabled;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 *
	 * @param string $timezone
	 * @return kyUser
	 */
	public function setTimezone($timezone) {
		$this->timezone = $timezone;
		return $this;
	}

	/**
	 *
	 * @return bool
	 */
	public function getEnableDST() {
		return $this->enable_dst;
	}

	/**
	 *
	 * @param bool $enable_dst
	 * @return kyUser
	 */
	public function setEnableDST($enable_dst) {
		$this->enable_dst = $enable_dst;
		return $this;
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
	 * @param int $sla_plan_id
	 * @return kyUser
	 */
	public function setSLAPlanId($sla_plan_id) {
		$this->sla_plan_id = $sla_plan_id;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getSLAPlanExpiry() {
		return $this->sla_plan_expiry;
	}

	/**
	 *
	 * @param string $sla_plan_expiry
	 * @return kyUser
	 */
	public function setSLAPlanExpiry($sla_plan_expiry) {
		$this->sla_plan_expiry = $sla_plan_expiry;
		return $this;
	}

	/**
	 *
	 * @param string $password
	 * @return kyUser
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	/**
	 *
	 * @param bool $send_welcome_email
	 * @return kyUser
	 */
	public function setSendWelcomeEmail($send_welcome_email) {
		$this->send_welcome_email = $send_welcome_email;
		return $this;
	}

	/**
	 * Creates new ticket with this User as the author.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyDepartment $department Department where the ticket will be created.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of the ticket.
	 * @return kyTicket
	 */
	public function newTicket($department, $contents, $subject) {
		return kyTicket::createNew($department, $this, $contents, $subject);
	}
}