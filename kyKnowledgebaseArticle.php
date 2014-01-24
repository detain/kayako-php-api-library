<?php
/**
 * Kayako KnowledgebaseArticle object.
 *
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+KnowledgebaseArticle
 * @since Kayako version 4.64
 * @package Object\Knowledgebase
 *
 */
class kyKnowledgebaseArticle extends kyObjectBase {

	/**
	 * Article Status - Published.
	 * The article status classified as Published will be visible to all end users in support center.
	 *
	 * @var int
	 */
	const STATUS_PUBLISHED = 1;

	/**
	 * Article Status - Draft.
	 * The article status classified as Draft considered as a draft kbarticle.
	 *
	 * @var int
	 */
	const STATUS_DRAFT = 2;

	static protected $controller = '/Knowledgebase/Article';
	static protected $object_xml_name = 'kbarticle';

	/**
	 * Knowledgebase article identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Knowledgebase article subject.
	 * @apiField
	 * @var string
	 */
	protected $subject;

	/**
	 * Knowledgebase article contents.
	 * @apiField required=true
	 * @var string
	 */
	protected $contents;

	/**
	 * Knowledgebase article creator Id.
	 * @apiField required=optional
	 * @var int
	 */
	protected $creator_id;

	/**
	 * Editor (staff) identifier.
	 * @apiField required_update=true
	 * @var int
	 */
	protected $edited_staff_id;

	/**
	 * Editor (staff).
	 * @var kyStaff
	 */
	private $edited_staff;

	/**
	 * Creator (staff).
	 * @var kyStaff
	 */
	private $creator;

	/**
	 * Knowledgebase article status.
	 * @apiField name=articlestatus
	 * @var int
	 */
	protected $article_status;

	/**
	 * Whether this Knowledgebase article is featured or not.
	 * @apiField
	 * @var bool
	 */
	protected $is_featured = false;

	/**
	 * Whether to allow comments.
	 * @apiField
	 * @var bool
	 */
	protected $allow_comments;

	/**
	 * Total count of Knowledgebase article comments.
	 * @apiField
	 * @var int
	 */
	protected $total_comments = 0;

	/**
	 * Whether the article has attachments or not.
	 * @apiField
	 * @var int
	 */
	protected $has_attachments = 0;

	/**
	 * Identifiers of Knowledgebase categories this Knowledgebase article belongs to.
	 * @apiField name=categories
	 * @var int[]
	 */
	protected $category_ids = array();

	/**
	 * Knowledgebase category this knowledgebase article belongs to.
	 * @var kyKnowledgebaseArticle[]
	 */
	private $categories = array();

	protected function parseData($data) {
		$this->id = ky_assure_positive_int($data['kbarticleid']);
		$this->subject = ky_assure_string($data['subject']);
		$this->contents = ky_assure_string($data['contents']);
		$this->creator_id = ky_assure_int($data['creatorid']);
		$this->article_status = ky_assure_bool($data['articlestatus']);
		$this->is_featured = ky_assure_bool($data['isfeatured']);
		$this->allow_comments = ky_assure_bool($data['allowcomments']);
		$this->total_comments = ky_assure_int($data['totalcomments']);
		$this->has_attachments = ky_assure_int($data['hasattachments']);

		$this->categories = array();
		if (is_array($data['categories'])) {
			if (is_string($data['categories'][0]['categoryid'])) {
				$this->category_ids[] = ky_assure_positive_int($data['categories'][0]['categoryid']);
			} else {
				foreach ($data['categories'][0]['categoryid'] as $category_id) {
					$this->category_ids[] = ky_assure_positive_int($category_id);
				}
			}
		}

	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$this->buildDataString($data, 'subject', $this->subject);
		$this->buildDataString($data, 'contents', $this->contents);

		if ($create) {
			$this->buildDataNumeric($data, 'creatorid', $this->creator_id);
		} else {
			$this->buildDataNumeric($data, 'editedstaffid', $this->edited_staff_id);
		}

		$this->buildDataNumeric($data, 'articlestatus', $this->article_status);
		$this->buildDataBool($data, 'allowcomments', $this->allow_comments);
		$this->buildDataList($data, 'categoryid', $this->category_ids);

		return $data;
	}

	static public function getAll($category = null, $max_items = null, $starting_kbarticle_id = null) {
		if ($category instanceof kyKnowledgebaseCategory) {
			$category_id = $category->getId();
		} else {
			$category_id = $category;
		}

		if (!is_numeric($category)) {
			return parent::getAll();
		} else {
			$search_parameters = array('ListAll', $category_id);

			if (is_numeric($starting_kbarticle_id) && $starting_kbarticle_id > 0) {
				if (!is_numeric($max_items) || $max_items <= 0) {
					$max_items = 1000;
				}
				$search_parameters[] = $max_items;
				$search_parameters[] = $starting_kbarticle_id;
			}
			return parent::getAll($search_parameters);
		}
	}

	public function toString() {
		return sprintf("(title: %s, contents: %s)", $this->getSubject(), substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''));
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns identifier of staff user, the creator of this knowledgebase article.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStaffId() {
		return $this->creator_id;
	}

	/**
	 * Sets identifier of staff user, the creator of this knowledgebase article.
	 *
	 * @param int $creator_id Staff user identifier.
	 * @return kyKnowledgebaseArticle
	 */
	public function setStaffId($creator_id) {
		$this->creator_id = ky_assure_positive_int($creator_id);
		$this->creator = null;
		return $this;
	}

	/**
	 * Gets the staff user, the creator of this knowledgebase article.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyStaff
	 */
	public function getStaff($reload = false) {
		if ($this->creator !== null && !$reload)
			return $this->creator;

		if ($this->creator_id === null)
			return null;

		$this->creator = kyStaff::get($this->creator_id);
		return $this->creator;
	}

