<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako Staff object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+Staff
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyStaff extends kyObjectBase {

	static protected $controller = '/Base/Staff';
	static protected $object_xml_name = 'staff';

	private $id = null;
	private $staff_group_id = null;
	private $first_name = null;
	private $last_name = null;
	private $full_name = null;
	private $user_name = null;
	private $email = null;
	private $designation = null;
	private $greeting = null;
	private $signature = null;
	private $mobile_number = null;
	private $is_enabled = true;
	private $timezone = 'GMT';
	private $enable_dst = false;
	private $password = null;

	private $staff_group = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->staff_group_id = intval($data['staffgroupid']);
		$this->first_name = $data['firstname'];
		$this->last_name = $data['lastname'];
		$this->full_name = $data['fullname'];
		$this->user_name = $data['username'];
		$this->email = $data['email'];
		$this->designation = $data['designation'];
		$this->greeting = $data['greeting'];
		$this->mobile_number = $data['mobilenumber'];
		$this->is_enabled = intval($data['isenabled']) === 0 ? false : true;
		$this->timezone = $data['timezone'];
		$this->enable_dst = intval($data['enabledst']) === 0 ? false : true;
	}

	protected function buildData($method) {
		$data = array();

		//TODO: check if required parameters are present

		$data['staffgroupid'] = $this->staff_group_id;
		$data['firstname'] = $this->first_name;
		$data['lastname'] = $this->last_name;
		$data['username'] = $this->user_name;
		$data['email'] = $this->email;
		$data['designation'] = $this->designation;
		$data['greeting'] = $this->greeting;
		if (strlen($this->signature) > 0)
			$data['staffsignature'] = $this->signature;
		$data['mobilenumber'] = $this->mobile_number;
		$data['isenabled'] = $this->is_enabled ? 1 : 0;
		$data['timezone'] = $this->timezone;
		$data['enabledst'] = $this->enable_dst ? 1 : 0;
		if (strlen($this->password) > 0)
			$data['password'] = $this->password;

		return $data;
	}

	public function toString() {
		return sprintf("%s (username: %s, email: %s)", $this->getFullName(), $this->getUserName(), $this->getEmail());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns staff group identifier of the staff user.
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getStaffGroupId() {
		return $this->staff_group_id;
	}

	/**
	 * Sets staff group identifier for the staff user.
	 * Invalidates staff group cache.
	 *
	 * @param int $staff_group_id Staff group identifier.
	 * @return kyStaff
	 */
	public function setStaffGroupId($staff_group_id) {
		$this->staff_group_id = $staff_group_id;
		$this->staff_group = null;
		return $this;
	}

	/**
	 * Returns staff group of the staff user.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyStaffGroup
	 */
	public function getStaffGroup($reload = false) {
		if ($this->staff_group !== null && !$reload)
			return $this->staff_group;

		if ($this->staff_group_id === null || $this->staff_group_id <= 0)
			return null;

		$this->staff_group = kyStaffGroup::get($this->staff_group_id);
		return $this->staff_group;
	}

	/**
	 * Sets staff group for the staff user.
	 *
	 * @param kyStaffGroup $staff_group Staff group object.
	 * @return kyStaff
	 */
	public function setStaffGroup(kyStaffGroup $staff_group) {
		$this->staff_group_id = $staff_group->getId();
		$this->staff_group = $staff_group;
		return $this;
	}

	/**
	 * Returns first name of the staff user.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getFirstName() {
		return $this->first_name;
	}

	/**
	 * Sets first name of the staff user.
	 *
	 * @param string $first_name First name of the staff user.
	 * @return kyStaff
	 */
	public function setFirstName($first_name) {
		$this->first_name = $first_name;
		return $this;
	}

	/**
	 * Returns last name of the staff user.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getLastName() {
		return $this->last_name;
	}

	/**
	 * Sets last name of the staff user.
	 *
	 * @param string $last_name Last name of the staff user.
	 * @return kyStaff
	 */
	public function setLastName($last_name) {
		$this->last_name = $last_name;
		return $this;
	}

	/**
	 * Returns full name of the staff user.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getFullName() {
		return $this->full_name;
	}

	/**
	 * Returns login username of the staff user.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getUserName() {
		return $this->user_name;
	}

	/**
	 * Sets login username of the staff user.
	 *
	 * @param string $user_name Login username of the staff user.
	 * @return kyStaff
	 */
	public function setUserName($user_name) {
		$this->user_name = $user_name;
		return $this;
	}

	/**
	 * Returns e-mail address of the staff user.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets e-mail address of the staff user.
	 *
	 * @param string $email E-mail address of the staff user.
	 * @return kyStaff
	 */
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}

	/**
	 * Returns designation of the staff user.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getDesignation() {
		return $this->designation;
	}

	/**
	 * Sets designation of the staff user.
	 *
	 * @param string $designation Designation of the staff user.
	 * @return kyStaff
	 */
	public function setDesignation($designation) {
		$this->designation = $designation;
		return $this;
	}

	/**
	 * Returns default greeting message when the staff user accepts a live chat request.
	 *
	 * @return string
	 * @filterBy()
	 */
	public function getGreeting() {
		return $this->greeting;
	}

	/**
	 * Sets default greeting message when the staff user accepts a live chat request.
	 *
	 * @param string $greeting Default greeting message when the staff user accepts a live chat request.
	 * @return kyStaff
	 */
	public function setGreeting($greeting) {
		$this->greeting = $greeting;
		return $this;
	}

	/**
	 * Returns signature that will be appended to each reply made by the staff user.
	 * The value is not available when the object was fetched from the server.
	 *
	 * @return string
	 */
	public function getSignature() {
		return $this->signature;
	}

	/**
	 * Sets signature that wil be appended to each reply made by the staff user.
	 *
	 * @param string $signature Signature that will be appended to each reply made by the staff user.
	 * @return kyStaff
	 */
	public function setSignature($signature) {
		$this->signature = $signature;
		return $this;
	}

	/**
	 * Returns mobile number of the staff user.
	 *
	 * @return string
	 * @filterBy()
	 */
	public function getMobileNumber() {
		return $this->mobile_number;
	}

	/**
	 * Sets mobile number of the staff user.
	 *
	 * @param string $mobile_number Mobile number of the staff user.
	 * @return kyStaff
	 */
	public function setMobileNumber($mobile_number) {
		$this->mobile_number = $mobile_number;
		return $this;
	}

	/**
	 * Returns whether the staff user is enabled.
	 *
	 * @return bool
	 * @filterBy()
	 * @orderBy()
	 */
	public function getIsEnabled() {
		return $this->is_enabled;
	}

	/**
	 * Sets whether the staff user is enabled.
	 * True is the default value when creating new staff user.
	 *
	 * @param bool $is_enabled True to enable the staff user. False to disable.
	 * @return kyStaff
	 */
	public function setIsEnabled($is_enabled) {
		$this->is_enabled = $is_enabled;
		return $this;
	}

	/**
	 * Returns timezone of the staff user.
	 *
	 * @return string
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 * Sets timezone of the staff user.
	 *
	 * @param string $timezone Timezone of the staff user.
	 * @return kyStaff
	 * @filterBy()
	 */
	public function setTimezone($timezone) {
		$this->timezone = $timezone;
		return $this;
	}

	/**
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getEnableDST() {
		return $this->enable_dst;
	}

	/**
	 * Sets whether Daylight Saving Time is enabled for the staff user.
	 * True is the default value when creating new staff user.
	 *
	 * @param bool $enable_dst True to enable Daylight Saving Time for the staff user. False to disable.
	 * @return kyStaff
	 */
	public function setEnableDST($enable_dst) {
		$this->enable_dst = $enable_dst;
		return $this;
	}

	/**
	 * Sets password for the staff user.
	 *
	 * @param string $password Password for the staff user.
	 * @return kyStaff
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	/**
	 * Creates new staff user.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $first_name First name of new staff user.
	 * @param string $last_name Last name of new staff user.
	 * @param string $user_name Login username of new staff user.
	 * @param string $email E-mail address of new staff user.
	 * @param kyStaffGroup $staff_group Staff group of new staff user.
	 * @param string $password Password for new staff user.
	 * @return kyStaff
	 */
	static public function createNew($first_name, $last_name, $user_name, $email, kyStaffGroup $staff_group, $password) {
		$new_staff = new kyStaff();
		$new_staff->setFirstName($first_name);
		$new_staff->setLastName($last_name);
		$new_staff->setUserName($user_name);
		$new_staff->setEmail($email);
		$new_staff->setStaffGroup($staff_group);
		$new_staff->setPassword($password);
		return $new_staff;
	}

	/**
	 * Creates new ticket with this staff user as the author.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyDepartment $department Department where the ticket will be created.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of the ticket.
	 * @return kyTicket
	 */
	public function newTicket(kyDepartment $department, $contents, $subject) {
		return kyTicket::createNew($department, $this, $contents, $subject);
	}
}