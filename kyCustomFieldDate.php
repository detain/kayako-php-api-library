<?php
/**
 * Class for date custom field.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 * @package Object\CustomField
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyCustomFieldDate extends kyCustomField {

	/**
	 * Timestamp representation of date.
	 * @var int
	 */
	private $timestamp;

	protected function parseData($data) {
		parent::parseData($data);
		$this->timestamp = strtotime($data['_contents']);
	}

	/**
	 * Returns field value as timestamp.
	 *
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Sets the date using timestamp.
	 *
	 * @param int $timestamp Timestamp.
	 * @return kyCustomFieldDate
	 */
	public function setTimestamp($timestamp) {
		$this->timestamp = ky_assure_int($timestamp, 0);

		$this->raw_value = date('m/d/Y', $this->timestamp);
		return $this;
	}
	/**
	 * Returns field value as formatted date.
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 */
	public function getDate($format = null) {
		if ($format === null) {
			$format = kyConfig::get()->getDateFormat();
		}

		return date($format, $this->timestamp);
	}

	/**
	 * Sets the date.
	 *
	 * @param string $date Date in format understood by PHP strtotime.
	 * @return kyCustomFieldDate
	 */
	public function setDate($date) {
		$this->setTimestamp(strtotime($date));
		return $this;
	}

	/**
	 * Returns field value as formatted date. Default format from client configuration is used.
	 * @see kyCustomField::getValue()
	 * @see kyCustomFieldDate::getDate()
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->getDate();
	}

	/**
	 * Sets the date.
	 *
	 * @see kyCustomField::setValue()
	 * @see kyCustomFieldDate::setDate()
	 *
	 * @param string $value Date in format understood by PHP strtotime.
	 * @return kyCustomFieldDate
	 */
	public function setValue($value) {
		$this->setDate($value);
		return $this;
	}
}