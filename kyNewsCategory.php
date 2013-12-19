<?php
/**
 * Kayako NewsCategory object.
 * Known issues:
 * - news items count not updated after removing news item (http://dev.kayako.com/browse/SWIFT-3112)
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+NewsCategory
 * @since Kayako version 4.51.1891
 * @package Object\News
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyNewsCategory extends kyObjectBase {

	/**
	 * News category visibility type - Public.
	 * Public news categories are visible in both the support center and Staff CP.
	 *
	 * @var string
	 */
	const VISIBILITY_TYPE_PUBLIC = 'public';

	/**
	 * News category visibility type - Private.
	 * Private news categories are visible only in Staff CP.
	 *
	 * @var string
	 */
	const VISIBILITY_TYPE_PRIVATE = 'private';

	static protected $controller = '/News/Category';
	static protected $object_xml_name = 'newscategory';

	/**
	 * News category identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * News category title.
	 * @apiField required=true
	 * @var string
	 */
	protected $title;

	/**
	 * Total count of news items in this category.
	 * @apiField
	 * @var int
	 */
	protected $news_item_count = 0;

	/**
	 * News category visibility type.
	 * @apiField required=true
	 * @var int
	 */
	protected $visibility_type;

	protected function parseData($data) {
		$this->id = ky_assure_positive_int($data['id']);
		$this->title = ky_assure_string($data['title']);
		$this->visibility_type = ky_assure_string($data['visibilitytype']);
		$this->news_item_count = ky_assure_positive_int($data['newsitemcount'], 0);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$this->buildDataString($data, 'title', $this->title);
		$this->buildDataString($data, 'visibilitytype', $this->visibility_type);

		return $data;
	}

	public function toString() {
		return sprintf("%s (visbility type: %s)", $this->getTitle(), $this->getVisibilityType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Return visibility type of the news category.
	 *
	 * @see kyNewsCategory::VISIBILITY_TYPE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getVisibilityType() {
		return $this->visibility_type;
	}

	/**
	 * Sets visibility type of the news category.
	 *
	 * @see kyNewsCategory::VISIBILITY_TYPE constants.
	 *
	 * @param int $visibility_type Visibility type of the news category.
	 * @return kyNewsCategory
	 */
	public function setVisibilityType($visibility_type) {
		$this->visibility_type = ky_assure_constant($visibility_type, $this, 'VISIBILITY_TYPE');
		return $this;
	}

	/**
	 * Returns title of the news category.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the news category.
	 *
	 * @param string $title Title of the news category.
	 * @return kyNewsCategory
	 */
	public function setTitle($title) {
		$this->title = ky_assure_string($title);
		return $this;
	}

	/**
	 * Returns total count of news items in this category.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getNewsItemCount() {
		return $this->news_item_count;
	}

	/**
	 * Creates a news category.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @see kyNewsCategory::VISIBILITY_TYPE constants.
	 *
	 * @param string $title Title of news category.
	 * @param int $visibility_type Visibility type of news item.
	 * @return kyNewsCategory
	 */
	static public function createNew($title, $visibility_type) {
		$new_news_category = new kyNewsCategory();
		$new_news_category->setTitle($title);
		$new_news_category->setVisibilityType($visibility_type);
		return $new_news_category;
	}
}