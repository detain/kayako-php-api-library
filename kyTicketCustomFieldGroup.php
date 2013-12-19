<?php
/**
 * Kayako TicketCustomField object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketCustomField
 * @since Kayako version 4.01.220
 * @package Object\Ticket
 */
class kyTicketCustomFieldGroup extends kyCustomFieldGroupBase {

	static protected $controller = '/Tickets/TicketCustomField';

	/**
	 * Ticket identifier.
	 * @var int
	 */
	protected $ticket_id;

	/**
	 * Constructor.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param array|null $data Object data from XML response converted into array.
	 */
	function __construct($ticket_id, $data = null) {
		parent::__construct($data);
		$this->type = kyCustomFieldGroupBase::TYPE_TICKET;
		$this->ticket_id = $ticket_id;
	}

	/**
	 * Fetches ticket custom fields groups from server.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @return kyResultSet
	 */
	static public function getAll($ticket_id) {
		$result = self::getRESTClient()->get(static::$controller, array($ticket_id));
		$objects = array();
		if (array_key_exists(static::$object_xml_name, $result)) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new static($ticket_id, $object_data);
			}
		}
		return new kyResultSet($objects);
	}

	/**
	 * Returns identifier of the ticket that this group is associated with.
	 *
	 * @return int
	 */
	public function getTicketId() {
		return $this->ticket_id;
	}
}