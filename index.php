<?php
date_default_timezone_set('Asia/Manila');
?>

<form name="paypalForm" action="paypal.v2.php" method="post">
<input type="hidden" name="id" value="123">
<input type="hidden" name="CatDescription" value="Nike Janoski Neon White">
                       <input type="hidden" name="payment" value="10">  
                       <input type="hidden" name="key" value="<? echo md5(date("Y-m-d:").rand()); ?>">
                           
                                    
<input type="image" SRC="http://www.coachsbr.com/images/site/paypal_button.gif" name="paypal"  value="Payment via Paypal" >
</form>
