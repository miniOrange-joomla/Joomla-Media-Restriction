<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_miniorange_mediarestriction
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

class MoMediaRestrictionCustomer
{

    public $email;
    public $phone;
    public $customerKey;
    public $transactionId;

    /*
    ** Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
    ** We need the default value for creating the OTP the first time,
    ** As we don't have the Default keys available before registering the user to our server.
    ** This default values are only required for sending an One Time Passcode at the user provided email address.
    */

    //auth
    private $defaultCustomerKey = "16555";
    private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

    function create_customer()
    {
        if (!MoMediaRestrictionUtility::is_curl_installed()) {
            return json_encode(array("apiKey"=>'CURL_ERROR','token'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        $hostname = MoMediaRestrictionUtility::getHostname();

        $url = $hostname . '/moas/rest/customer/add';
       
        $current_user = Factory::getUser();
        $customer_details = MoMediaRestrictionUtility::getCustomerDetails();

        $this->email = isset($customer_details['email']) ? $customer_details['email'] : '';
        $this->phone = isset($customer_details['admin_phone']) ? $customer_details['admin_phone'] : '';
        $password = isset($customer_details['password']) ? $customer_details['password'] : '';

        $fields = array(
            'companyName' => $_SERVER['SERVER_NAME'],
            'areaOfInterest' => 'Joomla Media Restriction',
            'firstname' => $current_user->name,
            'lastname' => '',
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $password
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    function check_status($code){
		
		$hostname = MoMediaRestrictionUtility::getHostname();
		$url = $hostname . '/moas/api/backupcode/verify';

		$customer_details = MoMediaRestrictionUtility::getCustomerDetails();
    
		$customerKey = $customer_details['customer_key'];
		$apiKey = $customer_details['api_key'];
	
		$fields = '';
		$fields = array(
			'code' => $code ,
			'customerKey' => $customerKey,
			'additionalFields' => array(
				'field1' => URI::root()	
			)
		);
	
		$field_string = json_encode($fields);

        return self::curl_call($url,$field_string);

	}


    function get_customer_key($email, $password)
    {
        if (!MoMediaRestrictionUtility::is_curl_installed()) {
            return json_encode(array("apiKey" => 'CURL_ERROR', 'token' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }

        $hostname = MoMediaRestrictionUtility::getHostname();

        $url = $hostname . "/moas/rest/customer/key";

        $fields = array(
            'email' => $email,
            'password' => $password
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    function submit_contact_us($q_email, $q_phone, $query)
    {
        if (!MoMediaRestrictionUtility::is_curl_installed()) {
            return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        $hostname = MoMediaRestrictionUtility::getHostname();
        $url = $hostname . "/moas/rest/customer/contact-us";
        $current_user = Factory::getUser();
        $subject = "Query for miniOrange Joomla Media Restriction Free  - " . $q_email;
        $query = '[Joomla Media Redirection Free]: ' . $query;
        $fields = array(
            'firstName' => $current_user->username,
            'lastName' => '',
            'company' => $_SERVER['SERVER_NAME'],
            'email' => $q_email,
            'ccEmail' => 'joomlasupport@xecurify.com',
            'phone' => $q_phone,
            'subject' => $subject,
            'query' => $query
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    function check_customer($email)
    {
        if (!MoMediaRestrictionUtility::is_curl_installed()) {
            return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        $hostname = MoMediaRestrictionUtility::getHostname();
        $url = $hostname . "/moas/rest/customer/check-if-exists";

        $fields = array(
            'email' => $email,
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    function send_otp_token($auth_type, $phone)
    {

        $hostname = MoMediaRestrictionUtility::getHostname();
        $url = $hostname . '/moas/api/auth/challenge';
        $customerKey = $this->defaultCustomerKey;
        $apiKey = $this->defaultApiKey;

        $customer_details = MoMediaRestrictionUtility::getCustomerDetails();
        $username= $customer_details['email'];
        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = round(microtime(true) * 1000);

        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue = hash("sha512", $stringToHash);

        $customerKeyHeader = "Customer-Key: " . $customerKey;
        $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;
        if ($auth_type == "EMAIL") {
            $fields = array(
                'customerKey' => $this->defaultCustomerKey,
                'email' => $username,
                'authType' => $auth_type,
                'transactionName' => 'Joomla Media Restriction Free'
            );
        } else {
            $fields = array(
                'customerKey' => $this->defaultCustomerKey,
                'phone' => $phone,
                'authType' => $auth_type,
                'transactionName' => 'Joomla Media Restriction Free'
            );
        }
        $field_string = json_encode($fields);
        return self::curl_call($url,$field_string);
    }

    function validate_otp_token($transactionId, $otpToken)
    {
        $hostname = MoMediaRestrictionUtility::getHostname();
        $url = $hostname . '/moas/api/auth/validate';

        $customerKey = $this->defaultCustomerKey;
        $apiKey = $this->defaultApiKey;

        $customer_details = MoMediaRestrictionUtility::getCustomerDetails();
        $username= $customer_details['email'];
        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = round(microtime(true) * 1000);

        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue = hash("sha512", $stringToHash);

        $customerKeyHeader = "Customer-Key: " . $customerKey;
        $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;

        $fields = '';

        //*check for otp over sms/email
        $fields = array(
            'txId' => $transactionId,
            'token' => $otpToken,
        );

        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string); 
    }

    function submit_feedback_form($email, $query,$cause)
    {
        $hostname = MoMediaRestrictionUtility::getHostname();
        $url = $hostname . '/moas/api/notify/send';
        $customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $dVar=new JConfig();
        $check_email = $dVar->mailfrom;
     
        $phpVersion = phpversion();
        $jCmsVersion = MoMediaRestrictionUtility::get_joomla_version();
        $moPluginVersion = MoMediaRestrictionUtility::get_plugin_version();
        $os_version    = MoMediaRestrictionUtility::get_os_info();
        $details = MoMediaRestrictionUtility::getCustomerDetails();
        $admin_email   = !empty($email)?$email:$check_email;
        $phone  =isset($details ['admin_phone']) ? $details ['admin_phone'] : '';
        $config_details=MoMediaRestrictionUtility::load_db_values('#__miniorange_mediarestriction_settings','loadAssoc');
        $fromEmail    = isset($details ['email']) ? $details ['email'] : '';
        $pluginName='Joomla Media Restriction';
        $saveConfig= $details['save_configuration']==1?'Yes':'No';
        $createdFile= $details['created_file']==1?'Yes':'No';
        $query1 = '['.$pluginName.' | '.$moPluginVersion.' | PHP ' . $phpVersion.' | OS ' . $os_version.'] ';
        if($query=='Save Configuration')
        {
            $ccEmail='nutan.barad@xecurify.com'; 
            $bccEmail='pritee.shinde@xecurify.com';
            $content = '<div >Hello, <br><br><strong>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" ></strong>' . $_SERVER['SERVER_NAME'] . '</a><br><br><strong>Phone Number :<strong>' . $phone . '<br><br><strong>Admin Email :<a href="mailto:' . $admin_email . '" target="_blank">' . $admin_email . '</a></strong><br><br><strong>Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a></strong><br><br><strong>Action: </strong> Save Configuration <br><br><strong>Extension name :</strong>'.$config_details['mo_media_restriction_file_types'].' <br><br> <strong>Possible Cause:</strong> '.$cause .'<br><br><strong> System Information: </strong>' . $query1 . '</div>';
            $subject = "miniOrange Joomla Media Restriction [Free] for Efficiency";
        }
        else if($query=='Create File')
        {
            $ccEmail='nutan.barad@xecurify.com'; 
            $bccEmail='pritee.shinde@xecurify.com';
            $content = '<div >Hello, <br><br><strong>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" ></strong>' . $_SERVER['SERVER_NAME'] . '</a><br><br><strong>Phone Number :<strong>' . $phone . '<br><br><strong>Admin Email :<a href="mailto:' . $admin_email . '" target="_blank">' . $admin_email . '</a></strong><br><br><strong>Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a></strong><br><br><strong>Action: </strong> Create .htaccess file <br><br><strong>Extension name :</strong>'.$config_details['mo_media_restriction_file_types'].' <br><br> <strong>Possible Cause:</strong> '.$cause .'<br><br><strong> System Information: </strong>' . $query1 . '</div>';
            $subject = "miniOrange Joomla Media Restriction [Free] for Efficiency";
           
        }
        else
        {
            $ccEmail='joomlasupport@xecurify.com';
            $bccEmail='joomlasupport@xecurify.com';
            $content = '<div >Hello, <br><br><strong>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" ></strong>' . $_SERVER['SERVER_NAME'] . '</a><br><br><strong>Phone Number :</strong>' . $phone . '<br><br><strong>Admin Email :<a href="mailto:' . $admin_email . '" target="_blank"></strong>' . $admin_email . '</a><br><br><strong>Plugin Deactivated: </strong>' . $query1 . '<br><br><strong>Reason: </strong>' . $cause . '<br><br> <strong>Save Configuration: </strong>' . $saveConfig . '<br><br><strong>Created .htaccess file: </strong>' . $createdFile . '<br><br></div>';   
            $subject = "Feedback for miniOrange Joomla Media Restriction Free";
        }
       

        $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $fromEmail,
                'bccEmail' 		=> $bccEmail,
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> $ccEmail,
                'toName' 		=> $bccEmail,
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
       
        $field_string = json_encode($fields);
        return self::curl_call($url,$field_string); 

    }
    
    function curl_call($url,$field_string)
    {
        $ch = curl_init($url);
        $customer_details = MoMediaRestrictionUtility::getCustomerDetails();
        $customerKey = !empty($customer_details['customer_key'])?$customer_details['customer_key']:$this->defaultCustomerKey;
        $apiKey = !empty($customer_details['api_key'])?$customer_details['api_key']:$this->defaultApiKey;
        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = round(microtime(true) * 1000);
     
        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue = hash("sha512", $stringToHash);

        $customerKeyHeader = "Customer-Key: " . $customerKey;
        $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
            exit();
        }
        curl_close($ch);

	
        return $content;
    }

    
} ?>
