<?php
/*
Plugin Name: Womens World Banking Authorize.net
Plugin URI: http://gorosterrazu.com.ar/
Description: This is a plugin made for Womens World Banking to create one time and recurring payments
Author: Gaston Gorosterrazu
Version: 1
Author URI: http://gorosterrazu.com.ar/
*/

// Actions / Hooks / Shortcodes
add_action( 'wp_ajax_authnet_recurring', 'authnet_recurring' );
add_action( 'wp_ajax_nopriv_authnet_recurring', 'authnet_recurring' );


include_once ("authnet/authnetfunction.php");

function authnet_getstatus( $subscription_id ) {
	include_once ("authnet/data.php");

	$content =
        "<?xml version=\"1.0\" encoding=\"utf-8\"?>".
        "<ARBGetSubscriptionStatusRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".
        "<merchantAuthentication>".
        "<name>" . $loginname . "</name>".
        "<transactionKey>" . $transactionkey . "</transactionKey>".
        "</merchantAuthentication>" .
        "<subscriptionId>" . $subscription_id . "</subscriptionId>".
        "</ARBGetSubscriptionStatusRequest>";

	//send the xml via curl
	$response = send_request_via_curl($host,$path,$content);

	if ($response) {
		list ($resultCode, $code, $text, $subscriptionId) =parse_return($response);

		echo " Response Code: $resultCode <br>";
		echo " Response Reason Code: $code<br>";
		echo " Response Text: $text<br>";
		echo " Subscription Id: $subscriptionId <br><br>";
	}
}

function authnet_single( $data = false) {
	include_once ("authnet/data.php");

	if ( function_exists($_POST['type'].'_create') ) $refId = call_user_func($_POST['type'].'_create', $_POST);
	else $refId = time();

	if ( !$data ) {
		$amount = str_replace(Array(','),Array(''), $_POST["amount"]);
		$length = isset( $_POST["length"] )?$_POST["length"]:1;
		$unit = isset( $_POST["unit"] )?$_POST['unit']:'months';
		$startDate = isset( $_POST["startDate"] )?$_POST["startDate"]:date('Y-m-d');
		$totalOccurrences = isset( $_POST["totalOccurrences"] )?$_POST["totalOccurrences"]:9999;
		$cardNumber = $_POST["cardNumber"];
		$expirationDate = substr($_POST['expirationDate'],3,4).'-'.substr($_POST['expirationDate'],0,2);
		$cardCode = $_POST['cardCode'];
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$company = $_POST['company'];
		$address = $_POST['address_1'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		$country = $_POST['country'];
		$email = $_POST['email'];	
	} else {
		extract($data);
	}

	if (!isset($data['lineItem'])) {
		$data['lineItem'] = Array();
		$data['lineItem']['name'] = 'Donation';
		$data['lineItem']['description'] = 'Single donation';
	}

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	//build XML to post
	$content = '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
		<merchantAuthentication><name>'.$loginname.'</name><transactionKey>'.$transactionkey.'</transactionKey></merchantAuthentication>
		<refId>'.$refId.'</refId>
		<transactionRequest>
			<transactionType>authCaptureTransaction</transactionType>
			<amount>'.$amount.'</amount>
			<payment>
				<creditCard>
					<cardNumber>'.$cardNumber.'</cardNumber>
					<expirationDate>'.$expirationDate.'</expirationDate>
					<cardCode>'.$cardCode.'</cardCode>
				</creditCard>
			</payment>
			<lineItems>
				<lineItem>
					<itemId>1</itemId>
					<name>'.$data['lineItem']['name'].'</name>
					<description>'.$data['lineItem']['description'].'</description>
					<quantity>1</quantity>
					<unitPrice>'.$amount.'</unitPrice>
				</lineItem>
			</lineItems>
			<customer>
				<email>'.$email.'</email>
			</customer>
			<billTo>
				<firstName>'.$first_name.'</firstName>
				<lastName>'.$last_name.'</lastName>
				<company>'.$company.'</company>
				<address>'.$address.'</address>
				<city>'.$city.'</city>
				<state>'.$state.'</state>
				<zip>'.$zip.'</zip>
				<country>'.$country.'</country>
			</billTo>
			<customerIP>'.$ip.'</customerIP>
    	</transactionRequest></createTransactionRequest>';
	$response = send_request_via_curl($host,$path,$content);
	if ($response) {
		$result = parse_return($response);
		if ( function_exists($_POST['type'].'_update') ) call_user_func($_POST['type'].'_update', $refId, Array(
			'status' => $result[1],
			'result_code' => $result[2],
			'result_text' => $result[3],
			'transaction_id' => $result[4]
		));
		if ($result[1] == 'Ok') {
			return 'Ok';
		} else {
			return '<strong>Error: </strong>'.$result[3].'<br>'.$result[2];
		}
		exit;
	} else {
		return "Transaction Failed to $host $path <br>";
	}
}

function authnet_recurring() {
	include_once ("authnet/data.php");

	$refId = call_user_func($_POST['type'].'_create', $_POST);

	$amount = $_POST["amount"];
	$length = isset( $_POST["length"] )?$_POST["length"]:1;
	$unit = isset( $_POST["unit"] )?$_POST['unit']:'months';
	$startDate = isset( $_POST["startDate"] )?$_POST["startDate"]:date('Y-m-d');
	$totalOccurrences = isset( $_POST["totalOccurrences"] )?$_POST["totalOccurrences"]:9999;
	$cardNumber = $_POST["cardNumber"];
	$expirationDate = substr($_POST['expirationDate'],3,4).'-'.substr($_POST['expirationDate'],0,2);
	$cardCode = $_POST['cardCode'];
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];

	//build xml to post
	$content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
		<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
		<merchantAuthentication>
			<transactionKey>" . $transactionkey . "</transactionKey>
		</merchantAuthentication>
		<refId>" . $refId . "</refId>
		<subscription>
			<name>" . $first_name . " " . $last_name . "</name>
			<paymentSchedule>
				<interval>
					<length>". $length ."</length>
					<unit>". $unit ."</unit>
				</interval>
				<startDate>" . $startDate . "</startDate>
				<totalOccurrences>". $totalOccurrences . "</totalOccurrences>
			</paymentSchedule>
			<amount>". $amount ."</amount>
			<payment>
				<creditCard>
					<cardNumber>" . $cardNumber . "</cardNumber>
					<expirationDate>" . $expirationDate . "</expirationDate>
					<cardCode>". $cardCode . "</cardCode>
				</creditCard>
			</payment>
			<order>
				<description>Recurring donation</description>
			</order>
			<customer>
				<email>".$email."</email>
			</customer>
	        	<billTo>".
					"<firstName>". $first_name . "</firstName>".
        			"<lastName>" . $last_name . "</lastName>".
	        	'</billTo>'.
	        	

	        "</subscription>".
	        "</ARBCreateSubscriptionRequest>";

	$response = send_request_via_curl($host,$path,$content);

	if ($response) {
		$result = parse_return($response);
		call_user_func($_POST['type'].'_update', $refId, Array(
			'status' => $result[1],
			'result_code' => $result[2],
			'result_text' => $result[3],
			'subscription_id' => $result[4]
		));
		if ($result[1] == 'Ok') {
			return 'Ok';
		} else {
			return '<strong>Error: </strong>'.$result[3];
		}
		exit;
	} else {
		return "Transaction Failed. <br>";
	}
}
