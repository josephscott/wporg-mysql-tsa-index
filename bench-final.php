#!/opt/homebrew/opt/php@8.4/bin/php
<?php
ini_set( 'strict_types', '1' );

const ITERATIONS = 1_000_000;
const SELECT_SQL = "SELECT COUNT( 1 ) FROM wp_posts WHERE post_type = 'post' AND post_status NOT IN ( 'trash','auto-draft','inherit','request-pending','request-confirmed','request-failed','request-completed' ) AND post_author = 1";

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
$db->query( "SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_ZERO_DATE', '')" );
$db->query( "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'" );

/* $start = -hrtime( true ); */
/* $res = $db->query( "SELECT BENCHMARK( ".ITERATIONS.", (" . SELECT_SQL . ") )" ); */
$res = $db->query( SELECT_SQL );
echo $res->type;
/* $duration = $start + hrtime( true ); */
/* echo "Duration: " . number_format( $duration / 1e6, 2 ) . " ms\n"; */
$db->close();