	/**
	 * Sets staff user, the creator of this knowledgebase article.
	 *
	 * @param kyStaff $staff Staff user.
	 * @return kyKnowledgebaseArticle
	 */
	public function setStaff($creator) {
		$this->creator = ky_assure_object($creator, 'kyStaff');
		$this->creator_id = $this->creator !== null ? $this->creator->getId() : null;
		return $this;
	}

	/**
	 * Returns subject of the knowledgebase article.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets subject of the knowledgebase article.
	 *
	 * @param string $subject Subject of the knowledgebase article.
	 * @return kyKnowledgebaseArticle
	 */
	public function setSubject($subject) {
		$this->subject = ky_assure_string($subject);
		return $this;
	}

	/**
	 * Returns contents of the knowledgebase article.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of the knowledgebase article.
	 *
	 * @param string $contents Contents of the knowledgebase article.
	 * @return kyKnowledgebaseArticle
	 */
	public function setContents($contents) {
		$this->contents = ky_assure_string($contents);
		return $this;
	}

	/**
	 * Returns status of the knowledgebase article.
	 *
	 * @see kyKnowledgebaseArticle::STATUS constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStatus() {
		return $this->article_status;
	}

	/**
	 * Sets status of the knowledgebase article.
	 *
	 * @see kyKnowledgebaseArticle::STATUS constants.
	 *
	 * @param int $status Status of the knowledgebase article.
	 * @return kyKnowledgebaseArticle
	 */
	public function setStatus($status) {
		$this->article_status = ky_assure_constant($status, $this, 'STATUS');
		return $this;
	}

	/**
	 * Returns creator Id of the knowledgebase article.
	 *
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorId() {
		return $this->creator_id;
	}

	/**
	 * Sets creator of the knowledgebase article.
	 *
	 *
	 * @param int $creator_id creator of the knowledgebase article.
	 * @return kyKnowledgebaseArticle
	 */
	public function setCreatorId($creator_id) {
		$this->creator_id = ky_assure_int($creator_id);
		return $this;
	}

	/**
	 * Sets identifier of staff user, the editor of this knowledgebase article update.
	 *
	 * @param int $staff_id Staff user identifier.
	 * @return kyKnowledgebaseArticle
	 */
	public function setEditedStaffId($staff_id) {
		$this->edited_staff_id = ky_assure_positive_int($staff_id);
		$this->edited_staff = null;
		return $this;
	}

	/**
	 * Returns whether this article is featured or not.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsFeatured() {
		return $this->is_featured;
	}

	/**
	 * Sets this knowledgebase article is featured or not.
	 *
	 * @param bool $is_featured knowledgebase article.
	 * @return kyKnowledgebaseCategory
	 */
	public function setIsFeatured($is_featured) {
		$this->is_featured = ky_assure_bool($is_featured);
		return $this;
	}

	/**
	 * Returns total count of comments on this knowledgebase article.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTotalComments() {
		return $this->total_comments;
	}

	/**
	 * Returns whether clients are permitted to comment on this knowledgebase article.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getAllowComments() {
		return $this->allow_comments;
	}

	/**
	 * Sets whether clients are permitted to comment on this knowledgebase article.
	 *
	 * @param bool $allow_comments True to allow clients to comment on this knowledgebase article item.
	 * @return kyKnowledgebaseCategory
	 */
	public function setAllowComments($allow_comments) {
		$this->allow_comments = ky_assure_bool($allow_comments);
		return $this;
	}

	/**
	 * whether this knowledgebase article contains attachments or not.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getHasAttachments() {
		return $this->has_attachments;
	}

	/**
	 * Returns identifiers of categories of this knowledgebase article.
	 *
	 * @return array
	 * @filterBy name=CategoryId
	 */
	public function getCategoryIds() {
		return $this->category_ids;
	}

	/**
	 * Sets categories (using their identifiers) for this knowledgebase article.
	 *
	 * @param int[] $category_ids Identifiers of categories for this knowledgebase article item.
	 * @return kyKnowledgebaseCategory
	 */
	public function setCategoryIds($category_ids) {
		//normalization to array
		if (!is_array($category_ids)) {
			if (is_numeric($category_ids)) {
				$category_ids = array($category_ids);
			} else {
				$category_ids = array();
			}
		}

		//normalization to positive integer values
		$this->category_ids = array();
		foreach ($category_ids as $category_id) {
			$category_id = ky_assure_positive_int($category_id);
			if ($category_id === null)
				continue;

			$this->category_ids[] = $category_id;
		}

		return $this;
	}

	/**
	 * Returns categories of this knowledgebase article.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyResultSet
	 */
	public function getCategories($reload = false) {
		foreach ($this->category_ids as $category_id) {
			if (!is_array($this->categories) || !array_key_exists($category_id, $this->categories) || $reload) {
				$this->categories[$category_id] = kyKnowledgebaseCategory::get($category_id);
			}
		}
		return new kyResultSet(array_values($this->categories));
	}

	/**
	 * Creates a knowledgebase article.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $subject Subject of knowledgebase article.
	 * @param string $contents Contents of knowledgebase article.
	 * @param kyStaff $staff Author (staff) of knowledgebase article.
	 * @return kyKnowledgebaseCategory
	 */
	static public function createNew($subject, $contents, kyStaff $creator_id) {
		$new_kbarticle_item = new kyKnowledgebaseArticle();
		$new_kbarticle_item->setSubject($subject);
		$new_kbarticle_item->setContents($contents);
		$new_kbarticle_item->setStaff($creator_id);
		return $new_kbarticle_item;
	}

}