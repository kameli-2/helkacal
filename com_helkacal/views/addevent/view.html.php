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
class HelkaCalViewAddEvent extends JViewLegacy
{
	public function __construct($config = array()) {
		// Load the AddEvent-form in JForm for the template to use
		$this->form = &JForm::getInstance('addEventForm', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'addeventform.xml');
		$jinput = JFactory::getApplication()->input;
		$this->form->setFieldAttribute('category', 'parentid', $jinput->get('category', 0, 'INT'), 'addEventFields');
		$this->form->setFieldAttribute('category', 'categorydepth', $jinput->get('categorydepth', 2, 'INT'), 'addEventFields');
		parent::__construct($config);
	}

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
                //$this->events = $this->get('Events("2014-10-01", "2014-12-01")');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
			return false;
		}

		// If form is sent, process it. Else, show form.
		if (isset($_POST['submit'])) {
			$this->storeresults = $this->store();
			if (isset($_POST['returnurl'])) {
				header("Location: ".$_POST['returnurl']);
				exit;
			}
		}
		// Show template
		parent::display($tpl);
        }

	/**
	 * Store event details
	 *
	 * @return	mixed	false on failure, articleid on success.
	 */
	public function store() {
		global $admin, $koid;
		if (!isset($_POST['submit'])) return false;

		///////////////
		// reCAPTCHA //
		///////////////
		if (JFactory::getUser()->guest) {
			$privatekey = "6Lfj1PMSAAAAAJoag_aDaTNoZ-3mE4JYMwjfmAV5";
			$resp = recaptcha_check_answer ($privatekey,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]);
			if (!$resp->is_valid) { // What happens when the CAPTCHA was entered incorrect$
				return "captcha-fail";
			}
		}
		// End of reCAPTCHA

		// Create new event
		$db = JFactory::getDbo();
		$event = new JTableEvent($db);

		// Check if event is being edited
		if ($admin && isset($_POST['articleid'])) {
			$event->koid = $koid;
			$event->load($_POST['articleid']);
			$event->article_id = $_POST['articleid'];
		}

		// Insert even information in the object
		$event->title = strip_tags($_POST['addEventFields']['eventName']);
		$event->catid = $_POST['addEventFields']['category'];

		// Create alias if creating new event
		if (!isset($_POST['articleid'])) {
			$event->alias = JFilterOutput::stringURLSafe($event->title);
			$table = JTable::getInstance('Content', 'JTable');
			$i=0;
			while ($table->load(array('alias' => $event->alias, 'catid' => $event->catid))) {
				$i++;
				$event->alias = JFilterOutput::stringURLSafe($event->title.$i);
                	}
		}

		// Handle image
		if (isset($_FILES['addEventFields']) && !$_FILES['addEventFields']['error']['eventImg']) {
			if ($_FILES['addEventFields']['size']['eventImg'] < 8000000) {
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$uploadPath = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.'opensubmit'.DIRECTORY_SEPARATOR.time().'_'.preg_replace("/[^A-Za-z0-9\._\-]/", "_", $_FILES['addEventFields']['name']['eventImg']);
				if (!JFile::upload($_FILES['addEventFields']['tmp_name']['eventImg'], $uploadPath)) unset($uploadPath);
				$this->resizeImage($uploadPath);
			}
		}

		$desc = $_POST['addEventFields']['eventDesc'];
		$event->fulltext = '';
		// Non-admins use simple textarea, no editor. Insert <p>-elements.
		if (!$admin) {
			$bodyparts = explode("\n", $desc);
			$event->introtext = strip_tags(array_shift($bodyparts));
			if ($bodyparts) {
				foreach ($bodyparts as $bodypart) {
					if ($bodypart) $event->fulltext .= '<p>'.strip_tags($bodypart).'</p>';
				}
			}
		}
		elseif (strstr($desc, '<hr id="system-readmore" />')) list($event->introtext, $event->fulltext) = explode('<hr id="system-readmore" />', $desc);
		else $event->introtext = $desc;

		// If image is uploaded, add it to the introtext
		if (isset($uploadPath)) $event->introtext = '<img src="'.strstr($uploadPath,'images'.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR).'" alt="'.$_FILES['addEventFields']['name']['eventImg'].'" style="margin:0.5em;float:left;">'.$event->introtext;

		$event->start_time = strip_tags($_POST['addEventFields']['startday'].' '.$_POST['addEventFields']['starthour'].':'.$_POST['addEventFields']['startminute'].':00');
		if (!isset($_POST['addEventFields']['endday']) || $_POST['addEventFields']['endday'] == '') $_POST['addEventFields']['endday'] = $_POST['addEventFields']['startday'];
		$event->end_time = strip_tags($_POST['addEventFields']['endday'].' '.$_POST['addEventFields']['endhour'].':'.$_POST['addEventFields']['endminute'].':00');
		if (strtotime($event->end_time) < strtotime($event->start_time)) $event->end_time = $event->start_time;
		if (isset($_POST['addEventFields']['allday']) && $_POST['addEventFields']['allday']) $event->whole_day = 1; else $event->whole_day = 0;
		if (isset($_POST['addEventFields']['children']) && $_POST['addEventFields']['children']) $event->children = 1; else $event->children = 0;
		if (isset($_POST['addEventFields']['chargeable']) && $_POST['addEventFields']['chargeable']) $event->chargeable = 1; else $event->chargeable = 0;

		$event->publish_down = substr($event->realEndTime(), 0, 10).' 23:59:59';

		// Location
		if (isset($_POST['no_location']) && $_POST['no_location']) $event->location = 'null';
		elseif (!$_POST['latitude'] || !$_POST['longitude'] || !$_POST['zoom']) $event->location = 'null';
		else $event->location = strip_tags(str_replace('|*|', ' ', $_POST['latitude']).'|*|'.str_replace('|*|', ' ', $_POST['longitude']).'|*|'.str_replace('|*|', ' ', $_POST['zoom']).'|*|'.str_replace('|*|', ' ', $_POST['locationdesc']).'|*|'.str_replace('|*|', ' ', $_POST['locationname']));

		if (isset($_POST['addEventFields']['featured']) && $_POST['addEventFields']['featured']) $event->featured = 1; else $event->featured = 0;

		$event->url = strip_tags($_POST['addEventFields']['url']);
