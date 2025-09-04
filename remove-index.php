#!/opt/homebrew/opt/php@8.4/bin/php
<?php
ini_set('strict_types', 1);

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
try { $db->query('DROP INDEX type_status_author on wp_posts'); } catch ( Exception $e ) {}
try { $db->query('DROP INDEX type_author_status on wp_posts'); } catch ( Exception $e ) {}
try { $db->query('DROP INDEX author_type_status on wp_posts'); } catch ( Exception $e ) {}
try { $db->query('DROP INDEX type_status_post_date_gmt on wp_posts'); } catch ( Exception $e ) {}
try { $db->query('DROP INDEX type_status_modified_date_gmt on wp_posts'); } catch ( Exception $e ) {}
