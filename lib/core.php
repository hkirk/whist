<?php

define('GROUND_POINTS', 1);

define('MIN_TRICKS', 0);
define('MAX_TRICKS', 13);
define('MIN_BID_TRICKS', 7);
define('MAX_BID_TRICKS', MAX_TRICKS);
define('MIN_TIPS', 1);
define('MAX_TIPS', 3);

define('MIN_PLAYER_POSITION', 0);
define('MAX_PLAYER_POSITION', 3);
define('DEFAULT_PLAYERS', 4); // TODO rename this to something more expressive
define('MAX_PLAYERS', 8); // Just some reasonable number greater than 4 - can be changed
define('MIN_BID_WINNERS', 1);
define('MAX_BID_WINNERS', DEFAULT_PLAYERS);

define('BID_PREFIX_SOLO', 'solo-');
define('BID_PREFIX_NORMAL', 'normal-');

define('POINT_RULE_REALLYBAD', 'reallybad');
define('POINT_RULE_TIPS', 'tips');
define('POINT_RULE_SOLOTRICKS', 'solotricks');

$POINT_RULES = [
		POINT_RULE_REALLYBAD => ['name' => 'Really Bad', 'description' => NULL],
		POINT_RULE_SOLOTRICKS => ['name' => 'Solo tricks counts', 'description' => 'Some description'],
		POINT_RULE_TIPS => ['name' => 'Tips counts', 'description' => 'The base bid points depends on the number of tips.']
];

$TIPS_COUNT_MULTIPLIERS = [
		1 => 1.5,
		2 => 2,
		3 => 3
]; // Or something

$REALLYBAD_POINTS = 64;


define('NONE', "none");
define('SANS', "sans");
define('TIPS', "tips");
define('STRONGS', "strongs");
define('GOODS', "goods");
define('HALVES', "halves");

$ATTACHMENTS = [
		NONE => ['multiplier' => 1, 'name' => 'None'],
		SANS => ['multiplier' => 1.5, 'name' => 'Sans', 'description' => 'No trump suit.'],
		TIPS => ['multiplier' => 1.5, 'name' => 'Tips'],
		STRONGS => ['multiplier' => 1.5, 'name' => 'Strongs', 'description' => 'Spades are trump.'],
		GOODS => ['multiplier' => 2, 'name' => 'Goods', 'description' => 'Clubs are trump.'],
		HALVES => ['multiplier' => 2, 'name' => 'Halves', 'description' => 'The mate chooses the trump suit (The mate suit is illegal).']
];



$ATTACHMENT_KEY_ORDER = [
		NONE, SANS, TIPS, STRONGS, GOODS, HALVES
];

$OPTIONAL_ATTACHMENTS = [
		SANS => &$ATTACHMENTS[SANS],
		TIPS => &$ATTACHMENTS[TIPS],
		STRONGS => &$ATTACHMENTS[STRONGS],
		HALVES => &$ATTACHMENTS[HALVES]
];

$OPTIONAL_ATTACHMENT_KEYS_ORDER = [
		SANS, TIPS, STRONGS, HALVES
];

$REQUIRED_ATTACHMENT_KEYS_ORDER = [
		NONE, GOODS
];

define('SOLO_SOLO', "solo");
define('SOLO_CLEANSOLO', "cleansolo");
define('SOLO_TABLE', "table");
define('SOLO_CLEANTABLE', "cleantable");
define('FIRST_SOLO_GAME_BEATS', 9);

$SOLO_GAMES = [
		SOLO_SOLO => ['multiplier' => 1, 'max_tricks' => 1, 'name' => 'Solo'],
		SOLO_CLEANSOLO => ['multiplier' => 2, 'max_tricks' => 0, 'name' => 'Clean Solo'],
		SOLO_TABLE => ['multiplier' => 4, 'max_tricks' => 1, 'name' => 'Table Solo'],
		SOLO_CLEANTABLE => ['multiplier' => 8, 'max_tricks' => 0, 'name' => 'Clean Table Solo']
];

$SOLO_GAME_KEY_ORDER = [
		SOLO_SOLO, SOLO_CLEANSOLO, SOLO_TABLE, SOLO_CLEANTABLE
];


