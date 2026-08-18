<?php
/**
 * ot_paymentmodulefee.php
 *
 * @package orderTotal
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ot_paymentmodulefee.php zc158a - 2.2.2 PHP8.2  to 8.5 V2.0.5 BMH (OldNGreY) 2026-07-25
 * @previous_authors  2011-05-12 02:45:10Z numinix $
 * @maintained by oldngrey
 *  version 2.0.5 -
 */
// 2026-07-25 fully encapsulated with installer to remove legacy files (corrected); removed redundant lines
// 2026-07-27 v 2.0.5 correct placement of '-' sign before '$' sign to match ot_coupon.php, using &#8209;  tidy code

if (!defined('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER')) {     define('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER', '');}
if (!defined('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES')) {    define('MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES', '');}
if (!defined('VERSION_PMF')) {    define('VERSION_PMF', '2.0.5');}

class ot_paymentmodulefee
{
    public string $code;                            // $code determines the internal 'code' name used to designate "this" payment module
    public string $description;                     // $description is a soft name for this payment method  @var string
    public $output = [];                            // $output is an array of the display elements used on checkout pages
    public string $payment_fee;                     // $payment_fee payment fee applied
    public array $payment_fees;                     // $payment_fees all payment fees
    public float $payment_module_fee;               // $payment_module_fee is the cost of the fee or discount
    public array $payment_modules;                  // $payment_modules is an array of available payment modules
    public int|null $sort_order;                               // $sort_order is the order priority of this payment module when displayed  @var int
    public string $title;                                      // $title is the displayed name for this order total method  @var string
    public mixed $tax;                                        //
    public string $tax_description;                            //
    /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected $_check;                                  // $_check is used to check the configuration key set up @var int

    // ----  OUTPUT LOGGING      ------------------//
    private ?string $_logDir = DIR_FS_SQL_CACHE;    //
    public ?string $errorString;                        //
    public string $log_file_name = "PaymentFee.log";    //
    // ------ EOF OUTPUT LOGGING -----------------      //

    /**
     * Summary of __construct
     */
    function __construct()
    {
        $this->code = 'ot_paymentmodulefee';
        $this->title = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESCRIPTION . ' V' . VERSION_PMF;
        $this->sort_order = MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER;
        $this->payment_modules = explode(',', MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES);
        $this->output = [];
        $this->payment_module_fee = 0.0;
        $key = '';
    }

