<?php
require_once('kySearchBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 *
 * Helper for searching Kayako tickets.
 * EXPERIMENTAL.
 *
 * @link http://wiki.kayako.com/display/DEV/REST+-+Ticket
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kySearchTicket extends kySearchBase {

	static protected $controller = '/Tickets/Ticket/ListAll';
	static protected $object_class_name = 'kyTicket';
	static protected $object_xml_name = 'ticket';

	private $department_ids = array();
	private $ticket_status_ids = array();
	private $owner_staff_ids = array();
	private $user_ids = array();

	protected function buildParameters() {
		$parameters = array();

		//department
		if (count($this->department_ids) === 0)
			throw new Exception('You must provide at least one department to search for tickets.');
		$parameters[] = implode(',', $this->department_ids);

		//ticket status
		if (count($this->ticket_status_ids) > 0)
			$parameters[] = implode(',', $this->ticket_status_ids);
		else
			$parameters[] = '-1';

		//owner staff
		if (count($this->owner_staff_ids) > 0)
			$parameters[] = implode(',', $this->owner_staff_ids);
		else
			$parameters[] = '-1';

		//user
		if (count($this->user_ids) > 0)
			$parameters[] = implode(',', $this->user_ids);
		else
			$parameters[] = '-1';

		return $parameters;
	}

	/**
	 *
	 * @param int $department_id
	 * @param bool $clear Clear the list before adding.
	 * @return kySearchTicket
	 */
	public function addDepartmentId($department_id, $clear = false) {
		if (is_numeric($department_id)) {
			if ($clear)
				$this->department_ids = array();

			$this->department_ids[] = $department_id;
		}
		return $this;
	}

	/**
	 *
	 * @param int $ticket_status_id
	 * @param bool $clear Clear the list before adding.
	 * @return kySearchTicket
	 */
	public function addTicketStatusId($ticket_status_id, $clear = false) {
		if (is_numeric($ticket_status_id)) {
			if ($clear)
				$this->ticket_status_ids = array();

			$this->ticket_status_ids[] = $ticket_status_id;
		}
		return $this;
	}

	/**
	 *
	 * @param int $owner_staff_id
	 * @param bool $clear Clear the list before adding.
	 * @return kySearchTicket
	 */
	public function addOwnerStaffId($owner_staff_id, $clear = false) {
		if (is_numeric($owner_staff_id)) {
			if ($clear)
				$this->owner_staff_ids = array();

			$this->owner_staff_ids[] = $owner_staff_id;
		}
		return $this;
	}

	/**
	 *
	 * @param int $user_id
	 * @param bool $clear Clear the list before adding.
	 * @return kySearchTicket
	 */
	public function addUserId($user_id, $clear = false) {
		if (is_numeric($user_id)) {
			if ($clear)
				$this->user_ids = array();

			$this->user_ids[] = $user_id;
		}
		return $this;
	}
}