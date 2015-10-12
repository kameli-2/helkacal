<?php


/**
 * Calendar Model
 */
class HelkaCalModelCalendar extends JModelItem {

	/**
	 * Get single event as JTableEvent object
	 *
	 * @var		$eventid	The id of the event (not article)
	 * @return	JTableEvent	Event as a JTableEvent object
	 */
	public function getEvent($eventid) {
		global $shareddb, $sharedtbl;
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('article_id').', '.$db->quoteName('koid'))
			->from($db->quoteName($shareddb).'.'.$db->quoteName($sharedtbl))
			->where($db->quoteName('id').' = '.$eventid);
		$db->setQuery($query);
		$result = $db->loadAssoc();
		if (!$result) return false;

		$eventobject = new JTableEvent($db);
		$eventobject->koid = $result['koid'];
		$eventobject->article_id = $result['article_id'];
		$eventobject->load($result['article_id']);
		return $eventobject;
	}

	public function getFutureEvents($startdate = null) {
		if ($startdate == null) $startdate = date("Y-m");
		global $shareddb, $sharedtbl, $sharedlinktbl, $koid;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
                        ->select($db->quoteName('end_time'))
                        ->from($db->quoteName($shareddb).'.'.$db->quoteName($sharedtbl))
                        ->where($db->quoteName('koid').' = '.$db->quote($koid))
			->order($db->quoteName('end_time').' DESC LIMIT 1');
                $db->setQuery($query);
                $result = $db->loadResult();
		if (!$result) return false;

		list($lastyear, $lastmonth) = explode("-", $result);

		$allevents = array();

		// Loop through every month of every year from now to last event (=$result)
		$y = date("Y", strtotime($startdate));
		$m = date("m", strtotime($startdate));
		while (strtotime($y.'-'.$m.'-00') <= strtotime($lastyear.'-'.$lastmonth.'-00')) {

			$start = $y.'-'.$m.'-01';
			$oldm=$m;
                        $m++;
                        if ($m > 12) {
                                $m -= 12;
                                $y += 1;
                        }

			if (isset($_GET['date'])) {
				$date = explode("-", $_GET['date']);
				if (isset($date[0])) {
					if ($date[0] != substr($start, 0, 4)) continue;
				}
				if (isset($date[1])) {
					if (intval($date[1]) != $oldm) continue;
				}
			}

			$end = $y.'-'.$m.'-00';

			$events = $this->getEvents($start.' 00:00:00', $end.' 23:59:59');
			if ($events) {
				// Put multiday events to the end if start day has already passed
				foreach ($events as $key => $event) {
					if (!$event->onedayevent() && (strtotime(substr($event->start_time, 0, 10))+60*60*24 < time() || strtotime(substr($event->start_time, 0, 10))+60*60*24 < strtotime($start))) {
						unset($events[$key]);
						$event->showdate = false;
						$events[$key] = $event;
					}
				}
			}
			if (count($events)) $allevents[$start] = $events;
		}

		$jinput = JFactory::getApplication()->input;
		$past = $jinput->get('past', array(1), 'ARRAY');
		if (!in_array(1, $past)) $allevents = array_reverse($allevents);

		// Get events without time information
		$allevents['no_time_info'] = $this->getEvents('0000-00-00 00:00:00', '1000-01-01 00:00:00');

		return $allevents;
	}

	/**
	 * Get events as JTableEvent objects
	 *
	 * @return	array		array of JTableEvent objects
	 */
	public function getEvents($start, $end, $all = false) {
		global $shareddb, $sharedtbl, $sharedlinktbl, $koid;
		//print_r($shareddb);return;

		$start = date("Y-m-d H:i:s", strtotime($start));
		$end = date("Y-m-d H:i:s", strtotime($end));

		$jinput = JFactory::getApplication()->input;
		$category = $jinput->get('category', 0, 'ARRAY');
		$categorydepth = $jinput->get('categorydepth', 0, 'INTEGER');
		$states = $jinput->get('state', array(1), 'ARRAY');
		$past = $jinput->get('past', array(1), 'ARRAY');
		if ($all) $past = array(1, 2);

		$db = JFactory::getDbo();

		// If category is selected, include as many children categories as desired
		if ($category) {
			if ($categorydepth) {
				$allcategories = $category;

				foreach($category as $catid) {
					// Get children elements of the category
					$query = $db->getQuery(true)
						->select($db->quoteName(array('id', 'parent_id')))
						->from($db->quoteName('#__categories'))
						->where($db->quoteName('extension')  . ' = ' . $db->quote('com_content') .  ' AND ' . $db->quoteName('published') . ' = 1');
					$db->setQuery($query);
					$categories = $db->loadAssocList();

					$allcategories = array_merge($allcategories, $this->getchildren($catid, 1, $categorydepth, $categories));
				}

				$category = $allcategories;
			}
		}

		// Get events from database
		$events = array();
		$query = $db->getQuery(true)
			->select($db->quoteName('article_id').', '.$db->quoteName('koid'))
			->from($db->quoteName($shareddb).'.'.$db->quoteName($sharedtbl))
			->where($db->quoteName('koid').' = '.$db->quote($koid)
			.' AND '.$db->quoteName('end_time').' >= '.$db->quote(date("Y-m-d H:i:s", strtotime($start)))
			.' AND '.$db->quoteName('start_time').' <= '.$db->quote(date("Y-m-d H:i:s", strtotime($end))));
		$db->setQuery($query);
		$result = $db->loadAssocList();
		if (!$result) return false;

		foreach ($result as $event) {
			$eventObject = new JTableEvent($db);
			$eventObject->koid = $event['koid'];
			$eventObject->load($event['article_id']);
			$eventObject->article_id = $event['article_id'];

			// Filter out unwanted events
			if ($category && !in_array($eventObject->catid, $category)) continue;
			if ($states && !in_array($eventObject->state, $states)) continue;
			if (isset($_GET['date'])) {
				$date = explode("-", $_GET['date']);
				if (isset($date[0])) {
					if (strtotime(substr($eventObject->start_time, 0, 4)) > strtotime($date[0])) continue;
					if (strtotime(substr($eventObject->realEndTime(), 0, 4)) < strtotime($date[0])) continue;
				}
				if (isset($date[1])) {
					if (strtotime(substr($eventObject->start_time, 0, 7)) > strtotime($date[0].'-'.$date[1])) continue;
					if (strtotime(substr($eventObject->realEndTime(), 0, 7)) < strtotime($date[0].'-'.$date[1])) continue;
				}
				if (isset($date[2])) {
					if (strtotime(substr($eventObject->start_time, 0, 10)) > strtotime($_GET['date'])) continue;
					if (strtotime(substr($eventObject->realEndTime(), 0, 10)) < strtotime($_GET['date'])) continue;
				}
			}
			else {
				if (!in_array(1, $past)) {
					// Discard upcoming events
					if (strtotime(substr($eventObject->start_time, 0, 10)) > time()) continue;
				}
				if (!in_array(2, $past)) {
					// Discard past events
					if (strtotime(substr($eventObject->realEndTime(), 0, 10))+60*60*24 < time() && $eventObject->start_time != '0000-00-00 00:00:00') continue;
				}
			}

			$events[] = $eventObject;
		}

		// Use cmp-function to sort events by start time
		usort($events, array($this, "cmp"));

		if (!in_array(1, $past)) $events = array_reverse($events);

		return $events;
	}

	function getAllEvents() {
		// Return all events within +- 1 year

		// Don't let getEvents() filter events by $_GET['date']
		if (isset($_GET['date'])) {
			$date = $_GET['date'];
			unset($_GET['date']);
		}

		$start = date("Y-m-d H:i:s", time()-60*60*24*365);
		$end = date("Y-m-d H:i:s", time()+60*60*24*365);

		$events = $this->getEvents($start, $end, true);

		if (isset($date)) $_GET['date'] = $date;

		return $events;
	}

	// Compare two events
	function cmp($a, $b) {
		return strcmp($a->start_time, $b->start_time);
	}

        function getchildren($id, $depth, $maxdepth, $categories) {
		// return all children of this category
		$children = array();
		foreach ($categories as $category) {
			if ($category['parent_id'] == $id) {
				$children[] = $category['id'];
				if ($depth < $maxdepth) $children = array_merge($children, $this->getchildren($category['id'], $depth+1, $maxdepth, $categories));
			}
		}

/*		echo '<pre>$children: ';
		print_r($children);
		echo '</pre>';
*/
		return $children;
	}
}
?>
