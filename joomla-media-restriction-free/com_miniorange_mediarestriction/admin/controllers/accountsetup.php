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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_mediarestriction' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_mediarestriction_utility.php';

class MiniorangeMediaRestrictionControllerAccountsetup extends FormController
{
    function __construct()
    {
        $this->view_list = 'accountsetup';
        parent::__construct();
    }

    function contactUs()
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];

        if (count($post) == 0) {
            $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=overview');
            return;
        }

        $query_email = isset($post['query_email']) ? $post['query_email'] : '';
        $query = isset($post['query_support']) ? $post['query_support'] : '';
        $phone = isset($post['query_phone']) ? $post['query_phone'] : '';
        $support_type = isset($post['support_type']) ? $post['support_type'] : 'general_query';
        $call_date     = isset($post['call_date'])     ? $post['call_date']     : '';
        $call_time     = isset($post['call_time'])     ? $post['call_time']     : '';
        $call_timezone = isset($post['call_timezone']) ? trim($post['call_timezone']) : '';
        $country_code = isset($post['country_code']) ? $post['country_code'] : '';
        $client_timezone = isset($post['client_timezone']) ? $post['client_timezone'] : '';
        $client_timezone_offset = isset($post['client_timezone_offset']) ? $post['client_timezone_offset'] : '';

        if (MoMediaRestrictionUtility::check_empty_or_null($query_email) || MoMediaRestrictionUtility::check_empty_or_null($query)) {
            $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=overview',  Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_SUBMIT_EMAIL'), 'error');
            return;
        } else {

            if ($support_type == 'setup_call') {
                if (MoMediaRestrictionUtility::check_empty_or_null($call_date)) {
                    $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_MEDIARESTRICTION_MSG_SELECT_DATE'), 'error');
                    return;
                }
                if (MoMediaRestrictionUtility::check_empty_or_null($call_time)) {
                    $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_MEDIARESTRICTION_MSG_SELECT_CALL'), 'error');
                    return;
                }
            }

            if ($support_type == 'setup_call') {
                $datetime_string    = $call_date . ' ' . $call_time;
                $formatted_datetime = date('F j, Y \a\t g:i A', strtotime($datetime_string));
                $query .= "\n\n--- Support Call Request Details ---";
                $query .= "\nDate & Time: " . $formatted_datetime;
                $query .= "\nTimezone: " . $call_timezone . ' (' . $timezone . ')';
            }

            $dial = preg_replace('/\D+/', '', (string) $country_code);
            $phone_raw = trim((string) $phone);

            if (!empty($dial) && $phone_raw !== '' && strpos($phone_raw, '+') !== 0) {
                $local = preg_replace('/\D+/', '', $phone_raw);
                $phone = '+' . $dial . ($local !== '' ? (' ' . $local) : '');
            } elseif (!empty($dial) && $phone_raw === '') {
                $phone = '+' . $dial;
            }

        // Timezone details (priority: user-selected call tz -> browser tz -> Joomla user tz -> global config offset)
        $user = Factory::getUser();
        $config = Factory::getConfig();

        $tzName = $call_timezone !== '' ? $call_timezone : trim((string) $client_timezone);
        if ($tzName === '') {
            $tzName = (string) $user->getParam('timezone');
        }
        if (trim((string) $tzName) === '') {
            $tzName = (string) $config->get('offset');
        }

        $timezone = MoMediaRestrictionUtility::format_timezone_with_utc_offset($tzName, $client_timezone_offset);

            $contact_us = new MoMediaRestrictionCustomer();
            $response = $contact_us->submit_contact_us($query_email, $phone, $query, $timezone);

            // Handle both JSON and plain-string responses from the contact-us API
            $submited = json_decode($response, true);
            $isJson = (json_last_error() == JSON_ERROR_NONE);

            $isSuccess = false;
            $errorMessage = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_QUERY_SUBMIT');

            if ($isJson) {
                if (is_string($submited)) {
                    $isSuccess = (stripos($submited, 'submitted') !== false);
                } elseif (is_array($submited)) {
                    $status = isset($submited['status']) ? strtoupper((string) $submited['status']) : '';
                    $message = isset($submited['message']) ? (string) $submited['message'] : (isset($submited['statusMessage']) ? (string) $submited['statusMessage'] : '');
                    if ($status === 'ERROR') {
                        $errorMessage = $message !== '' ? $message : $errorMessage;
                        $isSuccess = false;
                    } else {
                        $isSuccess = ($status === 'SUCCESS') || (stripos($message, 'submitted') !== false);
                        if (!$isSuccess && $message !== '') {
                            $errorMessage = $message;
                        }
                    }
                }
            } else {
                // Plain text fallback
                $isSuccess = is_string($response) && (stripos($response, 'submitted') !== false);
                if (!$isSuccess && is_string($response) && trim($response) !== '') {
                    $errorMessage = trim($response);
                }
            }

            if ($isSuccess) {
                $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_THANK_MSG'));
            } else {
                $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=support', $errorMessage, 'error');
            }
            return;
        }
    }

    function saveFileRestriction()
    {
        
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];

        if (count($post) == 0) {
            $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings');
            return;
        }

        $enable_media_restriction =  isset($post['mo_enable_media_restriction']) ? $post['mo_enable_media_restriction'] : 0;
        $mo_media_restriction_file_types =  array_key_exists('mo_media_restriction_file_types', $post) ? json_decode($post['mo_media_restriction_file_types']) : array();  
        $mo_file_types='';
        $files_types_allowed=array('png', 'jpg', 'gif', 'pdf', 'doc');
        if(!empty( $mo_media_restriction_file_types))
        {
          foreach($mo_media_restriction_file_types as $files)
          {
            if(in_array($files->value,$files_types_allowed))
            {
                $mo_file_types.=$files->value.',';
            }
            
          }   
        }

        if (empty(trim($mo_file_types, ','))) {
            $this->setRedirect(
                'index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings',
                Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_FILE_TYPES_REQUIRED'),
                'error'
            );
            return;
        }

        //update
        $db = MoMediaRestrictionUtility::moGetDatabase();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('enable_media_restriction') . ' = ' . $db->quote($enable_media_restriction),
            $db->quoteName('mo_media_restriction_file_types') . ' = ' . $db->quote($mo_file_types),
        );
        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = 1'
        );

        $query->update($db->quoteName('#__miniorange_mediarestriction_settings'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();

        $database_name = '#__miniorange_mediarestriction_customer_details';
        $updatefieldsarray = array(
            'save_configuration' => 1,
        );
        MoMediaRestrictionUtility::generic_update_query($database_name, $updatefieldsarray);

        $customer = new MoMediaRestrictionCustomer();
        $customer->submit_feedback_form('','Save Configuration','Successfully saved configuration.');

        $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_SAVED_SHOW_RULES_HTACCESS');
        $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings&show_rules=1', $message,'success');
    }


    function writeHtaccess()
    {
        $home_path    = JPATH_ROOT;
        $htaccess_file = $home_path . '/.htaccess';
        $backup_file   = $home_path . '/.miniorange-htaccess-backup';
        $insertion     = MoMediaRestrictionUtility::getRules();

        if (file_exists($htaccess_file) && is_writable($home_path) && is_writable($htaccess_file)) {
            copy($htaccess_file, $backup_file);
            $result = MoMediaRestrictionUtility::insertRules($htaccess_file, $insertion);

            if ($result === false) {
                $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_ACCESS_FILE');
                $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings', $message, 'error');
                return;
            }

            $database_name    = '#__miniorange_mediarestriction_customer_details';
            $updatefieldsarray = array('created_file' => 1);
            MoMediaRestrictionUtility::generic_update_query($database_name, $updatefieldsarray);
            $customer = new MoMediaRestrictionCustomer();
            $customer->submit_feedback_form('', 'Create File', 'File updated correctly');
            $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_HTACCESS_FILE');
            $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings', $message);
        } else {
            if (is_writable($home_path)) {
                if (file_exists($htaccess_file)) {
                    copy($htaccess_file, $backup_file);
                } else {
                    MoMediaRestrictionUtility::show_popup();
                }
            } else {
                $database_name    = '#__miniorange_mediarestriction_customer_details';
                $updatefieldsarray = array('created_file' => 1);
                MoMediaRestrictionUtility::generic_update_query($database_name, $updatefieldsarray);
                $customer = new MoMediaRestrictionCustomer();
                $customer->submit_feedback_form('', 'Create File', 'Do not have access to file.');
                $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_ACCESS_FILE');
                $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings', $message, 'error');
            }
        }
    }

    function createHtaccess()
    {
        $home_path         = JPATH_ROOT;
        $htaccess_file     = $home_path . '/.htaccess';
        $htaccess_txt_file = $home_path . '/htaccess.txt';
        $backup_file       = $home_path . '/.miniorange-htaccess-backup';

        $insertion    = MoMediaRestrictionUtility::getRules();
        $start_marker = '# BEGIN MINIORANGE MEDIA RESTRICTION';
        $end_marker   = '# END MINIORANGE MEDIA RESTRICTION';

        if (!file_exists($htaccess_txt_file)) {
            $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_ACCESS_FILE');
            $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings', $message, 'error');
            return;
        }

        $fp = fopen($htaccess_txt_file, 'r');
        if ($fp === false) {
            $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_ACCESS_FILE');
            $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings', $message, 'error');
            return;
        }

        $lines = array();
        while (!feof($fp)) {
            $lines[] = rtrim(fgets($fp), "\r\n");
        }
        fclose($fp);

        $insertion_rule = implode("\n", array_merge(
            array($start_marker),
            $insertion,
            array($end_marker),
            $lines
        ));

        if (class_exists('Joomla\CMS\Filesystem\File')) {
            $result = File::write($htaccess_file, $insertion_rule);
        } elseif (class_exists('Joomla\Filesystem\File')) {
            $result = \Joomla\Filesystem\File::write($htaccess_file, $insertion_rule);
        } else {
            $result = (file_put_contents($htaccess_file, $insertion_rule) !== false);
        }

        if (!$result) {
            $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_ACCESS_FILE');
            $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings', $message, 'error');
            return;
        }

        copy($htaccess_txt_file, $backup_file);
        $customer = new MoMediaRestrictionCustomer();
        $customer->submit_feedback_form('', 'Create File', 'File created successfully');
        $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_FILE_CREATED');
        $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings', $message);
    }

    function cancelPopup()
    {
        $message = Text::_('COM_MINIORANGE_MEDIARESTRICTION_CON_SUBMIT_MANUAL_FILE_CREATE');
        $this->setRedirect('index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_settings',$message, 'error');
    }

}