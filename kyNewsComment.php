<?php
/**
 * Kayako NewsComment object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+NewsComment
 * @since Kayako version 4.51.1891
 * @package Object\News
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyNewsComment extends kyCommentBase {

	static protected $controller = '/News/Comment';
	static protected $object_xml_name = 'newsitemcomment';

	/**
	 * News item identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $news_item_id;

	/**
	 * News item.
	 * @var kyNewsItem
	 */
	protected $news_item;

	protected function parseData($data) {
		parent::parseData($data);
		$this->news_item_id = ky_assure_positive_int($data['newsitemid']);
	}

	public function buildData($create) {
		$data = parent::buildData($create);

		$this->buildDataNumeric($data, 'newsitemid', $this->news_item_id);

		return $data;
	}

	/**
	 * Returns all comment of news item.
	 *
	 * @param kyNewsItem $knowledgebase_article News item.
	 * @return kyResultSet
	 */
	static public function getAll($knowledgebase_article) {
		if ($knowledgebase_article instanceof kyNewsItem) {
			$news_item_id = $knowledgebase_article->getId();
		} else {
			$news_item_id = $knowledgebase_article;
		}

		return parent::getAll(array('ListAll', $news_item_id));
	}

	/**
	 * Return news item identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getNewsItemId() {
		return $this->news_item_id;
	}

	/**
	 * Sets news item identifier.
	 *
	 * @param int $news_item_id News item identifier.
	 * @return kyNewsComment
	 */
	public function setNewsItemId($news_item_id) {
		$this->news_item_id = ky_assure_positive_int($news_item_id);
		$this->news_item = null;
		return $this;
	}

	/**
	 * Return news item.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyNewsItem
	 */
	public function getNewsItem($reload = false) {
		if ($this->news_item !== null && !$reload)
			return $this->news_item;

		if ($this->news_item_id === null)
			return null;

		$this->news_item = kyNewsItem::get($this->news_item_id);
		return $this->news_item;
	}

	/**
	 * Sets news item.
	 *
	 * @param kyNewsItem $news_item News item.
	 * @return kyNewsComment
	 */
	public function setNewsItem($news_item) {
		$this->news_item = ky_assure_object($news_item, 'kyNewsItem');
		$this->news_item_id = $this->news_item !== null ? $this->news_item->getId() : null;
		return $this;
	}

	/**
	 * Creates a new news item comment.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyNewsItem $knowledgebase_article News item.
	 * @param kyUser|kyStaff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return kyNewsComment
	 */
	static public function createNew($knowledgebase_article, $creator, $contents) {
		/** @var $new_comment kyNewsComment */
		$new_comment = parent::createNew($creator, $contents);
		$new_comment->setNewsItem($knowledgebase_article);
		return $new_comment;
	}
}