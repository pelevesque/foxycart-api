<?php
/**
 * Library for FoxyCart 0.7.2 using Curl
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
 * @author      Pierre-Emmanuel Lévesque
 * @email       pierre.e.levesque@gmail.com
 * @copyright   Copyright 2011, Pierre-Emmanuel Lévesque
 * @license     MIT License - @see LICENSE.md
 */

namespace Pel\Helper;

class FoxyCartAPI
{
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
     * API call - storeTemplateCache
     *
     * Updates the store's template using "automagicache" automatic template caching.
     *
     * @param   string  template type [cart, checkout, receipt, html_email, email]
     * @param   string  template URL
     * @param   string  email subject
     * @param   0 or 1  send html email (0 = text only email, 1 = text and html emails)
     * @return  object  XML response, or FALSE on failure
     */
    public function storeTemplateCache(
        $template_type,
        $template_url = NULL,
        $email_subject = NULL,
        $send_html_email = NULL
    )
    {
        return $this->apiCall(array(
            'api_action'      => __FUNCTION__,
            'template_type'   => $template_type,
            'template_url'    => $template_url,
            'email_subject'   => $email_subject,
            'send_html_email' => $send_html_email
        ));
    }

    /**
     * API call - storeIncludesGet
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
    public function storeIncludesGet($javascript_library = NULL, $cart_type = NULL)
    {
        return $this->apiCall(array(
            'api_action'         => __FUNCTION__,
            'javascript_library' => $javascript_library,
            'cart_type'          => $cart_type
        ));
    }

    /**
     * API call - attributeSave
     *
     * Attaches name/value pairs to customer, transaction, or subscription records.
     *
     * @param   array   name value pairs (array('name' => 'value', …))
     * @param   string  type [transaction, customer, subscription]
     * @param   mixed   identifier [transaction_id, customer_id, sub_token, sub_token_url]
     * @param   0 or 1  append (0 [def] = replaces the value , 1 = appends to the value)
     * @return  object  XML response, or FALSE on failure
     */
    public function attributeSave($name_value_pairs, $type, $identifier, $append = NULL)
    {
        return $this->apiCall(array(
            'api_action'       => __FUNCTION__,
            'name_value_pairs' => $name_value_pairs,
            'type'             => $type,
            'identifier'       => $identifier,
            'append'           => $append
        ));
    }

    /**
     * API call - attributeList
     *
     * Gets a list of attributes.
     *
     * @param   string  type [transaction, customer, subscription]
     * @param   mixed   identifier [transaction_id, customer_id, sub_token, sub_token_url]
     * @return  object  XML response, or FALSE on failure
     */
    public function attributeList($type, $identifier)
    {
        return $this->apiCall(array(
            'api_action' => __FUNCTION__,
            'type'       => $type,
            'identifier' => $identifier
        ));
    }

    /**
     * API call - attributeDelete
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
    public function attributeDelete($type, $identifier, $name = NULL, $all = NULL)
    {
        return $this->apiCall(array(
            'api_action' => __FUNCTION__,
            'type'       => $type,
            'identifier' => $identifier,
            'name'       => $name,
            'all'        => $all
        ));
    }

    /**
     * API call - categoryList
     *
     * Gets a list of all categories.
     *
     * @param   none
     * @return  object  XML response, or FALSE on failure
     */
    public function categoryList()
    {
        return $this->apiCall(array(
            'api_action' => __FUNCTION__
        ));
    }

    /**
     * API call - downloadableList
     *
     * Gets a list of all downloadables.
     *
     * @param   none
     * @return  object  XML response, or FALSE on failure
     */
    public function downloadableList()
    {
        return $this->apiCall(array(
            'api_action' => __FUNCTION__
        ));
    }

    /**
     * API call - customerGet
     *
     * Gets a customer's data.
     *
     * NOTE: Cannot be used for guest accounts.
     *
     * @param   mixed   customer id (int), or customer email (string)
     * @return  object  XML response, or FALSE on failure
     */
    public function customerGet($customer_id_or_email)
    {
        $field = is_string($customer_id_or_email) ? 'email' : 'id';

        return $this->apiCall(array(
            'api_action'         => __FUNCTION__,
            'customer_' . $field => $customer_id_or_email
        ));
    }

    /**
     * API call - customerSave
     *
     * Saves a customer's data.
     *
     * @param   mixed   customer id (int), or customer email (string)
     * @param   array   extra params
     * @return  object  XML response, or FALSE on failure
     */
    public function customerSave($customer_id_or_email, $extra_params = array())
    {
        $field = is_string($customer_id_or_email) ? 'email' : 'id';

        return $this->apiCall(array(
            'api_action'         => __FUNCTION__,
            'customer_' . $field => $customer_id_or_email,
            'extra_params'       => $extra_params
        ));
    }

    /**
     * API call - customerAddressGet
     *
     * Gets a customer's address.
     *
     * @param   mixed   customer id (int), or customer email (string)
     * @return  object  XML response, or FALSE on failure
     */
    public function customerAddressGet($customer_id_or_email)
    {
        $field = is_string($customer_id_or_email) ? 'email' : 'id';

        return $this->apiCall(array(
            'api_action'         => __FUNCTION__,
            'customer_' . $field => $customer_id_or_email
        ));
    }

