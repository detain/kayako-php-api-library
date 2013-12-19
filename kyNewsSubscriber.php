<?php
/**
 * Kayako NewsSubscriber object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+NewsSubscriber
 * @since Kayako version 4.51.1891
 * @package Object\News
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyNewsSubscriber extends kyObjectBase {

	static protected $controller = '/News/Subscriber';
	static protected $object_xml_name = 'newssubscriber';

	/**
	 * News subscriber identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Template group identifier of this news subscriber.
	 * @apiField name=tgroupid
	 * @var int
	 */
	protected $template_group_id;

	/**
	 * User identifier of this news subscriber.
	 * @apiField
	 * @var int
	 */
	protected $user_id;

	/**
	 * News subscriber e-mail.
	 * @apiField required=true
	 * @var string
	 */
	protected $email;

	/**
	 * Whether the email is validated.
	 * @apiField
	 * @var bool
	 */
	protected $is_validated;

	/**
	 * User group identifier of this news subscriber.
	 * @apiField
	 * @var int
	 */
	protected $user_group_id;

	protected function parseData($data) {
		$this->id = ky_assure_positive_int($data['id']);
		$this->template_group_id = ky_assure_positive_int($data['tgroupid']);
		$this->user_id = ky_assure_positive_int($data['userid']);
		$this->email = ky_assure_string($data['email']);
		$this->is_validated = ky_assure_bool($data['isvalidated']);
		$this->user_group_id = ky_assure_positive_int($data['usergroupid']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$this->buildDataString($data, 'email', $this->email);
		if ($create) {
			//this field works different than other bool fields (http://dev.kayako.com/browse/SWIFT-3141)
			if ($this->is_validated) {
				$this->buildDataBool($data, 'isvalidated', $this->is_validated);
			}
		}

		return $data;
	}

	public function toString() {
		return sprintf("%s (%svalidated)", $this->getEmail(), $this->getIsValidated() ? "" : "not ");
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns template group identifier of the news subscriber.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTemplateGroupId() {
		return $this->template_group_id;
	}

	/**
	 * Returns user identifier of the news subscriber.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * Returns email of the news subscriber.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets email of the news subscriber.
	 *
	 * @param string $email Email of the news subscriber.
	 * @return kyNewsSubscriber
	 */
	public function setEmail($email) {
		$this->email = ky_assure_string($email);
		return $this;
	}

	/**
	 * Returns whether the news subscriber's email is validated.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsValidated() {
		return $this->is_validated;
	}

	/**
	 * Sets whether the news subscriber's email is validated.
	 *
	 * @param bool $is_validated Whether the news subscriber's email is validated.
	 * @return kyNewsSubscriber
	 */
	public function setIsValidated($is_validated) {
		$this->is_validated = $is_validated;
		return $this;
	}

	/**
	 * Returns user group identifier of the news subscriber.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserGroupId() {
		return $this->user_group_id;
	}

	/**
	 * Creates a news subscriber.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $email Email of news subscriber.
	 * @param bool $is_validated Whether email address is validated.
	 * @return kyNewsSubscriber
	 */
	static public function createNew($email, $is_validated = false) {
		$new_news_subscriber = new kyNewsSubscriber();
		$new_news_subscriber->setEmail($email);
		$new_news_subscriber->setIsValidated($is_validated);
		return $new_news_subscriber;
	}
}