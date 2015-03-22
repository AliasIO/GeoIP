<?php

namespace GeoIP;

class Importer
{
	const EXCEPTION_ZIP_READ = 1;
	const EXCEPTION_ZIP_OPEN = 2;
	const EXCEPTION_CSV_READ = 3;

	public static function getFiles($url = '', array $filenames = []) {
		$files = [];

		foreach ( $filenames as $filename ) {
			$files[$filename] = null;
		}

		$zipFile = tempnam(sys_get_temp_dir(), 'geo');
		$zipDir  = tempnam(sys_get_temp_dir(), 'geo');

		unlink($zipDir);
		mkdir($zipDir);

		if ( ( $handle = @fopen($url, 'r') ) === false ) {
			throw new Exception('Failed to open file for reading', self::EXCEPTION_ZIP_READ);
		}

		file_put_contents($zipFile, $handle);

		fclose($handle);

		$zip = new \ZipArchive;

		if ( $zip->open($zipFile) ) {
			$zip->extractTo($zipDir);

			unlink($zipFile);

			$dir = dirname($zip->getNameIndex(0));

			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				// Strip top level directory from file path
				$filename = preg_replace('/^' . preg_quote($dir) . '\//', '', $zip->getNameIndex($i));

				if ( in_array($filename, $filenames) ) {
					$tmpFile = tempnam(sys_get_temp_dir(), 'geo');

					rename($zipDir . '/' . $dir . '/' . $filename, $tmpFile);

					$files[$filename] = $tmpFile;
				} else {
					unlink($zipDir . '/' . $dir . '/' . $filename);
				}
			}

			$zip->close();
		} else {
			throw new Exception('Failed to open ZIP archive', self::EXCEPTION_ZIP_OPEN);
		}

		return $files;
	}

	public static function csvRead($csv = '', callable $callback) {
		if ( ( $handle = @fopen($csv, 'r' ) ) === false ) {
			throw new Exception('Failed to open file for reading', self::EXCEPTION_CSV_READ);
		}

		$fields = [];

		while ( ( $row = fgetcsv($handle, 4096) ) !== false ) {
			if ( empty($fields) ) {
				$fields = $row;

				continue;
			}

			$values = [];

			foreach ( $row as $key => $value ) {
				$values[$fields[$key]] = $value;
			}

			call_user_func($callback, $values);
		}

		fclose($handle);
	}
}
