<?php
/**
 * Kayako Knowledgebase comment object.
 *
 * @author Saloni Dhall (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+KnowledgebaseComment
 * @since Kayako version 4.51.1891
 * @package Object\Knowledgebase
 *
 *
 */
class kyKnowledgebaseComment extends kyCommentBase {

	static protected $controller = '/Knowledgebase/Comment';
	static protected $object_xml_name = 'kbarticlecomment';

	/**
	 * kbarticle identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $kbarticle_id;

	/**
	 * kbarticle item.
	 * @var kyKnowledgebaseArticle
	 */
	protected $kbarticle;

	protected function parseData($data) {
		parent::parseData($data);
		$this->kbarticle_id = ky_assure_positive_int($data['kbarticleid']);
	}

	public function buildData($create) {
		$data = parent::buildData($create);

		$this->buildDataNumeric($data, 'knowledgebasearticleid', $this->kbarticle_id);

		return $data;
	}

	/**
	 * Returns all comment of kbarticle.
	 *
	 * @param kyKnowledgebaseArticle $kbarticle kyKnowledgebaseArticle item.
	 * @return kyResultSet
	 */
	static public function getAll($kbarticle) {
		if ($kbarticle instanceof kyKnowledgebaseArticle) {
			$kbarticle_id = $kbarticle->getId();
		} else {
			$kbarticle_id = $kbarticle;
		}

		return parent::getAll(array('ListAll', $kbarticle_id));
	}

	/**
	 * Return KnowledgebaseArticle identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getKbarticleId() {
		return $this->kbarticle_id;
	}

	/**
	 * Sets KnowledgebaseArticle identifier.
	 *
	 * @param int $kbarticle_id KnowledgebaseArticle identifier.
	 * @return kyKnowledgebaseArticle
	 */
	public function setKbarticleId($kbarticle_id) {
		$this->kbarticle_id = ky_assure_positive_int($kbarticle_id);
		$this->kbarticle = null;
		return $this;
	}

	/**
	 * Return KnowledgebaseArticle.
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
	 * Sets KnowledgebaseArticle.
	 *
	 * @param kyKnowledgebaseArticle $kbarticle kbarticle item.
	 * @return kyKnowledgebaseComment
	 */
	public function setKbarticle($kbarticle) {
		$this->kbarticle = ky_assure_object($kbarticle, 'kyKnowledgebaseArticle');
		$this->kbarticle_id = $this->kbarticle !== null ? $this->kbarticle->getId() : null;
		return $this;
	}

	/**
	 * Creates a new Knowledgebase article comment.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyKnowledgebaseArticle $kb_article KnowledgebaseArticle item.
	 * @param kyUser|kyStaff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return kyKnowledgebaseComment
	 */
	static public function createNew($kb_article, $creator, $contents) {
		/** @var $kbarticle_comment kyKnowledgebaseComment */
		$new_comment = parent::createNew($creator, $contents);
		$new_comment->setKbarticle($kb_article);
		return $new_comment;
	}
}