<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\View\Helper;

use Windwalker\Data\Data;
use Windwalker\DI\Container;
use Windwalker\Helper\DateHelper;

/**
 * The FrontViewHelper class.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class FrontViewHelper
{
	/**
	 * checkPublishedDate
	 *
	 * @param Data $item
	 *
	 * @return  void
	 */
	public static function checkPublishedDate(Data $item)
	{
		$pup  = DateHelper::getDate($item->publish_up)->toUnix();
		$pdw  = DateHelper::getDate($item->publish_down)->toUnix();
		$now  = DateHelper::getDate('now')->toUnix();
		$null = DateHelper::getDate('0000-00-00 00:00:00')->toUnix();

		if (($now < $pup && $pup != $null) || ($now > $pdw && $pdw != $null))
		{
			$item->published = 0;
		}

		if ($item->modified == '0000-00-00 00:00:00')
		{
			$item->modified = '';
		}
	}

	/**
	 * events
	 *
	 * @param Data       $item
	 * @param \JRegistry $params
	 *
	 * @return  void
	 */
	public static function events(Data $item, \JRegistry $params, $context)
	{
		$item->event = new Data;

		\JPluginHelper::importPlugin('content');
		$dispatcher = Container::getInstance()->get('event.dispatcher');

		$results = $dispatcher->trigger('onContentPrepare', array($context, &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array($context, &$item, &$params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$item, &$params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array($context, &$item, &$params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));
	}

	/**
	 * viewLevel
	 *
	 * @param Data       $item
	 * @param Data       $category
	 * @param \JRegistry $state
	 * @param \JRegistry $params
	 *
	 * @return  void
	 */
	public static function viewLevel(Data $item, Data $category, \JRegistry $state, \JRegistry $params)
	{
		$user = Container::getInstance()->get('user');

		if ($access = $state->get('filter.access'))
		{
			// If the access filter has been set, we already know this user can view.
			$params->set('access-view', true);
		}
		else
		{
			// If no access filter is set, the layout takes some responsibility for display of limited information.
			$groups = $user->getAuthorisedViewLevels();

			if (!$item->catid || empty($category->access))
			{
				$params->set('access-view', in_array($item->access, $groups));
			}
			else
			{
				$params->set('access-view', in_array($item->access, $groups) && in_array($category->access, $groups));
			}
		}
	}

	/**
	 * A quick function to show item information.
	 *
	 * @param   Data    $item  Item object.
	 * @param   string  $key   Information key.
	 * @param   string  $label Information label. If is null, will use JText.
	 * @param   string  $icon  Icon name for bootstrap icon.
	 * @param   string  $link  Has link URL?
	 * @param   string  $class Set class to this wrap.
	 *
	 * @return  string  Information HTML.
	 */
	public static function showInfo($item, $key = null, $label = null, $icon = '', $link = null, $class = null)
	{
		if (empty($item->$key))
		{
			return false;
		}

		$label = \JText::_($label);
		$value = $item->$key;

		if ($link)
		{
			$value = \JHtml::_('link', $link, $value);
		}

		$class = str_replace('_', '-', $key) . ' ' . $class;

		$icon = $icon ? 'icon-' . $icon : '';

		$info = <<<INFO
		<div class="{$class}">
            <span class="label">
            <i class="{$icon}"></i>
            {$label}
            </span>
            <span class="value">{$value}</span>
        </div>
INFO;

		return $info;
	}
}
