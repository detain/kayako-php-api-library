<?php
/**
 * Sample form for creating new user.
 *
 * Change following values according to your installation:
 * BASE_URL: Kayako REST API URL (Admin CP -> REST API -> API Information)
 * API_KEY: Kayako API Key (Admin CP -> REST API -> API Information)
 * SECRET_KEY: Kayako Secret Key (Admin CP -> REST API -> API Information)
 * DEBUG: true to output HTTP requests and responses to PHP error log, false to disable debugging
 * USER_GROUP_TITLE: Name of the group the user should be added to.
 * SEND_WELCOME_EMAIL: True to send welcome email to the user.
 *
 * Consult further comments for configuration and other explanations.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.240
 * @package Example
 */

define('BASE_URL', '<API URL>');
define('API_KEY', '<API key>');
define('SECRET_KEY', '<Secret key>');
define('DEBUG', true);
define('USER_GROUP_TITLE', 'Registered');
define('SEND_WELCOME_EMAIL', false);

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
 * Returns possible timezones as:
 * array(
 * 	'<timezone name>' => <true for DST, false otherwise>,
 * 	...
 * )
 *
 * @return array
 */
function get_timezones() {
	$timezones = array();

	foreach (timezone_abbreviations_list() as $tz_abbreviation) {
		foreach ($tz_abbreviation as $timezone) {
			if (strlen($timezone['timezone_id']) === 0)
				continue;

			$timezones[$timezone['timezone_id']] = $timezone['dst'];
		}
	}

	ksort($timezones);

	return $timezones;
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
	<title>Kayako user create form - sample</title>

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
		form div.label {
			display: block;
			float: left;
			clear: left;
			margin: 10px 0;
			font-size: 12px;
		}

			form label span.name,
			form div.label span.name {
				display: block;
				float: left;
				width: 80px;
				margin-right: 20px;
				font-weight: bold;
			}

			form label span.description,
			form div.label span.description {
				display: block;
				float: right;
				width: 350px;
				margin-left: 20px;
				font-weight: normal;
				font-style: italic;
			}

				form label label,
				form div.label label {
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

				form input[type="radio"],
				form input[type="checkbox"] {
					width: 20px;
					margin-right: 260px;
				}

				form label label input[type="radio"],
				form label label input[type="checkbox"],
				form div.label label input[type="radio"],
				form div.label label input[type="checkbox"] {
					width: 20px;
					margin-left: 100px;
				}
					form label label label input[type="radio"],
					form label label label input[type="checkbox"],
					form div.label label label input[type="radio"],
					form div.label label label input[type="checkbox"] {
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
	<script type="text/javascript">
	function timezoneChanged() {
		var enableDSTElement = document.getElementById('enable_dst');
		var timezoneElement = document.getElementById('timezone');
		var timezoneSelectedOption = timezoneElement.options[timezoneElement.selectedIndex];
		var enabledDST = timezoneSelectedOption.getAttribute('enabledst');
		enableDSTElement.checked = enabledDST == 'enable';
	}
	</script>
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
	case 'user': //we are submitting Department form

		$salutation = get_post_value('salutation', $form_valid, $fields_valid, false);
		$full_name = get_post_value('full_name', $form_valid, $fields_valid);
		$password = get_post_value('password', $form_valid, $fields_valid);
		$password_repeat = get_post_value('password_repeat', $form_valid, $fields_valid);
		$email = get_post_value('email', $form_valid, $fields_valid);
		$user_organization_id = get_post_value('user_organization_id', $form_valid, $fields_valid, false);
		$designation = get_post_value('designation', $form_valid, $fields_valid, false);
		$phone = get_post_value('phone', $form_valid, $fields_valid, false);
		$timezone = get_post_value('timezone', $form_valid, $fields_valid, false);
		$enable_dst = get_post_value('enable_dst', $form_valid, $fields_valid, false);
		$enable_dst = $enable_dst === 'enable' ? true : false;

		if ($password !== $password_repeat) {
			$form_valid = false;
			$fields_valid['password_repeat'] = 'Password do not match.';
		}

		//if valid, render General Information form, re-render Department form if it's invalid
		$render = $form_valid ? 'submit' : 'user';
	break;

	default:
		$salutation = null;
		$full_name = null;
		$password = null;
		$password_repeat = null;
		$email = null;
		$user_organization_id = null;
		$designation = null;
		$phone = null;
		$timezone = null;
		$enable_dst = false;

		//render User form as the first page
		$render = 'user';
	break;
}

//we are rendering User form
if ($render === 'user') {
	$user_organizations = kyUserOrganization::getAll()->filterByType(kyUserOrganization::TYPE_SHARED);
	$salutations = array('Mr.', 'Ms.', 'Mrs.', 'Dr.');

	$timezones = get_timezones();
?>
	<form method="POST">
		<input type="hidden" name="page" id="page" value="user">

		<fieldset>
			<legend>General Information</legend>

			<div class="label"><span class="name">Full Name</span>
				<select id="salutation" name="salutation" style="width: 60px;">
					<option value=""></option>
<?php
				foreach ($salutations as $salutation_option) {
					$selected = false;
					if ($salutation == $salutation_option) {
						$selected = true;
					}
?>
					<option<?=$selected ? ' selected' : ''?> value="<?=$salutation_option?>"><?=$salutation_option?></option>
<?php

				}
?>
				</select>
				<input type="text" value="<?=$full_name?>" id="full_name" name="full_name" style="width: 220px;">
				<span class="description required<?=!$form_valid && $fields_valid['full_name'] !== true ? ' error' : ''?>">Provide your first and last name.</span>
			</div>

			<label><span class="name">Email</span>
				<input type="text" value="<?=$email?>" id="email" name="email">
				<span class="description required<?=!$form_valid && $fields_valid['email'] !== true ? ' error' : ''?>">Provide your e-mail address.</span>
			</label>

			<label><span class="name">Password</span>
				<input type="password" value="<?=$password?>" id="password" name="password" autocomplete="off">
				<span class="description required<?=!$form_valid && $fields_valid['password'] !== true ? ' error' : ''?>">Choose your passwrord.</span>
			</label>

			<label><span class="name">Password (repeat)</span>
				<input type="password" value="<?=$password_repeat?>" id="password_repeat" name="password_repeat" autocomplete="off">
				<span class="description required<?=!$form_valid && $fields_valid['password_repeat'] !== true ? ' error' : ''?>">Type your password again.</span>
			</label>
		</fieldset>

		<fieldset>
			<legend>Additional Information</legend>

			<label><span class="name">Organization</span>
				<select id="user_organization_id" name="user_organization_id">
					<option value=""></option>
<?php
				foreach ($user_organizations as $user_organization) {
					/*@var $user_organization kyUserOrganization */
					$selected = false;
					if (is_numeric($user_organization_id) && $user_organization_id == $user_organization->getId()) {
						$selected = true;
					}
?>
					<option<?=$selected ? ' selected' : ''?> value="<?=$user_organization->getId()?>"><?=$user_organization->getName()?></option>
<?php

				}
?>
				</select>
				<span class="description <?=!$form_valid && $fields_valid['user_organization_id'] !== true ? ' error' : ''?>">Choose your organization.</span>
			</label>

			<label><span class="name">Title/Position</span>
				<input type="text" value="<?=$designation?>" id="designation" name="designation">
				<span class="description <?=!$form_valid && $fields_valid['designation'] !== true ? ' error' : ''?>">Type your title or position in the organization.</span>
			</label>

			<label><span class="name">Phone number</span>
				<input type="text" value="<?=$phone?>" id="phone" name="phone">
				<span class="description <?=!$form_valid && $fields_valid['phone'] !== true ? ' error' : ''?>">Type your phone number.</span>
			</label>
		</fieldset>

		<fieldset>
			<legend>Preferences</legend>

			<label><span class="name">Timezone</span>
				<select id="timezone" name="timezone" onChange="timezoneChanged();">
					<option value="">-- Default Time Zone --</option>
<?php
				foreach ($timezones as $timezone_id => $timezone_enable_dst) {
					$selected = false;
					if ($timezone == $timezone_id) {
						$selected = true;
					}
?>
					<option<?=$selected ? ' selected' : ''?> value="<?=$timezone_id?>" enabledst="<?=$timezone_enable_dst ? 'enable' : 'disable'?>"><?=$timezone_id?></option>
<?php

				}
?>
				</select>
				<span class="description <?=!$form_valid && $fields_valid['timezone'] !== true ? ' error' : ''?>">Choose your timezone.</span>
			</label>

			<label><span class="name">Enable DST</span>
				<input type="checkbox" value="enable" <?=$enable_dst ? 'checked' : ''?> id="enable_dst" name="enable_dst">
				<span class="description <?=!$form_valid && $fields_valid['enable_dst'] !== true ? ' error' : ''?>">Enable Daylight Saving Time.</span>
			</label>
		</fieldset>

		<input type="submit" name="submit_user" value="Submit">
	</form>
<?php
}

//we are creating the user
if ($render === 'submit') {
	//load default user group
	$user_group = kyUserGroup::getAll()->filterByTitle(USER_GROUP_TITLE)->first();

	//create the user
	$user = kyUser::createNew($full_name, $email, $user_group, $password)
		->setEnableDST($enable_dst)
		->setSendWelcomeEmail(SEND_WELCOME_EMAIL);

	if (is_numeric($user_organization_id)) {
		$user->setUserOrganizationId($user_organization_id);
	}

	if (strlen($salutation)) {
		$user->setSalutation($salutation);
	}

	if (strlen($salutation)) {
		$user->setDesignation($designation);
	}

	if (strlen($salutation)) {
		$user->setPhone($phone);
	}

	if (strlen($salutation)) {
		$user->setTimezone($timezone);
	}

	$user->create();

	$render = 'summary';
}

//we are rendering summary
if ($render === 'summary') {
?>
	<p>
	Your request was submitted sucessfully. The user was created.
	</p>
<?php
}
?>

</body>
</html>