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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
require_once JPATH_COMPONENT . '/helpers/mo_customer_setup.php';
require_once JPATH_COMPONENT . '/helpers/mo_mediarestriction_utility.php';

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_miniorange_mediarestriction'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by JoomlaRoleBasedRedirection
$controller = BaseController::getInstance('MiniorangeMediaRestriction');
 
// Perform the Request task
$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$task = ($input && method_exists($input, 'get')) ? $input->get('task') : '';
$controller->execute($task);
 
// Redirect if set by the controller
$controller->redirect();