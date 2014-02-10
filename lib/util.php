<?php


function render_page($subtitle, $headline, $view, $view_data) {
	$dir = dirname(__FILE__);
	require($dir . "/views/layout.php");
}


function render_page_and_exit($subtitle, $headline, $view, $view_data) {
	render_page($subtitle, $headline, $view, $view_data);
	exit;
}


function render_view($view, $data) {
	extract($data);
	require(dirname(__FILE__) . "/views/" . $view . ".php");
}


function request_method() {
	return $_SERVER['REQUEST_METHOD'];
}


function redirect_path($path) {
	global $SETTINGS;
	$url = $SETTINGS["base_url"] . $path;
	header("Location: $url");
	exit;
}


function value_or($value, $null_value) {
	if ($value === NULL) {
		return $null_value;
	} else {
		return $value;
	}
}


function option($value, $content) {
	?>
	<option value="<?php echo $value ?>"><?php echo $content ?></option>
	<?php
}


function name_id($name, $id_qualifier = NULL) {
	$id = ($id_qualifier === NULL) ? "" : $id_qualifier . "-";
	$id .= $name;
	return $id;
}


function name_value_id($name, $value, $id_qualifier = NULL) {
	$id = ($id_qualifier === NULL) ? "" : $id_qualifier . "-";
	$id .= $name . "-" . $value;
	return $id;
}


function radio_button($name, $value, $id_qualifier = NULL, $checked = FALSE) {
	$id = name_value_id($name, $value, $id_qualifier);
	$checked_html = $checked ? 'checked="checked" ' : '';
	?><input type="radio" name="<?php echo $name ?>" value="<?php echo $value ?>" id="<?php echo $id ?>" <?php echo $checked_html ?>/><?php
}


/*
  function checkbox($name_and_id) {
  ?>
  <input type="hidden" name="<?php echo $name_and_id ?>" value="off" />
  <input type="checkbox" name="<?php echo $name_and_id ?>" value="on" id="<?php echo $name_and_id ?>" />
  <?php
  }
 */


function multi_checkbox($name, $value, $id_qualifier = NULL) {
	$id = name_value_id($name, $value, $id_qualifier);
	?><input type="checkbox" name="<?php echo $name ?>[]" value="<?php echo $value ?>" id="<?php echo $id ?>" class="checkbox-inline" /><?php
}


function multi_element_label($name, $value, $content, $id_qualifier = NULL) {
	$for_id = name_value_id($name, $value, $id_qualifier);
	raw_label($for_id, $content);
}


function label($name, $content, $id_qualifier = NULL) {
	$for_id = name_id($name, $id_qualifier);
	raw_label($for_id, $content);
}


function raw_label($for_id, $content) {
	?><label for="<?php echo $for_id ?>"><?php echo $content ?></label><?php
}


function check_request_method($expected_method) {
	if (request_method() !== $expected_method) {
		respond_method_not_allowed([$expected_method]);
	}
}


function respond_method_not_allowed($allowed_methods) {
	header("Status: 405 Method Not Allowed");
	header("Allow: " . implode(", ", $allowed_methods));
	exit;
}


function check_get_multi_input_array($map, $param, &$valid_values) {
	if (!isset($map[$param])) {
// No checkboxes are checked
		return [];
	}
	$param = $map[$param];
	if (!is_array($param)) {
// Error - not an array
		return NULL;
	}
	foreach ($param as $value) {
		if (!isset($valid_values[$value])) {
// Invalid value
			return NULL;
		}
	}
	return $param;
}


/**
 * 
 * @param type $map
 * @param type $param
 * @param type $valid_values
 * @param type $allow_blank
 * @param type $unset_value Set to something, like the empty string, for radio buttons
 * @return null
 */
function check_get_enum($map, $param, &$valid_values, $allow_blank, $unset_value = NULL) {
	if (!isset($map[$param])) {
		// Missing input
		return $unset_value;
	}
	$param = $map[$param];
	if ($allow_blank && $param === '') {
		return $param;
	}
	if (!isset($valid_values[$param])) {
		// Invalid value
		return NULL;
	}
	return $param;
}


/**
 * An unset value is returned as the empty string 
 * @param type $map
 * @param type $param
 * @param type $valid_values
 * @return type
 */
function check_get_radio_enum($map, $param, &$valid_values) {
	return check_get_enum($map, $param, $valid_values, FALSE, '');
}


