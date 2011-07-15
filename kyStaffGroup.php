<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Kayako StaffGroup object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+StaffGroup
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
 */
class kyStaffGroup extends kyObjectBase {

	static protected $controller = '/Base/StaffGroup';
	static protected $object_xml_name = 'staffgroup';

	private $id = null;
	private $title = null;
	private $is_admin = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->is_admin = intval($data['isadmin']) === 0 ? false : true;
	}

	protected function buildData($method) {
		$data = array();

		$data['title'] = $this->title;
		$data['isadmin'] = $this->is_admin ? 0 : 1;

		return $data;
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @param string $title
	 * @return kyStaffGroup
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 *
	 * @return bool
	 */
	public function getIsAdmin() {
		return $this->is_admin;
	}

	/**
	 *
	 * @param bool $is_admin
	 * @return kyStaffGroup
	 */
	public function setIsAdmin($is_admin) {
		$this->is_admin = $is_admin;
		return $this;
	}
}