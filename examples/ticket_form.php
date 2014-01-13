<?php
/**
 * Sample form for creating new ticket. Supports ticket custom fields.
 *
 * Change following constants according to your installation:
 * DEFAULT_TICKET_STATUS_NAME: name of ticket status which should be assigned to newly created tickets
 * BASE_URL: Kayako REST API URL (Admin CP -> REST API -> API Information)
 * API_KEY: Kayako API Key (Admin CP -> REST API -> API Information)
 * SECRET_KEY: Kayako Secret Key (Admin CP -> REST API -> API Information)
 * DEBUG: true to output HTTP requests and responses to PHP error log, false to disable debugging
 * USER_GROUP_TITLE: Name of the user group that should be used to prepare the list of available ticket types and ticket priorities.
 *
 * Consult further comments for configuration and other explanations.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 * @package Example
 */

define('DEFAULT_TICKET_STATUS_NAME', 'Open');
define('BASE_URL', '<API URL>');
define('API_KEY', '<API key>');
define('SECRET_KEY', '<Secret key>');
define('DEBUG', true);
define('USER_GROUP_TITLE', 'Registered');

require_once("../kyIncludes.php");

/**
 * Initializes the client.
 */
function initKayako() {
	$config = new kyConfig(BASE_URL, API_KEY, SECRET_KEY);
	$config->setDebugEnabled(DEBUG);
	kyConfig::set($config);
}

/**
 * Returns list of departments in the form of array:
 * array(
 * 	<top department id> => array(
 * 		'department' => <top department object>,
 * 		'child_departments' => array(<child department object>, ...)
 * 	),
 * 	...
 * )
 *
 * @return array
 */
function getDepartmentsTree() {
	$departments_tree = array();
	$all_departments = kyDepartment::getAll()->filterByModule(kyDepartment::MODULE_TICKETS)->filterByType(kyDepartment::TYPE_PUBLIC);

	$top_departments = $all_departments->filterByParentDepartmentId(null)->orderByDisplayOrder();
	foreach ($top_departments as $top_department) {
		/* @var $top_department kyDepartment */
		$departments_tree[$top_department->getId()] = array(
			'department' => $top_department,
			'child_departments' => $all_departments->filterByParentDepartmentId($top_department->getId())->orderByDisplayOrder()
		);
	}

	return $departments_tree;
}

/**
 * Returns ticket custom fields in the form of array:
 * array(
 *     '<custom field group title>' => array(<custom field object>, ... ),
 *     ...
 * )
 *
 * @param kyTicket $ticket Ticket.
 * @param bool $file_custom_field_present Placeholder to indicate if there is file custom field.
 * @return array
 */
function get_ticket_custom_fields(kyTicket $ticket, &$file_custom_field_present) {
	$custom_field_groups = $ticket->getCustomFieldGroups();
	if (count($custom_field_groups) === 0)
		return array();

	$custom_fields = array();
	foreach ($custom_field_groups as $custom_field_group) {
		/* @var $custom_field_group kyTicketCustomFieldGroup */

		$group_custom_fields = array();
		foreach ($custom_field_group->getFields() as $custom_field) {
			/* @var $custom_field kyCustomField */
			if (!$custom_field->getDefinition()->getIsUserEditable())
				continue;

			if ($custom_field->getType() === kyCustomFieldDefinition::TYPE_FILE) {
				$file_custom_field_present = true;
			}

			$group_custom_fields[$custom_field->getDefinition()->getDisplayOrder()] = $custom_field;
		}

		if (count($group_custom_fields) > 0) {
			ksort($group_custom_fields, SORT_NUMERIC);
			$custom_fields[$custom_field_group->getTitle()] = array_values($group_custom_fields);
		}
	}

	return $custom_fields;
}

/**
 * Returns field value from POST data.
 *
 * @param string $field_name Field name.
 * @param bool $form_valid Reference to variable holding form validity status.
 * @param string[] $fields_valid Reference to array holding fields validity status.
 * @param bool $required Tells whether this field is required.
 * @param string $regexp If not empty, the field value will be validated against this PERL regular expression.
 * @param bool $as_array Tells whether field value is expected to be an array.
 * @return mixed Field value.
 */
