<?php
/** 
 * @package     Joomla.Plugin
 * @subpackage  plg_user_miniorangemediarestriction
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
// no direct access
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
defined('_JEXEC') or die('Restricted access');

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

jimport('joomla.user.helper');

class plgUserMiniorangeusermediarestriction extends CMSPlugin
{

    public function onUserAfterLogin($options)
    {
        $user = Factory::getUser();
        require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_mediarestriction' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_mediarestriction_utility.php';
        $configuration = MoMediaRestrictionUtility::getConfiguration();
        $enable_media_restriction = isset($configuration['enable_media_restriction'])? $configuration['enable_media_restriction']: 0;
        if($enable_media_restriction )
        {
            $cookie_name = "mo_user_logged_in";
            $cookie_value = "true";
            setcookie($cookie_name, $cookie_value, time() + (900), "/");

        }
    }


    public function onUserLogout($user, $options = array())
    {
       if(isset($_COOKIE['mo_user_logged_in']))
       { 
         setcookie('mo_user_logged_in','-1',time() - 100, '/');
         unset($_COOKIE['mo_user_logged_in']);
       }
    }

}
 
 