<?php
/**
 * Kayako User object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+User
 * @since Kayako version 4.01.204
 * @package Object\User
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyUser extends kyObjectBase {

	const ROLE_USER = 'user';
	const ROLE_MANAGER = 'manager';

	const SALUTATION_MR = 'Mr.';
	const SALUTATION_MISS = 'Ms.';
	const SALUTATION_MRS = 'Mrs.';
	const SALUTATION_DR = 'Dr.';

	static protected $controller = '/Base/User';
	static protected $object_xml_name = 'user';

	/**
	 * User identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * User group identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $user_group_id;

	/**
	 * User role.
	 *
	 * @see kyUser::ROLE constants.
	 *
	 * @apiField
	 * @var string
	 */
	protected $user_role = self::ROLE_USER;

	/**
	 * User organization identifier.
	 * @apiField
	 * @var int
	 */
	protected $user_organization_id;

	/**
	 * User salutation.
	 *
	 * @see kyUser::SALUTATION constants.
	 *
	 * @apiField
	 * @var string
	 */
	protected $salutation;

	/**
	 * Timestamp of when the user will expire.
	 * @apiField
	 * @var int
	 */
	protected $user_expiry = 0;

	/**
	 * Full name of the user.
	 * @apiField required=true
	 * @var string
	 */
	protected $full_name;

	/**
	 * List of user's e-mail addresses.
	 * @apiField required_create=true
	 * @var string[]
	 */
	protected $email = array();

	/**
	 * User designation.
	 * @apiField
	 * @var string
	 */
	protected $designation;

	/**
	 * User phone number.
	 * @apiField
	 * @var string
	 */
	protected $phone;

	/**
	 * Timestamp of when the user was created.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	/**
	 * Timestamp of when the user last visited support center.
	 * @apiField
	 * @var int
	 */
	protected $last_visit;

	/**
	 * Whether this user is enabled.
	 * @apiField
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * Timezone of the user.
	 * @apiField
	 * @var string
	 */
	protected $timezone;

	/**
	 * If Daylight Saving Time is enabled for the user.
	 * @apiField
	 * @var bool
	 */
	protected $enable_dst = false;

	/**
	 * Identifier of Service Level Agreement plan associated to this user.
	 * @apiField
	 * @var int
	 */
	protected $sla_plan_id;

	/**
	 * Timestamp of when the Service Level Agreement plan associated to this user will expire.
	 * @apiField
	 * @var int
	 */
	protected $sla_plan_expiry;

	/**
	 * Whether welcome e-mail should be send upon creation of the user.
	 * @apiField
	 * @var bool
	 */
	protected $send_welcome_email = true;

	/**
	 * User password.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $password;

	/**
	 * Group of the user.
	 * @var kyUserGroup
	 */
	private $user_group = null;

	/**
	 * Organization of the user.
	 * @var kyUserOrganization
	 */
	private $user_organization = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->user_group_id = ky_assure_positive_int($data['usergroupid']);
		$this->user_role = $data['userrole'];
		$this->user_organization_id = ky_assure_positive_int($data['userorganizationid']);
		$this->salutation = $data['salutation'];
		$this->user_expiry = ky_assure_positive_int($data['userexpiry']);
		$this->full_name = $data['fullname'];
		$this->email = $data['email'];
		if (is_string($this->email))
			$this->email = array($this->email);
		$this->designation = $data['designation'];
		$this->phone = $data['phone'];
		$this->dateline = ky_assure_positive_int($data['dateline']);
		$this->last_visit = ky_assure_positive_int($data['lastvisit']);
		$this->is_enabled = ky_assure_bool($data['isenabled']);
		$this->timezone = $data['timezone'];
		$this->enable_dst = ky_assure_bool($data['enabledst']);
		$this->sla_plan_id = ky_assure_positive_int($data['slaplanid']);
		$this->sla_plan_expiry = ky_assure_positive_int($data['slaplanexpiry']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['usergroupid'] = $this->user_group_id;
		$data['userrole'] = $this->user_role;
		$data['userorganizationid'] = $this->user_organization_id;
		$data['salutation'] = $this->salutation;
		$data['userexpiry'] = $this->user_expiry !== null ? $this->user_expiry : 0;
		$data['fullname'] = $this->full_name;
		$data['email'] = $this->email;
		$data['designation'] = $this->designation;
		$data['phone'] = $this->phone;
		$data['isenabled'] = $this->is_enabled ? 1 : 0;
		$data['timezone'] = $this->timezone;
		$data['enabledst'] = $this->enable_dst ? 1 : 0;
		$data['slaplanid'] = $this->sla_plan_id;
		$data['slaplanexpiry'] = $this->sla_plan_expiry !== null ? $this->sla_plan_expiry : 0;

		if ($create) {
			$data['password'] = $this->password;

			if ($this->send_welcome_email !== null) {
				$data['sendwelcomeemail'] = $this->send_welcome_email ? 1 : 0;
			}
		}

		return $data;
	}

	/**
	 * Fetches all users.
	 * Optionaly you can get segment of user objects by defining starting user identifier and maximum items count.
	 *
	 * @param int $starting_user_id Optional starting user identifier.
	 * @param int $max_items Optional maximum items count. Defaults to 1000 when starting user is defined.
	 * @return kyResultSet
	 */
	static public function getAll($starting_user_id = null, $max_items = null) {
		$search_parameters = array('Filter');
		if (is_numeric($starting_user_id) && $starting_user_id > 0) {
			if (!is_numeric($max_items) || $max_items <= 0) {
				$max_items = 1000;
			}
			$search_parameters[] = $starting_user_id;
			$search_parameters[] = $max_items;
		}

		return parent::getAll($search_parameters);
	}

	/**
	 * Searches for users.
	 *
	 * Performs server-side search of users. The search query is run against
	 * email, full name, phone, organization name and user group.
	 *
	 * @link http://wiki.kayako.com/display/DEV/REST+-+UserSearch
	 * @since Kayako version 4.40.1148
	 *
	 * @param string $query The search query.
	 * @return kyResultSet
	 */
	static public function search($query) {
		$data = array();
		$data['query'] = $query;

		$result = self::getRESTClient()->post('/Base/UserSearch', array(), $data);

		$objects = array();
		if (array_key_exists(static::$object_xml_name, $result)) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new static($object_data);
			}
		}
		return new kyResultSet($objects);
	}

	public function toString() {
		return sprintf("%s (email: %s)", $this->getFullName(), $this->getEmail());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns user group identifier of the user.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserGroupId() {
		return $this->user_group_id;
	}

	/**
	 * Sets user group identifier of the user.
	 *
	 * @param int $user_group_id User group identifier.
	 * @return kyUser
	 */
	public function setUserGroupId($user_group_id) {
		$this->user_group_id = ky_assure_positive_int($user_group_id);
		$this->user_group = null;
		return $this;
	}

	/**
	 * Returns user group of the user.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyUserGroup
	 */
	public function getUserGroup($reload = false) {
		if ($this->user_group !== null && !$reload)
			return $this->user_group;

		if ($this->user_group_id === null)
			return null;

		$this->user_group = kyUserGroup::get($this->user_group_id);
		return $this->user_group;
	}

	/**
	 * Sets user group for the user.
	 *
	 * @param kyUserGroup $user_group User group.
	 * @return kyUser
	 */
	public function setUserGroup(kyUserGroup $user_group) {
		$this->user_group = ky_assure_object($user_group, 'kyUserGroup');
		$this->user_group_id = $this->user_group !== null ? $this->user_group->getId() : null;
		return $this;
	}

	/**
	 * Returns role of the user.
	 *
	 * @see kyUser:ROLE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getUserRole() {
		return $this->user_role;
	}

	/**
	 * Sets role of the user.
	 *
	 * @see kyUser:ROLE constants.
	 *
	 * @param string $user_role User role.
	 * @return kyUser
	 */
	public function setUserRole($user_role) {
		$this->user_role = ky_assure_constant($user_role, $this, 'ROLE');
		return $this;
	}

	/**
	 * Returns identifier of user organization assigned to the user.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserOrganizationId() {
		return $this->user_organization_id;
	}

	/**
	 * Sets user organization assigned the user by its identifier.
	 *
	 * @param int $user_organization_id User organization identifier.
	 * @return kyUser
	 */
	public function setUserOrganizationId($user_organization_id) {
		$this->user_organization_id = ky_assure_positive_int($user_organization_id);
		$this->user_organization = null;
		return $this;
	}

	/**
	 * Returns user organization assigned to the user.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyUserOrganization
	 */
	public function getUserOrganization($reload = false) {
		if ($this->user_organization !== null && !$reload)
			return $this->user_organization;

		if ($this->user_organization_id === null)
			return null;

		$this->user_organization = kyUserOrganization::get($this->user_organization_id);
		return $this->user_organization;
	}

	/**
	 * Sets user organization assigned to the user.
	 *
	 * @param kyUserOrganization $user_organization User organization.
	 * @return kyUser
	 */
	public function setUserOrganization(kyUserOrganization $user_organization) {
		$this->user_organization = ky_assure_object($user_organization, 'kyUserOrganization');
		$this->user_organization_id = $this->user_organization !== null ? $this->user_organization->getId() : null;
		return $this;
	}

	/**
	 * Returns salutation of the user.
	 *
	 * @see kyUser::SALUTATION constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getSalutation() {
		return $this->salutation;
	}

	/**
	 * Sets salutation of the user.
	 *
	 * @see kyUser::SALUTATION constants.
	 *
	 * @param string $salutation User salutation.
	 * @return kyUser
	 */
	public function setSalutation($salutation) {
		$this->salutation = ky_assure_constant($salutation, $this, 'SALUTATION');
		return $this;
	}

	/**
	 * Returns expiration date of the user or null when expiration is disabled.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getUserExpiry($format = null) {
		if ($this->user_expiry == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->user_expiry);
	}

	/**
	 * Sets expiration date of the user.
	 *
	 * @see http://www.php.net/manual/en/function.strtotime.php
	 *
	 * @param string|int|null $user_expiry Date and time when the user will expire (timestamp or string format understood by PHP strtotime). Null to disable expiration.
	 * @return kyUser
	 */
	public function setUserExpiry($user_expiry) {
		$this->user_expiry = is_numeric($user_expiry) || $user_expiry === null ? ky_assure_positive_int($user_expiry) : strtotime($user_expiry);
		return $this;
	}

	/**
	 * Returns full name of the user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFullName() {
		return $this->full_name;
	}

	/**
	 * Sets full name of the user.
	 *
	 * @param string $full_name Full name of the user.
	 * @return kyUser
	 */
	public function setFullName($full_name) {
		$this->full_name = ky_assure_string($full_name);
		return $this;
	}

	/**
	 * Returns first e-mail from the list of user e-mails.
	 *
	 * @return string
	 */
	public function getEmail() {
		return reset($this->email);
	}

	/**
	 * Returns list of user e-mails.
	 *
	 * @return string[]
	 * @filterBy name=Email
	 */
	public function getEmails() {
		return $this->email;
	}

	/**
	 * Adds an e-mail to the list user e-mails.
	 *
	 * @param string $email E-mail address.
	 * @param bool $clear Clear the list before adding.
	 */
	public function addEmail($email, $clear = false) {
		if ($clear) {
			$this->email = array();
		}

		if ($email !== null) {
			$this->email[] = ky_assure_string($email);
		}
	}

	/**
	 * Return designation of the user.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDesignation() {
		return $this->designation;
	}

	/**
	 * Sets designation of the user.
	 *
	 * @param string $designation Designation of the user.
	 * @return kyUser
	 */
	public function setDesignation($designation) {
		$this->designation = ky_assure_string($designation);
		return $this;
	}

	/**
	 * Returns phone number of the user.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Sets phone number of the user.
	 *
	 * @param string $phone Phone number of the user.
	 * @return kyUser
	 */
	public function setPhone($phone) {
		$this->phone = ky_assure_string($phone);
		return $this;
	}

	/**
	 * Returns date and time when the user was created.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDateline($format = null) {
		if ($this->dateline == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->dateline);
	}

	/**
	 * Returns date and time when the user last logged in.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getLastVisit($format = null) {
		if ($this->last_visit == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->last_visit);
	}

	/**
	 * Returns whether the user is enabled.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsEnabled() {
		return $this->is_enabled;
	}

	/**
	 * Sets whether the user is enabled.
	 *
	 * @param bool $is_enabled True, to enable the user. False, to disable the user.
	 * @return kyUser
	 */
	public function setIsEnabled($is_enabled) {
		$this->is_enabled = ky_assure_bool($is_enabled);
		return $this;
	}

	/**
	 * Returns timezone of the user.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 * Sets timezone of the user.
	 * See http://php.net/manual/en/timezones.php for list of available timezones.
	 *
	 * @param string $timezone Timezone of the user.
	 * @return kyUser
	 */
	public function setTimezone($timezone) {
		$this->timezone = ky_assure_string($timezone);
		return $this;
	}

	/**
	 * Returns whether the user has enabled daylight saving time.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getEnableDST() {
		return $this->enable_dst;
	}

	/**
	 * Sets whether the user has enabled daylight saving time.
	 *
	 * @param bool $enable_dst True, to enable daylight saving time. False, to disable daylight saving time.
	 * @return kyUser
	 */
	public function setEnableDST($enable_dst) {
		$this->enable_dst = ky_assure_bool($enable_dst);
		return $this;
	}

	/**
	 * Return Service Level Agreement plan assigned to the user.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getSLAPlanId() {
		return $this->sla_plan_id;
	}

	/**
	 * Sets identifier of the Service Level Agreement plan assigned to the user.
	 *
	 * @param int $sla_plan_id Service Level Agreement plan identifier.
	 * @return kyUser
	 */
	public function setSLAPlanId($sla_plan_id) {
		$this->sla_plan_id = ky_assure_positive_int($sla_plan_id);
		return $this;
	}

	/**
	 * Returns expiration date of Service Level Agreement plan assignment or null when expiration is disabled.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getSLAPlanExpiry($format = null) {
		if ($this->sla_plan_expiry == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->sla_plan_expiry);
	}

	/**
	 * Sets expiration date of Service Level Agreement plan assignment.
	 *
	 * @see http://www.php.net/manual/en/function.strtotime.php
	 *
	 * @param string|int|null $sla_plan_expiry Date and time when Service Level Agreement plan for this user will expire (timestamp or string format understood by PHP strtotime). Null to disable expiration.
	 * @return kyUser
	 */
	public function setSLAPlanExpiry($sla_plan_expiry) {
		$this->sla_plan_expiry = is_numeric($sla_plan_expiry) || $sla_plan_expiry === null ? ky_assure_positive_int($sla_plan_expiry) : strtotime($sla_plan_expiry);
		return $this;
	}

	/**
	 * Sets password of the user.
	 *
	 * @param string $password Password of the user.
	 * @return kyUser
	 */
	public function setPassword($password) {
		$this->password = ky_assure_string($password);
		return $this;
	}

	/**
	 * Sets whether to send welcome email to new user.
	 *
	 * @param bool $send_welcome_email True, to send welcome email to new user. False, otherwise.
	 * @return kyUser
	 */
	public function setSendWelcomeEmail($send_welcome_email) {
		$this->send_welcome_email = ky_assure_bool($send_welcome_email);
		return $this;
	}

	/**
	 * Creates new user.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $full_name Full name of new user.
	 * @param string $email E-mail address of new user.
	 * @param kyUserGroup $user_group User group of new user.
	 * @param string $password Password of new user.
	 * @return kyUser
	 */
	static public function createNew($full_name, $email, kyUserGroup $user_group, $password) {
		$new_user = new kyUser();
		$new_user->setFullName($full_name);
		$new_user->addEmail($email);
		$new_user->setUserGroup($user_group);
		$new_user->setPassword($password);
		return $new_user;
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