<?php

namespace GeoIP;

class GeoIP
{
	static function lookup($ipAddress = '', $dbh) {
		$long = ip2long($ipAddress);

		$sth = $dbh->prepare('
			SELECT
				cl.locale_code,
				cl.continent_code,
				cl.continent_name,
				cl.country_iso_code,
				cl.country_name
			FROM  country_blocks         AS cb
			INNER JOIN country_locations AS cl ON cb.geoname_id = cl.geoname_id
			WHERE
				:long BETWEEN long_start AND long_end
			LIMIT 1
			');

		$sth->bindParam('long', $long, \PDO::PARAM_INT);

		$sth->execute();

		$result = $sth->fetchObject();

		$result->ip_address = $ipAddress;

		return $result;
	}
}
