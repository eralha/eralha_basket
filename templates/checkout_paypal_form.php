<form action="{paypalurl}" method="post">
  <input type="hidden" name="cmd" value="_xclick" />
  <input type="hidden" name="business" value="{paypalEmail}" />
  <input type="hidden" name="quantity" value="1" />
  <input type="hidden" name="item_name" value="{item_name}" />
  <input type="hidden" name="item_number" value="{item_number}" />
  <input type="hidden" name="amount" value="{amount}" />
  <input type="hidden" name="shipping" value="0.00" />
  <input type="hidden" name="no_shipping" value="1" />
  <input type="hidden" name="cn" value="Comments" />
  <input type="hidden" name="currency_code" value="EUR" />
  <input type="hidden" name="lc" value="EU" />
  <input type="hidden" name="bn" value="PP-BuyNowBF" />
  <input type="hidden" name="return" value="{paypalReturnURL}" />
  <input type="hidden" name="rm" value="2">  
  <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_buynow_SM.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
  <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>