<?php
require_once 'helpers.php';

$db = get_db_connection();
$result = run_count_query( $db );
$db->close();

var_dump( $result );
