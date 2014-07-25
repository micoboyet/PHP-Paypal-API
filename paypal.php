<?php
require_once('PaypalClass.php'); 

$paypal = new PaypalClass();       
$paypal->payment_mode('sandbox');

$baseUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$merchant_email = 'micoboyet.sandbox-facilitator@yahoo.com';

if (empty($_GET['action'])) 
  $_GET['action'] = 'process';  

switch ($_GET['action']) 
{
   case 'process':      
      
      $CatDescription = (string) $_REQUEST['CatDescription'];
      $payment = (string) $_REQUEST['payment'];
      $id = (int) $_REQUEST['id'];
      $key = (string) $_REQUEST['key'];

      $time_period = 'M';
      $time_period_limit = 1;

      $paypal->add_field('business', $merchant_email);
      $paypal->add_field('return', $baseUrl.'?action=success');
      $paypal->add_field('cancel_return', $baseUrl.'?action=cancel');
      $paypal->add_field('notify_url', $baseUrl.'?action=ipn');
      $paypal->add_field('item_name', $CatDescription);
      $paypal->add_field('amount', $payment);
      $paypal->add_field('key', $key);
      $paypal->add_field('item_number', $id);

      $paypal->recurring_mode(true);
      $paypal->subscription_terms($payment, $time_period, $time_period_limit);   

      $paypal->submit();
      break;
      
   case 'success':      

      echo "<br/><p><b>Thank you for your Donation. </b><br /></p>";
      
      foreach ($_POST as $key => $value) 
      { 
          echo "$key: $value<br>"; 
      }

      break;
      
   case 'cancel':       

      echo "<br/><p><b>The order was canceled!</b></p><br />";
    
      foreach ($_POST as $key => $value) 
      { 
          echo "$key: $value<br>";
      }
      
      break;
      
   case 'ipn':   
      
      if ($paypal->validate_ipn()) 
      { 
         $dated = date("D, d M Y H:i:s", time()); 
         
         $subject = 'Instant Payment Notification - Received Payment';
         $to = $merchant_email;   
         $body =  "An instant payment notification was successfully recieved\n";
         $body .= "from ".$paypal->ipn_data['payer_email']." on ".date('m/d/Y');
         $body .= " at ".date('g:i A')."\n\nDetails:\n";
         $headers = "";
         $headers .= "From: Test Paypal \r\n";
         $headers .= "Date: $dated \r\n";
        
        $PaymentStatus = $paypal->ipn_data['payment_status']; 
        $Email        =  $paypal->ipn_data['payer_email'];
        $id           =  $paypal->ipn_data['item_number'];
        
        if($PaymentStatus == 'Completed' or $PaymentStatus == 'Pending')
            $PaymentStatus = '2';
        else
            $PaymentStatus = '1';

        foreach ($paypal->ipn_data as $key => $value) 
        { 
            $body .= "\n$key: $value"; 
        }

        fopen("http://www.virtualphoneline.com/admins/TestHMS.php?to=".urlencode($to)."&subject=".urlencode($subject)."&message=".urlencode($body)."&headers=".urlencode($headers)."","r");         
      } 

      break;
 }     

?>
