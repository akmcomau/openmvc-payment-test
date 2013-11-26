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
	];

	public function payment() {
		$model = new Model($this->config, $this->database);
		$this->language->loadLanguageFile('customer.php');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$this->language->loadLanguageFile('administrator/orders.php', 'modules'.DS.'checkout');

		// cart and order objects
		$cart  = new CartContents($this->config, $this->database, $this->request);
		$order = new Order($this->config, $this->database, $cart);

		// populate the customer record
		$customer = $model->getModel('\core\classes\models\Customer');
		$customer->password = '';
		$customer->first_name = 'Joe';
		$customer->last_name = 'Bloggs';
		$customer->email = 'joe@example.com';
		$customer->login = $customer->email;

		// populate the address record
		$australia = $model->getModel('\core\classes\models\Country')->get([
			'code' => 'AU'
		]);
		$qld = $model->getModel('\core\classes\models\State')->get([
			'country_id' => $australia->id,
			'name' => 'Queensland',
		]);
		$brisbane = $model->getModel('\core\classes\models\City')->get([
			'country_id' => $australia->id,
			'state_id' => $qld->id,
			'name' => 'Brisbane',
		]);
		$address = $model->getModel('\core\classes\models\Address');
		$address->address_first_name = 'Joe';
		$address->address_last_name  = 'Bloggs';
		$address->address_line1      = '50 Edward St';
		$address->address_line2      = '';
		$address->address_postcode   = '4000';
		$address->city_id            = $brisbane->id;
		$address->state_id           = $qld->id;
		$address->country_id         = $australia->id;

		// purchase the order
		$checkout = $order->purchase($customer, $address, $address);
		$order->sendOrderEmails($checkout, $this->language);
		$cart->clear();

		$enc_checkout_id = Encryption::obfuscate($checkout->id, $this->config->siteConfig()->secret);
		throw new RedirectException($this->url->getUrl('Checkout', 'receipt', [$enc_checkout_id]));
	}

}