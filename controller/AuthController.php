<?php

/**
 * Acme Project - Auth Controller
 * @file AuthController.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 7 Feb 2013 23:160 -0300
 */

require_once 'Controller.php';
require_once 'model/User.php';
require_once 'model/Session.php';

class AuthController extends Controller {

	/*
	 * Constants
	 */

	const MAX_PASSWORD_MISMATCH = 5;

	/*
	 * Instance Variables
	 */

	protected $viewGroup = 'Auth';

	// void (void)
	public function index() {

		$data = array(
			'success' => false,
			'username' => '',
			'message'  => ''
		);

		try {

			// @todo check if a session is already opened...

			if ( $this->isPostRequest() ) {

				// check supplied arguments
				$username = $this->getPostVariable('username');
				if ( empty($username) )
					throw new Exception('Por favor, forneça um nome de usuário válido.');

				$password = $this->getPostVariable('password');
				if ( empty($password) )
					throw new Exception('Por favor, forneça uma senha válida.');

				// try to load user
				$user = User::userByUsername($username, $error);
				if ( $user == null || ! is_object($user) ) {
					if ($error == User::EINVAL)
						throw new Exception('Por favor, forneça um nome de usuário válido.');
					else if ($error == User::ENOENT)
						throw new Exception('Usuário não encontrado.');
					else
						throw new Exception("error loading user by name #$error.", 1);
				}

				// get user status
				$status = $user->getStatus();

				// check if user is blocked
				if ($status >= self::MAX_PASSWORD_MISMATCH)
					throw new Exception('Usuário bloqueado.');

				// check if password matches
				if ( $user->passwordMatches($password) ) {

					$user_id = $user->getUserId();
					$sess = Session::sessionWithUserId($user_id, $error);
					if ( $sess == null || ! is_object($sess) )
						throw new Exception("error creating session for user id $user_id #$error.", 2);

					// update user status
					$user->setStatus(0);
					if ( ! $user->save() ) {
						$error = $user->getErrorCode();
						throw new Exception("user store error #$error", 3);
					}

					// save session identifier on cookies
					setcookie('SID', $sess->getIdentifier(), $sess->getExpirationTime(), '/');
					// set refresh header
					header('Refresh: 2');
					$data['message'] = 'Login efetuado com sucesso! Redirecionando...';

				}
				else {
					$user->setStatus(++$status);
					if ( ! $user->save() ) {
						$error = $user->getErrorCode();
						throw new Exception("error saving user information #$error.", 3);
					}
					$status = self::MAX_PASSWORD_MISMATCH - $status;
					throw new Exception(
						$status > 1
						? "Senha incorreta! $status tentativas restantes."
						: ($status == 1 ? "Senha incorreta! Uma tentativa restante."
							: "Senha incorreta! Usuário bloqueado.")
					);
				}

			}

		}
		catch (Exception $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			if ($code > 0) {
				error_log("Auth/index $message ($code)\n", 3, 'error.log');
				$message = 'Erro ao tentar efetuar operação requisitada...';
			}
			$data['success'] = false;
			$data['message'] = $message;
		}

		// render output
		$this->render('index', $data);

	}

}
