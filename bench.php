#!/opt/homebrew/opt/php@8.4/bin/php -d opcache.enable_cli=true -d opcache.jit=on -d opcache.jit_buffer_size=50M
<?php
ini_set( 'strict_types', '1' );

const ITERATIONS = 1_000;

function test1_setup($db) {
	echo "\n***** Test: 1 *****\n";
	echo "> NO TSA index\n";
	echo "\n";
	run_sql_file( $db, 'create-table.sql' );
	run_sql_file( $db, 'autoinc.sql' );
	run_sql_file( $db, 'wp-posts-data.sql' );
}
function test1($db) {
	run_explain_count( $db );
}

function test2_setup($db) {
	echo "\n***** Test: 2 *****\n";
	echo "> New TSA index immediately after create table, before insert\n";
	echo "\n";
	run_sql_file( $db, 'create-table.sql' );
	run_sql_file( $db, 'autoinc.sql' );
	run_sql_file( $db, 'tsa-index.sql' );
	run_sql_file( $db, 'wp-posts-data.sql' );
}
function test2($db) {
	run_explain_count( $db,  );
}

function test2b_setup($db) {
	echo "\n***** Test: 2b *****\n";
	echo "> New TSA index immediately after create table, before insert (alt data set)\n";
	echo "\n";
	run_sql_file( $db, 'create-table.sql' );
	run_sql_file( $db, 'autoinc.sql' );
	run_sql_file( $db, 'tsa-index.sql' );
	run_sql_file_each_line( $db, 'wp-posts-data-alt.sql' );
}
function test2b($db) {
	run_explain_count( $db );
}

function test3_setup($db) {
	echo "\n***** Test: 3 *****\n";
	echo "> New TSA index immediately after create table, then analyze, before insert\n";
	echo "\n";
	run_sql_file( $db, 'create-table.sql' );
	run_sql_file( $db, 'autoinc.sql' );
	run_sql_file( $db, 'tsa-index.sql' );
	run_sql_file( $db, 'analyze-wp-posts.sql' );
	run_sql_file( $db, 'wp-posts-data.sql' );
}
function test3($db) {
	run_explain_count( $db );
}

function test4_setup($db) {
	echo "\n***** Test: 4 *****\n";
	echo "> New TSA index after inserting data\n";
	echo "\n";
	run_sql_file( $db, 'create-table.sql' );
	run_sql_file( $db, 'autoinc.sql' );
	run_sql_file( $db, 'wp-posts-data.sql' );
	run_sql_file( $db, 'tsa-index.sql' );
}
function test4($db) {
	run_explain_count( $db );
}

//
// *****
// Helper Functions
// *****
//

function time_tests( array $tests ) {
	$db = new mysqli(
		'127.0.0.1',
		'test_user',
		'test_pass',
		'test_db',
		6330
	);

	if ( $db->connect_error ) {
		echo "Connection error: {$db->connect_error}\n";
		exit( 1 );
	}

	try {
		$db->query( "SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_ZERO_DATE', '')" );
		$db->query( "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'" );
		show_versions( $db );


		/*
		foreach ( $tests as $setup ) {
			$res = $db->query( "SELECT BENCHMARK( 1000000, (" . SELECT_SQL . ") )" )->fetch_column(0);
			echo $res;
		}
		*/

		foreach ( $tests as $setup ) {
			$i = ITERATIONS;
			drop_table( $db );
			$setup( $db );
			$start = -hrtime( true );
			while ( $i-- ) {
				run_select_count( $db );
			}
			$duration = $start + hrtime( true );
			echo "Duration: " . number_format( $duration / 1e6, 2 ) . " ms\n";
		}
	} finally {
		drop_table( $db );
		$db->close();
	}
}

function show_versions( $db ) {
	echo "PHP version: " . PHP_VERSION . "\n";

	$result = $db->query( "SELECT VERSION()" );
	$row = $result->fetch_assoc();
	echo "MySQL version: {$row['VERSION()']}\n";
}

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

function run_sql_file_each_line( $db, $sql_file ) {
	echo "running: {$sql_file}\n";
	$sql = file_get_contents( $sql_file );
	$lines = explode( "\n", $sql );
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( empty( $line ) || substr( $line, 0, 2 ) === '--' ) {
			continue;
		}
		$db->query( $line );
	}
}

const EXPLAIN_SQL = "EXPLAIN SELECT COUNT( 1 ) FROM wp_posts WHERE post_type = 'post' AND post_status NOT IN ( 'trash','auto-draft','inherit','request-pending','request-confirmed','request-failed','request-completed' ) AND post_author = 1";

const SELECT_SQL = "SELECT COUNT( 1 ) FROM wp_posts WHERE post_type = 'post' AND post_status NOT IN ( 'trash','auto-draft','inherit','request-pending','request-confirmed','request-failed','request-completed' ) AND post_author = 1";

function run_explain_count( $db ) {
	$result = $db->query( EXPLAIN_SQL );
	$row = $result->fetch_assoc();
	echo "Result: {$row['key']}\n";
	print_r( $row );
}

function run_select_count( $db ) {
	$result = $db->query( SELECT_SQL );
	$res = $result->fetch_column(0);
	/* print_r( $res ); */
	/* echo "Result: {$res}\n"; */
}

time_tests([
 	'test1_setup',
	'test2_setup',
	'test2b_setup',
	'test3_setup',
	'test4_setup',
]);
