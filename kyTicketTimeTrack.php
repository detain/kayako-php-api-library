<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Kayako TicketTimeTrack object.
 * CAUTION: Time track in API is the same XML element as ticket note. I introduced TimeTrack object because its structure is completely different than note structure.
 *
 * @author Tomasz Sawicki (Tomasz.Sawicki@put.poznan.pl)
 */
class kyTicketTimeTrack extends kyObjectBase {

	const COLOR_YELLOW = 1;
	const COLOR_PURPLE = 2;
	const COLOR_BLUE = 3;
	const COLOR_GREEN = 4;
	const COLOR_RED = 5;

	static protected $read_only = true;

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

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']); //Introduced in the patch
		$this->ticket_id = intval($data['_attributes']['ticketid']); //Introduced in the patch
		$this->time_worked = $data['_attributes']['timeworked'];
		$this->time_billable = $data['_attributes']['timebillable'];
		$this->bill_date = intval($data['_attributes']['billdate']) > 0 ? date(self::$datetime_format, $data['_attributes']['billdate']) : null;
		$this->work_date = intval($data['_attributes']['workdate']) > 0 ? date(self::$datetime_format, $data['_attributes']['workdate']) : null;
		$this->worker_staff_id = intval($data['_attributes']['workerstaffid']);
		$this->worker_staff_name = $data['_attributes']['workerstaffname'];
		$this->creator_staff_id = intval($data['_attributes']['creatorstaffid']);
		$this->creator_staff_name = $data['_attributes']['creatorstaffname'];
		$this->note_color = intval($data['_attributes']['notecolor']);
	}

	static public function getAll($ticket_id) {
		throw new Exception("You can't get time tracks this way. Load a ticket in order to get it's time tracks.");
	}

	static public function get($ticket_id, $id) {
		throw new Exception("You can't get time track this way. Load a ticket in order to get it's time tracks.");
	}

	public function refresh() {
		throw new Exception("You can't refresh time track object.");
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
	 * @param bool $formatted True to format result nicely (ex. 02:30:00).
	 * @return int|string
	 */
	public function getTimeWorked($formatted = false) {
		if ($formatted)
			return ky_seconds_format($this->time_worked);
		else
			return $this->time_worked;
	}

	/**
	 *
	 * @param bool $formatted True to format result nicely (ex. 02:30:00).
	 * @return int|string
	 */
	public function getTimeBillable($formatted = false) {
		if ($formatted)
			return ky_seconds_format($this->time_billable);
		else
			return $this->time_billable;
	}

	/**
	 *
	 * @return string
	 */
	public function getBillDate() {
		return $this->bill_date;
	}

	/**
	 *
	 * @return string
	 */
	public function getWorkDate() {
		return $this->work_date;
	}

	/**
	 *
	 * @return int
	 */
	public function getWorkerStaffId() {
		return $this->worker_staff_id;
	}

	/**
	 *
	 * @todo Cache the result in object private field.
	 * @return kyStaff
	 */
	public function getWorkerStaff() {
		if ($this->worker_staff_id === null || $this->worker_staff_id <= 0)
			return null;

		return kyStaff::get($this->worker_staff_id);
	}

	/**
	 *
	 * @return string
	 */
	public function getWorkerStaffName() {
		return $this->worker_staff_name;
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
	 *
	 * @return int
	 */
	public function getNoteColor() {
		return $this->note_color;
	}
}