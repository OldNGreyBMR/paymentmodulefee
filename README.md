Version v2.0.5
==============
payment module fee for zc158a to 2.2.2 and PHP8.2 to PHP8.5  
This module allows Zen Cart stores to add a fee or a discount based on payment module selection.  

2026-07-28  v2.0.5 Fully encapsulated with installer to remove legacy files (corrected); removed redundant lines; placement of '-' sign before '$' sign to match ot_coupon.php;  tidy code 
```
The module follows the original concept and discounts or adds surcharge on the total price.  
    If tax is selected, the tax is applied to the discount; eg if you want a discount of -3% including tax (where tax is 10%) the discount entered should be -3% /1.1 = -2.73. [ Total Price / (1 + Tax Rate) = Base Price ex Tax].  
    This will apply a tax of 10% to the discount making the discount shown as -3% inc.  
    The final tax amount on the order includes the tax on the discount or surcharge.  
    The final total includes the discount or surcharge.  
~~~