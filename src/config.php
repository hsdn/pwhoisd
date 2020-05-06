<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

// This line can not be deleted!
namespace pWhoisd;

return [
	// Daemon configuration
	'daemon' =>
	[
		// Server listen IP address
		'listen_address' => '0.0.0.0',

		// Server listen IPv6 address
		'listen_address_ipv6' => '',

		// Server listen port
		'listen_port'    => 43,

		// Max server worker processes
		'workers'        => 300,
	],

	// Logging configuration
	'logging' =>
	[
		// Log severity (Log::debug, Log::info, Log::warning, Log::error)
		'severity' => Log::debug,

		// Log file path (e.g. ./pwhoisd.log)
		'file'     => FALSE,
	],

	// Security configuration
	'security' =>
	[
		// List of access rules
		// Warning! By default all connections are allowed and no limits do not apply.
		//
		// Access rule format:
		// ['ACTION'(, 'MESSAGE')(, ['VARIABLE', 'OPERATOR', 'VALUE/UNIT'])(, ['VARIABLE', 'OPERATOR', 'VALUE/UNIT'])(, ...)]
		//
		// ACTION:
		//   allow       Permit a client connections and requests
		//   deny        Print specified message to a client (optional) and drop the connection
		//   drop        Silently drop a client connection. No messages is sent
		//
		// VARIABLE:
		//   client_ip   Client connection IP address (used for IP/subnets checks)
		//   requests    Used to specify a requests rate limit to the specific client connections
		//   rate        Used to specify the global server requests rate limit for all clients connections
		//
		// MESSAGE:
		//   Here specify the name of the section in the messages array configuration.
		//
		// OPERATOR:
		// Any one of the allowed operators. Not to be used in IP rules. The allowed operators are:
		//   <           Less than
		//   ==          Equal to
		//   <=          Less than or equal to
		//   >           Greater than
		//   >=          Greater than or equal to
		//
		// VALUE:
		// For requests rate rules specifies a numeric value with UNIT (see below).
		// It can also be a single IP address (subnet, in CIDR format) or an array of IP addresses (subnets).
		//
		// UNIT:
		// Any one of the allowed units. Not to be used in IP rules. The allowed units are:
		//   sec         Second(s)
		//   hr          Hour(s)
		//   min         Minute(s)
		//   day         Day(s)
		//
		'rules' =>
		[
			// Drop all connections if server rate more 20 requests per one seconds
			// This rule will work with the previous rules (sets a global server rate limits).
			['drop', ['rate', '>', '20/sec']],

			// Allow all connections from IP 127.0.0.1.
			// It is owerride previous (bottom) 'deny' rule for this IP (limits do not works)
			['allow', ['client_ip', '127.0.0.1']],

			// Deny any client if rate more 50 requests per one minute.
			['deny', 'limit_exceeded', ['requests', '>', '50/min']],

			// Permit all connections (it is first rule).
			['allow'],
		],
	],

	// Messages configuration (use empty quotes to set <CR><LF>)
	//
	// This variables is available to use in all messages:
	//   {_request_}      Current request string
	//   {_client_ip_}    Current client IP address
	//   {_client_port_}  Current client port number
	//   {%...%}          Includes other message context (for example: {%header%})
	//   {...}            Includes any data-field value
	//
	'messages' =>
	[
		//
		// Global messages
		//

		// Limit exceeded response message
		'limit_exceeded' => 
		[
			'Whois limit exceeded.',
		],

		// Access denied response message
		'access_denied' => 
		[
			'Access denied.',
		],

		//
		// RIPN's style messages
		//

		// Whois response header fragment
		'header_ripn'  => 
		[
			'% By submitting a query to RIPN\'s Whois Service',
			'% you agree to abide by the following terms of use:',
			'% http://www.ripn.net/about/servpol.html#3.2 (in Russian)',
			'% http://www.ripn.net/about/en/servpol.html#3.2 (in English).',
			'', // <CR><LF>
		],

		// Whois response footer fragment
		'footer_ripn'  => 
		[
			'', // <CR><LF>
			'Last updated on {updated_new}',
		],


		//
		// PIR's style messages
		//

		// Whois response footer fragment
		'footer_pir'  => 
		[
			'', // <CR><LF>
			'Access to Public Interest Registry WHOIS information is provided to assist persons in determining the contents of a domain name registration record in the Public Interest Registry registry database. The data in this record is provided by Public Interest Registry for informational purposes only, and Public Interest Registry does not guarantee its accuracy. This service is intended only for query-based access. You agree that you will use this data only for lawful purposes and that, under no circumstances will you use this data to(a) allow, enable, or otherwise support the transmission by e-mail, telephone, or facsimile of mass unsolicited, commercial advertising or solicitations to entities other than the data recipient\'s own existing customers; or (b) enable high volume, automated, electronic processes that send queries or data to the systems of Registry Operator, a Registrar, or Afilias except as reasonably necessary to register domain names or modify existing registrations. All rights reserved. Public Interest Registry reserves the right to modify these terms at any time. By submitting this query, you agree to abide by this policy. For more information on Whois status codes, please visit https://www.icann.org/resources/pages/epp-status-codes-2014-06-16-en.',
		],
	],

	// Data configuration (first entry is default)
	'data' =>
	[
		// Domain (RIPN's style)
		[
			// Request flag or key
			'flag'    => '-R domain',

			// Storage configuration
			'storage' =>
			[
				// Storage type (mysql)
				'type'    => 'mysql', // Could be psql/mysql.

				// MySQL storage configuration
				'db_host' => 'localhost',
				'db_port' => 3306,
				'db_user' => 'pwhoisd',
				'db_pass' => 'J35tn4fFhEB7YJ6B',
				'db_name' => 'pwhoisd',

				// Database SQL queries (uses first row of query result)
				//
				// This variables is available to use in queries (use variable {_request_} to specify request string):
				//   {_request_}      Current request string
				//   {_client_ip_}    Current client IP address
				//   {_client_port_}  Current client port number
				//   {...}            Includes any data-field value from previous query results (uses first rows)
				// Warning! Value of all variabled are automate escaped.
				//
				'queries' =>
				[
					// Fetch last database update timestamp
					'SELECT MAX(`updated`) AS `updated` FROM `domain_ripn`',

					// Search and fetch row by request
					'SELECT * FROM `domain_ripn` WHERE `domain` LIKE \'{_request_}\' LIMIT 1',
				],
			],

			// Formatting data in fields (overrides or creates fields)
			// The variables is specified in the following format: {VARIABLE}.
			// Warning! Value of all variables are automate quoted (uses double quotes).
			'format'  =>
			[
				// Create new field 'updated_new' based on date-formatted field 'updated'
				['updated_new', 'date("Y.m.d H:i:s \M\S\K", strtotime({updated}))'],

				// Format date fields
				['created', 'date("Y.m.d", strtotime({created}))'],
				['paid_till', 'date("Y.m.d", strtotime({paid_till}))'],
				['free_date', 'date("Y.m.d", strtotime({free_date}))'],

				// Example: hide 'admin_contact' field if not exists flag '-L' in request
				['admin_contact_new', '{admin_contact}'],
				['admin_contact', NULL],
				['admin_contact', '{admin_contact_new}', '-L'],

				// Make a domain field uppercase
				['domain', 'strtoupper({domain})'],
			],

			// Enable fields message space padding (uses only for ['MESSAGE', 'FIELD'] entries)
			//
			// Example #1 (no padding):
			//   ...
			//   domain:HSDN.RU
			//   nserver:ns1.hsdn.org.
			//   org:OOO "Informacionnye Seti"
			//   ...
			//
			// Example #2 (with padding):
			//   ...
			//   domain:  HSDN.RU
			//   nserver: ns1.hsdn.org.
			//   org:     OOO "Informacionnye Seti"
			//   ...
			//
			'spacing' => TRUE,

			// Enables empty (not NULL) fields hidding
			// Note: Fields contains NULL values are hidden anyway.
			'hide_empty' => TRUE,

			// Response data fields entries (use empty entry array to set <CR><LF>)
			//
			// Entries format:
			// [('MESSAGE')(, 'FIELD')(, 'FLAG')]
			//
			// MESSAGE:
			//   The message for the data-field or any other text. Any variables allowed (see. above).
			//   Note: Can insert padding before the data values, use the option 'spacing'.
			//
			// FIELD:
			//   The name of existing the data-field name or new formatted field (see. above).
			//
			// FLAG:
			//   NULL    Entry is shown if the data-field exists and is not empty.
			//           The value of the data-field is inserted after the text (by default).
			//
			//   TRUE    Entry shown only if there is a field exists and data not empty.
			//           The value of the field data is not inserted.
			//
			//   FALSE   Entry shown only if there is no a field or data is empty.
			//           The value of the field data is not inserted.
			'fields'  =>
			[
				// Header message anchor
				['{%header_ripn%}'],

				// This text shown only an empty data-field 'domain'
				['No entries found for the selected source(s).', 'domain', FALSE],

				// Fields below are only shown if exists and data not empty
				['domain', 'domain'],
				['nserver', 'nserver1'],
				['nserver', 'nserver2'],
				['nserver', 'nserver3'],
				['nserver', 'nserver4'],
				['state', 'state'],
				['person', 'person'],
				['org', 'org'],
				['registrar', 'registrar'],
				['admin-contact', 'admin_contact'],
				['descr', 'descr'],
				['created', 'created'],
				['paid-till', 'paid_till'],
				['free-date', 'free_date'],
				['source', 'source'],

				// Footer message anchor (always shown, even if the result is empty)
				['{%footer_ripn%}'],
			],

			// Invalid request message
			'invalid_request'=> 
			[
				'{%header_ripn%}',
				'Invalid request.',
			],
		],

		// Domain (PIR's style)
		[
			'flag'    => 'pir',

			'storage' =>
			[
				'type'    => 'mysql', // Could be psql/mysql.

				'db_host' => 'localhost',
				'db_port' => 3306,
				'db_user' => 'pwhoisd',
				'db_pass' => 'J35tn4fFhEB7YJ6B',
				'db_name' => 'pwhoisd',

				'queries' =>
				[
					// Fetch domain information
					'SELECT * FROM `domain_pir` WHERE `DomainName` LIKE \'{_request_}\' LIMIT 1',

					// Fetch registrant contact
					'SELECT 
						`ID` AS `RegistrantID`,
						`Name` AS `RegistrantName`,
						`Organization` AS `RegistrantOrganization`,
						`Street` AS `RegistrantStreet`,
						`City` AS `RegistrantCity`,
						`StateProvince` AS `RegistrantStateProvince`,
						`PostalCode` AS `RegistrantPostalCode`,
						`Country` AS `RegistrantCountry`,
						`Phone` AS `RegistrantPhone`,
						`PhoneExt` AS `RegistrantPhoneExt`,
						`Fax` AS `RegistrantFax`,
						`FaxExt` AS `RegistrantFaxExt`,
						`Email` AS `RegistrantEmail`
					FROM `contact_pir` WHERE `ID` = \'{RegistrantID}\' LIMIT 1',

					// Fetch admin contact
					'SELECT 
						`ID` AS `AdminID`,
						`Name` AS `AdminName`,
						`Organization` AS `AdminOrganization`,
						`Street` AS `AdminStreet`,
						`City` AS `AdminCity`,
						`StateProvince` AS `AdminStateProvince`,
						`PostalCode` AS `AdminPostalCode`,
						`Country` AS `AdminCountry`,
						`Phone` AS `AdminPhone`,
						`PhoneExt` AS `AdminPhoneExt`,
						`Fax` AS `AdminFax`,
						`FaxExt` AS `AdminFaxExt`,
						`Email` AS `AdminEmail`
					FROM `contact_pir` WHERE `ID` = \'{AdminID}\' LIMIT 1',

					// Fetch tech contact
					'SELECT 
						`ID` AS `TechID`,
						`Name` AS `TechName`,
						`Organization` AS `TechOrganization`,
						`Street` AS `TechStreet`,
						`City` AS `TechCity`,
						`StateProvince` AS `TechStateProvince`,
						`PostalCode` AS `TechPostalCode`,
						`Country` AS `TechCountry`,
						`Phone` AS `TechPhone`,
						`PhoneExt` AS `TechPhoneExt`,
						`Fax` AS `TechFax`,
						`FaxExt` AS `TechFaxExt`,
						`Email` AS `TechEmail`
					FROM `contact_pir` WHERE `ID` = \'{TechID}\' LIMIT 1',
				],
			],

			'format' =>
			[
				['CreationDate', 'date("Y-m-d\TH:i:s\Z", strtotime({CreationDate}))'],
				['UpdatedDate', 'date("Y-m-d\TH:i:s\Z", strtotime({UpdatedDate}))'],
				['RegistryExpiryDate', 'date("Y-m-d\TH:i:s\Z", strtotime({RegistryExpiryDate}))'],
				['DSCreated1', 'date("Y-m-d\TH:i:s\Z", strtotime({DSCreated1}))'],
				['DSCreated2', 'date("Y-m-d\TH:i:s\Z", strtotime({DSCreated2}))'],
			],

			'spacing'    => FALSE,
			'hide_empty' => FALSE,

			'fields' =>
			[
				['Domain Name', 'DomainName'],
				['Domain ID', 'DomainID'],
				['Creation Date', 'CreationDate'],
				['Updated Date', 'UpdatedDate'],
				['Registry Expiry Date', 'RegistryExpiryDate'],
				['Trademark Name', 'TrademarkName'],
				['Trademark Date', 'TrademarkDate'],
				['Trademark Country', 'TrademarkCountry'],
				['Trademark Number', 'TrademarkNumber'],
				['Sponsoring Registrar', 'SponsoringRegistrar'],
				['Sponsoring Registrar IANA ID', 'SponsoringRegistrarIANAID'],
				['WHOIS Server', 'WHOISServer'],
				['Referral URL', 'ReferralURL'],
				['Domain Status', 'DomainStatus'],
				['Registrant ID', 'RegistrantID'],
				['Registrant Name', 'RegistrantName'],
				['Registrant Organization', 'RegistrantOrganization'],
				['Registrant Street', 'RegistrantStreet'],
				['Registrant City', 'RegistrantCity'],
				['Registrant State/Province', 'RegistrantStateProvince'],
				['Registrant Postal Code', 'RegistrantPostalCode'],
				['Registrant Country', 'RegistrantCountry'],
				['Registrant Phone', 'RegistrantPhone'],
				['Registrant Phone Ext', 'RegistrantPhoneExt'],
				['Registrant Fax', 'RegistrantFax'],
				['Registrant Fax Ext', 'RegistrantFaxExt'],
				['Registrant Email', 'RegistrantEmail'],
				['Admin ID', 'AdminID'],
				['Admin Name', 'AdminName'],
				['Admin Organization', 'AdminOrganization'],
				['Admin Street', 'AdminStreet'],
				['Admin City', 'AdminCity'],
				['Admin State/Province', 'AdminStateProvince'],
				['Admin Postal Code', 'AdminPostalCode'],
				['Admin Country', 'AdminCountry'],
				['Admin Phone', 'AdminPhone'],
				['Admin Phone Ext', 'AdminPhoneExt'],
				['Admin Fax', 'AdminFax'],
				['Admin Fax Ext', 'AdminFaxExt'],
				['Admin Email', 'AdminEmail'],
				['Tech ID', 'TechID'],
				['Tech Name', 'TechName'],
				['Tech Organization', 'TechOrganization'],
				['Tech Street', 'TechStreet'],
				['Tech City', 'TechCity'],
				['Tech State/Province', 'TechStateProvince'],
				['Tech Postal Code', 'TechPostalCode'],
				['Tech Country', 'TechCountry'],
				['Tech Phone', 'TechPhone'],
				['Tech Phone Ext', 'TechPhoneExt'],
				['Tech Fax', 'TechFax'],
				['Tech Fax Ext', 'TechFaxExt'],
				['Tech Email', 'TechEmail'],
				['Name Server', 'NameServer1'],
				['Name Server', 'NameServer2'],
				['Name Server', 'NameServer3'],
				['Name Server', 'NameServer4'],
				['Name Server', 'NameServer5'],
				['Name Server', 'NameServer6'],
				['Name Server', 'NameServer7'],
				['Name Server', 'NameServer8'],
				['Name Server', 'NameServer9'],
				['Name Server', 'NameServer10'],
				['Name Server', 'NameServer11'],
				['Name Server', 'NameServer12'],
				['Name Server', 'NameServer13'],
				['DNSSEC', 'DNSSEC'],
				['DS Created 1', 'DSCreated1'],
				['DS Key Tag 1', 'DSKeyTag1'],
				['Algorithm 1', 'Algorithm1'],
				['Digest Type 1', 'DigestType1'],
				['Digest 1', 'Digest1'],
				['DS Maximum Signature Life 1', 'DSMaximumSignatureLife1'],
				['DS Created 2', 'DSCreated2'],
				['DS Key Tag 2', 'DSKeyTag2'],
				['Algorithm 2', 'Algorithm2'],
				['Digest Type 2', 'DigestType2'],
				['Digest 2', 'Digest2'],
				['DS Maximum Signature Life 2', 'DSMaximumSignatureLife2'],

				// This text shown only an empty data-field 'DomainName'
				['NOT FOUND', 'DomainName', FALSE],

				// Footer message anchor (shown only if the data-field 'DomainName' is exists and not empty)
				['{%footer_pir%}', 'DomainName', TRUE],
			],

			'invalid_request' => 
			[
				'{%header_ripn%}',
				'Invalid request.',
			],
		],
        // FileStorage
        [
            'flag'    => 'pir',

            'storage' =>
            [
                'type'    => 'file',
                'storage' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage',

                'queries' => [
                    // Fetch domain information
                    '{_request_}',
                ],
            ],

            'format' =>
            [
                ['CreationDate', 'date("Y-m-d\TH:i:s\Z", strtotime({CreationDate}))'],
                ['UpdatedDate', 'date("Y-m-d\TH:i:s\Z", strtotime({UpdatedDate}))'],
                ['RegistryExpiryDate', 'date("Y-m-d\TH:i:s\Z", strtotime({RegistryExpiryDate}))'],
                ['RegistrarExpiryDate', 'date("Y-m-d\TH:i:s\Z", strtotime({RegistrarExpiryDate}))'],
                ['DomainName', 'strtoupper({DomainName})'],
                ['NameServer'. 'strtoupper({NameServer})'],
                ['LASTUPDATETIME', 'date("Y-m-d\TH:i:s\Z")'],
            ],

            'spacing'    => TRUE,
            'hide_empty' => TRUE,

            'fields' =>
            [
                ['Domain Name', 'DomainName'],
                ['Domain ID', 'DomainID'],
                ['Creation Date', 'CreationDate'],
                ['Updated Date', 'UpdatedDate'],
                ['Registry Expiry Date', 'RegistryExpiryDate'],
                ['Registrar Expiry Date', 'RegistrarExpiryDate'],
                ['Trademark Name', 'TrademarkName'],
                ['Trademark Date', 'TrademarkDate'],
                ['Trademark Country', 'TrademarkCountry'],
                ['Trademark Number', 'TrademarkNumber'],
                ['Sponsoring Registrar', 'SponsoringRegistrar'],
                ['Sponsoring Registrar IANA ID', 'SponsoringRegistrarIANAID'],
                ['WHOIS Server', 'WHOISServer'],
                ['Referral URL', 'ReferralURL'],
                ['Domain Status', 'DomainStatus'],
                ['Registrant ID', 'RegistrantID'],
                ['Registrant Name', 'RegistrantName'],
                ['Registrant Organization', 'RegistrantOrganization'],
                ['Registrant Street', 'RegistrantStreet'],
                ['Registrant City', 'RegistrantCity'],
                ['Registrant State/Province', 'RegistrantStateProvince'],
                ['Registrant Postal Code', 'RegistrantPostalCode'],
                ['Registrant Country', 'RegistrantCountry'],
                ['Registrant Phone', 'RegistrantPhone'],
                ['Registrant Phone Ext', 'RegistrantPhoneExt'],
                ['Registrant Fax', 'RegistrantFax'],
                ['Registrant Fax Ext', 'RegistrantFaxExt'],
                ['Registrant Email', 'RegistrantEmail'],
                ['Admin ID', 'AdminID'],
                ['Admin Name', 'AdminName'],
                ['Admin Organization', 'AdminOrganization'],
                ['Admin Street', 'AdminStreet'],
                ['Admin City', 'AdminCity'],
                ['Admin State/Province', 'AdminStateProvince'],
                ['Admin Postal Code', 'AdminPostalCode'],
                ['Admin Country', 'AdminCountry'],
                ['Admin Phone', 'AdminPhone'],
                ['Admin Phone Ext', 'AdminPhoneExt'],
                ['Admin Fax', 'AdminFax'],
                ['Admin Fax Ext', 'AdminFaxExt'],
                ['Admin Email', 'AdminEmail'],
                ['Tech ID', 'TechID'],
                ['Tech Name', 'TechName'],
                ['Tech Organization', 'TechOrganization'],
                ['Tech Street', 'TechStreet'],
                ['Tech City', 'TechCity'],
                ['Tech State/Province', 'TechStateProvince'],
                ['Tech Postal Code', 'TechPostalCode'],
                ['Tech Country', 'TechCountry'],
                ['Tech Phone', 'TechPhone'],
                ['Tech Phone Ext', 'TechPhoneExt'],
                ['Tech Fax', 'TechFax'],
                ['Tech Fax Ext', 'TechFaxExt'],
                ['Tech Email', 'TechEmail'],
                ['Billing ID', 'BillingID'],
                ['Billing Name', 'BillingName'],
                ['Billing Organization', 'BillingOrganization'],
                ['Billing Street', 'BillingStreet'],
                ['Billing City', 'BillingCity'],
                ['Billing State/Province', 'BillingStateProvince'],
                ['Billing Postal Code', 'BillingPostalCode'],
                ['Billing Country', 'BillingCountry'],
                ['Billing Phone', 'BillingPhone'],
                ['Billing Phone Ext', 'BillingPhoneExt'],
                ['Billing Fax', 'BillingFax'],
                ['Billing Fax Ext', 'BillingFaxExt'],
                ['Billing Email', 'BillingEmail'],
                ['Name Server', 'NameServer'],
                ['DNSSEC', 'DNSSEC'],
                ['DNSSEC DS Data', 'DNSSECDATA'],
                ['DS Maximum Signature Life', 'DSMaximumSignatureLife'],

                // This text shown only an empty data-field 'DomainName'
                ['NOT FOUND', 'DomainName', FALSE],

                // Footer message anchor (shown only if the data-field 'DomainName' is exists and not empty)
                ['{%footer_pir%}', 'DomainName', TRUE],
            ],

            'invalid_request' =>
            [
                '{%header_ripn%}',
                'Invalid request.',
            ],
        ],


		// Help
		[
			'flag'   => 'help',
			'fields' =>
			[
				['{%header_ripn%}'],
				['This is help!'],
			],
		],
	],

];

// end of config
