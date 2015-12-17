<?php namespace Afischoff;

class RubyEnvToPhp
{
	/**
	 * Loads the parameters in line by line, from a Ruby .env file
	 * @param string $filePath
	 */
	public static function load($filePath)
	{
		// Read file into an array of lines with auto-detected line endings
		$autodetect = ini_get('auto_detect_line_endings');
		ini_set('auto_detect_line_endings', '1');
		$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		ini_set('auto_detect_line_endings', $autodetect);

		foreach ($lines as $line) {
			// Disregard comments
			if (strpos(trim($line), '#') === 0) {
				continue;
			}
			// Only use non-empty lines that look like setters
			if (strpos($line, '=') !== false) {
				list($name, $value) = static::splitOutKeyValue($line);
				static::setEnvironmentVariable($name, $value);
			}
		}
	}

	/**
	 * Stores the variables in the $_ENV and $_SERVER globals
	 * @param string $name
	 * @param string $value
	 */
	public static function setEnvironmentVariable($name, $value)
	{
		putenv("{$name}={$value}");
		$_ENV[$name] = $value;
		$_SERVER[$name] = $value;
	}

	/**
	 * Converts Ruby .env line to key and value
	 * @param string $line
	 * @return array
	 */
	public static function splitOutKeyValue($line)
	{
		list($key, $val) = explode("=", $line);
		$val = trim($val);
		$val = str_replace(["\"", "'"], "", $val);

		$key = trim($key);
		$key = str_replace(["ENV[", "]", "\"", "'"], "", $key);

		return [$key, $val];
	}

	/**
	 * Converts Ruby db connection string into Laravel/Lumen DB environment variables
	 * @param string $dbstring
	 * @param null|string $connection - overrides the connection type
	 */
	public static function splitDbStringToVars($dbstring, $connection = null)
	{
		list($connectionFromString, $string) = explode('://', $dbstring);
		if (!$connection) {
			$connection = $connectionFromString;
		}
		$credsHost = explode('@', $string);
		list($user, $password) = explode(':', $credsHost[0]);
		list($hostraw, $db) = explode('/', $credsHost[1]);
		if (strpos($hostraw, ":") !== false) {
			list($host, $port) = explode(':', $hostraw);
		} else {
			$host = $hostraw;
			$port = 3306;
		}

		if (strpos($db, "?") !== false) {
			list($db, $query) = explode('?', $db);
		}

		static::setEnvironmentVariable('DB_CONNECTION', $connection);
		static::setEnvironmentVariable('DB_HOST', $host);
		static::setEnvironmentVariable('DB_PORT', $port);
		static::setEnvironmentVariable('DB_DATABASE', $db);
		static::setEnvironmentVariable('DB_USERNAME', $user);
		static::setEnvironmentVariable('DB_PASSWORD', $password);
		static::setEnvironmentVariable('DB_ADDITIONAL_PARAMS', $query);
	}
}
