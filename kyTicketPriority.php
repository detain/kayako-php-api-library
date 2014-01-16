<?php
/**
 * Kayako TicketPriority object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketPriority
 * @since Kayako version 4.01.240
 * @package Object\Ticket
 */
class kyTicketPriority extends kyObjectBase {

	const TYPE_PUBLIC = 'public';
	const TYPE_PRIVATE = 'private';

	static protected $controller = '/Tickets/TicketPriority';
	static protected $object_xml_name = 'ticketpriority';
	protected $read_only = true;

	/**
	 * Ticket priority identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Ticket priority title.
	 * @apiField
	 * @var string
	 */
	protected $title;

	/**
	 * Ticket priority display order.
	 * @apiField
	 * @var string
	 */
	protected $display_order;

	/**
	 * Foreground (font) color of this priority in GUI.
	 * @apiField getter=getForegroundColor
	 * @var string
	 */
	protected $fr_color_code;

	/**
	 * Background color of this priority in GUI.
	 * @apiField getter=getBackgroundColor
	 * @var string
	 */
	protected $bg_color_code;

	/**
	 * Path to icon displayed in GUI for this ticket priority.
	 * @apiField
	 * @var string
	 */
	protected $display_icon;

	/**
	 * Type of this ticket priority.
	 *
	 * @see kyTicketPriority::TYPE constants.
	 *
	 * @apiField
	 * @var string
	 */
	protected $type;

	/**
	 * If this ticket priority is visible to specific user groups only.
	 * @apiField
	 * @var bool
	 */
	protected $user_visibility_custom;

	/**
	 * Identifiers of user group this ticket priority is visible to.
	 * @apiField name=usergroupid
	 * @var string
	 */
	protected $user_group_ids = array();

	/**
	 * User groups this ticket priority is visible to.
	 * @var kyUserGroup[]
	 */
	private $user_groups = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->display_order = intval($data['displayorder']);
		$this->fr_color_code = $data['frcolorcode'];
		$this->bg_color_code = $data['bgcolorcode'];
		$this->type = $data['type'];
		$this->user_visibility_custom = ky_assure_bool($data['uservisibilitycustom']);
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
	 * Returns title of this ticket priority.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns display order of this ticket priority.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->display_order;
	}

	/**
	 * Returns foreground (font) color of this priority in GUI.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getForegroundColor() {
		return $this->fr_color_code;
	}

	/**
	 * Returns background color of this priority in GUI.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getBackgroundColor() {
		return $this->bg_color_code;
	}

	/**
	 * Returns type of this ticket priority.
	 *
	 * @see kyTicketPriority::TYPE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns whether this ticket priority is visible to specific user groups only.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 * Returns identifiers of user groups that this ticket priority is visible to.
	 *
	 * @return int[]
	 * @filterBy name=UserGroupId
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 * Returns user groups that this ticket priority is visible to.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyResultSet
	 */
	public function getUserGroups($reload = false) {
		foreach ($this->user_group_ids as $user_group_id) {
			if (!is_array($this->user_groups) || !array_key_exists($user_group_id, $this->user_groups) || $reload) {
				$this->user_groups[$user_group_id] = kyUserGroup::get($user_group_id);
			}
		}
		return new kyResultSet(array_values($this->user_groups));
	}

	/**
	 * Returns whether this ticket type is visible to specified user group.
	 *
	 * @param kyUserGroup|int $user_group User group or its identifier.
	 * @return bool
	 * @filterBy
	 */
	public function isVisibleToUserGroup($user_group) {
		if ($this->type !== self::TYPE_PUBLIC)
			return false;

		if ($this->user_visibility_custom === false)
			return true;

		if ($user_group instanceof kyUserGroup) {
			$user_group_id = $user_group->getId();
		} else {
			$user_group_id = intval($user_group);
		}

		return in_array($user_group_id, $this->user_group_ids);
	}
}