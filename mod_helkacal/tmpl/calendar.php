<?php
// No direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_helkacal/tmpl/calendar_'.$style.'.css');
$document->addScript('modules/mod_helkacal/tmpl/calendar.js');

if (strstr($calendarurl, '?')) $calendarurl .= '&';
else $calendarurl .= '?';

$dayswithevents = array();
$categories = array();
// Count how many events are on each day
foreach ($events as $event) {
	if ($categorysymbols && (isset($_GET['event']) || isset($_GET['date']))) $categories[$event->catid] = $event->categorysymbol();

	if ($event->start_time == '0000-00-00 00:00:00') continue;
	for ($starttime = strtotime($event->start_time); $starttime < strtotime($event->realEndTime()); $starttime += 60*60*24) {
		if (!isset($dayswithevents[date("Y", $starttime)][date("m", $starttime)][date("d", $starttime)])) $dayswithevents[date("Y", $starttime)][date("m", $starttime)][date("d", $starttime)] = 0;
		$dayswithevents[date("Y", $starttime)][date("m", $starttime)][date("d", $starttime)]++;
	}
}

if ($dayswithevents) {
	echo '<div class="mod_helkacal_container">';

	ksort($dayswithevents);
	$firstyear = array_shift(array_keys($dayswithevents));
	ksort($dayswithevents[$firstyear]);
	$firstmonth = array_shift(array_keys($dayswithevents[$firstyear]));
	$lastyear = array_pop(array_keys($dayswithevents));
	ksort($dayswithevents[$lastyear]);
	$lastmonth = array_pop(array_keys($dayswithevents[$lastyear]));

	// Force current month between first and last
	if (mktime(0, 0, 0, $firstmonth, 1, $firstyear) > time()) {
		$firstmonth = date("m");
		$firstyear = date("Y");
	}
	elseif (mktime(0, 0, 0, $lastmonth, 1, $lastyear) < mktime(0, 0, 0, date("m"), 1)) {
		$lastmonth = date("m");
		$lastyear = date("Y");
	}

	$year = $firstyear;
	$month = $firstmonth;
	while (strtotime($year.'-'.$month.'-01 00:00:00') <= strtotime($lastyear.'-'.$lastmonth.'-01 00:00:00')) {
		echo draw_calendar(sprintf("%'.02d", $month),$year,$dayswithevents,$calendarurl);
		$month++;
		if ($month > 12) {
			$month -= 12;
			$year++;
		}
	}
/*
foreach ($dayswithevents as $year => $months) {
	foreach ($months as $month => $days) {
		echo draw_calendar($month,$year,$dayswithevents,$calendarurl);
	}
}
*/
	echo '</div>';
}
if ($map && isset($_GET['event'])) {
	echo '<div class="mod_helkacal_container">';

	echo $currentevent->getLocationMap();

	echo '</div>';
}

if ($categorysymbols && (isset($_GET['event']) || isset($_GET['date']))) {
	$document->addStyleDeclaration('#helkacal_catfiltercontainer { display: none !important; }');

	echo '<div class="mod_helkacal_container">';
	echo '<div class="helkacal_category">';
	echo '<a href="'.substr($calendarurl, 0, -1).'">';
	echo '<img class="helkacal_category_symbol" src="components/com_helkacal/img/symbols/kaikki.png" alt="N&auml;yt&auml; kaikki" title="N&auml;yt&auml; kaikki" />';
	echo '</a>';
	echo '</div>';
        foreach ($categories as $catid => $category) {
                echo '<div class="helkacal_category">';
			echo '<a href="'.$calendarurl.'chosencategory='.$catid.'">';
				echo $category;
			echo '</a>';
		echo '</div>';
        }
	echo '</div>';
}

/* draws a calendar */
function draw_calendar($month,$year,$dayswithevents,$calendarurl){
	/* draw table */
	$calendar = '<table cellpadding="0" cellspacing="0" class="mod_helkacal_calendar mod_helkacal_month-'.$year.'-'.$month.'">';

	$time = new JDate($year.'-'.$month.'-01 00:00:00');
	$calendar .= '<tr class="mod_helkacal_calendar-year"><td class="mod_helkacal_leftarrow">&lt;</td><td colspan="5" class="mod_helkacal_title"><a href="'.$calendarurl.'date='.$time->format('Y', false, true).'">'.$time->format('Y', false, true).'</a></td><td class="mod_helkacal_rightarrow">&gt;</tr>';
	$calendar .= '<tr class="mod_helkacal_calendar-month"><td class="mod_helkacal_leftarrow">&lt;</td><td colspan="5" class="mod_helkacal_title"><a href="'.$calendarurl.'date='.$time->format('Y-m', false, true).'">'.$time->format('F', false, true).'</a></td><td class="mod_helkacal_rightarrow">&gt;</tr>';

	/* table headings */
	$headings = array(JText::_('MON'),JText::_('TUE'),JText::_('WED'),JText::_('THU'),JText::_('FRI'),JText::_('SAT'), JText::_('SUN'));
	$calendar.= '<tr class="mod_helkacal_calendar-row"><td class="mod_helkacal_calendar-day-head">'.implode('</td><td class="mod_helkacal_calendar-day-head">',$headings).'</td></tr>';

	/* days and weeks vars now ... */
	$running_day = date('N',mktime(0,0,0,$month,1,$year))-1; //date('N') returns 1-7, starting with monday
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	/* row for week one */
	$calendar.= '<tr class="mod_helkacal_calendar-row">';

	/* print "blank" days until the first of the current week */
	for ($x = 0; $x < $running_day; $x++) {
		$calendar .= '<td class="mod_helkacal_calendar-day-np"> </td>';
		$days_in_this_week++;
	}

	/* keep going with days.... */
	for ($list_day = 1; $list_day <= $days_in_month; $list_day++) {
		$calendar .= '<td class="mod_helkacal_calendar-day';
		if (isset($dayswithevents[$year][$month][sprintf("%02s", $list_day)])) $calendar .= ' mod_helkacal_calendar-eventful';
		if (date("Y-m-d") == $year.'-'.$month.'-'.sprintf("%02s", $list_day)) $calendar .= ' mod_helkacal_calendar-today';

		if (isset($_GET['date'])) {
			$date = explode("-", $_GET['date']);
			if (isset($date[2]) && sprintf("%02s", $list_day) == $date[2] && $month == $date[1] && $year == $date[0]) $calendar .= ' mod_helkacal_calendar-selected';
		}

		$calendar .= '">';

		if (isset($dayswithevents[$year][$month][sprintf("%02s", $list_day)])) $calendar .= '<a href="'.$calendarurl.'date='.$year.'-'.$month.'-'.sprintf("%02s", $list_day).'">';
		/* add in the day number */
		$calendar.= '<div>'.$list_day.'</div>';
		if (isset($dayswithevents[$year][$month][sprintf("%02s", $list_day)])) $calendar .= '</a>';

		$calendar.= '</td>';
		if($running_day == 6) {
			$calendar.= '</tr>';
			if (($day_counter+1) != $days_in_month) {
				$calendar.= '<tr class="mod_helkacal_calendar-row">';
			}
			$running_day = -1;
			$days_in_this_week = 0;
		}
		$days_in_this_week++; $running_day++; $day_counter++;
	}

	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="mod_helkacal_calendar-day-np"> </td>';
		endfor;
	endif;

	/* final row */
	$calendar.= '</tr>';

	/* end the table */
	$calendar.= '</table>';
	
	/* all done, return result */
	return $calendar;
}

?>
