<?php
/**
 * ot_paymentmodulefee.php
 *
 * @package orderTotal
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ot_paymentmodulefee.php zc158 PHP8.2 V2.0.1 BMH (OldNGreY) 2023-01-31
 */ 
 
 if (!defined('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER')) { 
    define('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER', '') ;
 }
 if (!defined('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES')) {
     define('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES', '') ;
 }
  
  class ot_paymentmodulefee {
    public $check_query;            // 
    public $code;                   // $code determines the internal 'code' name used to designate "this" payment module
    public $description;            // $description is a soft name for this payment method  @var string 
    public $output =[];             // $output is an array of the display elements used on checkout pages
    public $pass;                   // $pass configuration check
    public $payment_fee;            // $payment_fee payment fee applied
    public $payment_fees;           // $payment_fees all payment fees
    public $payment_module_fee;     // $payment_module_fee is the cost of the fee or discount
    public $payment_modules =[];    // $payment_modules is an array of available peyment modules 
    public $payment_subtotal_plus_shipping; //
    public $sort_order;             // $sort_order is the order priority of this payment module when displayed  @var int
    public $title;                  // $title is the displayed name for this order total method  @var string
    public $tax;                    // 
    public $tax_address;            // 
    public $tax_description;        // 

    protected $_check;              // $_check is used to check the configuration key set up @var int

	function __construct() {
      $this->code = 'ot_paymentmodulefee';
      $this->title = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TITLE;
      $this->description = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESCRIPTION;
      $this->sort_order = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER;
      $this->payment_modules = explode(',', MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES); 
      $this->output = [];
      $payment_module_fee = '';     
    }

    function process() {
      global $order, $currencies;


      if (MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE_ALLOW == 'true') {
        switch (MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
          case 'both':
            $pass = true; break;
          default:
            $pass = false; break;
        }
        
        if ($pass == true) {  
          if (MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_MIN > 0) {
            if (MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_MIN > $order->info['subtotal']) {
              $pass = true;
            } else {
              $pass = false;
            }
          } else {
            $pass = true;
          }
        }
        if (isset($_SESSION['payment'])) {  // BMH continue as payment type selected avoids PHP 8.0 error      
        
            if (($pass  == true) && in_array($_SESSION['payment'], $this->payment_modules)) { // BMH 
          $charge_it = 'true';
          if ($charge_it == 'true') {
            $tax_address = zen_get_tax_locations();

            $tax = zen_get_tax_rate(MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS, $tax_address['country_id'], $tax_address['zone_id']);

            $tax_description = zen_get_tax_description(MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS, $tax_address['country_id'], $tax_address['zone_id']);
            
            $key = array_search($_SESSION['payment'], $this->payment_modules);

            $this->payment_fees = explode(',', MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE);
            $this->payment_fee = $this->payment_fees[$key]; 
            // calculate from flat fee or percentage
            if (substr($this->payment_fee, -1) == '%') {
              $payment_subtotal_plus_shipping = $order->info['subtotal'] + $order->info['shipping_cost']; 
              $payment_module_fee = ($payment_subtotal_plus_shipping * ((int)$this->payment_fee/100)); 
             
            } else {
              $payment_module_fee = $this->payment_fee;
            }
            $order->info['tax'] += zen_calculate_tax($payment_module_fee, $tax);    
            
            if ($tax_description != TEXT_UNKNOWN_TAX_RATE) { // BMH TEXT_UNKNOWN_TAX_RATE value set to 'Sales Tax' returned by function
                $order->info['tax_groups']["$tax_description"] += zen_calculate_tax($payment_module_fee, $tax);
                }
            $order->info['total'] += $payment_module_fee + zen_calculate_tax($payment_module_fee, $tax);
            if (DISPLAY_PRICE_WITH_TAX == 'true') {
              $payment_module_fee += zen_calculate_tax($payment_module_fee, $tax);
            }

            $this->output[] = array('title' => $this->title . ':',
                                    'text' => $currencies->format($payment_module_fee, true, $order->info['currency'], $order->info['currency_value']),
                                    'value' => $payment_module_fee);
          }
            } // eof $pass  == true) && in_array($_SESSION['payment']
        } // BMH eof bypass
        else {
            // BMH continue and bypass code // NO PAYMENT SELECTED';
        }
      }
    }

    function check() {
	  global $db;
      if (!isset($this->_check)) {
        $check_query = "select configuration_value
                        from " . TABLE_CONFIGURATION . "
                        where configuration_key = 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_STATUS'";

        $check_query = $db->Execute($check_query);
        $this->_check = $check_query->RecordCount();
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_STATUS', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_MIN', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE_ALLOW', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESTINATION', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS');
    }

    function install() {
      global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER', '500', 'Sort order of display.', '6', '2', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Payment Module Fee', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE_ALLOW', 'false', 'Do you want to allow payment module fees?', '6', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Payment Modules', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES', 'moneyorder,paypalwpp,paypal', 'Enter the payment module codes separate by commas (no spaces)', '6', '4', '', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Minimum Amount', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_MIN', '200', 'Only charge a fee on orders under a specified amount (Enter 0 to always require a fee)', '6', '5', '', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Fee', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE', '-3%,2%,5', 'For Percentage Calculation - include a % Example: 10%<br />For a flat amount just enter the amount - Example: 5 for $5.00. Negative (-) gives a discount.', '6', '5', '', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Attach Payment Module Fee On Orders Made', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESTINATION', 'both', 'Attach payment module fee for orders sent to the set destination.', '6', '6', 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS', '0', 'Use the following tax class on the payment module fee.', '6', '7', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
	    global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>