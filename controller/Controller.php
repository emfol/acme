<?php

/**
 * Acme Project - Controller Abstract Class
 * @file Controller.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 7 Feb 2013 23:160 -0300
 */

abstract class Controller {

	/*
	 * Constants
	 */

	const RE_VIEWNAME = '/^\w+$/';
	const RE_VARNAME  = '/^[_a-zA-Z]\w*$/';

	/*
	 * Instance Variables
	 */

	protected $viewGroup    = null;
	protected $outputBuffer = null;

	/*
	 * Protected Methods
	 */

	// void (string, [array])
	protected function render($arga, $argb = null) {

		static $forbidden = array('arga', 'argb', 'argk', 'argv', 'forbidden');

		// check for supplied view
		if ( ! is_string($arga) || preg_match(self::RE_VIEWNAME, $arga) != 1 )
				return;

		// load supplied data
		if ( is_array($argb) ) {
			foreach ($argb as $argk => $argv ) {
				if ( is_string($argk) && ! in_array($argk, $forbidden)
					&& preg_match(self::RE_VARNAME, $argk) == 1 )
					$$argk = $argv;
			}
		}

		// check for view group
		if ( is_string($this->viewGroup)
			&& preg_match(self::RE_VIEWNAME, $this->viewGroup) == 1 )
			$arga = "{$this->viewGroup}/$arga";

		// adjust view path
		$arga = "view/$arga.php";

		// start output buffering
		ob_start();

		// include selected view
		include($arga);

		// get buffer contents and destroy it
		$ob = ob_get_contents();
		ob_end_clean();

		// update output buffer
		$this->outputBuffer = is_string($ob) ? $ob : null;

	}

	// string (string, [array])
	protected function loadView($arga, $argb = null) {

		static $forbidden = array('arga', 'argb', 'argk', 'argv', 'forbidden');

		// check for supplied view
		if ( ! is_string($arga) || preg_match(self::RE_VIEWNAME, $arga) != 1 )
				return null;

		// load supplied data
		if ( is_array($argb) ) {
			foreach ($argb as $argk => $argv ) {
				if ( is_string($argk) && ! in_array($argk, $forbidden)
					&& preg_match(self::RE_VARNAME, $argk) == 1 )
					$$argk = $argv;
			}
		}

		// check for view group
		if ( is_string($this->viewGroup)
			&& preg_match(self::RE_VIEWNAME, $this->viewGroup) == 1 )
			$arga = "{$this->viewGroup}/$arga";

		// adjust view path
		$arga = "view/$arga.php";

		// start output buffering
		ob_start();

		// include selected view
		include($arga);

		// get buffer contents and destroy it
		$ob = ob_get_contents();
		ob_end_clean();

		// return output buffer
		if ( is_string($ob) )
			return $ob;

		return null;

	}

	protected function isPostRequest() {
		if ( isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
			&& strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0 )
			return true;
		return false;
	}

	protected function getRequestVariable($name) {
		if ( is_string($name) && isset($_GET[$name]) )
			return $_GET[$name];
		return null;
	}

	protected function getPostVariable($name) {
		if ( is_string($name) && isset($_POST[$name]) )
			return $_POST[$name];
		return null;
	}

	/*
	 * Public Methods
	 */

	// string (void)
	public function getOutputBuffer() {
		return $this->outputBuffer;
	}

}
