CREATE TABLE country_blocks (
	network                        TEXT,
	long_start                     INTEGER,
	long_end                       INTEGER,
	geoname_id                     INTEGER,
	registered_country_geoname_id  INTEGER,
	represented_country_geoname_id INTEGER,
	is_anonymous_proxy             INTEGER,
	is_satellite_provider          INTEGER
);

CREATE UNIQUE INDEX country_blocks_network    ON country_blocks ( network );
CREATE        INDEX country_blocks_geoname_id ON country_blocks ( geoname_id );

CREATE TABLE country_locations (
	geoname_id       INTEGER,
	locale_code      TEXT,
	continent_code   TEXT,
	continent_name   TEXT,
	country_iso_code TEXT,
	country_name     TEXT
);

CREATE UNIQUE INDEX country_locations_geoname_id ON country_locations ( geoname_id );