function build_valid_player_position_input_values($n_players) {
	$valid_player_positions = [];
	for ($i = 0; $i < $n_players; $i++) {
		$valid_player_positions[] = "$i"; // Input values are strings
	}
	return $valid_player_positions;
}


function build_valid_bid_input_values() {
	global $SOLO_GAME_KEY_ORDER;
	$valid_bid_values = [];
	for ($tricks = MIN_BID_TRICKS; $tricks <= MAX_TRICKS; $tricks++) {
		$valid_bid_values[BID_PREFIX_NORMAL . $tricks] = TRUE;
	}
	foreach ($SOLO_GAME_KEY_ORDER as $solo_game_key) {
		$valid_bid_values[BID_PREFIX_SOLO . $solo_game_key] = TRUE;
	}
	return $valid_bid_values;
}


function build_valid_attachment_input_values() {
	global $ATTACHMENTS;
	$valid_attachment_values = $ATTACHMENTS;
	unset($valid_attachment_values[TIPS]);
	for ($tips = MIN_TIPS; $tips <= MAX_TIPS; $tips++) {
		$valid_attachment_values[TIPS . "-" . $tips] = TRUE;
	}
	$valid_attachment_values[''] = TRUE; // The "null" / "solo" value
	return $valid_attachment_values;
}


//
// The points calculated be these methods are the bidder points
//

function normal_game_bid_base_points($point_rules, $bid_tricks, $bid_attachment_key = NULL, $tips = NULL) {
	if ($bid_attachment_key === NULL) {
		$bid_attachment_key = NONE;
	}
	if ($bid_attachment_key === TIPS && $tips !== NULL && in_array(POINT_RULE_TIPS, $point_rules)) {
		global $TIPS_COUNT_MULTIPLIERS;
		$attachment_multiplier = $TIPS_COUNT_MULTIPLIERS[$tips];
	} else {
		global $ATTACHMENTS;
		$bid_attachment = $ATTACHMENTS[$bid_attachment_key];
		$attachment_multiplier = $bid_attachment['multiplier'];
	}
	return GROUND_POINTS * pow(2, $bid_tricks - 7) * $attachment_multiplier;
}


function normal_game_points($point_rules, $bid_tricks, $bid_attachment_key, $tricks, $tips = NULL) {
	$displacement = $bid_tricks > $tricks ? 0 : 1; // Lost?
	$multiplier = $bid_tricks > $tricks ? 2 : 1; // Lost?
	$bid_base_points = normal_game_bid_base_points($point_rules, $bid_tricks, $bid_attachment_key, $tips);
	$reallybad_points = get_really_bad_points($point_rules, $tricks);
	printf("mul: %s, base: %s", $multiplier, $bid_base_points);
	return $bid_base_points * ($tricks - $bid_tricks + $displacement) * $multiplier + $reallybad_points;
}


function solo_game_bid_base_points($point_rules, $solo_game) {
	// pow(2, 9-7)*1.5 = 6
	return GROUND_POINTS * 6 * $solo_game['multiplier'];
}


function solo_game_points($point_rules, $solo_game, $tricks) {
	$max_tricks = $solo_game['max_tricks'];
	$lost = $max_tricks < $tricks;
	if (in_array(POINT_RULE_SOLOTRICKS, $point_rules)) {
		$justified_tricks = $tricks;
	} else {
		// Number of tricks does not count
		$justified_tricks = $lost ? $max_tricks + 1 : $max_tricks;
	}
	$displacement = $lost ? 0 : 1;
	$multiplier = $lost ? 2 : 1;
	$bid_base_points = solo_game_bid_base_points($point_rules, $solo_game);
	$reallybad_points = -get_really_bad_points($point_rules, $tricks); // negate points
	return $bid_base_points * ($max_tricks - $justified_tricks + $displacement) * $multiplier + $reallybad_points;
}


// TODO lower limit for solo games
function get_really_bad_points($point_rules, $tricks) {
	error_log("Really bad, tricks: " . $tricks);
	if ($tricks === MAX_TRICKS && in_array(POINT_RULE_REALLYBAD, $point_rules)) {
		global $REALLYBAD_POINTS;
		return $REALLYBAD_POINTS;
	} else {
		return 0;
	}
}

