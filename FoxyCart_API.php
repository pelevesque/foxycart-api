<?php
/**
 * Foxycart API library for FoxyCart 0.7.2 using Curl
 *
 * Default Curl options:
 *
 * CURLOPT_POST = TRUE
 * CURLOPT_SSL_VERIFYPEER = FALSE
 * CURLOPT_SSL_VERIFYHOST = FALSE
 * CURLOPT_FOLLOWLOCATION = TRUE
 * CURLOPT_RETURNTRANSFER = TRUE
 * CURLOPT_HEADER = FALSE
 * CURLOPT_TIMEOUT = 30
 * CURLOPT_CONNECTTIMEOUT = 5
 *
 * @version     1.0
 * @author      Pierre-Emmanuel Lévesque
 * @email       pierre.e.levesque@gmail.com
 * @copyright   Copyright 2011, Pierre-Emmanuel Lévesque
 * @license     MIT License - @see README.md
 */
class FoxyCart_API {

	/**
	 * @var  array   errors from the last API call
	 */
	public $errors = array();

	/**
	 * @var  string  Foxycart API endpoint URL
	 */
	protected $endpoint_url;

	/**
	 * @var  string  Foxycart API token
	 */
	protected $api_token;

	/**
	 * @var  array   Curl options
	 */
	protected $curl_options;

	/**
	 * Constructor
	 *
	 * @param   string  Foxycart API endpoint URL
	 * @param   string  Foxycart API token
	 * @param   array   Curl options array(option => value, …))
	 * @return  void
	 */
	public function __construct($endpoint_url, $api_token, $curl_options = array())
	{
		$this->endpoint_url = $endpoint_url;
		$this->api_token = $api_token;
		$this->curl_options = $curl_options;
	}

	/**
	 * API call - store_template_cache
	 *
	 * Updates the store's template using "automagicache" automatic template caching.
	 *
	 * @param   string  template type [cart, checkout, receipt, html_email, email]
	 * @param   string  template URL
	 * @param   string  email subject
	 * @param   0 or 1  send html email (0 = text only email, 1 = text and html emails)
	 * @return  object  XML response, or FALSE on failure
	 */
	public function store_template_cache(
		$template_type,
		$template_url = NULL,
		$email_subject = NULL,
		$send_html_email = NULL
	)
	{
		return $this->api_call(array(
			'api_action'      => __FUNCTION__,
			'template_type'   => $template_type,
			'template_url'    => $template_url,
			'email_subject'   => $email_subject,
			'send_html_email' => $send_html_email
		));
	}

	/**
	 * API call - store_includes_get
	 *
	 * Gets the Foxycart include scripts.
	 *
	 * WARNING: This call should not be run on every pageload on your site.
	 * If you use this, cache it locally so you can serve your pages quickly
	 * and without needing to make an external request to the FoxyCart API.
	 * For the performance of our entire system, we may have to deny access
	 * for any stores that make excessive calls to this method.
	 *
	 * @param   string  javascript library [none, jquery]
	 * @param   string  cart type [none, colorbox]
	 * @return  object  XML response, or FALSE on failure
	 */
	public function store_includes_get($javascript_library = NULL, $cart_type = NULL)
	{
		return $this->api_call(array(
			'api_action'         => __FUNCTION__,
			'javascript_library' => $javascript_library,
			'cart_type'          => $cart_type
		));
	}

	/**
	 * API call - attribute_save
	 *
	 * Attaches name/value pairs to customer, transaction, or subscription records.
	 *
	 * @param   array   name value pairs (array('name' => 'value', …))
	 * @param   string  type [transaction, customer, subscription]
	 * @param   mixed   identifier [transaction_id, customer_id, sub_token, sub_token_url]
	 * @param   0 or 1  append (0 [def] = replaces the value , 1 = appends to the value)
	 * @return  object  XML response, or FALSE on failure
	 */
	public function attribute_save($name_value_pairs, $type, $identifier, $append = NULL)
	{
		return $this->api_call(array(
			'api_action'       => __FUNCTION__,
			'name_value_pairs' => $name_value_pairs,
			'type'             => $type,
			'identifier'       => $identifier,
			'append'           => $append
		));
	}

	/**
	 * API call - attribute_list
	 *
	 * Gets a list of attributes.
	 *
	 * @param   string  type [transaction, customer, subscription]
	 * @param   mixed   identifier [transaction_id, customer_id, sub_token, sub_token_url]
	 * @return  object  XML response, or FALSE on failure
	 */
	public function attribute_list($type, $identifier)
	{
		return $this->api_call(array(
			'api_action' => __FUNCTION__,
			'type'       => $type,
			'identifier' => $identifier
		));
	}

