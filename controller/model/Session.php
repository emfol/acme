<?php

/**
 * Acme Project - Session Model
 * @file Session.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 7 Feb 2013 19:40 -0300
 */

require_once 'Model.php';

class Session extends Model {

	/*
	 * Constants
	 */

	// error codes
	const EINVAL  = 16;
	const ENOENT  = 17;

	// default timeout (4h)
	const DEFAULT_TIMEOUT = 14400;

	// regular expressions
	const RE_HASH       = '/^[\w\-]{64}$/';
	const RE_IDENTIFIER = '/^([0-9]{10})-([\w\-]{64})$/';

	/*
	 * Instance Variables
	 */

	protected $id      = 0;    // integer
	protected $hash    = null; // string
	protected $user_id = 0;    // string
	protected $created = 0;    // string
	protected $expires = 0;    // integer
	protected $error   = 0;    // internal error code

	/*
	 * Class Methods
	 */

	/* Protected */

	protected static function getHash() {
		static $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';
		$hash = str_repeat(' ', 64);
		for ($i = 0; $i < 64; $i++)
			$hash[$i] = $charset[mt_rand(0, 63)];
		return $hash;
	}

	/* Public */

	// object (string, &integer)
	public static function sessionWithIdentifier($identifier, &$error) {

		// initialize error
		$error = 0;

		if ( ! is_string($identifier) || preg_match(self::RE_IDENTIFIER, $identifier, $match) != 1 ) {
			$error = self::EINVAL;
			return null;
		}

		// match result
		$id   = intval($match[1]);
		$hash = $match[2];

		$pdo = self::getPDOInstance($error);
		if ($pdo == null)
			return null;

		try {
			$sql = 'SELECT id, hash, user_id, UNIX_TIMESTAMP(created) AS created, UNIX_TIMESTAMP(expires) AS expires FROM sessions WHERE id = :id AND hash = :hash LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
			$stmt->execute();
			$stmt->bindColumn(1, $id, PDO::PARAM_INT);
			$stmt->bindColumn(2, $hash, PDO::PARAM_STR);
			$stmt->bindColumn(3, $user_id, PDO::PARAM_INT);
			$stmt->bindColumn(4, $created, PDO::PARAM_INT);
			$stmt->bindColumn(5, $expires, PDO::PARAM_INT);
			if ($stmt->fetch(PDO::FETCH_BOUND)) {
				$inst = new self(array(
						$id, $hash, $user_id,
						$created, $expires
					)
				);
				if ($inst->getSessionId() < 1) {
					$error = self::EINVAL;
					return null;
				}
				return $inst;
			}
			else {
				$error = self::ENOENT;
				return null;
			}
		}
		catch (Exception $e) {
			$error = self::EFAULT;
			return null;
		}

	}

	// object (string, &integer)
	public static function sessionWithUserId($user_id, &$error) {

		// initialize error
		$error = 0;

		if ( ! is_int($user_id) || $user_id < 1 ) {
			$error = self::EINVAL;
			return null;
		}

		$pdo = self::getPDOInstance($error);
		if ($pdo == null)
			return null;

		// build hash
		$hash = self::getHash();
		$created = time();
		$expires = $created + (defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : self::DEFAULT_TIMEOUT);

		try {
			$sql = 'INSERT INTO sessions (hash, user_id, created, expires) VALUES (:hash, :user_id, FROM_UNIXTIME(:created), FROM_UNIXTIME(:expires))';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
			$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
			$stmt->bindValue(':created', $created, PDO::PARAM_INT);
			$stmt->bindValue(':expires', $expires, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() != 1)
				throw new Exception('No Record Affected.', 1);
			$id = intval($pdo->lastInsertId());
			$inst = new self(array(
					$id, $hash, $user_id,
					$created, $expires
				)
			);
			if ($inst->getSessionId() < 1) {
				$error = self::EINVAL;
				return null;
			}
			return $inst;
		}
		catch (Exception $e) {
			$error = self::EFAULT;
			return null;
		}

	}

	/*
	 * Instance Methods
	 */

	/* Public */

	// integer (void)
	public function getSessionId() {
		return $this->id;
	}

	// string (void)
	public function getIdentifier() {
		return sprintf('%010d-%s', $this->id, $this->hash);
	}

	// integer (void)
	public function getUserId() {
		return $this->user_id;
	}

	// integer (void)
	public function getCreationTime() {
		return $this->created;
	}

	// integer (void)
	public function getExpirationTime() {
		return $this->expires;
	}

	// bool (void)
	public function isExpired() {
		$now = time();
		if ($now < $this->expires)
			return false;
		return true;
	}

	// bool (void)
	public function close() {

		// initialize error
		$this->error = 0;

		if ($this->id < 1) {
			$this->error = self::EINVAL;
			return false;
		}

		$pdo = self::getPDOInstance($error);
		if ($pdo == null) {
			$this->error = $error;
			return false;
		}

		try {
			$sql = 'UPDATE sessions SET expires = CURRENT_TIMESTAMP WHERE id = :id';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
			$stmt->execute();
			return true;
		}
		catch (Exception $e) {
			$this->error = self::EFAULT;
			return false;
		}

	}

	public function getErrorCode() {
		return $this->error;
	}

	// void (array)
	public function __construct($data = null) {

		// parameter
		if ( ! is_array($data) || count($data) != 5 )
			return;

		// id
		if ( ! is_int($data[0]) || $data[0] < 1 )
			return;

		// hash
		if ( ! is_string($data[1]) || preg_match(self::RE_HASH, $data[1]) != 1 )
			return;

		// user_id
		if ( ! is_int($data[2]) || $data[2] < 1 )
			return;

		// created
		if ( ! is_int($data[3]) || $data[3] < 0 )
			return;

		// expires
		if ( ! is_int($data[4]) || $data[4] < 0 )
			return;

		// data
		$this->id      = $data[0];
		$this->hash    = $data[1];
		$this->user_id = $data[2];
		$this->created = $data[3];
		$this->expires = $data[4];

	}

}
