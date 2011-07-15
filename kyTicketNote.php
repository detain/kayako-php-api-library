<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Kayako TicketNote object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicketNote extends kyObjectBase {

	/**
	 * Color of note - yellow.
	 *
	 * @var int
	 */
	const COLOR_YELLOW = 1;

	/**
	 * Color of note - purple.
	 *
	 * @var int
	 */
	const COLOR_PURPLE = 2;

	/**
	 * Color of note - blue.
	 *
	 * @var int
	 */
	const COLOR_BLUE = 3;

	/**
	 * Color of note - green.
	 *
	 * @var int
	 */
	const COLOR_GREEN = 4;

	/**
	 * Color of note - red.
	 *
	 * @var int
	 */
	const COLOR_RED = 5;

	/**
	 * Note type - connected to ticket.
	 *
	 * @var string
	 */
	const TYPE_TICKET = 'ticket';

	/**
	 * Note type - connected to user.
	 *
	 * @var string
	 */
	const TYPE_USER = 'user';

	/**
	 * Note type - connected to user organization.
	 *
	 * @var string
	 */
	const TYPE_USER_ORGANIZATION = 'userorganization';

	static protected $controller = '/Tickets/TicketNote';
	static protected $object_xml_name = 'note';

	private $id = null;
	private $ticket_id = null;
	private $user_id = null;
	private $user_organization_id = null;
	private $type = self::TYPE_TICKET;
	private $note_color = null;
	private $creator_staff_id = null;
	private $creator_staff_name = null;
	private $for_staff_id = null;
	private $creation_date = null;
	private $contents = null;

	private $creator_staff = null;
	private $for_staff = null;
	private $ticket = null;
	private $user = null;
	private $user_organization = null;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->type = $data['_attributes']['type'];
		$this->note_color = intval($data['_attributes']['notecolor']);
		$this->creator_staff_id = intval($data['_attributes']['creatorstaffid']);
		$this->creator_staff_name = $data['_attributes']['creatorstaffname'];
		$this->for_staff_id = intval($data['_attributes']['forstaffid']);
		$this->creation_date = intval($data['_attributes']['creationdate']) > 0 ? date(self::$datetime_format, $data['_attributes']['creationdate']) : null;
		$this->contents = $data['_contents'];

		if ($this->getType() === kyTicketNote::TYPE_TICKET)
			$this->ticket_id = intval($data['_attributes']['ticketid']);
		elseif ($this->getType() === kyTicketNote::TYPE_USER)
			$this->user_id = intval($data['_attributes']['userid']);
		elseif ($this->getType() === kyTicketNote::TYPE_USER_ORGANIZATION)
			$this->user_organization_id = intval($data['_attributes']['userorganizationid']);
	}

	protected function buildData($method) {
		$data = array();

		$data['ticketid'] = $this->ticket_id;
		$data['notecolor'] = $this->note_color;
		if (is_numeric($this->creator_staff_id))
			$data['staffid'] = $this->creator_staff_id;
		elseif (strlen($this->creator_staff_name) > 0)
			$data['fullname'] = $this->creator_staff_name;
		$data['forstaffid'] = $this->for_staff_id;
		$data['contents'] = $this->contents;

		return $data;
	}

	/**
	 * Returns all notes of the ticket.
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
	 * Returns ticket note.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param int $id Ticket note identifier.
	 * @return kyTicketNote
	 */
	static public function get($ticket_id, $id) {
		return parent::get(array($ticket_id, $id));
	}

	public function create() {
		if ($this->getType() !== self::TYPE_TICKET)
			throw new Exception('You can create only note of type "ticket"');

		parent::create();
	}

	public function update() {
		throw new Exception("You can't update objects of type kyTicketNote.");
	}

	public function delete() {
		if ($this->getType() !== self::TYPE_TICKET)
			throw new Exception('You can delete only note of type "ticket"');

		static::_delete(array($this->ticket_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (type: %s)", substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''), $this->getType());
	}

	public function getId($complete = false) {
		switch ($this->getType()) {
			case self::TYPE_USER:
				return $complete ? array($this->user_id, $this->id) : $this->id;
			case self::TYPE_USER_ORGANIZATION:
				return $complete ? array($this->user_organization_id, $this->id) : $this->id;
			default:
				return $complete ? array($this->ticket_id, $this->id) : $this->id;
		}
	}

	/**
	 * Return ticket identifier of this note.
	 * Applicable only for notes of type kyTicketNote::TYPE_TICKET.
	 *
	 * @return int
	 */
	public function getTicketId() {
		if ($this->getType() !== self::TYPE_TICKET)
			return null;

		return $this->ticket_id;
	}

	/**
	 *
	 * @param int $ticket_id
	 * @return kyTicketNote
	 */
	public function setTicketId($ticket_id) {
		$this->type = self::TYPE_TICKET;
		$this->ticket_id = $ticket_id;
		$this->ticket = null;
		return $this;
	}

	/**
	 * Return the ticket this note refers to.
	 * Applicable only for notes of type kyTicketNote::TYPE_TICKET.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyTicket
	 */
	public function getTicket($reload = false) {
		if ($this->getType() !== self::TYPE_TICKET)
			return null;

		if ($this->ticket !== null && !$reload)
			return $this->ticket;

		if ($this->ticket_id === null || $this->ticket_id <= 0)
			return null;

		$this->ticket = kyTicket::get($this->ticket_id);
		return $this->ticket;
	}

	/**
	 * Sets the ticket thah the note will be connected with.
	 *
	 * @param kyTicket $ticket
	 * @return kyTicketNote
	 */
	public function setTicket(kyTicket $ticket) {
		$this->type = self::TYPE_TICKET;
		$this->ticket_id = $ticket->getId();
		$this->ticket = $ticket;
		return $this;
	}

	/**
	 * Return identifier of user this note refers to.
	 * Applicable only for notes of type kyTicketNote::TYPE_USER.
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getUserId() {
		if ($this->getType() !== self::TYPE_USER)
			return null;

		return $this->user_id;
	}

	/**
	 * Return the user this note refers to.
	 * Applicable only for notes of type kyTicketNote::TYPE_USER.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyUser
	 */
	public function getUser($reload = false) {
		if ($this->getType() !== self::TYPE_USER)
			return null;

		if ($this->user !== null && !$reload)
			return $this->user;

		if ($this->user_id === null || $this->user_id <= 0)
			return null;

		$this->user = kyUser::get($this->user_id);
		return $this->user;
	}

	/**
	 * Return identifier of user organization this note refers to.
	 * Applicable only for notes of type kyTicketNote::TYPE_USER_ORGANIZATION.
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getUserOrganizationId() {
		if ($this->getType() !== self::TYPE_USER_ORGANIZATION)
			return null;

		return $this->user_organization_id;
	}

	/**
	 * Return the user organization this note refers to.
	 * Applicable only for notes of type kyTicketNote::TYPE_USER_ORGANIZATION.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyUserOrganization
	 */
	public function getUserOrganization($reload = false) {
		if ($this->getType() !== self::TYPE_USER_ORGANIZATION)
			return null;

		if ($this->user_organization !== null && !$reload)
			return $this->user_organization;

		if ($this->user_organization_id === null || $this->user_organization_id <= 0)
			return null;

		$this->user_organization = kyUserOrganization::get($this->user_organization_id);
		return $this->user_organization;
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
	 * @return int
	 * @filterBy()
	 */
	public function getNoteColor() {
		return $this->note_color;
	}

	/**
	 *
	 * @param int $note_color
	 * @return kyTicketNote
	 */
	public function setNoteColor($note_color) {
		$this->note_color = $note_color;
		return $this;
	}

	/**
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getCreatorStaffId() {
		return $this->creator_staff_id;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyStaff
	 */
	public function getCreatorStaff() {
		if ($this->creator_staff_id === null || $this->creator_staff_id <= 0)
			return null;

		return kyStaff::get($this->creator_staff_id);
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getCreatorStaffName() {
		return $this->creator_staff_name;
	}

	/**
	 * Sets creator (Staff) of this note.
	 *
	 * @param int $creator_staff_id Creator (Staff) identifier.
	 * @param string $creator_name Creator full name.
	 * @return kyTicketNote
	 */
	public function setCreator($creator_staff_id = null, $creator_name = null) {
		if (is_numeric($creator_staff_id))
			$this->creator_staff_id = $creator_staff_id;
		else
			$this->creator_staff_name = $creator_name;
		return $this;
	}

	/**
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getForStaffId() {
		return $this->for_staff_id;
	}

	/**
	 *
	 * @param int $for_staff_id
	 * @return kyTicketNote
	 */
	public function setForStaffId($for_staff_id) {
		$this->for_staff_id = $for_staff_id;
		return $this;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyStaff
	 */
	public function getForStaff() {
		if ($this->for_staff_id === null || $this->for_staff_id <= 0)
			return null;

		return kyStaff::get($this->for_staff_id);
	}

	/**
	 *
	 * @param kyStaff $for_staff
	 * @return kyTicketNote
	 */
	public function setForStaff($for_staff) {
		$this->for_staff_id = $for_staff->getId();
		return $this;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getCreationDate() {
		return $this->creation_date;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 *
	 * @param string $contents
	 * @return kyTicketNote
	 */
	public function setContents($contents) {
		$this->contents = $contents;
		return $this;
	}
}