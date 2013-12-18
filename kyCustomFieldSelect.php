<?php
/**
 * Class for select custom field with single option.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 * @package Object\CustomField
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyCustomFieldSelect extends kyCustomField {

	/**
	 * Selected option.
	 * @var kyCustomFieldOption
	 */
	protected $option;

	protected function parseData($data) {
		parent::parseData($data);
		$this->option = $this->getOption($data['_contents']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		if ($this->option !== null) {
			$data[$this->name] = $this->option->getId();
		}

		return $data;
	}

	/**
	 * Sets the field selected option.
	 *
	 * @param kyCustomFieldOption $option Field option.
	 * @return kyCustomFieldSelect
	 */
	public function setSelectedOption($option) {
		$this->option = ky_assure_object($option, 'kyCustomFieldOption');

		$this->raw_value = $this->option !== null ? $this->option->getValue() : null;
		return $this;
	}

	/**
	 * Returns selected option for this field.
	 *
	 * @return kyCustomFieldOption
	 */
	public function getSelectedOption() {
		return $this->option;
	}

	/**
	 * Returns selected field option.
	 *
	 * @see kyCustomField::getValue()
	 * @see kyCustomFieldSelect::getSelectedOption()
	 *
	 * @return kyCustomFieldOption
	 */
	public function getValue() {
		return $this->option;
	}

	/**
	 * Sets the option for this field.
	 *
	 * @see kyCustomField::setValue()
	 * @see kyCustomField::setSelectedOption()
	 *
	 * @param mixed $value Identifier of field option OR value of field option OR an option.
	 * @return kyCustomFieldSelect
	 */
	public function setValue($value) {
		$this->setSelectedOption($this->getOption($value));
		return $this;
	}
}