<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako TicketPriority object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicketPriority extends kyObjectBase {

	const TYPE_PUBLIC = 'public';
	const TYPE_PRIVATE = 'private';

	static protected $controller = '/Tickets/TicketPriority';
	static protected $object_xml_name = 'ticketpriority';
	protected $read_only = true;

	private $id = null;
	private $title = null;
	private $display_order = null;
	private $fr_color_code = null;
	private $bg_color_code = null;
	private $display_icon = null;
	private $type = null;
	private $user_visibility_custom = null;
	private $user_group_ids = array();

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->display_order = intval($data['displayorder']);
		$this->fr_color_code = $data['frcolorcode'];
		$this->bg_color_code = $data['bgcolorcode'];
		$this->display_icon = $data['displayicon'];
		$this->type = $data['type'];
		$this->user_visibility_custom = intval($data['uservisibilitycustom']) === 0 ? false : true;
		if ($this->user_visibility_custom && is_array($data['usergroupid'])) {
			foreach ($data['usergroupid'] as $user_group_id) {
				$this->user_group_ids[] = intval($user_group_id);
			}
		}
	}

	public function toString() {
		return sprintf("%s (type: %s)", $this->getTitle(), $this->getType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 */
	public function getForegroundColor() {
		return $this->fr_color_code;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 */
	public function getBackgroundColor() {
		return $this->bg_color_code;
	}

	/**
	 *
	 * @return string
	 */
	public function getDisplayIcon() {
		return $this->display_icon;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 *
	 * @return int[]
	 * @filterBy(UserGroupId)
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyResultSet
	 */
	public function getUserGroups() {
		$user_groups = array();
		foreach ($this->user_group_ids as $user_group_id) {
			$user_groups[] = kyUserGroup::get($user_group_id);
		}
		return new kyResultSet($user_groups);
	}
}