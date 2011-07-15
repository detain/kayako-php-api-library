<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako StaffGroup object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+StaffGroup
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyStaffGroup extends kyObjectBase {

	static protected $controller = '/Base/StaffGroup';
	static protected $object_xml_name = 'staffgroup';

	private $id = null;
	private $title = null;
	private $is_admin = false;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->is_admin = intval($data['isadmin']) === 0 ? false : true;
	}

	protected function buildData($method) {
		$data = array();

		//TODO: check if required parameters are present

		$data['title'] = $this->title;
		$data['isadmin'] = $this->is_admin ? 1 : 0;

		return $data;
	}

	public function toString() {
		return sprintf("%s (isadmin: %s)", $this->getTitle(), $this->getIsAdmin() ? "yes" : "no");
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of the staff group.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the staff group.
	 *
	 * @param string $title Title of the staff group.
	 * @return kyStaffGroup
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Returns whether staff members assigned to this group are Administrators.
	 *
	 * @return bool
	 * @filterBy()
	 * @orderBy()
	 */
	public function getIsAdmin() {
		return $this->is_admin;
	}

	/**
	 * Sets whether staff members assigned to this group are Administrators.
	 *
	 * @param bool $is_admin True, if you want staff members assigned to this group to be Administrators. False (default), otherwise.
	 * @return kyStaffGroup
	 */
	public function setIsAdmin($is_admin) {
		$this->is_admin = $is_admin;
		return $this;
	}
}