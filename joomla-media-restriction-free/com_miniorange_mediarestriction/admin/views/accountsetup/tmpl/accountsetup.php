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
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_miniorange_mediarestriction/assets/js/bootstrap.js');
$document->addScript(Uri::base() . 'components/com_miniorange_mediarestriction/assets/js/countries.js');
$document->addScript(Uri::base() . 'components/com_miniorange_mediarestriction/assets/js/utility.js');
$document->addScript('https://cdn.jsdelivr.net/npm/@yaireo/tagify');
$document->addScript('https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_mediarestriction/assets/css/miniorange_mediarestriction.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_mediarestriction/assets/css/miniorange_boot.css');
$document->addStyleSheet('https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
HTMLHelper::_('jquery.framework');

$jsonFile = Uri::base() . 'components/com_miniorange_mediarestriction/assets/json/tabs.json';

function getJsonData($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // Disable SSL verification (ONLY for local/dev environments)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        return null;
    }

    curl_close($ch);
    return $response;
}

$tabsJson = getJsonData($jsonFile);
$tabs = json_decode($tabsJson, true);

if (MoMediaRestrictionUtility::is_curl_installed() == 0){ ?>
    <p class="mo_mediarestriction_warning_text">(<?php echo Text::_('COM_MINIORANGE_MEDIA_PLUGIN_WARNING');?>: <a href="http://php.net/manual/en/curl.installation.php" target="_blank"><?php echo Text::_('COM_MINIORANGE_MEDIA_PLUGIN_WARNING_W1');?></a><?php echo Text::_('COM_MINIORANGE_MEDIA_PLUGIN_WARNING_W2');?>)</p>
    <?php
}

$tab_name = 'overview';
$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$get = ($input && $input->get) ? $input->get->getArray() : [];
$active_tab = ($input && $input->get) ? $input->get->getArray() : [];

if (isset($active_tab['tab-panel']) && !empty($active_tab['tab-panel'])) {
    $tab_name = $active_tab['tab-panel'];
}
?>

<div class="mo_boot_row mo_boot_p-3">
    <div class="mo_boot_col-sm-12">
        <a class="mo_boot_btn mo_boot_px-4 mo_boot_py-1 mo_heading_export_btn" target="_blank" href="https://www.miniorange.com/businessfreetrial"><i class="fa fa-envelope mo_boot_mx-1"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_MEDIRARESTRICTION_TRIAL_REQUEST');?></a>
        <a class="mo_boot_btn mo_boot_px-4 mo_boot_py-1 mo_heading_export_btn" href="index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=support"><i class="fa fa-phone mo_boot_mx-1"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_MEDIA_TAB3_SUPPORT');?></a>
    </div>
</div>

