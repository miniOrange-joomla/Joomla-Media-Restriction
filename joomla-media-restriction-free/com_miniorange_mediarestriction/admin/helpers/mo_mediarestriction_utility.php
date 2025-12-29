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
 * This class contains all the utility functions
 **/
defined('_JEXEC') or die('Restricted access');
?>

<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

class MoMediaRestrictionUtility
{
    public static function is_customer_registered()
    {
        $result = self::getCustomerDetails();

        $email 			= isset($result['email']) ? $result['email'] : '';
        $customerKey 	= isset($result['customer_key']) ? $result['customer_key'] : 0;
        $status = isset($result['registration_status']) ? $result['registration_status'] : '';

        if($email && $status == 'SUCCESS'){
            return 1;
        } else{
            return 0;
        }
    }

  

    public static function get_plugin_version()
    {
        $db = Factory::getDbo();
        $dbQuery = $db->getQuery(true)
            ->select('manifest_cache')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_mediarestriction'));
        $db->setQuery($dbQuery);
        $manifest = json_decode($db->loadResult());
        return($manifest->version);
    }

    public static function isCurrentGroupExist($mapping_value, $role_based_redirect_key_value)
    {
        if (in_array($mapping_value, $role_based_redirect_key_value))
        {
            return 'ALLOW';
        }
        else
        {
            return 'NOT_ALLOWED';
        }
    }

	
	public static function encrypt($str){
		$str = stripcslashes($str);

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('customer_token');
		$query->from($db->quoteName('#__miniorange_mediarestriction_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$key = $db->loadResult();
		
		return base64_encode(openssl_encrypt($str, 'aes-128-ecb', $key, OPENSSL_RAW_DATA));
	}
    public static function getUserId($username)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = ' . $db->quote($username));
        $db->setQuery($query, 0, 1);

        try
        {
            $result = $db->loadResult();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }

    public static function getAllGroups()
    {
        $all_groups = self::loadGroups();

        $groups = array();
        foreach ($all_groups as $key => $value) {
            array_push($groups, $value['title']);
        }
        return $groups;
    }

    public static function getConfiguration()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_mediarestriction_settings'));
        $db->setQuery($query);

        try
        {
            $result = $db->loadAssoc();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;

    }

    public static function getUserGroupID($groupID)
    {
        $group_id = '';
        foreach ($groupID as $groups)
        {
            $group_id = $groups;
        }
        return $group_id;
    }

    public static function get_role_based_redirect_values($role_mapping_key_value, $currentUserGroup)
    {
        $groups = array();
        foreach ($role_mapping_key_value as $mapping_key => $mapping_value){
            if (!empty($mapping_key)) {
                if($mapping_key == $currentUserGroup){
                    $groups = $mapping_value;
                }
            }
        }
        return $groups;
    }

    public static function check($val)
    {
        if (empty($val))
            return "";
        else
            return self::decrypt($val);
    }

    public static function decrypt($value)
    {
        if (!self::isExtensionInstalled('openssl')) {
            return;
        }
        $customer_token= self::getCustomerToken();

        $string = rtrim(openssl_decrypt(base64_decode($value), 'aes-128-ecb', $customer_token, OPENSSL_RAW_DATA), "\0");
        return trim($string, "\0..\32");
    }

    public static function isExtensionInstalled($name)
    {
        if (in_array($name, get_loaded_extensions())) {
            return true;
        } else {
            return false;
        }
    }

    public static function isBlank( $value )
    {
        if( ! isset( $value ) || empty( $value ) ) return TRUE;
        return FALSE;
    }

    public static function getCustomerDetails()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_mediarestriction_customer_details'));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    public static function check_empty_or_null($value)
    {
        if (!isset($value) || empty($value)) {
            return true;
        }
        return false;
    }

    public static function is_curl_installed()
    {
        if (in_array('curl', get_loaded_extensions())) {
            return 1;
        } else
            return 0;
    }

    public static function getCustomerToken()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('customer_token');
        $query->from($db->quoteName('#__miniorange_mediarestriction_customer_details'));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);

        try
        {
            $result = $db->loadResult();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }

    public static function is_extension_installed($name)
    {
        if (in_array($name, get_loaded_extensions())) {
            return true;
        } else {
            return false;
        }
    }

    public static function getHostname()
    {
        return 'https://login.xecurify.com';
    }

    public static function loadGroups(){
        $db = Factory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from("#__usergroups")
        );

        try
        {
            $result = $db->loadRowList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }


    public static function loadUserGroups($user_id){
        $db = Factory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('group_id')
            ->from("#__user_usergroup_map")
            ->where($db->quoteName('user_id'). ' = ' . $db->quote($user_id))
        );
        try
        {
            $result = $db->loadAssocList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
        return $result;
    }

    public static function getGroupNameByID($group_id)
    {


        $db = Factory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('title')
            ->from("#__usergroups")
            ->where($db->quoteName('id'). ' = ' . $db->quote($group_id))
        );

        try
        {
            $result = $db->loadAssoc();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');
            return null;
        }
        return $result['title'];
    }

