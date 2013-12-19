<?php
/**
 * Class for select custom field with multiple options.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 * @package Object\CustomField
 *
 * @noinspection PhpDocSignatureInspection
 */
class kyCustomFieldMultiSelect extends kyCustomField {

	/**
	 * Separator of field selected values.
	 * @var string
	 */
	const VALUES_SEPARATOR = ', ';

	/**
	 * List of selected field options.
	 * @var kyCustomFieldOption[]
	 */
	protected $options;

	protected function parseData($data) {
		parent::parseData($data);

		$values = explode(self::VALUES_SEPARATOR, $data['_contents']);

		$this->options = array();
		foreach ($values as $value) {
			$field_option = $this->getOption($value);
			if ($field_option === null)
				continue;

			$this->options[] = $field_option;
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		foreach ($this->options as $key => $option) {
			/* @var $option kyCustomFieldOption */
			$data[sprintf('%s[%d]', $this->name, $key)] = $option->getId();
		}

		return $data;
	}

	/**
	 * Returns list of selected options of this custom field.
	 *
	 * @return kyResultSet
	 */
	public function getSelectedOptions() {
		/** @noinspection PhpParamsInspection */
		return new kyResultSet($this->options);
	}

	/**
	 * Sets selected options of this custom field.
	 *
	 * @param kyCustomFieldOption[] $options List of options.
	 * @return kyCustomFieldMultiSelect
	 */
	public function setSelectedOptions($options) {
		//make sure it's array
		if (!is_array($options)) {
			if ($options === null) {
				$options = array();
			} else {
				$options = array($options);
			}
		}

		//check for proper class and eliminate duplicates
		$options_ids = array();
		$this->options = array();
		foreach ($options as $option) {
			$option = ky_assure_object($option, 'kyCustomFieldOption');
			if ($option !== null && !in_array($option->getId(), $options_ids)) {
				$this->options[] = $option;
				$options_ids[] = $option->getId();
			}
		}

		//update raw value
		$option_values = array();
		foreach ($this->options as $field_option) {
			$option_values[] = $field_option->getValue();
		}
		$this->raw_value = implode(self::VALUES_SEPARATOR, $option_values);
	}

	/**
	 * Returns list of selected options of this custom field.
	 *
	 * @see kyCustomField::getValue()
	 * @see kyCustomFieldMultiSelect::getSelectedOptions()
	 *
	 * @return kyCustomFieldOption[]
	 */
	public function getValue() {
		return $this->options;
	}

	/**
	 * Returns selected options values as array:
	 * array(
	 * 	<field option id> => '<field option value>',
	 * 	...
	 * )
	 *
	 * @return array
	 */
	public function getValues() {
		$values = array();
		foreach ($this->options as $field_option) {
			/* @var $field_option kyCustomFieldOption */
			$values[$field_option->getId()] = $field_option->getValue();
		}
		return $values;
	}

	/**
	 * Sets selected options of this custom field.
	 *
	 * @param array $value List of values where each value can be: identifier of field option OR value of field option OR an option.
	 * @return kyCustomFieldMultiSelect
	 */
	public function setValue($value) {
		//make sure it's array
		if (!is_array($value)) {
			if ($value === null) {
				$values = array();
			} else {
				$values = array($value);
			}
		} else {
			$values = $value;
		}

		//build list of kyCustomFieldOption objects
		$options = array();
		foreach ($values as $value) {
			$field_option = $this->getOption($value);
			if ($field_option === null)
				continue;

			$options[] = $field_option;
		}

		//set selected options
		$this->setSelectedOptions($options);

		return $this;
	}
}