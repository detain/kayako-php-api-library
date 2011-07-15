<?php
require_once('kyObjectBase.php');

/**
 * Part of PHP client to REST API of Kayako v4 (Kayako Fusion).
 * Compatible with Kayako version >= 4.01.204.
 *
 * Kayako TicketAttachment object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 */
class kyTicketAttachment extends kyObjectBase {
	static protected $controller = '/Tickets/TicketAttachment';
	static protected $object_xml_name = 'attachment';

	private $id = null;
	private $ticket_id = null;
	private $ticket_post_id = null;
	private $file_name = null;
	private $file_size = null;
	private $file_type = null;
	private $dateline = null;
	private $contents = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->ticket_id = intval($data['ticketid']);
		$this->ticket_post_id = intval($data['ticketpostid']);
		$this->file_name = $data['filename'];
		$this->file_size = intval($data['filesize']);
		$this->file_type = $data['filetype'];
		$this->dateline = intval($data['dateline']) > 0 ? date(self::$datetime_format, $data['dateline']) : null;
		if (array_key_exists('contents', $data) && strlen($data['contents']) > 0)
			$this->contents = base64_decode($data['contents']);
	}

	protected function buildData($method) {
		$data = array();

		$data['ticketid'] = $this->ticket_id;
		$data['ticketpostid'] = $this->ticket_post_id;
		$data['filename'] = $this->file_name;
		$data['contents'] = base64_encode($this->contents);

		return $data;
	}

	/**
	 * Returns all attachments in posts of the ticket.
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
	 * Returns ticket (post) attachment.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param int $id Ticket attachement identifier.
	 * @return kyTicketAttachment
	 */
	static public function get($ticket_id, $id) {
		return parent::get(array($ticket_id, $id));
	}

	public function update() {
		throw new Exception("You can't update objects of type kyTicketAttachment.");
	}

	public function delete() {
		static::_delete(array($this->ticket_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (filetype: %s, filesize: %s)", $this->getFileName(), $this->getFileType(), $this->getFileSize(true));
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
	 * @return kyTicketAttachment
	 */
	public function setTicketId($ticket_id) {
		$this->ticket_id = $ticket_id;
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getTicketPostId() {
		return $this->ticket_post_id;
	}

	/**
	 *
	 * @param int $ticket_post_id
	 * @return kyTicketAttachment
	 */
	public function setTicketPostId($ticket_post_id) {
		$this->ticket_post_id = $ticket_post_id;
		return $this;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getFileName() {
		return $this->file_name;
	}

	/**
	 *
	 * @param string $file_name
	 * @return kyTicketAttachment
	 */
	public function setFileName($file_name) {
		$this->file_name = $file_name;
		return $this;
	}

	/**
	 *
	 * @param bool $formatted True to format result nicely (KB, MB, and so on).
	 * @return mixed
	 * @filterBy()
	 * @orderBy()
	 */
	public function getFileSize($formatted = false) {
		if ($formatted)
			return ky_bytes_format($this->file_size);
		else
			return $this->file_size;
	}

	/**
	 *
	 * @return string
	 * @filterBy()
	 * @orderBy()
	 */
	public function getFileType() {
		return $this->file_type;
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
	 * Return raw contents of the attachment (base64 decoded).
	 *
	 * @param bool $auto_fetch True to automatically fetch the contents of the attachment if not present.
	 * @return string
	 */
	public function &getContents($auto_fetch = true) {
		if ($this->contents === null && is_numeric($this->id) && is_numeric($this->ticket_id) && $auto_fetch) {
			$attachment = $this->get($this->ticket_id, $this->id);
			$this->contents = $attachment->getContents(false);
		}
		return $this->contents;
	}

	/**
	 * Sets raw contents of the attachment (base64 decoded).
	 *
	 * @param string $contents Raw contents of the attachment (base64 decoded).
	 * @return kyTicketAttachment
	 */
	public function setContents(&$contents) {
		$this->contents =& $contents;
		return $this;
	}

	/**
	 * Sets contents of the attachment by reading it from a physical file.
	 *
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 * @return kyTicketAttachment
	 */
	public function setContentsFromFile($file_path, $file_name = null) {
		$this->contents = file_get_contents($file_path);
		if ($file_name === null)
			$file_name = pathinfo($file_path, PATHINFO_BASENAME);
		$this->file_name = $file_name;
		return $this;
	}

	/**
	 * Creates new attachment for ticket post with contents provided as parameter.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTicketPost $ticket_post Ticket post.
	 * @param string $contents Raw contents of the file.
	 * @param string $file_name Filename.
	 * @return kyTicketAttachment
	 */
	static public function createNew($ticket_post, $contents, $file_name) {
		$new_ticket_attachment = new kyTicketAttachment();

		$new_ticket_attachment->setTicketId($ticket_post->getTicketId());
		$new_ticket_attachment->setTicketPostId($ticket_post->getId());
		$new_ticket_attachment->setContents($contents);
		$new_ticket_attachment->setFileName($file_name);

		return $new_ticket_attachment;
	}

	/**
	 * Creates new attachment for ticket post with contents read from physical file.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTicketPost $ticket_post Ticket post.
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 * @return kyTicketAttachment
	 */
	static public function createNewFromFile($ticket_post, $file_path, $file_name = null) {
		$new_ticket_attachment = new kyTicketAttachment();

		$new_ticket_attachment->setTicketId($ticket_post->getTicketId());
		$new_ticket_attachment->setTicketPostId($ticket_post->getId());
		$new_ticket_attachment->setContentsFromFile($file_path, $file_name);

		return $new_ticket_attachment;
	}
}