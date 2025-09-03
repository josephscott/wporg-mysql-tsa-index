<?php
require_once 'helpers.php';

$db = get_db_connection();
drop_table( $db );
$db->close();