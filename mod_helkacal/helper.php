<?php
require_once( dirname(__FILE__) . '/../../components/com_helkacal/models/calendar.php' );

class modHelkaCalHelper {
	public static function getAllEvents() {
		$calendarmodel = new HelkaCalModelCalendar();
		return $calendarmodel->getAllEvents();
	}
	public static function getEvent($eventid) {
		$calendarmodel = new HelkaCalModelCalendar();
		return $calendarmodel->getEvent($eventid);
	}
}
?>
