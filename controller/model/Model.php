<?php

/**
 * Acme Project - Model Abstract Class
 * @file Model.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 8 Feb 2013 13:20 -0300
 */

if ( ! defined('DB_CONF') )
	die('Database Settings Not Defined.');

abstract class Model {

	/*
	 * Constants
	 */

	const EFAULT  = 1;
	const ENOLINK = 2;

	/*
	 * Class Variables
	 */

	// Protected

	protected static $pdo = null; // PDO Instance

	/*
	 * Class Methods
	 */

	// Protected

	protected static function getPDOInstance(&$error) {

		// initialize error
		$error = 0;

		if ( self::$pdo != null && is_object(self::$pdo) )
			return self::$pdo;

		try {
			$dns = sprintf('mysql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);
			$user = DB_USER;
			$password = DB_PASS;
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
			$pdo = new PDO($dns, $user, $password, $options);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$pdo = $pdo;
			return self::$pdo;
		}
		catch (Exception $e) {
			$error = self::ENOLINK;
			return null;
		}

	}

	/* Public */

	public static function UTF8StringLength($string, &$size)
	{

		try {

			if ( ! is_string($string) )
				throw new Exception('invalid argument type');

			$length = 0;
			$size = strlen($string);

			for ($i = 0, $j = 0; $i < $size; $i++) {
				$byte = ord($string[$i]);
				if ($byte < 0x80)
					$j = 0;
				else if (($byte & 0xE0) == 0xC0)
					$j = 1;
				else if (($byte & 0xF0) == 0xE0)
					$j = 2;
				else if (($byte & 0xF8) == 0xF0)
					$j = 3;
				else
					throw new Exception('invalid first byte');
				while ($j > 0)
				{
					$j--;
					$i++;
					if ($i >= $size || (($byte = ord($string[$i])) & 0xC0) != 0x80)
						throw new Exception('invalid byte sequence');
				}
				$length++;
			}

			return $length;

		}
		catch (Exception $e) {
			$size = 0;
			return -1;
		}

	}

}
