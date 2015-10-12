<?php

/*
This router doesn't work properly and is not in use.
*/
/*
// Get configuration
require_once('php'.DIRECTORY_SEPARATOR.'OpenSubmitConfiguration.php');
$helkacal_config = new OpenSubmitConfiguration(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_helkacal'.DIRECTORY_SEPARATOR.'config');

// Get koid
$config = JFactory::getConfig();
$databaseName=$config->get('db');
$db = JFactory::getDbo();
$dbquery = $db->getQuery(true)
        ->select($db->quoteName('koid'))
        ->from($db->quoteName($helkacal_config->shareddb[0]).'.'.$db->quoteName($helkacal_config->sharedlinktbl[0]))
        ->where($db->quoteName('db').' = '.$db->quote($databaseName));
$db->setQuery($dbquery);

global $shareddb, $sharedtbl, $koid;
$koid = $db->loadResult();
$shareddb = $helkacal_config->shareddb[0];
$sharedtbl = $helkacal_config->sharedtbl[0];

function helkacalBuildRoute(&$query) {
	// vastaanota array('event' => eventid)
	// palauta array('artikkelin_alias')

	$segments = array();
	if (isset($query['view'])) {
		$segments[] = $query['view'];
		unset($query['view']);
	}
	if (isset($query['event'])) {
		// Selvita artikkelin alias
		global $shareddb, $sharedtbl;
		$db = JFactory::getDbo();
		$dbquery = $db->getQuery(true)
			->select($db->quoteName('article_id'))
			->from($db->quoteName($shareddb).'.'.$db->quoteName($sharedtbl))
			->where($db->quoteName('id').' = '.$query['event']);
		$db->setQuery($dbquery);
		$articleid = $db->loadResult();
		if ($articleid) {
			$dbquery = $db->getQuery(true)
				->select($db->quoteName('alias'))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id').' = '.$articleid);
			$db->setQuery($dbquery);
			$alias = $db->loadResult();
			if ($alias) {
				$segments[] = $alias;
				unset($query['event']);
			}
		}
	}
	return $segments;
}

function helkacalParseRoute($segments) {
	// vastaanota array('artikkelin_alias')
	// palauta array('event' => eventid)
	$vars = array();
	if ($segments[0]) {
		$vars['view'] = $segments[0];
	}
	if ($segments[1]) {
		global $shareddb, $sharedtbl, $koid;
		$db = JFactory::getDbo();
		$dbquery = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__content'))
			->where($db->quoteName('alias').' = '.$db->quote($segments[0]));
		$db->setQuery($dbquery);
		$articleid = $db->loadResult();
		if ($articleid) {
			$dbquery = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName($shareddb).'.'.$db->quoteName($sharedtbl))
				->where($db->quoteName('koid').' = '.$db->quote($koid).' AND '.$db->quoteName('article_id').' = '.$articleid);
			$db->setQuery($dbquery);
			$event = $db->loadResult();
			if ($event) $vars['event'] = (int) $event;
		}
	}
	return $vars;
}

?>
*/
