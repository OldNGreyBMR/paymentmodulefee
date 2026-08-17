Version v2.0.5
==============
payment module fee for zc158a to 2.2.2 and PHP8.2 to PHP8.5
This module allows Zen Cart stores to add a fee or a discount based on payment module selection.

===================================================
Changes to payment_module_fee 1.1.0 to make Version 2.0.0
Changes to payment_module_fee 1.1.0 to make Version 2.0.0
Compatible with Zen Cart 157d 158 and PHP7.4 to PHP8.2
2020-11-06 update for PHP 7.4
2022-02-24 Update includes\modules\order_total\ot_paymentmodulefee.php
        to function __construct() { 
        change includes\templates\bmh_bootstrap\auto_loaders\loader_ot_paymentmodulefee.php to use  jscript\jquery.min.js which is the latest jquery version (from 1.6)
2022-09-06  lns 75 76 include postage costs so fee is on total cost
2022-09-06  PHP 8.1.9 compatible; skip value for TEXT_UNKNOWN_TAX_RATE
2022-09-15  PHP 8.0 no-numeric ln35 
2022-09-25  ln44 undefined pass
2022-09-30  ln28 init $payment_module_fee
2022-10-01  ln60 Undefined index: payment => conditional branch on line 60
2022-10-01  zc158 lang file in includes\languages\english\modules\order_total
2023-01-28  ln20 define MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER
2023-01-28  ln22 MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES
2023-01-30  PHP8.2 declared all class variables
2023-02-15  Undefined variable: pass
2023-05-14  include coupons and group discount; added version for admin display
2023-05-15  initialise group_discountfee
2024-04-01  change (int) to (float) to allow discount amt < 1
2024-04-03  allow for no value set for coupon and discount fee [Scott Wilson email 2024-04-03]
2026-06-14  v2.0.4 Encapsulated version only. Compatible with Zen Cart 1.5.8a to 2.2.2; PHP 8.2 to 8.5; Installer removes legacy files
2026-07-28  v2.0.5 Fully encapsulated with installer to remove legacy files (corrected); removed redundant lines; placement of '-' sign before '$' sign to match ot_coupon.php;  tidy code 

     The module follows the original concept and discounts or adds surcharge on the total price. 
     If tax is selected, the tax is applied to the discount; eg if you want a discount of -3% including tax (where tax is 10%) the discount entered should be -3% /1.1 = -2.73. [ Total Price / (1 + Tax Rate) = Base Price ex Tax]. This will apply a tax of 10% to the discount making the discount shown as -3% inc.
     The final tax amount on the order includes the tax on the discount or surcharge.
     The final total includes the discount or surcharge.
