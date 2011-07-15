<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Kayako TicketTimeTrack object.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketTimeTrack
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicketTimeTrack extends kyObjectBase {

	/**
	 * Color of time track - yellow.
	 *
	 * @var int
	 */
	const COLOR_YELLOW = 1;

	/**
	 * Color of time track - purple.
	 *
	 * @var int
	 */
	const COLOR_PURPLE = 2;

	/**
	 * Color of time track - blue.
	 *
	 * @var int
	 */
	const COLOR_BLUE = 3;

	/**
	 * Color of time track - green.
	 *
	 * @var int
	 */
	const COLOR_GREEN = 4;

	/**
	 * Color of time track - red.
	 *
	 * @var int
	 */
	const COLOR_RED = 5;

	static protected $controller = '/Tickets/TicketTimeTrack';
	static protected $object_xml_name = 'timetrack';

	private $id = null;
	private $ticket_id = null;
	private $time_worked = null;
	private $time_billable = null;
	private $bill_date = null;
	private $work_date = null;
	private $worker_staff_id = null;
	private $worker_staff_name = null;
	private $creator_staff_id = null;
	private $creator_staff_name = null;
	private $note_color = null;
	private $contents = null;

	private $worker_staff = null;
	private $creator_staff = null;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->ticket_id = intval($data['_attributes']['ticketid']);
		$this->time_worked = $data['_attributes']['timeworked'];
		$this->time_billable = $data['_attributes']['timebillable'];
		$this->bill_date = intval($data['_attributes']['billdate']) > 0 ? date(self::$datetime_format, $data['_attributes']['billdate']) : null;
		$this->work_date = intval($data['_attributes']['workdate']) > 0 ? date(self::$datetime_format, $data['_attributes']['workdate']) : null;
		$this->worker_staff_id = intval($data['_attributes']['workerstaffid']);
		$this->worker_staff_name = $data['_attributes']['workerstaffname'];
		$this->creator_staff_id = intval($data['_attributes']['creatorstaffid']);
		$this->creator_staff_name = $data['_attributes']['creatorstaffname'];
		$this->note_color = intval($data['_attributes']['notecolor']);
		$this->contents = $data['_contents'];
	}

	protected function buildData($method) {
		$data = array();

		//TODO: check if required parameters are present

		$data['ticketid'] = $this->ticket_id;
		$data['contents'] = $this->contents;
		$data['staffid'] = $this->creator_staff_id;
		$data['worktimeline'] = strtotime($this->work_date);
		$data['billtimeline'] = strtotime($this->bill_date);
		$data['timespent'] = $this->time_worked;
		$data['timebillable'] = $this->time_billable;
		if (is_numeric($this->worker_staff_id))
			$data['workerstaffid'] = $this->worker_staff_id;
		$data['notecolor'] = $this->note_color;

		return $data;
	}

	/**
	 * Returns all time tracks of the ticket.
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
	 * Returns ticket time track.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param int $id Ticket time track identifier.
	 * @return kyTicketTimeTrack
	 */
	static public function get($ticket_id, $id) {
		return parent::get(array($ticket_id, $id));
	}

	public function update() {
		throw new Exception("You can't update objects of type kyTicketTimeTrack.");
	}

	public function toString() {
		return sprintf("%s (worker: %s)", substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''), $this->getWorkerStaffName());
	}

	public function getId($complete = false) {
		return $complete ? array($this->ticket_id, $this->id) : $this->id;
	}

	/**
	 * Returns ticket identifier of this time track.
	 *
	 * @return int
	 */
	public function getTicketId() {
		return $this->ticket_id;
	}

	/**
	 * Sets ticket identifier of the time track.
	 *
	 * @param int $ticket_id Ticket identifier of the time track
	 * @return kyTicketTimeTrack
	 */
	public function setTicketId($ticket_id) {
		$this->ticket_id = $ticket_id;
		return $this;
	}

	/**
	 * Returns time worked for this time track.
	 *
	 * @param bool $formatted True to format result nicely (ex. 02:30:00). False to return amount of seconds.
	 * @return int|string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getTimeWorked($formatted = false) {
		if ($formatted)
			return ky_seconds_format($this->time_worked);
		else
			return $this->time_worked;
	}

	/**
	 * Sets worked time for this time track.
	 *
	 * @param mixed $time_worked Worked time in seconds or formatted as hh:mm.
	 * @param bool $formatted True to indicate that time is formatted as hh:mm. False to indicate that time is provided in seconds.
	 * @return kyTicketTimeTrack
	 */
	public function setTimeWorked($time_worked, $formatted = false) {
		if ($formatted) {
			list($hours, $minutes) = explode(':', $time_worked);
			$time_worked = (60 * 60 * $hours) + (60 * $minutes);
		}
		$this->time_worked = $time_worked;
		return $this;
	}

	/**
	 * Returns billable time for this time track.
	 *
	 * @param bool $formatted True to format result nicely (ex. 02:30:00). False to return amount of seconds.
	 * @return int|string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getTimeBillable($formatted = false) {
		if ($formatted)
			return ky_seconds_format($this->time_billable);
		else
			return $this->time_billable;
	}

	/**
	 * Sets billable time for this time track.
	 *
	 * @param mixed $time_billable Billable time in seconds or formatted as hh:mm.
	 * @param bool $formatted True to indicate that time is formatted as hh:mm. False to indicate that time is provided in seconds.
	 * @return kyTicketTimeTrack
	 */
	public function setTimeBillable($time_billable, $formatted = false) {
		if ($formatted) {
			list($hours, $minutes) = explode(':', $time_billable);
			$time_billable = (60 * 60 * $hours) + (60 * $minutes);
		}
		$this->time_billable = $time_billable;
		return $this;
	}

	/**
	 * Returns date and time when the work was executed.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getWorkDate() {
		return $this->work_date;
	}

	/**
	 * Sets date and time when the work was executed.
	 *
	 * @param int $work_date Date and time when the work was executed.
	 * @return kyTicketTimeTrack
	 */
	public function setWorkDate($work_date) {
		$this->work_date = $work_date;
		return $this;
	}

	/**
	 * Returns date and time when to bill the worker.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getBillDate() {
		return $this->bill_date;
	}

	/**
	 * Sets date and time when to bill the worker.
	 *
	 * @param int $bill_date Date and time when to bill the worker.
	 * @return kyTicketTimeTrack
	 */
	public function setBillDate($bill_date) {
		$this->bill_date = $bill_date;
		return $this;
	}

	/**
	 * Shortcut function for setting worked time and when the work was executed.
	 *
	 * @param string $time_worked Worked time formatted as hh:mm.
	 * @param string $work_date When the work was exectued. Default is the current datetime.
	 */
	public function setWorkedData($time_worked, $work_date = null) {
		$this->setTimeWorked($time_worked, true);
		if ($work_date === null)
			$work_date = date(kyBase::$datetime_format);
		$this->setWorkDate($work_date);
	}

	/**
	 * Shortcut function for setting billable time and when to bill the worker.
	 *
	 * @param string $time_billable Billable time formatted as hh:mm.
	 * @param string $bill_date When to bill the worker. Default is the current datetime.
	 */
	public function setBillingData($time_billable, $bill_date = null) {
		$this->setTimeBillable($time_billable, true);
		if ($bill_date === null)
			$bill_date = date(kyBase::$datetime_format);
		$this->setBillDate($bill_date);
	}

	/**
	 * Returns identifier of staff user that has done the work.
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getWorkerStaffId() {
		return $this->worker_staff_id;
	}

	/**
	 * Sets the identifier of staff user that has done the work.
	 * Invalidates worker staff cache.
	 *
	 * @param int $worker_staff_id Identifier of staff user that has done the work.
	 * @return kyTicketTimeTrack
	 */
	public function setWorkerStaffId($worker_staff_id) {
		$this->worker_staff_id = $worker_staff_id;
		$this->worker_staff = null;
		return $this;
	}

	/**
	 * Returns staff user object that has done the work.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyStaff
	 */
	public function getWorkerStaff($reload = false) {
		if ($this->worker_staff !== null && !$reload)
			return $this->worker_staff;

		if ($this->worker_staff_id === null || $this->worker_staff_id <= 0)
			return null;

		$this->worker_staff = kyStaff::get($this->worker_staff_id);
		return $this->worker_staff;
	}

	/**
	 * Sets staff user that has done the work.
	 *
	 * @param kyStaff $worker_staff Staff user that has done the work.
	 * @return kyTicketTimeTrack
	 */
	public function setWorkerStaff(kyStaff $worker_staff) {
		if ($worker_staff === null)
			return;

		$this->worker_staff_id = $worker_staff->getId();
		$this->worker_staff = $worker_staff;
		$this->worker_staff_name = $worker_staff->getFullName();
		return $this;
	}

	/**
	 * Returns full name of staff user that has done the work.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getWorkerStaffName() {
		return $this->worker_staff_name;
	}

	/**
	 * Returns identifier of staff user that created the time track.
	 *
	 * @return int
	 * @filterBy()
	 * @orderBy()
	 */
	public function getCreatorStaffId() {
		return $this->creator_staff_id;
	}


	/**
	 * Sets the identifier of staff user that creates the time track.
	 * Invalidates creator staff cache.
	 *
	 * @param int $creator_staff_id Identifier of staff user that creates the time track.
	 * @return kyTicketTimeTrack
	 */
	public function setCreatorStaffId($creator_staff_id) {
		$this->creator_staff_id = $creator_staff_id;
		$this->creator_staff = null;
		return $this;
	}

	/**
	 * Returns staff user that creates the time track.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyStaff
	 */
	public function getCreatorStaff($reload = false) {
		if ($this->creator_staff !== null && !$reload)
			return $this->creator_staff;

		if ($this->creator_staff_id === null || $this->creator_staff_id <= 0)
			return null;

		$this->creator_staff = kyStaff::get($this->creator_staff_id);
		return $this->creator_staff;
	}

	/**
	 * Sets staff user that creates the time track.
	 *
	 * @param kyStaff $creator_staff Staff user that creates the time track.
	 * @return kyTicketTimeTrack
	 */
	public function setCreatorStaff(kyStaff $creator_staff) {
		if ($creator_staff === null)
			return;

		$this->creator_staff_id = $creator_staff->getId();
		$this->creator_staff = $creator_staff;
		$this->creator_staff_name = $creator_staff->getFullName();
		return $this;
	}

	/**
	 * Returns full name of staff user that created the time track.
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getCreatorStaffName() {
		return $this->creator_staff_name;
	}

	/**
	 * Returns color of the time track - one of kyTicketTimeTrack::COLOR_* constants.
	 *
	 * @return int
	 */
	public function getNoteColor() {
		return $this->note_color;
	}

	/**
	 * Sets color of the time track.
	 *
	 * @param int $note_color Color of the time track - one of kyTicketTimeTrack::COLOR_* constants.
	 * @return kyTicketTimeTrack
	 */
	public function setNoteColor($note_color) {
		$this->note_color = $note_color;
		return $this;
	}

	/**
	 * Returns contents of the time track.
	 *
	 * @return string
	 * @filterBy()
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of the time track.
	 *
	 * @param string $contents Contents of the time track.
	 * @return kyTicketTimeTrack
	 */
	public function setContents($contents) {
		$this->contents = $contents;
		return $this;
	}

	/**
	 * Creates new ticket time track.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTicket $ticket Ticket to attach the timetrack to.
	 * @param string $contents Note contents.
	 * @param kyStaff $staff Staff user - both creator and worker.
	 * @param string $time_worked Worked time formatted as hh:mm. Work date will be set to current datetime.
	 * @param string $time_billable Billable time formatted as hh:mm. Bill date will be set to current datetime.
	 * @return kyTicketTimeTrack
	 */
	static public function createNew(kyTicket $ticket, $contents, kyStaff $staff, $time_worked, $time_billable) {
		$ticket_time_track = new self();
		$ticket_time_track->setTicketId($ticket->getId());
		$ticket_time_track->setContents($contents);
		$ticket_time_track->setCreatorStaff($staff);
		$ticket_time_track->setWorkerStaff($staff);
		$ticket_time_track->setBillingData($time_billable);
		$ticket_time_track->setWorkedData($time_worked);
		return $ticket_time_track;
	}
}