    /**
     * Summary of process
     * @return void
     */
    function process()
    {
        global $order, $currencies;
        global $pass;

        if (MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE_ALLOW == 'true') {
            switch (MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESTINATION) {
                case 'national':
                    if ($order->delivery['country_id'] == STORE_COUNTRY)
                        $pass = true;
                    break;
                case 'international':
                    if ($order->delivery['country_id'] != STORE_COUNTRY)
                        $pass = true;
                    break;
                case 'both':
                    $pass = true;
                    break;
                default:
                    $pass = false;
                    break;
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
            if (isset($_SESSION['payment'])) {                                                   // ---- continue as payment module for fee is set in admin

                if (($pass == true) && in_array($_SESSION['payment'], $this->payment_modules)) { //  compare payment module allowed is same as one being used
                    $charge_it = 'true';                                                         //  payment module allowed is same as one being used
                    if ($charge_it == 'true') {
                        $tax_address = zen_get_tax_locations();

                        //$this->_log(msg: 'ln' . __LINE__ . ' $tax_address = ' . serialize($tax_address) ); // BMH DEBUG write to log file

                        $tax = zen_get_tax_rate(MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS, $tax_address['country_id'], $tax_address['zone_id']);

                        $tax_description = zen_get_tax_description(MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS, $tax_address['country_id'], $tax_address['zone_id']);

                        $key = array_search($_SESSION['payment'], $this->payment_modules);

                        $this->payment_fees = array_map('trim', explode(',', MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE)); // trim to remove any spaces

                        $this->payment_fee = $this->payment_fees[$key];

                        // calculate from flat fee or percentage
                        if (substr($this->payment_fee, -1) == '%') {                // ---- fee is a percentage ---------------- //
                            $payment_module_fee = ( $order->info['total']* ((float) $this->payment_fee / 100));

                            // $this->_log(msg: 'ln ' . __LINE__ . ' payment_module_fee is a percentage: ' . $payment_module_fee); // BMH DEBUG write to log file

                        } else {                                                    // ----  fee is not a percentage --------------------- //
                            $payment_module_fee = $this->payment_fee;
                            // $this->_log(msg: 'ln ' . __LINE__ . ' payment_module_fee not a percentage: ' . $payment_module_fee); // BMH DEBUG write to log file
                        }

                        $order->info['tax'] += zen_calculate_tax($payment_module_fee, $tax);

                        if ($tax_description != TEXT_UNKNOWN_TAX_RATE) {                // BMH TEXT_UNKNOWN_TAX_RATE value set to 'Sales Tax' returned by function
                            $order->info['tax_groups']["$tax_description"] += zen_calculate_tax($payment_module_fee, $tax);
                        }

                        $order->info['total'] += $payment_module_fee + zen_calculate_tax($payment_module_fee, $tax);

                        $order->info['total'] = round($order->info['total'], 2);

                        if (DISPLAY_PRICE_WITH_TAX == 'true') {
                            $payment_module_fee += zen_calculate_tax($payment_module_fee, $tax);
                        }

                        $this->output[] = array(
                            'title' => rtrim($this->title , ' :') . ':',
                            'text' => ($payment_module_fee < 0 ? '&#8209;' : '') . $currencies->format(abs($payment_module_fee), true, $order->info['currency'], $order->info['currency_value']),
                                // &#8209; is a non-break-hyphen so displays with number
                            'value' => $payment_module_fee
                        );
                    }
                } // eof $pass  == true) && in_array($_SESSION['payment']
            } //  eof bypass
            else {
                // continue and bypass code // NO PAYMENT SELECTED';
            }
        }
    }
    // ---- DEBUGGING LOG FILE OUTPUT ----------------------------- //
    /**
     * Write to log file
     *  Prints error with purchase order id and time + date
     * @param  string $msg          error message
     * @param  string $suffix
     */
    private function _log($msg, $suffix = '')
    {

        $file = $this->_logDir . '/' . $this->log_file_name;
        if ($fp = @fopen($file, 'a')) {
            $today = date("Y-m-d_H:i:s");         //
            @fwrite($fp, "" . time() . ": " . $today . ": " . $msg . " " .  "\r\n"); // store epoch time + date
            @fclose($fp);
        }
    }
    // ---- EOF DEBUGGING OUTPIUT  -------------------------------- //

    // ----- ADMIN FEATURES -------------------------------------- //
    /**
     * Summary of check
     * @return int
     */
    function check()
    {
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

    /**
     * Summary of keys
     * @return string[]
     */
    function keys()
    {
        return array(
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_STATUS',
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER',
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE',
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_MIN',
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE_ALLOW',
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES',
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESTINATION',
            'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS',
        );
    }

    /**
     * Summary of install
     * @return void
     */
    function install()
    {
        global $db;
        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
        values ('This module is installed', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
        values ('Sort Order', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER', '650', 'Sort order of display. Should be last item before Total', '6', '2', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
        values ('Allow Payment Module Fee', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE_ALLOW', 'false', 'Do you want to allow payment module fees?', '6', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added)
        values ('Payment Modules', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES', 'moneyorder,paypalwpp,paypal', 'Enter the payment module codes separate by commas (no spaces)', '6', '4', '', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added)
        values ('Minimum Amount', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_MIN', '200', 'Only charge a fee on orders under a specified amount (Enter 0 to always require a fee)', '6', '5', '', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added)
        values ('Fee', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_FEE', '-3%,2%,5', 'For Percentage Calculation - include a % Example: 10%<br />For a flat amount just enter the amount - Example: 5 for $5.00. Negative (-) gives a discount.', '6', '5', '', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
        values ('Attach Payment Module Fee On Orders Made', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_DESTINATION', 'both', 'Attach payment module fee for orders sent to the set destination.', '6', '6', 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added)
        values ('Tax Class', 'MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_TAX_CLASS', '0', 'Use the following tax class on the payment module fee.', '6', '7', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    }

    /**
     * Summary of remove
     * @return void
     */
    function remove()
    {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
