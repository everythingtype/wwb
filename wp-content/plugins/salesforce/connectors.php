<?php

/*
Step A 
	1.  Lookup MC4SF__MC_Subscriber__c (email against tfa_attemailc)
		1.1.  If matching record:
			1.1.1.  Prefill email -> MC4SF__Email2__c
			1.1.2.  Prefill current interests -> MC4SF__Interests__c
	2.  Lookup Contact (email against Email)
		2.1.  If matching record:
			2.1.1.  Prefill Current Account ID -> AccountId

Step B
	1.  Update Contact object
		1.1.  Create new Opportunity object 
			1.1.1.  Create new Opportunity_Component__c object 
			1.1.2.  Create new OpportunityContactRole object 
		1.2.  Update MC4SF__MC_Subscriber__c object 
	2.  Lookup Contact object 
		2.1.  Create new Account object 
			2.1.1.  Create new Contact object 
		2.2.  Update MC4SF__MC_Subscriber__c object 
		2.3.  Create new Opportunity object 
			2.3.1.  Create new Opportunity_Component__c object
			2.3.2.  Create new OpportunityContactRole object 
	3.  Update CampaignMember object
	4.  Lookup MC4SF__MC_Subscriber__c object 
		4.1.  Create new MC4SF__MC_Subscriber__c object
	5.  Update Contact object 
	6.  Update Contact object 
*/

define('ggsf_campaignid','701G0000000WpDv');
define('ggsf_relationshipmanager','005G0000004lLpM');
define('ggsf_funraisingtarget','a1CG00000021rk7');
define('ggsf_oppcomponentname','2015 Web Donation');
define('ggsf_interests','a12G000000204FVIAY');
define('ggsf_mclist','a14G0000001g4jEIAQ');
define('ggsf_mclistid','489dee8f5f');

include_once('new_wwb_donation_connector.php');
include_once('new_wwb_contactus_connector.php');
include_once('summit_connector.php');

add_action('wwb_form_donation_submit', Array('ggsf_dfc', 'donate_form_connector') );

