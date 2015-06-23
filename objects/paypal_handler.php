<?php

	$listener = new IpnListener();
	$listener->use_sandbox = true;
	//$listener->use_ssl = false;
	//$listener->use_curl = false;

	// Assign payment notification values to local variables
	  $item_name = $_POST['item_name'];
	  $item_number = $_POST['item_number'];
	  $payment_status = $_POST['payment_status'];
	  $payment_amount = $_POST['mc_gross'];
	  $payment_currency = $_POST['mc_currency'];
	  $txn_id = $_POST['txn_id'];
	  $receiver_email = $_POST['receiver_email'];
	  $payer_email = $_POST['payer_email'];

	try {
	    $verified = $listener->processIpn();
	} catch (Exception $e) {
	    // fatal error trying to process IPN.
	    echo $e->getMessage();
	    exit(0);
	}

	if ($verified){
		if($receiver_email == $this->paypalEmail){
			$wpdb->update($table_encomendas, array("vchEstadoEncomenda" => $this->estados[1][0]), array("vchEncRef" => $item_number), array("%d"), array("%s"));
	      	$output .= file_get_contents($pluginDir."templates/checkout_paypal_payment_received.php");

	      	//Send email to the seller to notify that this order is payed.
	      	/*
		      $mail_From = "IPN@example.com";
		      $mail_To = "Your-eMail-Address";
		      $mail_Subject = "VERIFIED IPN";
		      $mail_Body = $req;
		      mail($mail_To, $mail_Subject, $mail_Body, $mail_From);
	      */
		}else{
			$output .= file_get_contents($pluginDir."templates/checkout_paypal_payment_failed.php");
		}
	} else {
	    $output .= file_get_contents($pluginDir."templates/checkout_paypal_payment_failed.php");
	}

?>