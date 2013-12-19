<?php
/**
 * Kayako UserOrganization object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+UserOrganization
 * @since Kayako version 4.01.204
 * @package Object\User
 */
class kyUserOrganization extends kyObjectBase {

	const TYPE_RESTRICTED = 'restricted';
	const TYPE_SHARED = 'shared';

	static protected $controller = '/Base/UserOrganization';
	static protected $object_xml_name = 'userorganization';

	/**
	 * User organization identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * User organization name.
	 * @apiField required=true
	 * @var string
	 */
	protected $name;

	/**
	 * User organization type.
	 *
	 * @see kyUserOrganization::TYPE constants.
	 *
	 * @apiField name=organizationtype required_create=true
	 * @var string
	 */
	protected $type = self::TYPE_RESTRICTED;

	/**
	 * User organization address.
	 * @apiField
	 * @var string
	 */
	protected $address;

	/**
	 * User organization city.
	 * @apiField
	 * @var string
	 */
	protected $city;

	/**
	 * User organization state.
	 * @apiField
	 * @var string
	 */
	protected $state;

	/**
	 * User organization postal code.
	 * @apiField
	 * @var string
	 */
	protected $postal_code;

	/**
	 * User organization country.
	 * @apiField
	 * @var string
	 */
	protected $country;

	/**
	 * User organization phone number.
	 * @apiField
	 * @var string
	 */
	protected $phone;

	/**
	 * User organization FAX number.
	 * @apiField
	 * @var string
	 */
	protected $fax;

	/**
	 * User organization website URL.
	 * @apiField
	 * @var string
	 */
	protected $website;

	/**
	 * Timestamp of when the user organization was created.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	/**
	 * Timestamp of when the user organization was last updated.
	 * @apiField
	 * @var int
	 */
	protected $last_update;

	/**
	 * Identifier of Service Level Agreement plan associated to this user organization.
	 * @apiField
	 * @var int
	 */
	protected $sla_plan_id;

	/**
	 * Timestamp of when the Service Level Agreement plan associated to this user organization will expire.
	 * @apiField
	 * @var int
	 */
	protected $sla_plan_expiry;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->name = $data['name'];
		$this->type = $data['organizationtype'];
		$this->address = $data['address'];
		$this->city = $data['city'];
		$this->state = $data['state'];
		$this->postal_code = $data['postalcode'];
		$this->country = $data['country'];
		$this->phone = $data['phone'];
		$this->fax = $data['fax'];
		$this->website = $data['website'];
		$this->dateline = ky_assure_positive_int($data['dateline']);
		$this->last_update = ky_assure_positive_int($data['lastupdate']);
		$this->sla_plan_id = ky_assure_positive_int($data['slaplanid']);
		$this->sla_plan_expiry = ky_assure_positive_int($data['slaplanexpiry']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['name'] = $this->name;
		$data['organizationtype'] = $this->type;
		$data['address'] = $this->address;
		$data['city'] = $this->city;
		$data['state'] = $this->state;
		$data['postalcode'] = $this->postal_code;
		$data['country'] = $this->country;
		$data['phone'] = $this->phone;
		$data['fax'] = $this->fax;
		$data['website'] = $this->website;
		$data['slaplanid'] = $this->sla_plan_id;
		$data['slaplanexpiry'] = $this->sla_plan_expiry !== null ? $this->sla_plan_expiry : 0;

		return $data;
	}

