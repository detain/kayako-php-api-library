<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Kayako TicketNote object.
 * CAUTION: Some features needs the patch (see further).
 *
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
 */
class kyTicketNote extends kyObjectBase {

	const COLOR_YELLOW = 1;
	const COLOR_PURPLE = 2;
	const COLOR_BLUE = 3;
	const COLOR_GREEN = 4;
	const COLOR_RED = 5;

	const TYPE_TICKET = 'ticket';
	const TYPE_USER = 'user';
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

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']); //Introduced in the patch
		$this->type = $data['_attributes']['type'];
		$this->note_color = intval($data['_attributes']['notecolor']);
		$this->creator_staff_id = intval($data['_attributes']['creatorstaffid']);
		$this->creator_staff_name = $data['_attributes']['creatorstaffname'];
		$this->for_staff_id = intval($data['_attributes']['forstaffid']);
		$this->creation_date = intval($data['_attributes']['creationdate']) > 0 ? date(self::$datetime_format, $data['_attributes']['creationdate']) : null;
		$this->contents = $data['_contents'];

		/*
		 * Introduced in the patch.
		 */
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
	 * @return kyTicketNote[]
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

	public function getId($complete = false) {
		return $complete ? array($this->ticket_id, $this->id) : $this->id;
	}

	/**
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
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getUserId() {
		if ($this->getType() !== self::TYPE_USER)
			return null;

		return $this->user_id;
	}

	/**
	 *
	 * @return int
	 */
	public function getUserOrganizationId() {
		if ($this->getType() !== self::TYPE_USER_ORGANIZATION)
			return null;

		return $this->user_organization_id;
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
	 * @return int
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
	 */
	public function getCreationDate() {
		return $this->creation_date;
	}

	/**
	 *
	 * @return string
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