<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_system_miniorangemediarestriction
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Installer\Installer;

include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_mediarestriction' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_mediarestriction_utility.php';

class plgSystemMiniorangemediarestriction extends CMSPlugin
{
    public function onAfterInitialise()
    {
       
        require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_mediarestriction' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_mediarestriction_utility.php';
        $configuration = MoMediaRestrictionUtility::getConfiguration();
        $enable_media_restriction = isset($configuration['enable_media_restriction'])? $configuration['enable_media_restriction']: 0;
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $get = ($input && $input->get) ? $input->get->getArray() : [];
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if (isset($post['mojsp_media_feedback']) || isset($post['mojspfree_skip_feedback'])) {
        
                $radio = $post['deactivate_plugin']??'';
                $data = $post['query_feedback']??'';
                $feedback_email = $post['feedback_email']??'';
                $database_name = '#__miniorange_mediarestriction_customer_details';
                $updatefieldsarray = array(
                    'submited_feedback' => 1,
                );
                MoMediaRestrictionUtility::generic_update_query($database_name, $updatefieldsarray);
                $current_user = Factory::getUser();
                $customerResult = MoMediaRestrictionUtility::getCustomerDetails();
    
                $dVar=new JConfig();
                $check_email = $dVar->mailfrom;
                $admin_email = !empty($details ['admin_email']) ? $details ['admin_email'] :$check_email;
                $admin_email = !empty($admin_email)?$admin_email:self::getSuperUser();
                $admin_phone = $customerResult['admin_phone'];
                $data1 = $radio . ' : ' . $data . '  <br><br><strong>Email:</strong>  ' . $feedback_email;
         
                if(isset($post['mojspfree_skip_feedback']))
                {
                    $data1='Skipped the feedback';
                }
    
                if(file_exists(JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_mediarestriction' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php'))
                {
                    require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_mediarestriction' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';
                    $customer = new MoMediaRestrictionCustomer();
                    $customer->submit_feedback_form($admin_email, $admin_phone, $data1);
                }
              
                require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';
    
                foreach ($post['result'] as $fbkey) {
    
                    $result = MoMediaRestrictionUtility::load_db_values('#__extensions', 'loadColumn','type',  'extension_id', $fbkey);
                    $identifier = $fbkey;
                    $type = 0;
                    foreach ($result as $results) {
                        $type = $results;
                    }
    
                    if ($type) {
                        $cid = 0;
                        try {
                            $installer = null;
                            // Try Joomla 4+ dependency injection container first
                            if (method_exists('Joomla\CMS\Factory', 'getContainer')) {
                                try {
                                    $container = Factory::getContainer();
                                    if ($container && method_exists($container, 'get')) {
                                        $installer = $container->get(Installer::class);
                                    }
                                } catch (Exception $e) {
                                    Factory::getApplication()->getLogger()->warning(
                                        'Installer container resolution failed: ' . $e->getMessage()
                                    );
                                }
                            }
                            
                            // Fallback: manual instantiation for all versions
                            if (!$installer) {
                                $installer = new Installer();
                                if (method_exists($installer, 'setDatabase')) {
                                    $installer->setDatabase(Factory::getDbo());
                                }
                            }
                            
                            $installer->uninstall($type, $identifier, $cid);
                            
                        } catch (Exception $e) {
                            $app = Factory::getApplication();
                            if (method_exists($app, 'enqueueMessage')) {
                                $app->enqueueMessage('Error uninstalling extension media restiction free: ' . $e->getMessage(), 'warning');
                            }
                        }
                    }
                
            }
    
        }

        if(MoMediaRestrictionUtility::isSSOPluginEnable())
        {
            $user = Factory::getUser();
            if($enable_media_restriction)
            {
                if(!empty($user->id))
                {   
                    $cookie_name = "mo_user_logged_in";
                    $cookie_value = "true";
                    setcookie($cookie_name, $cookie_value, time() + (900), "/"); 
                
                } 
         
            }
            
        }
    }

    public static function getSuperUser()
    {
        $db = Factory::getDBO();
        $query = $db->getQuery(true)->select('user_id')->from('#__user_usergroup_map')->where('group_id=' . $db->quote(8));
        $db->setQuery($query);
        $results = $db->loadColumn();
        return  $results[0];
    }


