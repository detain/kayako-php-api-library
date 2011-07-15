<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako UserOrganization object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyUserOrganization extends kyObjectBase {

	const TYPE_RESTRICTED = 'restricted';
	const TYPE_SHARED = 'shared';

	static protected $controller = '/Base/UserOrganization';
	static protected $object_xml_name = 'userorganization';

	private $id = null;
	private $name = null;
	private $type = self::TYPE_RESTRICTED;
	private $address = null;
	private $city = null;
	private $state = null;
	private $postal_code = null;
	private $country = null;
	private $phone = null;
	private $fax = null;
	private $website = null;
	private $dateline = null;
	private $last_update = null;
	private $sla_plan_id = null;
	private $sla_plan_expiry = null;

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
		$this->dateline = date(self::$datetime_format, $data['dateline']);
		$this->last_update = intval($data['lastupdate']) > 0 ? date(self::$datetime_format, $data['lastupdate']) : null;
		$this->sla_plan_id = intval($data['slaplanid']);
		$this->sla_plan_expiry = intval($data['slaplanexpiry']) > 0 ? date(self::$datetime_format, $data['slaplanexpiry']) : null;
	}

	protected function buildData($method) {
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
		$data['slaplanexpiry'] = 0;
		if ($this->sla_plan_expiry !== null)
			$this->sla_plan_expiry = strtotime($data['slaplanexpiry']);

		return $data;
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 *
	 * @returns string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @param string $name
	 * @return kyUserOrganization
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @param string $type
	 * @return kyUserOrganization
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 *
	 * @param string $address
	 * @return kyUserOrganization
	 */
	public function setAddress($address) {
		$this->address = $address;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 *
	 * @param string $city
	 * @return kyUserOrganization
	 */
	public function setCity($city) {
		$this->city = $city;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 *
	 * @param string $state
	 * @return kyUserOrganization
	 */
	public function setState($state) {
		$this->state = $state;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getPostalCode() {
		return $this->postal_code;
	}

	/**
	 *
	 * @param string $postal_code
	 * @return kyUserOrganization
	 */
	public function setPostalCode($postal_code) {
		$this->postal_code = $postal_code;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 *
	 * @param string $country
	 * @return kyUserOrganization
	 */
	public function setCountry($country) {
		$this->country = $country;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 *
	 * @param string $phone
	 * @return kyUserOrganization
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getFAX() {
		return $this->fax;
	}

	/**
	 *
	 * @param string $fax
	 * @return kyUserOrganization
	 */
	public function setFAX($fax) {
		$this->fax = $fax;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getWebsite() {
		return $this->website;
	}

	/**
	 *
	 * @param string $website
	 * @return kyUserOrganization
	 */
	public function setWebsite($website) {
		$this->website = $website;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getDateline() {
		return $this->dateline;
	}

	public function getLastUpdate() {
		return $this->last_update;
	}

	/**
	 *
	 * @returns int
	 */
	public function getSLAPlanId() {
		return $this->sla_plan_id;
	}

	/**
	 *
	 * @param int $sla_plan_id
	 * @return kyUserOrganization
	 */
	public function setSLAPlanId($sla_plan_id) {
		$this->sla_plan_id = $sla_plan_id;
		return $this;
	}

	/**
	 *
	 * @returns string
	 */
	public function getSLAPlanExpiry() {
		return $this->sla_plan_expiry;
	}

	/**
	 *
	 * @param string $sla_plan_expiry
	 * @return kyUserOrganization
	 */
	public function setSLAPlanExpiry($sla_plan_expiry) {
		$this->sla_plan_expiry = $sla_plan_expiry;
		return $this;
	}
}