    public static function loadAllGroups(){
        $db = Factory::getDbo();
        $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from("#__usergroups")
        );
        try
        {
            $result = $db->loadAssocList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');
            return null;
        }
        return $result;
    }

    public static function getRules()
    {
        $configuration = MoMediaRestrictionUtility::getConfiguration();

        $mo_media_restriction_file_types = isset($configuration['mo_media_restriction_file_types'])? $configuration['mo_media_restriction_file_types']: '';
        $mo_media_restriction_file_types=  explode(',',$mo_media_restriction_file_types);     
        $htacces_file_types='';
        $files_allowed=array('png', 'jpg', 'gif', 'pdf', 'doc');
        if(!empty($mo_media_restriction_file_types))
        {
            foreach($mo_media_restriction_file_types as $file_types)
            {
               
                if(!empty($file_types) && in_array($file_types,$files_allowed))   
                {
                    $htacces_file_types.=$file_types.'|';
                }
            }
 
            $htacces_file_types=rtrim($htacces_file_types,'|');
        }else
        {
            $htacces_file_types='';
        }
        
        $insertion='';
        $insertion.=' RewriteCond %{REQUEST_FILENAME} ^.*('.$htacces_file_types .')$ +';
        $insertion.='RewriteCond %{REQUEST_URI} images +';    
        $insertion.=' RewriteCond %{HTTP_COOKIE} !^.*mo_user_logged_in.*$ [NC] +';
        $insertion.='RewriteRule . - [R=403,L]';
    
        
        $insertion = explode("+", $insertion);
        return $insertion;

    }

    public static function insertRules($htaccess_file,$insertion)
    {
        if ( ! file_exists( $htaccess_file ) ) {
            
            return false;

        } else if ( ! is_writable( $htaccess_file ) ) {
            return false;
        }
        $marker="MINIORANGE MEDIA RESTRICTION";
        $start_marker = "# BEGIN {$marker}";
	    $end_marker   = "# END {$marker}";

        $fp = fopen( $htaccess_file, 'r+' );
        $lines = array();

        while ( ! feof( $fp ) ) {
            $lines[] = rtrim( fgets( $fp ), "\r\n" );
        }

        	// Split out the existing file into the preceding lines, and those that appear after the marker.
        $pre_lines        = array();
        $post_lines       = array();
        $existing_lines   = array();
        $found_marker     = false;
        $found_end_marker = false;
        
        foreach ( $lines as $line ) {
            if ( ! $found_marker && false !== strpos( $line, $start_marker ) ) {
                $found_marker = true;
                continue;
            } elseif ( ! $found_end_marker && false !== strpos( $line, $end_marker ) ) {
                $found_end_marker = true;
                continue;
            }

            if ( ! $found_marker ) {
                $pre_lines[] = $line;
            } elseif ( $found_marker && $found_end_marker ) {
                $post_lines[] = $line;
            } else {
                $existing_lines[] = $line;
            }
        }

        // Check to see if there was a change.
        if ( $existing_lines === $insertion ) {
            flock( $fp, LOCK_UN );
            fclose( $fp );

            return true;
        }

        // Generate the new file data.
        $new_file_data = implode(
            "\n",
            array_merge(
               
                array( $start_marker ),
                $insertion,
                array( $end_marker ),
                $pre_lines,
                $post_lines
            )
        );

        // Write to the start of the file, and truncate it to that length.
        fseek( $fp, 0 );
        $bytes = fwrite( $fp, $new_file_data );

        if ( $bytes ) {
            ftruncate( $fp, ftell( $fp ) );
        }

        fflush( $fp );
        flock( $fp, LOCK_UN );
        fclose( $fp );

        return (bool) $bytes;
        
    }

    public static function isSSOPluginEnable()
    {
        $arr = array('samlredirect','miniorangeoauth');
        $result=array();
        foreach ($arr as $key)
        {
            array_push( $result,self::checkExtensionEnabled( $key)) ;
        }
        return $result[0] || $result[1] ;
    }

    public static function checkExtensionEnabled($plugin)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('enabled');
        $query->from('#__extensions');
        $query->where($db->quoteName('element') . " = " . $db->quote($plugin));
        $query->where($db->quoteName('type') . " = " . $db->quote('plugin'));
        $db->setQuery($query);
        return($db->loadAssoc());
    }

    public static function show_popup()
    {
        ?>
        <head>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
            <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
            <script src="<?php echo URI::base() .'components/com_miniorange_mediarestriction/assets/js/utility.js'?>"></script>
            <link rel="stylesheet" href="<?php echo URI::base() . 'components/com_miniorange_mediarestriction/assets/css/miniorange_mediarestriction.css'?>" type="text/css">
            <link rel="stylesheet" href="<?php echo URI::base() . 'components/com_miniorange_mediarestriction/assets/css/bootstrap-select-min.css'?>" type="text/css">
        </head>
        <style>
        
        </style>
            <div id="my_TC_Modal" class="TC_modal">
               <div class="TC_modal-content">
                    <div class="mt-3">
                        <div class="col-sm-12">
                            <p style="TC_modal_close "><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_POPUP_TEXT1');?><p>
                            <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_POPUP_TEXT2');?></p>
                            <div class="text-center">
                                <button onclick="create_file()" class="btn btn-success"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_POPUP_YES');?></button>
                                <button onclick="close_popup()" class="btn btn-danger"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_POPUP_NO');?></button>
                            </div>
                        </div>
                    </div>
                    <form method="post" id="close_popup" name="f" action="<?php echo Route::_('index.php?option=com_miniorange_mediarestriction&view=accountsetup&task=accountsetup.cancelPopup'); ?>" > 
                    </form>   
                    <form method="post" id="create_file" name="f" action="<?php echo Route::_('index.php?option=com_miniorange_mediarestriction&view=accountsetup&task=accountsetup.createHtaccess'); ?>" > 
                    </form>
                </div>
           </div>
        <?php
    }

    public static function generic_update_query($database_name, $updatefieldsarray){

        $db = Factory::getDbo();

        $query = $db->getQuery(true);
        foreach ($updatefieldsarray as $key => $value)
        {
            $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        }
        $query->update($db->quoteName($database_name))->set($database_fileds)->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $db->execute();
    }

    public static function getServerType()
    {
        $server = $_SERVER['SERVER_SOFTWARE'] ?? '';

        if (stripos($server, 'Apache') !== false) {
            return 'Apache';
        }

        if (stripos($server, 'nginx') !== false) {
            return 'Nginx';
        }

        if (stripos($server, 'LiteSpeed') !== false) {
            return 'LiteSpeed';
        }

        if (stripos($server, 'IIS') !== false) {
            return 'IIS';
        }

        return 'Unknown';
    }

    public static function get_os_info()
    {

        if (isset($_SERVER)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            global $HTTP_SERVER_VARS;
            if (isset($HTTP_SERVER_VARS)) {
                $user_agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
            } else {
                global $HTTP_USER_AGENT;
                $user_agent = $HTTP_USER_AGENT;
            }
        }

        $os_array = [
            'windows nt 10' => 'Windows 10',
            'windows nt 6.3' => 'Windows 8.1',
            'windows nt 6.2' => 'Windows 8',
            'windows nt 6.1|windows nt 7.0' => 'Windows 7',
            'windows nt 6.0' => 'Windows Vista',
            'windows nt 5.2' => 'Windows Server 2003/XP x64',
            'windows nt 5.1' => 'Windows XP',
            'windows xp' => 'Windows XP',
            'windows nt 5.0|windows nt5.1|windows 2000' => 'Windows 2000',
            'windows me' => 'Windows ME',
            'windows nt 4.0|winnt4.0' => 'Windows NT',
            'windows ce' => 'Windows CE',
            'windows 98|win98' => 'Windows 98',
            'windows 95|win95' => 'Windows 95',
            'win16' => 'Windows 3.11',
            'mac os x 10.1[^0-9]' => 'Mac OS X Puma',
            'macintosh|mac os x' => 'Mac OS X',
            'mac_powerpc' => 'Mac OS 9',
            'linux' => 'Linux',
            'ubuntu' => 'Linux - Ubuntu',
            'iphone' => 'iPhone',
            'ipod' => 'iPod',
            'ipad' => 'iPad',
            'android' => 'Android',
            'blackberry' => 'BlackBerry',
            'webos' => 'Mobile',

            '(media center pc).([0-9]{1,2}\.[0-9]{1,2})' => 'Windows Media Center',
            '(win)([0-9]{1,2}\.[0-9x]{1,2})' => 'Windows',
            '(win)([0-9]{2})' => 'Windows',
            '(windows)([0-9x]{2})' => 'Windows',


            'Win 9x 4.90' => 'Windows ME',
            '(windows)([0-9]{1,2}\.[0-9]{1,2})' => 'Windows',
            'win32' => 'Windows',
            '(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})' => 'Java',
            '(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}' => 'Solaris',
            'dos x86' => 'DOS',
            'Mac OS X' => 'Mac OS X',
            'Mac_PowerPC' => 'Macintosh PowerPC',
            '(mac|Macintosh)' => 'Mac OS',
            '(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'SunOS',
            '(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'BeOS',
            '(risc os)([0-9]{1,2}\.[0-9]{1,2})' => 'RISC OS',
            'unix' => 'Unix',
            'os/2' => 'OS/2',
            'freebsd' => 'FreeBSD',
            'openbsd' => 'OpenBSD',
            'netbsd' => 'NetBSD',
            'irix' => 'IRIX',
            'plan9' => 'Plan9',
            'osf' => 'OSF',
            'aix' => 'AIX',
            'GNU Hurd' => 'GNU Hurd',
            '(fedora)' => 'Linux - Fedora',
            '(kubuntu)' => 'Linux - Kubuntu',
            '(ubuntu)' => 'Linux - Ubuntu',
            '(debian)' => 'Linux - Debian',
            '(CentOS)' => 'Linux - CentOS',
            '(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - Mandriva',
            '(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - SUSE',
            '(Dropline)' => 'Linux - Slackware (Dropline GNOME)',
            '(ASPLinux)' => 'Linux - ASPLinux',
            '(Red Hat)' => 'Linux - Red Hat',
            '(linux)' => 'Linux',
            '(amigaos)([0-9]{1,2}\.[0-9]{1,2})' => 'AmigaOS',
            'amiga-aweb' => 'AmigaOS',
            'amiga' => 'Amiga',
            'AvantGo' => 'PalmOS',
            '[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})' => 'Linux',
            '(webtv)/([0-9]{1,2}\.[0-9]{1,2})' => 'WebTV',
            'Dreamcast' => 'Dreamcast OS',
            'GetRight' => 'Windows',
            'go!zilla' => 'Windows',
            'gozilla' => 'Windows',
            'gulliver' => 'Windows',
            'ia archiver' => 'Windows',
            'NetPositive' => 'Windows',
            'mass downloader' => 'Windows',
            'microsoft' => 'Windows',
            'offline explorer' => 'Windows',
            'teleport' => 'Windows',
            'web downloader' => 'Windows',
            'webcapture' => 'Windows',
            'webcollage' => 'Windows',
            'webcopier' => 'Windows',
            'webstripper' => 'Windows',
            'webzip' => 'Windows',
            'wget' => 'Windows',
            'Java' => 'Unknown',
            'flashget' => 'Windows',
            'MS FrontPage' => 'Windows',
            '(msproxy)/([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
            '(msie)([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
            'libwww-perl' => 'Unix',
            'UP.Browser' => 'Windows CE',
            'NetAnts' => 'Windows',
        ];

        $arch_regex = '/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
        $arch = preg_match($arch_regex, $user_agent) ? '64' : '32';

        foreach ($os_array as $regex => $value) {
            if (preg_match('{\b(' . $regex . ')\b}i', $user_agent)) {
                return $value . ' x' . $arch;
            }
        }

        return 'Unknown';
    }

    public static function get_joomla_version()
    {
        $jVersion = new Version();
        return($jVersion->getShortVersion());
    }



    public static function load_db_values($table, $load_by, $col_name = '*', $id_name = 'id', $id_value = 1){
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($col_name);

        $query->from($db->quoteName($table));
        if(is_numeric($id_value)){
            $query->where($db->quoteName($id_name)." = $id_value");

        }else{
            $query->where($db->quoteName($id_name) . " = " . $db->quote($id_value));
        }
        $db->setQuery($query);

        if($load_by == 'loadAssoc'){
            $default_config = $db->loadAssoc();
        }
        elseif ($load_by == 'loadResult'){
            $default_config = $db->loadResult();
        }
        elseif($load_by == 'loadColumn'){
            $default_config = $db->loadColumn();
        }
        return $default_config;
    }

    public function load_database_values($table)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        $default_config = $db->loadAssoc();
        return $default_config;
    }

    public static function send_efficiency_mail($fromEmail, $content)
    {
        $url = 'https://login.xecurify.com/moas/api/notify/send';
        $customer_details = (new MoMediaRestrictionUtility)->load_database_values('#__miniorange_mediarestriction_customer_details');
        $customerKey = !empty($customer_details['customer_key']) ? $customer_details['customer_key'] : '16555';
        $apiKey = !empty($customer_details['api_key']) ? $customer_details['api_key'] : 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
        $currentTimeInMillis = round(microtime(true) * 1000);
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $headers = [
            "Content-Type: application/json",
            "Customer-Key: $customerKey",
            "Timestamp: $currentTimeInMillis",
            "Authorization: $hashValue"
        ];
        $fields = [
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => [
                'customerKey' => $customerKey,
                'fromEmail' => $fromEmail,
                'fromName' => 'miniOrange',
                'toEmail' => 'nutan.barad@xecurify.com',
                'bccEmail' => 'pritee.shinde@xecurify.com',
                'subject' => 'Installation of Joomla Media Restriction [Free]',
                'content' => '<div>' . $content . '</div>',
            ],
        ];
        $field_string = json_encode($fields);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMsg = 'SendMail CURL Error: ' . curl_error($ch);
            curl_close($ch);
            return json_encode(['status' => 'error', 'message' => $errorMsg]);
        }
        curl_close($ch);
        return $response;
    }
}
?>