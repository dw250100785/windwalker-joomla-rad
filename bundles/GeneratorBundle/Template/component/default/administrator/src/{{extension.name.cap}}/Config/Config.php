<?php
/**
 * Part of Component {{extension.name.cap}} files.
 *
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace {{extension.name.cap}}\Config;

use Windwalker\System\Config\AbstractConfig;

// No direct access
defined('_JEXEC') or die;

/**
 * Class Config
 *
 * @since 1.0
 */
abstract class Config extends AbstractConfig
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected static $type = 'json';

	/**
	 * getPath
	 *
	 * @return  string
	 */
	public static function getPath()
	{
		$type = static::$type;
		$ext  = (static::$type == 'yaml') ? 'yml' : $type;

		return {{extension.name.upper}}_ADMIN . '/etc/config.' . $ext;
	}
}
