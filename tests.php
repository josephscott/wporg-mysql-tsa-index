<?php
$db = new mysqli(
	'127.0.0.1',
	'test_user',
	'test_pass',
	'test_db',
	6330
);

if ( $db->connect_error ) {
	echo "Connection error: " . $db->connect_error . "\n";
	exit( 1 );
}

$db->query( "SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_ZERO_DATE', '')" );
$db->query( "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'" );

echo "\n***** Test: 1 *****\n";
echo "> NO TSA index\n";
echo "\n";

drop_table( $db );
run_sql_file( $db, 'create-table.sql' );
run_sql_file( $db, 'autoinc.sql' );
run_sql_file( $db, 'wp-posts-data.sql' );
run_explain_count( $db, $argv );
echo "\n";

echo "\n***** Test: 2 *****\n";
echo "> New TSA index immediately after create table, before insert\n";
echo "\n";

drop_table( $db );
run_sql_file( $db, 'create-table.sql' );
run_sql_file( $db, 'autoinc.sql' );
run_sql_file( $db, 'tsa-index.sql' );
run_sql_file( $db, 'wp-posts-data.sql' );
run_explain_count( $db, $argv );
echo "\n";

echo "\n***** Test: 3 *****\n";
echo "> New TSA index immediately after create table, then analyze, before insert\n";
echo "\n";

drop_table( $db );
run_sql_file( $db, 'create-table.sql' );
run_sql_file( $db, 'autoinc.sql' );
run_sql_file( $db, 'tsa-index.sql' );
run_sql_file( $db, 'analyze-wp-posts.sql' );
run_sql_file( $db, 'wp-posts-data.sql' );
run_explain_count( $db, $argv );
echo "\n";

echo "\n***** Test: 4 *****\n";
echo "> New TSA index after inserting data\n";
echo "\n";

drop_table( $db );
run_sql_file( $db, 'create-table.sql' );
run_sql_file( $db, 'autoinc.sql' );
run_sql_file( $db, 'wp-posts-data.sql' );
run_sql_file( $db, 'tsa-index.sql' );
run_explain_count( $db, $argv );
echo "\n";

//
// *****
// Helper Functions
// *****
//

function drop_table( $db ) {
	echo "dropping table: wp_posts\n";
	$sql = "DROP TABLE IF EXISTS `wp_posts`";
	$db->query( $sql );
}

function run_sql_file( $db, $sql_file ) {
	echo "running: {$sql_file}\n";
	$sql = file_get_contents( $sql_file );
	$db->query( $sql );
}

function run_explain_count( $db, $argv ) {
	$sql = "EXPLAIN SELECT COUNT( 1 )
            FROM wp_posts
            WHERE post_type = 'post'
            AND post_status NOT IN ( 'trash','auto-draft','inherit','request-pending','request-confirmed','request-failed','request-completed' )
            AND post_author = 1";

	$result = $db->query( $sql );
	$row = $result->fetch_assoc();

	echo "Using index: {$row['key']}\n";

	if ( isset( $argv[1] ) && $argv[1] === '-v' ) {
		print_r( $row );
	}
}
