<?php
require_once 'helpers.php';

$db = get_db_connection();
drop_table( $db );
run_sql_file( $db, 'create-table.sql' );
run_sql_file( $db, 'autoinc.sql' );
run_sql_file_each_line( $db, 'wp-posts-data-alt.sql' );
/* run_sql_file( $db, 'tsa-index.sql' ); */
$db->close();