<div class="mo_boot_container-fluid">
    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0">
        <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_mediarestriction_navbar">
            <?php foreach ($tabs as $tabKey => $tab): ?>
                <a id="<?php echo $tab['id']; ?>" class="mo_boot_py-3 mo_mediarestriction_border mo_nav-tab <?php echo $tab_name == $tabKey ? 'mo_nav_tab_active' : ''; ?>" 
                    href="<?php echo $tab['href']; ?>" 
                    onclick="add_css_tab('#<?php echo $tab['id']; ?>');"
                    data-toggle="tab">
                    <?php echo Text::_($tab['text']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="mo_boot_container-fluid">
    <div class="tab-content mo_mediarestriction_border" id="myTabContent">
        <div id="media_plugin_overview" class="tab-pane <?php echo $tab_name == 'overview' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php media_restriction_plugin_overview(); ?>
                </div>
            </div>
        </div>
        <div id="file_restriction" class="tab-pane <?php echo $tab_name == 'media_restriction_settings' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php file_restriction(); ?>
                </div>
            </div>
        </div>
        <div id="role_based_file_restriction" class="tab-pane <?php echo $tab_name == 'media_restriction_advance_settings' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php role_based_file_restriction(); ?>
                </div>
            </div>
        </div>
        <div id="contact_us" class="tab-pane <?php echo $tab_name == 'support' ? 'active' : ''; ?>">
            <div class="mo_boot_row mo_boot_p-0 mo_boot_m-0">
                <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0" >
                    <?php support_form();?>
                </div>
            </div>
        </div>
        <div id="upgrade_plans" class="tab-pane <?php echo $tab_name == 'media_restriction_upgrade' ? 'active' : ''; ?>">
            <div class="mo_boot_row mo_boot_p-0 mo_boot_m-0">
                <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0">
                    <?php media_restriction_licensing_plans(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

function media_restriction_plugin_overview()
{
    ?>
    <div class="mo_boot_container-fluid">
        <div class="mo_boot_row">
            <div class="mo_boot_col-lg-7 mo_boot_p-4 mo_boot_text-justify">
                <h1><?php echo Text::_('COM_MINIORANGE_MEDIA_RESTRICTION_PLUGIN');?></h1><hr>
                <p><?php echo Text::_('COM_MINIORANGE_MEDIA_RESTRICTION_PLUGIN_TEXT');?>
                    <br><br>
                    <strong><?php echo Text::_('COM_MINIORANGE_MEDIA_RESTRICTION_PLUGIN_TEXT1');?></strong>&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIA_RESTRICTION_PLUGIN_TEXT5');?>
                    <br><br><?php echo Text::_('COM_MINIORANGE_MEDIA_RESTRICTION_PLUGIN_TEXT2');?>
                    <strong> <?php echo Text::_('COM_MINIORANGE_MEDIA_RESTRICTION_PLUGIN_TEXT3');?></strong>&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIA_RESTRICTION_PLUGIN_TEXT4');?>
                </p>
                <a class="mo_boot_btn mo_media_restrictionbtn mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/media-restriction-in-joomla"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_VISIT_SITE');?></a>
                <a class="mo_boot_btn mo_media_restrictionbtn mo_boot_px-3 mo_boot_mx-1" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_mediarestriction&view=accountsetup&tab-panel=media_restriction_upgrade';?>"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_LICENSE_PLANS');?></a>
                <a class="mo_boot_btn mo_media_restrictionbtn mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/restrict-media-file-folder-access-in-joomla"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_GUIDES');?></a>
            </div>
            <div class="mo_boot_col-lg-5 mo_boot_col-sm-12 mo_mediarestriction_img">
                <img class="img img-fluid" src="<?php echo URI::base();?>/components/com_miniorange_mediarestriction/assets/images/image.png"  alt="Media Restriction Image">   
            </div>
        </div>
    </div>
    <?php
}

function file_restriction()
{
    $configuration = MoMediaRestrictionUtility::getConfiguration();
    $enable_media_restriction = isset($configuration['enable_media_restriction'])? $configuration['enable_media_restriction']: 0;
    $mo_media_restriction_file_types = isset($configuration['mo_media_restriction_file_types'])? $configuration['mo_media_restriction_file_types']: '';
    $insertion = MoMediaRestrictionUtility::getRules();
    ?>
    <div class="mo_boot_container-fluid">
            <div class="file_restriction_UI">
                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-lg-8 mo_boot_col-sm-7 mo_boot_mt-1">
                            <h3><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FILE_RESTRICTION');?></h3>
                        </div>
                        <div class="mo_boot_col-lg-4 mo_boot_col-sm-4 mo_boot_mt-1">
                            <button  class= "mo_boot_btn mo_boot_mt-1 mo_media_restrictionbtn mo_boot_p-2 mo_disable_option show_rules mo_media_restriction_btn"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SHOW_RULES');?></button>
                        </div>
                    </div><hr>
                </div>
                <form name="f"  action="<?php echo Route::_('index.php?option=com_miniorange_mediarestriction&view=accountsetup&task=accountsetup.saveFileRestriction'); ?>"  method="post">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-5">
                                <strong><h4><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_MSG');?></h4></strong>
                            </div>
                            <div  class="mo_boot_col-sm-7 " >
                                <div class="mo_boot_form-check mo_boot_form-switch">
                                    <label>
                                        <input value="1" name="mo_enable_media_restriction" type="checkbox" id="mo_enable_media_restriction" class="mo_boot_form-check-input"<?php if($enable_media_restriction == 1) echo "checked" ?> >
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="media_restriction_options">
                        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-5">
                                    <strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_OPTION1');?></strong><br>
                                    <p class="mo_media_restriction_option"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_OPTION1_T1');?><strong> <?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_OPTION1_T2');?></strong></p>
                                </div>
                                <div class="mo_boot_col-sm-7">
                                    <input class="mo-form-control mo_media_restriction_textarea" name="mo_media_restriction_file_types" value="<?php echo  $mo_media_restriction_file_types; ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_OPTION1_T3');?>">
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-5">
                                    <strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_OPTION2');?><sup><a href='#' class='clickable-icon' onclick="moMediaUpgrade();"><img class="crown_img_small mo_boot_mx-2" src="<?php echo URI::base();?>\components\com_miniorange_mediarestriction\assets\images\crown.webp"></a></sup>:</strong><br>
                                    <p class="mo_media_restriction_option"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_OPTION2_T1');?></p>
                                </div>
                                <div class="mo_boot_col-sm-7">
                                    <textarea id="foldername" class="mo-form-control mo_disable_option mo_media_restriction_textarea " name="mo_folder_name" rows="4" cols="50" disabled placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_OPTION2_T2');?>" ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-5">
                                    <strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION');?><sup><a href='#' class='clickable-icon' onclick="moMediaUpgrade();"><img class="crown_img_small mo_boot_mx-2" src="<?php echo URI::base();?>\components\com_miniorange_mediarestriction\assets\images\crown.webp"></a></sup>:</strong>
                                    <p  class="mo_media_restriction_option"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_TEXT');?><strong>&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_TEXT2');?></strong> <?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_TEXT3');?></p>
                                </div>
                                <div class="mo_boot_col-sm-7">
                                    <select class="mo-form-control mo-form-control-select mo_disable_option mo_media_restriction_textarea" id="auto_redirect_option" name="auto_redirect_option" readonly>
                                        <option value="forbidden_page" selected><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_TEXT2');?></option>
                                        <option value="front_end_login" disabled><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_O2');?></option>
                                        <option value="back_end_login" disabled><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_O3');?></option>
                                        <option value="custom_url" disabled><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_O4');?></option>
                                        <option value="sso_url" disabled><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_OPTION_O5');?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-5">
                                </div>
                                <div class="mo_boot_col-sm-7">
                                    <input type="text" class="mo_custom_url_box mo_media_restriction_textarea " id="url_redirection" name="mo_redirection_option_value"  placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_PLACE');?>"  readonly disabled>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-5">
                                    <strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SERVER_NAME');?></strong>
                                </div>
                                <div class="mo_boot_col-sm-2">
                                    <input type="radio" name="server_name_value" value="Apache" disabled checked>&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_APACHE');?>
                                </div>
                                <div class="mo_boot_col-sm-3">
                                    <input type="radio" name="server_name_value" value="Ngix" disabled>&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NGINX');?><strong><sup><a href='#' class='clickable-icon' onclick="moMediaUpgrade();"><img class="crown_img_small mo_boot_mx-2" src="<?php echo URI::base();?>\components\com_miniorange_mediarestriction\assets\images\crown.webp"></a></sup></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_row mo_boot_mt-5">
                            <div class="mo_boot_col-sm-12 mo_boot_text-center">
                                <input type="submit" name="role_based_access" value="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SAVE_SETTINGS');?>" class= "mo_boot_btn mo_media_restrictionbtn  mo_boot_p-2 mo_boot_mt-1"/>
                            </div>
                        </div>
                    </div>
                    <p class="mo_media_restriction_note mo_boot_mt-2"><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NOTE');?></strong>&nbsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NOTE2');?>&nbsp;<strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SHOW_RULES');?></strong>&nbsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NOTE3');?> </p>
                </form>
            </div>
            <?php echo show_htaccess_rules(); ?>
    </div>
    <?php
}

