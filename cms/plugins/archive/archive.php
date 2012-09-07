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
 * @subpackage plugins.archive
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Maslakov Alexandr <jmas.ukraine@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Maslakov Alexander, 2011
 */

/**
 * The Archive class...
 */
class Archive
{
    public function __construct(&$page, $params)
    {
        $this->page =& $page;
        $this->params = $params;
        
        switch(count($params))
        {
            case 0: break;
            case 1:
                if (strlen((int) $params[0]) == 4)
                    $this->_archiveBy('year', $params);
                else
                    $this->_displayPage($params[0]);
            break;
            
            case 2:
                $this->_archiveBy('month', $params);
            break;
            
            case 3:
                $this->_archiveBy('day', $params);
            break;
            
            case 4:
                $this->_displayPage($params[3]);
            break;
            
            default:
                page_not_found();
        }
    }
    
    private function _archiveBy($interval, $params)
    {
        $this->interval = $interval;
        
        $conn = Record::getConnection();
        
        $page = $this->page->children(array(
            'where' => "behavior_id = 'archive_{$interval}_index'",
            'limit' => 1
        ), array(), true);
        
        if ($page)
        {
            $this->page = $page;
            $month = isset($params[1]) ? (int)$params[1]: 1;
            $day = isset($params[2]) ? (int)$params[2]: 1;

            $this->page->time = mktime(0, 0, 0, $month, $day, (int)$params[0]);
        }
        else
        {
            page_not_found();
        }
    }
    
    private function _displayPage($slug)
    {
        if( ($this->page = FrontPage::findBySlug($slug, $this->page)) === false )
            page_not_found();
    }
    
    public function get()
    {
        $date = join('-', $this->params);
        
        $pages = $this->page->parent->children(array(
            'where' => "page.created_on LIKE '{$date}%'",
            'order' => 'page.created_on DESC'
        ));
        return $pages;
    }
    
    public function archivesByYear()
    {
        $conn = Record::getConnection();
        
        $out = array();

        $sql = "SELECT DISTINCT(DATE_FORMAT(created_on, '%Y')) FROM ".TABLE_PREFIX."page WHERE parent_id=? AND status_id != ".FrontPage::STATUS_HIDDEN." ORDER BY created_on DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($this->page->id));
        
        while ($date = $stmt->fetchColumn())
            $out[] = $date;
        
        return $out;
    }
    
    public function archivesByMonth($year='all')
    {
        $conn = Record::getConnection();
        
        $out = array();
        
        $sql = "SELECT DISTINCT(DATE_FORMAT(created_on, '%Y/%m')) FROM ".TABLE_PREFIX."page WHERE parent_id=? AND status_id != ".FrontPage::STATUS_HIDDEN." ORDER BY created_on DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($this->page->id));
        
        while ($date = $stmt->fetchColumn())
            $out[] = $date;
        
        return $out;
    }
    
    public function archivesByDay($year='all')
    {
        $conn = Record::getConnection();
        
        $out = array();
        
        if ($year == 'all') $year = '';
        
        $sql = "SELECT DISTINCT(DATE_FORMAT(created_on, '%Y/%m/%d')) FROM ".TABLE_PREFIX."page WHERE parent_id=? AND status_id != ".FrontPage::STATUS_HIDDEN." ORDER BY created_on DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($this->page->id));
        
        while ($date = $stmt->fetchColumn())
            $out[] = $date;
        
        return $out;
    }
	
} // end class Archive


class PageArchive extends FrontPage
{
    protected function setUrl()
    {
        $this->url = trim($this->parent->url . date('/Y/m/d/', strtotime($this->created_on)). $this->slug, '/');
    }
    
    public function title() { return isset($this->time) ? strftime($this->title, $this->time): $this->title; }
    
    public function breadcrumb() { return isset($this->time) ? strftime($this->breadcrumb, $this->time): $this->breadcrumb; }
	
} // end class PageArchive