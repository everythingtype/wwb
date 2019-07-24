<?php

add_action('wwb_form_contact_submit', Array('ggsf_c', 'contact_form_connector') );

Class ggsf_c {
	public static $post_data;
	public static $lead_id;
	public static $account_id;
	public static $account_name;
	public static $campaign_member_id;
	public static $contact_id;

	public static function contact_form_connector() {
		self::$post_data = $_POST;

		self::create_lead_object();

		if (self::$post_data['email_updates'] == 'true') {
			self::subscribe_to_newsletter();
			self::send_confirmation_email();
		}
	}

	private static function send_confirmation_email() {
		$headers = 'From: Women\'s World Banking <communications@womensworldbanking.org>' . "\r\n";
		$content = '<table width="600" border="0" cellpadding="5"><tbody><tr><td><a href="http://www.womensworldbanking.org" target="_blank"><img style="float: left;" src="http://www.womensworldbanking.org/wp-content/uploads/2013/06/WWB_Horizontal_RGB.png" alt="Women\'s World Banking" width="200" height="19" /></a></td><td colspan="2"><p style="font-family: arial; color: #4d4f53; font-size: 11px;">122 East 42nd Street, 42nd Floor<br /> New York, NY, 10168<br /> <strong>Tax ID #: 13-3101527</strong></p></td></tr><tr><td colspan="3"><hr /><p style="font-family: arial; color: #4d4f53; font-size: 11px;">Dear '.self::$post_data['first_name'].',</p><p style="font-family: arial; color: #4d4f53; font-size: 11px;"> Thank you for signing up to receive email updates from Women\'s World Banking. We look forward to sharing our quarterly newsletter with you, as well as other special news and events as they come. In the meantime, please make sure to visit our <a href="https://www.womensworldbanking.org/news/blog/" target="_blank">blog</a>, read our <a href="https://www.womensworldbanking.org/publications/annual-report-2013/" target="_blank">annual report<a/> and follow us on social media to get a window into our work.</p><p style="font-family: arial; color: #4d4f53; font-size: 11px;">Women\'s World Banking has worked for over 35 years to ensure that millions more women can build security and prosperity for themselves and their families in communities worldwide. We are thrilled to count you as a supporter of our work and hope you will learn and engage with the work we do to increase access to finance for women around the globe.</p><p style="font-family: arial; color: #4d4f53; font-size: 11px;">Warm regards,<br /> Mary Ellen Iskenderian<br /> President and CEO</p><p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Women\'s World Banking is the global nonprofit devoted to giving more low-income women access to the financial tools and resources essential to their security and prosperity. Learn more about our work at <a href="http://www.womensworldbanking.org" target="_blank">womensworldbanking.org</a>.</em></p><p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Follow us on Twitter at <a href="http://www.twitter.com/womensworldbnkg" target="_blank">@womensworldbnkg</a> and Like us on Facebook at <a href="http://www.facebook.com/womensworldbanking" target="_blank">facebook.com/womensworldbanking</a>.</em></p></td></tr></tbody></table>';

		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		wp_mail( self::$post_data['email'], 'Thanks for signing up to our newsletter!', $content, $headers );
		//wp_mail( 'development@womensworldbanking.org', 'Thank you copy for a subscriber', $content, $headers );
		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

	}

	private static function update_contact_object() {
		$contact_object = ggsf_lookup_object('Contact', 'Email', self::$post_data['email']);
		if ( $contact_object['totalSize'] > 0) {
			$data = Array();
			if ( isset( $post_data['address_1'] ) ) $data['MailingStreet'] = self::$post_data['address_1'];
			if ( isset( $post_data['city'] ) ) $data['MailingCity'] = self::$post_data['city'];
			if ( isset( $post_data['state'] ) ) $data['MailingState'] = self::$post_data['state'];
			if ( isset( $post_data['country'] ) ) $data['MailingCountry'] = self::$post_data['country'];
			if ( isset( $post_data['zip'] ) ) $data['MailingPostalCode'] = self::$post_data['zip'];
			if ( isset( $post_data['phone'] ) ) $data['MobilePhone'] = self::$post_data['phone'];
			if ( isset( $post_data['salutation'] ) ) $data['Salutation'] = self::$post_data['salutation'];
			if ( isset( $post_data['last_name'] ) ) $data['LastName'] = self::$post_data['last_name'];
			if ( isset( $post_data['message'] ) ) $data['Description'] = self::$post_data['message'];
			self::$contact_id = $contact_object['records'][0]['Id'];
			$contact_object = ggsf_update_object('Contact', self::$contact_id, $data);
			return true;
		} else {
			return false;
		}
	}

	private static function create_opportunity() {
		$opp_name = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - '.self::$post_data['recurrence'];

		$data = Array(
			'Name' => $opp_name,
			'StageName' => 'Awarded',
			'CloseDate' => date('Y-m-d'),
			'AccountId' => self::$account_id,
			'Type' => 'Renewal',
			'CampaignId' => ggsf_campaignid,
			'Restrictions__c' => 'Unrestricted',
			'OwnerId' => ggsf_relationshipmanager
		);

		$opportunity_object = ggsf_create_object('Opportunity', $data);
		if ( isset($opportunity_object['id'])) self::$opportunity_id = $opportunity_object['id'];
	}

	private static function create_lead_object() {
		$data = Array(
			'LastName' => self::$post_data['last_name'],
			'FirstName' => self::$post_data['first_name'],
			'Email' => self::$post_data['email'],
			'Salutation' => self::$post_data['salutation'],
			'Title' => self::$post_data['title'],
			'Referred_by__c' => isset(self::$post_data['refer'])?$post_data['refer']:'',
			'Phone' => self::$post_data['phone'],
			'Description' => isset(self::$post_data['message'])?self::$post_data['message']:'',
			'Company' => (isset(self::$post_data['company']) && self::$post_data['company'] != '')?self::$post_data['company']:self::$post_data['first_name'].' '.self::$post_data['last_name'],
			'WWB_Staff_Contact__c' => 'Web',
			'Status' => 'Open',
			'LeadSource' => self::$post_data['refer_source'],
			'Web_Form__c' => 'Contact Us',
		);

		if ( self::$post_data['email_updates'] == 'true') {
			$data['Groups__c'] = 'Individual Solicitation';
		}

		$lead_object = ggsf_create_object('Lead', $data);
		self::$lead_id = $lead_object['id'];
	}

	private static function subscribe_to_newsletter() {
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
		MC4WP_Lite_Form_Request::subscribe(self::$post_data['email'], $merge_vars);

		// Add to Salesforce
		$vars = Array(
			'MC4SF__Email2__c' => self::$post_data['email'],
			'MC4SF__Interests__c' => ggsf_newinterests,
			'MC4SF__MC_List__c' => ggsf_newmclist,
			'Name' => self::$post_data['email'].' - Main Mail 2',
			'MC4SF__Member_Status__c' => 'Subscribed',
			'MC4SF__MailChimp_List_ID__c' => ggsf_mclistid
		);
		$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);

		$newsletter_object = ggsf_lookup_object('MC4SF__MC_Subscriber__c', 'MC4SF__Email2__c', self::$post_data['email']);
		if ( $newsletter_object['totalSize'] == 0) { // Create
			$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);
		}
	}
}
?>
