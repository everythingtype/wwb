<?php 
define('ggsf_newcampaignid','701G0000000WpE0');
define('ggsf_newinterests','a1216000003bYlYAAU');
define('ggsf_newmclist','a14160000031QOnAAM');

add_action('new_wwb_form_donation_submit', Array('ggsf_ndfc', 'donate_form_connector') );

Class ggsf_ndfc {
	public static $post_data;
	public static $contact_id;
	public static $account_id;
	public static $account_name;
	public static $campaign_member_id;
	public static $opportunity_id;
	public static $opportunity_contact_id;
	public static $opportunity_component_id;
	public static $new_contact;

	public static function donate_form_connector() {
		self::$post_data = $_POST;

		self::donate_form_connect_lookup_contact_object();
		// Create or Update contact **************************
		if ( self::$contact_id ) {
			self::$new_contact = false;
			self::donate_form_connector_update_contact_object();
		} else {
			self::$new_contact = true;
			self::donate_form_create_contact_object();
		}

		// Check Recurrence **************************
		if ( self::$post_data['recurrence'] == 'Monthly') {
			self::donate_form_create_recurring_donation();
		} else {
			self::donate_form_create_opportunity();
			self::donate_form_create_opportunity_component();
		}

		if (self::$post_data['email_updates'] == 'true') { // Subscribe to newsletter
			self::donate_form_subscribe_to_newsletter();
		}

		//self::donate_form_create_opportunity_contact_role();
		//self::donate_form_create_payment();
		self::donate_form_create_campaign_member();
		self::send_confirmation_email();
	}

	private static function donate_form_connect_lookup_contact_object() {
		$contact_object = ggsf_lookup_object('Contact', 'npe01__HomeEmail__c', self::$post_data['email']);
		if ( $contact_object['totalSize'] == 0 ) $contact_object = ggsf_lookup_object('Contact', 'npe01__Preferred_Email__c', self::$post_data['email']);
		if ( $contact_object['totalSize'] == 0 ) $contact_object = ggsf_lookup_object('Contact', 'npe01__WorkEmail__c', self::$post_data['email']);
		if ( $contact_object['totalSize'] == 0 ) {
			self::$contact_id = false;
		} else { // At least one record matching
			self::$contact_id = $contact_object['records'][0]['Id'];
		}
	}

	private static function donate_form_create_contact_object() {
		$email_selector = 'npe01__HomeEmail__c';
		if ( self::$post_data['email_type'] == 'Alternate') $email_selector = 'npe01__AlternateEmail__c';
		if ( self::$post_data['email_type'] == 'Work') $email_selector = 'npe01__WorkEmail__c';

		$phone_selector = 'HomePhone';
		if ( self::$post_data['phone_type'] == 'Alternate') $phone_selector = 'OtherPhone';
		if ( self::$post_data['phone_type'] == 'Work') $phone_selector = 'npe01__WorkPhone__c';
		
		$data = Array(
			'OwnerId' => ggsf_relationshipmanager,
			'FirstName' => self::$post_data['first_name'],
			'LastName' => self::$post_data['last_name'],
			'Salutation' => self::$post_data['salutation'],
			'npe01__Preferred_Email__c' => self::$post_data['email_type'],
			$email_selector => self::$post_data['email'],
			'npe01__PreferredPhone__c' => self::$post_data['phone_type'],
			$phone_selector => self::$post_data['phone'],
			'npe01__Primary_Address_Type__c' => self::$post_data['address_type'],
			'npsp__is_Address_Override__c' => true,
			'MailingStreet' => self::$post_data['street'],
			'MailingCity' => self::$post_data['city'],
			'MailingState' => self::$post_data['state'],
			'MailingPostalCode' => self::$post_data['zip'],
			'MailingCountry' => self::$post_data['country']
		);

		if ( self::$post_data['billing_equal'] == false) {
			$addData = Array(
				'OtherStreet' => '',
				'OtherCity' => '',
				'OtherState' => '',
				'OtherPostalCode' => '',
				'OtherCountry' => ''
			);
		} else {
			$addData = Array(
				'npe01__Secondary_Address_Type__c' => 'Other',
				'OtherStreet' => self::$post_data['billing_address_1'],
				'OtherCity' => self::$post_data['billing_city'],
				'OtherState' => self::$post_data['billing_state'],
				'OtherPostalCode' => self::$post_data['billing_zip'],
				'OtherCountry' => self::$post_data['billing_country']
			);
		}

		$data = array_merge($data,$addData);

		if (self::$post_data['email_updates'] == 'true') {
			$data['Groups_sf__c'] = 'Individual Solicitation';
		};

		$contact_object = ggsf_create_object('Contact', $data);
		self::$contact_id = $contact_object['id'];
	}

	private static function donate_form_connector_update_contact_object() {
		$email_selector = 'npe01__HomeEmail__c';
		if ( self::$post_data['email_type'] == 'Alternate') $email_selector = 'npe01__AlternateEmail__c';
		if ( self::$post_data['email_type'] == 'Work') $email_selector = 'npe01__WorkEmail__c';

		$phone_selector = 'HomePhone';
		if ( self::$post_data['phone_type'] == 'Alternate') $phone_selector = 'OtherPhone';
		if ( self::$post_data['phone_type'] == 'Work') $phone_selector = 'npe01__WorkPhone__c';
		
		$data = Array(
			'FirstName' => self::$post_data['first_name'],
			'LastName' => self::$post_data['last_name'],
			'Salutation' => self::$post_data['salutation'],
			'npe01__Preferred_Email__c' => self::$post_data['email_type'],
			$email_selector => self::$post_data['email'],
			'npe01__PreferredPhone__c' => self::$post_data['phone_type'],
			$phone_selector => self::$post_data['phone'],
			'npe01__Primary_Address_Type__c' => self::$post_data['address_type'],
			'npsp__is_Address_Override__c' => true,
			'MailingStreet' => self::$post_data['street'],
			'MailingCity' => self::$post_data['city'],
			'MailingState' => self::$post_data['state'],
			'MailingPostalCode' => self::$post_data['zip'],
			'MailingCountry' => self::$post_data['country']
		);

		if ( self::$post_data['billing_equal'] == true) {
			$addData = Array(
				'OtherStreet' => self::$post_data['street'],
				'OtherCity' => self::$post_data['city'],
				'OtherState' => self::$post_data['state'],
				'OtherPostalCode' => self::$post_data['zip'],
				'OtherCountry' => self::$post_data['country']
			);
		} else {
			$addData = Array(
				'OtherStreet' => self::$post_data['billing_address_1'],
				'OtherCity' => self::$post_data['billing_city'],
				'OtherState' => self::$post_data['billing_state'],
				'OtherPostalCode' => self::$post_data['billing_zip'],
				'OtherCountry' => self::$post_data['billing_country']
			);
		}

		$data = array_merge($data,$addData);

		ggsf_update_object('Contact', self::$contact_id, $data);
	}

	private static function donate_form_create_recurring_donation() {
		$data = Array(
			'npe03__Installments__c' => 1,
			'npe03__Amount__c' => str_replace(',','',self::$post_data['amount']),
			'Name' => self::$post_data['first_name'].' '.self::$post_data['last_name'].' - Recurring Donation',
			'npe03__Date_Established__c' =>  date('Y-m-d'),
			'npe03__Contact__c' => self::$contact_id,
			'npe03__Recurring_Donation_Campaign__c' => isset(self::$post_data['sf_campaign'])?self::$post_data['sf_campaign']:ggsf_newcampaignid,
			'npe03__Schedule_Type__c' => 'Multiple By',
			'npe03__Open_Ended_Status__c' => 'Open',
			'npe03__Installment_Period__c' => 'Monthly'
		);

		if ( isset(self::$post_data['sp_oppname']) ) {
			$data['Name'] = self::$post_data['first_name'].' '.self::$post_data['last_name'].self::$post_data['sp_oppname'].' (Recurring)';
		}

		$recurring_object = ggsf_create_object('npe03__Recurring_Donation__c', $data);
		$opps = ggsf_lookup_object('Opportunity', 'npe03__Recurring_Donation__c', $recurring_object['id']);

		for ($I = 0; $I < count($opps['records']); $I++ ) {
			self::donate_form_create_opportunity_component( $opps['records'][$I]['Id']);
		}
	}

	private static function donate_form_create_opportunity() {
		$opp_name = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - '.self::$post_data['recurrence'];

		if ( isset(self::$post_data['sp_oppname']) ) {
			$opp_name = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - '.self::$post_data['recurrence'].self::$post_data['sp_oppname'];	
		}
		$message = '';

		if ( isset(self::$post_data['message'])) $message = self::$post_data['message']."\n";
		if ( isset(self::$post_data['employer_match']) && self::$post_data['employer_match'] == 'true') $message .= "This donor's employer will match, please contact them.\n";
		if ( isset(self::$post_data['in_memory']) && self::$post_data['in_memory'] == 'true') {
			$message .= "This donation is a tribute gift, please contact them.";

			$headers = 'From: Women\'s World Banking <development@womensworldbanking.org>' . "\r\n";
			$content = 'From Name: '.self::$post_data['first_name'].' '.self::$post_data['last_name']."\n";
			$content .= 'From Email: '.self::$post_data['email']."\n";
			$content .= 'Amount:'.self::$post_data['amount']."\n";
			$content .= 'Recipient:'.self::$post_data['tribute_name']."\n";
			$content .= 'Recipients email:'.self::$post_data['tribute_email']."\n";
			$content .= 'Message:'.self::$post_data['tribute_message']."\n";

			wp_mail( 'development@womensworldbanking.org', 'Tribute gift alert', $content, $headers );
			wp_mail( 'development@womensworldbanking.org', 'Thank you for your donation', $content, $headers );
		}

		if ( self::$new_contact ) $opp_type = 'New Business';
		else $opp_type = 'Renewal';

		$AccountId = self::getAccountID( self::$contact_id );

		$data = Array(
			'AccountId' => $AccountId,
			'Name' => $opp_name,
			'StageName' => 'Awarded',
			'CloseDate' => date('Y-m-d'),
			'Type' => $opp_type,
			'CampaignId' => isset(self::$post_data['sf_campaign'])?self::$post_data['sf_campaign']:ggsf_newcampaignid,
			'Restrictions__c' => 'Unrestricted',
			'OwnerId' => ggsf_relationshipmanager,
			'Amount' => str_replace(',','',self::$post_data['amount']),
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
		return $response['records'][0]['AccountId'];
	}

	private static function donate_form_create_opportunity_component( $opp_id = false) {
		$data = Array(
			'Fundraising_Target__c' => isset(self::$post_data['sf_fund_target'])?self::$post_data['sf_fund_target']:ggsf_funraisingtarget,
			'Payment_Received__c' => 'Yes',
			'Name' => '2015 Web Donation',
			'Component_Amount__c' => str_replace(',','',self::$post_data['amount']),
			'Opportunity__c' => $opp_id?$opp_id:self::$opportunity_id
		);
		if ( isset(self::$post_data['sp_oppname']) ) {
			$data['Name'] = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - '.self::$post_data['recurrence'].self::$post_data['sp_oppname'];
		}

		$opportunity_component_object = ggsf_create_object('Opportunity_Component__c', $data);
		if ( isset($opportunity_component_object['id'])) self::$opportunity_component_id = $opportunity_component_object['id'];	
	}

	private static function donate_form_create_opportunity_contact_role() {
		$data = Array(
			'ContactId' => self::$contact_id,
			'Role' => 'Donor',
			'IsPrimary' => 'true',
			'OpportunityId' => self::$opportunity_id
		);

		$opportunity_contact_object = ggsf_create_object('OpportunityContactRole', $data);
		if ( isset($opportunity_contact_object['id'])) self::$opportunity_contact_id = $opportunity_contact_object['id'];	
	}

	private static function donate_form_create_payment() {
		$data = Array(	
			'npe01__Opportunity__c' => self::$opportunity_id,
			'npe01__Payment_Amount__c' => 12 * str_replace(',','',self::$post_data['amount']),
			'npe01__Payment_Date__c' =>  date('Y-m-d'),
			'npe01__Payment_Method__c' => 'Credit Card',
			'npe01__Paid__c' => true
		);

		$payment = ggsf_create_object('npe01__OppPayment__c', $data);
	}

	private static function send_confirmation_email() {
		$recurringadd = '';
		if ( self::$post_data['recurrence'] == 'Monthly') $recurringadd = '&nbsp;(Monthly)';
		 $headers = 'From: Women\'s World Banking <development@womensworldbanking.org>' . "\r\n";
		$content = '<table width="600" border="0" cellpadding="5"><tbody><tr><td><a href="http://www.womensworldbanking.org" target="_blank"><img style="float: left;" src="http://www.womensworldbanking.org/wp-content/uploads/2013/06/WWB_Horizontal_RGB.png" alt="Women\'s World Banking" width="200" height="19" /></a></td>
<td colspan="2"><p style="font-family: arial; color: #4d4f53; font-size: 11px;">122 East 42nd Street, 42nd Floor<br /> New York, NY, 10168<br /> <strong>Tax ID #: 13-3101527</strong></p></td></tr>
<tr><td colspan="3"><hr /></td></tr><tr><td width="216"><p style="font-family: arial; color: #4d4f53; font-size: 11px;"><strong>DONOR NAME:</strong><br /> '.self::$post_data['first_name'].' '.self::$post_data['last_name'].' </p>
<p style="font-family: arial; color: #4d4f53; font-size: 11px;"><strong>DONOR ADDRESS:</strong><br />'.self::$post_data['address_1'].'<br /> '.self::$post_data['city'].', '.self::$post_data['state'].' '.self::$post_data['zip'].'<br /> '.self::$post_data['country'].'</p>
</td><td width="140"><p style="font-family: arial; color: #4d4f53; font-size: 11px;"><strong>GIFT AMOUNT:</strong><br /> $ '.self::$post_data['amount'].$recurringadd.'<br /> <br /> <strong>GIFT DATE:</strong><br /> '.date('m/d/Y').'</p>
</td><td align="right" width="206"><img src="http://www.womensworldbanking.org/wp-content/uploads/2013/07/Donate-Form-Imagev2.png" alt="" /></td>
</tr><tr><td colspan="3"><hr /><p style="font-family: arial; color: #4d4f53; font-size: 11px;">Dear '.self::$post_data['first_name'].',</p>
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Please accept our most heartfelt thanks for your support of Women&rsquo;s World Banking. The collective power of giving has a tremendous impact on our ability to develop new and innovative financial products and services for women and girls around the world.</p>
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Kindly note that the tax-deductible amount of your gift is listed above. Please save this letter for your tax records as confirmation of your donation. Friends of Women\'s World Banking USA, Inc. is a 501(c)(3) nonprofit organization. If you have any questions, please email <a href="mailto:development@womensworldbanking.org">development@womensworldbanking.org</a>.</p>
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Friends of WWB/USA, Inc. is exempt under Section 501(c)(3) of the Internal Revenue Code, as such, contributions are deductible for federal income tax purposes. The latest annual report and other information about Friends of WWB/USA Inc.’s purpose, programs and activities can be obtained by contacting Tom Jones at 122 East 42nd Street, 42nd Floor, New York, NY 10168 or the New York State Attorney General’s Charities Bureau, 120 Broadway, 3rd Floor, New York, NY 10271..</p><br />
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">With thanks,<br /> <br />Mary Ellen Iskenderian<br />President and CEO</p>
<p style="font-family: arial; color: #4d4f53; font-size: 12px;"><sup>PS: You may be able to double your gift! Please inquire to your Human Resources team to learn if your company offers a Matching Gift Program.</sup></p><br />
<p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Women\'s World Banking is the global nonprofit devoted to giving more low-income women access to the financial tools and resources essential to their security and prosperity. Learn more about our work at <a href="http://www.womensworldbanking.org" target="_blank">womensworldbanking.org</a>.</em></p>
<p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Follow us on Twitter at <a href="http://www.twitter.com/womensworldbnkg" target="_blank">@womensworldbnkg</a> and Like us on Facebook at <a href="http://www.facebook.com/womensworldbanking" target="_blank">facebook.com/womensworldbanking</a>.</em></p>
</td>
</tr>
</tbody>
</table>';

		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		wp_mail( self::$post_data['email'], 'Thank you for your donation', $content, $headers );
		wp_mail( 'development@womensworldbanking.org', 'Thank you for your donation', $content, $headers );
		wp_mail( 'klm@womensworldbanking.org', 'Thank you for your donation', $content, $headers );
		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

	}

	private static function donate_form_create_campaign_member() {
		$data = Array(
			'CampaignId' => isset(self::$post_data['sf_campaign'])?self::$post_data['sf_campaign']:ggsf_newcampaignid,
			'Status' => 'Registered',
			'ContactId' => self::$contact_id
		);

		$campaign_object = ggsf_create_object('CampaignMember', $data);
		// $campaign_object = ggsf_lookup_object('CampaignMember', 'ContactId', self::$contact_id);
		// if ( $campaign_object['totalSize'] == 0) { // Campaign member doesn't exist: create it
			
		// 	if( !isset($campaign_object['id']) ) var_dump($campaign_object);
		// 	self::$campaign_member_id = $campaign_object['id'];
		// } else { // Update it
		// 	self::$campaign_member_id = $campaign_object['records'][0]['Id'];
		// 	$campaign_object = ggsf_update_object('CampaignMember', self::$campaign_member_id, $data);
		// }
	}

	

	

	

	private static function donate_form_subscribe_to_newsletter() {
		$merge_vars = Array(
			"SALUTATION" => self::$post_data['salutation'],
			"FNAME" => self::$post_data['first_name'],
			"LNAME" => self::$post_data['last_name'],
			"GROUPINGS" => Array(
				Array(
					'id' => 1,
					'groups' => Array('Individual Solicitation')
				)
			),
			"ACTION" => 'mc4wp_subscribe'
		);

		// Add to mailchimp (on hold, need to confirm this is necessary)
		//MC4WP_Lite_Form_Request::subscribe(self::$post_data['email'], $merge_vars);

		// Add to Salesforce
		//$vars = Array(
			//'MC4SF__Email2__c' => self::$post_data['email'],
			//'MC4SF__Interests__c' => ggsf_newinterests,
			//'MC4SF__MC_List__c' => ggsf_newmclist,
			//'Name' => self::$post_data['email'].' - Main Mail 2',
			//'MC4SF__Member_Status__c' => 'Subscribed',
			//'MC4SF__MailChimp_List_ID__c' => ggsf_mclistid
		//);
		//$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);
	}

	private static function donate_form_create_account_object() {
		$account_name = isset( self::$post_data['company'] ) && !empty(self::$post_data['company'])?self::$post_data['company']:self::$post_data['first_name'].' '.self::$post_data['last_name'];

		$data = Array(
			'Account_Status__c' => 'Monetary - Active',
			'Profile_Type__c' => isset( self::$post_data['company'] ) && !empty(self::$post_data['company'])?'Professional':'Personal',
			'OwnerId' => ggsf_relationshipmanager,
			'Name' => $account_name
		);

		$account_object = ggsf_lookup_object('Account', 'Name', $account_name);

		if ( $account_object['totalSize'] == 0) { // Account doesn't exist: create it
			$account_object = ggsf_create_object('Account', $data);
			self::$account_id = $account_object['id'];
		} else { // Update it
			self::$account_id = $account_object['records'][0]['Id'];
			$response = ggsf_update_object('Account', self::$account_id, $data);
			if ( $response ) $account_object = $response;
		}
	}
}
