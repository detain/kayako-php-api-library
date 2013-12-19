<?php
/**
 * Kayako TicketAttachment object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketAttachment
 * @since Kayako version 4.01.240
 * @package Object\Ticket
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyTicketAttachment extends kyObjectBase {
	static protected $controller = '/Tickets/TicketAttachment';
	static protected $object_xml_name = 'attachment';

	/**
	 * Ticket attachment identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Identifier of ticket that this attachment is attached to.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $ticket_id;

	/**
	 * Identifier of ticket post that this attachment is attached to.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $ticket_post_id;

	/**
	 * Attachment file name.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $file_name;

	/**
	 * Attachment size in bytes.
	 * @apiField
	 * @var int
	 */
	protected $file_size;

	/**
	 * Attachment MIME type.
	 * @apiField
	 * @var string
	 */
	protected $file_type;

	/**
	 * Timestamp of when this attachment was created.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	/**
	 * Raw contents of attachment.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	/**
	 * Ticket with this attachment.
	 * @var kyTicket
	 */
	private $ticket = null;

	/**
	 * Ticket post with this attachment.
	 * @var kyTicketPost
	 */
	private $ticket_post = null;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->ticket_id = ky_assure_positive_int($data['ticketid']);
		$this->ticket_post_id = ky_assure_positive_int($data['ticketpostid']);
		$this->file_name = $data['filename'];
		$this->file_size = intval($data['filesize']);
		$this->file_type = $data['filetype'];
		$this->dateline = ky_assure_positive_int($data['dateline']);
		if (array_key_exists('contents', $data) && strlen($data['contents']) > 0)
			$this->contents = base64_decode($data['contents']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

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
	 * Returns ticket attachment.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param int $id Ticket attachment identifier.
	 * @return kyTicketAttachment
	 */
	static public function get($ticket_id, $id) {
		return parent::get(array($ticket_id, $id));
	}

	public function update() {
		throw new BadMethodCallException("You can't update objects of type kyTicketAttachment.");
	}

	public function delete() {
		self::getRESTClient()->delete(static::$controller, array($this->ticket_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (filetype: %s, filesize: %s)", $this->getFileName(), $this->getFileType(), $this->getFileSize(true));
	}

	public function getId($complete = false) {
		return $complete ? array($this->ticket_id, $this->id) : $this->id;
	}

	/**
	 * Returns identifier of the ticket this attachment belongs to.
	 *
	 * @return int
	 */
	public function getTicketId() {
		return $this->ticket_id;
	}

	/**
	 * Sets identifier of the ticket this attachment will belong to.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @return kyTicketAttachment
	 */
	public function setTicketId($ticket_id) {
		$this->ticket_id = ky_assure_positive_int($ticket_id);
		$this->ticket = null;
		return $this;
	}

	/**
	 * Returns the ticket this attachment belongs to.
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
	 * Returns identifier of the ticket post this attachment is attached to.
	 * @return int
	 */
	public function getTicketPostId() {
		return $this->ticket_post_id;
	}

	/**
	 * Sets identifier of the ticket post this attachment will be attached to.
	 * @param int $ticket_post_id Ticket post identifier.
	 * @return kyTicketAttachment
	 */
	public function setTicketPostId($ticket_post_id) {
		$this->ticket_post_id = ky_assure_positive_int($ticket_post_id);
		$this->ticket_post = null;
		return $this;
	}

	/**
	 * Returns the ticket post this attachment is attached to.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyTicketPost
	 */
	public function getTicketPost($reload = false) {
		if ($this->ticket_post !== null && !$reload)
			return $this->ticket_post;

		if ($this->ticket_id === null || $this->ticket_post_id === null)
			return null;

		$this->ticket_post = kyTicketPost::get($this->ticket_id, $this->ticket_post_id);
		return $this->ticket_post;
	}

	/**
	 * Sets the ticket post this attachment will be attached to.
	 *
	 * Automatically sets the ticket.
	 *
	 * @param kyTicketPost $ticket_post Ticket post.
	 */
	public function setTicketPost($ticket_post) {
		$this->ticket_post = ky_assure_object($ticket_post, 'kyTicketPost');
		$this->ticket_post_id = $this->ticket_post !== null ? $this->ticket_post->getId() : null;
		$this->ticket = $this->ticket_post !== null ? $this->ticket_post->getTicket() : null;
		$this->ticket_id = $this->ticket !== null ? $this->ticket->getId() : null;
	}

	/**
	 * Returns attachment file name.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFileName() {
		return $this->file_name;
	}

	/**
	 * Sets the attachment file name.
	 *
	 * @param string $file_name File name.
	 * @return kyTicketAttachment
	 */
	public function setFileName($file_name) {
		$this->file_name = ky_assure_string($file_name);
		return $this;
	}

	/**
	 * Returns attachment file size.
	 *
	 * @param bool $formatted True to format result nicely (KB, MB, and so on).
	 * @return mixed
	 * @filterBy
	 * @orderBy
	 */
	public function getFileSize($formatted = false) {
		if ($formatted) {
			return ky_bytes_format($this->file_size);
		}

		return $this->file_size;
	}

	/**
	 * Returns attachment MIME type.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFileType() {
		return $this->file_type;
	}

	/**
	 * Returns date and time of when this attachment was created.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDateline($format = null) {
		if ($this->dateline == null)
			return null;

		if ($format === null) {
			$format = kyConfig::get()->getDatetimeFormat();
		}

		return date($format, $this->dateline);
	}

	/**
	 * Return raw contents of the attachment (NOT base64 encoded).
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
	 * Sets raw contents of the attachment (NOT base64 encoded).
	 *
	 * @param string $contents Raw contents of the attachment (NOT base64 encoded).
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
	 * @throws kyException
	 * @return kyTicketAttachment
	 */
	public function setContentsFromFile($file_path, $file_name = null) {
		$contents = file_get_contents($file_path);
		if ($contents === false)
			throw new kyException(sprintf("Error reading contents of %s.", $file_path));

		$this->contents =& $contents;
		if ($file_name === null)
			$file_name = basename($file_path);
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