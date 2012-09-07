<?php if (!defined('CMS_ROOT')) die;

/**
 * Flexo CMS - Content Management System. <http://flexo.up.dn.ua>
 * Copyright (C) 2008 Maslakov Alexander <jmas.ukraine@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Flexo CMS.
 *
 * Flexo CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Flexo CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Flexo CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Flexo CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * @package Flexo
 * @subpackage plugins.cache
 *
 * @author Maslakov Alexandr <jmas.ukraine@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Maslakov Alexander, 2011
 */
 
$PDO = Record::getConnection();
$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));

if( $driver == 'mysql' )
{
	$PDO->exec('CREATE TABLE IF NOT EXISTS '.TABLE_PREFIX.'cache_page (
		page_id int(11) NOT NULL,
		cache_id varchar(50) collate utf8_bin NOT NULL,
		UNIQUE KEY page_id (page_id)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8');
}
else if( $driver == 'sqlite')
{
	$PDO->exec('CREATE TABLE IF NOT EXISTS cache_page (
		page_id int(11) NOT NULL,
		cache_id varchar(50) NOT NULL,
		UNIQUE (page_id)
	)');
}

$settings = array(
	'cache_static'        => 'no',
	'cache_remove_static' => 'no',
	'cache_lifetime'      => 86400
);

// Save plugin settings
Plugin::setAllSettings($settings, 'cache');