	/**
	 * API call - attribute_delete
	 *
	 * Deletes an attribute with a given name, or all attributes.
	 *
	 * NOTE: All takes precedence over name.
	 *
	 * @param   string  type [transaction, customer, subscription]
	 * @param   mixed   identifier [transaction_id, customer_id, sub_token, sub_token_url]
	 * @param   string  value names to delete (without name, all is required)
	 * @param   0 or 1  delete all attributes [def = 0]
	 * @return  object  XML response, or FALSE on failure
	 */
	public function attribute_delete($type, $identifier, $name = NULL, $all = NULL)
	{
		return $this->api_call(array(
			'api_action' => __FUNCTION__,
			'type'       => $type,
			'identifier' => $identifier,
			'name'       => $name,
			'all'        => $all
		));
	}

	/**
	 * API call - category_list
	 *
	 * Gets a list of all categories.
	 *
	 * @param   none
	 * @return  object  XML response, or FALSE on failure
	 */
	public function category_list()
	{
		return $this->api_call(array(
			'api_action' => __FUNCTION__
		));
	}

	/**
	 * API call - downloadable_list
	 *
	 * Gets a list of all downloadables.
	 *
	 * @param   none
	 * @return  object  XML response, or FALSE on failure
	 */
	public function downloadable_list()
	{
		return $this->api_call(array(
			'api_action' => __FUNCTION__
		));
	}

	/**
	 * API call - customer_get
	 *
	 * Gets a customer's data.
	 *
	 * NOTE: Cannot be used for guest accounts.
	 *
	 * @param   mixed   customer id (int), or customer email (string)
	 * @return  object  XML response, or FALSE on failure
	 */
	public function customer_get($customer_id_or_email)
	{
		$field = is_string($customer_id_or_email) ? 'email' : 'id';

		return $this->api_call(array(
			'api_action'         => __FUNCTION__,
			'customer_' . $field => $customer_id_or_email
		));
	}

	/**
	 * API call - customer_save
	 *
	 * Saves a customer's data.
	 *
	 * @param   mixed   customer id (int), or customer email (string)
	 * @param   array   extra params
	 * @return  object  XML response, or FALSE on failure
	 */
	public function customer_save($customer_id_or_email, $extra_params = array())
	{
		$field = is_string($customer_id_or_email) ? 'email' : 'id';

		return $this->api_call(array(
			'api_action'         => __FUNCTION__,
			'customer_' . $field => $customer_id_or_email,
			'extra_params'       => $extra_params
		));
	}

	/**
	 * API call - customer_address_get
	 *
	 * Gets a customer's address.
	 *
	 * @param   mixed   customer id (int), or customer email (string)
	 * @return  object  XML response, or FALSE on failure
	 */
	public function customer_address_get($customer_id_or_email)
	{
		$field = is_string($customer_id_or_email) ? 'email' : 'id';

		return $this->api_call(array(
			'api_action'         => __FUNCTION__,
			'customer_' . $field => $customer_id_or_email
		));
	}

	/**
	 * API call - customer_address_save
	 *
	 * Saves a customer's address.
	 *
	 * @param   mixed   customer id (int), or customer email (string)
	 * @param   array   extra params
	 * @return  object  XML response, or FALSE on failure
	 */
	public function customer_address_save($customer_id_or_email, $extra_params = array())
	{
		$field = is_string($customer_id_or_email) ? 'email' : 'id';

		return $this->api_call(array(
			'api_action'         => __FUNCTION__,
			'customer_' . $field => $customer_id_or_email,
			'extra_params'       => $extra_params
		));
	}

	/**
	 * API call - transaction_get
	 *
	 * Gets a transaction.
	 *
	 * @param   int     transaction id
	 * @return  object  XML response, or FALSE on failure
	 */
	public function transaction_get($transaction_id)
	{
		return $this->api_call(array(
			'api_action'     => __FUNCTION__,
			'transaction_id' => $transaction_id
		));
	}

	/**
	 * API call - transaction_list
	 *
	 * Gets a list of transactions.
	 *
	 * @param   array   filter options
	 * @return  object  XML response, or FALSE on failure
	 */
	public function transaction_list($filter_options = array())
	{
		return $this->api_call(array(
			'api_action'   => __FUNCTION__,
			'extra_params' => $filter_options
		));
	}

	/**
	 * API call - transaction_modify
	 *
	 * Modifies a transaction.
	 *
	 * @param   int     transaction id
	 * @param   0 or 1  data is fed
	 * @param   0 or 1  hide transaction
	 * @return  object  XML response, or FALSE on failure
	 */
	public function transaction_modify(
		$transaction_id,
		$data_is_fed = NULL,
		$hide_transaction = NULL
	)
	{
		return $this->api_call(array(
			'api_action'       => __FUNCTION__,
			'transaction_id'   => $transaction_id,
			'data_is_fed'      => $data_is_fed,
			'hide_transaction' => $hide_transaction
		));
	}