function get_post_value($field_name, &$form_valid, &$fields_valid, $required = true, $regexp = null, $as_array = false) {
	$fields_valid[$field_name] = true;

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		$form_valid = false;
		return null;
	}

	if (!array_key_exists($field_name, $_POST)) {
		if ($required) {
			$form_valid = false;
			$fields_valid[$field_name] = "This field is required.";
			return false;
		} else {
			return null;
		}
	}

	if ($as_array) {
		$value = $_POST[$field_name];
		if (!is_array($value)) {
			if (strlen(trim($value)) > 0) {
				$value = array($value);
			} else {
				$value = array();
			}
		}

		if ($required && count($value) === 0) {
			$form_valid = false;
			$fields_valid[$field_name] = "This field is required.";
			return false;
		}
	} else {
		$value = trim($_POST[$field_name]);

		if ($required && strlen($value) === 0) {
			$form_valid = false;
			$fields_valid[$field_name] = "This field is required.";
			return false;
		}

		if (strlen($regexp) > 0 && !preg_match($regexp, $value)) {
			$form_valid = false;
			$fields_valid[$field_name] = "Error validating field value.";
			return $value;
		}
	}

	return $value;
}

initKayako();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>Kayako ticket submit form - sample</title>

	<style type="text/css">
	body {
		font-family: 'Helvetica neue', sans-serif;
	}

	span.error {
		color: red;
	}

	span.required {
		font-weight: bold !important;
	}

	form {
		float: left;
		width: 800px;
	}

		form fieldset {
			border: none;
			padding: 20px;
			margin: 10px 0;
			background: #EAEAEA;
		}

			form fieldset legend {
				font-weight: bold;
			}

		form label,
		form div.field {
			display: block;
			float: left;
			clear: left;
			margin: 10px 0;
			font-size: 12px;
		}

			form label span.name,
			form div.field span.name {
				display: block;
				float: left;
				width: 80px;
				margin-right: 20px;
				font-weight: bold;
			}

			form label span.description,
			form div.field span.description {
				display: block;
				float: right;
				width: 350px;
				margin-left: 20px;
				font-weight: normal;
				font-style: italic;
			}

				form label label,
				form div.field label,
				form div.field div.field {
					margin: 2px 0;
					width: 380px;
				}

			form label input,
			form label textarea,
			form label select {
				float: left;
				font-size: 14px;
				width: 280px;
			}

				form label label input[type="radio"],
				form label label input[type="checkbox"],
				form div.field label input[type="radio"],
				form div.field label input[type="checkbox"],
				form div.field div.field input[type="radio"],
				form div.field div.field input[type="checkbox"] {
					width: 20px;
					margin-left: 100px;
				}
					form label label label input[type="radio"],
					form label label label input[type="checkbox"],
					form div.field label label input[type="radio"],
					form div.field label label input[type="checkbox"],
					form div.field div.field label input[type="radio"],
					form div.field div.field label input[type="checkbox"] {
						width: 20px;
						margin-left: 130px;
					}

		form p {
			clear: left;
			font-size: 14px;
			margin: 10px 0 0;
		}

		form input[type="submit"] {
		}
	</style>
</head>
<body>
<?php

//what page are we on?
$page = get_post_value('page', $nop, $nop, false);

//holds form validity status
$form_valid = true;

//holds fields validity status
$fields_valid = array();

