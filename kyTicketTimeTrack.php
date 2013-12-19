<?php
/**
 * Kayako TicketTimeTrack object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketTimeTrack
 * @since Kayako version 4.01.240
 * @package Object\Ticket
 *
 * @noinspection PhpDocSignatureInspection
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

	/**
	 * Ticket time track identifier.
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
	 * Time worked (in seconds) in this ticket time track.
	 * @apiField required_create=true alias=timespent
	 * @var int
	 */
	protected $time_worked;

	/**
	 * Billable time (in seconds) in this ticket time track.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $time_billable;

	/**
	 * Bill timestamp of this ticket time track.
	 * @apiField required_create=true alias=billtimeline
	 * @var int
	 */
	protected $bill_date;

	/**
	 * Work timestamp of this ticket time track.
	 * @apiField required_create=true alias=worktimeline
	 * @var int
	 */
	protected $work_date;

	/**
	 * Worker staff identifier.
	 * @apiField
	 * @var int
	 */
	protected $worker_staff_id;

	/**
	 * Worker staff full name.
	 * @apiField
	 * @var string
	 */
	protected $worker_staff_name;

	/**
	 * Creator staff identifier.
	 * @apiField required_create=true alias=staffid
	 * @var int
	 */
	protected $creator_staff_id;

	/**
	 * Creator staff full name.
	 * @apiField
	 * @var string
	 */
	protected $creator_staff_name;

	/**
	 * Ticket time track note color.
	 *
	 * @see kyTicketTimeTrack::COLOR constants.
	 *
	 * @apiField
	 * @var int
	 */
	protected $note_color;

	/**
	 * Note contents of this ticket time track.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $contents;

	/**
	 * The ticket that this time track will be connected with.
	 * @var kyTicket
	 */
	private $ticket;

	/**
	 * Worker staff.
	 * @var kyStaff
	 */
	private $worker_staff = null;

	/**
	 * Creator staff.
	 * @var kyStaff
	 */
	private $creator_staff = null;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->ticket_id = ky_assure_positive_int($data['_attributes']['ticketid']);
		$this->time_worked = $data['_attributes']['timeworked'];
		$this->time_billable = $data['_attributes']['timebillable'];
		$this->bill_date = ky_assure_positive_int($data['_attributes']['billdate']);
		$this->work_date = ky_assure_positive_int($data['_attributes']['workdate']);
		$this->worker_staff_id = ky_assure_positive_int($data['_attributes']['workerstaffid']);
		$this->worker_staff_name = $data['_attributes']['workerstaffname'];
		$this->creator_staff_id = ky_assure_positive_int($data['_attributes']['creatorstaffid']);
		$this->creator_staff_name = $data['_attributes']['creatorstaffname'];
		$this->note_color = intval($data['_attributes']['notecolor']);
		$this->contents = $data['_contents'];
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['ticketid'] = $this->ticket_id;
		$data['contents'] = $this->contents;
		$data['staffid'] = $this->creator_staff_id;
		$data['worktimeline'] = $this->work_date;
		$data['billtimeline'] = $this->bill_date;
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
		throw new BadMethodCallException("You can't update objects of type kyTicketTimeTrack.");
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
		$this->ticket_id = ky_assure_positive_int($ticket_id);
		$this->ticket = null;
		return $this;
	}