Class ggsf_dfc {
	public static $post_data;
	public static $contact_id;
	public static $account_id;
	public static $account_name;
	public static $campaign_member_id;
	public static $opportunity_id;
	public static $opportunity_contact_id;
	public static $opportunity_component_id;

	public static function donate_form_connector() {
		self::$post_data = $_POST;
		self::donate_form_connect_lookup_contact_object();
		if ( self::$contact_id ) self::donate_form_connector_update_contact_object();

		if (self::$post_data['email_updates'] == 'true') { // Subscribe to newsletter
			self::donate_form_subscribe_to_newsletter();
		}

		self::donate_form_create_opportunity();
		self::donate_form_create_opportunity_component();
		self::donate_form_create_opportunity_contact_role();
		self::donate_form_create_campaign_member();
		self::send_confirmation_email();
	}

	private static function send_confirmation_email() {
		 $headers = 'From: Women\'s World Banking <development@womensworldbanking.org>' . "\r\n";
		$content = '<table width="600" border="0" cellpadding="5"><tbody><tr><td><a href="http://www.womensworldbanking.org" target="_blank"><img style="float: left;" src="http://www.womensworldbanking.org/wp-content/uploads/2013/06/WWB_Horizontal_RGB.png" alt="Women\'s World Banking" width="200" height="19" /></a></td>
<td colspan="2"><p style="font-family: arial; color: #4d4f53; font-size: 11px;">122 East 42nd Street, 42nd Floor<br /> New York, NY, 10168<br /> <strong>Tax ID #: 13-3101527</strong></p></td></tr>
<tr><td colspan="3"><hr /></td></tr><tr><td width="216"><p style="font-family: arial; color: #4d4f53; font-size: 11px;"><strong>DONOR NAME:</strong><br /> '.self::$post_data['first_name'].' '.self::$post_data['last_name'].' </p>
<p style="font-family: arial; color: #4d4f53; font-size: 11px;"><strong>DONOR ADDRESS:</strong><br />'.self::$post_data['address_1'].'<br /> '.self::$post_data['city'].', '.self::$post_data['state'].' '.self::$post_data['zip'].'<br /> '.self::$post_data['country'].'</p>
</td><td width="140"><p style="font-family: arial; color: #4d4f53; font-size: 11px;"><strong>GIFT AMOUNT:</strong><br /> $ '.self::$post_data['amount'].'<br /> <br /> <strong>GIFT DATE:</strong><br /> '.date('m/d/Y').'</p>
</td><td align="right" width="206"><img src="http://www.womensworldbanking.org/wp-content/uploads/2013/07/Donate-Form-Imagev2.png" alt="" /></td>
</tr><tr><td colspan="3"><hr /><p style="font-family: arial; color: #4d4f53; font-size: 11px;">Dear '.self::$post_data['first_name'].'</p>
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Please accept our most heartfelt thanks for your support of Women&rsquo;s World Banking. The collective power of giving has a tremendous impact on our ability to develop new and innovative financial products and services for women and girls around the world.</p> <br />
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Kindly note that the tax-deductible amount of your gift is listed above. Please save this letter for your tax records as confirmation of your donation. Friends of Women\'s World Banking USA, Inc. is a 501(c)(3) nonprofit organization. If you have any questions, please email <a href="mailto:development@womensworldbanking.org">development@womensworldbanking.org</a>.</p>
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">Friends of WWB/USA, Inc. is exempt under Section 501(c)(3) of the Internal Revenue Code, as such, contributions are deductible for federal income tax purposes. The latest annual report and other information about Friends of WWB/USA Inc.’s purpose, programs and activities can be obtained by contacting Tom Jones at 122 East 42nd Street, 42nd Floor, New York, NY 10168 or the New York State Attorney General’s Charities Bureau, 120 Broadway, 3rd Floor, New York, NY 10271..</p><br />
<p style="font-family: arial; color: #4d4f53; font-size: 11px;">With thanks,<br /> <br />Vivian Santora<br /> Chief Development Officer</p>
<p style="font-family: arial; color: #4d4f53; font-size: 12px;"><sup>PS: You may be able to double your gift! Please inquire to your Human Resources team to learn if your company offers a Matching Gift Program.</sup></p><br />
<p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Women\'s World Banking is the global nonprofit devoted to giving more low-income women access to the financial tools and resources essential to their security and prosperity. Learn more about our work at <a href="http://www.womensworldbanking.org" target="_blank">womensworldbanking.org</a>.</em></p>
<p style="font-family: arial; color: #4d4f53; font-size: 10px;"><em>Follow us on Twitter at <a href="http://www.twitter.com/womensworldbnkg" target="_blank">@womensworldbnkg</a> and Like us on Facebook at <a href="http://www.facebook.com/womensworldbanking" target="_blank">facebook.com/womensworldbanking</a>.</em></p>
</td>
</tr>
</tbody>
</table>';

		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		wp_mail( self::$post_data['email'], 'Thank you for your donation', $content, $headers );
		wp_mail( 'development@womensworldbanking.org', 'Thank you copy for a donation', $content, $headers );
		wp_mail( 'klm@womensworldbanking.org', 'Thank you copy for a donation', $content, $headers );
		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

	}

	private static function donate_form_create_campaign_member() {
		$data = Array(
			'CampaignId' => ggsf_campaignid,
			'Status' => 'Registered',
			'ContactId' => self::$contact_id
		);

		$campaign_object = ggsf_lookup_object('CampaignMember', 'ContactId', self::$contact_id);
		if ( $campaign_object['totalSize'] == 0) { // Campaign member doesn't exist: create it
			$campaign_object = ggsf_create_object('CampaignMember', $data);
			if( !isset($campaign_object['id']) ) var_dump($campaign_object);
			self::$campaign_member_id = $campaign_object['id'];
		} else { // Update it
			self::$campaign_member_id = $campaign_object['records'][0]['Id'];
			$campaign_object = ggsf_update_object('CampaignMember', self::$campaign_member_id, $data);
		}
	}

	private static function donate_form_create_opportunity() {
		$opp_name = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - '.self::$post_data['recurrence'];
		$message = '';
		if ( isset(self::$post_data['employer_match']) && self::$post_data['employer_match'] == 'true') $message = "This donor's employer will match, please contact them.\n";
		if ( isset(self::$post_data['in_memory']) && self::$post_data['in_memory'] == 'true') {
			$message .= "This donation is a tribute gift, please contact them.";

			$headers = 'From: Women\'s World Banking <development@womensworldbanking.org>' . "\r\n";
			$content = 'Name: '.self::$post_data['first_name'].' '.self::$post_data['last_name']."\n";
			$content .= 'Email: '.self::$post_data['email']."\n";
			$content .= 'Amount:'.self::$post_data['amount']."\n";

			wp_mail( 'development@womensworldbanking.org', 'Tribute gift alert', $content, $headers );
			wp_mail( 'development@womensworldbanking.org', 'Thank you copy for a donation', $content, $headers );
		} 


		$data = Array(
			'Name' => $opp_name,
			'StageName' => 'Awarded',
			'CloseDate' => date('Y-m-d'),
			'AccountId' => self::$account_id,
			'Component_Amount__c' => self::$post_data['amount'],
			'Type' => 'Renewal',
			'CampaignId' => ggsf_campaignid,
			'Restrictions__c' => 'Unrestricted',
			'OwnerId' => ggsf_relationshipmanager,
			'Description' => $message
		);

		$opportunity_object = ggsf_create_object('Opportunity', $data);
		if ( isset($opportunity_object['id'])) self::$opportunity_id = $opportunity_object['id'];
	}

	private static function donate_form_create_opportunity_component() {
		$data = Array(
			'Fundraising_Target__c' => ggsf_funraisingtarget,
			'Payment_Received__c' => 'Yes',
			'Name' => '2015 Web Donation',
			'Component_Amount__c' => self::$post_data['amount'],
			'Opportunity__c' => self::$opportunity_id
		);

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

	private static function donate_form_subscribe_to_newsletter() {
		$merge_vars = Array(
			"SALUTATION" => self::$post_data['salutation'],
			"FNAME" => self::$post_data['first_name'],
			"LNAME" => self::$post_data['last_name'],
			"GROUPINGS" => Array(
				Array(
					'id' => 1,
					'groups' => isset(self::$post_data['Contact_Category'])?Array( self::$post_data['Contact_Category'] ):Array('Individual Solicitation')
				)
			),
			"ACTION" => 'mc4wp_subscribe'
		);

		// Add to mailchimp (on hold, need to confirm this is necessary)
		MC4WP_Lite_Form_Request::subscribe(self::$post_data['email'], $merge_vars);

		// Add to Salesforce
		$vars = Array(
			'MC4SF__Email2__c' => self::$post_data['email'],
			'MC4SF__Interests__c' => ggsf_interests,
			'MC4SF__MC_List__c' => ggsf_mclist,
			'Name' => self::$post_data['email'].' - Main Mail 2',
			'MC4SF__Member_Status__c' => 'Subscribed',
			'MC4SF__MailChimp_List_ID__c' => ggsf_mclistid
		);
		$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);
	}

	private static function donate_form_connect_lookup_contact_object() {
		$contact_object = ggsf_lookup_object('Contact', 'Email', self::$post_data['email']);
		if ( $contact_object['totalSize'] == 0 ) { // No records matching
			self::$contact_id = false;
			self::donate_form_create_account_object();
			self::donate_form_create_contact_object();
		} else { // At least one record matching
			self::$contact_id = $contact_object['records'][0]['Id'];
			self::donate_form_create_account_object();
		}
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

	private static function donate_form_create_contact_object() {
		$data = Array(
			'LastName' => self::$post_data['last_name'],
			'FirstName' => self::$post_data['first_name'],
			'MailingStreet' => self::$post_data['address_1'],
			'MailingCity' => self::$post_data['city'],
			'MailingState' => self::$post_data['state'],
			'MailingPostalCode' => self::$post_data['zip'],
			'MailingCountry' => self::$post_data['country'],
			'MobilePhone' => self::$post_data['phone'],
			'AccountId' => self::$account_id,
			'Email' => self::$post_data['email'],
			'OwnerId' => ggsf_relationshipmanager,
			'Salutation' => self::$post_data['salutation']
		);


		if (self::$post_data['email_updates'] == 'true') {
			$data['Groups_sf__c'] = isset(self::$post_data['Contact_Category'])?self::$post_data['Contact_Category']:'Individual Solicitation';
		};


		$contact_object = ggsf_create_object('Contact', $data);
		self::$contact_id = $contact_object['id'];
	}

	private static function donate_form_connector_update_contact_object() {
		$data = Array(
			'MailingStreet' => self::$post_data['address_1'],
			'MailingCity' => self::$post_data['city'],
			'MailingState' => self::$post_data['state'],
			'MailingPostalCode' => self::$post_data['zip'],
			'MobilePhone' => self::$post_data['phone'],
			'LastName' => self::$post_data['last_name'],
			'Salutation' => self::$post_data['salutation']
		);

		ggsf_update_object('Contact', self::$contact_id, $data);
	}

}


add_action('wwb_form_globalbenefit2014_submit', Array('ggsf_gb14', 'globalbenefit2014_form_connector') );

Class ggsf_gb14 {
	public static $post_data;
	public static $contact_id;
	public static $account_id;
	public static $account_name;
	public static $campaign_member_id;

	public static function globalbenefit2014_form_connector() {
		self::$post_data = $_POST;

		if ( self::update_contact_object() ) {
			self::create_opportunity();
			self::create_opportunity_component();
			self::create_opportunity_contact_role();
		} else {
			self::create_account_object();
			self::create_contact_object();
			self::create_opportunity('New Business');
			self::create_opportunity_component();
			self::create_opportunity_contact_role();
		}

		if (self::$post_data['email_updates'] == 'true') {
			self::subscribe_to_newsletter();
		}
	}

	private static function update_contact_object() {
		$contact_object = ggsf_lookup_object('Contact', 'Email', self::$post_data['email']);
		if ( $contact_object['totalSize'] > 0) {
			$data = Array(
				'MailingStreet' => self::$post_data['address_1'],
				'MailingCity' => self::$post_data['city'],
				'MailingState' => self::$post_data['state'],
				'MailingCountry' => self::$post_data['country'],
				'MailingPostalCode' => self::$post_data['zip'],
				'MobilePhone' => self::$post_data['phone'],
				'LastName' => self::$post_data['last_name'],
				'Salutation' => self::$post_data['salutation']
			);
			self::$contact_id = $contact_object['records'][0]['Id'];
			$contact_object = ggsf_update_object('Contact', self::$contact_id, $data);
			return true;
		} else {
			return false;
		}
	}

	private static function create_contact_object() {
		$data = Array(
			'LastName' => self::$post_data['last_name'],
			'FirstName' => self::$post_data['first_name'],
			'MailingStreet' => self::$post_data['address_1'],
			'MailingCity' => self::$post_data['city'],
			'MailingState' => self::$post_data['state'],
			'MailingPostalCode' => self::$post_data['zip'],
			'MailingCountry' => self::$post_data['country'],
			'MobilePhone' => self::$post_data['phone'],
			'AccountId' => self::$account_id,
			'Email' => self::$post_data['email'],
			'OwnerId' => ggsf_relationshipmanager,
			'Salutation' => self::$post_data['salutation']
		);

		$contact_object = ggsf_create_object('Contact', $data);
		self::$contact_id = $contact_object['id'];
	}

	private static function create_opportunity( $type = false ) {
		$opp_name = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - 2014 Benefit';

		$data = Array(
			'Name' => $opp_name,
			'StageName' => 'Awarded',
			'CloseDate' => date('Y-m-d'),
			'Type' => (!$type)?'Renewal':$type,
			'CampaignId' => ggsf_campaignid,
			'Restrictions__c' => 'Unrestricted',
			'OwnerId' => ggsf_relationshipmanager
		);

		if ( isset(self::$account_id) ) $data['AccountId'] = self::$account_id;

		$opportunity_object = ggsf_create_object('Opportunity', $data);
		if ( isset($opportunity_object['id'])) self::$opportunity_id = $opportunity_object['id'];
		return true;
	}

	private static function create_opportunity_component() {
		$data = Array(
			'Fundraising_Target__c' => 'a1CG00000021lBH',
			'Component_Amount__c' => self::$post_data['amount'],
			'Payment_Received__c' => 'Yes',
			'Name' => 'Ben 2014'
		);

		$opportunity_component_object = ggsf_create_object('Opportunity_Component__c', $data);
		if ( isset($opportunity_component_object['id'])) self::$opportunity_component_id = $opportunity_component_object['id'];	
		return true;
	}

	private static function create_opportunity_contact_role() {
		$data = Array(
			'ContactId' => self::$contact_id,
			'Role' => 'Donor',
			'IsPrimary' => 'true'
		);

		$opportunity_contact_object = ggsf_create_object('OpportunityContactRole', $data);
		if ( isset($opportunity_contact_object['id'])) self::$opportunity_contact_id = $opportunity_contact_object['id'];	
	}

	private static function create_account_object() {
		$account_name = isset( self::$post_data['company'] ) && !empty(self::$post_data['company'])?self::$post_data['company'].' ('.self::$post_data['last_name'].' '.self::$post_data['first_name'].')':self::$post_data['last_name'].' '.self::$post_data['first_name'];

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
			$account_object = ggsf_update_object('Account', self::$account_id, $data);
		}
		return true;
	}

	private static function subscribe_to_newsletter() {
		$merge_vars = Array(
			"SALUTATION" => self::$post_data['salutation'],
			"FNAME" => self::$post_data['first_name'],
			"LNAME" => self::$post_data['last_name'],
			"GROUPINGS" => Array(
				Array(
					'id' => 1,
					'groups' => isset(self::$post_data['Contact_Category'])?Array( self::$post_data['Contact_Category'] ):Array('Individual Solicitation')
				)
			),
			"ACTION" => 'mc4wp_subscribe'
		);

		// Add to mailchimp (on hold, need to confirm this is necessary)
		MC4WP_Lite_Form_Request::subscribe(self::$post_data['email'], $merge_vars);

		// Add to Salesforce
		// $vars = Array(
		// 	'MC4SF__Email2__c' => self::$post_data['email'],
		// 	'MC4SF__Interests__c' => 'a12G000000204FVIAY',
		// 	'MC4SF__MC_List__c' => 'a14G0000001g4jEIAQ',
		// 	'Name' => self::$post_data['email'].' - Main Mail 2',
		// 	'MC4SF__Member_Status__c' => 'Subscribed',
		// 	'MC4SF__MailChimp_List_ID__c' => '489dee8f5f'
		// );

		// $newsletter_object = ggsf_lookup_object('MC4SF__MC_Subscriber__c', 'MC4SF__Email2__c', self::$post_data['email']);
		// if ( $newsletter_object['totalSize'] == 0) { // Create
		// 	$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);
		// }
	}
}

