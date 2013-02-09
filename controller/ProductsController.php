<?php

/**
 * Acme Project - Products Controller
 * @file AuthController.php
 * @author Emanuel Fiuza de Oliveira
 * @email efiuza@me.com
 * @date Wed, 7 Feb 2013 23:160 -0300
 */

require_once 'Controller.php';
require_once 'model/Product.php';

class ProductsController extends Controller {

	/*
	 * Constants
	 */

	const MAX_PASSWORD_MISMATCH = 5;

	/*
	 * Instance Variables
	 */

	protected $viewGroup = 'Products';

	// void (void)
	public function product() {

		// view data
		$data = array(
			'pid' => 0,
			'product' => null,
			'success' => true,
			'message' => null
		);

		try {

			// get pid
			$pid = $this->getRequestVariable('pid');
			if ( ! is_string($pid) || ($pid = intval($pid)) < 1 )
				$pid = 0;

			// initialize product variable
			$product = null;

			// try to load product if pid > 0
			if ($pid > 0) {
				$product = Product::productById($pid, $error);
				if ( $product != null && is_object($product) ) {
					$data['pid'] = $pid;
					$data['product'] = $product;
				}
				else if ( $error == Product::EINVAL || $error == Product::ENOENT )
					throw new Exception('O produto solicitado não foi encontrado.');
				else
					throw new Exception("product load error #$error", 1);
			}

			if ( $this->isPostRequest() ) {

				if ( $product == null || ! is_object($product) )
					$product = new Product;

				// save product reference
				$data['product'] = $product;

				if ( ! $product->setTitle($this->getPostVariable('title')) )
					throw new Exception("O nome fornecido para o produto é inválido.");

				if ( ! $product->setBarcode($this->getPostVariable('barcode')) )
					throw new Exception("O código de barras fornecido para o produto é inválido.");

				if ( ! $product->setModel($this->getPostVariable('model')) )
					throw new Exception("O modelo fornecido para o produto é inválido.");

				if ( ! $product->setDescription($this->getPostVariable('description')) )
					throw new Exception("A descrição fornecida para o produto é inválida.");

				$status = $this->getPostVariable('status');
				if ( is_string($status) ) {
					if ( ! $product->setStatus(intval($status)) )
						throw new Exception("O status fornecido para o produto é inválido.");
				}

				if ( ! $product->save() ) {
					$error = $product->getErrorCode();
					throw new Exception("product store error #$error", 2);
				}

				// set message
				$data['message'] = $pid > 0
					? 'Dados atualizados com sucesso.'
					: 'Dados inseridos com sucesso';
				$data['pid'] = $product->getProductId();

			}

		}
		catch (Exception $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			if ($code > 0) {
				error_log("Products/product $message ($code)\n", 3, 'error.log');
				$message = 'Erro ao tentar efetuar operação requisitada...';
			}
			$data['success'] = false;
			$data['message'] = $message;
		}

		$this->render('product', $data);

	}

	public function delete() {

		$data = array('message' => null);

		try {

			// get pid
			$pid = $this->getRequestVariable('pid');
			if ( ! is_string($pid) || ($pid = intval($pid)) < 1 )
				throw new Exception('Produto não encontrado.');

			$product = Product::productById($pid, $error);
			if ( $product == null || ! is_object($product) ) {
				if ( $error != Product::EINVAL && $error != Product::ENOENT )
					throw new Exception("product load error #$error", 1);
				throw new Exception('Produto não encontrado.');
			}

			$product->setDeleted(true);
			if ( ! $product->save() ) {
				$error = $product->getErrorCode();
				throw new Exception("product store error #$error", 2);
			}

			$data['message'] = 'Produto apagado com sucesso!';

		}
		catch (Exception $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			if ($code > 0) {
				error_log("Products/delete $message ($code)\n", 3, 'error.log');
				$message = 'Erro ao tentar efetuar operação requisitada...';
			}
			$data['message'] = $message;
		}

		$this->render('delete', $data);

	}

	// void (void)
	public function index() {

		$data = array(
			'products' => array(),
			'info' => array(
				'parameter' => '',
				'field' => 'title',
				'total' => 0,
				'page' => 1,
				'limit' => 10
			),
			'message' => null
		);

		// field
		$field = $this->getRequestVariable('field') == 'barcode' ? 'barcode' : 'title';
		$data['info']['field'] = $field;

		// parameter
		$param = $this->getRequestVariable('param');
		if ( empty($param) || ! is_string($param) ) {
			$data['info']['parameter'] = '';
			$param = '%';
		}
		else {
			$data['info']['parameter'] = $param;
			$param = "%$param%";
		}

		// limit
		$limit = $this->getRequestVariable('limit');
		$limit = is_string($limit) && intval($limit) > 0 ? intval($limit) : 10;
		$data['info']['limit'] = $limit;

		// page
		$page = $this->getRequestVariable('page');
		$page = is_string($page) && intval($page) > 1 ? intval($page) : 1;
		$data['info']['page'] = $page;

		if ($field == 'barcode')
			$products = Product::productsByBarcode($param, $limit, ($page - 1) * $limit, $total, $error);
		else {
			$field = null;
			$products = Product::productsByTitle($param, $limit, ($page - 1) * $limit, $total, $error);
		}

		if ( is_array($products) )
			$data['products'] = $products;
		else {
			error_log("Products/index product listing error #$error\n", 3, 'error.log');
			$data['message'] = 'Erro ao tentar efetuar operação requisitada...';
		}

		$data['info']['total'] = $total;

		// render output
		$this->render('index', $data);

	}

}