//processing of submitted forms
switch ($page) {
	case 'department': //we are submitting Department form
		$department_id = get_post_value('department_id', $form_valid, $fields_valid);

		//if valid, render General Information form, re-render Department form if it's invalid
		$render = $form_valid ? 'general' : 'department';
	break;

	case 'general': //we are submitting General Information form
		$department_id = get_post_value('department_id', $form_valid, $fields_valid);

		$creator_full_name = get_post_value('creator_full_name', $form_valid, $fields_valid);
		$creator_email = get_post_value('creator_email', $form_valid, $fields_valid);
		$type_id = get_post_value('type_id', $form_valid, $fields_valid);
		$priority_id = get_post_value('priority_id', $form_valid, $fields_valid);
		$subject = get_post_value('subject', $form_valid, $fields_valid);
		$contents = get_post_value('contents', $form_valid, $fields_valid);

		//if valid, submit the ticket, re-render General Information form if it's invalid
		$render = $form_valid ? 'submit' : 'general';
	break;

	case 'custom_fields': //we are submitting Custom Fields form
		$ticket_id = get_post_value('ticket_id', $form_valid, $fields_valid);

		/** @var $ticket kyTicket */
		$ticket = kyTicket::get($ticket_id);

		//load ticket custom fields; check if there is a file custom field (for proper form encoding)
		$file_custom_field_present = false;
		$ticket_custom_fields = get_ticket_custom_fields($ticket, $file_custom_field_present);

		//render summary if there is no ticket custom fields
		if (count($ticket_custom_fields) === 0) {
			$render = 'summary';
			break;
		}

		//load custom field values
		$custom_field_values = array();
		foreach ($ticket_custom_fields as $custom_fields) {
			foreach ($custom_fields as $custom_field) {
				/* @var $custom_field kyCustomField */
				$custom_field_definition = $custom_field->getDefinition();

				//process file custom field
				if ($custom_field_definition->getType() === kyCustomFieldDefinition::TYPE_FILE) {
					$fields_valid[$custom_field->getName()] = true;
					if (array_key_exists($custom_field->getName(), $_FILES) && $_FILES[$custom_field->getName()]['error'] != UPLOAD_ERR_NO_FILE) {
						if ($_FILES[$custom_field->getName()]['error'] != UPLOAD_ERR_OK || !is_uploaded_file($_FILES[$custom_field->getName()]['tmp_name'])) {
							$form_valid = false;
							$fields_valid[$custom_field->getName()] = "Error uploading file.";
							continue;
						}

						$custom_field_values[$custom_field->getName()] = $_FILES[$custom_field->getName()]['name'];
					} else {
						if ($custom_field_definition->getIsRequired()) {
							$form_valid = false;
							$fields_valid[$custom_field->getName()] = "This field is required.";
							continue;
						}
					}
					continue;
				}

				//process other custom fields
				$custom_field_values[$custom_field->getName()] = get_post_value($custom_field->getName(), $form_valid, $fields_valid, $custom_field_definition->getIsRequired(), $custom_field_definition->getRegexpValidate(), $custom_field_definition->getType() === kyCustomFieldDefinition::TYPE_CHECKBOX || $custom_field_definition->getType() === kyCustomFieldDefinition::TYPE_MULTI_SELECT);
			}
		}

		//if valid, submit the ticket custom fields, re-render Custom Fields form if it's invalid
		$render = $form_valid ? 'submit_custom_fields' : 'custom_fields';
	break;

	default: //we are displaying the page for the first time
		$department_id = null;

		//render Department form as the first page
		$render = 'department';
	break;
}

//we are rendering Department form
if ($render === 'department') {
	//load departments tree
	$departments_tree = getDepartmentsTree();
?>
	<form method="POST">
		<input type="hidden" name="page" id="page" value="department">

		<fieldset>
			<legend>Department</legend>

			<div class="field"><span class="name">Department</span>
				<span class="description required<?=!$form_valid && $fields_valid['department_id'] !== true ? ' error' : ''?>">Choose department based on the nature of your request.</span>
<?php
				//iterating over top departments
				foreach ($departments_tree as $department_leaf) {
					$top_department = $department_leaf['department'];
					$child_departments = $department_leaf['child_departments'];
					/*@var $top_department kyDepartment */
?>
				<label>
					<input type="radio" name="department_id" value="<?=$top_department->getId()?>">
					<span><?=$top_department->getTitle()?></span>
				</label>

				<div class="field">
<?php
					//iterating over child departments
					foreach ($child_departments as $child_department) {
						/*@var $child_department kyDepartment */
?>
						<label>
							<input type="radio" name="department_id" value="<?=$child_department->getId()?>">
							<span><?=$child_department->getTitle()?></span>
						</label>
<?php
					}
?>
				</div>
<?php

				}
?>
			</div>
		</fieldset>

		<input type="submit" name="submit_department" value="Next">
	</form>
<?php
}

