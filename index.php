<?php

namespace GeoIP;

error_reporting(-1);

chdir(dirname(__FILE__));

require 'vendor/autoload.php';

try {
	$dbFile = 'db/geo.sdb';

	if ( !is_file($dbFile) ) {
		throw new Exception('Run init.php to initialise the database');
	}

	$ipAddress = isset($argv) && isset($argv[1]) ? $argv[1] : '';

	if ( !$ipAddress ) {
		echo 'Usage: php ' . basename(__FILE__) . ' [ip_address]' . "\n";

		exit(1);
	}

	$dbh = new \PDO('sqlite:' . $dbFile);

	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	echo json_encode(GeoIP::lookup($ipAddress, $dbh)) . "\n";
} catch ( \Exception $e ) {
	echo $e->getMessage() . "\n";

	exit(1);
}

exit(0);
