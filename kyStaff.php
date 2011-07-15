<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Kayako Staff object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+Staff
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
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
	private $is_enabled = false;
	private $timezone = 'GMT';
	private $enable_dst = false;
	private $password = null;

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

		$data['staffgroupid'] = $this->staff_group_id;
		$data['firstname'] = $this->first_name;
		$data['lastname'] = $this->last_name;
		$data['username'] = $this->user_name;
		$data['email'] = $this->email;
		$data['designation'] = $this->designation;
		$data['greeting'] = $this->greeting;
		if (strlen($this->signature) > 0)
			$data['signature'] = $this->signature;
		$data['mobilenumber'] = $this->mobile_number;
		$data['isenabled'] = $this->is_enabled ? 0 : 1;
		$data['timezone'] = $this->timezone;
		$data['enabledst'] = $this->enable_dst ? 0 : 1;
		if (strlen($this->password) > 0)
			$this->password = $data['password'];

		return $data;
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 *
	 * @return int
	 */
	public function getStaffGroupId() {
		return $this->staff_group_id;
	}

	/**
	 *
	 * @param int $staff_group_id
	 * @return kyStaff
	 */
	public function setStaffGroupId($staff_group_id) {
		$this->staff_group_id = $staff_group_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyStaffGroup
	 */
	public function getStaffGroup() {
		if ($this->user_staff_id === null || $this->user_staff_id <= 0)
			return null;

		return kyStaffGroup::get($this->staff_group_id);
	}

	/**
	 *
	 * @param kyStaffGroup $staff_group
	 * @return kyStaff
	 */
	public function setStaffGroup($staff_group) {
		$this->staff_group_id = $staff_group->getId();
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getFirstName() {
		return $this->first_name;
	}

	/**
	 *
	 * @param string $first_name
	 * @return kyStaff
	 */
	public function setFirstName($first_name) {
		$this->first_name = $first_name;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastName() {
		return $this->last_name;
	}

	/**
	 *
	 * @param string $last_name
	 * @return kyStaff
	 */
	public function setLastName($last_name) {
		$this->last_name = $last_name;
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
	public function getUserName() {
		return $this->user_name;
	}

	/**
	 *
	 * @param string $user_name
	 * @return kyStaff
	 */
	public function setUserName($user_name) {
		$this->user_name = $user_name;
		return $this;
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
	 * @param string $email
	 * @return kyStaff
	 */
	public function setEmail($email) {
		$this->email = $email;
		return $this;
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
	 * @return kyStaff
	 */
	public function setDesignation($designation) {
		$this->designation = $designation;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getGreeting() {
		return $this->greeting;
	}

	/**
	 *
	 * @param string $greeting
	 * @return kyStaff
	 */
	public function setGreeting($greeting) {
		$this->greeting = $greeting;
		return $this;
	}

	/**
	 *
	 * @param string $signature
	 * @return kyStaff
	 */
	public function setSignature($signature) {
		$this->signature = $signature;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getMobileNumber() {
		return $this->mobile_number;
	}

	/**
	 *
	 * @param string $mobile_number
	 * @return kyStaff
	 */
	public function setMobileNumber($mobile_number) {
		$this->mobile_number = $mobile_number;
		return $this;
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
	 * @return kyStaff
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
	 * @return kyStaff
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
	 * @return kyStaff
	 */
	public function setEnableDST($enable_dst) {
		$this->enable_dst = $enable_dst;
		return $this;
	}

	/**
	 *
	 * @param string $password
	 * @return kyStaff
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	/**
	 * Creates new ticket with this Staff as the author.
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