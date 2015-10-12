<?php

/**
 * JTableEvent extends JTableContent (Joomla! articles, basically) to create
 * events with start time, ending time, etc.
 *
 * Events are saved in a shared database. This way they can be easily used
 * to create an event calendar which contains events from all Joomla! instances.
 */

class JTableEvent extends JTableContent {
	public $title;
	public $introtext;
	public $fulltext;
	public $start_time;
	public $end_time;
	public $whole_day;
	public $location;
	public $url;
	public $tickets;
	public $contact;
	public $koid;
	public $created;
	public $alias;
	public $catid;
	public $id;
	public $state;
	public $article_id;
	public $access;
	public $language;
	public $featured;
	public $publish_down;
	public $children;
	public $chargeable;
	public $showdate;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct(JDatabaseDriver $db) {
		global $shareddb, $sharedtbl, $sharedlinktbl, $koid, $admin;

		parent::__construct($db);

		// Fetch columns from eventtable
		$query = $this->_db->getQuery(true)
                        ->select($this->_db->quoteName('column_name'))
                        ->from($this->_db->quoteName('information_schema').'.'.$this->_db->quoteName('columns'))
			->where($this->_db->quoteName('table_schema') . ' = ' . $this->_db->quote($shareddb) . ' AND ' . $this->_db->quoteName('table_name') . ' = ' . $this->_db->quote($sharedtbl));
		$this->_db->setQuery($query);
		$columns = $this->_db->loadColumn();

		// Initiate variables for columns
		foreach ($columns as $field) {
			if (!property_exists($this, $field)) $this->$field = NULL;
		}

		$this->koid = $koid;
		if ($admin) $this->state = 1;
		else $this->state = 0;
		$this->showdate = true;
	}

	/**
	 * Overrides JTableContent::store. Stores event info.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false) {
		global $shareddb, $sharedtbl, $sharedlinktbl, $koid;

		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey()) {
			$fields = array(
				$this->_db->quoteName('start_time') . ' = ' . $this->_db->quote($this->start_time),
				$this->_db->quoteName('end_time') . ' = ' . $this->_db->quote($this->end_time),
				$this->_db->quoteName('whole_day') . ' = ' . $this->whole_day,
				$this->_db->quoteName('location') . ' = ' . $this->_db->quote($this->location),
				$this->_db->quoteName('url') . ' = ' . $this->_db->quote($this->url),
				$this->_db->quoteName('tickets') . ' = ' . $this->_db->quote($this->tickets),
				$this->_db->quoteName('contact') . ' = ' . $this->_db->quote($this->contact),
				$this->_db->quoteName('chargeable') . ' = ' . $this->_db->quote($this->chargeable),
				$this->_db->quoteName('children') . ' = ' . $this->_db->quote($this->children)
			);
			$query = $this->_db->getQuery(true)
				->update($this->_db->quoteName($shareddb).'.'.$this->_db->quoteName($sharedtbl))
				->set($fields)
				->where($this->_db->quoteName('article_id').' = '.$this->article_id .' AND '.$this->_db->quoteName('koid').' = '.$this->_db->quote($this->koid));
			$this->_db->setQuery($query);
			$this->_db->execute();

                        /*
                         * Since parent::store() tries to store all of the objects properties
                         * to #__content, we have to set free those properties that we don't
                         * want to store there. But we will need those properties later, so
                         * let's save them as temporary local variables first.
                         */
                        $columns = array(
                                'article_id' => $this->article_id,
                                'start_time' => $this->start_time,
                                'end_time' => $this->end_time,
                                'whole_day' => $this->whole_day,
                                'location' => $this->location,
                                'url' => $this->url,
                                'tickets' => $this->tickets,
                                'contact' => $this->contact,
                                'koid' => $this->koid,
                                'children' => $this->children,
                                'chargeable' => $this->chargeable
                        );

                        // Free properties
                        $this->article_id = null;
                        $this->start_time = null;
                        $this->end_time = null;
                        $this->whole_day = null;
                        $this->location = null;
                        $this->url = null;
                        $this->tickets = null;
                        $this->contact = null;
                        $this->koid = null;
                        $this->chargeable = null;
                        $this->children = null;
			$this->showdate = null;

