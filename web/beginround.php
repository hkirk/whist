<?php

require("../lib/lib.php");


$id = db_create_normal_round(87, 11, 'halves', 2, NULL);
$data = db_end_normal_round_get_data($id);
printf("Data: "); var_dump($data);

$_db->commit();

$id = db_create_solo_round(87, 'cleantable', array(0,3));


list($solo_type, $bid_winner_positions) = db_end_solo_round_get_data($id);

printf("Solo type: "); var_dump($solo_type);
printf("Bid winner position: "); var_dump($bid_winner_positions);