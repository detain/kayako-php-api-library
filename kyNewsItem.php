<?php
/**
 * Kayako NewsItem object.
 * Known issues:
 * - could not create NewsItem with PUBLIC and PRIVATE type (http://dev.kayako.com/browse/SWIFT-3108)
 * - fields customemailsubject and fromname are ignored (http://dev.kayako.com/browse/SWIFT-3111)
 * - field totalcomments is not updated by Kayako
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+NewsItem
 * @since Kayako version 4.51.1891
 * @package Object\News
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyNewsItem extends kyCommentableBase {

	/**
	 * News type - Global.
	 * The news articles classified as Global are visible in both the client support center and the staff control panel.
	 *
	 * @var int
	 */
	const TYPE_GLOBAL = 1;

	/**
	 * News type - Public.
	 * The news articles classified as Public are visible only in the client support center.
	 *
	 * @var int
	 */
	const TYPE_PUBLIC = 2;

	/**
	 * News type - Private.
	 * The news article classified as Private are only visible in the staff control panel.
	 *
	 * @var int
	 */
	const TYPE_PRIVATE = 3;

	/**
	 * News status - Draft.
	 *
	 * @var int
	 */
	const STATUS_DRAFT = 1;

	/**
	 * News status - Published.
	 *
	 * @var int
	 */
	const STATUS_PUBLISHED = 2;

	static protected $controller = '/News/NewsItem';
	static protected $object_xml_name = 'newsitem';
	static protected $comment_class = 'kyNewsComment';

	/**
	 * News item identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Creator (staff) identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $staff_id;

	/**
	 * Creator (staff).
	 * @var kyStaff
	 */
	private $staff;

	/**
	 * Editor (staff) identifier.
	 * @apiField required_update=true
	 * @var int
	 */
	protected $edited_staff_id;

	/**
	 * Editor (staff).
	 * @var kyStaff
	 */
	private $edited_staff;

	/**
	 * News item type.
	 * @apiField name=newstype
	 * @var int
	 */
	protected $type;

	/**
	 * News item status.
	 * @apiField name=newsstatus
	 * @var int
	 */
	protected $status;

	/**
	 * Author's fullname.
	 * @apiField
	 * @var string
	 */
	protected $author;

	/**
	 * Author's email.
	 * @var string
	 */
	protected $author_email;

	/**
	 * Name of email notification sender.
	 * @var string
	 */
	protected $from_name;

	/**
	 * Address of email notification sender.
	 * @apiField
	 * @var string
	 */
	protected $email;

	/**
	 * News item subject.
	 * @apiField
	 * @var string
	 */
	protected $subject;

	/**
	 * Email subject.
	 * @apiField alias=customemailsubject
	 * @var string
	 */
	protected $email_subject;

	/**
	 * Whether to send email.
	 * @apiField
	 * @var bool
	 */
	protected $send_email;

	/**
	 * Whether to allow comments.
	 * @apiField
	 * @var bool
	 */
	protected $allow_comments;

	/**
	 * Timestamp of when the news item was created.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	/**
	 * Timestamp of when this news item will expire.
	 * @apiField
	 * @var int
	 */
	protected $expiry;

	/**
	 * Whether this news item was downloaded (synchronised) from external RSS feed.
	 * @apiField
	 * @var bool
	 */
	protected $is_synced = false;

	/**
	 * Total count of news item comments.
	 * @apiField
	 * @var int
	 */
	protected $total_comments = 0;

	/**
	 * If this news item is visible to specific user groups only.
	 * @see kyNewsItem::$user_group_ids
	 * @apiField
	 * @var bool
	 */
	protected $user_visibility_custom = false;

	/**
	 * User group identifiers this news item is visible to.
	 * @apiField name=usergroupidlist
	 * @var int[]
	 */
	protected $user_group_ids = array();

	/**
	 * User groups this news item is visible to.
	 * @var kyUserGroup[]
	 */
	private $user_groups = null;

	/**
	 * If this news item is visible to specific staff groups only.
	 * @see kyNewsItem::$staff_group_ids
	 * @apiField
	 * @var bool
	 */
	protected $staff_visibility_custom;

	/**
	 * User group identifiers this news item is visible to.
	 * @apiField name=staffgroupidlist
	 * @var int[]
	 */
	protected $staff_group_ids = array();

	/**
	 * User groups this news item is visible to.
	 * @var kyStaffGroup[]
	 */
	private $staff_groups = null;

	/**
	 * News item contents.
	 * @apiField required=true
	 * @var string
	 */
	protected $contents;

	/**
	 * Identifiers of news categories this news item belongs to.
	 * @apiField name=categories
	 * @var int[]
	 */
	protected $category_ids = array();

	/**
	 * News categories this news item belongs to.
	 * @var kyNewsCategory[]
	 */
	private $categories = array();

	protected function parseData($data) {
		$this->id = ky_assure_positive_int($data['id']);
		$this->staff_id = ky_assure_positive_int($data['staffid']);
		$this->type = ky_assure_positive_int($data['newstype']);
		$this->status = ky_assure_positive_int($data['newsstatus']);
		$this->author = ky_assure_string($data['author']);
		$this->author_email = ky_assure_string($data['email']);
		$this->subject = ky_assure_string($data['subject']);
		$this->email_subject = ky_assure_string($data['emailsubject']);
		$this->dateline = ky_assure_positive_int($data['dateline']);
		$this->expiry = ky_assure_positive_int($data['expiry']);
		$this->is_synced = ky_assure_bool($data['issynced']);
		$this->total_comments = ky_assure_positive_int($data['totalcomments'], 0);
		$this->allow_comments = ky_assure_bool($data['allowcomments']);
		$this->contents = ky_assure_string($data['contents']);

		$this->categories = array();
		if (is_array($data['categories'])) {
			if (is_string($data['categories'][0]['categoryid'])) {
				$this->category_ids[] = ky_assure_positive_int($data['categories'][0]['categoryid']);
			} else {
				foreach ($data['categories'][0]['categoryid'] as $category_id) {
					$this->category_ids[] = ky_assure_positive_int($category_id);
				}
			}
		}

		$this->user_visibility_custom = ky_assure_bool($data['uservisibilitycustom']);
		$this->user_group_ids = array();
		if ($this->user_visibility_custom && is_array($data['usergroupidlist'])) {
			if (is_string($data['usergroupidlist'][0]['usergroupid'])) {
				$this->user_group_ids[] = ky_assure_positive_int($data['usergroupidlist'][0]['usergroupid']);
			} else {
				foreach ($data['usergroupidlist'][0]['usergroupid'] as $user_group_id) {
					$this->user_group_ids[] = ky_assure_positive_int($user_group_id);
				}
			}
		}

		$this->staff_visibility_custom = ky_assure_bool($data['staffvisibilitycustom']);
		$this->staff_group_ids = array();
		if ($this->staff_visibility_custom && is_array($data['staffgroupidlist'])) {
			if (is_string($data['staffgroupidlist'][0]['staffgroupid'])) {
				$this->staff_group_ids[] = ky_assure_positive_int($data['staffgroupidlist'][0]['staffgroupid']);
			} else {
				foreach ($data['staffgroupidlist'][0]['staffgroupid'] as $staff_group_id) {
					$this->staff_group_ids[] = ky_assure_positive_int($staff_group_id);
				}
			}
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$this->buildDataString($data, 'subject', $this->subject);
		$this->buildDataString($data, 'contents', $this->contents);

		if ($create) {
			$this->buildDataNumeric($data, 'staffid', $this->staff_id);
			$this->buildDataNumeric($data, 'newstype', $this->type);
		} else {
			$this->buildDataNumeric($data, 'editedstaffid', $this->edited_staff_id);
		}

		$this->buildDataNumeric($data, 'newsstatus', $this->status);
		$this->buildDataString($data, 'fromname', $this->from_name);
		$this->buildDataString($data, 'email', $this->email);
		$this->buildDataString($data, 'customemailsubject', $this->email_subject);
		$this->buildDataBool($data, 'sendemail', $this->send_email);
		$this->buildDataBool($data, 'allowcomments', $this->allow_comments);

		$this->buildDataBool($data, 'uservisibilitycustom', $this->user_visibility_custom);
		if ($this->user_visibility_custom) {
			$this->buildDataList($data, 'usergroupidlist', $this->user_group_ids);
		}

		$this->buildDataBool($data, 'staffvisibilitycustom', $this->staff_visibility_custom);
		if ($this->staff_visibility_custom) {
			$this->buildDataList($data, 'staffgroupidlist', $this->staff_group_ids);
		}

		//watch out for http://dev.kayako.com/browse/SWIFT-3110 resolution
		$data['expiry'] = date('m/d/Y', $this->expiry);
//		$this->buildDataNumeric($data, 'expiry', $this->expiry);

		$this->buildDataList($data, 'newscategoryidlist', $this->category_ids);

		return $data;
	}

	static public function getAll($category = null) {
		if ($category instanceof kyNewsCategory) {
			$category_id = $category->getId();
		} else {
			$category_id = $category;
		}

		if (!is_numeric($category)) {
			return parent::getAll();
		} else {
			return parent::getAll(array('ListAll', $category_id));
		}
	}

	public function toString() {
		return sprintf("%s (type: %s, status: %s, expiry: %s)", $this->getSubject(), $this->getType(), $this->getStatus(), $this->getExpiry());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns identifier of staff user, the creator of this news item.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStaffId() {
		return $this->staff_id;
	}

	/**
	 * Sets identifier of staff user, the creator of this news item.
	 *
	 * @param int $staff_id Staff user identifier.
	 * @return kyNewsItem
	 */
	public function setStaffId($staff_id) {
		$this->staff_id = ky_assure_positive_int($staff_id);
		$this->staff = null;
		return $this;
	}

	/**
	 * Returns the staff user, the creator of this news item.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyStaff
	 */
	public function getStaff($reload = false) {
		if ($this->staff !== null && !$reload)
			return $this->staff;

		if ($this->staff_id === null)
			return null;

		$this->staff = kyStaff::get($this->staff_id);
		return $this->staff;
	}

	/**
	 * Sets staff user, the creator of this news item.
	 *
	 * @param kyStaff $staff Staff user.
	 * @return kyNewsItem
	 */
	public function setStaff($staff) {
		$this->staff = ky_assure_object($staff, 'kyStaff');
		$this->staff_id = $this->staff !== null ? $this->staff->getId() : null;
		return $this;
	}

	/**
	 * Sets identifier of staff user, the editor of this news item update.
	 *
	 * @param int $staff_id Staff user identifier.
	 * @return kyNewsItem
	 */
	public function setEditedStaffId($staff_id) {
		$this->edited_staff_id = ky_assure_positive_int($staff_id);
		$this->edited_staff = null;
		return $this;
	}

	/**
	 * Sets staff user, the editor of this news item update.
	 *
	 * @param kyStaff $staff Staff user.
	 * @return kyNewsItem
	 */
	public function setEditedStaff($staff) {
		$this->edited_staff = ky_assure_object($staff, 'kyStaff');
		$this->edited_staff_id = $this->edited_staff !== null ? $this->edited_staff->getId() : null;
		return $this;
	}

	/**
	 * Returns type of the news item.
	 *
	 * @see kyNewsItem::TYPE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets type of the news item.
	 *
	 * @see kyNewsItem::TYPE constants.
	 * @see http://dev.kayako.com/browse/SWIFT-3108
	 *
	 * @param int $type Type of the news item.
	 * @return kyNewsItem
	 */
	public function setType($type) {
		$this->type = ky_assure_constant($type, $this, 'TYPE');
		return $this;
	}

	/**
	 * Returns status of the news item.
	 *
	 * @see kyNewsItem::STATUS constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Sets status of the news item.
	 *
	 * @see kyNewsItem::STATUS constants.
	 *
	 * @param int $status Status of the news item.
	 * @return kyNewsItem
	 */
	public function setStatus($status) {
		$this->status = ky_assure_constant($status, $this, 'STATUS');
		return $this;
	}

	/**
	 * Returns full name of author.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Returns email of author.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getAuthorEmail() {
		return $this->author_email;
	}

	/**
	 * Sets name of notification email sender.
	 *
	 * @see http://dev.kayako.com/browse/SWIFT-3111
	 *
	 * @param string $from_name The From Name email header that will be used for the emails sent out to subscribers.
	 * @return kyNewsItem
	 */
	public function setFromName($from_name) {
		$this->from_name = ky_assure_string($from_name);
		return $this;
	}

	/**
	 * Sets address of notification email sender.
	 *
	 * @param string $email The From Email address that will be used for the emails sent out to subscribers. Please note that this may be the address users reply back to (if they reply to the news article email).
	 * @return kyNewsItem
	 */
	public function setEmail($email) {
		$this->email = ky_assure_string($email);
		return $this;
	}

	/**
	 * Returns subject of the news item.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets subject of the news item.
	 *
	 * @param string $subject Subject of the news item.
	 * @return kyNewsItem
	 */
	public function setSubject($subject) {
		$this->subject = ky_assure_string($subject);
		return $this;
	}

	/**
	 * Returns subject of notification email.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getEmailSubject() {
		return $this->email_subject;
	}

	/**
	 * Sets subject of notification email.
	 *
	 * @see http://dev.kayako.com/browse/SWIFT-3111
	 *
	 * @param string $email_subject Subject for the mass email that will be send out to the subscribers for this news article. If no subject is specified, the subject of the news article will be used instead.
	 * @return kyNewsItem
	 */
	public function setEmailSubject($email_subject) {
		$this->email_subject = ky_assure_string($email_subject);
		return $this;
	}

	/**
	 * Sets whether to send notification email to subscribers when creating or updating this news item.
	 *
	 * @param bool $send_email True to send notification email to subscribers when creating or updating this news item. False otherwise.
	 * @return kyNewsItem
	 */
	public function setSendEmail($send_email) {
		$this->send_email = ky_assure_bool($send_email);
		return $this;
	}

	/**
	 * Returns whether clients are permitted to comment on this news item.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getAllowComments() {
		return $this->allow_comments;
	}

	/**
	 * Sets whether clients are permitted to comment on this news item.
	 *
	 * @param bool $allow_comments True to allow clients to comment on this news item.
	 * @return kyNewsItem
	 */
	public function setAllowComments($allow_comments) {
		$this->allow_comments = ky_assure_bool($allow_comments);
		return $this;
	}

	/**
	 * Returns date and time when the news item was created.
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
	 * Returns expiration date of the user or null when expiration is disabled.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getExpiry($format = null) {
		if ($this->expiry == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->expiry);
	}

	/**
	 * Sets expiration date of the news item.
	 *
	 * @see http://www.php.net/manual/en/function.strtotime.php
	 *
	 * @param string|int|null $expiry Date and time when the news item will expire (timestamp or string format understood by PHP strtotime). Null to disable expiration.
	 * @return kyNewsItem
	 */
	public function setExpiry($expiry) {
		$this->expiry = is_numeric($expiry) || $expiry === null ? ky_assure_positive_int($expiry) : strtotime($expiry);
		return $this;
	}

	/**
	 * Returns whether this news item is expired.
	 *
	 * @return bool
	 * @filterBy name=IsExpired
	 * @orderBy name=IsExpired
	 */
	public function IsExpired() {
		return $this->expiry > 0 && $this->expiry <= time();
	}

	/**
	 * Returns whether this news item was downloaded (synchronised) from external RSS feed.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsSynced() {
		return $this->is_synced;
	}

	/**
	 * Returns total count of comments on this news item.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTotalComments() {
		return $this->total_comments;
	}

	/**
	 * Returns true to indicate that visibility of this news item is restricted to particular user groups.
	 * Use getUserGroupIds to get their identifiers or getUserGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 * Sets wheter to restrict visibility of this news item to particular user groups.
	 * Use setUserGroupIds to set these groups using identifiers or addUserGroup to set them using objects.
	 * Automatically clears user groups when set to false.
	 *
	 * @param bool $user_visibility_custom True to restrict visibility of this news item to particular user groups. False otherwise.
	 * @return kyNewsItem
	 */
	public function setUserVisibilityCustom($user_visibility_custom) {
		$this->user_visibility_custom = ky_assure_bool($user_visibility_custom);
		if ($this->user_visibility_custom === false) {
			$this->user_group_ids = array();
			$this->user_groups = null;
		}
		return $this;
	}

	/**
	 * Returns identifiers of user groups that this news item will be visible to.
	 *
	 * @return array
	 * @filterBy name=UserGroupId
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 * Sets user groups (using their identifiers) that this news item will be visible to.
	 *
	 * @param int[] $user_group_ids Identifiers of user groups that this news item will be visible to.
	 * @return kyNewsItem
	 */
	public function setUserGroupIds($user_group_ids) {
		//normalization to array
		if (!is_array($user_group_ids)) {
			if (is_numeric($user_group_ids)) {
				$user_group_ids = array($user_group_ids);
			} else {
				$user_group_ids = array();
			}
		}

		//normalization to positive integer values
		$this->user_group_ids = array();
		foreach ($user_group_ids as $user_group_id) {
			$user_group_id = ky_assure_positive_int($user_group_id);
			if ($user_group_id === null)
				continue;

			$this->user_group_ids[] = $user_group_id;
		}

		return $this;
	}

	/**
	 * Returns user groups that this news item will be visible to.
	 * Result is cached until the end of script.
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
	 * Returns whether this news item is visible to specified user group.
	 *
	 * @param kyUserGroup|int $user_group User group or its identifier.
	 * @param bool $check_expiration Whether to also check if news item is expired.
	 * @return bool
	 * @filterBy
	 */
	public function isVisibleToUserGroup($user_group, $check_expiration = true) {
		if ($this->type === self::TYPE_PRIVATE)
			return false;

		if ($check_expiration === true && $this->isExpired())
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

	/**
	 * Add user group to the list of groups that this news item will be visible to.
	 * Automatically sets custom user visibility flag to True.
	 *
	 * @param kyUserGroup $user_group User group that this news item will be visible to.
	 * @param bool $clear Clear the list before adding.
	 * @return kyNewsItem
	 */
	public function addUserGroup(kyUserGroup $user_group, $clear = false) {
		if ($clear) {
			$this->user_groups = array();
			$this->user_group_ids = array();
		}

		if (!in_array($user_group->getId(), $this->user_group_ids)) {
			$this->user_group_ids[] = $user_group->getId();
			$this->user_visibility_custom = true;
		}

		return $this;
	}

	/**
	 * Returns true to indicate that visibility of this news item is restricted to particular staff groups.
	 * Use getStaffGroupIds to get their identifiers or getStaffGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getStaffVisibilityCustom() {
		return $this->staff_visibility_custom;
	}

	/**
	 * Sets wheter to restrict visibility of this news item to particular staff groups.
	 * Use setStaffGroupIds to set these groups using identifiers or addStaffGroup to set them using objects.
	 * Automatically clears staff groups when set to false.
	 *
	 * @param bool $staff_visibility_custom True to restrict visibility of this news item to particular staff groups. False otherwise.
	 * @return kyNewsItem
	 */
	public function setStaffVisibilityCustom($staff_visibility_custom) {
		$this->staff_visibility_custom = ky_assure_bool($staff_visibility_custom);
		if ($this->staff_visibility_custom === false) {
			$this->staff_group_ids = array();
			$this->staff_groups = null;
		}
		return $this;
	}

	/**
	 * Returns identifiers of staff groups that this news item will be visible to.
	 *
	 * @return array
	 * @filterBy name=StaffGroupId
	 */
	public function getStaffGroupIds() {
		return $this->staff_group_ids;
	}

	/**
	 * Sets staff groups (using their identifiers) that this news item will be visible to.
	 *
	 * @param int[] $staff_group_ids Identifiers of staff groups that this news item will be visible to.
	 * @return kyNewsItem
	 */
	public function setStaffGroupIds($staff_group_ids) {
		//normalization to array
		if (!is_array($staff_group_ids)) {
			if (is_numeric($staff_group_ids)) {
				$staff_group_ids = array($staff_group_ids);
			} else {
				$staff_group_ids = array();
			}
		}

		//normalization to positive integer values
		$this->staff_group_ids = array();
		foreach ($staff_group_ids as $staff_group_id) {
			$staff_group_id = ky_assure_positive_int($staff_group_id);
			if ($staff_group_id === null)
				continue;

			$this->staff_group_ids[] = $staff_group_id;
		}

		return $this;
	}

	/**
	 * Returns staff groups that this news item will be visible to.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyResultSet
	 */
	public function getStaffGroups($reload = false) {
		foreach ($this->staff_group_ids as $staff_group_id) {
			if (!is_array($this->staff_groups) || !array_key_exists($staff_group_id, $this->staff_groups) || $reload) {
				$this->staff_groups[$staff_group_id] = kyStaffGroup::get($staff_group_id);
			}
		}
		return new kyResultSet(array_values($this->staff_groups));
	}

	/**
	 * Returns whether this news item is visible to specified staff group.
	 *
	 * @param kyStaffGroup|int $staff_group Staff group or its identifier.
	 * @param bool $check_expiration Whether to also check if news item is expired.
	 * @return bool
	 * @filterBy
	 */
	public function isVisibleToStaffGroup($staff_group, $check_expiration = true) {
		if ($this->type === self::TYPE_PUBLIC)
			return false;

		if ($check_expiration === true && $this->isExpired())
			return false;

		if ($this->staff_visibility_custom === false)
			return true;

		if ($staff_group instanceof kyStaffGroup) {
			$staff_group_id = $staff_group->getId();
		} else {
			$staff_group_id = intval($staff_group);
		}

		return in_array($staff_group_id, $this->staff_group_ids);
	}

	/**
	 * Add staff group to the list of groups that this news item will be visible to.
	 * Automatically sets custom staff visibility flag to True.
	 *
	 * @param kyStaffGroup $staff_group Staff group that this news item will be visible to.
	 * @param bool $clear Clear the list before adding.
	 * @return kyNewsItem
	 */
	public function addStaffGroup(kyStaffGroup $staff_group, $clear = false) {
		if ($clear) {
			$this->staff_groups = array();
			$this->staff_group_ids = array();
		}

		if (!in_array($staff_group->getId(), $this->staff_group_ids)) {
			$this->staff_group_ids[] = $staff_group->getId();
			$this->staff_visibility_custom = true;
		}

		return $this;
	}

	/**
	 * Returns contents of the news item.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of the news item. Can containt HTML tags.
	 *
	 * @param string $contents Contents of the news item.
	 * @return kyNewsItem
	 */
	public function setContents($contents) {
		$this->contents = ky_assure_string($contents);
		return $this;
	}

	/**
	 * Returns identifiers of categories of this news item.
	 *
	 * @return array
	 * @filterBy name=CategoryId
	 */
	public function getCategoryIds() {
		return $this->category_ids;
	}

	/**
	 * Sets categories (using their identifiers) for this news item.
	 *
	 * @param int[] $category_ids Identifiers of categories for this news item.
	 * @return kyNewsItem
	 */
	public function setCategoryIds($category_ids) {
		//normalization to array
		if (!is_array($category_ids)) {
			if (is_numeric($category_ids)) {
				$category_ids = array($category_ids);
			} else {
				$category_ids = array();
			}
		}

		//normalization to positive integer values
		$this->category_ids = array();
		foreach ($category_ids as $category_id) {
			$category_id = ky_assure_positive_int($category_id);
			if ($category_id === null)
				continue;

			$this->category_ids[] = $category_id;
		}

		return $this;
	}

	/**
	 * Returns categories of this news item.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyResultSet
	 */
	public function getCategories($reload = false) {
		foreach ($this->category_ids as $category_id) {
			if (!is_array($this->categories) || !array_key_exists($category_id, $this->categories) || $reload) {
				$this->categories[$category_id] = kyNewsCategory::get($category_id);
			}
		}

		foreach ($this->categories as $category_id => $category) {
			if (!in_array($category_id, $this->category_ids)) {
				unset($this->categories[$category_id]);
			}
		}

		return new kyResultSet(array_values($this->categories));
	}

	/**
	 * Returns whether this news is in specified category.
	 *
	 * @param kyNewsCategory|int $category News category or its identifier.
	 * @return bool
	 * @filterBy
	 */
	public function isInCategory($category) {
		if ($category instanceof kyNewsCategory) {
			$category_id = $category->getId();
		} else {
			$category_id = intval($category);
		}

		return in_array($category_id, $this->category_ids);
	}

	/**
	 * Adds category to the list of this news item categories.
	 *
	 * @param kyNewsCategory $category News category.
	 * @param bool $clear Clear the list before adding.
	 * @return kyNewsItem
	 */
	public function addCategory(kyNewsCategory $category, $clear = false) {
		if ($clear) {
			$this->categories = array();
			$this->category_ids = array();
		}

		if (!in_array($category->getId(), $this->category_ids)) {
			$this->category_ids[] = $category->getId();
			$this->categories[$category->getId()] = $category;
		}

		return $this;
	}

	/**
	 * Creates a news item.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $subject Subject of news item.
	 * @param string $contents Contents of news item.
	 * @param kyStaff $staff Author (staff) of news item.
	 * @return kyNewsItem
	 */
	static public function createNew($subject, $contents, kyStaff $staff) {
		$new_news_item = new kyNewsItem();
		$new_news_item->setSubject($subject);
		$new_news_item->setContents($contents);
		$new_news_item->setStaff($staff);
		return $new_news_item;
	}
}