//we are rendering General Information form
if ($render === 'general') {
	//load the department we've chosen in Department form
	$department = kyDepartment::get($department_id);

	//load user group for ticket type and priority visibility test
	$user_group = kyUserGroup::getAll()->filterByTitle(USER_GROUP_TITLE)->first();

	//load public ticket types
	$ticket_types = kyTicketType::getAll()->filterByType(kyTicketType::TYPE_PUBLIC);

	//get only types visible to user group
	if ($user_group !== null) {
		$ticket_types = $ticket_types->filterByIsVisibleToUserGroup(true, $user_group);
	}

	//load public ticket priorities
	$ticket_priorities = kyTicketPriority::getAll()->filterByType(kyTicketPriority::TYPE_PUBLIC);

	//get only priorities visible to user group
	if ($user_group !== null) {
		$ticket_priorities = $ticket_priorities->filterByIsVisibleToUserGroup(true, $user_group);
	}
?>
	<form method="POST">
		<input type="hidden" name="page" id="page" value="general">

		<input type="hidden" name="department_id" id="department_id" value="<?=$department_id?>">

		<fieldset>
			<legend>General Information</legend>
			<label><span class="name">Full Name</span>
				<input type="text" value="<?=$creator_full_name?>" id="creator_full_name" name="creator_full_name">
				<span class="description required<?=!$form_valid && $fields_valid['creator_full_name'] !== true ? ' error' : ''?>">Provide your first and last name.</span>
			</label>

			<label><span class="name">Email</span>
				<input type="text" value="<?=$creator_email?>" id="creator_email" name="creator_email">
				<span class="description required<?=!$form_valid && $fields_valid['creator_email'] !== true ? ' error' : ''?>">Provide your e-mail address.</span>
			</label>

			<label><span class="name">Type</span>
				<select id="type_id" name="type_id">
<?php
				foreach ($ticket_types as $ticket_type) {
					/*@var $ticket_type kyTicketType */
					$selected = false;
					if (is_numeric($type_id) && $type_id == $ticket_type->getId()) {
						$selected = true;
					}
?>
					<option<?=$selected ? ' selected' : ''?> value="<?=$ticket_type->getId()?>"><?=$ticket_type->getTitle()?></option>
<?php

				}
?>
				</select>
				<span class="description required<?=!$form_valid && $fields_valid['type_id'] !== true ? ' error' : ''?>">Choose the type of your request.</span>
			</label>

			<label><span class="name">Priority</span>
				<select id="priority_id" name="priority_id">
<?php
				foreach ($ticket_priorities as $ticket_priority) {
					/*@var $ticket_priority kyTicketPriority */
					$selected = false;
					if (is_numeric($priority_id) && $priority_id == $ticket_priority->getId()) {
						$selected = true;
					}
?>
					<option<?=$selected ? ' selected' : ''?> value="<?=$ticket_priority->getId()?>"><?=$ticket_priority->getTitle()?></option>
<?php

				}
?>
				</select>
				<span class="description required<?=!$form_valid && $fields_valid['priority_id'] !== true ? ' error' : ''?>">Choose, what is the urgency of your request.</span>
			</label>
		</fieldset>

		<fieldset>
			<legend>Message Details</legend>

			<label><span class="name">Subject</span>
				<input type="text" value="<?=$subject?>" id="subject" name="subject">
				<span class="description required<?=!$form_valid && $fields_valid['subject'] !== true ? ' error' : ''?>">Provide the subject of your request.</span>
			</label>

			<label><span class="name">Contents</span>
				<textarea id="contents" name="contents" cols="30" rows="10"><?=$contents?></textarea>
				<span class="description required<?=!$form_valid && $fields_valid['contents'] !== true ? ' error' : ''?>">Provide the contents of your request.</span>
			</label>

		</fieldset>

		<input type="submit" name="submit_general" value="Submit">
	</form>
<?php
}

//we are submitting the ticket; we are doing this before rendering Custom Fields form because there is no way to load them before creating the ticket (see http://dev.kayako.com/browse/SWIFT-2391 for improvement request)
if ($render === 'submit') {
	//load the department we've chosen in Department form
	/** @var $department kyDepartment */
	$department = kyDepartment::get($department_id);

	//load default ticket status (based on DEFAULT_TICKET_STATUS_NAME constant defined at top of the file)
	$status_id = kyTicketStatus::getAll()->filterByTitle(DEFAULT_TICKET_STATUS_NAME)->first()->getId();

	//initialize default ticket status (loaded line before), priority (from General Information form) and type (from General Information form)
	kyTicket::setDefaults($status_id, $priority_id, $type_id);

	//create the ticket
	/** @var $ticket kyTicket */
	$ticket = kyTicket::createNewAuto($department, $creator_full_name, $creator_email, $contents, $subject)
		->create();

	//get ticket id
	$ticket_id = $ticket->getId();

	//load ticket custom fields
	$file_custom_field_present = true;
	$ticket_custom_fields = get_ticket_custom_fields($ticket, $file_custom_field_present);

	if (count($ticket_custom_fields) > 0) {
		//load custom field default values
		$custom_field_values = array();
		foreach ($ticket_custom_fields as $custom_fields) {
			foreach ($custom_fields as $custom_field) {
				/* @var $custom_field kyCustomField */
				$field_default_value = $custom_field->getDefinition()->getDefaultValue();
				if ($field_default_value === null)
					continue;

				$custom_field_values[$custom_field->getName()] = $field_default_value;
			}
		}
	}

	//if there are custom fields, render Custom Fields form; render summary otherwise
	$render = count($ticket_custom_fields) > 0 ? 'custom_fields' : 'summary';
}

