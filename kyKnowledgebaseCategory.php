<?php
/**
 * Kayako KnowledgebaseCategory object.
 *
 *
 * @author Saloni Dhall (https://github.com/SaloniDhall)
 * @link http://wiki.kayako.com/display/DEV/REST+-+KnowledgebaseCategory
 * @since Kayako version 4.64
 * @package Object\Knowledgebase
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyKnowledgebaseCategory extends kyObjectBase {

	/**
	 * Knowledegbase category type - Global.
	 * Private knowledegbase categories are visible in your end users in the support center and in the staff control panel.
	 *
	 * @var int
	 */
	const CATEGORY_TYPE_GLOBAL = '1';

	/**
	 * Knowledegbase category type - Public.
	 * Public knowledgebase categories are visible in both the support center.
	 *
	 * @var int
	 */
	const CATEGORY_TYPE_PUBLIC = '2';

	/**
	 * Knowledegbase category type - Private.
	 * Private knowledegbase categories are visible only in Staff CP.
	 *
	 * @var int
	 */
	const CATEGORY_TYPE_PRIVATE = '3';

	/**
	 * Knowledegbase category type - Inherit.
	 * The category will inherit the scope of its parent category.
	 *
	 * @var int
	 */
	const CATEGORY_TYPE_INHERIT = '4';

	static protected $controller = '/Knowledgebase/Category';
	static protected $object_xml_name = 'kbcategory';

	/**
	 * Knowledegbase category identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Knowledegbase category title.
	 * @apiField required=true
	 * @var string
	 */
	protected $title;

	/**
	 * Knowledegbase category type.
	 * @apiField required=true
	 * @var int
	 */
	protected $category_type;

	/**
	 * Knowledegbase parent category Id.
	 * @apiField required=optional
	 * @var int
	 */
	protected $parent_kbcategoryid;

	/**
	 * Knowledegbase category display order
	 * @apiField required=optional
	 * @var int
	 */
	protected $displayorder;

	/**
	 * Knowledegbase category article_sortorder.
	 * @apiField required=optional
	 * @var string
	 */
	protected $article_sortorder;

	/**
	 * Knowledegbase category allow_comments.
	 * @apiField required=optional
	 * @var int
	 */
	protected $allow_comments;

	/**
	 * Knowledegbase category allow_rating.
	 * @apiField required=optional
	 * @var int
	 */
	protected $allow_rating;

	/**
	 * Knowledegbase category is_published.
	 * @apiField required=optional
	 * @var string(yes or no)
	 */
	protected $is_published;

	/**
	 * If this Knowledegbase category is visible to specific user groups only
	 * @see kyKnowledgebaseCategory::$user_group_ids
	 * @var bool
	 */
	protected $user_visibility_custom;

	/**
	 * User group identifiers this Knowledegbase category is visible to.
	 * @apiField name=usergroupidlist
	 * @var int[]
	 */
	protected $user_group_ids;

	/**
	 * If this knowledgebase category is visible to specific staff groups only.
	 * @see kyKnowledgebaseCategory::$staff_group_ids
	 * @apiField
	 * @var bool
	 */
	protected $staff_visibility_custom;

	/**
	 * User group identifiers this knowledgebase category is visible to.
	 * @apiField name=staffgroupidlist
	 * @var int[]
	 */
	protected $staff_group_ids = array();

	/**
	 * Knowledegbase category created by which staff Id
	 * @apiField required=optional
	 * @var int
	 */
	protected $staff_id;

	protected function parseData($data) {
		$this->id = ky_assure_positive_int($data['id']);
		$this->title = ky_assure_string($data['title']);
		$this->category_type = ky_assure_string($data['categorytype']);
		$this->parent_kbcategoryid = ky_assure_positive_int($data['parentkbcategoryid']);
		$this->displayorder = ky_assure_positive_int($data['displayorder']);
		$this->allow_comments = ky_assure_bool($data['allowcomments']);
		$this->allow_rating = ky_assure_bool($data['allowrating']);
		$this->is_published = ky_assure_bool($data['ispublished']);
		if ($this->user_visibility_custom && is_array($data['usergroupidlist'])) {
			$this->user_group_ids = array();
			if (is_string($data['usergroupidlist'][0]['usergroupid'])) {
				$this->user_group_ids[] = intval($data['usergroupidlist'][0]['usergroupid']);
			} else {
				foreach ($data['usergroupidlist'][0]['usergroupid'] as $user_group_id) {
					$this->user_group_ids[] = intval($user_group_id);
				}
			}
		}

		$this->staff_visibility_custom = ky_assure_bool($data['staffvisibilitycustom']);
		$this->staff_group_ids = array();
		if ($this->staff_visibility_custom && is_array($data['staffgroupidlist'])) {
			if (is_string($data['staffgroupidlist'][0]['staffgroupid'])) {
				$this->staff_group_ids[] = ky_assure_positive_int($data['staffgroupidlist'][0]['staffgroupid']);
			} else {
				foreach ($data['staffgroupidlist'][0]['staffgroupid'] as $staff_group_id) {
					$this->staff_group_ids[] = ky_assure_positive_int($staff_group_id);
				}
			}
		}

		$this->staff_id = ky_assure_int($data['staffid']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$this->buildDataString($data, 'title', $this->title);
		$this->buildDataString($data, 'categorytype', $this->category_type);
		$this->buildDataNumeric($data, 'parentkbategoryid', $this->parent_kbcategoryid);
		$this->buildDataNumeric($data, 'displayorder', $this->displayorder);
		$this->buildDataNumeric($data, 'articlesortorder', $this->article_sortorder);
		$this->buildDataBool($data, 'allowcomments', $this->allow_comments);
		$this->buildDataBool($data, 'allowrating', $this->allow_rating);
		$this->buildDataString($data, 'ispublished', $this->is_published);
		$this->buildDataBool($data, 'uservisibilitycustom', $this->user_visibility_custom);
		if ($this->user_visibility_custom) {
			$this->buildDataList($data, 'usergroupidlist', $this->user_group_ids);
		}

		$this->buildDataBool($data, 'staffvisibilitycustom', $this->staff_visibility_custom);
		if ($this->staff_visibility_custom) {
			$this->buildDataList($data, 'staffgroupidlist', $this->staff_group_ids);
		}
		$this->buildDataString($data, 'staffid', $this->staff_id);

		return $data;
	}

	/**
	 * Returns all categories of knowledgebase.
	 *
	 * @param int $starting_kbcategory_id Optional starting kbcategoryid identifier.
	 * @param int $max_items Optional maximum items count. Defaults to 1000 when starting kbcategoryid is defined.
	 * @return kyResultSet
	 */
	static public function getAll($max_items = null, $starting_kbcategory_id = 0) {
		$search_parameters = array('GetList');

		if (is_numeric($starting_kbcategory_id) && $starting_kbcategory_id > 0) {
			if (!is_numeric($max_items) || $max_items <= 0) {
				$max_items = 1000;
			}
			$search_parameters[] = $max_items;
			$search_parameters[] = $starting_kbcategory_id;
		}

		return parent::getAll($search_parameters);
	}

	public function toString() {
		return sprintf("%s (category type: %s)", $this->getTitle(), $this->getCategoryType());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Return category type of the knowledgebase category.
	 *
	 * @see kyKnowledgebaseCategory::CATEGORY_TYPE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCategoryType() {
		return $this->category_type;
	}

	/**
	 * Sets category type of the knowledgebase category.
	 *
	 * @see kyKnowledgebaseCategory::CATEGORY_TYPE constants.
	 *
	 * @param int $category_type Category type of the knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setCategoryType($category_type) {
		$this->category_type = ky_assure_constant($category_type, $this, 'CATEGORY_TYPE');
		return $this;
	}

	/**
	 * Returns title of the knowledgebase category.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the knowledgebase category.
	 *
	 * @param string $title Title of the knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setTitle($title) {
		$this->title = ky_assure_string($title);
		return $this;
	}

	/**
	 * Returns displayorder of the knowledgebase category.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->displayorder;
	}

	/**
	 * Sets displayorder of the knowledgebase category.
	 *
	 * @param int $display_order of the knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setDisplayOrder($display_order) {
		$this->displayorder = ky_assure_int($display_order, 0);
		return $this;
	}

	/**
	 * Returns parentCategoryId of the knowledgebase category.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getParentCategoryId() {
		return $this->parent_kbcategoryid;
	}

	/**
	 * Sets parentCategoryId of the knowledgebase category.
	 *
	 * @param int $parent_categoryid of the knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setParentCategoryId($parent_categoryid) {
		$this->parent_kbcategoryid = ky_assure_int($parent_categoryid);
		return $this;
	}

	/**
	 * Returns articlesortorder of the knowledgebase category.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getArticleSortOrder() {
		return $this->article_sortorder;
	}

	/**
	 * Sets articlesortorder of the knowledgebase category.
	 *
	 * @param int $articlesortorder Sort Order of the knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setArticleSortOrder($articlesortorder) {
		$this->article_sortorder = ky_assure_int($articlesortorder);
		return $this;
	}

	/**
	 * Returns allow_comments of the knowledgebase category.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getAllowComments() {
		return $this->allow_comments;
	}

	/**
	 * Sets allow_comments of the knowledgebase category.
	 *
	 * @param string $allow_comments Title of the knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setAllowComments($allow_comments) {
		$this->allow_comments = ky_assure_bool($allow_comments);
		return $this;
	}

	/**
	 * Returns allow_rating of the knowledgebase category.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getAllowRating() {
		return $this->allow_rating;
	}

	/**
	 * Sets allow_rating of the knowledgebase category.
	 *
	 * @param bool $allow_rating knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setAllowRating($allow_rating) {
		$this->allow_rating = ky_assure_bool($allow_rating);
		return $this;
	}

	/**
	 * Returns is_published of the knowledgebase category.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsPublished() {
		return $this->is_published;
	}

	/**
	 * Sets is_published of the knowledgebase category.
	 *
	 * @param bool $is_published knowledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setIsPublished($is_published) {
		$this->is_published = ky_assure_bool($is_published);
		return $this;
	}

	/**
	 * Returns true to indicate that visibility of this knowledgebase category is restricted to particular user groups.
	 * Use getUserGroupIds to get their identifiers or getUserGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getUserVisibilityCustom() {
		return $this->user_visibility_custom;
	}

	/**
	 * Sets whether to restrict visibility of this knowledgebase category to particular user groups.
	 * Use setUserGroupIds to set these groups using identifiers set them using objects.
	 * Automatically clears user groups when set to false.
	 *
	 * @param bool $user_visibility_custom True to restrict visibility of this knowledgebase category to particular user groups. False otherwise.
	 * @return kyKnowledgebaseCategory
	 */
	public function setUserVisibilityCustom($user_visibility_custom) {
		$this->user_visibility_custom = ky_assure_bool($user_visibility_custom);
		if ($this->user_visibility_custom === false) {
			$this->user_group_ids = array();
		}
		return $this;
	}

	/**
	 * Returns identifiers of user groups that this knowledgebase category will be visible to.
	 *
	 * @return array
	 * @filterBy name=UserGroupId
	 */
	public function getUserGroupIds() {
		return $this->user_group_ids;
	}

	/**
	 * Sets user groups (using their identifiers) that this knowledgebase category will be visible to.
	 *
	 * @param int[] $user_group_ids Identifiers of user groups that this knowledgebase category will be visible to.
	 * @return kyKnowledgebaseCategory
	 */
	public function setUserGroupIds($user_group_ids) {
		//normalization to array
		if (!is_array($user_group_ids)) {
			if (is_numeric($user_group_ids)) {
				$user_group_ids = array($user_group_ids);
			} else {
				$user_group_ids = array();
			}
		}

		//normalization to positive integer values
		$this->user_group_ids = array();
		foreach ($user_group_ids as $user_group_id) {
			$user_group_id = ky_assure_positive_int($user_group_id);
			if ($user_group_id === null)
				continue;

			$this->user_group_ids[] = $user_group_id;
		}

		return $this;
	}

	/**
	 * Returns true to indicate that visibility of this knowledgebase category is restricted to particular staff groups.
	 * Use getStaffGroupIds to get their identifiers or getStaffGroups to get the objects.
	 *
	 * @return bool
	 * @filterBy
	 */
	public function getStaffVisibilityCustom() {
		return $this->staff_visibility_custom;
	}

	/**
	 * Sets whether to restrict visibility of this knowledgebase category item to particular staff groups.
	 * Use setStaffGroupIds to set these groups using identifiers to set them using objects.
	 * Automatically clears staff groups when set to false.
	 *
	 * @param bool $staff_visibility_custom True to restrict visibility of this knowledgebase category to particular staff groups. False otherwise.
	 * @return kyKnowledgebaseCategory
	 */
	public function setStaffVisibilityCustom($staff_visibility_custom) {
		$this->staff_visibility_custom = ky_assure_bool($staff_visibility_custom);
		if ($this->staff_visibility_custom === false) {
			$this->staff_group_ids = array();
			$this->staff_groups = null;
		}
		return $this;
	}

	/**
	 * Returns identifiers of staff groups that this knowledgebase category item will be visible to.
	 *
	 * @return array
	 * @filterBy name=StaffGroupId
	 */
	public function getStaffGroupIds() {
		return $this->staff_group_ids;
	}

	/**
	 * Sets staff groups (using their identifiers) that this knowledgebase category item will be visible to.
	 *
	 * @param int[] $staff_group_ids Identifiers of staff groups that this knowledgebase category item will be visible to.
	 * @return kyKnowledgebaseCategory
	 */
	public function setStaffGroupIds($staff_group_ids) {
		//normalization to array
		if (!is_array($staff_group_ids)) {
			if (is_numeric($staff_group_ids)) {
				$staff_group_ids = array($staff_group_ids);
			} else {
				$staff_group_ids = array();
			}
		}

		//normalization to positive integer values
		$this->staff_group_ids = array();
		foreach ($staff_group_ids as $staff_group_id) {
			$staff_group_id = ky_assure_positive_int($staff_group_id);
			if ($staff_group_id === null)
				continue;

			$this->staff_group_ids[] = $staff_group_id;
		}

		return $this;
	}

	/**
	 * Returns staff Id of the knowledgebase category.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStaffId() {
		return $this->staff_id;
	}

	/**
	 * Sets staff Id of the knowledgebase category.
	 *
	 * @param string $staff_id of the knoledgebase category.
	 * @return kyKnowledgebaseCategory
	 */
	public function setStaffId($staff_id) {
		$this->staff_id = ky_assure_positive_int($staff_id);
		return $this;
	}

	/**
	 * Creates a knowledegbase category.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @see kyKnowledgebaseCategory::CATEGORY_TYPE constants.
	 *
	 * @param string $title Title of knowledgebase category.
	 * @param int $category_type Category type of knowledgebase item.
	 * @return kyKnowledgebaseCategory
	 */
	static public function createNew($title, $category_type) {
		$new_knowledgebase_category = new kyKnowledgebaseCategory();
		$new_knowledgebase_category->setTitle($title);
		$new_knowledgebase_category->setCategoryType($category_type);
		return $new_knowledgebase_category;
	}
}