<?php
if (!defined('ABSPATH')) {
  exit;
}

return apply_filters('wc_mypay_payment_settings',
  array(
    'enabled' => array(
      'title' => __('Enable/Disable', 'mypay'),
      'type' => 'checkbox',
      'label' => __('Enable', 'mypay'),
      'default' => 'no'
    ),
    'title' => array(
      'title' => __('Title', 'mypay'),
      'type' => 'text',
      'description' => __('This controls the title which the user sees during checkout.', 'mypay'),
      'default' => __('MYPay', 'mypay'),
      'desc_tip' => true,
    ),
    'description' => array(
      'title' => __('Description', 'mypay'),
      'type' => 'textarea',
      'description' => __('This controls the description which the user sees during checkout. If enable AFTERPAY payment method, please notify user the place order amount limit is NTD 10,000.', 'mypay'),
      'desc_tip' => true,
    ),
    'mypay_test_mode' => array(
      'title' => __('Test Mode', 'mypay'),
      'label' => __('Enable', 'mypay'),
      'type' => 'checkbox',
      'description' => __('Test order will add date as prefix.', 'mypay'),
      'default' => 'no',
      'desc_tip' => true,
    ),
    'mypay_merchant_id' => array(
      'title' => __('Merchant ID', 'mypay'),
      'type' => 'text',
      'default' => ''
    ),
    'mypay_hash_key' => array(
      'title' => __('Hash Key', 'mypay'),
      'type' => 'text',
      'default' => ''
    ),
    'mypay_payment_methods' => array(
      'title' => __('Payment Method', 'mypay'),
      'type' => 'multiselect',
      'description' => __('Press CTRL and the right button on the mouse to select multi payments. NOTICE: option `ALL` was not support `AFTERPAY`', 'mypay'),
      'options' => array(
        'ALL' => $this->get_payment_desc('All'),
        'CREDITCARD' => $this->get_payment_desc('CREDITCARD'),
        'CSTORECODE' => $this->get_payment_desc('CSTORECODE'),
        'WEBATM' => $this->get_payment_desc('WEBATM'),
        'E_COLLECTION' => $this->get_payment_desc('E_COLLECTION'),
        'UNIONPAY' => $this->get_payment_desc('UNIONPAY'),
        'SVC' => $this->get_payment_desc('SVC'),
        'ABROAD' => $this->get_payment_desc('ABROAD'),
        'ALIPAY' => $this->get_payment_desc('ALIPAY'),
        'MATM' => $this->get_payment_desc('MATM'),
        'WECHAT' => $this->get_payment_desc('WECHAT'),
        'DIRECTDEBIT' => $this->get_payment_desc('DIRECTDEBIT'),
        'LINEPAYON' => $this->get_payment_desc('LINEPAYON'),
        'APPLEPAY' => $this->get_payment_desc('APPLEPAY'),
        'GOOGLEPAY' => $this->get_payment_desc('GOOGLEPAY'),
        'EACH' => $this->get_payment_desc('EACH'),
        'C_INSTALLMENT' => $this->get_payment_desc('C_INSTALLMENT'),
        'C_REDEEM' => $this->get_payment_desc('C_REDEEM'),
        'CARDLESS' => $this->get_payment_desc('CARDLESS'),
        'PION' => $this->get_payment_desc('PION'),
        'AMEX' => $this->get_payment_desc('AMEX'),
        'JKOON' => $this->get_payment_desc('JKOON'),
        'AFP' => $this->get_payment_desc('AFP'),
        'EASYWALLETON' => $this->get_payment_desc('EASYWALLETON'),
        'BARCODE' => $this->get_payment_desc('BARCODE'),
      ),
      'desc_tip' => true,
    ),
    // ref :
    // 1. https://docs.woocommerce.com/document/payment-gateway-api/
    // 2. https://docs.woocommerce.com/document/wc_api-the-woocommerce-api-callback/
    'mypay_payment_callback_url' => array(
      'title' => __('Payment Callback URL', 'mypay'),
      'type' => 'textarea',
      'custom_attributes' => [
        'readonly' => 'readonly',
      ],
      'style' =>'width:100%;',
      'description' => __('Payment Callback URL, Please use the following payment callback url in MyPay payment notify & asynchronous notify setting.', 'mypay'),
      'desc_tip' => true,
    ),
  )
);
