<?php

// No direct access to this file
defined('_JEXEC') or die;

// Add stylesheet
$jinput = JFactory::getApplication()->input;
$style = $jinput->get('style', 'default', 'STRING');
JHtml::_('jquery.framework', false);
JFactory::getDocument()->addStyleSheet(JUri::base().'components/com_helkacal/views/calendar/tmpl/'.$style.'.css');
JFactory::getDocument()->addScript(JUri::base().'components/com_helkacal/views/calendar/tmpl/'.$style.'.js');
$showcategories = $jinput->get('showcategories', '1', 'INTEGER');
if (!$showcategories) JFactory::getDocument()->addStyleDeclaration(".helkacal_categorycontainer, .helkacal_category { display: none !important; }");

global $admin;

function printableEvent($event) {
	echo '<div class="helkacal_event'.(($event->featured) ? ' helkacal_featured' : '').'">';

	echo '<div class="helkacal_eventcontainer">';

	if ($event->getFirstImg() !== null) echo '<img class="helkacal_eventimg" src="'.$event->getFirstImg().'">';

	echo '<div class="helkacal_categorycontainer">';
	echo '<div class="helkacal_category">'.$event->categorysymbol().'</div>';
	if ($event->children) echo '<div class="helkacal_category">'.$event->categorysymbol("Lapset").'</div>';
	if ($event->chargeable) echo '<div class="helkacal_category">'.$event->categorysymbol("Maksullinen").'</div>';
	echo '</div>';

	echo '<div class="helkacal_title">'.$event->title.'</div>';
	echo '<div class="helkacal_eventdesc">'.implode(' ', array_slice(explode(' ', strip_tags($event->introtext)), 0, 50)).'</div>';
	if ($event->eventtime() || $event->getLocation('name')) {
		echo '<div class="helkacal_details">';
		if ($event->eventtime()) echo '<div class="helkacal_event_time">'.$event->eventtime().'</div>';
		if ($event->getLocation('name')) echo '<div class="helkacal_event_location">'.$event->printLocation(true).'</div>';
		echo '</div>';
	}

	echo '</div>';

	echo '</div>';
}