/**
 * An unset value is not accepted.
 * 
 * @param type $map
 * @param type $param
 * @param type $valid_values
 * @param type $allow_blank
 * @return type
 */
function check_get_select_enum($map, $param, &$valid_values, $allow_blank) {
	return check_get_enum($map, $param, $valid_values, $allow_blank, NULL);
}


function check_get_string($map, $param) {
	if (!isset($map[$param])) {
// Missing input
		return NULL;
	}
	$param = $map[$param];
	if (!is_string($param)) {
// Error - not a string
		return NULL;
	}
	return $param;
}


function check_get_indexed_array($map, $param, $length = NULL, $value_validator = NULL) {
	$param = check_get_array($map, $param, $length);
	if ($param === NULL) {
		return NULL;
	}
	$expected_key = 0;
	foreach ($param as $key => $value) {
		if (!is_int($key) || $key !== $expected_key) {
			return NULL;
		}
		if ($value_validator && !$value_validator($value)) {
			return NULL;
		}
		$expected_key++;
	}
	return $param;
}


function check_get_array($map, $param, $length = NULL, &$valid_indices = NULL, $unset_value = NULL) {
	if (!isset($map[$param])) {
		// Missing input
		return $unset_value;
	}
	$param = $map[$param];
	if (!is_array($param)) {
		// Error - not an array
		return NULL;
	}
	if ($length != NULL && count($param) != $length) {
		// Invalid length
		return NULL;
	}
	if ($valid_indices !== NULL) {
		foreach ($param as $key => $value) {
			if (!in_array($key, $valid_indices)) {
				return NULL;
			}
		}
	}
	return $param;
}


function check_get_array_length($map, $param, $unset_value = NULL) {
	if (!isset($map[$param])) {
		// Missing input
		return $unset_value;
	}
	$param = $map[$param];
	if (is_array($param)) {
		return count($param);
	}
	return 0;
}


function check_get_radio_array($map, $param, $length = NULL, &$valid_indices = NULL) {
	return check_get_array($map, $param, $length, $valid_indices, []);
}


function check_get_uint($map, $param, $allow_blank = FALSE, $min = NULL, $max = NULL, $unset_value = NULL) {
	$param = check_get_string($map, $param);
	if ($param === NULL) {
		return $unset_value;
	}
	if ($param === '') {
		if ($allow_blank) {
			return $param;
		} else {
			return NULL;
		}
	}
	if (!ctype_digit($param)) {
		return NULL;
	}
	$int = (int) $param;
	if (($min !== NULL && $int < $min) || ($max !== NULL && $int > $max)) {
		return NULL;
	}
	return $int;
}


function check_get_radio_uint($map, $param, $allow_blank = FALSE, $min = NULL, $max = NULL) {
	return check_get_uint($map, $param, $allow_blank, $min, $max, '');
}


function check_input() {
	$params = func_get_args();
	$i = 0;
	foreach ($params as $param) {
		if (is_null($param)) {
			render_unexpected_input_page_and_exit("Missing parameter or invalid type/value! $i");
		}
		$i++;
	}
}


function render_unexpected_input_page_and_exit($message = NULL) {
	$data = ['message' => $message];
	render_page_and_exit("Unexpected input", "Unexpected input", "unexpected_input", $data);
}


function array_filter_entries($array, $source_key_prefix, $keys) {
	$sub_array = [];
	foreach ($keys as $key) {
		$sub_array[$key] = $array[$source_key_prefix . $key];
	}
	return $sub_array;
}


function array_convert_numerics_to_ints(&$array) {
	foreach ($array as $key => $value) {
		if ($value !== NULL && $value !== '' && ctype_digit($value)) {
			$array[$key] = (int) $value;
		}
	}
}


function array_map_nulls($array, $null_replacement) {
	$out = [];
	foreach ($array as $key => $value) {
		if ($value === NULL) {
			$out[$key] = $null_replacement;
		} else {
			$out[$key] = $value;
		}
	}
	return $out;
//	$f = function($e) {
//				if ($e === NULL) {
//					return $null_replacement;
//				} else {
//					return $e;
//				}
//			};
//	return array_map($f, $array);
}


function nonnull_index($array, $key) {
	return isset($array[$key]) && $array[$key] !== NULL;
}

