<?php
 
// No direct access to this file
defined('_JEXEC') or die;
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
* HTML View class for the HelkaCal Component
*
* @since 0.0.1
*/
class HelkaCalViewCalendar extends JViewLegacy
{
        /**
         * Display the Calendar view
         *
         * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
         *
         * @return  void
         */
        public function display($tpl = null)
        {
                // Assign data to the view
		$jinput = JFactory::getApplication()->input;
		// $past: 1 = upcoming, 2 = past
		$past = $jinput->get('past', array(1), 'ARRAY');

		if (isset($_GET['event'])) $this->event = $this->getModel()->getEvent($_GET['event']);
		elseif (isset($_GET['date'])) $this->events = $this->getModel()->getFutureEvents("2014-01");
		elseif (!in_array(2, $past)) $this->events = $this->getModel()->getFutureEvents();
		else $this->events = $this->getModel()->getFutureEvents("2014-01");

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
			return false;
		}

		// Display the view
		parent::display($tpl);
        }
}

?>
