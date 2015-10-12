<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of minimum two digit integers with specified first, last and step values.
 * Made especially for selecting time (hour/minute/second).
 */
class JFormFieldTwoDigitInteger extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'TwoDigitInteger';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$first = (int) $this->element['first'];
		$last = (int) $this->element['last'];
		$step = (int) $this->element['step'];

		// Sanity checks.
		if ($step == 0)
		{
			// Step of 0 will create an endless loop.
			return $options;
		}
		elseif ($first < $last && $step < 0)
		{
			// A negative step will never reach the last number.
			return $options;
		}
		elseif ($first > $last && $step > 0)
		{
			// A position step will never reach the last number.
			return $options;
		}
		elseif ($step < 0)
		{
			// Build the options array backwards.
			for ($i = $first; $i >= $last; $i += $step)
			{
				if ($i < 10 && $i > -10) $option = '0'.$i;
				else $option = ''.$i;
				$options[] = JHtml::_('select.option', $option);
			}
		}
		else
		{
			// Build the options array.
			for ($i = $first; $i <= $last; $i += $step)
			{
				if ($i < 10 && $i > -10) $option = '0'.$i;
				else $option = ''.$i;
				$options[] = JHtml::_('select.option', $option);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
