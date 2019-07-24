<?php
/*
Plugin Name: Salesforce integration
Plugin URI: http://gorosterrazu.com.ar/
Description: This is a plugin made for Womens World Banking
Author: Gaston Gorosterrazu
Version: 1
Author URI: http://gorosterrazu.com.ar/
*/

define("GGSF_CLIENT_ID", '3MVG98XJQQAccJQdVv2Br2TWnS3oB.zc2qF9jUNrJ_4CSwWuCMpuUxVGa_zJt_JvN1vz8Sbpmthg1AwyY.N2d');
define("GGSF_CLIENT_SECRET", '5181586418508535458');
define("GGSF_USERNAME",'jborja@womensworldbanking.org');
define("GGSF_PASSWORD",'Women11@');
define("GGSF_SECURITYTOKEN",'SZ5qFS97ivxgLCNxYHKQo9hE');
define("GGSF_REDIRECT_URI", "https://72.55.165.119/salesforce/oauth_callback");
define("GGSF_LOGIN_URI", "https://login.salesforce.com");
define("GGSF_PATH_ADD",5);
define("GGSF_ADMIN_EMAIL",'gastonius@gmail.com');

// Actions
add_action( 'init', 'ggsf_custom_post_types' );
add_action( 'add_meta_boxes', 'ggsf_add_meta_box' );
add_action('parse_request', 'ggsf_custom_url_handler');
add_action( 'wp_ajax_salesforce_object_info', 'ggsf_object_info' );

// Connectors
include_once('connectors.php');


// Wordpress Functions
function ggsf_object_info() {
	$object = $_POST['object'];
	echo json_encode( ggsf_get_object_details( $object ) );
	die();
}

function ggsf_custom_post_types() {
	register_post_type('sf_integration', Array(
		'label' => 'Salesforce Integrations',
		'description' => '',
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'capability_type' => 'post',
		'map_meta_cap' => true,
		'hierarchical' => false,
		'rewrite' => array('slug' => 'sfintegration', 'with_front' => true),
		'query_var' => true,
		'has_archive' => true,
		'supports' => array('title','revisions','author','page-attributes'),
		'labels' => array (
			'name' => 'Salesforce Integrations',
			'singular_name' => 'Salesforce Integration',
			'menu_name' => 'Salesforce',
			'add_new' => 'Add new connection',
			'add_new_item' => 'Add new connection',
			'edit' => 'Edit',
			'edit_item' => 'Edit connection',
			'new_item' => 'New connection',
			'view' => 'View Connection',
			'view_item' => 'View Connection',
			'search_items' => 'Search Connections',
			'not_found' => 'No Connections Found',
			'not_found_in_trash' => 'No Connections Found in Trash',
			'parent' => 'Parent Connection',
		)
	));
}

function ggsf_add_meta_box() {
	add_meta_box(
		'ggsf_connection',
		__( 'Connection Setup', 'ggsf' ),
		'ggsf_meta_box_callback',
		'sf_integration',
		'normal',
		'high'
	);
}

function ggsf_meta_box_callback( $post ) {
	wp_nonce_field( 'ggsf_meta_box', 'ggsf_meta_box_nonce' );
	$ggsf_stored_meta = get_post_meta( $post->ID );
	include_once('connector_setup.php');
}

function ggsf_custom_url_handler() {
	if ( substr($_SERVER['REQUEST_URI'],GGSF_PATH_ADD,11) == '/salesforce') { // My area.
		$action = substr($_SERVER['REQUEST_URI'],12 + GGSF_PATH_ADD);
		if (substr($action,0,14) == 'oauth_callback') ggsf_oauth_callback();
		if ($action == 'oauth') ggsf_oauth();
		if ($action == 'demo_rest') ggsf_demo_rest();
		exit;
	}
}

