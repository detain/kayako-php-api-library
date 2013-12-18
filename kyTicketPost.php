<?php
/**
 * Kayako TicketPost object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketPost
 * @since Kayako version 4.50.1636
 * @package Object\Ticket
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyTicketPost extends kyObjectBase {
	/**
	 * Post creator type - staff user.
	 * @var int
	 */
	const CREATOR_STAFF = 1;

	/**
	 * Post creator type - user.
	 * @var int
	 */
	const CREATOR_USER = 2;

	/**
	 * Post creator type - user.
	 * @var int
	 */
	const CREATOR_CLIENT = 2;

	/**
	 * Post creator type - owner of e-mail marked as CC in ticket properties.
	 * @var int
	 */
	const CREATOR_CC = 3;

	/**
	 * Post creator type - owner of e-mail marked as BCC in ticket properties.
	 * @var int
	 */
	const CREATOR_BCC = 4;

	/**
	 * Post creator type - owner of e-mail marked as Third Party in ticket properties.
	 * @var int
	 */
	const CREATOR_THIRDPARTY = 5;

	static protected $controller = '/Tickets/TicketPost';
	static protected $object_xml_name = 'post';

	/**
	 * Ticket post identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Ticket identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $ticket_id;

	/**
	 * Timestamp of creation date and time.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	/**
	 * Identifier of the user who created this post.
	 *
	 * Applicable if the post was created by a known user through an email queue or through the web interface.
	 *
	 * @apiField
	 * @var int
	 */
	protected $user_id;

	/**
	 * The full name of the person who created the ticket post.
	 * @apiField
	 * @var int
	 */
	protected $full_name;

	/**
	 * The email address of the person who created the ticket post.
	 * @apiField
	 * @var string
	 */
	protected $email;

	/**
	 * The email address of the user associated with the ticket.
	 *
	 * Applicable when the 'send email' option is used by the a staff user when creating the ticket post.
	 *
	 * @apiField
	 * @var string
	 */
	protected $email_to;

	/**
	 * IP address from which this post was created.
	 * @apiField
	 * @var string
	 */
	protected $ip_address;

	/**
	 * Whether this ticket post has attachments.
	 * @apiField
	 * @var bool
	 */
	protected $has_attachments;

	/**
	 * Type of this ticket post creator.
	 *
	 * @see kyTicketPost::CREATOR constants.
	 *
	 * @apiField getter=getCreatorType
	 * @var int
	 */
	protected $creator;

	/**
	 * Whether this post was created by owner of e-mail marked as Third Party in ticket properties.
	 * @apiField
	 * @var bool
	 */
	protected $is_third_party;

	/**
	 * Whether this ticket post contains HTML data.
	 * @apiField
	 * @var bool
	 */
	protected $is_html;

	/**
	 * Whether this post was created through an email queue.
	 * @apiField
	 * @var bool
	 */
	protected $is_emailed;

	/**
	 * Staff user identifier.
	 *
	 * Applicable if the post was created by staff user.
	 *
	 * @apiField
	 * @var int
	 */
	protected $staff_id;

	/**
	 * Whether this post is a survey comment.
	 * @apiField
	 * @var bool
	 */
	protected $is_survey_comment;

	/**
	 * Ticket post contents.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	/**
	 * The subject this ticket post.
	 *
	 * If the ticket post was created through an e-mail queue this is subject of the email message that resulted in the creation of the post.
	 *
	 * @apiField
	 * @var string
	 */
	protected $subject;

	/**
	 * Whether the ticket post should be created as private (hidden from the customer) or not.
	 * @apiField
	 * @var bool
	 */
	protected $is_private = false;

	/**
	 * Ticket post attachments.
	 * @var kyTicketAttachment[]
	 */
	private $attachments = null;

	/**
	 * User, the creator of this post.
	 * @var kyUser
	 */
	private $user = null;

	/**
	 * Staff user, the creator of this post.
	 *
	 * Applicable if the post was created by staff user.
	 *
	 * @var kyStaff
	 */
	private $staff = null;

	/**
	 * Ticket that this post is connected to.
	 * @var kyTicket
	 */
	private $ticket = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->ticket_id = ky_assure_positive_int($data['ticketid']);
		$this->dateline = ky_assure_positive_int($data['dateline']);
		$this->user_id = ky_assure_positive_int($data['userid']);
		$this->full_name = $data['fullname'];
		$this->email = $data['email'];
		$this->email_to = $data['emailto'];
		$this->ip_address = $data['ipaddress'];
		$this->has_attachments = ky_assure_bool($data['hasattachments']);
		$this->creator = intval($data['creator']);
		$this->is_third_party = ky_assure_bool($data['isthirdparty']);
		$this->is_html = ky_assure_bool($data['ishtml']);
		$this->is_emailed = ky_assure_bool($data['isemailed']);
		$this->staff_id = ky_assure_positive_int($data['staffid']);
		$this->is_survey_comment = ky_assure_bool($data['issurveycomment']);
		$this->contents = $data['contents'];
		//isprivate field is not returned when getting all ticket posts (it may be a bug in Kayako)
		if (array_key_exists('isprivate', $data)) {
			$this->is_private = ky_assure_bool($data['isprivate']);
		} else {
			$this->is_private = null;
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['ticketid'] = $this->ticket_id;
		$data['subject'] = $this->subject !== null ? $this->subject : '';
		$data['contents'] = $this->contents;
		$data['isprivate'] = $this->is_private;

		if (!is_numeric($this->staff_id) && !is_numeric($this->user_id))
			throw new kyException("Value for API fields 'staffid' or 'userid' is required for this operation to complete.");

		switch ($this->creator) {
			case self::CREATOR_STAFF:
				$data['staffid'] = $this->staff_id;
				break;
			case self::CREATOR_USER:
				$data['userid'] = $this->user_id;
				break;
		}

		return $data;
	}

	/**
	 * Returns all posts of the ticket.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @return kyResultSet
	 */
	static public function getAll($ticket_id) {
		$search_parameters = array('ListAll');

		$search_parameters[] = $ticket_id;

		return parent::getAll($search_parameters);
	}

	/**
	 * Returns ticket post.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param int $id Ticket post identifier.
	 * @return kyTicketPost
	 */
	static public function get($ticket_id, $id) {
		return parent::get(array($ticket_id, $id));
	}

	public function update() {
		throw new BadMethodCallException("You can't update objects of type kyTicketPost.");
	}

	public function delete() {
		self::getRESTClient()->delete(static::$controller, array($this->ticket_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (creator: %s)", substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''), $this->getFullName());
	}

	public function getId($complete = false) {
		return $complete ? array($this->ticket_id, $this->id) : $this->id;
	}

	/**
	 * Returns the ticket identifier.
	 *
	 * @return int
	 */
	public function getTicketId() {
		return $this->ticket_id;
	}

	/**
	 * Sets the ticket identifier.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @return kyTicketPost
	 */
	public function setTicketId($ticket_id) {
		$this->ticket_id = ky_assure_positive_int($ticket_id);
		$this->ticket = null;
		return $this;
	}

	/**
	 * Returns the ticket that this post is connected with.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyTicket
	 */
	public function getTicket($reload = false) {
		if ($this->ticket !== null && !$reload)
			return $this->ticket;

		if ($this->ticket_id === null)
			return null;

		$this->ticket = kyTicket::get($this->ticket_id);
		return $this->ticket;
	}

	/**
	 * Sets the ticket that the post will be connected with.
	 *
	 * @param kyTicket $ticket Ticket.
	 * @return kyTicketPost
	 */
	public function setTicket($ticket) {
		$this->ticket = ky_assure_object($ticket, 'kyTicket');
		$this->ticket_id = $this->ticket !== null ? $this->ticket->getId() : null;
		return $this;
	}

	/**
	 * Returns date and time this post was created.
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
	 * Returns identifier of the user, the creator of this post.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * Sets identifier of the user, the creator of this post.
	 *
	 * @param int $user_id User identifier.
	 * @return kyTicketPost
	 */
	public function setUserId($user_id) {
		$this->user_id = ky_assure_positive_int($user_id);
		$this->creator = $this->user_id > 0 ? self::CREATOR_USER : null;
		$this->user = null;
		$this->staff_id = null;
		$this->staff = null;
		return $this;
	}

	/**
	 * Gets the user, the creator of this ticket post.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyUser
	 */
	public function getUser($reload = false) {
		if ($this->user !== null && !$reload)
			return $this->user;

		if ($this->user_id === null)
			return null;

		$this->user = kyUser::get($this->user_id);
		return $this->user;
	}

	/**
	 * Sets the user, the creator of this post.
	 *
	 * @param kyUser $user User.
	 * @return kyTicketPost
	 */
	public function setUser($user) {
		$this->user = ky_assure_object($user, 'kyUser');
		$this->user_id = $this->user !== null ? $this->user->getId() : null;
		$this->creator = $this->user !== null ? self::CREATOR_USER : null;
		$this->staff_id = null;
		$this->staff = null;
		return $this;
	}

	/**
	 * Returns the full name of the person who created the ticket post.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFullName() {
		return $this->full_name;
	}

	/**
	 * Returns the email address of the person who created the ticket post.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Returns the email address of the user associated with the ticket.
	 *
	 * Applicable when the 'send email' option is used by the a staff user when creating the ticket post.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getEmailTo() {
		return $this->email_to;
	}

	/**
	 * Returns IP address from which this post was created.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getIPAddress() {
		return $this->ip_address;
	}

	/**
	 * Returns whether this ticket post has attachments.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getHasAttachments() {
		return $this->has_attachments;
	}

	/**
	 * Returns type of this ticket post creator.
	 *
	 * @see kyTicketPost::CREATOR constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorType() {
		return $this->creator;
	}

	/**
	 * Returns whether this post was created by owner of e-mail marked as Third Party in ticket properties.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsThirdParty() {
		return $this->is_third_party;
	}

	/**
	 * Returns whether this ticket post contains HTML data.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsHTML() {
		return $this->is_html;
	}

	/**
	 * Returns whether this post was created through an email queue.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsEmailed() {
		return $this->is_emailed;
	}

	/**
	 * Returns identifier of staff user, the creator of this post.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStaffId() {
		return $this->staff_id;
	}

	/**
	 * Sets identifier of staff user, the creator of this post.
	 *
	 * @param int $staff_id Staff user identifier.
	 * @return kyTicketPost
	 */
	public function setStaffId($staff_id) {
		$this->staff_id = ky_assure_positive_int($staff_id);
		$this->creator = $this->staff_id > 0 ? self::CREATOR_STAFF : null;
		$this->staff = null;
		$this->user_id = null;
		$this->user = null;
		return $this;
	}

	/**
	 * Gets the staff user, the creator of this ticket post.
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
	 * Sets staff user, the creator of this post.
	 *
	 * @param kyStaff $staff Staff user.
	 * @return kyTicketPost
	 */
	public function setStaff($staff) {
		$this->staff = ky_assure_object($staff, 'kyStaff');
		$this->staff_id = $this->staff !== null ? $this->staff->getId() : null;
		$this->creator = $this->staff !== null ? self::CREATOR_STAFF : null;
		$this->user_id = null;
		$this->user = null;
		return $this;
	}

	/**
	 * Sets the creator (User or Staff) of this post.
	 *
	 * @see kyTicketPost::CREATOR constants.
	 *
	 * @param int|kyUser|kyStaff $creator User identifier OR staff identifier OR user OR staff user.
	 * @param int $type Creator type. Required only when $creator is an identifier.
	 * @return kyTicketPost
	 */
	public function setCreator($creator, $type = null) {
		if (is_numeric($creator)) {
			switch ($type) {
				case self::CREATOR_USER:
					$this->setUserId($creator);
				break;

				case self::CREATOR_STAFF:
					$this->setStaffId($creator);
				break;
			}
		} elseif ($creator instanceof kyUser) {
			$this->setUser($creator);
		} elseif ($creator instanceof kyStaff) {
			$this->setStaff($creator);
		}

		return $this;
	}

	/**
	 * Returns creator of this post (User or Staff).
	 *
	 * @return kyUser|kyStaff
	 */
	public function getCreator() {
		switch ($this->creator) {
			case self::CREATOR_STAFF:
				return $this->getStaff();
			case self::CREATOR_USER:
				return $this->getUser();
		}
		return null;
	}

	/**
	 * Returns whether this post is a survey comment.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getIsSurveyComment() {
		return $this->is_survey_comment;
	}

	/**
	 * Returns subject of this post.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets subject of this post.
	 *
	 * @param string $subject Post subject.
	 * @return kyTicketPost
	 */
	public function setSubject($subject) {
		$this->subject = ky_assure_string($subject);
		return $this;
	}

	/**
	 * Returns contents of this post.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of this post.
	 *
	 * @param string $contents Post contents.
	 * @return kyTicketPost
	 */
	public function setContents($contents) {
		$this->contents = ky_assure_string($contents);
		return $this;
	}

	/**
	 * Returns whether the ticket post was created as private (hidden from the customer) or not.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsPrivate() {
		return $this->is_private;
	}

	/**
	 * Sets whether the ticket post should be created as private (hidden from the customer) or not.
	 *
	 * @param bool $is_private Whether the ticket post should be created as private (hidden from the customer) or not.
	 * @return kyTicketPost
	 */
	public function setIsPrivate($is_private) {
		$this->is_private = ky_assure_bool($is_private);
		return $this;
	}

	/**
	 * Returns list of attachments in this post. Result is cached.
	 *
	 * @param bool $reload True to reload attachments from server.
	 * @return kyResultSet
	 */
	public function getAttachments($reload = false) {
		if ($this->attachments === null || $reload) {
			$this->attachments = array();

			if ($this->has_attachments) {
				/*
				 * Need to get all attachments, and then filter by post identifier.
				 */
				$attachments = kyTicketAttachment::getAll($this->ticket_id);
				foreach ($attachments as $attachment) {
					/* @var $attachment kyTicketAttachment */
					if ($attachment->getTicketPostId() === $this->id) {
						$this->attachments[] = $attachment;
					}
				}
			}
		}
		/** @noinspection PhpParamsInspection */
		return new kyResultSet($this->attachments);
	}

	/**
	 * Creates new ticket post.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTicket $ticket Ticket in which to create the post.
	 * @param kyUser|kyStaff $creator Creator (User or Staff) of new post.
	 * @param string $contents Contents of new post.
	 * @return kyTicketPost
	 */
	static public function createNew($ticket, $creator, $contents) {
		$new_ticket_post = new kyTicketPost();
		$new_ticket_post->setTicket($ticket);
		$new_ticket_post->setCreator($creator);
		$new_ticket_post->setContents($contents);
		return $new_ticket_post;
	}

	/**
	 * Creates new attachment in this post with contents provided as parameter.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $contents Raw contents of the file.
	 * @param string $file_name Filename.
	 * @return kyTicketAttachment
	 */
	public function newAttachment($contents, $file_name) {
		return kyTicketAttachment::createNew($this, $contents, $file_name);
	}

	/**
	 * Creates new attachment in this post with contents read from physical file.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 * @return kyTicketAttachment
	 */
	public function newAttachmentFromFile($file_path, $file_name = null) {
		return kyTicketAttachment::createNewFromFile($this, $file_path, $file_name);
	}
}