function show_htaccess_rules()
{
    $configuration = MoMediaRestrictionUtility::getConfiguration();
    $enable_media_restriction = isset($configuration['enable_media_restriction'])? $configuration['enable_media_restriction']: 0;
    $insertion = MoMediaRestrictionUtility::getRules();
    ?>
    <div class="mo_boot_container-fluid">  
      <div class="mo_boot_col-sm-12">
            <div class="rules mo_media_restriction_rules">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8 mo_boot_mt-3">
                        <h3><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_HTACCESS_RULE');?></h3>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <button class= "mo_boot_btn mo_boot_mt-1 mo_media_restrictionbtn mo_boot_p-2 hide_rules mo_media_restriction_btn"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CONFIGURATION');?></button>
                    </div>
                </div>
                <hr>
                <div class="mo_boot_text-center mo_boot_mt-4 ">
                    <p><span class="mo_mediarestriction_warning_text"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CONFIG_TEXT1');?> <strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CONFIG_TEXT2');?></strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CONFIG_TEXT3');?>
                    </span></p>
                </div>

                <div class="mo_boot_text-center mo_boot_mt-3 mo_media_restriction_scroll" >
                <?php if( $enable_media_restriction)
                    {
                        ?>
                        <?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_BEGIN');?> <br>
                        <?php
                        foreach($insertion as $insert_rule)
                        {
                            echo $insert_rule; ?>
                            <br>
                            <?php
                        }
                        ?>
                        <?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_END');?>
                        <?php
                    }
                    ?>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_text-center mo_boot_mt-4">
                    <form class="d-inline-block" action="<?php echo Route::_('index.php?option=com_miniorange_mediarestriction&view=accountsetup&task=accountsetup.writeHtaccess'); ?>" method="post">
                        <input type="button"  value="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CONFIG_TEXT2');?>" onclick='submit();' class= "mo_boot_btn mo_media_restrictionbtn  mo_boot_p-2 mo_boot_mb-3"  /> <br/>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}