// Salesforce functions
// Login into Salesforce if there's no active session
function ggsf_oauth() {
	$token_url = GGSF_LOGIN_URI . "/services/oauth2/token";

	$params = "grant_type=password"
		. "&client_id=" . GGSF_CLIENT_ID
		. "&client_secret=" . GGSF_CLIENT_SECRET
		. "&username=" . GGSF_USERNAME
		. "&password=" . GGSF_PASSWORD.GGSF_SECURITYTOKEN;

	$curl = curl_init($token_url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

	$response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ( $status == 200) {
		$json_response = json_decode($response);
		$_SESSION['access_token'] = $json_response->access_token;
		$_SESSION['instance_url'] = $json_response->instance_url;
	}
}

function ggsf_get_objects() {
	if ( !isset($_SESSION['access_token']) ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$url = $instance_url.'/services/data/v20.0/sobjects/';
	
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token"));

	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 201 ) {
		$response = json_decode($json_response, true);
		if (isset($response[0]) &&  $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
			ggsf_oauth();
			return ggsf_get_objects();
		} else {
			sf_error('Error: Call to '.$url.' failed with status '.$status."\n".'Response was:'. $json_response.', curl error: '.curl_error($curl)."\n".'Post data is: '.json_encode($_POST));
		}
	}
	curl_close($curl);
	$response = json_decode($json_response, true);
	return $response['sobjects'];
}

function ggsf_get_object_details( $object_name ) {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$url = $instance_url.'/services/data/v20.0/sobjects/'.$object_name.'/describe';

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token"));

	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 201 ) {
		$response = json_decode($json_response, true);
		if (isset($response[0]) &&  $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
			ggsf_oauth();
			return ggsf_get_object_details( $object_name );
		} else {
			sf_error('Error: Call to '.$url.' failed with status '.$status."\n".'Response was:'. $json_response.', curl error: '.curl_error($curl)."\n".'Post data is: '.json_encode($_POST));
		}
	}
	curl_close($curl);

	$response = json_decode($json_response, true);
	return $response;
}

function ggsf_create_object($object, $data) {
	if ( !isset($_SESSION['access_token']) ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$url = "$instance_url/services/data/v20.0/sobjects/".$object."/";

	$content = json_encode($data);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token",
				"Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ( $status != 501 ) {
		$response = json_decode($json_response, true);
		if (isset($response[0]) &&  $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
			ggsf_oauth();
			return ggsf_create_object($object, $data);
		} else {
			sf_error('Error: Call to '.$url.' failed with status '.$status."\n".'Response was:'. $json_response.', curl error: '.curl_error($curl)."\n".'Post data is: '.json_encode($_POST)."\n".'Contents: '.json_encode($content));
		}
	}
	curl_close($curl);
	$response = json_decode($json_response, true);
	return $response;
}

function ggsf_update_object($object, $id, $data) {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$url = "$instance_url/services/data/v20.0/sobjects/$object/$id";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token",
				"Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");

	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status == 204) {
		return false;
	} elseif ( $status != 201 ) {
		$response = json_decode($json_response, true);
		if (isset($response[0]) &&  $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
			ggsf_oauth();
			return ggsf_update_object($object, $id, $data);
		} else {
			sf_error('Error: Call to '.$url.' failed with status '.$status."\n".'Response was:'. $json_response.', curl error: '.curl_error($curl)."\n".'Post data is: '.json_encode($_POST));
		}
	} 
	curl_close($curl);
	$response = json_decode($json_response, true);
	return $response;
}

function ggsf_lookup_object($object, $field, $value) {
	if ( !isset($_SESSION['access_token']) ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$query = "SELECT Id From $object WHERE $field = '$value'";
	$url = $instance_url.'/services/data/v20.0/query?q=' . urlencode($query);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $access_token"));
	$json_response = curl_exec($curl);
	$response = json_decode($json_response, true);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 201 ) {
		$response = json_decode($json_response, true);
		if (isset($response[0]) &&  $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
			ggsf_oauth();
			return ggsf_lookup_object($object, $field, $value);
		} else {
			sf_error('Error: Call to '.$url.' failed with status '.$status."\n".'Response was:'. $json_response.', curl error: '.curl_error($curl)."\n".'Post data is: '.json_encode($_POST));
		}
	}
	curl_close($curl);

	$response = json_decode($json_response, true);
	return $response;
}

// Old stuff -> delete it!

