<?php
ini_set('strict_types', 1);

function get_db_connection() {
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

	return $db;
}

function drop_table( $db ) {
	$sql = "DROP TABLE IF EXISTS `wp_posts`";
	$db->query( $sql );
}

function run_sql_file( $db, $sql_file ) {
	$sql = file_get_contents( $sql_file );
	$db->query( $sql );
}

function run_sql_file_each_line( $db, $sql_file ) {
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

const QUERY = "SELECT COUNT( 1 )
            FROM wp_posts
            WHERE post_type = 'post'
            AND post_status NOT IN ( 'trash','auto-draft','inherit','request-pending','request-confirmed','request-failed','request-completed' )
            AND post_author = 1";

function run_count_query( $db ) {
	$sql =
		// "EXPLAIN " .
		QUERY;

	$result = $db->query( $sql );
	$row = $result->fetch_assoc();

	return $row;
}