//we are rendering Custom Fields form
if ($render === 'custom_fields') {
?>
	<form<?=$file_custom_field_present === true ? ' enctype="multipart/form-data"' : ''?> method="POST">
		<input type="hidden" name="page" id="page" value="custom_fields">

		<input type="hidden" name="ticket_id" id="ticket_id" value="<?=$ticket_id?>">
<?php
		//iterating over custom field groups
		foreach ($ticket_custom_fields as $custom_field_group_title => $custom_fields) {
?>
		<fieldset>
			<legend><?=$custom_field_group_title?></legend>

<?php
			//iterating over custom fields in group
			foreach ($custom_fields as $custom_field) {
				/* @var $custom_field kyCustomField */

				//assign to local variables to write less
				$custom_field_definition = $custom_field->getDefinition();
				$field_title = $custom_field_definition->getTitle();
				$field_name = $custom_field_definition->getName();
				$field_description = $custom_field_definition->getDescription();
				if (strlen(trim($field_description)) === 0) {
					$field_description = $field_title;
				}
				$field_required = $custom_field_definition->getIsRequired();
				$field_value = array_key_exists($field_name, $custom_field_values) ? $custom_field_values[$field_name] : null;
				$field_valid = array_key_exists($field_name, $fields_valid) ? $fields_valid[$field_name] : true;

				//render custom field based on its type
				switch ($custom_field->getType()) {
					case kyCustomFieldDefinition::TYPE_CUSTOM:
					case kyCustomFieldDefinition::TYPE_TEXT:
?>
						<label><span class="name"><?=$field_title?></span>
							<input type="text" value="<?=$field_value?>" id="<?=$field_name?>" name="<?=$field_name?>">
							<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_TEXTAREA:
?>
						<label><span class="name"><?=$field_title?></span>
							<textarea id="<?=$field_name?>" name="<?=$field_name?>" cols="30" rows="10"><?=$field_value?></textarea>
							<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_PASSWORD:
?>
						<label><span class="name"><?=$field_title?></span>
							<input type="password" value="<?=$field_value?>" id="<?=$field_name?>" name="<?=$field_name?>" autocomplete="off">
							<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_RADIO:
?>
						<div class="field"><span class="name"><?=$field_title?></span>
						<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
<?php
						//iterating over possible options
						foreach ($custom_field_definition->getOptions()->orderByDisplayOrder() as $field_option) {
							/*@var $field_option kyCustomFieldOption */
							$is_checked = false;
							if (($field_value === null && $field_option->getIsSelected()) || ($field_value == $field_option->getId())) {
								$is_checked = true;
							}
?>
							<label>
								<input type="radio" name="<?=$field_name?>" <?=$is_checked ? 'checked' : ''?> value="<?=$field_option->getId()?>">
								<span><?=$field_option->getValue()?></span>
							</label>
<?php

						}
?>
						</div>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_SELECT:
?>
						<label><span class="name"><?=$field_title?></span>
						<select id="<?=$field_name?>" name="<?=$field_name?>">
<?php
							//iterating over possible options
							foreach ($custom_field_definition->getOptions()->orderByDisplayOrder() as $field_option) {
								/*@var $field_option kyCustomFieldOption */
								$is_selected = false;
								if (($field_value === null && $field_option->getIsSelected()) || ($field_value == $field_option->getId())) {
									$is_selected = true;
								}
?>
								<option<?=$is_selected ? ' selected' : ''?> value="<?=$field_option->getId()?>"><?=$field_option->getValue()?></option>
<?php

							}
?>
						</select>
						<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_LINKED_SELECT:
?>
						<label><span class="name"><?=$field_title?></span>
						<select id="<?=$field_name?>" name="<?=$field_name?>">
<?php
							$custom_field_options = $custom_field_definition->getOptions();
							//iterating over options without parent option; render them as option groups (we are assuming that we can't select these options)
							foreach ($custom_field_options->filterByParentOptionId(null)->orderByDisplayOrder() as $field_parent_option) {
								/*@var $field_parent_option kyCustomFieldOption */
?>
								<optgroup label="<?=$field_parent_option->getValue()?>">
<?php
								//iterating over child options
								foreach ($custom_field_options->filterByParentOptionId($field_parent_option->getId())->orderByDisplayOrder() as $field_child_option) {
									/*@var $field_child_option kyCustomFieldOption */
									$is_selected = false;
									if (($field_value === null && $field_child_option->getIsSelected()) || ($field_value == $field_child_option->getId())) {
										$is_selected = true;
									}
?>
									<option<?=$is_selected ? ' selected' : ''?> value="<?=$field_child_option->getId()?>"><?=$field_child_option->getValue()?></option>
<?php
								}
?>
								</optgroup>
<?php
							}
?>
						</select>
						<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_CHECKBOX:
?>
						<div class="field"><span class="name"><?=$field_title?></span>
						<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
<?php
						//iterating over possible options
						foreach ($custom_field_definition->getOptions()->orderByDisplayOrder() as $field_option) {
							/*@var $field_option kyCustomFieldOption */
							$is_checked = false;
							if (($field_value === null && $field_option->getIsSelected()) || (is_array($field_value) && in_array($field_option->getId(), $field_value))) {
								$is_checked = true;
							}
?>
							<label>
								<input type="checkbox" name="<?=$field_name?>[]" <?=$is_checked ? 'checked' : ''?> value="<?=$field_option->getId()?>">
								<span><?=$field_option->getValue()?></span>
							</label>
<?php
						}
?>
						</div>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_MULTI_SELECT:
?>
						<label><span class="name"><?=$field_title?></span>
						<select id="<?=$field_name?>" name="<?=$field_name?>[]" multiple>
<?php
							//iterating over possible options
							foreach ($custom_field_definition->getOptions()->orderByDisplayOrder() as $field_option) {
								/*@var $field_option kyCustomFieldOption */
								$is_selected = false;
								if (($field_value === null && $field_option->getIsSelected()) || (is_array($field_value) && in_array($field_option->getId(), $field_value))) {
									$is_selected = true;
								}
?>
								<option<?=$is_selected ? ' selected' : ''?> value="<?=$field_option->getId()?>"><?=$field_option->getValue()?></option>
<?php

							}
?>
						</select>
						<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_DATE:
						//I'm lazy, render it as simple text field
?>
						<label><span class="name"><?=$field_title?></span>
							<input type="text" value="<?=$field_value?>" id="<?=$field_name?>" name="<?=$field_name?>">
							<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;

					case kyCustomFieldDefinition::TYPE_FILE:
?>
						<label><span class="name"><?=$field_title?></span>
							<input type="file" id="<?=$field_name?>" name="<?=$field_name?>">
							<span class="description<?=$field_required ? ' required' : ''?><?=!$form_valid && $field_valid !== true ? ' error' : ''?>"><?=!$form_valid && is_string($field_valid) ? $field_valid : $field_description?></span>
						</label>
<?php
					break;
				}
			}
?>
		</fieldset>
<?php
		}
?>


		<input type="submit" name="submit_custom_fields" value="Submit">
	</form>
<?php
}

