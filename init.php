<?php

namespace GeoIP;

error_reporting(-1);

chdir(dirname(__FILE__));

require 'vendor/autoload.php';

try {
	$url    = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country-CSV.zip';
	$dbFile = 'db/geo.sdb';

	@unlink($dbFile);

	$countryBlocks    = '';
	$countryLocations = '';

	$files = Importer::getFiles($url, ['GeoLite2-Country-Blocks-IPv4.csv', 'GeoLite2-Country-Locations-en.csv']);

	$dbh = new \PDO('sqlite:' . $dbFile);

	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	$sql = file_get_contents('db/schema.sql');

	$dbh->exec($sql);

	Importer::csvRead($files['GeoLite2-Country-Blocks-IPv4.csv'], function($values) use($dbh) {
		$cidr = explode('/', $values['network']);

		$longStart = ip2long($cidr[0]) & ( ( -1 << 32 - $cidr[1] ) );
		$longEnd   = ip2long($cidr[0]) + pow(2, 32 - $cidr[1]) - 1;

		$sth = $dbh->prepare('
			INSERT INTO country_blocks (
				network,
				long_start,
				long_end,
				geoname_id,
				registered_country_geoname_id,
				represented_country_geoname_id,
				is_anonymous_proxy,
				is_satellite_provider
			) VALUES (
				:network,
				:long_start,
				:long_end,
				:geoname_id,
				:registered_country_geoname_id,
				:represented_country_geoname_id,
				:is_anonymous_proxy,
				:is_satellite_provider
			)
			');

		$sth->bindParam('network',                        $values['network'],                        \PDO::PARAM_STR);
		$sth->bindParam('long_start',                     $longStart,                                \PDO::PARAM_INT);
		$sth->bindParam('long_end',                       $longEnd,                                  \PDO::PARAM_INT);
		$sth->bindParam('geoname_id',                     $values['geoname_id'],                     \PDO::PARAM_INT);
		$sth->bindParam('registered_country_geoname_id',  $values['registered_country_geoname_id'],  \PDO::PARAM_INT);
		$sth->bindParam('represented_country_geoname_id', $values['represented_country_geoname_id'], \PDO::PARAM_INT);
		$sth->bindParam('is_anonymous_proxy',             $values['is_anonymous_proxy'],             \PDO::PARAM_INT);
		$sth->bindParam('is_satellite_provider',          $values['is_satellite_provider'],          \PDO::PARAM_INT);

		$sth->execute();
	});

	Importer::csvRead($files['GeoLite2-Country-Locations-en.csv'], function($values) use($dbh) {
		$sth = $dbh->prepare('
			INSERT INTO country_locations (
				geoname_id,
				locale_code,
				continent_code,
				continent_name,
				country_iso_code,
				country_name
			) VALUES (
				:geoname_id,
				:locale_code,
				:continent_code,
				:continent_name,
				:country_iso_code,
				:country_name
			)
			');

		$sth->bindParam('geoname_id',       $values['geoname_id'],       \PDO::PARAM_INT);
		$sth->bindParam('locale_code',      $values['locale_code'],      \PDO::PARAM_STR);
		$sth->bindParam('continent_code',   $values['continent_code'],   \PDO::PARAM_STR);
		$sth->bindParam('continent_name',   $values['continent_name'],   \PDO::PARAM_STR);
		$sth->bindParam('country_iso_code', $values['country_iso_code'], \PDO::PARAM_STR);
		$sth->bindParam('country_name',     $values['country_name'],     \PDO::PARAM_STR);

		$sth->execute();
	});
} catch ( \Exception $e ) {
	echo $e->getMessage() . "\n";

	exit(1);
}

exit(0);