			$this->id = $columns['article_id'];

			$r = parent::store($updateNulls);

                        // return properties
                        $this->article_id = $columns['article_id'];
                        $this->start_time = $columns['start_time'];
                        $this->end_time = $columns['end_time'];
                        $this->whole_day = $columns['whole_day'];
                        $this->location = $columns['location'];
                        $this->url = $columns['url'];
                        $this->tickets = $columns['tickets'];
                        $this->contact = $columns['contact'];
                        $this->koid = $columns['koid'];
                        $this->children = $columns['children'];
                        $this->chargeable = $columns['chargeable'];

			return $r;
		}
		else {

			/*
			 * Since parent::store() tries to store all of the objects properties
			 * to #__content, we have to set free those properties that we don't
			 * want to store there. But we will need those properties later, so
			 * let's save them as temporary local variables first.
			 */
			$columns = array(
				'article_id' => $this->article_id,
				'start_time' => $this->start_time,
				'end_time' => $this->end_time,
				'whole_day' => $this->whole_day,
				'location' => $this->location,
				'url' => $this->url,
				'tickets' => $this->tickets,
				'contact' => $this->contact,
				'koid' => $this->koid,
				'children' => $this->children,
				'chargeable' => $this->chargeable
			);

			// Free properties
			$this->article_id = null;
			$this->start_time = null;
			$this->end_time = null;
			$this->whole_day = null;
			$this->location = null;
			$this->url = null;
			$this->tickets = null;
			$this->contact = null;
			$this->koid = null;
			$this->children = null;
			$this->chargeable = null;
			$this->showdate = null;

			if (!parent::store()) return false;

			// return properties
			$this->article_id = $columns['article_id'];
			$this->start_time = $columns['start_time'];
			$this->end_time = $columns['end_time'];
			$this->whole_day = $columns['whole_day'];
			$this->location = $columns['location'];
			$this->url = $columns['url'];
			$this->tickets = $columns['tickets'];
			$this->contact = $columns['contact'];
			$this->koid = $columns['koid'];
			$this->chargeable = $columns['chargeable'];
			$this->children = $columns['children'];

			$query = $this->_db->getQuery(true)
				->insert($this->_db->quoteName($shareddb).'.'.$this->_db->quoteName($sharedtbl))
				->columns(array_keys($columns))
				->values($this->getArticleId().', '.$this->_db->quote($this->start_time).', '.$this->_db->quote($this->end_time).', '.$this->whole_day.', '.$this->_db->quote($this->location).', '.$this->_db->quote($this->url).', '.$this->_db->quote($this->tickets).', '.$this->_db->quote($this->contact).', '.$this->_db->quote($koid).', '.$this->children.', '.$this->chargeable);
			$this->_db->setQuery($query);
			return $this->_db->execute();
		}
	}

	/**
	 * Overrides JTable::load(). Loads event information and binds it.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an a$
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new r$
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @link    http://docs.joomla.org/JTable/load
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true) {
		global $shareddb, $sharedtbl, $sharedlinktbl, $koid;

		// Load article content
		$result = parent::load($keys, $reset);
		if ($result === false) return false;

		// No need to load anything if there's no primary key
		if (!$this->hasPrimaryKey()) return true;

		// Load event content
		$query = $this->_db->getQuery(true)
                        ->select('*')
                        ->from($this->_db->quoteName($shareddb).'.'.$this->_db->quoteName($sharedtbl))
			->where($this->_db->quoteName('article_id') . ' = ' . $this->getArticleId() .' AND '.$this->_db->quoteName('koid').' = '.$this->_db->quote($this->koid));
		$this->_db->setQuery($query);
		$row = $this->_db->loadAssoc();
		// Bind event content to this object
		if ($row != null) return $this->bind($row);
	}

	/**
	 * Returns ID of the article.
	 *
	 * @return	int		Article id.
	 *
	 */
	public function getArticleId() {
		$article_id = $this->getPrimaryKey();
		if (!$article_id) return 0;
		$article_id = array_shift($article_id);
		if (!$article_id) return 0;
		return $article_id;
	}

	/**
	 * Overrides JTable::delete. Deletes event.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the insta$
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/delete
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function delete($pk = null) {
		global $shareddb, $sharedtbl, $sharedlinktbl, $koid;

		if (!parent::delete($pk)) return false;
		$query = $this->_db->getQuery(true)
                        ->delete($this->_db->quoteName($shareddb).'.'.$this->_db->quoteName($sharedtbl))
						->where($this->_db->quoteName('article_id') . ' = ' . $this->getArticleId() .' AND '.$this->_db->quoteName('koid').' = '.$this->_db->quote($this->koid));
		$this->_db->setQuery($query);
		return $this->_db->execute();
	}

	public function __set($property, $value) {
		if (property_exists($this, $property)) $this->$property = $value;
		return $this;
	}

	function categoryname() {
		// Get category name from catid
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('title'))
			->from($this->_db->quoteName('#__categories'))
			->where($this->_db->quoteName('id') . ' = ' . $this->catid);
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	function categorysymbol($type = null) {
		if (!$type) $type = $this->categoryname();
		$imgpath = 'img'.DIRECTORY_SEPARATOR.'symbols'.DIRECTORY_SEPARATOR.JFilterOutput::stringURLSafe($type).'.png';
		if (file_exists(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_helkacal'.DIRECTORY_SEPARATOR.$imgpath)) return '<img class="helkacal_category_symbol" src="'.JURI::base().'/components/com_helkacal/'.$imgpath.'" alt="'.$type.'" title="'.$type.'">';
		return '<div class="helkacal_category_symbol">'.$type.'</div>';
	}

	function onedayevent() {
		// Figure out whether event lasts one day or more
		if (strtotime($this->start_time) > strtotime($this->end_time)) return true;
		if (substr($this->start_time, 0, 10) == substr($this->end_time, 0, 10)) return true;
		return false;
	}

	function withinonemonth() {
		// Figure out whether event starts and ends in same month
		if (substr($this->start_time, 0, 7) == substr($this->end_time, 0, 7)) return true;
		return false;
	}

	function withinoneyear() {
		// Figure out whether event starts and ends in same month
		if (substr($this->start_time, 0, 4) == substr($this->end_time, 0, 4)) return true;
		return false;
	}

	function realEndTime() {
		// Show real end time
		$realendtime = $this->start_time;
		if (strtotime($this->start_time) < strtotime($this->end_time)) $realendtime = $this->end_time;
		if ($this->whole_day) $realendtime = date("Y-m-d H:i:s", strtotime(substr($realendtime, 0, 10))+60*60*24);
		return $realendtime;
	}

	function eventtime($eventpage = false) {
		// Show event time in a sensible format
		$r = '';
		if ($this->start_time == '0000-00-00 00:00:00') return '';
		if (!$this->onedayevent()) {
			$r .= date("j.", strtotime($this->start_time));
			if (!$this->withinonemonth()) $r .= date("n.", strtotime($this->start_time));
			$r .= date("-j.n.", strtotime($this->end_time));
			return $r;
		}
		elseif ($eventpage) {
			$r .= date("j.n.", strtotime($this->start_time));
		}
		if ($this->whole_day) return $r;
		$r .= ' '.JText::_('COM_HELKACAL_KLO').' ';
		$r .= date("G", strtotime($this->start_time));
		if (date("i", strtotime($this->start_time)) != '00' || date("i", strtotime($this->end_time)) != '00') $r .= date(":i", strtotime($this->start_time));
		if (strtotime($this->start_time) < strtotime($this->end_time)) {
			$r .= date("-G", strtotime($this->end_time));
			if (date("i", strtotime($this->start_time)) != '00' || date("i", strtotime($this->end_time)) != '00') $r .= date(":i", strtotime($this->end_time));
		}
		return trim($r, " ");
	}

	function eventtime_fp() {
		if ($this->start_time == '0000-00-00 00:00:00') return '';
		if ($this->onedayevent()) return date("d/m/Y", strtotime($this->start_time));
		if ($this->withinonemonth()) return date("d", strtotime($this->start_time)).'-'.date("d/m/Y", strtotime($this->end_time));
		if ($this->withinoneyear()) return date("d/m", strtotime($this->start_time)).' - '.date("d/m/Y", strtotime($this->end_time));
		return date("d/m/Y", strtotime($this->start_time)).' - '.date("d/m/Y", strtotime($this->end_time));
	}

	function fixurl($url) {
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}
		if (filter_var($url, FILTER_VALIDATE_URL)) return $url;
		return false;
	}

	function shortUrl($url) {
		// Shorten url if too long
		if (substr($url, 0, 11) == 'http://www.') $url = substr($url, 7);
		if (strlen($url) < 50) return $url;

		$parsed_url = parse_url($this->fixurl($url));
		$newurl = '';
		if (isset($parsed_url['scheme'])) $newurl = $parsed_url['scheme'].'://';
		if (isset($parsed_url['host'])) $newurl .= $parsed_url['host'];
		if (isset($parsed_url['path'])) {
			$pathdirs = explode("/", trim($parsed_url['path'], '/'));
			if (count($pathdirs) > 1) $newurl .= '/...';
			$lastdir = array_pop($pathdirs);
			$newurl .= '/'.$lastdir;
		}

		return $newurl;
	}

	function urlToEvent() {
		global $itemid;
		if (!isset($itemid)) {
			$itemid = JSite::getMenu()->getActive()->id;
		}
		$parsed_url = parse_url(JURI::base());
		if (!$parsed_url) return false;
		$url = $parsed_url['scheme'].'://'.$parsed_url['host'];
		$url .= JRoute::_('index.php?option=com_helkacal&Itemid='.$itemid.'&event='.$this->id);

//		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//		if (strstr($url, '?')) $url .= '&';
//		else $url .= '?';
//		$url .= 'event='.$this->id;
//		$url = JRoute::_($url);
		return $url;
	}

	function __toString() {
		global $admin;
		if ($this->state != 1 && !$admin) return '';
		$r = '
			<div class="helkacal_event helkacal_category'.$this->catid.'">
				<div class="helkacal_date"><a href="'.$this->urlToEvent().'"><span class="helkacal_dateimglink"></span></a>';
		if ($this->start_time != '0000-00-00 00:00:00' && $this->showdate) $r .= '
					<div class="helkacal_datecontainer">
						<div class="helkacal_smallmonth">'.JText::_(date("F", strtotime($this->start_time)).'_SHORT').'</div>
						<div class="helkacal_day">'.date("d", strtotime($this->start_time)).'</div>
					</div>';
		$r .= '		</div>
				<div class="helkacal_titlecontainer">
					<div class="helkacal_title"><a href="'.$this->urlToEvent().'">'.$this->title.'</a></div>';
		if ($this->eventtime() != '') $r .= '
					<div class="helkacal_eventtime"><img src="components/com_helkacal/img/time.png" alt="Time">'.$this->eventtime().'</div>';
		if ($this->getStreetAddress()) $r .= '
					<div class="helkacal_location"><img src="components/com_helkacal/img/location.png" alt="Location">'.$this->printLocation().'</div>';

		$r .= '
				</div>
				<div class="helkacal_categorycontainer">
				<div class="helkacal_category">'.$this->categorysymbol().'</div>'
				.(($this->children) ? '<div class="helkacal_category">'.$this->categorysymbol("Lapset").'</div>' : '')
				.(($this->chargeable) ? '<div class="helkacal_category">'.$this->categorysymbol("Maksullinen").'</div>' : '');

		$r .= '</div><div class="helkacal_eventdesc">';

		if ($this->introtext != '' && $this->introtext != 'null') $r .= '<div class="helkacal_introtext">'.$this->introtext.'</div>';
//		if ($this->fulltext != '' && $this->fulltext != 'null') $r .= '<p class="readmore"><a href="'.$this->urlToEvent().'">'.JText::_('COM_CONTENT_READ_MORE_TITLE').'</a></p>';
//		if ($this->url != '' && $this->url != 'null' && $this->fixurl($this->url)) $r .= '<div class="helkacal_url"><a href="'.$this->fixurl($this->url).'" target="_blank">'.$this->shortUrl($this->url).'</a></div>';
//		if ($this->tickets != '' && $this->tickets != 'null' && $this->fixurl($this->tickets)) $r .= '<div class="helkacal_tickets">'.JText::_('COM_HELKACAL_EVENT_TICKETS_LABEL').': <a href="'.$this->fixurl($this->tickets).'" target="_blank">'.$this->tickets.'</a></div>';
//		if ($this->getLocation('longitude')) $r .= $this->getLocationMap();
		if ($admin) {
			if ($this->contact != '' && $this->contact != 'null') $r .= '<div class="helkacal_contactinfo"><img src="components/com_helkacal/img/contact.png" alt="Contact">'.$this->contact.'</div>';
			$r .= '<div class="helkacal_publishbuttons">';
			if ($this->state != 1) $r .= '<div class="helkacal_publishbuttoncontainer"><div class="helkacal_publish" id="publish_'.$this->article_id.'"></div>'.JText::_('COM_HELKACAL_EVENT_PUBLISH').'</div>';
			$r .= '<div class="helkacal_publishbuttoncontainer"><div class="helkacal_edit" id="edit_'.$this->article_id.'"></div>'.JText::_('JGLOBAL_EDIT').'</div>';
			if ($this->state != -2) $r .= '<div class="helkacal_publishbuttoncontainer"><div class="helkacal_delete" id="delete_'.$this->article_id.'"></div>'.JText::_('COM_HELKACAL_EVENT_DELETE').'</div>';
			$r .= '</div>';
		}

		$r .= '</div>';

		$jinput = JFactory::getApplication()->input;
		$socialmedia = $jinput->get('socialmedia', '1', 'INTEGER');
		if ($socialmedia) $r .= $this->getSocialMedia();

		$r .= '</div>';

		return $r;
	}

	function getSocialMedia() {
		$r = '';
		$r .= '<div class="helkacal_some">';

		// FACEBOOK
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");

		$r .= '<div class="helkacal_facebook"><a href="https://www.facebook.com/sharer/sharer.php?u='.$this->urlToEvent().'" target="_blank">'
			.'<img class="fb-logo" src="images/facebookx.png" alt="facebook logo"/></a>';

		if (isset($_GET['event'])) {
			$r .= '<div class="helka-facebook"><p>';

			$url = 'http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls='.$this->urlToEvent();
			$cache = $memcache->get($url);
		        if(!$cache){
       			        $json_string = file_get_contents($url);
       	        		$memcache->add($url, $json_string, 0, 3600);
		        } else {
       			        $json_string = $cache;
	        	}
	       		$data = json_decode($json_string);
		        if(is_array($data)){
       			    $json=$data[0];
	        	    if(!is_null($json->total_count)){
        	       		$r .= $json->total_count;
        		    }
		        }
			$r .= '</p></div>';
		}
		$r .= '</div>';

		// TWITTER
		$r .= '<div class="helkacal_twitter"><a target="_blank" href="https://twitter.com/share?text='.$this->title.'&url='.$this->urlToEvent().'">'
			.'<img class="twitter-logo" src="images/twitterx.png" alt="twitter logo"/></a>';

		if (isset($_GET['event'])) {
			$r .= '<div class="helka-twitter"><p>';
			$url = 'http://urls.api.twitter.com/1/urls/count.json?url='.$this->urlToEvent();
			$cache = $memcache->get($url);
			if (!$cache) {
				$json_string = file_get_contents($url);
				$memcache->add($url, $json_string, 0, 3600);
			} else {
				$json_string = $cache;
			}
			$json = json_decode($json_string);
			if (!is_null($json->count)) {
				$r .= $json->count;
			}
			$r .= '</p></div>';
		}

		$r .= '</div>';
		$r .= '</div>';

		return $r;
	}

	function getSocialMediaComment() {
		$r = '
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/fi_FI/sdk.js#xfbml=1&appId=579339135497873&version=v2.0";
			fjs.parentNode.insertBefore(js, fjs);
			}(document, \'script\', \'facebook-jssdk\'));</script>
		<div class="fb-comments" data-href="'.$this->urlToEvent().'" data-width="100%" data-numposts="5" data-colorscheme="light"></div>
		';
		return $r;
	}

	function getDescription() {
		$r = $this->introtext;
		if ($this->introtext && $this->fulltext) $r .= '<hr id="system-readmore" />';
		$r .= $this->fulltext;
		return $r;
	}

	function getFirstImg() {
		$html = str_get_html($this->getDescription());
		if (!$html) return null;
		$img = $html->find('img', 0);
		if (!$img) return null;
		$src = $img->src;
		if (substr($src, 0, 4) != "http") $src = JUri::base().$src;
		return $src;
	}

	// Get location stuff
	function getLocation($attr) {
		if (!$this->location || $this->location == 'null') return false;
//		if ($attr == 'desc') return $this->getStreetAddress();
//		list($latitude, $longitude, $zoom, $desc) = explode("|*|", $this->location);
		$locationinfo = explode("|*|", $this->location);
		if (isset($locationinfo[0])) $latitude = $locationinfo[0];
		if (isset($locationinfo[1])) $longitude = $locationinfo[1];
		if (isset($locationinfo[2])) $zoom = $locationinfo[2];
		if (isset($locationinfo[3])) {
			$desc = $locationinfo[3];
			$name = $desc;
		}
		if (isset($locationinfo[4])) $name = $locationinfo[4];
		if (!isset($$attr)) return false;
		return $$attr;
	}
	function getStreetAddress() {
/*		if (!$this->getLocation('latitude')) return false;
		$language = JFactory::getLanguage()->getTag();
		$json = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.$this->getLocation('latitude').','.$this->getLocation('longitude').'&sensor=true&language='.$language);
		$object = json_decode($json);
		if (!is_object($object)) return false;
		if (!property_exists($object, 'results')) return false;
		if (!isset($object->results[0])) return false;
		if (!is_object($object->results[0])) return false;
		return $object->results[0]->address_components[1]->long_name.' '.$object->results[0]->address_components[0]->long_name.', '.$object->results[0]->address_components[4]->long_name.' '.$object->results[0]->address_components[2]->long_name;
*/
		return $this->getLocation('desc');
	}

	function printLocation($print = false) {
		if (!$this->getLocation('name')) return false;
		$r = '';
		if ($this->getLocation('desc')) $r = '<a href="http://maps.google.com?q='.$this->getStreetAddress().'" target="_blank">';
		if ($this->getLocation('name') != $this->getLocation('desc') && $this->getLocation('name')) $r .= '<span class="helkacal_location_name">';
		$r .= $this->getLocation('name');
		if ($this->getLocation('name') != $this->getLocation('desc') && $this->getLocation('name')) $r .= '</span>';
		if ($this->getLocation('name') != $this->getLocation('desc') && $this->getLocation('desc')) {
			if (!$print) $r .= ', ';
			$r .= $this->getLocation('desc');
		}

		$jinput = JFactory::getApplication()->input;
		$postalcode = $jinput->get('postalcode', '0', 'INTEGER');
		if ($postalcode != 1) $r = substr($r, 0, strrpos($r, ','));

//		if ($this->getLocation('name') != $this->getLocation('desc') && $this->getLocation('desc')) $r .= ')';

		if ($this->getLocation('desc')) $r .= '</a>';
		return $r;
	}

	function getLocationMap() {
		if ($this->getStreetAddress()) return '
		<div class="helkacal_location_map">
                        <iframe src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCTtvYZRh326102Ia58c7-xk6Mjqes2Jl0&q='.str_replace(" ", "+", $this->getStreetAddress()).'&zoom='.$this->getLocation('zoom').'"
                                width="100%" height="200" frameborder="0" style="border:0;">
                        </iframe>
                </div>';
	}

}

?>
