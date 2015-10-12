<?php

// No direct access to this file
defined('_JEXEC') or die;

// Add stylesheet & js
JHtml::_('jquery.framework', false);
JFactory::getDocument()->addStyleSheet(JUri::base().'components/com_helkacal/views/addevent/tmpl/default.css');
JFactory::getDocument()->addScript(JUri::base().'components/com_helkacal/views/addevent/tmpl/inputchange.js');
JFactory::getDocument()->addScript(JUri::base().'components/com_helkacal/views/addevent/tmpl/default.js');

// Map stylesheet
JFactory::getDocument()->addStyleSheet(JUri::base().'components/com_helkacal/lib/map/css/jquery-gmaps-latlon-picker.css');

// Some JavaScript for the map
//JFactory::getDocument()->addScript('JUri::base().'components/com_helkacal/lib/map/js/jquery-2.1.1.min.js');
JFactory::getDocument()->addScript('http://maps.googleapis.com/maps/api/js?sensor=false');
JFactory::getDocument()->addScript(JUri::base().'components/com_helkacal/lib/map/js/jquery-gmaps-latlon-picker.js');

if (isset($this->storeresults)) {
	if ($this->storeresults && $this->storeresults != 'captcha-fail') echo '<p>'.JText::_('COM_HELKACAL_THANKS').'</p>';
	elseif ($this->storeresults == 'captcha-fail') {
		echo '<p>Wrong captcha!</p>';
	}
	else {
		// Fail, indicate this
		echo '<p>'.JText::_('COM_HELKACAL_FAIL').'</p>';
	}
}
else {
	echo '<div id="fb-root"></div>';
	echo '<script src="'.JUri::base().'components/com_helkacal/views/addevent/tmpl/fbevents.js"></script>';

	echo '
		<a href="#" id="open_fb_import">Tuo tiedot Facebook-tapahtumasta</a>
		<div id="fb_import">
			<fb:login-button scope="public_profile" onlogin="checkLoginState();">
			</fb:login-button>

			<div id="status">
			</div>

			<div id="fb-import-help" style="display: none;">Sy&ouml;t&auml; Facebook-tapahtuman osoite ja klikkaa "Hae".</div>

			<div id="fb-import-search-container">
				<input type="text" id="fb-event-id" placeholder="http://www.facebook.com/events/..." style="display: none;" /><input type="button" id="hae" value="Hae" style="display: none;" />
				<div id="errormessage" style="display: none;"></div>
			</div>
		</div>
	';

	echo '<form method="post" action="" id="helkacal_addevent" enctype="multipart/form-data">';
	global $admin, $koid;
	if (isset($_POST['articleid']) && $admin) {
		// Load article info for editing
		echo '
			<input type="hidden" name="articleid" value="'.$_POST['articleid'].'" />
			<input type="hidden" name="returnurl" value="'.$_POST['returnurl'].'" />
		';
		$db = JFactory::getDbo();
		$eventObject = new JTableEvent($db);
		$eventObject->koid = $koid;
		$eventObject->load($_POST['articleid']);
	}

	// Iterate through the normal form fieldsets and display each one.
	foreach ($this->form->getFieldsets('addEventFields') as $fieldsets => $fieldset) {

		echo '
			<fieldset>
		        <legend>'.JText::_('COM_HELKACAL_ADDEVENT_'.$fieldset->name).'</legend>
			<dl>
		';
		if ($fieldset->name == 'addEventDates') { // Event start and end times are displayed differently
			$datefields = $this->form->getFieldset($fieldset->name);
			if ($admin && isset($eventObject)) {
				// Editing object, add default values
				list($start_date, $start_clock) = explode(' ', $eventObject->start_time);
				$datefields['addEventFields_startday']->setValue($start_date);
				list($start_hour, $start_minute) = explode(':', $start_clock);
				$datefields['addEventFields_starthour']->setValue($start_hour);
				$datefields['addEventFields_startminute']->setValue($start_minute);

				list($end_date, $end_clock) = explode(' ', $eventObject->end_time);
				$datefields['addEventFields_endday']->setValue($end_date);
				list($end_hour, $end_minute) = explode(':', $end_clock);
				$datefields['addEventFields_endhour']->setValue($end_hour);
				$datefields['addEventFields_endminute']->setValue($end_minute);

				$datefields['addEventFields_allday']->setValue($eventObject->whole_day);
			}
			echo '
				<dt>'.$datefields['addEventFields_startday']->label.'</dt>
				<dd class="helkacal_time">'.$datefields['addEventFields_startday']->input.' '.JText::_('COM_HELKACAL_ADDEVENT_KLO').' '.$datefields['addEventFields_starthour']->input.$datefields['addEventFields_startminute']->input.'</dd>
				<dt>'.$datefields['addEventFields_endday']->label.'</dt>
				<dd class="helkacal_time">'.$datefields['addEventFields_endday']->input.' '.JText::_('COM_HELKACAL_ADDEVENT_KLO').' '.$datefields['addEventFields_endhour']->input.$datefields['addEventFields_endminute']->input.'</dd>
				<dt>'.$datefields['addEventFields_allday']->label.'</dt>
				<dd>'.$datefields['addEventFields_allday']->input.'</dd>
			';
		}
		else {
			// Iterate through the fields and display them.
			foreach($this->form->getFieldset($fieldset->name) as $field) {
				// Don't show image upload to admins since they can use JCE
				if ($admin && $field->id == 'addEventFields_eventImg') continue;
				if (!$admin && $field->id == 'addEventFields_featured') continue;

				// If the field is hidden, only use the input.
				if ($field->hidden) echo $field->input;
				elseif ($field->id == 'addEventFields_location') {
					// Let's try a different location...
					echo '<dt>'.$field->label.'</dt>';
					echo '<dd>

					<fieldset class="gllpLatlonPicker" id="custom_id">
						<input type="text" class="gllpSearchField">
						<input type="button" class="gllpSearchButton" value="'.JText::_('JSEARCH_FILTER_SUBMIT').'">
						<input type="checkbox" name="no_location" value="1"'.(($admin && isset($eventObject) && !$eventObject->getLocation('zoom')) ? ' checked="checked"' : '').'> '.JText::_('COM_HELKACAL_NO_LOCATION').'
						<br/><small>Kartta l&auml;hennet&auml;&auml;n sille et&auml;isyydelle (zoom), johon sen tallentaessasi j&auml;t&auml;t.</small>
						<br/>
						<div class="gllpMap">Google Maps</div>
						<input type="hidden" class="gllpLatitude" name="latitude" value="'.(($admin && isset($eventObject) && $eventObject->getLocation('latitude')) ? $eventObject->getLocation('latitude') : '60.17332440000001').'"/>
						<input type="hidden" class="gllpLongitude" name="longitude" value="'.(($admin && isset($eventObject) && $eventObject->getLocation('longitude')) ? $eventObject->getLocation('longitude') : '24.941024800000037').'"/>
						<input type="hidden" class="gllpZoom" name="zoom" value="'.(($admin && isset($eventObject) && $eventObject->getLocation('zoom')) ? $eventObject->getLocation('zoom') : '12').'"/>
						<input type="hidden" class="gllpDesc" name="locationdesc" value="'.(($admin && isset($eventObject) && $eventObject->getLocation('desc')) ? $eventObject->getLocation('desc') : '').'"/>
						'.JText::_('COM_HELKACAL_LOCATION_NAME').': <input type="text" name="locationname" '.(($admin && isset($eventObject) && $eventObject->getLocation('name')) ? 'value="'.$eventObject->getLocation('name').'"' : '').' id="location_street_address">
					</fieldset>

					</dd>';
				}
				elseif (!$admin && $field->id == 'addEventFields_eventDesc') {
					// Display simple textarea to visitors instead of JCE
					echo '
						<dt>'.$field->label.'</dt>
						<dd style="clear: both; margin: 0;"><textarea id="addEventFields_eventDesc" name="addEventFields[eventDesc]" style="width: 100%; height: 500px;"></textarea></dd>';
				}
				else {
					if ($admin && isset($eventObject)) {
						// Editing object, add default values
						switch ($field->id) {
							case 'addEventFields_eventName':
								$field->setValue($eventObject->title);
								break;
							case 'addEventFields_eventDesc':
								$field->setValue($eventObject->getDescription());
								break;
							case 'addEventFields_category':
								$field->setValue($eventObject->catid);
								break;
							case 'addEventFields_featured':
								$field->setValue($eventObject->featured);
								break;
							case 'addEventFields_location':
								if ($eventObject->location != 'null' && $eventObject->location) $field->setValue($eventObject->location);
								break;
							case 'addEventFields_url':
								if ($eventObject->url != 'null' && $eventObject->url) $field->setValue($eventObject->url);
								break;
							case 'addEventFields_tickets':
								if ($eventObject->tickets && $eventObject->tickets != 'null') $field->setValue($eventObject->tickets);
								break;
							case 'addEventFields_contact':
								if ($eventObject->contact != 'null' && $eventObject->contact) $field->setValue($eventObject->contact);
								break;
						}
					}
					echo '
						<dt>'.$field->label.'</dt>
						<dd'.(($field->type == 'Editor' || $field->type == 'Textarea') ? ' style="clear: both; margin: 0;"' : '').'>'.$field->input.($field->id == 'addEventFields_contact' ? '<small>Yhteystiedot n&auml;kyv&auml;t vain yll&auml;pidolle.</small>' : '').'</dd>';
				}
			}
		}
		echo '
			</dl>
			</fieldset>
	';
	}

	///////////////
	// reCAPTCHA //
	///////////////

	if (JFactory::getUser()->guest) {
		print '<div class="opensubmit_recaptcha">Kirjoita tekstikentt&auml;&auml;n kuvassa oleva teksti</div>';
		$publickey = "6Lfj1PMSAAAAAJpQWI6v27HoRYmpLgMesYOLOBOR";
		echo recaptcha_get_html($publickey);
	}
	// End of reCAPTCHA

	echo '<input type="submit" name="submit" value="'.JText::_('JSAVE').'" /></form>';
	if (isset($_POST['returnurl'])) echo '
		<form method="post" action="'.$_POST['returnurl'].'"><input type="submit" value="'.JText::_('JCANCEL').'" class="helkacal_cancel" /></form>
	';
}