//we are submitting ticket custom field values
if ($render === 'submit_custom_fields') {
	//iterating over custom field groups
	foreach ($ticket_custom_fields as $custom_fields) {
		//iterating over custom fields in group
		foreach ($custom_fields as $custom_field) {
			/* @var $custom_field kyCustomField */

			$field_value = array_key_exists($custom_field->getName(), $custom_field_values) ? $custom_field_values[$custom_field->getName()] : null;

			//set custom field value based on its type
			if ($custom_field->getType() === kyCustomFieldDefinition::TYPE_FILE) {
				/* @var $custom_field kyCustomFieldFile */
				if (array_key_exists($custom_field->getName(), $_FILES) && $_FILES[$custom_field->getName()]['error'] != UPLOAD_ERR_NO_FILE) {
					$file_data = $_FILES[$custom_field->getName()];
					$custom_field->setContentsFromFile($file_data['tmp_name'], $file_data['name']);
				}
			} else {
				$custom_field->setValue($field_value);
			}
		}
	}

	//send update request
	$ticket->updateCustomFields();

	//render summary
	$render = 'summary';
}

//we are rendering summary
if ($render === 'summary') {
?>
	<p>
	Your request was submitted sucessfully. The request number is <strong><?=$ticket->getDisplayId()?></strong>.
	</p>
<?php
}
?>

</body>
</html>
