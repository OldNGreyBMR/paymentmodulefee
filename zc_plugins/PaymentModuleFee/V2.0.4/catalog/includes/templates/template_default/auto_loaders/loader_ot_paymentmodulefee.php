<?php
/**
* @package Pages
* @copyright Copyright 2008-2009 RubikIntegration.com
* @copyright Copyright 2003-2023 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: loader_ot_paymentmodulefee.php V2.0.0 BMH (OldNGreY) 2023-01-28
 ZC158 PHP8.1 
*/                                             
if (MODULE_ORDER_TOTAL_PAYMENTMODULEFEE_STATUS == 'true') {                                                            
  $loaders[] = array('conditions' => array('pages' => array('checkout', 'quick_checkout')),
										  'jscript_files' => array(
										    'jscript/jquery.min.js' => 1,
                        'jquery/jquery_ot_paymentmodulefee.js' => 2										
                      )
                    );  
}