function ggsf_demo_rest() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>REST/OAuth Example</title>
	</head>
	<body>
		<tt>
			<?php
			$_POST['EMAIL'] = 'gastonius@gmail.com';
			$_POST['LNAME'] = 'Goro';
			$_POST['FNAME'] = 'Gaston';
			sf_add_mailchimp_subscriber();
			//sf_get_contact_object();

			// show_accounts($instance_url, $access_token);

			// $id = create_account("My New Org", $instance_url, $access_token);

			// show_account($id, $instance_url, $access_token);

			// show_accounts($instance_url, $access_token);

			// update_account($id, "My New Org, Inc", "San Francisco",
			//         $instance_url, $access_token);

			// show_account($id, $instance_url, $access_token);

			// show_accounts($instance_url, $access_token);

			// delete_account($id, $instance_url, $access_token);

			// show_accounts($instance_url, $access_token);
			?>
		</tt>
	</body>
</html>
<?php
}

function sf_error($message) {
	$to = GGSF_ADMIN_EMAIL;
	$subject = 'Salesforce Integration Error';

	wp_mail( $to, $subject, $message);
}

function sf_add_mailchimp_subscriber( $campaign_id = false) {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];
	
	if (!isset($access_token) || $access_token == "") {
		sf_error('Error - access token missing from session!');
	}

	// Search for contact
	$contact = sf_find_contact_by_email($_POST['EMAIL']);
	if (isset($contact['totalSize']) && $contact['totalSize'] == 0) { // New contact
		// Create new Account Object
		$vars = Array();
		$vars['Name'] = $_POST['LNAME'].' '.$_POST['FNAME'];
		$vars['Profile_Type__c'] = 'Personal';
		$vars['Account_Status__c'] = 'Monetary - Active';
		$vars['OwnerId'] = '005G0000004lLpM';

		$response = sf_create_account($vars);
		if (isset($response['success']) && $response['success'] == true) {
			// Create new Contact Object
			$vars = Array();
			$vars['FirstName'] = $_POST['FNAME'];
			$vars['LastName'] = $_POST['LNAME'];
			$vars['AccountId'] = $response['id'];
			$vars['OwnerId'] = '005G0000004lLpM';
			$vars['Groups_sf__c'] = 'Individual Solicitation';
			$vars['Email'] = $_POST['EMAIL'];
			if (isset($_REQUEST['desc'])) {
				$vars['Description'] = $_REQUEST['desc'];
				if ( $_REQUEST['desc'] == 'Bank on Her Microsite Sign-up' ) $campaign_id = '70116000000wSD3';
				else if ( substr($_REQUEST['desc'], 0, 12) == 'Publication:' ) $campaign_id = '70116000000wRhg';
			}

			if (isset($_REQUEST['Contact_Category'])) {
				$vars['Groups_sf__c'] = $_REQUEST['Contact_Category'];
			}

			$response = sf_create_contact($vars);
			$contact_id = $response['id'];
		}
	} else {
		$contact_id = $response['id'];
	}

	// Search at Mailchimp
	$mailchimp = sf_find_in_mailchimp($_POST['EMAIL']);
	
	// Find MC4SF__MC_Subscriber__c By Email
	if (isset($mailchimp['totalSize']) && $mailchimp['totalSize'] == 0) {
		// Create new MC4SF__MC_Subscriber__c
		$vars = Array();
		$vars['MC4SF__Email2__c'] = $_POST['EMAIL'];
		$vars['MC4SF__Interests__c'] = 'a12G000000204FVIAY';
		$vars['MC4SF__MC_List__c'] = 'a14160000031QOn';
		$vars['Name'] = $_POST['EMAIL'].' - Main Mail 2';
		$vars['MC4SF__Member_Status__c'] = 'Subscribed';
		$vars['MC4SF__MailChimp_List_ID__c'] = '489dee8f5f';

		$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);
	}

	if ( $campaign_id ) {
		// Add campaign member 
		$data = Array(
			'CampaignId' => $campaign_id,
			'Status' => 'Registered',
			'ContactId' => $contact_id
		);

		$campaign_object = ggsf_lookup_object('CampaignMember', 'ContactId', $contact_id);
		if ( $campaign_object['totalSize'] == 0) { // Campaign member doesn't exist: create it
			$campaign_object = ggsf_create_object('CampaignMember', $data);
			if( !isset($campaign_object['id']) ) var_dump($campaign_object);
		} else { // Update it
			$campaign_object = ggsf_update_object('CampaignMember', $campaign_object['records'][0]['Id'], $data);
		}
	}
	
}