    /**
     * API call - customerAddressSave
     *
     * Saves a customer's address.
     *
     * @param   mixed   customer id (int), or customer email (string)
     * @param   array   extra params
     * @return  object  XML response, or FALSE on failure
     */
    public function customerAddressSave($customer_id_or_email, $extra_params = array())
    {
        $field = is_string($customer_id_or_email) ? 'email' : 'id';

        return $this->apiCall(array(
            'api_action'         => __FUNCTION__,
            'customer_' . $field => $customer_id_or_email,
            'extra_params'       => $extra_params
        ));
    }

    /**
     * API call - transactionGet
     *
     * Gets a transaction.
     *
     * @param   int     transaction id
     * @return  object  XML response, or FALSE on failure
     */
    public function transactionGet($transaction_id)
    {
        return $this->apiCall(array(
            'api_action'     => __FUNCTION__,
            'transaction_id' => $transaction_id
        ));
    }

    /**
     * API call - transactionList
     *
     * Gets a list of transactions.
     *
     * @param   array   filter options
     * @return  object  XML response, or FALSE on failure
     */
    public function transactionList($filter_options = array())
    {
        return $this->apiCall(array(
            'api_action'   => __FUNCTION__,
            'extra_params' => $filter_options
        ));
    }

    /**
     * API call - transactionModify
     *
     * Modifies a transaction.
     *
     * @param   int     transaction id
     * @param   0 or 1  data is fed
     * @param   0 or 1  hide transaction
     * @return  object  XML response, or FALSE on failure
     */
    public function transactionModify(
        $transaction_id,
        $data_is_fed = NULL,
        $hide_transaction = NULL
    )
    {
        return $this->apiCall(array(
            'api_action'       => __FUNCTION__,
            'transaction_id'   => $transaction_id,
            'data_is_fed'      => $data_is_fed,
            'hide_transaction' => $hide_transaction
        ));
    }

    /**
     * API call - transactionDatafeed
     *
     * Re-feeds the transaction XML datafeed.
     *
     * @param   int     transaction id
     * @return  object  XML response, or FALSE on failure
     */
    public function transactionDatafeed($transaction_id)
    {
        return $this->apiCall(array(
            'api_action'     => __FUNCTION__,
            'transaction_id' => $transaction_id
        ));
    }

    /**
     * API call - subscriptionGet
     *
     * Gets a subscription.
     *
     * @param   string  sub token (token by itself, or with complete URL)
     * @return  object  XML response, or FALSE on failure
     */
    public function subscriptionGet($sub_token)
    {
        return $this->apiCall(array(
            'api_action' => __FUNCTION__,
            'sub_token'  => $sub_token
        ));
    }

    /**
     * API call - subscriptionCancel
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
    public function subscriptionCancel($sub_token)
    {
        return $this->apiCall(array(
            'api_action' => __FUNCTION__,
            'sub_token'  => $sub_token
        ));
    }

    /**
     * API call - subscriptionModify
     *
     * Modifies a subscription.
     *
     * @param   string  sub token (token by itself, or with complete URL)
     * @param   array   extra params
     * @return  object  XML response, or FALSE on failure
     */
    public function subscriptionModify($sub_token, $extra_params = array())
    {
        return $this->apiCall(array(
            'api_action'   => __FUNCTION__,
            'sub_token'    => $sub_token,
            'extra_params' => $extra_params
        ));
    }

    /**
     * API call - subscriptionList
     *
     * Gets a list of subscriptions.
     *
     * @param   array   extra params (filter options)
     * @return  object  XML response, or FALSE on failure
     */
    public function subscriptionList($extra_params = array())
    {
        return $this->apiCall(array(
            'api_action'   => __FUNCTION__,
            'extra_params' => $extra_params
        ));
    }

    /**
     * API call - subscriptionDatafeed
     *
     * Re-feeds the subscription XML datafeed.
     *
     * @param   none
     * @return  object  XML response, or FALSE on failure
     */
    public function subscriptionDatafeed()
    {
        return $this->apiCall(array(
            'api_action' => __FUNCTION__
        ));
    }

    /**
     * Runs an API call and sets $this->errors
     *
     * @param   array   params
     * @return  object  XML response, or FALSE on failure
     */
    protected function apiCall($params)
    {
        $params['api_token'] = $this->api_token;
        $params = $this->parseParams($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint_url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (! ini_get('safe_mode')) {
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        if (! empty($this->curl_options)) {
            curl_setopt_array($ch, $this->curl_options);
        }

        $response = trim(curl_exec($ch));

        $errors = array();

        if ($response == FALSE) {
            $errors[] = curl_error($ch);
        } else {
            $response = simplexml_load_string($response, NULL, LIBXML_NOCDATA);

            if ($response->result == "ERROR" AND isset($response->messages)) {
                foreach ($response->messages as $message) {
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
    protected function parseParams($params, $recursive_call = FALSE)
    {
        $parsed_params = '';

        foreach ($params as $param => $value) {
            if (is_array($value)) {
                foreach ($value as $par => $val) {
                    $parsed_params .= $this->parseParams(array($par => $val), TRUE);
                }
            } elseif ($value !== NULL) {
                $parsed_params .= $param . '=' . urlencode($value) . '&';
        }

        if ($recursive_call === FALSE) {
            $parsed_params = substr($parsed_params, 0, -1);
        }

        return $parsed_params;
    }

}
