<?php
/**
 * ot_payment_module_fee
 *
 * @package orderTotal
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ot_paymentmodulefee.php 5 2011-05-12 02:45:10Z numinix $
 */ 
//  BMH 2020-11-06 update for PHP 7.4
//  BMH 2022-09-06  lns 75 76 include postage costs so fee is on total cost
//  BMH 2022-09-06  PHP 8.1.9 compatible; skip value for TEXT_UNKNOWN_TAX_RATE
 // BMH 2022-09-15  PHP 8.0 no-numeric ln35 
 // BMH 2022-09-25  ln44 undefined pass
 // BMH 2022-09-30  ln28 init $payment_module_fee
 // BMH 202210-01   ln60 Undefined index: payment => condition on line 60
 
  class ot_paymentmodulefee {
    var $title, $output;


    //function ot_paymentmodulefee() { // BMH 2020-11-06
	function __construct() {
        
      $this->code = 'ot_paymentmodulefee';
      $this->title = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TITLE;
      $this->description = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESCRIPTION;
      $this->sort_order = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER;
      $this->payment_modules = explode(',', MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES); 
      $this->output = array();
      $payment_module_fee = '';     // BMH
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
        
        if ($pass == true) {  // BMH
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
                    // change to include postage in discount eg discount on total payment // BMH
                $payment_subtotal_plus_shipping = $order->info['subtotal'] + $order->info['shipping_cost']; // BMH
                $payment_module_fee = ($payment_subtotal_plus_shipping * ((int)$this->payment_fee/100)); // BMH
                // BMH  $payment_module_fee = ($order->info['subtotal'] * ((int)$this->payment_fee/100)); // BMH original lines
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
            // BMH continue and bypass code 
            //echo '<br> ln 105 NO PAYMENT SELECTED';
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
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Payment Modules', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES', 'paypalwpp,paypal', 'Enter the payment module codes separate by commas (no spaces)', '6', '4', '', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Minimum Amount', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_MIN', '200', 'Only charge a fee on orders under a specified amount (Enter 0 to always require a fee)', '6', '5', '', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Fee', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE', '2%,5', 'For Percentage Calculation - include a % Example: 10%<br />For a flat amount just enter the amount - Example: 5 for $5.00', '6', '5', '', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Attach Payment Module Fee On Orders Made', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESTINATION', 'both', 'Attach payment module fee for orders sent to the set destination.', '6', '6', 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS', '0', 'Use the following tax class on the payment module fee.', '6', '7', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
	    global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>