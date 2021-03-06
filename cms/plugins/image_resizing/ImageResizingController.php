<?php if(!defined('CMS_ROOT')) die;

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
 * @subpackage plugins.image_resizing
 *
 * @author Maslakov Alexandr <jmas.ukraine@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Maslakov Alexander, 2011
 */

class ImageResizingController extends PluginController
{	
	public function __construct()
	{
		parent::__construct();
		
		$this->setLayout('backend');
	}
	
	public function settings()
	{
		if (get_request_method() == 'POST')
		{
			$settings = array();
			
			if (!empty($_POST['setting']['cache_sizes']))
			{
				$settings['cache_sizes'] = serialize($_POST['setting']['cache_sizes']);
			}
			else
			{
				$settings['cache_sizes'] = '';
			}
			
			foreach ($_POST['setting'] as $key => $val)
			{
				if ( !is_array($val))
					$settings[$key] = $val;
			}
			
			Plugin::setAllSettings($settings, 'image_resizing');
			
			Flash::set('success', __('Settings has been saved!'));
			redirect(get_url('plugin/image_resizing/settings'));
		}
		
		$this->display('image_resizing/views/settings', array(
			'setting' => Plugin::getAllSettings('image_resizing')
		));
	}
} // end class ImageResizingController