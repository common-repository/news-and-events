<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$values = $_POST['newsandeventsOptionsForm'];

foreach(ne_Options::$optionsDef as $option) {
	$op = ne_Options::get($option['name']);

	if(isset($values[$option['name']])) {
		$val = $values[$option['name']];
	}
	else {
		$val = $op->value;
	}

	if($option['type'] == 'checkbox') {
		$op->set_on($values[$option['name']]);
	}
	else {
		$op->value = $values[$option['name']];
	}
	
	$op->save();
}
ne_redirect("pages/optionsCtrlAdmin_index.php");



?>