    function onExtensionBeforeUninstall($id)
    {
        $home_path=JPATH_ROOT;
        $htaccess_file = $home_path.'\.htaccess';
        if (file_exists( $htaccess_file ) ) {
            
            $marker="MINIORANGE MEDIA RESTRICTION";
            $start_marker = "# BEGIN {$marker}";
            $end_marker   = "# END {$marker}";
    
            $fp = fopen( $htaccess_file, 'r+' );
            $lines = array();
    
            while ( ! feof( $fp ) ) {
                $lines[] = rtrim( fgets( $fp ), "\r\n" );
            }

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
            $new_file_data = implode(
                "\n",
                array_merge(
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
        }

        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        $tables = Factory::getDbo()->getTableList();
        $result = MoMediaRestrictionUtility::load_db_values('#__extensions', 'loadColumn', 'extension_id', 'element', 'com_miniorange_mediarestriction');
        $tab = 0;
        foreach ($tables as $table) {
            if (strpos($table, "miniorange_mediarestriction_settings") !== FALSE)
                $tab = $table;
        }
        if ($tab === 0)
            return;
        if ($tab) {
            $tpostData = $post;
            $customerResult = MoMediaRestrictionUtility::getCustomerDetails();
            $fid = $customerResult['submited_feedback'];
            $dVar=new JConfig();
            $check_email = $dVar->mailfrom;
            $feedback_email = !empty($customerResult ['admin_email']) ? $customerResult ['admin_email'] :$check_email;

            if (1) {
                if ($fid == 0) {
                    foreach ($result as $results) {
                        if ($results == $id) {?>
                            <link rel="stylesheet" type="text/css" href="<?php echo URI::base();?>/components/com_miniorange_mediarestriction/assets/css/miniorange_mediarestriction.css" />
                            <link rel="stylesheet" type="text/css" href="<?php echo URI::base();?>/components/com_miniorange_mediarestriction/assets/css/miniorange_boot.css" />
                            <div class="form-style-6 mo_boot_offset-4 mo_boot_col-4 mo_boot_mt-2">
                                <h1>Feedback form for Joomla Media Restriction Plugin</h1>
                                <form name="f" method="post" action="" id="mojsp_feedback" class="mo_boot_p-3">
                                    <h3>What Happened? </h3>
                                    <input type="hidden" name="mojsp_media_feedback" value="mojsp_media_feedback"/>
                                    <div>
                                        <p class="mo_boot_ml-3">
                                            <?php
                                            $deactivate_reasons = array(
                                                'Does not have the features I am looking for?',
                                                'Confusing Interface',
                                                'Not able to Configure',
                                                'I found a better plugin',
                                                'It is a temporary deactivation',
                                                'Bugs in the plugin',
                                                'Not working',
                                                'Pricing concern',
                                                'Other Reasons:'
                                            );
                                            foreach ($deactivate_reasons as $deactivate_reasons) { ?>
                                        <div class="radio" class="mo_boot_p-3 mo_boot_ml-2">
                                            <label for="<?php echo $deactivate_reasons; ?>">
                                                <input type="radio" name="deactivate_plugin" value="<?php echo $deactivate_reasons; ?>" required>
                                                <?php echo $deactivate_reasons; ?></label>
                                        </div>

                                        <?php } ?>
                                        <br>

                                        <textarea id="query_feedback" name="query_feedback" rows="4" class="mo-form-control-textarea" cols="50" placeholder="Write your query here"></textarea><br><br><br>
                                        <tr>
                                            <td class="mo_boot_col-2"><strong>Email<span style="color: #ff0000;">*</span>:</strong></td>
                                            <td><input type="email" name="feedback_email" required value="<?php echo $feedback_email; ?>" placeholder="Enter email to contact." class="mo_boot_col-10" /></td>
                                        </tr>

                                        <?php
                                        foreach ($tpostData['cid'] as $key) { ?>
                                            <input type="hidden" name="result[]" value=<?php echo $key ?>>
                                        <?php } ?>
                                        <br><br>
                                        <div class="mojsp_modal-footer mo_boot_text-center">
                                            <input type="submit" name="miniorange_feedback_submit" class="mo_boot_btn mo_media_restrictionbtn mo_boot_col-12 mo_boot_p-2" value="Submit"/>
                                        </div>
                                    </div>
                                </form>
                                <form name="f" method="post" action="" id="mojspfree_feedback_form_close">
                                    <input type="hidden" name="mojspfree_skip_feedback" value="mojspfree_skip_feedback"/>
                                    <div class="mo_boot_text-center mo_boot_p-3">
                                        <button class="mo_boot_btn mo_media_restrictionbtn mo_boot_col-12 mo_boot_p-2" onClick="skipMediaRestrictionForm()">Skip Feedback</button>
                                    </div>
                                    <?php
                                        foreach ($tpostData['cid'] as $key) { ?>
                                            <input type="hidden" name="result[]" value=<?php echo $key ?>>
                                        <?php }
                                    ?>
                            </div>
                            <script src="https://code.jquery.com/jquery-3.6.3.js"></script>
                            <script>
                                jQuery('input:radio[name="deactivate_plugin"]').click(function () {
                                    var reason = jQuery(this).val();
                                    jQuery('#query_feedback').removeAttr('required')
                                    if (reason === 'Confusing Interface') {
                                        jQuery('#query_feedback').attr("placeholder",'Can you please describe the issue in detail?');
                                    } else if (reason === 'Does not have the features I am looking for?') {
                                        jQuery('#query_feedback').attr("placeholder", 'Let us know what feature are you looking for');
                                    } else if (reason === 'I found a better plugin'){
                                        jQuery('#query_feedback').attr("placeholder", 'Can you please name that plugin which one you feel better?');
                                    }else if (reason === 'Not working'){
                                        jQuery('#query_feedback').attr("placeholder", 'Can you please let us know which plugin part you find not working?');
                                    } else if (reason === 'Other Reasons:' || reason === 'It is a temporary deactivation' ) {
                                        jQuery('#query_feedback').attr("placeholder", 'Can you let us know the reason for deactivation?');
                                        jQuery('#query_feedback').prop('required', true);
                                    }else if (reason === 'Pricing concern'){
                                        jQuery('#query_feedback').attr("placeholder", 'Need help with pricing? Share your concern.');
                                    }else if (reason === 'Not able to Configure') {
                                        jQuery('#query_feedback').attr("placeholder", 'Not able to Configure? let us know so that we can improve the interface.');
                                    }
                                });

                                function skipMediaRestrictionForm(){
                                    jQuery('#mojspfree_feedback_form_close').submit();
                                }

                            </script>
                            <?php
                            exit;
                        }
                    }
                }
            }
        }
    }
}
