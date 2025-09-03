<?php
require_once 'helpers.php';

$db = get_db_connection();
$index_used = run_explain_count( $db );
$db->close();

echo $index_used;