	/**
	 * API call - transaction_datafeed
	 *
	 * Re-feeds the transaction XML datafeed.
	 *
	 * @param   int     transaction id
	 * @return  object  XML response, or FALSE on failure
	 */
	public function transaction_datafeed($transaction_id)
	{
		return $this->api_call(array(
			'api_action'     => __FUNCTION__,
			'transaction_id' => $transaction_id
		));
	}

	/**
	 * API call - subscription_get
	 *
	 * Gets a subscription.
	 *
	 * @param   string  sub token (token by itself, or with complete URL)
	 * @return  object  XML response, or FALSE on failure
	 */
	public function subscription_get($sub_token)
	{
		return $this->api_call(array(
			'api_action' => __FUNCTION__,
			'sub_token'  => $sub_token
		));
	}

	/**
	 * API call - subscription_cancel
	 *
	 * Cancels a subscription.
	 *
	 * NOTE: Sets the sub_enddate to the next day, effectively canceling
	 * the subscription. This way the subscription cancellation will still
	 * be included in the Subscription XML Datafeed. To deactivate a
	 * subscription immediately you can use the subscription_modify method.
	 *
	 * @param   string  sub token (token by itself, or with complete URL)
	 * @return  object  XML response, or FALSE on failure
	 */
	public function subscription_cancel($sub_token)
	{
		return $this->api_call(array(
			'api_action' => __FUNCTION__,
			'sub_token'  => $sub_token
		));
	}

	/**
	 * API call - subscription_modify
	 *
	 * Modifies a subscription.
	 *
	 * @param   string  sub token (token by itself, or with complete URL)
	 * @param   array   extra params
	 * @return  object  XML response, or FALSE on failure
	 */
	public function subscription_modify($sub_token, $extra_params = array())
	{
		return $this->api_call(array(
			'api_action'   => __FUNCTION__,
			'sub_token'    => $sub_token,
			'extra_params' => $extra_params
		));
	}

	/**
	 * API call - subscription_list
	 *
	 * Gets a list of subscriptions.
	 *
	 * @param   array   extra params (filter options)
	 * @return  object  XML response, or FALSE on failure
	 */
	public function subscription_list($extra_params = array())
	{
		return $this->api_call(array(
			'api_action'   => __FUNCTION__,
			'extra_params' => $extra_params
		));
	}

	/**
	 * API call - subscription_datafeed
	 *
	 * Re-feeds the subscription XML datafeed.
	 *
	 * @param   none
	 * @return  object  XML response, or FALSE on failure
	 */
	public function subscription_datafeed()
	{
		return $this->api_call(array(
			'api_action' => __FUNCTION__
		));
	}

	/**
	 * Runs an API call and sets $this->errors
	 *
	 * @param   array   params
	 * @return  object  XML response, or FALSE on failure
	 */
	protected function api_call($params)
	{
		$params['api_token'] = $this->api_token;
		$params = $this->parse_params($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->endpoint_url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		if ( ! ini_get('safe_mode'))
		{
			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		if ( ! empty($this->curl_options))
		{
			curl_setopt_array($ch, $this->curl_options);
		}

		$response = trim(curl_exec($ch));

		$errors = array();

		if ($response == FALSE)
		{
			$errors[] = curl_error($ch);
		}
		else
		{
			$response = simplexml_load_string($response, NULL, LIBXML_NOCDATA);

			if ($response->result == "ERROR" AND isset($response->messages))
			{
				foreach ($response->messages as $message)
				{
					$errors[] = (string) $message->message;
				}
			}
		}

		curl_close($ch);

		$this->errors = $errors;

		return $response;
	}

	/**
	 * Parses the API call params
	 *
	 * NOTE: Do not modify the recursive call parameter.
	 * It is reserved for this function's internal use.
	 *
	 * @param   array   params
	 * @param   bool    recursive call (Do not modify)
	 * @return  string  parsed params
	 */
	protected function parse_params($params, $recursive_call = FALSE)
	{
		$parsed_params = '';

		foreach ($params as $param => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $par => $val)
				{
					$parsed_params .= $this->parse_params(array($par => $val), TRUE);
				}
			}
			elseif ($value !== NULL)
			{
				$parsed_params .= $param . '=' . urlencode($value) . '&';
			}
		}

		if ($recursive_call === FALSE)
		{
			$parsed_params = substr($parsed_params, 0, -1);
		}

		return $parsed_params;
	}

} // End Foxycart_API
