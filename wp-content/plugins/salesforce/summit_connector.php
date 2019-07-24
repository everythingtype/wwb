<?php

add_action('wwb_form_summit_submit', Array('ggsf_sc', 'summit_form_connector') );

Class ggsf_sc {
	public static $post_data;
	public static $contact_id;
	public static $account_id;
	public static $account_name;
	public static $campaign_member_id;
	public static $opportunity_id;
	public static $opportunity_contact_id;
	public static $opportunity_component_id;
	public static $new_contact;
	public static $main_contact_id;

	public static function summit_form_connector() {
		self::$post_data = $_POST;

		/*

		Name: Gaston Goro
		Company: Aptugo

		2nd attendant
		Name: Ariadna
		Company: Empty

		3rd attendant
		Name: Eva
		Company: Google

		0 - Auth.net and aprove the $1500

		1 - contact gaston goro
		2 - account aptugo

		ONLY IF FILL IN THE COMPANY NAME
			3 - affiliation between contact / account (gaston / aptugo)
				3.1 - Role in aff. is TITLE
				3.2 - Primary should be checked for Gaston only


		4 - contact Ariadna

		5 - contact Eva
		6 - account google

		ONLY IF FILL IN THE COMPANY NAME
			3 - affiliation between contact / account (eva / google)
				3.1 - Role in aff. is TITLE
				3.2 - Primary should be UNchecked (not primary)

		Ariadna and Eva related to gaston

		7 - relationship between Ariadna and Gaston
			7.1 type is Coworker
			7.2 description is: Subscribed together to Summit 2015

		8 - relationship between Eva and Gaston
			8.1 type is Coworker
			8.2 description is: Subscribed together to Summit 2015


array(38) {
  ["action"]=>
  string(18) "summit_form_submit"
  ["ticket_quantity"]=>
  string(1) "2"
  ["salutation"]=>
  string(2) "Mr"
  ["first_name"]=>
  string(6) "Gaston"
  ["last_name"]=>
  string(12) "Gorosterrazu"
  ["email"]=>
  string(19) "gastonius@gmail.com"
  ["phone"]=>
  string(10) "9174841406"
  ["personal_title"]=>
  string(3) "cto"
  ["personal_company"]=>
  string(6) "Aptugo"
  ["salutation_2"]=>
  string(3) "Mrs"
  ["first_name_2"]=>
  string(7) "Ariadna"
  ["last_name_2"]=>
  string(12) "Gorosterrazu"
  ["email_2"]=>
  string(19) "gastonius@gmail.com"
  ["personal_title_2"]=>
  string(0) ""
  ["personal_company_2"]=>
  string(6) "Aptugo"
  ["salutation_3"]=>
  string(2) "--"
  ["first_name_3"]=>
  string(0) ""
  ["last_name_3"]=>
  string(0) ""
  ["email_3"]=>
  string(0) ""
  ["personal_title_3"]=>
  string(0) ""
  ["personal_company_3"]=>
  string(6) "Aptugo"
  ["salutation_4"]=>
  string(2) "--"
  ["first_name_4"]=>
  string(0) ""
  ["last_name_4"]=>
  string(0) ""
  ["email_4"]=>
  string(0) ""
  ["personal_title_4"]=>
  string(0) ""
  ["personal_company_4"]=>
  string(6) "Aptugo"
  ["card_number"]=>
  string(16) "4444444444444441"
  ["card_cvv"]=>
  string(3) "841"
  ["card_expire"]=>
  string(7) "12/2015"
  ["addresstype"]=>
  string(4) "Work"
  ["street"]=>
  string(58) "A lot of places, but IRS reaches me at Staten Island, NYC."
  ["city"]=>
  string(10) "Westervelt"
  ["state_prov"]=>
  string(8) "New York"
  ["zip"]=>
  string(5) "10301"
  ["country"]=>
  string(13) "United States"
  ["refer_source"]=>
  string(16) "Email/Newsletter"
  ["email_updates"]=>
  string(2) "on"
}








		*/

		// Main attendant

		$email_selector = 'npe01__HomeEmail__c';
		$phone_selector = 'HomePhone';

		$contact_data = Array(
			'OwnerId' => ggsf_relationshipmanager,
			'Salutation' => self::$post_data['salutation'],
			'FirstName' => self::$post_data['first_name'],
			'LastName' => self::$post_data['last_name'],
			'npe01__Preferred_Email__c' => self::$post_data['emailtype'],
			$email_selector => self::$post_data['email'],
			'npe01__PreferredPhone__c' => self::$post_data['phonetype'],
			$phone_selector => self::$post_data['phone'],
			'MailingStreet' => self::$post_data['street'],
			'MailingCity' => self::$post_data['city'],
			'MailingState' => self::$post_data['state'],
			'MailingPostalCode' => self::$post_data['zip'],
			'MailingCountry' => self::$post_data['country'],
			'Title' => self::$post_data['personal_title']
		);
		if (self::$post_data['email_updates'] == 'on') $contact_data['email_updates'] = true;

		$contact_id = self::summit_form_connect_lookup_contact_object( self::$post_data['email'] );
		if ( $contact_id ) {
			self::$new_contact = false;
			self::summit_form_connector_update_contact_object( $contact_id, $contact_data );
		} else {
			self::$new_contact = true;
			$contact_id = self::summit_form_create_contact_object( $contact_data );
		}
		$main_contact_id = $contact_id;
		self::$main_contact_id = $main_contact_id;

		if (self::$post_data['email_updates'] == 'on') {
			self::summit_form_subscribe_to_newsletter(self::$post_data['email'], self::$post_data['salutation'],self::$post_data['first_name'],self::$post_data['last_name']);
		}

		self::summit_form_create_campaign_member( $contact_id );

		if ( isset( self::$post_data['personal_company'] ) ) {
			$account = self::summit_form_create_account_object( self::$post_data['personal_company'] );
			$account_id = $account['records'][0]['Id'];
			self::summit_form_create_affiliation( $contact_id, $account_id );
		}

		// 2nd attentant

		if ( isset(self::$post_data['first_name_2']) && !empty(self::$post_data['first_name_2'])  && self::$post_data['ticket_quantity'] > 1) {
			$contact_data = Array(
				'OwnerId' => ggsf_relationshipmanager,
				'Salutation' => self::$post_data['salutation_2'],
				'FirstName' => self::$post_data['first_name_2'],
				'LastName' => self::$post_data['last_name_2'],
				'npe01__Preferred_Email__c' => self::$post_data['emailtype_2'],
				$email_selector => self::$post_data['email_2'],
				'npe01__PreferredPhone__c' => self::$post_data['phone_type'],
				$phone_selector => self::$post_data['phone_2'],
				'Title' => self::$post_data['personal_title_2']
			);
			if (self::$post_data['email_updates'] == 'on') $contact_data['email_updates'] = true;

			$contact_id = self::summit_form_connect_lookup_contact_object( self::$post_data['email_2'] );
			if ( $contact_id ) {
				self::$new_contact = false;
				self::summit_form_connector_update_contact_object( $contact_id, $contact_data );
			} else {
				self::$new_contact = true;
				$contact_id = self::summit_form_create_contact_object( $contact_data );
			}

			if (self::$post_data['email_updates'] == 'on') {
				self::summit_form_subscribe_to_newsletter(self::$post_data['email_2'],self::$post_data['salutation_2'],self::$post_data['first_name_2'],self::$post_data['last_name_2']);
			}

			self::summit_form_create_campaign_member( $contact_id );

			if ( isset( self::$post_data['personal_company_2'] ) ) {
				$account = self::summit_form_create_account_object( self::$post_data['personal_company_2'] );
				$account_id = $account['records'][0]['Id'];
				self::summit_form_create_affiliation( $contact_id, $account_id );
			}

			self::summit_form_create_relationship( $main_contact_id, $contact_id);
		}

		// 3rd attentant

		if ( isset(self::$post_data['first_name_3']) && !empty(self::$post_data['first_name_3'])  && self::$post_data['ticket_quantity'] > 2) {
			$contact_data = Array(
				'OwnerId' => ggsf_relationshipmanager,
				'Salutation' => self::$post_data['salutation_3'],
				'FirstName' => self::$post_data['first_name_3'],
				'LastName' => self::$post_data['last_name_3'],
				'npe01__Preferred_Email__c' => self::$post_data['emailtype_3'],
				$email_selector => self::$post_data['email_3'],
				'npe01__PreferredPhone__c' => self::$post_data['phone_type'],
				$phone_selector => self::$post_data['phone_3'],
				'Title' => self::$post_data['personal_title_3']
			);
			if (self::$post_data['email_updates'] == 'on') $contact_data['email_updates'] = true;

			$contact_id = self::summit_form_connect_lookup_contact_object( self::$post_data['email_3'] );
			if ( $contact_id ) {
				self::$new_contact = false;
				self::summit_form_connector_update_contact_object( $contact_id, $contact_data );
			} else {
				self::$new_contact = true;
				$contact_id = self::summit_form_create_contact_object( $contact_data );
			}

			if (self::$post_data['email_updates'] == 'on') {
				self::summit_form_subscribe_to_newsletter(self::$post_data['email_3'],self::$post_data['salutation_3'],self::$post_data['first_name_3'],self::$post_data['last_name_3']);
			}

			self::summit_form_create_campaign_member( $contact_id );

			if ( isset( self::$post_data['personal_company_3'] ) ) {
				$account = self::summit_form_create_account_object( self::$post_data['personal_company_3'] );
				$account_id = $account['records'][0]['Id'];
				self::summit_form_create_affiliation( $contact_id, $account_id );
			}

			self::summit_form_create_relationship( $main_contact_id, $contact_id);
		}

		// 4th attentant

		if ( isset(self::$post_data['first_name_4']) && !empty(self::$post_data['first_name_4'])  && self::$post_data['ticket_quantity'] > 3) {
			$contact_data = Array(
				'OwnerId' => ggsf_relationshipmanager,
				'Salutation' => self::$post_data['salutation_4'],
				'FirstName' => self::$post_data['first_name_4'],
				'LastName' => self::$post_data['last_name_4'],
				'npe01__Preferred_Email__c' => self::$post_data['emailtype_4'],
				$email_selector => self::$post_data['email_4'],
				'npe01__PreferredPhone__c' => self::$post_data['phone_type'],
				$phone_selector => self::$post_data['phone_4'],
				'Title' => self::$post_data['personal_title_4']
			);
			if (self::$post_data['email_updates'] == 'on') $contact_data['email_updates'] = true;

			$contact_id = self::summit_form_connect_lookup_contact_object( self::$post_data['email_4'] );
			if ( $contact_id ) {
				self::$new_contact = false;
				self::summit_form_connector_update_contact_object( $contact_id, $contact_data );
			} else {
				self::$new_contact = true;
				$contact_id = self::summit_form_create_contact_object( $contact_data );
			}

			if (self::$post_data['email_updates'] == 'on') {
				self::summit_form_subscribe_to_newsletter(self::$post_data['email_4'],self::$post_data['salutation_4'],self::$post_data['first_name_4'],self::$post_data['last_name_4']);
			}

			self::summit_form_create_campaign_member( $contact_id );

			if ( isset( self::$post_data['personal_company_4'] ) ) {
				$account = self::summit_form_create_account_object( self::$post_data['personal_company_4'] );
				$account_id = $account['records'][0]['Id'];
				self::summit_form_create_affiliation( $contact_id, $account_id );
			}

			self::summit_form_create_relationship( $main_contact_id, $contact_id);
		}

		$opp_id = self::summit_form_create_opportunity();
		self::summit_form_create_opportunity_component($opp_id);
		self::send_confirmation_email();
	}

	private static function summit_form_create_affiliation( $contact_id, $account_id ) {
		$data = Array(
			'npe5__Organization__c' => $account_id,
			'npe5__Contact__c' => $contact_id,
			'npe5__Role__c' => self::$post_data['personal_title']
		);

		$affiliation_object = ggsf_create_object('npe5__Affiliation__c', $data);
		return $affiliation_object;
	}

	private static function summit_form_create_account_object( $company ) {
		$data = Array(
			'Account_Status__c' => 'Current',
			'Profile_Type__c' => '01216000001IhRv',
			'OwnerId' => ggsf_relationshipmanager,
			'Name' => $company
		);

		$account_object = ggsf_lookup_object('Account', 'Name', $company);

		if ( $account_object['totalSize'] == 0) { // Account doesn't exist: create it
			$account_object = ggsf_create_object('Account', $data);
			self::$account_id = $account_object['id'];
		} else { // Update it
			self::$account_id = $account_object['records'][0]['Id'];
			$response = ggsf_update_object('Account', self::$account_id, $data);
			if ( $response ) $account_object = $response;
		}
		return $account_object;
	}

	private static function summit_form_connect_lookup_contact_object( $email ) {
		$contact_object = ggsf_lookup_object('Contact', 'npe01__HomeEmail__c', $email);
		if ( $contact_object['totalSize'] == 0 ) $contact_object = ggsf_lookup_object('Contact', 'npe01__Preferred_Email__c', $email);
		if ( $contact_object['totalSize'] == 0 ) $contact_object = ggsf_lookup_object('Contact', 'npe01__WorkEmail__c', $email);
		if ( $contact_object['totalSize'] == 0 ) {
			self::$contact_id = false;
		} else { // At least one record matching
			self::$contact_id = $contact_object['records'][0]['Id'];
		}
		return self::$contact_id;
	}

	private static function summit_form_create_contact_object( $data ) {
		if ($data['email_updates'] == true) {
			$data['Groups_sf__c'] = 'Individual Solicitation';
		};

		unset( $data['email_updates'] );
		$contact_object = ggsf_create_object('Contact', $data);
		self::$contact_id = $contact_object['id'];
		return $contact_object['id'];
	}

	private static function summit_form_connector_update_contact_object( $contact_id, $data ) {
		unset( $data['email_updates'] );
		ggsf_update_object('Contact', $contact_id, $data);
	}

	private static function summit_form_create_recurring_donation() {
		$data = Array(
			'npe03__Installments__c' => 1,
			'npe03__Amount__c' => self::$post_data['amount'],
			'Name' => self::$post_data['first_name'].' '.self::$post_data['last_name'].' - Recurring Donation',
			'npe03__Date_Established__c' =>  date('Y-m-d'),
			'npe03__Contact__c' => self::$contact_id,
			'npe03__Recurring_Donation_Campaign__c' => '70116000000wPKb',
			'npe03__Schedule_Type__c' => 'Multiple By',
			'npe03__Open_Ended_Status__c' => 'Open',
			'npe03__Installment_Period__c' => 'Monthly'
		);

		$recurring_object = ggsf_create_object('npe03__Recurring_Donation__c', $data);
		$opps = ggsf_lookup_object('Opportunity', 'npe03__Recurring_Donation__c', $recurring_object['id']);

		for ($I = 0; $I < count($opps['records']); $I++ ) {
			self::summit_form_create_opportunity_component( $opps['records'][$I]['Id']);
		}
	}

	private static function summit_form_create_opportunity() {
		$opp_name = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - Summit 2015';
		$message = '';

		if ( self::$new_contact ) $opp_type = 'New Business';
		else $opp_type = 'Renewal';

		$AccountId = self::getAccountID( self::$main_contact_id );

		$data = Array(
			'AccountId' => $AccountId,
			'Name' => $opp_name,
			'StageName' => 'Awarded',
			'CloseDate' => date('Y-m-d'),
			'Type' => $opp_type,
			'CampaignId' => '70116000000wPKb',
			'Restrictions__c' => 'Unrestricted',
			'OwnerId' => ggsf_relationshipmanager,
			'Amount' => self::$post_data['ticket_quantity'] * 500,
			'Description' => $message
		);

		$opportunity_object = ggsf_create_object('Opportunity', $data);
		if ( isset($opportunity_object['id'])) self::$opportunity_id = $opportunity_object['id'];
	}

	private static function getAccountID( $contactID ) {
		if ( !isset($_SESSION['access_token']) ) ggsf_oauth();
		$access_token = $_SESSION['access_token'];
		$instance_url = $_SESSION['instance_url'];

		$query = "SELECT AccountId From Contact WHERE Id ='$contactID'";
		$url = $instance_url.'/services/data/v20.0/query?q=' . urlencode($query);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $access_token"));
		$json_response = curl_exec($curl);
		$response = json_decode($json_response, true);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ( $status != 200 ) {
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
		return $response['records'][0]['AccountId'];
	}

	private static function summit_form_create_opportunity_component( $opp_id = false) {
		$data = Array(
			'Fundraising_Target__c' => 'a1CG0000003Xs0H',
			'Payment_Received__c' => 'Yes',
			'Name' => self::$post_data['first_name'].' '.self::$post_data['last_name'].' - 2015 Global Forum',
			'Component_Amount__c' => self::$post_data['ticket_quantity'] * 500,
			'Opportunity__c' => $opp_id?$opp_id:self::$opportunity_id
		);

		$opportunity_component_object = ggsf_create_object('Opportunity_Component__c', $data);
		if ( isset($opportunity_component_object['id'])) self::$opportunity_component_id = $opportunity_component_object['id'];	
		return self::$opportunity_component_id;
	}

	private static function summit_form_create_opportunity_contact_role() {
		$data = Array(
			'ContactId' => self::$contact_id,
			'Role' => 'Donor',
			'IsPrimary' => 'true',
			'OpportunityId' => self::$opportunity_id
		);

		$opportunity_contact_object = ggsf_create_object('OpportunityContactRole', $data);
		if ( isset($opportunity_contact_object['id'])) self::$opportunity_contact_id = $opportunity_contact_object['id'];	
	}

	private static function summit_form_create_payment() {
		$data = Array(	
			'npe01__Opportunity__c' => self::$opportunity_id,
			'npe01__Payment_Amount__c' => 12 * self::$post_data['amount'],
			'npe01__Payment_Date__c' =>  date('Y-m-d'),
			'npe01__Payment_Method__c' => 'Credit Card',
			'npe01__Paid__c' => true
		);

		$payment = ggsf_create_object('npe01__OppPayment__c', $data);
	}

	private static function send_confirmation_email() {
		$recurringadd = '';
		if ( self::$post_data['recurrence'] == 'Monthly') $recurringadd = '&nbsp;(Monthly)';
		 $headers = 'From: Women\'s World Banking <communications@womensworldbanking.org>' . "\r\n";


		 $content = '<table width="600" border="0" cellpadding="5"><tbody><tr><td><a href="http://www.womensworldbanking.org" target="_blank"><img style="max-width: 600px;" src="http://www.womensworldbanking.org/wp-content/uploads/2015/06/MFFW-Summit-banner600px.png" alt="Womens World Banking"></a></td></tr><tr><td>
			<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Dear '.self::$post_data['first_name'].' '.self::$post_data['last_name'].', </p>
			<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Thank you for purchasing your ticket for the Making Finance Work for Women Conference.</p>
			<p style="font-family: arial; color: #4d4f53; font-size: 11px;">This e-mail confirms your purchase and admission.</p>
			<p></p>
			<p style="font-family: arial; color: #4d4f53; font-size: 11px;">The event will take place at the Federal Ministry for Economic Cooperation and Development (BMZ) located at Stresemannstraße 92, 10963 Berlin, Germany November 11-12, 2015. Directions to the offices can be found <a href="http://www.bmz.de/en/service/contact/berlin/anfahrtsskizze/index.html">here</a>. Registration will open at 8:00 am. </p>
			<p></p>
			<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Please contact <a href="mailto:and@womensworldbanking.org">Ashleigh DeLuca</a> if you have any further questions.</p>
			<p style="font-family: arial; color: #4d4f53; font-size: 11px;">We look forward to seeing you in Berlin.</p>
			<p></p>
			<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Sincerely, <br>Women\'s World Banking</p>
			<p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Women\'s World Banking is the global nonprofit devoted to giving more low-income women access to the financial tools and resources essential to their security and prosperity. Learn more about our work at <a href="http://www.womensworldbanking.org" target="_blank">womensworldbanking.org</a>.</em></p>
<p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Follow us on Twitter at <a href="http://www.twitter.com/womensworldbnkg" target="_blank">@womensworldbnkg</a> and Like us on Facebook at <a href="http://www.facebook.com/womensworldbanking" target="_blank">facebook.com/womensworldbanking</a>.</em></p></td></tr></table>';

		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		wp_mail( self::$post_data['email'], 'Making Finance Work For Women – Ticket Information', $content, $headers );
		wp_mail( 'and@womensworldbanking.org', 'Making Finance Work For Women – Ticket Information (copy)', $content, $headers );
		wp_mail( 'afp@womensworldbanking.org', 'Making Finance Work For Women – Ticket Information (copy)', $content, $headers );
		wp_mail( 'mjf@womensworldbanking.org', 'Making Finance Work For Women – Ticket Information (copy)', $content, $headers );
		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
	}	

	private static function summit_form_subscribe_to_newsletter( $email, $sal, $fn, $ln ) {
		$merge_vars = Array(
			"SALUTATION" => $sal,
			"FNAME" => $fn, 
			"LNAME" => $ln,
			"GROUPINGS" => Array(
				Array(
					'id' => 1,
					'groups' => Array('Individual Solicitation')
				)
			),
			"ACTION" => 'mc4wp_subscribe'
		);

		// Add to mailchimp (on hold, need to confirm this is necessary)
		MC4WP_Lite_Form_Request::subscribe($email, $merge_vars);

		// Add to Salesforce
		$vars = Array(
			'MC4SF__Email2__c' => $email,
			'MC4SF__Interests__c' => ggsf_newinterests,
			'MC4SF__MC_List__c' => ggsf_newmclist,
			'Name' => $email.' - Main Mail 2',
			'MC4SF__Member_Status__c' => 'Subscribed',
			'MC4SF__MailChimp_List_ID__c' => ggsf_mclistid
		);
		$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);
	}

	private static function summit_form_create_relationship( $main, $contact ) {
		$data = Array(
			'npe4__Contact__c' => $main,
			'npe4__Description__c' => 'Subscribed together to Summit 2015',
			'npe4__Type__c' => 'Coworker',
			'npe4__RelatedContact__c' => $contact
		);

		ggsf_create_object('npe4__Relationship__c', $data);
	}

	private static function summit_form_create_campaign_member( $contact_id ) {
		$data = Array(
			'CampaignId' => '70116000000wPKb',
			'Status' => 'Registered',
			'ContactId' => $contact_id
		);

		$campaign_object = ggsf_lookup_object('CampaignMember', 'ContactId', $contact_id);
		if ( $campaign_object['totalSize'] == 0) { // Campaign member doesn't exist: create it
			$campaign_object = ggsf_create_object('CampaignMember', $data);
			if( !isset($campaign_object['id']) ) var_dump($campaign_object);
			self::$campaign_member_id = $campaign_object['id'];
		} else { // Update it
			self::$campaign_member_id = $campaign_object['records'][0]['Id'];
			$campaign_object = ggsf_update_object('CampaignMember', self::$campaign_member_id, $data);
		}
	}	
}
