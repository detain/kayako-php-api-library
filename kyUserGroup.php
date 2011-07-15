<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako UserGroup object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyUserGroup extends kyObjectBase {

	/**
	 * Type of user group - guest.
	 * @var int
	 */
	const TYPE_GUEST = 'guest';

	/**
	 * Type of user group - registered.
	 * @var int
	 */
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

	public function toString() {
		return sprintf("%s (type: %s)", $this->getTitle(), $this->getType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of the user group.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the user group.
	 *
	 * @param string $title Title of the user group.
	 * @return kyUserGroup
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Returns type of the user group - one of kyUserGroup::TYPE_* constants.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets type of the user group.
	 *
	 * @param string $type Type of the user group - one of kyUserGroup::TYPE_* constants
	 * @return kyUserGroup
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Returns whether the user group is master group (built-in).
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getIsMaster() {
		return $this->is_master;
	}

	/**
	 * Creates new user group.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $title Title of new user group.
	 * @param string $type Type of new user group - one of kyUserGroup::TYPE_* constants.
	 * @return kyUserGroup
	 */
	static public function createNew($title, $type = self::TYPE_REGISTERED) {
		$new_user_group = new kyUserGroup();
		$new_user_group->setTitle($title);
		$new_user_group->setType($type);
		return $new_user_group;
	}

	/**
	 * Creates new user in this user group.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $full_name Full name of new user.
	 * @param string $email E-mail address of new user.
	 * @param string $password Password of new user.
	 * @return kyUser
	 */
	public function newUser($full_name, $email, $password) {
		return kyUser::createNew($full_name, $email, $this, $password);
	}
}