/**
	 * Returns the ticket that this time track is connected with.
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
	 * Sets the ticket that this time track will be connected with.
	 *
	 * @param kyTicket $ticket Ticket.
	 * @return kyTicketTimeTrack
	 */
	public function setTicket($ticket) {
		$this->ticket = ky_assure_object($ticket, 'kyTicket');
		$this->ticket_id = $this->ticket !== null ? $this->ticket->getId() : null;
		return $this;
	}

	/**
	 * Returns time worked for this time track.
	 *
	 * @param bool $formatted True to format result nicely (ex. 02:30:00). False to return amount of seconds.
	 * @return int|string
	 * @filterBy
	 * @orderBy
	 */
	public function getTimeWorked($formatted = false) {
		if ($formatted) {
			return ky_seconds_format($this->time_worked);
		}

		return $this->time_worked;
	}

	/**
	 * Sets worked time for this time track.
	 *
	 * @param string|int $time_worked Worked time (as seconds or formatted as hh:mm).
	 * @return kyTicketTimeTrack
	 */
	public function setTimeWorked($time_worked) {
		if (!is_numeric($time_worked)) {
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
	 * @filterBy
	 * @orderBy
	 */
	public function getTimeBillable($formatted = false) {
		if ($formatted) {
			return ky_seconds_format($this->time_billable);
		}

		return $this->time_billable;
	}

	/**
	 * Sets billable time for this time track.
	 *
	 * @param string|int $time_billable Billable time (as seconds or formatted as hh:mm).
	 * @return kyTicketTimeTrack
	 */
	public function setTimeBillable($time_billable) {
		if (!is_numeric($time_billable)) {
			list($hours, $minutes) = explode(':', $time_billable);
			$time_billable = (60 * 60 * $hours) + (60 * $minutes);
		}
		$this->time_billable = $time_billable;
		return $this;
	}

	/**
	 * Returns date and time when the work was executed.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getWorkDate($format = null) {
		if ($this->work_date == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->work_date);
	}

	/**
	 * Sets date and time when the work was executed.
	 *
	 * @see http://www.php.net/manual/en/function.strtotime.php
	 *
	 * @param string|int $work_date Date and time when the work was executed (timestamp or string format understood by PHP strtotime).
	 * @return kyTicketTimeTrack
	 */
	public function setWorkDate($work_date) {
		$this->work_date = is_numeric($work_date) ? $work_date : strtotime($work_date);
		return $this;
	}

	/**
	 * Returns date and time when to bill the worker.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getBillDate($format = null) {
		if ($this->bill_date == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->bill_date);
	}

	/**
	 * Sets date and time when to bill the worker.
	 *
	 * @see http://www.php.net/manual/en/function.strtotime.php
	 *
	 * @param string|int $bill_date Date and time when the work was executed (timestamp or string format understood by PHP strtotime).
	 * @return kyTicketTimeTrack
	 */
	public function setBillDate($bill_date) {
		$this->bill_date = is_numeric($bill_date) ? $bill_date : strtotime($bill_date);
		return $this;
	}

	/**
	 * Shortcut function for setting worked time and when the work was executed.
	 *
	 * @see http://www.php.net/manual/en/function.strtotime.php
	 *
	 * @param string|int $time_worked Worked time (as seconds or formatted as hh:mm).
	 * @param string $work_date Date and time when the work was exectued (timestamp or string format understood by PHP strtotime). Defaults to current datetime.
	 */
	public function setWorkedData($time_worked, $work_date = null) {
		$this->setTimeWorked($time_worked, true);
		if ($work_date === null) {
			$work_date = time();
		}
		$this->setWorkDate($work_date);
	}

	/**
	 * Shortcut function for setting billable time and when to bill the worker.
	 *
	 * @see http://www.php.net/manual/en/function.strtotime.php
	 *
	 * @param string|int $time_billable Billable time (as seconds or formatted as hh:mm).
	 * @param string $bill_date Date and time when to bill the worker (timestamp or string format understood by PHP strtotime). Defaults to current datetime.
	 */
	public function setBillingData($time_billable, $bill_date = null) {
		$this->setTimeBillable($time_billable);
		if ($bill_date === null) {
			$bill_date = time();
		}
		$this->setBillDate($bill_date);
	}

	/**
	 * Returns identifier of staff user that has done the work.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getWorkerStaffId() {
		return $this->worker_staff_id;
	}

	/**
	 * Sets the identifier of staff user that has done the work.
	 *
	 * @param int $worker_staff_id Identifier of staff user that has done the work.
	 * @return kyTicketTimeTrack
	 */
	public function setWorkerStaffId($worker_staff_id) {
		$this->worker_staff_id = ky_assure_positive_int($worker_staff_id);
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
	public function setWorkerStaff($worker_staff) {
		$this->worker_staff = ky_assure_object($worker_staff, 'kyStaff');
		$this->worker_staff_id = $this->worker_staff !== null ? $this->worker_staff->getId() : null;
		$this->worker_staff_name = $this->worker_staff !== null ? $this->worker_staff->getFullName() : null;
		return $this;
	}

	/**
	 * Returns full name of staff user that has done the work.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getWorkerStaffName() {
		return $this->worker_staff_name;
	}

	/**
	 * Returns identifier of staff user that created the time track.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorStaffId() {
		return $this->creator_staff_id;
	}


	/**
	 * Sets the identifier of staff user that creates the time track.
	 *
	 * @param int $creator_staff_id Identifier of staff user that creates the time track.
	 * @return kyTicketTimeTrack
	 */
	public function setCreatorStaffId($creator_staff_id) {
		$this->creator_staff_id = ky_assure_positive_int($creator_staff_id);
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
	public function setCreatorStaff($creator_staff) {
		$this->creator_staff = ky_assure_object($creator_staff, 'kyStaff');
		$this->creator_staff_id = $this->creator_staff !== null ? $this->creator_staff->getId() : null;
		$this->creator_staff_name = $this->creator_staff !== null ? $this->creator_staff->getFullName() : null;
		return $this;
	}

	/**
	 * Returns full name of staff user that created the time track.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorStaffName() {
		return $this->creator_staff_name;
	}

	/**
	 * Returns color of the time track - one of kyTicketTimeTrack::COLOR_* constants.
	 *
	 * @see kyTicketTimeTrack::COLOR constants.
	 *
	 * @return int
	 */
	public function getNoteColor() {
		return $this->note_color;
	}

	/**
	 * Sets color of the time track.
	 *
	 * @see kyTicketTimeTrack::COLOR constants.
	 *
	 * @param int $note_color Color of the time track - one of kyTicketTimeTrack::COLOR_* constants.
	 * @return kyTicketTimeTrack
	 */
	public function setNoteColor($note_color) {
		$this->note_color = ky_assure_constant($note_color, $this, 'COLOR');
		return $this;
	}

	/**
	 * Returns contents of the time track.
	 *
	 * @return string
	 * @filterBy
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
		$this->contents = ky_assure_string($contents);
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