<?php
/**
 * Kayako kyKnowledgebaseAttachment object.
 *
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+KnowledgebaseAttachment
 * @since Kayako version 4.64
 * @package Object\Knowledgebase
 *
 */
class kyKnowledgebaseAttachment extends kyObjectBase {

	/**
	 * kbarticle attachment identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * kbarticleid identifier.
	 * @apiField
	 * @var int
	 */
	protected $kbarticle_id;

	/**
	 * kbarticle file name.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $file_name;

	/**
	 * kbarticle size in bytes.
	 * @apiField
	 * @var int
	 */
	protected $file_size;

	/**
	 * kbarticle MIME type.
	 * @apiField
	 * @var string
	 */
	protected $file_type;

	/**
	 * Raw contents of attachment.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	/**
	 * kbarticle with this attachment.
	 * @var kyKnowledgebaseArticle
	 */
	private $kbarticle = null;

	/**
	 * Timestamp of when this attachment was created.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	static protected $controller = '/Knowledgebase/Attachment';
	static protected $object_xml_name = 'kbattachment';

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->kbarticle_id = ky_assure_positive_int($data['kbarticleid']);
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

		$data['kbarticleid'] = $this->kbarticle_id;
		$data['filename'] = $this->file_name;
		$data['contents'] = $this->contents;

		return $data;
	}

	/**
	 * Returns all attachments in kbarticle.
	 *
	 * @param int $kbarticle_id kbarticle identifier.
	 * @return kyResultSet
	 */
	static public function getAll($kbarticle_id) {
		$search_parameters = array('ListAll');

		$search_parameters[] = $kbarticle_id;

		return parent::getAll($search_parameters);
	}

	/**
	 * Returns kbarticle attachment.
	 *
	 * @param int $kbarticle_id kbarticle identifier.
	 * @param int $id kbarticle attachment id identifier.
	 * @return kyKnowledgebaseAttachment
	 */
	static public function get($kbarticle_id, $id) {
		return parent::get(array($kbarticle_id, $id));
	}

	public function update() {
		throw new BadMethodCallException("You can't update objects of type kyKnowledgebaseAttachment.");
	}

	public function delete() {
		self::getRESTClient()->delete(static::$controller, array($this->kbarticle_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (filetype: %s, filesize: %s)", $this->getFileName(), $this->getFileType(), $this->getFileSize(true));
	}

	public function getId($complete = false) {
		return $complete ? array($this->kbarticle_id, $this->id) : $this->id;
	}

	/**
	 * Returns identifier of the kbarticle this attachment belongs to.
	 *
	 * @return int
	 */
	public function getKbarticleId() {
		return $this->kbarticle_id;
	}

	/**
	 * Sets identifier of the kbarticle this attachment will belong to.
	 *
	 * @param int $kbarticle_id kbarticle identifier.
	 * @return kyKnowledgebaseAttachment
	 */
	public function setKbarticleId($kbarticle_id) {
		$this->kbarticle_id = ky_assure_positive_int($kbarticle_id);
		$this->kbarticle = null;
		return $this;
	}

	/**
	 * Returns the kbarticle this attachment belongs to.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyKnowledgebaseArticle
	 */
	public function getKbarticle($reload = false) {
		if ($this->kbarticle !== null && !$reload)
			return $this->kbarticle;

		if ($this->kbarticle_id === null)
			return null;

		$this->kbarticle = kyKnowledgebaseArticle::get($this->kbarticle_id);
		return $this->kbarticle;
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
	 * @return kyKnowledgebaseAttachment
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
		if ($this->contents === null && is_numeric($this->id) && is_numeric($this->kbarticle_id) && $auto_fetch) {
			$attachment = $this->getId($this->kbarticle_id);
			$this->contents = $attachment->getContents(false);
		}
		return $this->contents;
	}

	/**
	 * Sets raw contents of the attachment (NOT base64 encoded).
	 *
	 * @param string $contents Raw contents of the attachment (NOT base64 encoded).
	 * @return kyKnowledgebaseAttachment
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
	 * @return kyKnowledgebaseAttachment
	 */
	public function setContentsFromFile($file_path, $file_name = null) {
		$contents = base64_encode(file_get_contents($file_path));
		if ($contents === false)
			throw new kyException(sprintf("Error reading contents of %s.", $file_path));

		$this->contents = & $contents;
		if ($file_name === null)
			$file_name = basename($file_path);
		$this->file_name = $file_name;
		return $this;
	}

	/**
	 * Creates new attachment for kbarticle with contents provided as parameter.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyKnowledgebaseArticle $kbarticle knowledgebase article.
	 * @param string $contents Raw contents of the file.
	 * @param string $file_name Filename.
	 * @return kyKnowledgebaseAttachment
	 */
	static public function createNew($kbarticle, $contents, $file_name) {
		$new_kbarticle_attachment = new kyKnowledgebaseAttachment();

		$new_kbarticle_attachment->setKbarticleId($kbarticle->getId());
		$new_kbarticle_attachment->setContents($contents);
		$new_kbarticle_attachment->setFileName($file_name);

		return $new_kbarticle_attachment;
	}

	/**
	 * Creates new attachment for kbarticle with contents read from physical file.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyKnowledgebaseArticle $kbarticle_id kbarticle_id.
	 * @param string $file_path Path to file.
	 * @param string $file_name Optional. Use to set filename other than physical file.
	 * @return kyKnowledgebaseAttachment
	 */
	static public function createNewFromFile($kbarticle, $file_path, $file_name = null) {
		$new_kbarticle_attachment = new kyKnowledgebaseAttachment();

		$new_kbarticle_attachment->setKbarticleId($kbarticle->getId());
		$new_kbarticle_attachment->setContentsFromFile($file_path, $file_name);

		return $new_kbarticle_attachment;
	}
}