	public function toString() {
		return sprintf("%s (type: %s)", $this->getName(), $this->getType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns user organization name.
	 *
	 * @returns string
	 * @filterBy
	 * @orderBy
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets user organization name.
	 *
	 * @param string $name User organization name.
	 * @return kyUserOrganization
	 */
	public function setName($name) {
		$this->name = ky_assure_string($name);
		return $this;
	}

	/**
	 * Returns user organization type.
	 *
	 * @see kyUserOrganization::TYPE constans.
	 *
	 * @returns string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets user organization type.
	 *
	 * @see kyUserOrganization::TYPE constans.
	 *
	 * @param string $type User organization type
	 * @return kyUserOrganization
	 */
	public function setType($type) {
		$this->type = ky_assure_constant($type, $this, 'TYPE');
		return $this;
	}

	/**
	 * Returns user organization address.
	 *
	 * @returns string
	 * @filterBy
	 * @orderBy
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Sets user organization address.
	 *
	 * @param string $address User organization address.
	 * @return kyUserOrganization
	 */
	public function setAddress($address) {
		$this->address = ky_assure_string($address);
		return $this;
	}

	/**
	 * Returns user organization city.
	 *
	 * @returns string
	 * @filterBy
	 * @orderBy
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Sets user organization city.
	 *
	 * @param string $city User organization city.
	 * @return kyUserOrganization
	 */
	public function setCity($city) {
		$this->city = ky_assure_string($city);
		return $this;
	}

	/**
	 * Returns user organization state.
	 *
	 * @returns string
	 * @filterBy
	 * @orderBy
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Sets user organization state.
	 *
	 * @param string $state User organization state.
	 * @return kyUserOrganization
	 */
	public function setState($state) {
		$this->state = ky_assure_string($state);
		return $this;
	}

	/**
	 * Returns user organization postal code.
	 *
	 * @returns string
	 * @filterBy
	 * @orderBy
	 */
	public function getPostalCode() {
		return $this->postal_code;
	}

	/**
	 * Sets user organization postal code.
	 *
	 * @param string $postal_code User organization postal code.
	 * @return kyUserOrganization
	 */
	public function setPostalCode($postal_code) {
		$this->postal_code = ky_assure_string($postal_code);
		return $this;
	}

	/**
	 * Returns user organization country.
	 *
	 * @returns string
	 * @filterBy
	 * @orderBy
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Sets user organization  country.
	 *
	 * @param string $country User organization country.
	 * @return kyUserOrganization
	 */
	public function setCountry($country) {
		$this->country = ky_assure_string($country);
		return $this;
	}

	/**
	 * Returns user organization phone number.
	 *
	 * @returns string
	 * @filterBy
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Sets user organization phone number.
	 *
	 * @param string $phone User organization phone number.
	 * @return kyUserOrganization
	 */
	public function setPhone($phone) {
		$this->phone = ky_assure_string($phone);
		return $this;
	}

	/**
	 * Returns user organization FAX number.
	 *
	 * @returns string
	 * @filterBy
	 */
	public function getFAX() {
		return $this->fax;
	}

	/**
	 * Sets user organization FAX number.
	 *
	 * @param string $fax User organization FAX number.
	 * @return kyUserOrganization
	 */
	public function setFAX($fax) {
		$this->fax = ky_assure_string($fax);
		return $this;
	}

	/**
	 * Returns user organization website URL.
	 *
	 * @returns string
	 * @filterBy
	 */
	public function getWebsite() {
		return $this->website;
	}

	/**
	 * Sets user organization website URL.
	 *
	 * @param string $website User organization website URL.
	 * @return kyUserOrganization
	 */
	public function setWebsite($website) {
		$this->website = ky_assure_string($website);
		return $this;
	}

	/**
	 * Returns date and time when the user organization was created.
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
	 * Returns date and time when the user organization was last updated.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getLastUpdate($format = null) {
		if ($this->last_update == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->last_update);
	}

	/**
	 * Return Service Level Agreement plan assigned to the user organization.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getSLAPlanId() {
		return $this->sla_plan_id;
	}

	/**
	 * Sets identifier of the Service Level Agreement plan assigned to the user organization.
	 *
	 * @param int $sla_plan_id Service Level Agreement plan identifier.
	 * @return kyUserOrganization
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
	 * @param string|int|null $sla_plan_expiry Date and time when Service Level Agreement plan for this user organization will expire (timestamp or string format understood by PHP strtotime). Null to disable expiration.
	 * @return kyUserOrganization
	 */
	public function setSLAPlanExpiry($sla_plan_expiry) {
		$this->sla_plan_expiry = is_numeric($sla_plan_expiry) || $sla_plan_expiry === null ? ky_assure_positive_int($sla_plan_expiry) : strtotime($sla_plan_expiry);
		return $this;
	}
}