function sf_get_contact_object() {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$objects = ggsf_get_objects();
	foreach ($objects['sobjects'] as $object) {
		if ($object['name'] == 'MC4SF__MC_Subscriber__c') { // Use this one
			$contact_object = $object;
			continue;
		}
	}

	$url = $instance_url.'/services/data/v20.0/sobjects/'.$contact_object['name'].'/describe';

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token"));

	$json_response = curl_exec($curl);
	curl_close($curl);

	$response = json_decode($json_response, true);
	echo '<pre>';
	foreach ($response['fields'] as $field) {
		echo $field['name'].' - <br>';
	}
}

function sf_find_in_mailchimp($email) {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$query = "SELECT Id FROM MC4SF__MC_Subscriber__c WHERE MC4SF__Email2__c = '$email'";
	$url = $instance_url.'/services/data/v20.0/query?q=' . urlencode($query);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $access_token"));
	$json_response = curl_exec($curl);
	$response = json_decode($json_response, true);
	curl_close($curl);

	return $response;
}

function sf_find_contact_by_email($email) {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$query = "SELECT Id From Contact WHERE Email = '$email'";
	$url = $instance_url.'/services/data/v20.0/query?q=' . urlencode($query);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $access_token"));
	$json_response = curl_exec($curl);
	$response = json_decode($json_response, true);

	curl_close($curl);
	if (isset($response[0]) && $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
		ggsf_oauth();
		return sf_find_contact_by_email($email);
	}

	return $response;
}



function show_accounts($instance_url, $access_token) {
	$query = "SELECT Name, Id from Account LIMIT 100";
	$url = "$instance_url/services/data/v20.0/query?q=" . urlencode($query);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token"));

	$json_response = curl_exec($curl);
	curl_close($curl);

	$response = json_decode($json_response, true);

	$total_size = $response['totalSize'];

	echo "$total_size record(s) returned<br/><br/>";
	foreach ((array) $response['records'] as $record) {
		echo $record['Id'] . ", " . $record['Name'] . "<br/>";
	}
	echo "<br/>";
}



function sf_create_contact($data) {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$url = "$instance_url/services/data/v20.0/sobjects/Contact/";

	$content = json_encode($data);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token",
				"Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ( $status != 201 ) {
		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}
	curl_close($curl);
	$response = json_decode($json_response, true);
	return $response;
}

function sf_create_account($data) {
	if ( !$_SESSION['access_token'] ) ggsf_oauth();
	$access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];

	$url = "$instance_url/services/data/v20.0/sobjects/Account/";

	$content = json_encode($data);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token",
				"Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ( $status != 201 ) {
		$response = json_decode($json_response, true);
		if ( $response['errorCode'] == 'INVALID_SESSION_ID') {
			ggsf_oauth();
			sf_create_account($data);
		} else {
			sf_error('Error: Call to '.$url.' failed with status '.$status."\n".'Response was:'. $json_response.', curl error: '.curl_error($curl)."\n".'Post data is: '.json_encode($_POST));
		}
	}
	curl_close($curl);
	$response = json_decode($json_response, true);
	return $response;
}

function show_account($id, $instance_url, $access_token) {
	$url = "$instance_url/services/data/v20.0/sobjects/Account/$id";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token"));

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ( $status != 200 ) {
		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	echo "HTTP status $status reading account<br/><br/>";

	curl_close($curl);

	$response = json_decode($json_response, true);

	foreach ((array) $response as $key => $value) {
		echo "$key:$value<br/>";
	}
	echo "<br/>";
}

function update_account($id, $new_name, $city, $instance_url, $access_token) {
	$url = "$instance_url/services/data/v20.0/sobjects/Account/$id";

	$content = json_encode(array("Name" => $new_name, "BillingCity" => $city));

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token",
				"Content-type: application/json"));
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

	curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ( $status != 204 ) {
		die("Error: call to URL $url failed with status $status, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	echo "HTTP status $status updating account<br/><br/>";

	curl_close($curl);
}

function delete_account($id, $instance_url, $access_token) {
	$url = "$instance_url/services/data/v20.0/sobjects/Account/$id";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Authorization: OAuth $access_token"));
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

	curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ( $status != 204 ) {
		die("Error: call to URL $url failed with status $status, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	echo "HTTP status $status deleting account<br/><br/>";

	curl_close($curl);
}
