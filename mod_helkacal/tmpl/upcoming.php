<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_category
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$modulePath = JURI::base() . 'modules/mod_helkacal/';
$document->addStyleSheet($modulePath.'tmpl/upcoming_'.$style.'.css');

if (!$params->get('categorysymbols', 0)) {
	$document->addStyleDeclaration(".helkacal_categorycontainer { display: none !important; }");
}

$pages = $params->get('pages', 5);

if (strstr($calendarurl, '?')) $calendarurl .= '&';
else $calendarurl .= '?';

echo '<div class="mod_helkacal_upcoming" style="background-color:'.$params->get('bgcolor', '#FFFFFF').';">';

if ($params->get('title', '') != '') echo '<div class="mod_helkacal_upcoming_title"><h2>'.$params->get('title', '').'</h2></div>';

foreach ($events as $event) {
	if (strtotime($event->realEndTime()) < time()) continue;
	echo $event;
	$pages--;
	if ($pages <= 0) break;
}
/*foreach ($events as $event) {
	if (strtotime($event->start_time) > time()) continue;
	echo $event;
	$pages--;
	if ($pages <= 0) break;
}*/

echo '</div>';
