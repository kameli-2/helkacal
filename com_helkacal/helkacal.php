<?php

// No direct access to this file
defined('_JEXEC') or die;

// Include JTableEvent class that extends JTableContent
require_once('php'.DIRECTORY_SEPARATOR.'simple_html_dom.php');
require_once('php'.DIRECTORY_SEPARATOR.'JTableEvent.php');
require_once('php'.DIRECTORY_SEPARATOR.'TwoDigitInteger.php');
require_once('php'.DIRECTORY_SEPARATOR.'SubCategory.php');
require_once('php'.DIRECTORY_SEPARATOR.'Location.php');
require_once('php'.DIRECTORY_SEPARATOR.'OpenSubmitConfiguration.php');
require_once('lib'.DIRECTORY_SEPARATOR.'recaptchalib.php');

// Get configuration
$helkacal_config = new OpenSubmitConfiguration(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'config');

// Get koid
$config = JFactory::getConfig();
$databaseName=$config->get('db');
$db = JFactory::getDbo();
$query = $db->getQuery(true)
	->select($db->quoteName('koid'))
	->from($db->quoteName($helkacal_config->shareddb[0]).'.'.$db->quoteName($helkacal_config->sharedlinktbl[0]))
	->where($db->quoteName('db').' = '.$db->quote($databaseName));
$db->setQuery($query);

global $shareddb, $sharedtbl, $sharedlinktbl, $koid, $admin;
$koid = $db->loadResult();
$shareddb = $helkacal_config->shareddb[0];
$sharedtbl = $helkacal_config->sharedtbl[0];
$sharedlinktbl = $helkacal_config->sharedlinktbl[0];
$admin = false;
$user =& JFactory::getUser();

// Language file for "Read more..."
JFactory::getLanguage()->load('com_content');

$uri = '';
if (isset($_SERVER['SCRIPT_URI'])) $uri = $_SERVER['SCRIPT_URI'];
$path = dirname(__FILE__).DIRECTORY_SEPARATOR;
if (strpos($path, "/site/") === 0) $path = substr($path, 5);
if(!strstr($uri, 'kaupunginosat.net')){
	$path=strstr($path, '/components/com_helkacal');
}
JFactory::getDocument()->addScriptDeclaration('
function getPath() {
	return "'.$path.'";
}
');

// Use the com_content component's access control
if ($user->authorise('core.edit', 'com_content')) $admin = true;


// Get an instance of the controller prefixed by HelkaCal
$controller = JControllerLegacy::getInstance('HelkaCal');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();

?>
