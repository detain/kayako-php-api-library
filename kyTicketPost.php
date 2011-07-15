<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Kayako TicketPost object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicketPost extends kyObjectBase {
	const CREATOR_STAFF = 1;
	const CREATOR_USER = 2;
	const CREATOR_CLIENT = 2;

	static protected $controller = '/Tickets/TicketPost';
	static protected $object_xml_name = 'post';

	private $id = null;
	private $ticket_id = null;
	private $dateline = null;
	private $user_id = null;
	private $full_name = null;
	private $email = null;
	private $email_to = null;
	private $ip_address = null;
	private $has_attachments = null;
	private $creator = null;
	private $is_third_party = null;
	private $is_html = null;
	private $is_emailed = null;
	private $staff_id = null;
	private $is_survey_comment = null;
	private $contents = null;
	private $subject = null;

	private $attachments = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->ticket_id = intval($data['ticketid']);
		$this->dateline = intval($data['dateline']) > 0 ? date(self::$datetime_format, $data['dateline']) : null;
		$this->user_id = intval($data['userid']);
		$this->full_name = $data['fullname'];
		$this->email = $data['email'];
		$this->email_to = $data['emailto'];
		$this->ip_address = $data['ipaddress'];
		$this->has_attachments = intval($data['hasattachments']) === 0 ? false : true;
		$this->creator = intval($data['creator']);
		$this->is_third_party = intval($data['isthirdparty']) === 0 ? false : true;
		$this->is_html = intval($data['ishtml']) === 0 ? false : true;
		$this->is_emailed = intval($data['isemailed']) === 0 ? false : true;
		$this->staff_id = intval($data['staffid']);
		$this->is_survey_comment = intval($data['issurveycomment']) === 0 ? false : true;
		$this->contents = $data['contents'];
	}

	protected function buildData($method) {
		$data = array();

		$data['ticketid'] = $this->ticket_id;
		$data['subject'] = $this->subject;
		$data['contents'] = $this->contents;
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
		throw new Exception("You can't update objects of type kyTicketPost.");
	}

	public function delete() {
		static::_delete(array($this->ticket_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (creator: %s)", substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''), $this->getFullName());
	}

	public function getId($complete = false) {
		return $complete ? array($this->ticket_id, $this->id) : $this->id;
	}

	/**
	 *
	 * @return int
	 */
	public function getTicketId() {
		return $this->ticket_id;
	}

	/**
	 *
	 * @param int $ticket_id
	 * @return kyTicketPost
	 */
	public function setTicketId($ticket_id) {
		$this->ticket_id = $ticket_id;
		return $this;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getDateline() {
		return $this->dateline;
	}

	/**
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyUser
	 */
	public function getUser() {
		if ($this->user_id === null || $this->user_id <= 0)
			return null;

		return kyUser::get($this->user_id);
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getFullName() {
		return $this->full_name;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getEmailTo() {
		return $this->email_to;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getIPAddress() {
		return $this->ip_address;
	}

	/**
	 *
	 * @return bool
	 * @filterBy()
	 * @orderBy()
	 */
	public function getHasAttachments() {
		return $this->has_attachments;
	}

	/**
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getCreatorType() {
		return $this->creator;
	}

	/**
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getIsThirdParty() {
		return $this->is_third_party;
	}

	/**
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getIsHTML() {
		return $this->is_html;
	}

	/**
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getEmailed() {
		return $this->is_emailed;
	}

	/**
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getStaffId() {
		return $this->staff_id;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyStaff
	 */
	public function getStaff() {
		if ($this->staff_id === null || $this->staff_id <= 0)
			return null;

		return kyStaff::get($this->staff_id);
	}

	/**
	 * Sets the creator (User or Staff) of this post.
	 *
	 * @param int $type Creator type. One of self::CREATOR_* constants.
	 * @param int $id Creator (User of Staff) identifier.
	 * @return kyTicketPost
	 */
	public function setCreator($type, $id) {
		$this->creator = $type;
		switch ($type) {
			case self::CREATOR_STAFF:
				$this->staff_id = $id;
				break;
			case self::CREATOR_USER:
				$this->user_id = $id;
				break;
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
	 *
	 * @return bool
	 * @filterBy()
	 */
	public function getIsSurveyComment() {
		return $this->is_survey_comment;
	}

	/**
	 *
	 * @param string $subject
	 * @return kyTicketPost
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of this post.
	 *
	 * @param string $contents
	 * @return kyTicketPost
	 */
	public function setContents($contents) {
		$this->contents = $contents;
		return $this;
	}

	/**
	 * Returns list of attachments in this post. Result is cached.
	 *
	 * @param bool $reload True to reload attachments from server.
	 * @return array
	 */
	public function getAttachments($reload = false) {
		if ($this->attachments === null || $reload) {
			$this->attachments = array();
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
		return new kyResultSet($this->attachments);
	}

	/**
	 * Creates new ticket post.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTicket $ticket Ticket in which to create the post.
	 * @param kyUser|kyStaff $creator Creator (User or Staff) of new post.
	 * @param string $contents Contents of new post.
	 * @param string $subject Subject of new post (it's not displayed anywhere in Kayako so I don't see why it's required in API hence the default value).
	 * @return kyTicketPost
	 */
	static public function createNew($ticket, $creator, $contents, $subject = 'Brak tytułu') {
		$new_ticket_post = new kyTicketPost();
		$new_ticket_post->setTicketId($ticket->getId());
		if ($creator instanceOf kyUser) {
			$new_ticket_post->setCreator(self::CREATOR_USER, $creator->getId());
		} elseif ($creator instanceOf kyStaff) {
			$new_ticket_post->setCreator(self::CREATOR_STAFF, $creator->getId());
		}
		$new_ticket_post->setContents($contents);
		$new_ticket_post->setSubject($subject);
		return $new_ticket_post;
	}

	/**
	 * Creates new attachment in this post with contents provided as parameter.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $contents Raw contents of the file.
	 * @param string $file_name Filename.
	 */
	public function newAttachment($contents, $file_name) {
		return kyTicketAttachment::createNewFromFile($this, $file_path, $file_name);
	}

	/**
	 * Creates new attachment in this post with contents read from physical file.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 */
	public function newAttachmentFromFile($file_path, $file_name = null) {
		return kyTicketAttachment::createNewFromFile($this, $file_path, $file_name);
	}
}