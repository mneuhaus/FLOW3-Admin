<?php

namespace Admin\Actions;

/* *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Action to display the list and apply Bulk aktions and filter if necessary
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("prototype")
 */
class ListAction extends \Admin\Core\Actions\AbstractAction {

	/**
	 * Function to Check if this Requested Action is supported
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function canHandle($being, $action = null, $id = false) {
		return false;
	}

	/**
	 * Delete objects
	 *
	 * @param string $being
	 * @param array $ids
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function execute($being, $ids = null) {
		$this->being = $being;
		
		$this->handleBulkActions();
		
		$this->adapter->initQuery($being);
		
		$this->total = $this->adapter->getTotal($being);
		$this->view->assign("total", $this->total);
		
		$this->handleLimits();
		$this->handlePagination();
		$this->handleFilters();
		
		$beings = $this->adapter->getBeings($this->being);
		
		$this->view->assign("objects", $beings);
		
		// Redirect to creating a new Object if there aren't any (Clean Slate)
		if( count($beings) < 1 ) {
			$arguments = array("being" => \Admin\Core\Register::get("classShortNames", $being), "adapter" => get_class($this->adapter));
			$this->controller->redirect("create", NULL, NULL, $arguments);
		}
		
		$listActions = $this->controller->getActions("list", $being, true);
		$this->view->assign('listActions', $listActions);
	}
	
	public function handleBulkActions(){
		$actions = $this->controller->getActions("bulk", $this->being, true);
		$this->view->assign("actions", $actions);
		
		if( $this->request->hasArgument("bulk") ) {
			$bulkAction = $this->request->getArgument("bulkAction");
			if( isset($actions[$bulkAction]) ) {
				$action = $actions[$bulkAction];
				$items = $this->request->getArgument("bulkItems");
				$action->execute($this->being, $items);
			}
			$arguments = array("being" => $this->being, "adapter" => get_class($this->adapter));
			$this->controller->redirect("list", NULL, NULL, $arguments);
		}
	}

	public function handleLimits(){
		$limits = array("10" => true, "20" => false, "100" => false, "1000" => false, "10000" => false);
		
		if($this->request->hasArgument("limit"))
			$this->limit = $this->request->getArgument("limit");
		else
			$this->limit = key($limits);
		
		$unset = false;
		foreach ($limits as $key => $value) {
			$limits[$key] = ($this->limit == $key);
			
			if(!$unset && intval($key) >= intval($this->total)){
				$unset = true;
				continue;
			}
			if($unset)
				unset($limits[$key]);
		}
		
		$this->view->assign("limits", $limits);
		$this->adapter->applyLimit($this->limit);
	}
	
	public function handlePagination(){
		$currentPage = 1;
		
		if( $this->request->hasArgument("page") )
			$currentPage = $this->request->getArgument("page");
		
		$pages = array();
		for($i=0; $i < ($this->total / $this->limit); $i++) { 
			$pages[] = $i + 1;
		}
		
		if($currentPage > count($pages))
			$currentPage = count($pages);
		
		$offset = ($currentPage - 1) * $this->limit;
		$offset = $offset < 0 ? 0 : $offset;
		$this->adapter->applyOffset($offset);
		$this->view->assign("offset", $offset);
		
		
		if(count($pages) > 1){
			$this->view->assign("pages", $pages);
			
			$this->view->assign("currentpage", $currentPage);
		
			if($currentPage < count($pages))
				$this->view->assign("nextpage", $currentPage + 1);
			
			if($currentPage > 1)
				$this->view->assign("prevpage", $currentPage - 1);
		}
	}
	
	public function handleFilters(){
		if( $this->request->hasArgument("filter") ) {
			$filters = $this->request->getArgument("filters");
			$this->adapter->applyFilters($filters);
			$this->view->assign("filters", $this->adapter->getFilter($this->being, $filters));
		}else {
			$this->view->assign("filters", $this->adapter->getFilter($this->being));
		}
	}
}
?>