function role_based_file_restriction()
{
    $disabled="disabled";
    $groups = MoMediaRestrictionUtility::loadGroups();
    ?>
    <div class="mo_boot_container-fluid">
        <div class="mo_boot_row">
            <div class="mo_boot_p-2 file_restriction_UI" >
                <div class="mo_boot_col-sm-12 mo_boot_mt-3 ">
                    <div class="mo_boot_row">
                            <div class="mo_boot_col-lg-9 mo_boot_col-sm-8 mo_boot_mt-1">
                                <h3><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_ROLE_BASED');?><sup><a href='#' class='clickable-icon' onclick="moMediaUpgrade();"><img class="crown_img_small mo_boot_mx-2" src="<?php echo URI::base();?>\components\com_miniorange_mediarestriction\assets\images\crown.webp"></a></sup></h3>
                            </div>
                            <div class="mo_boot_col-sm-3 mo_boot_mt-1">
                                <button  class= "mo_boot_btn mo_boot_mt-1 mo_media_restrictionbtn mo_boot_p-2 mo_disable_option show_rules mo_media_restriction_btn" disabled><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SHOW_RULES');?></button>
                            </div>
                    </div><hr>
                </div>
                <form name="f"  action="<?php echo Route::_('index.php?option=com_miniorange_mediarestriction&view=accountsetup&task=accountsetup.saveRoleBasedRestriction'); ?>"  method="post">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-4">
                                <strong><h4><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_ROLE_BASED_RESTRICTION');?></h4></strong>
                            </div>
                            <div  class="mo_boot_col-sm-8 " >
                                <div class="mo_boot_form-check mo_boot_form-switch">
                                    <label>
                                        <input value="1" name="enable_role_based_restriction" type="checkbox"  class="mo_disable_option mo_boot_form-check-input" id="enable_role_based_restriction" disabled>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-4">
                                <strong><h5><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_GROUP');?></h5></strong>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <strong><h5><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FOLDER');?></h5></strong>
                            </div>
                        </div>
                        <div class="mo_boot_row">
                        <?php
                            if (empty($role_based_restrict_key_value)) {
                                foreach ($groups as $group) {
                                    if ($group[4] != 'Super Users') {
                            ?>
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo  $group[4] ?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="text" class="mo-form-control mo_role_restriction_disable" disabled name="mo_role_based_restriction_values<?php echo $group[0] ?>" value= "" placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_PLACEHOLDER1');?> <?php echo $group[4] ?>&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_PLACEHOLDER2');?> " />
                                    </div>
                                </div>
                            </div>
                            <?php 
                                    }
                                }
                            }
                        ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12 mo_boot_mt-5 mo_boot_text-center">
                        <input type="submit" name="role_based_access" value="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SAVE_SETTINGS');?>"  disabled class= "mo_boot_btn mo_media_restrictionbtn mo_boot_p-2 mo_boot_mt-1"/>
                    </div>
                    <p class="mo_media_restriction_note">&nbsp;<strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NOTE');?></strong>&nbsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NOTE2');?>&nbsp;<strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SHOW_RULES');?></strong>&nbsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NOTE3');?></p>
                </form>
            </div>
            <?php echo show_htaccess_rules(); ?>
        </div>
    </div>
    <?php
}

