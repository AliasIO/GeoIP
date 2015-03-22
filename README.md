# GeoIP

Returns country information in JSON format for a given IP address.

Usage:

```
php index.php <ip_address>

```

Example output:

```
$ php index.php 63.245.215.20
{"locale_code":"en","continent_code":"NA","continent_name":"North America","country_iso_code":"US","country_name":"United States","ip_address":"63.245.215.20"}
```

Using Docker:

```
docker run alias/geoip <ip_address>
```
