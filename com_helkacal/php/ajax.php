<?php
include(substr(__DIR__, 0, strpos(__DIR__, 'com_helkacal'.DIRECTORY_SEPARATOR.'php')).'com_helkakg'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'header.php');

$user =& JFactory::getUser();
// Use the com_content component's access control
if (!$user->authorise('core.edit', 'com_content')) exit('Access denied.');

if (!isset($_GET['action'])) exit('No action.');
$action = $_GET['action'];

$db = JFactory::getDbo();

switch ($action) {
	case 'changeState':
		if (!isset($_GET['state']) || !isset($_GET['articleid'])) exit('No state or articleid.');
		$state = $_GET['state'];
		$articleid = $_GET['articleid'];
		$query = $db->getQuery(true)
			->update($db->quoteName('#__content'))
			->set($db->quoteName('state').' = '.$state)
			->where($db->quoteName('id').' = '.$articleid);
		$db->setQuery($query);
		$result = $db->execute();

		if ($result) exit('true');
		else exit($db->getErrorMsg());

		break;
}
exit('false');
?>