function support_form()
{
    $current_user = Factory::getUser();
    $result       = MoMediaRestrictionUtility::getCustomerDetails();
    $admin_email  = isset($result['email']) ? $result['email'] : '';
    $admin_phone  = isset($result['admin_phone']) ? $result['admin_phone'] : '';
    if($admin_email == '')
        $admin_email = $current_user->email;
    ?>
    <div id="sp_support_usync" class="mo_mediarestriction_border">
        <div class="mo_boot_row mo_boot_p-3">
            <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                <h3><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FEATURE_REQUEST');?></h3>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                <form  name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_mediarestriction&view=accountsetup&task=accountsetup.contactUs');?>">
                    <div class="mo_boot_col-12 mo_boot_mb-3">
                        <div class="mo_boot_row mo_boot_mt-2">
                            <div class="mo_boot_col-6 mo_boot_px-2">
                                <input type="radio" id="support_general" name="support_type" value="general_query" checked onclick="toggleCallTimeField()" style="display: none;">
                                <label for="support_general" class="support-type-btn" id="general_query_btn">
                                    <strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_GENERAL_QUERY');?></strong>
                                </label>
                            </div>
                            <div class="mo_boot_col-6 mo_boot_px-2">
                                <input type="radio" id="support_call" name="support_type" value="setup_call" onclick="toggleCallTimeField()" style="display: none;">
                                <label for="support_call" class="support-type-btn" id="setup_call_btn">
                                    <strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_SETUP_CALL');?></strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_text-center mo_boot_ml-3">
                            <img src="<?php echo URI::base();?>/components/com_miniorange_mediarestriction/assets/images/phone.svg" width="27" height="27"  alt="Phone Image">
                        </div>
                        <div class="mo_boot_col-sm-10">
                            <p><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_TEXT1');?><span class="mo_mediarestriction_warning_text">+1 978 658 9387</span></strong></p>
                        </div>
                    </div>

                    <div class="mo_boot_row mo_boot_mt-3">
                        <div class="mo_boot_col-sm-12">
                            <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_TEXT2');?></p>
                        </div>
                        <div class="mo_boot_col-sm-12">
                            <input  type="email" class="mo-form-control mo_media_restriction_input" name="query_email" value="<?php echo $admin_email; ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_TEXT3');?>" required />
                        </div>

                        <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                            <div class="mo_boot_row mo-phone-inline-row">
                                <div class="mo_boot_col-sm-4">
                                    <div class="mo-phone-card">
                                        <div class="mo-country-select" id="countrySelect">
                                            <span class="flag flag-in"></span>
                                            <span class="dial-code">+91</span>
                                            <span class="arrow">▾</span>
                                        </div>
                                        <ul class="mo-country-list" id="countryList"></ul>

                                        <input type="hidden" name="country_code" id="countryCode" value="91">
                                        <input type="hidden" name="client_timezone" id="moClientTimezone" value="">
                                        <input type="hidden" name="client_timezone_offset" id="moClientTimezoneOffset" value="">
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <input type="tel" class="mo-form-control mo_media_restriction_input" name="query_phone" id="query_phone" value="<?php echo $admin_phone; ?>" />
                                </div>
                            </div>
                        </div>

                        <div class="mo_boot_col-sm-12 mo_boot_mt-2" id="call_date_field" style="display: none;">
                            <h4 class="mo_boot_mb-1"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_DATE');?>:<span class="mo_required_field">*</span></h4>
                            <input type="date" class="mo-form-control" id="call_date" name="call_date" placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_DATE_PLACEHOLDER');?>"/>
                        </div>

                        <div class="mo_boot_col-sm-12 mo_boot_mt-2" id="call_time_field" style="display: none;">
                            <h4 class="mo_boot_mb-1"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_TIME');?>:<span class="mo_required_field">*</span></h4>
                            <input type="time" class="mo-form-control" id="call_time" name="call_time" placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_TIME_PLACEHOLDER');?>"/>
                        </div>

                        <div class="mo_boot_col-sm-12"><br>
                            <textarea  name="query_support" class="mo_boot_form-text-control mo_media_restriction_placeholder"  cols="52" rows="5" required placeholder="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_TEXT4');?>"></textarea>
                        </div>
                    </div>
                    
                    <div class="mo_boot_row mo_boot_text-center">
                        <div class="mo_boot_col-sm-12">
                            <input type="hidden" name="option1" value="mo_usync_login_send_query"/><br>
                            <input type="submit" name="send_query" value="<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_SUBMIT_QUERY');?>" class= "mo_boot_btn mo_media_restrictionbtn mo_boot_p-2 mo_media_restriction_submit" />
                        </div>
                    </div><hr>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-12">
                            <p><br><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_TEXT5');?><a class="mo_media_restriction_text" href="mailto:joomlasupport@xecurify.com"> joomlasupport@xecurify.com</a> </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function media_restriction_licensing_plans()
{
    $isRegistered=MoMediaRestrictionUtility::is_customer_registered();
    $upgradeURL="https://www.miniorange.com/contact";

    ?>
    <div class="mo_boot_container-fluid">
        <div class=" mo_boot_offset-1 mo_boot_col-sm-10">
            <div class="mo_mediarestriction-pricing-container cd-has-margins"><br>
                <ul class="cd-pricing-list cd-bounce-invert" >
                    <li class="cd-black" >
                        <ul class="cd-pricing-wrapper">
                            <li id="singlesite_tab" data-type="singlesite" class="mosslp is-visible cd-singlesite">
                                <header class="cd-pricing-header">
                                    <h2 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FREE');?><br/></h2>
                                </header>
                                <br>
                                <div class=" mo_mediarestriction_text_center">
                                    <span id="plus_total_price" class="mo_mediarestriction_pricing">$0</span>
                                </div>
                                <br>
                                <div class=" mo_mediarestriction_text_center">
                                    <a target="_blank" href="https://www.miniorange.com/contact" class= "mo_boot_btn mo_media_restrictionbtn mo_boot_p-2"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CONTACT');?></a>
                                </div>
                                <br>
                                <div class="cd-pricing-body">
                                    <ul class="cd-pricing-features">
                                        <li><strong><u><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FILE_RES');?></u></strong></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NUMBER');?>:&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_5');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_O');?></strong></u></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_403');?></li>
                                        <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CUSTOM');?></li>
                                        <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_JOOMLA');?></li>
                                        <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SSO');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FOLDER_RES');?></strong></u></li>
                                        <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_USER_RES');?></li>
                                        <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_ROLE_RES');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SUPPORT_SERVER');?></strong></u></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_APACHE');?></li>
                                        <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NGINX');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SECURITY');?></strong></u></li>
                                        <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_COOKIE');?></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </li>
                                    
                    <li class="cd-black">
                        <ul class="cd-pricing-wrapper">
                            <li id="singlesite_tab" data-type="singlesite" class="mosslp is-visible">
                                <header class="cd-pricing-header">
                                    <h2 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_PRE_VERSION');?><br/></h2>
                                </header>              
                                <br>
                                <div class="mo_mediarestriction_text_center">
                                    <span id="plus_total_price" class="mo_mediarestriction_pricing">$149</span>
                                </div>
                                <br>
                                <div class="mo_mediarestriction_text_center">
                                    <a target="_blank" href="https://portal.miniorange.com/initializepayment?requestOrigin=joomla_media_page_restriction" class= "mo_boot_btn mo_media_restrictionbtn mo_boot_p-2"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_UPGRADE');?></a>
                                </div>
                                <br> 
                                <div class="cd-pricing-body">
                                    <ul class="cd-pricing-features">
                                        <li><strong><u><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FILE_RES');?></u></strong></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NUMBER');?>:&ensp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_UNLIMITED');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_REDIRECT_O');?></strong></u></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_403');?></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_CUSTOM');?></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_JOOMLA');?></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SSO');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_FOLDER_RES');?></strong></u></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_USER_RES');?></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_ROLE_RES');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SUPPORT_SERVER');?></strong></u></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_APACHE');?></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_NGINX');?></li>
                                        <li><u><strong><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_SECURITY');?></strong></u></li>
                                        <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_PLUGIN_COOKIE');?></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul> 
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_mt-4 ">
                <details class="mo_mediarestriction_details">
                    <summary class="mo_mediarestriction_summary">
                            <?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_UPGRADE_PLAN'); ?>
                    </summary>

                    <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-6 mo_mediarestriction_upgarde_step">
                                <div><strong>1</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_UPGRADE_STEP1');?></p>
                            </div>
                            <div class="mo_boot_col-sm-6 mo_mediarestriction_upgarde_step">
                                <div><strong>4</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_UPGRADE_STEP4');?></p>
                            </div>
                        </div>

                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-6 mo_mediarestriction_upgarde_step">
                                <div><strong>2</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_UPGRADE_STEP2');?></p>
                            </div>
                            <div class="mo_boot_col-sm-6 mo_mediarestriction_upgarde_step">
                                <div><strong>5</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_UPGRADE_STEP5');?></p>
                            </div>
                        </div>

                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-6 mo_mediarestriction_upgarde_step">
                                <div><strong>3</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_UPGRADE_STEP3');?></p>
                            </div>
                            <div class="mo_boot_col-sm-6 mo_mediarestriction_upgarde_step">
                                <div><strong>6</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_UPGRADE_STEP6');?></p>
                            </div>
                        </div>
                    </div>
                </details>
            </div>

            <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                <details class="mo_mediarestriction_details">
                    <summary class="mo_mediarestriction_summary"><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_RETURN_POLICY'); ?></summary>

                    <div class="mo_boot_col-sm-12 mo_boot_pb-2">
                        <div>
                            <p><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_RETURN_POLICY_TEXT'); ?></p>
                        </div>
                        <div>
                            <h4><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_RETURN_POLICY_TEXT1'); ?></h4>
                            <ul>
                                <li><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_RETURN_POLICY_TEXT2'); ?></li>
                                <li><?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_RETURN_POLICY_TEXT3'); ?></li>
                            </ul>
                        </div>
                        <div>
                            <p>
                                <?php echo Text::_('COM_MINIORANGE_MEDIARESTRICTION_RETURN_POLICY_TEXT4'); ?>
                                <a href="mailto:joomlasupport@xecurify.com">
                                    <span class="mo_mediarestriction_word_wrap">joomlasupport@xecurify.com</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
    <?php
}
?>