// Print version
if (isset($_GET['print'])) {
		echo '<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8">
				<link rel="stylesheet" href="'.JURI::base().'/components/com_helkacal/views/calendar/tmpl/print.css" type="text/css" />
				<link rel="stylesheet" href="'.JURI::base().'/components/com_helkacal/views/calendar/tmpl/print'.((isset($_GET['cols'])) ? $_GET['cols'] : '3').'cols.css" type="text/css" />
				<link rel="stylesheet" href="'.JURI::base().'/components/com_helkacal/views/calendar/tmpl/print'.((isset($_GET['size'])) ? $_GET['size'] : 'A4').'.css" type="text/css" />
				<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
				<script src="'.JURI::base().'/components/com_helkacal/views/calendar/tmpl/print.js"></script>
				<!--[if IE]>
					<style>
						@font-face{
							font-family: BommerSlab;
							font-weight: normal;
							font-style: normal;
							src: url("'.JURI::base().'components/com_helkacal/fonts/BommerSlab-Regular.eot");
						}
						@font-face{
							font-family: BommerSlab;
							font-weight: bold;
							font-style: normal;
							src: url("'.JURI::base().'components/com_helkacal/fonts/BommerSlab-Bold.eot");
						}
						@font-face{
							font-family: BommerSlab;
							font-weight: normal;
							font-style: italic;
							src: url("'.JURI::base().'components/com_helkacal/fonts/BommerSlab-RegularItalic.eot");
						}
					</style>
				<![endif]-->
			</head>
			<body>
				<div id="helkacal_allwrapper">
		';

	echo '<div id="helkacal_print_header">';
	echo '<div id="helkacal_print_title">Arabian</div>';
	if (isset($_GET['month'])) $starttime = new JDate($_GET['month']);
	if (isset($_GET['date'])) $starttime = new JDate($_GET['date']);
	else $starttime = new JDate(array_shift(array_shift(array_values($this->events)))->start_time);
	echo '<div id="helkacal_print_month">'.$starttime->format('F Y', false, true).'</div>';
	echo '<div id="helkacal_print_subtitle">kulttuurikalenteri</div>';
	echo '</div>';

	echo '<div class="helkacal_events">';

	$onedayevents = array();

	$continuoustitleprinted = false;
	foreach ($this->events as $events) {
		foreach ($events as $event) {
			if ($event->onedayevent() || substr($event->start_time, 0, 7) == $starttime->format('Y-m')) {
				$onedayevents[] = $event;
				continue;
			}
			if (!$continuoustitleprinted) {
				echo '<div oncontextmenu="return false;" class="helkacal_eventsofoneday helkacal_continuous"><div class="helkacal_continuous_title">Jatkuvat</div>';
				$continuoustitleprinted = true;
			}
			printableEvent($event);
		}
	}
	$laststarttime = 0;
	foreach ($onedayevents as $event) {
		$starttime = new JDate($event->start_time);
		if (substr($laststarttime, 0, 10) != substr($event->start_time, 0, 10)) echo '</div><div oncontextmenu="return false;" class="helkacal_eventsofoneday"><div class="helkacal_eventdate"><div class="helkacal_eventday">'.$starttime->format('j.', false, true).'</div><div class="helkacal_eventweekday">'.JText::_($starttime->format('l', true, false)).'</div></div>';
		$laststarttime = $event->start_time;
		printableEvent($event);
	}
	echo '</div>';

	echo '<div oncontextmenu="return false;" class="helkacal_eventsofoneday">';
	echo '	<div class="helkacal_ilmoita_tapahtumasta">
			<div class="helkacal_ilmoita_tapahtumasta_title">Ilmoita tapahtuma!</div>
			<div class="helkacal_ilmoita_tapahtumasta_desc">Tapahtumat poimitaan artova.fi:n kalenterista. Voit ilmoittaa tapahtumasi mukaan Ilmoita tapahtumasta -lomakkeella.</div>
		</div>';
	echo '<div class="helkacal_site_url">www.artova.fi</div>';
	echo '<div class="helkacal_site_logo"><img src="'.JURI::base().'components/com_helkacal/img/artovalogo.png" /></div>';
	echo '</div>';

	echo '</div>';
	echo '</div>';

	exit(1);
}
elseif (isset($_GET['event'])) {
	// Show single event instead of calendar
	$doc =& JFactory::getDocument();
	$doc->addCustomTag('<meta property="og:type" content="article" />');
	$doc->addCustomTag('<meta property="og:url" content="'.$this->event->urlToEvent().'" />');
	$doc->addCustomTag('<meta property="og:title" content="'.$this->event->title.'" />');
	$doc->addCustomTag('<meta property="og:description" content="'.substr(strip_tags($this->event->introtext), 0, 300).'" />');

	$domdoc = new DOMDocument();
	@$domdoc->loadHTML($this->event->introtext);
	$tags = $domdoc->getElementsByTagName('img');
	$tag = $tags->item(0);
	if (isset($tag)) $doc->addCustomTag('<meta property="og:image" content="'.JURI::base().$tag->getAttribute('src').'" />');

	echo '<div class="helkacal_eventpage">';

	echo '<div class="helkacal_category">'.$this->event->categorysymbol().'</div>';

	if ($this->event->eventtime(true) != '') echo '<div class="helkacal_eventtime"><img src="components/com_helkacal/img/time.png" alt="Time">'.$this->event->eventtime(true).'</div>';
	if ($this->event->getLocation('name')) echo '<div class="helkacal_location"><img src="components/com_helkacal/img/location.png" alt="Location">'.$this->event->printLocation().'</div>';

	echo '<h2 class="helkacal_eventtitle">'.$this->event->title.'</h2>';

	echo '<p>'.$this->event->introtext.$this->event->fulltext.'</p>';

	if ($this->event->url != '' && $this->event->url != 'null' && $this->event->fixurl($this->event->url)) echo '<div class="helkacal_url"><a href="'.$this->event->fixurl($this->event->url).'" target="_blank">'.$this->event->shortUrl($this->event->url).'</a></div>';

	$jinput = JFactory::getApplication()->input;
	$showmap = $jinput->get('showmap', 1, 'INTEGER');
	if ($showmap && $this->event->getLocation('latitude')) echo $this->event->getLocationMap();

	if ($admin) {
		if ($this->event->contact != '' && $this->event->contact != 'null') echo '<div class="helkacal_contactinfo"><img src="components/com_helkacal/img/contact.png" alt="Contact">'.$this->event->contact.'</div>';
		echo '<div class="helkacal_publishbuttons">';
		if ($this->event->state != 1) echo '<div class="helkacal_publishbuttoncontainer"><div class="helkacal_publish" id="publish_'.$this->event->article_id.'"></div>'.JText::_('COM_HELKACAL_EVENT_PUBLISH').'</div>';
		echo '<div class="helkacal_publishbuttoncontainer"><div class="helkacal_edit" id="edit_'.$this->event->article_id.'"></div>'.JText::_('JGLOBAL_EDIT').'</div>';
		if ($this->event->state != -2) echo '<div class="helkacal_publishbuttoncontainer"><div class="helkacal_delete" id="delete_'.$this->event->article_id.'"></div>'.JText::_('COM_HELKACAL_EVENT_DELETE').'</div>';
		echo '</div>';
	}

	$jinput = JFactory::getApplication()->input;
	$socialmedia = $jinput->get('socialmedia', '1', 'INTEGER');
	if ($socialmedia) {
		echo $this->event->getSocialMedia();
	}
	$socialmediacomment = $jinput->get('socialmediacomment', '0', 'INTEGER');
	if ($socialmediacomment) echo $this->event->getSocialMediaComment();

	echo '</div>';

}
else {
	if (!$this->events) echo JText::_('COM_HELKACAL_NO_EVENTS');
	else {
		// Category filter
		$categories = array();

		$jinput = JFactory::getApplication()->input;
		$showcategoryfilter = $jinput->get('categoryfilter', 1, 'INTEGER');

		if ($showcategoryfilter) {
			if ($style != 'artova') echo '<h3>'.JText::_('COM_HELKACAL_CATEGORYFILTER').'</h3>';
			echo '<div id="helkacal_catfiltercontainer">';
			foreach ($this->events as $month) {
				if ($month) {
					foreach ($month as $event) {
						if (!is_object($event)) print_r($event); else
						$categories[$event->catid] = $event->categorysymbol();
					}
				}
			}
			echo '<div class="helkacal_category" id="helkacal_catfilter-all"><img class="helkacal_category_symbol" src="components/com_helkacal/img/symbols/kaikki.png" alt="N&auml;yt&auml; kaikki" title="N&auml;yt&auml; kaikki" /></div>';
			foreach ($categories as $catid => $category) {
				echo '<div class="helkacal_category" id="helkacal_catfilter-'.$catid.'">'.$category.'</div>';
			}
			echo '</div>';
		}

		if (isset($_GET['date'])) {
			$date = explode("-", $_GET['date']);
			if (isset($date[2])) {
				$jdate = new JDate($_GET['date']);
				echo '<h2 class="helkacal_month">'.$jdate->format('F j', false, true).'</h2>';

			}
		}

		$month = "";
		foreach ($this->events as $key => $month) {
			if (!$month) continue;
			if ($key == 'no_time_info') {
				if (!isset($date[2])) echo '<h2 class="helkacal_month">'.JText::_('COM_HELKACAL_NO_TIME_INFO').'</h2>';
			}
			else {
				$starttime = new JDate($key);
				if (!isset($date[2])) echo '<h2 class="helkacal_month">'.$starttime->format('F Y', false, true).'</h2>';
			}
			foreach ($month as $event) echo $event;
		}
	}
}
if ($admin) {
	// Obtain link to addevent form from ItemID
	$jinput = JFactory::getApplication()->input;
	$itemid = $jinput->get('addeventurl');
	if ($itemid) {
		$addeventurl = JRoute::_('index.php?option=com_helkacal&Itemid='.$itemid);

		echo '
		<form id="helkacal_editform" class="hidden" action="'.$addeventurl.'" method="post">
			<input type="hidden" name="articleid" id="helkacal_editform_articleid" value="">
			<input type="hidden" name="returnurl" id="helkacal_editform_returnurl" value="">
		</form>';
	}
}

?>
