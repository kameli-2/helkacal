<?php

// no direct access
defined('_JEXEC') or die;

require_once( dirname(__FILE__) . '/helper.php' );
require_once( dirname(__FILE__) . '/../../components/com_helkacal/php/JTableEvent.php' );
require_once(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_helkacal'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'OpenSubmitConfiguration.php');


// Get configuration
$helkacal_config = new OpenSubmitConfiguration(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_helkacal'.DIRECTORY_SEPARATOR.'config');

// Get koid
$config = JFactory::getConfig();
$databaseName=$config->get('db');
$db = JFactory::getDbo();
$query = $db->getQuery(true)
        ->select($db->quoteName('koid'))
        ->from($db->quoteName($helkacal_config->shareddb[0]).'.'.$db->quoteName($helkacal_config->sharedlinktbl[0]))
        ->where($db->quoteName('db').' = '.$db->quote($databaseName));
$db->setQuery($query);

global $shareddb, $sharedtbl, $sharedlinktbl, $koid, $admin, $itemid;
$koid = $db->loadResult();
$shareddb = $helkacal_config->shareddb[0];
$sharedtbl = $helkacal_config->sharedtbl[0];
$sharedlinktbl = $helkacal_config->sharedlinktbl[0];
$admin = false;
$user =& JFactory::getUser();

// Language file for "Read more..."
JFactory::getLanguage()->load('mod_articles_category');
JFactory::getLanguage()->load('com_helkacal');
JFactory::getLanguage()->load('com_content');

$categorysymbols = $params->get('categorysymbols', 0);
$map = $params->get('map', 0);
$style = $params->get('calendarstyle', 'default');
$itemid = $params->get('calendarurl'); // ItemID of the calendar
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$item_heading = $params->get('item_heading');

// Obtain link to gallery from ItemID
$calendarurl = JRoute::_('index.php?option=com_helkacal&Itemid='.$itemid);

$events = modHelkaCalHelper::getAllEvents(); // Get events from db
if (isset($_GET['event'])) $currentevent = modHelkaCalHelper::getEvent($_GET['event']);

require( JModuleHelper::getLayoutPath('mod_helkacal', $params->get('layout', 'default')));

?>
