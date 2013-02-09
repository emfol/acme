<?php

/**
 * Acme Project - Product Model
 * @file Product.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 9 Feb 2013 05:15 -0300
 */

require_once 'Model.php';

class Product extends Model {

	/*
	 * Constants
	 */

	// error codes
	const EINVAL  = 16;
	const ENOENT  = 17;

	// limits
	const TITLE_LIMIT       = 150;
	const BARCODE_LIMIT     = 50;
	const MODEL_LIMIT       = 50;
	const DESCRIPTION_LIMIT = 250;

	/*
	 * Instance Variables
	 */

	protected $id          = 0;    // integer
	protected $title       = null; // string
	protected $barcode     = null; // string
	protected $model       = null; // string
	protected $description = null; // string
	protected $status      = 0;    // integer
	protected $created     = 0;    // integer (unix timestamp)
	protected $deleted     = 0;    // integer (unix timestamp)
	protected $error       = 0;    // internal error code

	/*
	 * Class Methods
	 */

	/* Public */

	// object (integer, &integer)
	public static function productById($id, &$error) {

		// initialize error
		$error = 0;

		if ( ! is_int($id) || $id < 1 ) {
			$error = self::EINVAL;
			return null;
		}

		$pdo = self::getPDOInstance($error);
		if ($pdo == null)
			return null;

		try {

			$sql = 'SELECT id, title, barcode, model, description, status, UNIX_TIMESTAMP(created) AS created, deleted FROM products WHERE id = :id LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->execute();

			// bind columns in result
			$stmt->bindColumn(1, $id, PDO::PARAM_INT);
			$stmt->bindColumn(2, $title, PDO::PARAM_STR);
			$stmt->bindColumn(3, $barcode, PDO::PARAM_STR);
			$stmt->bindColumn(4, $model, PDO::PARAM_STR);
			$stmt->bindColumn(5, $description, PDO::PARAM_STR);
			$stmt->bindColumn(6, $status, PDO::PARAM_INT);
			$stmt->bindColumn(7, $created, PDO::PARAM_INT);
			$stmt->bindColumn(8, $deleted, PDO::PARAM_INT);

			if ($stmt->fetch(PDO::FETCH_BOUND)) {
				$inst = new self(array(
						$id, $title, $barcode, $model,
						$description, $status, $created, $deleted
					)
				);
				if ($inst->getProductId() < 1) {
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

	// array (string, &integer)
	public static function productsByTitle($title, $limit, $offset, &$total, &$error) {

		// initialize total
		$total = 0;

		// initialize error
		$error = 0;

		if ( ($len = self::UTF8StringLength($title, $size)) < 1
			&& $len > self::TITLE_LIMIT || ! is_int($limit) || ! is_int($offset) ) {
			$error = self::EINVAL;
			return null;
		}

		$pdo = self::getPDOInstance($error);
		if ($pdo == null)
			return null;

		try {

			// count records
			$sql = 'SELECT COUNT(*) AS qtd FROM products WHERE title LIKE :title AND deleted = 0';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':title', $title, PDO::PARAM_STR);
			$stmt->execute();
			$stmt->bindColumn(1, $total, PDO::PARAM_INT);
			if ( ! $stmt->fetch(PDO::FETCH_BOUND) )
				throw new Exception('unexpected result');

			// load result set
			$sql = 'SELECT id, title, barcode, model, description, status, UNIX_TIMESTAMP(created) AS created, deleted FROM products WHERE title LIKE :title AND deleted = 0 ORDER BY title ASC LIMIT :lim OFFSET :off';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':title', $title, PDO::PARAM_STR);
			$stmt->bindValue(':lim', $limit < 0 ? 1000 : $limit, PDO::PARAM_INT);
			$stmt->bindValue(':off', $offset < 0 ? 0 : $offset, PDO::PARAM_INT);
			$stmt->execute();

			// bind columns in result
			$stmt->bindColumn(1, $id, PDO::PARAM_INT);
			$stmt->bindColumn(2, $title, PDO::PARAM_STR);
			$stmt->bindColumn(3, $barcode, PDO::PARAM_STR);
			$stmt->bindColumn(4, $model, PDO::PARAM_STR);
			$stmt->bindColumn(5, $description, PDO::PARAM_STR);
			$stmt->bindColumn(6, $status, PDO::PARAM_INT);
			$stmt->bindColumn(7, $created, PDO::PARAM_INT);
			$stmt->bindColumn(8, $deleted, PDO::PARAM_INT);

			// build result array
			$array = array();

			// iterate over result set
			while ($stmt->fetch(PDO::FETCH_BOUND)) {
				$inst = new self(array(
						$id, $title, $barcode, $model,
						$description, $status, $created, $deleted
					)
				);
				if ($inst->getProductId() < 1) {
					$error = self::EINVAL;
					return null;
				}
				array_push($array, $inst);
			}

			return $array;

		}
		catch (Exception $e) {
			$error = self::EFAULT;
			return null;
		}

	}

	// array (string, &integer)
	public static function productsByBarcode($barcode, $limit, $offset, &$total, &$error) {

		// initialize total
		$total = 0;

		// initialize error
		$error = 0;

		if ( ($len = self::UTF8StringLength($barcode, $size)) < 1
			&& $len > self::BARCODE_LIMIT || ! is_int($limit) || ! is_int($offset) ) {
			$error = self::EINVAL;
			return null;
		}

		$pdo = self::getPDOInstance($error);
		if ($pdo == null)
			return null;

		try {

			// count records
			$sql = 'SELECT COUNT(*) AS qtd FROM products WHERE barcode LIKE :barcode AND deleted = 0';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':barcode', $barcode, PDO::PARAM_STR);
			$stmt->execute();
			$stmt->bindColumn(1, $total, PDO::PARAM_INT);
			if ( ! $stmt->fetch(PDO::FETCH_BOUND) )
				throw new Exception('unexpected result');

			// load result set
			$sql = 'SELECT id, title, barcode, model, description, status, UNIX_TIMESTAMP(created) AS created, deleted FROM products WHERE barcode LIKE :barcode AND deleted = 0 ORDER BY barcode ASC LIMIT :lim OFFSET :off';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':barcode', $barcode, PDO::PARAM_STR);
			$stmt->bindValue(':lim', $limit < 0 ? 1000 : $limit, PDO::PARAM_INT);
			$stmt->bindValue(':off', $offset < 0 ? 0 : $offset, PDO::PARAM_INT);
			$stmt->execute();

			// bind columns in result
			$stmt->bindColumn(1, $id, PDO::PARAM_INT);
			$stmt->bindColumn(2, $title, PDO::PARAM_STR);
			$stmt->bindColumn(3, $barcode, PDO::PARAM_STR);
			$stmt->bindColumn(4, $model, PDO::PARAM_STR);
			$stmt->bindColumn(5, $description, PDO::PARAM_STR);
			$stmt->bindColumn(6, $status, PDO::PARAM_INT);
			$stmt->bindColumn(7, $created, PDO::PARAM_INT);
			$stmt->bindColumn(8, $deleted, PDO::PARAM_INT);

			// build result array
			$array = array();

			// iterate over result set
			while ($stmt->fetch(PDO::FETCH_BOUND)) {
				$inst = new self(array(
						$id, $title, $barcode, $model,
						$description, $status, $created, $deleted
					)
				);
				if ($inst->getProductId() < 1) {
					$error = self::EINVAL;
					return null;
				}
				array_push($array, $inst);
			}

			return $array;

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
	public function getProductId() {
		return $this->id;
	}

	// string (void)
	public function getTitle() {
		return $this->title;
	}

	// string (void)
	public function getBarcode() {
		return $this->barcode;
	}

	// string (void)
	public function getModel() {
		return $this->model;
	}

	// string (void)
	public function getDescription() {
		return $this->description;
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
	public function setTitle($title) {
		if ( ($len = self::UTF8StringLength($title, $size)) >= 1
			&& $len <= self::TITLE_LIMIT ) {
			$this->title = $title;
			return true;
		}
		return false;
	}

	// bool (string)
	public function setBarcode($barcode) {
		if ( ($len = self::UTF8StringLength($barcode, $size)) >= 1
			&& $len <= self::BARCODE_LIMIT ) {
			$this->barcode = $barcode;
			return true;
		}
		return false;
	}

	// bool (string)
	public function setModel($model) {
		if ( ($len = self::UTF8StringLength($model, $size)) >= 0
			&& $len <= self::MODEL_LIMIT ) {
			$this->model = $model;
			return true;
		}
		return false;
	}

	// bool (string)
	public function setDescription($description) {
		if ( ($len = self::UTF8StringLength($description, $size)) >= 0
			&& $len <= self::DESCRIPTION_LIMIT ) {
			$this->description = $description;
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

	// bool (bool)
	public function setDeleted($deleted) {
		$this->deleted = $deleted ? time() : 0;
		return true;
	}

	// bool (void)
	public function save() {

		// initialize error
		$this->error = 0;

		if ( $this->title == null || $this->barcode == null ) {
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
				? 'UPDATE products SET title = :title, barcode = :barcode, model = :model, description = :description, status = :status, deleted = :deleted WHERE id = :id'
				: 'INSERT INTO products (title, barcode, model, description, status, created, deleted) VALUES (:title, :barcode, :model, :description, :status, FROM_UNIXTIME(:created), :deleted)';
			$stmt = $pdo->prepare($sql);
			if ($this->id > 0)
				$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
			else {
				$this->created = time();
				$stmt->bindValue(':created', $this->created, PDO::PARAM_INT);
			}
			$stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
			$stmt->bindValue(':barcode', strval($this->barcode), PDO::PARAM_STR);
			$stmt->bindValue(':model', strval($this->model), PDO::PARAM_STR);
			$stmt->bindValue(':description', strval($this->description), PDO::PARAM_STR);
			$stmt->bindValue(':status', $this->status, PDO::PARAM_INT);
			$stmt->bindValue(':deleted', $this->deleted, PDO::PARAM_INT);
			$stmt->execute();
			if ($this->id < 1) {
				if ($stmt->rowCount() != 1)
					throw new Exception('No Record Affected.');
				$this->id = intval($pdo->lastInsertId());
			}
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
		if ( ! is_array($data) || count($data) != 8 )
			return;

		// id
		if ( ! is_int($data[0]) || $data[0] < 1 )
			return;

		// title
		if ( ($len = self::UTF8StringLength($data[1], $size)) < 1
			|| $len > self::TITLE_LIMIT )
			return;

		// barcode
		if ( ($len = self::UTF8StringLength($data[2], $size)) < 1
			|| $len > self::BARCODE_LIMIT )
			return;

		// model
		if ( ($len = self::UTF8StringLength($data[3], $size)) < 0
			|| $len > self::MODEL_LIMIT )
			return;

		// description
		if ( ($len = self::UTF8StringLength($data[4], $size)) < 0
			|| $len > self::DESCRIPTION_LIMIT )
			return;

		// status
		if ( ! is_int($data[5]) )
			return;

		// created
		if ( ! is_int($data[6]) || $data[6] < 0 )
			return;

		// deleted
		if ( ! is_int($data[7]) || $data[7] < 0 )
			return;

		// data
		$this->id          = $data[0];
		$this->title       = $data[1];
		$this->barcode     = $data[2];
		$this->model       = $data[3];
		$this->description = $data[4];
		$this->status      = $data[5];
		$this->created     = $data[6];
		$this->deleted     = $data[7];

	}

}
