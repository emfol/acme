<?php

/**
 * Acme Project - User Model
 * @file User.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 7 Feb 2013 15:20 -0300
 */

require_once 'Model.php';

class User extends Model {

	/*
	 * Constants
	 */

	// error codes
	const EINVAL  = 16;
	const EEXIST  = 17;
	const ENOENT  = 18;

	// regular expressions
	const RE_USERNAME = '/^[a-zA-Z]\w{1,19}$/';
	const RE_PASSWORD = '/^[0-9A-F]{32}|[0-9a-f]{32}$/';
	const RE_EMAIL    = '/^[\w\-\.]+@[\w\-\.]+$/';

	/*
	 * Instance Variables
	 */

	protected $id       = 0;    // integer
	protected $username = null; // string
	protected $password = null; // string
	protected $email    = null; // string
	protected $status   = 0;    // integer
	protected $created  = 0;    // integer (unix timestamp)
	protected $error    = 0;    // internal error code

	/*
	 * Class Methods
	 */

	/* Public */

	// object (string, &integer)
	public static function userByUsername($username, &$error) {

		// initialize error
		$error = 0;

		if ( ! is_string($username) || preg_match(self::RE_USERNAME, $username) != 1 ) {
			$error = self::EINVAL;
			return null;
		}

		$pdo = self::getPDOInstance($error);
		if ($pdo == null)
			return null;

		try {
			$sql = 'SELECT id, username, password, email, status, UNIX_TIMESTAMP(created) AS created FROM users WHERE username = ? LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(1, $username, PDO::PARAM_STR);
			$stmt->execute();
			$stmt->bindColumn(1, $id, PDO::PARAM_INT);
			$stmt->bindColumn(2, $username, PDO::PARAM_STR);
			$stmt->bindColumn(3, $password, PDO::PARAM_STR);
			$stmt->bindColumn(4, $email, PDO::PARAM_STR);
			$stmt->bindColumn(5, $status, PDO::PARAM_INT);
			$stmt->bindColumn(6, $created, PDO::PARAM_INT);
			if ($stmt->fetch(PDO::FETCH_BOUND)) {
				$inst = new self(array(
						$id, $username, $password,
						$email, $status, $created
					)
				);
				if ($inst->getUserId() < 1) {
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

	/*
	 * Instance Methods
	 */

	/* Public */

	// integer (void)
	public function getUserId() {
		return $this->id;
	}

	// string (void)
	public function getUsername() {
		return $this->username;
	}

	// string (void)
	public function getEmail() {
		return $this->email;
	}

	// integer (void)
	public function getStatus() {
		return $this->status;
	}

	// integer (void)
	public function getCreationTime() {
		return $this->created;
	}

	// bool (string)
	public function setUsername($username) {
		if ( is_string($username) && preg_match(self::RE_USERNAME, $username) == 1 ) {
			$this->username = $username;
			return true;
		}
		return false;
	}

	// bool (string)
	public function setPassword($password) {
		if ( is_string($password) && strlen($password) >= 6 ) {
			$this->password = md5($password);
			return true;
		}
		return false;
	}

	// bool (string)
	public function setEmail($email) {
		if ( is_string($email) && preg_match(self::RE_EMAIL, $email) == 1 ) {
			$this->email = $email;
			return true;
		}
		return false;
	}

	// bool (integer)
	public function setStatus($status) {
		if ( is_int($status) ) {
			$this->status = $status;
			return true;
		}
		return false;
	}

	// bool (string)
	public function passwordMatches($password) {
		if ( is_string($password) && strlen($password) >= 6 ) {
			$password = md5($password);
			if ( $this->id > 0 && strcasecmp($this->password, $password) == 0 )
				return true;
		}
		return false;
	}

	// bool (void)
	public function save() {

		// initialize error
		$this->error = 0;

		if ( $this->username == null || $this->password == null || $this->email == null ) {
			$this->error = self::EINVAL;
			return false;
		}

		$pdo = self::getPDOInstance($error);
		if ($pdo == null) {
			$this->error = $error;
			return false;
		}

		try {
			$sql = $this->id > 0
				? 'UPDATE users SET username = :username, password = :password, email = :email, status = :status WHERE id = :id'
				: 'INSERT INTO users (username, password, email, status, created) VALUES (:username, :password, :email, :status, FROM_UNIXTIME(:created))';
			$stmt = $pdo->prepare($sql);
			if ($this->id > 0)
				$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
			else {
				$this->created = time();
				$stmt->bindValue(':created', $this->created, PDO::PARAM_INT);
			}
			$stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
			$stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
			$stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
			$stmt->bindValue(':status', $this->status, PDO::PARAM_INT);
			$stmt->execute();
			if ($this->id < 1) {
				if ($stmt->rowCount() != 1)
					throw new Exception('No Record Affected.', 1);
				$this->id = intval($pdo->lastInsertId());
			}
			return true;
		}
		catch (Exception $e) {
			$msg = $e->getMessage();
			$this->error = preg_match('/SQLSTATE\[23[0-9]{3}\]/', $msg) == 1 ? self::EEXIST : self::EFAULT;
			return false;
		}

	}

	public function getErrorCode() {
		return $this->error;
	}

	// void (array)
	public function __construct($data = null) {

		// parameter
		if ( ! is_array($data) || count($data) != 6 )
			return;

		// id
		if ( ! is_int($data[0]) || $data[0] < 1 )
			return;

		// username
		if ( ! is_string($data[1]) || preg_match(self::RE_USERNAME, $data[1]) != 1 )
			return;

		// password
		if ( ! is_string($data[2]) || preg_match(self::RE_PASSWORD, $data[2]) != 1 )
			return;

		// email
		if ( ! is_string($data[3]) || preg_match(self::RE_EMAIL, $data[3]) != 1 )
			return;

		// status
		if ( ! is_int($data[4]) )
			return;

		// created
		if ( ! is_int($data[5]) || $data[5] < 0 )
			return;

		// data
		$this->id       = $data[0];
		$this->username = $data[1];
		$this->password = $data[2];
		$this->email    = $data[3];
		$this->status   = $data[4];
		$this->created  = $data[5];

	}

}
