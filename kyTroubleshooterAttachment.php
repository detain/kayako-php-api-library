<?php
/**
 * Kayako TroubleshooterAttachment object.
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TroubleshooterCategory
 * @since Kayako version 4.64.1
 * @package Object\TroubleshooterCategory
 *
 */
class kyTroubleshooterAttachment extends kyObjectBase {
	static protected $controller = '/Troubleshooter/Attachment';
	static protected $object_xml_name = 'troubleshooterattachment';

	/**
	 * TroubleshooterStep attachment identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Identifier of TroubleshooterStep that this attachment is attached to.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $troubleshooter_step_id;

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

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->troubleshooter_step_id = ky_assure_positive_int($data['troubleshooterstepid']);
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

		$data['troubleshooterstepid'] = $this->troubleshooter_step_id;
		$data['filename'] = $this->file_name;
		$data['contents'] = $this->contents;

		return $data;
	}

	/**
	 * Returns all attachments of the TroubleshooterStep
	 *
	 * @param int $troubelshooter_step_id TroubleshooterStep identifier.
	 * @return kyResultSet
	 */
	static public function getAll($troubelshooter_step_id) {
		$search_parameters = array('ListAll');

		$search_parameters[] = $troubelshooter_step_id;

		return parent::getAll($search_parameters);
	}

	/**
	 * Returns TroubleshooterStep attachment.
	 *
	 * @param int $troubelshooter_step_id TroubleshooterStep identifier.
	 * @param int $id TroubleshooterStep attachment identifier.
	 * @return kyTroubleshooterAttachment
	 */
	static public function get($troubelshooter_step_id, $id) {
		return parent::get(array($troubelshooter_step_id, $id));
	}

	public function update() {
		throw new BadMethodCallException("You can't update objects of type kyTroubleshooterAttachment.");
	}

	public function delete() {
		self::getRESTClient()->delete(static::$controller, array($this->troubleshooter_step_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (filetype: %s, filesize: %s)", $this->getFileName(), $this->getFileType(), $this->getFileSize(true));
	}

	public function getId($complete = false) {
		return $complete ? array($this->troubleshooter_step_id, $this->id) : $this->id;
	}

	/**
	 * Returns identifier of the TroubleshooterStep this attachment belongs to.
	 *
	 * @return int
	 */
	public function getTroubleshooterStepId() {
		return $this->troubleshooter_step_id;
	}

	/**
	 * Sets identifier of the TroubleshooterStep this attachment will belong to.
	 *
	 * @param int $troubleshooter_step_id TroubleshooterStep identifier.
	 * @return kyTroubleshooterAttachment
	 */
	public function setTroubleshooterStepId($troubleshooter_step_id) {
		$this->troubleshooter_step_id = ky_assure_positive_int($troubleshooter_step_id);
		return $this;
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
	 * @return kyTroubleshooterAttachment
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
		if ($this->contents === null && is_numeric($this->id) && is_numeric($this->troubleshooter_step_id) && $auto_fetch) {
			$attachment = $this->get($this->troubleshooter_step_id, $this->id);
			$this->contents = $attachment->getContents(false);
		}
		return $this->contents;
	}

	/**
	 * Sets raw contents of the attachment (NOT base64 encoded).
	 *
	 * @param string $contents Raw contents of the attachment (NOT base64 encoded).
	 * @return kyTroubleshooterAttachment
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
	 * @return kyTroubleshooterAttachment
	 */
	public function setContentsFromFile($file_path, $file_name = null) {
		$contents = base64_encode(file_get_contents($file_path));
		if ($contents === false)
			throw new kyException(sprintf("Error reading contents of %s.", $file_path));

		$this->contents =& $contents;
		if ($file_name === null)
			$file_name = basename($file_path);
		$this->file_name = $file_name;
		return $this;
	}

	/**
	 * Creates new attachment for troubleshooterstep with contents provided as parameter.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTroubleshooterStep $troubleshooter_step TroubleshooterStep.
	 * @param string $contents Raw contents of the file.
	 * @param string $file_name Filename.
	 * @return kyTroubleshooterAttachment
	 */
	static public function createNew($troubleshooter_step, $contents, $file_name) {
		$new_troubleshooter_attachment = new kyTroubleshooterAttachment();

		$new_troubleshooter_attachment->setTroubleshooterStepId($troubleshooter_step->getId());
		$new_troubleshooter_attachment->setContents($contents);
		$new_troubleshooter_attachment->setFileName($file_name);

		return $new_troubleshooter_attachment;
	}

	/**
	 * Creates new attachment for troubleshooterstep with contents read from physical file.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTroubleshooterStep $troubleshooter_step troubleshooterstep.
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 * @return kyTroubleshooterAttachment
	 */
	static public function createNewFromFile($troubleshooter_step, $file_path, $file_name = null) {
		$new_troubleshooter_attachment = new kyTroubleshooterAttachment();

		$new_troubleshooter_attachment->setTroubleshooterStepId($troubleshooter_step->getId());
		$new_troubleshooter_attachment->setContentsFromFile($file_path, $file_name);

		return $new_troubleshooter_attachment;
	}

}