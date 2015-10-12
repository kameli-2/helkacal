<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package     Joomla.Legacy
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldSubCategory extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'SubCategory';
	public $categories = array();

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();
		$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
		$published = (string) $this->element['published'];
		$parentid = (int) $this->element['parentid'];
		$depth = (int) $this->element['depth'];

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select($db->quoteName(array('id', 'parent_id', 'title')))
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('extension')  . ' = ' . $db->quote($extension) . (($published) ? ' AND ' . $db->quote('published') . ' = 1' : ''));
		$db->setQuery($query);
		$this->categories = $db->loadAssocList();


		// find $parentid and all its children
		return $this->getchildren($parentid, 1, $depth);
	}

	function getchildren($id, $depth, $maxdepth) {
		// return all children of this category
		$children = array();
		foreach ($this->categories as $category) {
			if ($category['parent_id'] == $id) {
				$children[$category['id']] = $category['title'];
				for ($i = 1; $i < $depth; $i++) $children[$category['id']] = '- '.$children[$category['id']];
				if ($depth < $maxdepth) $children = $children + $this->getchildren($category['id'], $depth+1, $maxdepth);
			}
		}
		return $children;
	}
}
