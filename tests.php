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
