<?php

namespace modules\payment_test\controllers;

use core\classes\exceptions\RedirectException;
use core\classes\renderable\Controller;
use core\classes\Encryption;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;
use modules\checkout\classes\Cart as CartContents;
use modules\checkout\classes\Order;

class PaymentTest extends Controller {

	protected $permissions = [
		'payment' => ['customer'],
	];

	public function payment() {
		$cart  = new CartContents($this->config, $this->database, $this->request);
		$order = new Order($this->config, $this->database, $cart);
		$checkout = $order->purchase();

		$enc_checkout_id = Encryption::obfuscate($checkout->id, $this->config->siteConfig()->secret);
		throw new RedirectException($this->url->getURL('Checkout', 'receipt', [$enc_checkout_id]));
	}

}