add_action('wwb_form_1billion_submit', Array('ggsf_1b', 'onebillion_form_connector') );

Class ggsf_1b {
	public static $post_data;
	public static $contact_id;
	public static $account_id;
	public static $account_name;
	public static $campaign_member_id;

	public static function onebillion_form_connector() {
		self::$post_data = $_POST;

		if ( self::update_contact_object() ) {
			self::create_opportunity();
			self::create_opportunity_component();
			self::create_opportunity_contact_role();
		} else {
			self::create_account_object();
			self::create_contact_object();
			self::create_opportunity('New Business');
			self::create_opportunity_component();
			self::create_opportunity_contact_role();
		}

		if (self::$post_data['email_updates'] == 'true') {
			self::subscribe_to_newsletter();
		}
	}

	private static function update_contact_object() {
		$contact_object = ggsf_lookup_object('Contact', 'Email', self::$post_data['email']);
		if ( $contact_object['totalSize'] > 0) {
			$data = Array(
				'MailingStreet' => self::$post_data['address_1'],
				'MailingCity' => self::$post_data['city'],
				'MailingState' => self::$post_data['state'],
				'MailingCountry' => self::$post_data['country'],
				'MailingPostalCode' => self::$post_data['zip'],
				'MobilePhone' => self::$post_data['phone'],
				'LastName' => self::$post_data['last_name'],
				'Salutation' => self::$post_data['salutation']
			);
			self::$contact_id = $contact_object['records'][0]['Id'];
			$contact_object = ggsf_update_object('Contact', self::$contact_id, $data);
			return true;
		} else {
			return false;
		}
	}

	private static function create_contact_object() {
		$data = Array(
			'LastName' => self::$post_data['last_name'],
			'FirstName' => self::$post_data['first_name'],
			'MailingStreet' => self::$post_data['address_1'],
			'MailingCity' => self::$post_data['city'],
			'MailingState' => self::$post_data['state'],
			'MailingPostalCode' => self::$post_data['zip'],
			'MailingCountry' => self::$post_data['country'],
			'MobilePhone' => self::$post_data['phone'],
			'AccountId' => self::$account_id,
			'Email' => self::$post_data['email'],
			'OwnerId' => ggsf_relationshipmanager,
			'Salutation' => self::$post_data['salutation']
		);

		$contact_object = ggsf_create_object('Contact', $data);
		self::$contact_id = $contact_object['id'];
	}

	private static function create_opportunity( $type = false ) {
		$opp_name = self::$post_data['first_name'].' '.self::$post_data['last_name'].' - 2014 1billionwomen appeal';

		$data = Array(
			'Name' => $opp_name,
			'StageName' => 'Awarded',
			'CloseDate' => date('Y-m-d'),
			'Type' => (!$type)?'Renewal':$type,
			'CampaignId' => ggsf_campaignid,
			'Restrictions__c' => 'Unrestricted',
			'OwnerId' => ggsf_relationshipmanager
		);

		if ( isset(self::$account_id) ) $data['AccountId'] = self::$account_id;

		$opportunity_object = ggsf_create_object('Opportunity', $data);
		if ( isset($opportunity_object['id'])) self::$opportunity_id = $opportunity_object['id'];
		return true;
	}

	private static function create_opportunity_component() {
		$data = Array(
			'Fundraising_Target__c' => 'a1CG00000021iXk',
			'Component_Amount__c' => self::$post_data['amount'],
			'Payment_Received__c' => 'Yes',
			'Name' => '2014 Web Donation'
		);

		$opportunity_component_object = ggsf_create_object('Opportunity_Component__c', $data);
		if ( isset($opportunity_component_object['id'])) self::$opportunity_component_id = $opportunity_component_object['id'];	
		return true;
	}

	private static function create_opportunity_contact_role() {
		$data = Array(
			'ContactId' => self::$contact_id,
			'Role' => 'Donor',
			'IsPrimary' => 'true'
		);

		$opportunity_contact_object = ggsf_create_object('OpportunityContactRole', $data);
		if ( isset($opportunity_contact_object['id'])) self::$opportunity_contact_id = $opportunity_contact_object['id'];	
	}

	private static function create_account_object() {
		$account_name = isset( self::$post_data['company'] ) && !empty(self::$post_data['company'])?self::$post_data['company'].' ('.self::$post_data['last_name'].' '.self::$post_data['first_name'].')':self::$post_data['last_name'].' '.self::$post_data['first_name'];

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
			$account_object = ggsf_update_object('Account', self::$account_id, $data);
		}
		return true;
	}

	private static function subscribe_to_newsletter() {
		$merge_vars = Array(
			"SALUTATION" => self::$post_data['salutation'],
			"FNAME" => self::$post_data['first_name'],
			"LNAME" => self::$post_data['last_name'],
			"GROUPINGS" => Array(
				Array(
					'id' => 1,
					'groups' => isset(self::$post_data['Contact_Category'])?Array( self::$post_data['Contact_Category'] ):Array('Individual Solicitation')
				)
			),
			"ACTION" => 'mc4wp_subscribe'
		);

		// Add to mailchimp (on hold, need to confirm this is necessary)
		MC4WP_Lite_Form_Request::subscribe(self::$post_data['email'], $merge_vars);

		// Add to Salesforce
		// $vars = Array(
		// 	'MC4SF__Email2__c' => self::$post_data['email'],
		// 	'MC4SF__Interests__c' => 'a12G000000204FVIAY',
		// 	'MC4SF__MC_List__c' => 'a14G0000001g4jEIAQ',
		// 	'Name' => self::$post_data['email'].' - Main Mail 2',
		// 	'MC4SF__Member_Status__c' => 'Subscribed',
		// 	'MC4SF__MailChimp_List_ID__c' => '489dee8f5f'
		// );

		// $newsletter_object = ggsf_lookup_object('MC4SF__MC_Subscriber__c', 'MC4SF__Email2__c', self::$post_data['email']);
		// if ( $newsletter_object['totalSize'] == 0) { // Create
		// 	$response = ggsf_create_object('MC4SF__MC_Subscriber__c',$vars);
		// }
	}
}