//		$event->tickets = $_POST['addEventFields']['tickets'];
		$event->contact = strip_tags($_POST['addEventFields']['contact']);
		$event->language = "*";
		$event->access = 1;

		// store event
		$result = $event->store();
		if (!$result) return false;
		$this->sendNotificationEmail();
		return $event->getArticleId();
	}

	function sendNotificationEmail() {
		global $admin;
		if ($admin) return false; // Send email only if event was created by a visitor

		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();

		// Add recipients from koconde
		$jdb = JFactory::getDBO();
		$cdb = trim($config->get( 'db' ));
		if (file_exists('/var/www/globalconfig/globaldatabase')) {
			$gdb = trim(file_get_contents('/var/www/globalconfig/globaldatabase'));
		}
		$query = "SELECT email,name FROM ".$jdb->quoteName($gdb).".jos_koconde_contacts con ".
		"JOIN ".$jdb->quoteName($gdb).".jos_joomla_db_link lnk ON (lnk.koid = con.ko) ".
		"WHERE lnk.db = ".$jdb->quote($cdb)." ".
		"AND con.role = \"publiccontact\" ".
		"ORDER BY con.ispublic ASC";
		$jdb->setQuery($query);
		$jdb->query();
		$entries = $jdb->loadAssocList();
		foreach ($entries as $entry) {
			$mailer->addRecipient($entry['email']);
		}

		$sender = array(
			$config->get( 'mailfrom' ),
			$config->get( 'fromname' )
		);
		$site = $config->get('sitename');

		$mailer->setSender($sender);

		$mailer->setSubject($site.': Tapahtumakalenteriin on lähetetty uusi tapahtuma');

	        $jinput = JFactory::getApplication()->input;
	        $itemid = $jinput->get('calendarurl', '');
        	if ($itemid) {
			$calendarurl = parse_url(JURI::base(), PHP_URL_SCHEME).'://'.parse_url(JURI::base(), PHP_URL_HOST).JRoute::_('index.php?option=com_helkacal&Itemid='.$itemid);
		}
		else $calendarurl = "";

		$body = "Tämä on automaattinen ilmoitus.\n\n";
		$body .= "Sivuston $site tapahtumakalenteriin on lähetetty uusi tapahtuma otsikolla \"".strip_tags($_POST['addEventFields']['eventName'])."\".\nTapahtuma odottaa tarkastusta.\n\n";
		$body .= "Voit tarkastaa, ja julkaista tai poistaa tapahtuman ";
		if ($calendarurl) $body .= "osoitteesta\n\n".$calendarurl;
		else $body .= "sivuston tapahtumakalenterista.";
		$mailer->setBody($body);
		$send =& $mailer->Send();
		if ( $send !== true ) {
			return false;
			//echo 'Error sending email: ' . $send->message;
		} else {
			return true;
			//echo 'Mail sent';
		}
	}

	function resizeImage($imgpath) {
		// Get file extension
		$extension = strtolower(array_pop(explode(".", $imgpath)));
		if ($extension == "jpg" || $extension == "jpeg") $src_image = imagecreatefromjpeg($imgpath);
		elseif ($extension == "png") $src_image = imagecreatefrompng($imgpath);
		elseif ($extension == "gif") $src_image = imagecreatefromgif($imgpath);
		else return false;

		// Calculate thumbnail size
		list($src_w, $src_h) = getimagesize($imgpath);
		$maxwidth = 900;
		$maxheight = 6400;
		$dst_w = $maxwidth;
		$dst_h = $src_h * ($dst_w / $src_w);
		if ($dst_h > $maxheight) {
				$dst_h = $maxheight;
				$dst_w = $src_w * ($dst_h / $src_h);
		}

		// Create thumbnail
		$dst_image = imagecreatetruecolor($dst_w, $dst_h);
		imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		if ($extension == "jpg" || $extension == "jpeg") imagejpeg($dst_image, $imgpath);
		elseif ($extension == "png") imagepng($dst_image, $imgpath);
		else imagegif($dst_image, $imgpath);
	}
}

?>
