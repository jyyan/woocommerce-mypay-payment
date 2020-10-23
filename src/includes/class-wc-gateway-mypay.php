<?php

class WC_Gateway_Mypay extends WC_Payment_Gateway
{
  public $mypay_test_mode;
  public $mypay_merchant_id;
  public $mypay_hash_key;
  public $mypay_choose_payment;
  public $mypay_payment_methods;

  public function __construct()
  {

    $this->id = 'mypay';
    $this->method_title = __('MYPay', 'mypay');
    $this->method_description = __('MYPay is the most popular payment gateway for online shopping in Taiwan', 'mypay');
    $this->has_fields = true;
    $this->icon = apply_filters('woocommerce_mypay_icon', plugins_url('images/icon.png', dirname(__FILE__)));

    # Load the form fields
    $this->init_form_fields();

    # Load the administrator settings
    $this->init_settings();

    $this->title = $this->get_option('title');
    $this->description = $this->get_option('description');
    $this->mypay_test_mode = $this->get_option('mypay_test_mode');
    $this->mypay_merchant_id = $this->get_option('mypay_merchant_id');
    $this->mypay_hash_key = $this->get_option('mypay_hash_key');
    $this->mypay_payment_methods = $this->get_option('mypay_payment_methods');

    # Register a action to save administrator settings
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

    # Register a action to redirect to MYPay payment center
    add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

    # Register a action to process the callback
    add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'receive_response'));

    add_action('woocommerce_thankyou_mypay', array($this, 'thankyou_page'));
  }

  /**
   * 載入參數設定欄位
   */
  public function init_form_fields()
  {
    $this->form_fields = include(untrailingslashit(plugin_dir_path(WC_MYPAY_MAIN_FILE)) . '/includes/settings-mypay.php');
  }

  /**
   * Display the form when chooses MYPay payment
   */
  public function payment_fields()
  {
    if (!empty($this->description)) {
      echo $this->add_next_line($this->description . '<br /><br />');
    }
    echo __('Payment Method', 'mypay') . ' : ';
    echo $this->add_next_line('<select name="mypay_choose_payment">');
    foreach ($this->mypay_payment_methods as $payment_method) {
      echo $this->add_next_line('  <option value="' . $payment_method . '">');
      echo $this->add_next_line('    ' . $this->get_payment_desc($payment_method));
      echo $this->add_next_line('  </option>');
    }
    echo $this->add_next_line('</select>');
  }

  /**
   * Check the payment method and the chosen payment
   */
  public function validate_fields()
  {
    $choose_payment = $_POST['mypay_choose_payment'];
    $payment_desc = $this->get_payment_desc($choose_payment);
    if ($_POST['payment_method'] == $this->id && !empty($payment_desc)) {
      $this->mypay_choose_payment = $choose_payment;
      return true;
    } else {
      wc_add_notice(__('Invalid payment method.') . $payment_desc, 'error');
      return false;
    }
  }

  /**
   * Process the payment
   */
  public function process_payment($order_id)
  {
    # Update order status
    $order = new WC_Order($order_id);
    $order->update_status('pending', __('Awaiting MYPay payment', 'mypay'));

    # Set the MYPay payment type to the order note
    $order->add_order_note($this->mypay_choose_payment, true);

    return array(
      'result' => 'success',
      'redirect' => $order->get_checkout_payment_url(true)
    );
  }

  public function set_payment($order, $para)
  {
    $service_url = $para['service_url'];
    $store_uid = $para['store_uid'];
    $key = $para['key'];
    $order_id = $para['order_id'];
    $choose_payment = strtoupper($para['choose_payment']);

    // 商品資料
    $payment = array();
    $payment['store_uid'] = $store_uid;
    $payment['ip'] = $this->get_client_ip(); // 此為消費者IP，會做為驗證用
    $payment['pfn'] = $choose_payment;
    $payment['success_returl'] = add_query_arg(array(
      'wc-api' => 'WC_Gateway_MYPay',
      'returl' => 'success',
      'order_id' => $order_id,
    ), home_url('/'));
    $payment['failure_returl'] = add_query_arg(array(
      'wc-api' => 'WC_Gateway_MYPay',
      'returl' => 'failure',
      'order_id' => $order_id,
    ), home_url('/'));

    //找出訂單資訊
    $order_data = $order->get_data();
    $name = "";
    $phone = "";
    $email = "";
    //$address = "";
    if (isset($order_data['billing']['first_name'])) {
      $name = $order_data['billing']['last_name'] . $order_data['billing']['first_name'];
    }
    if (isset($order_data['billing']['phone'])) {
      $phone = $order_data['billing']['phone'];
    }
    if (isset($order_data['billing']['email'])) {
      $email = $order_data['billing']['email'];
    }
    if (isset($order_data['billing']['city'])) {
      //$address = $order_data['billing']['state']. " ". $order_data['billing']['city']. " ". $order_data['billing']['address_1'];
    }
    //echo "幣別：". $order_data['currency']. "<br />";
    $payment['cost'] = $order_data['total']; //總金額
    $payment['user_id'] = "phper";
    $payment['order_id'] = $order_id;
    $payment['discount_total'] = $order_data['discount_total']; //echo "折扣金額
    $payment['user_real_name'] = $name;
    $payment['user_email'] = $email;
    $payment['user_cellphone'] = $phone;
    $items = $order->get_items();

    $payment['item'] = count($items);;

    $idx_count = 0;
    foreach ($items as $item_idx => $item) {
      $item_data = $item->get_data();
      $product = $item->get_product();
      $payment['i_' . $idx_count . '_id'] = $item_data['product_id'];//商品ID
      $payment['i_' . $idx_count . '_name'] = $item_data['name'];    //商品名稱
      $payment['i_' . $idx_count . '_cost'] = $product->get_price(); //商品單價
      $payment['i_' . $idx_count . '_amount'] = $item_data['quantity'];//商品數量
      $payment['i_' . $idx_count . '_total'] = $item_data['total'];  //商品小計
      ++$idx_count;
    }
    return $payment;
  }

  public function jump_page($res_json)
  {
    //生成表單，自動送出
    $szHtml = '<!DOCTYPE html>';
    $szHtml .= '<html>';
    $szHtml .= '<head>';
    $szHtml .= '<meta charset="utf-8">';
    $szHtml .= '</head>';
    $szHtml .= '<body>';
    $szHtml .= "<form id=\"__mypayForm\" method=\"post\" action=\"{$res_json['url']}\">";

    $szHtml .= '</form>';
    $szHtml .= '<script type="text/javascript">document.getElementById("__mypayForm").submit();</script>';
    $szHtml .= '</body>';
    $szHtml .= '</html>';

    echo $szHtml;
  }

  /*
   * 資料送出
   */
  public function send_start($service_url, $post_data)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $service_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }

  // 加密方法 for PHP 7.x 使用 openssl
  public function encrypt($fields, $key)
  {
    $data = json_encode($fields);
    $iv = random_bytes(16);
    $padding = 16 - (strlen($data) % 16);
    $data .= str_repeat(chr($padding), $padding);
    $data = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
    $data = base64_encode($iv . $data);
    return $data;
  }

  /**
   * Redirect to MYPay
   */
  public function receipt_page($order_id)
  {
    # Clean the cart
    global $woocommerce;
    $woocommerce->cart->empty_cart();

    $service_url = '';
    if ($this->mypay_test_mode == 'yes') {
      $service_url = "https://pay.usecase.cc/api/init";
    } else {
      $service_url = "https://mypay.tw/api/init";
    }

    // 次特店商務代號
    $store_uid = $this->mypay_merchant_id;
    // 次特店金鑰
    $key = $this->mypay_hash_key;
    $order = new WC_Order($order_id);

    //取出支付方式
    $notes = $order->get_customer_order_notes();
    $choose_payment = 'ALL';
    $choose_installment = '';
    if (isset($notes[0])) {
      $chooseParam = explode('_', $notes[0]->comment_content);
      $choose_payment = isset($chooseParam[0]) ? $chooseParam[0] : '';
      $choose_installment = isset($chooseParam[1]) ? $chooseParam[1] : '';
    }
    $para = array();
    $para['service_url'] = $service_url;
    $para['store_uid'] = $store_uid;
    $para['key'] = $key;
    $para['order_id'] = $order_id;
    $para['choose_payment'] = $choose_payment;

    $payment = $this->set_payment($order, $para);

    // 送出欄位
    $post_data = array();
    $post_data['store_uid'] = $store_uid;
    $post_data['service'] = $this->encrypt(array(
      'service_name' => 'api',
      'cmd' => 'api/orders'
    ), $key);
    $post_data['encry_data'] = $this->encrypt($payment, $key);

    // 資料送出
    $result = $this->send_start($service_url, $post_data);

    // 回傳 JSON 內容
    $res_json = json_decode($result, true);
    if ($res_json['code'] == 200) {
      $order->add_order_note($result);
      $order->save();
      $this->jump_page($res_json);
    } else {
      throw new Exception($res_json['msg']);
    }
    exit;
  }

  /*
   * 取出訂單的注記
   */
  public function get_private_order_notes($order_id)
  {
    global $wpdb;

    $table_perfixed = $wpdb->prefix . 'comments';
    $results = $wpdb->get_results("
        SELECT *
        FROM $table_perfixed
        WHERE  `comment_post_ID` = $order_id
        AND  `comment_type` LIKE  'order_note'
        ");

    foreach ($results as $note) {
      $order_note[] = array(
        'note_id' => $note->comment_ID,
        'note_date' => $note->comment_date,
        'note_author' => $note->comment_author,
        'note_content' => $note->comment_content,
      );
    }
    return $order_note;
  }

  /*
   * 查詢參數設置
   */
  public function set_query_payment($para)
  {
    $payment = array();
    $payment['key'] = $para['key'];
    $payment['uid'] = $para['uid'];
    return $payment;
  }

  /**
   * mypay 的狀態碼轉換成WooCommerce狀態
   */
  public function mypay_prc_transfer($prc)
  {
    $status = "wc-pending";
    switch ($prc) {
      case "250":
        $status = "wc-completed";
        break;
      case "260":
      case "270":
      case "280":
        $status = "wc-processing";
        break;
      case "300":
      case "380":
      case "400":
        $status = "wc-failed";
        break;
      case "A0002":
        $status = "wc-cancelled";
        break;
    }
    return $status;
  }

  /**
   * Process the callback
   */
  public function receive_response()
  {
    date_default_timezone_set("Asia/Taipei");
    if (isset($_GET['notify']) && $_GET['notify'] == "order") {
      //mypay 背景通知所在
      $ip = $this->get_client_ip();
      if ($this->mypay_test_mode == 'yes') {
      } else {
        //若是正式區，要對來源進行檢查
        $host_name = gethostbyaddr($ip);
        if (strpos($host_name, "compute.amazonaws.com") === FALSE) {
          echo "you are not from mypay(aws),now in normal mode";
          exit;
        }
      }
      $res_json = json_encode($_POST);
      if (isset($_POST['order_id'])) {
        $order = wc_get_order($_POST['order_id']);
        if (isset($order->id) && isset($_POST['prc'])) {
          $order_data = $order->get_data();
          $status = $this->mypay_prc_transfer($_POST['prc']);
          $order->update_status($status);
        }
      }
      echo "8888";
      exit;
    }
    // 次特店商務代號
    $store_uid = $this->mypay_merchant_id;
    // 次特店金鑰
    $key = $this->mypay_hash_key;
    $service_url = '';
    if ($this->mypay_test_mode == 'yes') {
      $service_url = "https://pay.usecase.cc/api/init";
    } else {
      $service_url = "https://mypay.tw/api/init";
    }

    if (isset($_GET['returl'])) {
      $order_id = $_GET['order_id'];
      $order = new WC_Order($order_id);
      $notes = $this->get_private_order_notes($order_id);
      if (isset($notes[1])) {
        $res_json = json_decode($notes[1]['note_content'], true);
        $payment = $this->set_query_payment($res_json);

        // 送出欄位
        $post_data = array();
        $post_data['store_uid'] = $store_uid;
        $post_data['service'] = $this->encrypt(array(
          'service_name' => 'api',
          'cmd' => 'api/queryorder'
        ), $key);
        $post_data['encry_data'] = $this->encrypt($payment, $key);

        // 資料送出
        $result = $this->send_start($service_url, $post_data);
        $query_result = json_decode($result, true);
        if (isset($query_result['prc'])) {
          $status = $this->mypay_prc_transfer($query_result['prc']);
          $order->update_status($status);
        }
      }
      $this->thankyou_page($order_id);
      exit;
    }
  }

  public function get_client_ip()
  {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
    else
      $ipaddress = 'UNKNOWN';
    return $ipaddress;
  }

  # Custom function

  /**
   * Get the payment method description
   * @param string   payment name
   * @return string   payment method description
   */
  private function get_payment_desc($payment_name)
  {
    $payment_desc = array(
      'All' => __('All', 'mypay'),
      'CREDITCARD' => __('CREDITCARD', 'mypay'),
      'CSTORECODE' => __('CSTORECODE', 'mypay'),
      'WEBATM' => __('WEBATM', 'mypay'),
      'TELECOM' => __('TELECOM', 'mypay'),
      'E_COLLECTION' => __('E_COLLECTION', 'mypay'),
      'SVC' => __('SVC', 'mypay'),
      'UNIONPAY' => __('UNIONPAY', 'mypay'),
      'ABROAD' => __('ABROAD', 'mypay'),
      'ALIPAY' => __('ALIPAY', 'mypay'),
      'SMARTPAY' => __('SMARTPAY', 'mypay'),
      'MATM' => __('MATM', 'mypay'),
      'WECHAT' => __('WECHAT', 'mypay'),
      'DIRECTDEBIT' => __('DIRECTDEBIT', 'mypay'),
      'LINEPAYON' => __('LINEPAYON', 'mypay'),
      'LINEPAYOFF' => __('LINEPAYOFF', 'mypay'),
      'QQ' => __('QQ', 'mypay'),
      'QQH5' => __('QQH5', 'mypay'),
      'WECHATH5' => __('WECHATH5', 'mypay'),
      'APPLEPAY' => __('APPLEPAY', 'mypay'),
    );

    return $payment_desc[$payment_name];
  }

  /**
   * Add a next line character
   * @param string   content
   * @return string   content with next line character
   */
  private function add_next_line($content)
  {
    return $content . "\n";
  }

  /**
   * Format the version description
   * @param string   version string
   * @return string   version description
   */
  private function format_version_desc($version)
  {
    return str_replace('.', '_', $version);
  }

  /**
   * Check if the order status is complete
   * @param object   order
   * @return boolean  is the order complete
   */
  private function is_order_complete($order)
  {
    $status = '';
    $status = (method_exists($order, 'get_status') == true) ? $order->get_status() : $order->status;

    if ($status == 'pending') {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Get the payment method from the payment_type
   * @param string   payment type
   * @return string   payment method
   */
  private function get_payment_method($payment_type)
  {
    $info_pieces = explode('_', $payment_type);

    return $info_pieces[0];
  }

  /**
   * Get the order comments
   * @param array    MYPay feedback
   * @return string   order comments
   */
  function get_order_comments($mypay_feedback)
  {
    $comments = array(
      'ATM' =>
        sprintf(
          __('Bank Code : %s<br />Virtual Account : %s<br />Payment Deadline : %s<br />', 'mypay'),
          $mypay_feedback['BankCode'],
          $mypay_feedback['vAccount'],
          $mypay_feedback['ExpireDate']
        ),
      'CVS' =>
        sprintf(
          __('Trade Code : %s<br />Payment Deadline : %s<br />', 'mypay'),
          $mypay_feedback['PaymentNo'],
          $mypay_feedback['ExpireDate']
        ),
      'BARCODE' =>
        sprintf(
          __('Payment Deadline : %s<br />BARCODE 1 : %s<br />BARCODE 2 : %s<br />BARCODE 3 : %s<br />', 'mypay'),
          $mypay_feedback['ExpireDate'],
          $mypay_feedback['Barcode1'],
          $mypay_feedback['Barcode2'],
          $mypay_feedback['Barcode3']
        )
    );
    $payment_method = $this->get_payment_method($mypay_feedback['PaymentType']);

    return $comments[$payment_method];
  }

  /**
   * Complete the order and add the comments
   * @param object   order
   */
  function confirm_order($order, $comments, $mypay_feedback)
  {
    $order->add_order_note($comments, true);

    $order->payment_complete();

    // 加入信用卡後四碼，提供電子發票開立使用 v1.1.0911
    if (isset($mypay_feedback['card4no']) && !empty($mypay_feedback['card4no'])) {
      add_post_meta($order->get_id(), 'card4no', $mypay_feedback['card4no'], true);
    }

    // call invoice model
    $invoice_active_mypay = 0;
    $invoice_active_allpay = 0;

    $active_plugins = (array)get_option('active_plugins', array());

    $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));

    foreach ($active_plugins as $key => $value) {
      if ((strpos($value, '/woocommerce-mypayinvoice.php') !== false)) {
        $invoice_active_mypay = 1;
      }

      if ((strpos($value, '/woocommerce-allpayinvoice.php') !== false)) {
        $invoice_active_allpay = 1;
      }
    }

    if ($invoice_active_mypay == 0 && $invoice_active_allpay == 1) { // allpay
      if (is_file(get_home_path() . '/wp-content/plugins/allpay_invoice/woocommerce-allpayinvoice.php')) {
        $aConfig_Invoice = get_option('wc_allpayinvoice_active_model');

        // 記錄目前成功付款到第幾次
        $nTotalSuccessTimes = (isset($mypay_feedback['TotalSuccessTimes']) && (empty($mypay_feedback['TotalSuccessTimes']) || $mypay_feedback['TotalSuccessTimes'] == 1)) ? '' : $mypay_feedback['TotalSuccessTimes'];
        update_post_meta($order->get_id(), '_total_success_times', $nTotalSuccessTimes);

        if (isset($aConfig_Invoice) && $aConfig_Invoice['wc_allpay_invoice_enabled'] == 'enable' && $aConfig_Invoice['wc_allpay_invoice_auto'] == 'auto') {
          do_action('allpay_auto_invoice', $order->get_id(), $mypay_feedback['SimulatePaid']);
        }
      }
    } elseif ($invoice_active_mypay == 1 && $invoice_active_allpay == 0) { // mypay

      if (is_file(get_home_path() . '/wp-content/plugins/mypay_invoice/woocommerce-mypayinvoice.php')) {
        $aConfig_Invoice = get_option('wc_mypayinvoice_active_model');

        // 記錄目前成功付款到第幾次
        $nTotalSuccessTimes = (isset($mypay_feedback['TotalSuccessTimes']) && (empty($mypay_feedback['TotalSuccessTimes']) || $mypay_feedback['TotalSuccessTimes'] == 1)) ? '' : $mypay_feedback['TotalSuccessTimes'];
        update_post_meta($order->get_id(), '_total_success_times', $nTotalSuccessTimes);

        if (isset($aConfig_Invoice) && $aConfig_Invoice['wc_mypay_invoice_enabled'] == 'enable' && $aConfig_Invoice['wc_mypay_invoice_auto'] == 'auto') {
          do_action('mypay_auto_invoice', $order->get_id(), $mypay_feedback['SimulatePaid']);
        }
      }
    }
  }

  /**
   * Output for the order received page.
   *
   * @param int $order_id
   */
  public function thankyou_page($order_id)
  {

    $this->payment_details($order_id);

  }


  /**
   * Get payment details and place into a list format.
   *
   * @param int $order_id
   */
  private function payment_details($order_id = '')
  {
    $order = new WC_Order($order_id);
    $order_data = $order->get_data();

    get_header();

    $order_details = $order->get_items();
    echo "<div id='primary' class='content-area'>";
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<td>商品名稱</td>";
    echo "<td>商品單價</td>";
    echo "<td>商品數量</td>";
    echo "<td>商品小計</td>";
    echo "</tr>";
    echo "</thead>";
    foreach ($order_details as $detail) {
      $detail_data = $detail->get_data();
      $product = $detail->get_product();
      echo "<tr>";
      echo "<td>" . $detail_data['name'] . "</td>";
      echo "<td>" . $product->get_price() . "</td>";
      echo "<td>" . $detail_data['quantity'] . "</td>";
      echo "<td>" . $detail_data['total'] . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "<table>";
    echo "<tr>";
    echo "<td>折扣 : </td>";
    echo "<td>" . $order_data['discount_total'] . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>總額 : </td>";
    echo "<td>" . $order_data['total'] . "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";

    get_sidebar();
    get_footer();
  }
}

