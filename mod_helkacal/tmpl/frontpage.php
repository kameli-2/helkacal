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
$document->addStyleSheet($modulePath.'tmpl/frontpage.css');
$document->addScript($modulePath.'tmpl/js/quickpager.jquery.js');
$document->addScript($modulePath.'tmpl/frontpage.js');

if (strstr($calendarurl, '?')) $calendarurl .= '&';
else $calendarurl .= '?';
?>
<div class="category-module-container" style="background-color:<?php echo $params->get('bgcolor', '#FFFFFF');?>;">
	<script type="text/javascript">
		var pages = <?php echo $params->get('pages', 8); ?>;
	</script>

	<?php if($params->get('title', '') != '') {?>
		<div class="blog-title">
			<h1><a href="<?php echo substr($calendarurl, 0, -1); ?>"><?php echo $params->get('title', '');?></a></h1>
		</div>
	<?php } ?>

	<div id="container" class="blog-featured category-module<?php echo $moduleclass_sfx; ?>">
		<?php foreach ($events as $event) {
			if (strtotime($event->start_time)+60*60*24 < time()) continue;
			if (strtotime($event->realEndTime())+60*60*4 < time()) continue;
			printEvent($event, $calendarurl, $params);
		}
		foreach ($events as $event) {
			if (strtotime($event->start_time)+60*60*24 > time()) continue;
			if (strtotime($event->realEndTime())+60*60*4 < time()) continue;
			printEvent($event, $calendarurl, $params);
		}
		?>
<?php
function printEvent($event, $calendarurl, $params) {
	echo '<div class="items-row">
		<div class="item">';
	$domdoc = new DOMDocument();
	@$domdoc->loadHTML($event->introtext);
	$tags = $domdoc->getElementsByTagName('img');
	$tag = $tags->item(0);

	if (isset($tag)) {
		if (strpos($tag->getAttribute('src'), "http") !== FALSE && !strpos($tag->getAttribute('src'), "www") !== FALSE) {
			echo '<div class="mod_helkacal_imagecontainer helkacal_bgimage" style="background-image: url('.$tag->getAttribute('src').');" alt="">';
	    	}
		else {
			echo '<div class="mod_helkacal_imagecontainer helkacal_bgimage" style="background-image: url('.JURI::root().$tag->getAttribute('src').');" alt="">';
	    	}
	}
	else {
		echo '<div class="mod_helkacal_imagecontainer mod_helkacal_category-'.$event->catid.'">';
	}

	echo '<a href="'.$calendarurl.'event='.$event->id.'"><span class="helkacal_dateimglink"></span></a>';

	if ($event->start_time != '0000-00-00 00:00:00' && strtotime($event->start_time)+60*60*24 > time()) {
		echo '
			<div class="helkacal_datecontainer">
                        	<div class="helkacal_smallmonth">'.JText::_(date("F", strtotime($event->start_time)).'_SHORT').'</div>
                                <div class="helkacal_day">'.date("d", strtotime($event->start_time)).'</div>
			</div>';

	}

	echo '</div>';

	echo '<div class="article-info">';

//	if ($event->printLocation()) echo '<div class="mod-articles-category-category"><img src="components/com_helkacal/img/location.png" alt="Location">'.$event->printLocation().'</div>';

	echo '<div class="mod-articles-category-category"><a href="'.$calendarurl.'chosencategory='.$event->catid.'">'.$event->categoryname().'</a></div>';

	if ($event->eventtime_fp()) echo '<div class="mod-articles-category-date modified">'.$event->eventtime_fp().'</div>';

/*	if ($event->start_time != '0000-00-00 00:00:00') {
		echo '<div class="mod-articles-category-date modified">'.date("d/m", strtotime($event->start_time));
		if (strtotime($event->end_time) > strtotime($event->start_time) && date("d/m/Y", strtotime($event->start_time)) != date("d/m/Y", strtotime($event->end_time))) echo '-'.date("d/m", strtotime($event->end_time));
		echo '</div>';
	}
*/
	echo '</div>';



	echo '<h2><a class="mod-articles-category-title" href="'.$calendarurl.'event='.$event->id.'">'.$event->title.'</a></h2>';

	if ($params->get('show_introtext')) {
		echo '<p class="mod-articles-category-introtext">'.substr(strip_tags($event->introtext), 0, $params->get('introtext_limit'));
		if (substr(strip_tags($event->introtext), 0, $params->get('introtext_limit')) != strip_tags($event->introtext)) echo '...';
		echo '</p>';
	}

	if ($params->get('show_readmore')) {
		echo '<p class="mod-articles-category-readmore readmore"><a class="mod-articles-category-title" href="'.$calendarurl.'event='.$event->id.'">'.rtrim(JText::_('COM_CONTENT_READ_MORE_TITLE'), '.').'</a></p>';
	}

	echo '</div></div>';
}

echo '
	</div>';


echo '<div class="mod_helkacal_read_all"><a href="'.substr($calendarurl, 0, -1).'">Katso kaikki</a></div>';

//	<div class="pagination"></div>
echo '</div>';

?>
