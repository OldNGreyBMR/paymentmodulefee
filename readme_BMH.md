Version 2.0.2a
=============
Compatible with Zen Cart 157d and ZC158a and PHP7.4 to PHP8.2
Added version number to admin display
includes group discounts and coupon credit notes

Changes to payment_module_fee 1.1.0 to make Version 2.0.0
Compatible with Zen Cart 157d 158 and PHP7.4 to PHP8.2
===================================================
BMH 2020-11-06 update for PHP 7.4
BMH update 2022-02-24
    Update includes\modules\order_total\ot_paymentmodulefee.php
        to function __construct() { 
    on line 14
    change includes\templates\bmh_bootstrap\auto_loaders\loader_ot_paymentmodulefee.php to use  jscript\jquery.min.js which is the latest jquery version (from 1.6)
    
BMH 2022-09-06  lns 75 76 include postage costs so fee is on total cost
BMH 2022-09-06  PHP 8.1.9 compatible; skip value for TEXT_UNKNOWN_TAX_RATE
BMH 2022-09-15  PHP 8.0 no-numeric ln35 
BMH 2022-09-25  ln44 undefined pass
BMH 2022-09-30  ln28 init $payment_module_fee
BMH 2022-10-01  ln60 Undefined index: payment => conditional branch on line 60
BMH 2022-10-01  zc158 lang file in includes\languages\english\modules\order_total
BMH 2023-01-28  ln20 define MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_SORT_ORDER
BMH 2023-01-28  ln22 MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_PAYMENT_MODULES
BMH 2023-01-30 PHP8.2 declared all class variables
