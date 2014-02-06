<?php
/**
 * Kayako TroubleshooterComment object.
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TroubleshooterComment
 * @since Kayako version 4.64.1
 * @package Object\TroubleshooterComment
 *
 */
class kyTroubleshooterComment extends kyCommentBase {

	static protected $controller = '/Troubleshooter/Comment';
	static protected $object_xml_name = 'troubleshooterstepcomment';

	/**
	 * Troubleshooterstep item identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $troubleshooterstep_item_id;

	/**
	 * Troubleshooterstep item.
	 * @var kyTroubleshooterstep
	 */
	protected $troubleshooterstep_item;

	protected function parseData($data) {
		parent::parseData($data);
		$this->troubleshooterstep_item_id = ky_assure_positive_int($data['troubleshooterstepid']);
	}

	public function buildData($create) {
		$data = parent::buildData($create);

		$this->buildDataNumeric($data, 'troubleshooterstepid', $this->troubleshooterstep_item_id);

		return $data;
	}

	/**
	 * Returns all the troubleshooter step identifiers.
	 *
	 * @param array $troubleshooterstep
	 *
	 * @return kyResultSet
	 */
	static public function getAll($troubleshooterstep) {
		if ($troubleshooterstep instanceof kyTroubleshooterStep) {
			$troubleshooterstep_item_id = $troubleshooterstep->getId();
		} else {
			$troubleshooterstep_item_id = $troubleshooterstep;
		}

		return parent::getAll(array('ListAll', $troubleshooterstep_item_id));
	}

	/**
	 * Return troubleshooter item identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTroubelshooterStepId() {
		return $this->troubleshooterstep_item_id;
	}

	/**
	 * Sets the troubleshooterstep Id.
	 *
	 * @param int $troubleshooterstep_item_id TroubleshooterStep identifier
	 *
	 * @return $this
	 */
	public function setTroubelshooterStepId($troubleshooterstep_item_id) {
		$this->troubleshooterstep_item_id = ky_assure_positive_int($troubleshooterstep_item_id);
		$this->troubleshooterstep_item = null;
		return $this;
	}

	/**
	 * Return troubleshooter item.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyTroubleshooterStep
	 */
	public function getTroubleshooterStep($reload = false) {
		if ($this->troubleshooterstep_item !== null && !$reload)
			return $this->troubleshooterstep_item;

		if ($this->troubleshooterstep_item_id === null)
			return null;

		$this->troubleshooterstep_item = kyTroubleshooterStep::get($this->troubleshooterstep_item_id);
		return $this->troubleshooterstep_item;
	}

	/**
	 * Sets the troubleshooterstep item
	 *
	 * @param int $troubleshooterstep_item
	 *
	 * @return $this
	 */
	public function setTroubleshooterStep($troubleshooterstep_item) {
		$this->troubleshooterstep_item = ky_assure_object($troubleshooterstep_item, 'kyTroubleshooterStep');
		$this->troubleshooterstep_item_id = $this->troubleshooterstep_item !== null ? $this->troubleshooterstep_item->getId() : null;
		return $this;
	}

	/**
	 * Creates a new troubelshooterstep comment.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyTroubleshooterStep $troubleshooterstep TroubleshooterStep item.
	 * @param kyUser|kyStaff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return kyTroubleshooterComment
	 */
	static public function createNew($troubleshooterstep, $creator, $contents) {
		/** @var $new_comment kyTroubleshooterComment */
		$new_comment = parent::createNew($creator, $contents);
		$new_comment->setTroubleshooterStep($troubleshooterstep);
		return $new_comment;
	}
}