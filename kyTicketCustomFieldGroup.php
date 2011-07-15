<?php
require_once('kyCustomFieldGroupBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.240.
 *
 * Kayako TicketCustomField object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicketCustomFieldGroup extends kyCustomFieldGroupBase {

	static protected $controller = '/Tickets/TicketCustomField';

	private $ticket_id;

	function __construct($ticket_id, $data = null) {
		parent::__construct($data);
		$this->ticket_id = $ticket_id;
	}

	/**
	 * Fetches ticket custom fields groups from server.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @return kyResultSet
	 */
	static public function getAll($ticket_id) {
		$result = static::_get(array($ticket_id));
		$objects = array();
		if (array_key_exists(static::$object_xml_name, $result)) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new static($ticket_id, $object_data);
			}
		}
		return new kyResultSet($objects);
	}

	/**
	 * Returns ticket identifier, this group is associated with.
	 *
	 * @return int
	 */
	public function getTicketId() {
		return $this->ticket_id;
	}
}