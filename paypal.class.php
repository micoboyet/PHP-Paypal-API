<?php

class PaypalClass 
{
   public $paypal_url;  
   public $last_error;
   public $ipn_log;
   public $ipn_log_file;
   public $ipn_response;
   public $recurring_mode;
   public $ipn_data = array();
   public $fields = array();

   public function __construct()
   {
      $this->last_error = '';
      $this->recurring_mode = false;
      $this->ipn_log_file = '.ipn_results.log';
      $this->ipn_log = true; 
      $this->ipn_response = '';
                                                                
      $this->add_field('rm','2');
   }

   public function payment_mode($mode = 'sandbox')
   {
      if($mode == 'live')
         $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
      else
         $this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
   }

   public function recurring_mode($mode = 'false')
   {
      $this->recurring_mode = $mode;

      if($mode)
      {
         $this->add_field('cmd','_xclick-subscriptions'); 
      }
      else
         $this->add_field('cmd','_xclick'); 
   }

   public function subscription_terms($amount, $time_period, $time_period_limit = 1)
   {
      if($this->recurring_mode)
      {
         $this->add_field('currency_code','USD'); 
         $this->add_field('no_shipping','2'); 

         $this->add_field('a3', $amount);
         $this->add_field('t3', $time_period);
         $this->add_field('p3', $time_period_limit);
         $this->add_field('src', '1');
         $this->add_field('sra', '1');
      }
   }
   
   public function add_field($field, $value) 
   {
      $this->fields["$field"] = $value;
   }

   public function submit() 
   {
      echo "<html>\n";
      echo "<head><title>Processing Payment...</title></head>\n";
      echo "<body onLoad=\"document.forms['paypal_form'].submit();\">\n";
      echo "<center><h2>Please wait, your order is being processed and you";
      echo " will be redirected to the paypal website.</h2></center>\n";
      echo "<form method=\"post\" name=\"paypal_form\" ";
      echo "action=\"".$this->paypal_url."\">\n";

      foreach ($this->fields as $name => $value) 
      {
         echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
      }

      echo "<center><br/><br/>If you are not automatically redirected to ";
      echo "paypal within 5 seconds...<br/><br/>\n";
      echo "<input type=\"submit\" value=\"Click Here\"></center>\n";
      
      echo "</form>\n";
      echo "</body></html>\n";
   }
   
   public function validate_ipn() 
   {
      $url_parsed=parse_url($this->paypal_url);        

      $post_string = '';  

      foreach ($_POST as $field=>$value) 
      { 
         $this->ipn_data["$field"] = $value;
         $post_string .= $field.'='.urlencode(stripslashes($value)).'&'; 
      }

      $post_string.="cmd=_notify-validate"; 

      $fp = fsockopen($url_parsed[host],"80",$err_num,$err_str,30); 
      if(!$fp) 
      {
         $this->last_error = "fsockopen error no. $errnum: $errstr";
         $this->log_ipn_results(false);       
         return false;
         
      } 
      else 
      { 
         fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n"); 
         fputs($fp, "Host: $url_parsed[host]\r\n"); 
         fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
         fputs($fp, "Content-length: ".strlen($post_string)."\r\n"); 
         fputs($fp, "Connection: close\r\n\r\n"); 
         fputs($fp, $post_string . "\r\n\r\n"); 

         while(!feof($fp)) 
         { 
            $this->ipn_response .= fgets($fp, 1024); 
         } 

         fclose($fp); 
      }
      
      if (eregi("VERIFIED",$this->ipn_response)) 
      {
         $this->log_ipn_results(true);
         return true;       
         
      } 
      else 
      {
         $this->last_error = 'IPN Validation Failed.';
         $this->log_ipn_results(false);   
         return false;
         
      }
      
   }
   
   public function log_ipn_results($success) 
   {
       
      if (!$this->ipn_log) return; 
      
      $text = '['.date('m/d/Y g:i A').'] - '; 
      
      if ($success) 
         $text .= "SUCCESS!\n";
      else 
         $text .= 'FAIL: '.$this->last_error."\n";
      
      $text .= "IPN POST Vars from Paypal:\n";

      foreach ($this->ipn_data as $key=>$value) 
      {
         $text .= "$key=$value, ";
      }
 
      $text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;
      
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text . "\n\n"); 

      fclose($fp);  
   }

   public function dump_fields() 
   {
      echo "<h3>paypal_class->dump_fields() Output:</h3>";
      echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>"; 
      
      ksort($this->fields);

      foreach ($this->fields as $key => $value)
       {
         echo "<tr><td>$key</td><td>".urldecode($value)."&nbsp;</td></tr>";
      }
 
      echo "</table><br>"; 
   }
}         


 
