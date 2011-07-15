<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Kayako UserGroup object.
 *
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
 */
class kyUserGroup extends kyObjectBase {

	const TYPE_GUEST = 'guest';
	const TYPE_REGISTERED = 'registered';

	static protected $controller = '/Base/UserGroup';
	static protected $object_xml_name = 'usergroup';

	private $id = null;
	private $title = null;
	private $type = null;
	private $is_master = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->type = $data['grouptype'];
		$this->is_master = intval($data['ismaster']) === 0 ? false : true;
	}

	protected function buildData($method) {
		$data = array();

		$data['title'] = $this->title;
		$data['grouptype'] = $this->type;

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
	 * @return kyUserGroup
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @param string $type User group type. One of self::TYPE_* constants.
	 * @return kyUserGroup
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @return bool
	 */
	public function getIsMaster() {
		return $this->is_master;
	}
}