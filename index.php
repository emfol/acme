<?php

/**
 * Acme Project - Main File
 * @file index.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 6 Feb 2013 22:26 -0300
 */

require_once 'config.php';
require_once 'controller/model/Session.php';

/*
 * Settings
 */

error_reporting(
	defined('DEVELOPMENT') && DEVELOPMENT
	? E_ALL
	: 0
);

/*
 * Constants
 */

define('BASE_PATH', dirname(__FILE__));
define('REGEX_CONTROLLER', '/^[a-zA-Z]\w*$/');
define('REGEX_ACTION', '/^[a-zA-Z]\w*$/');
define('AUTH_CONTROLLER', 'Auth');
define('DEFAULT_CONTROLLER', 'Products');
define('DEFAULT_ACTION', 'index');

/*
 * Default HTTP Content-Type Header
 */

header('Content-Type: text/html; charset=utf-8');

try {

	/*
	 * Session Control
	 */

	try {

		if ( ! isset($_COOKIE['SID']) )
			throw new Exception('Session not initialized.', 0);

		// load session
		$sess = Session::sessionWithIdentifier($_COOKIE['SID'], $error);
		if ( $sess == null || ! is_object($sess) ) {
			if ( $error == Session::EINVAL || $error == Session::ENOENT )
				throw new Exception('Session invalid or not found.', 1);
			else
				throw new Exception('Internal error.', 3);
		}

		// check if session expired
		if ($sess->isExpired())
			throw new Exception('Session Expired.', 2);

		// load default controller and action
		$controller = DEFAULT_CONTROLLER;
		$action     = DEFAULT_ACTION;

		// check for requested controller
		if ( isset($_GET['c']) && is_string($_GET['c'])
			&& preg_match(REGEX_CONTROLLER, $_GET['c']) == 1 )
			$controller = $_GET['c'];

		// check for requested action
		if ( isset($_GET['a']) && is_string($_GET['a'])
			&& preg_match(REGEX_ACTION, $_GET['a']) == 1 )
			$action = $_GET['a'];

	}
	catch (Exception $e) {

		// treat error
		switch ($e->getCode()) {
			case 1:
			case 2:
				// unset SID cookie
				setcookie('SID', '', time() - 3600, '/');
				break;
			case 3:
				error_log("unexpected error when loading session.\n", 3, 'error.log');
				break;
		}

		// define controller and action
		$controller = AUTH_CONTROLLER;
		$action     = DEFAULT_ACTION;

	}


	// @todo validate controller and action string

	// adjust controller info
	$controller .= 'Controller';
	$controllerPath = "controller/$controller.php";

	// check file existence and permissions
	if ( ! is_file($controllerPath) )
		throw new Exception('Not Found', 404);

	if ( ! is_readable($controllerPath) )
		throw new Exception('Forbidden', 403);

	// load requested controller
	ob_start();
	include $controllerPath;
	ob_end_clean();

	if ( ! class_exists($controller) )
		throw new Exception('Not Found', 404);

	// instantiate controller
	$inst = new $controller;

	if ( ! method_exists($inst, $action) )
		throw new Exception('Not Found', 404);

	// execute action as a controller method
	call_user_func(array($inst, $action));

	if (method_exists($inst, 'getOutputBuffer')) {
		$output = $inst->getOutputBuffer();
		if ( is_string($output) )
			echo $output;
	}

	// destroy controller
	unset($inst);

}
catch (Exception $e) {
	$code = $e->getCode();
	switch ($code) {
		case 403:
			header('HTTP/1.1 403 Forbidden');
			readfile('error/403.html');
			break;
		case 404:
			header('HTTP/1.1 404 Not Found');
			readfile('error/404.html');
			break;
		default:
			header('HTTP/1.1 500 Internal Server Error');
			readfile('error/500.html');
			break;
	}
}

