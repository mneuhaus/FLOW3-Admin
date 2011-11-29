<?php

namespace Admin\ViewHelpers\Query;

/*                                                                        *
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
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("prototype")
 */
class PaginateViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;
	
	/**
	 *
	 * @param mixed $objects
	 * @param string $as
	 * @param string $limitsAs
	 * @param string $paginationAs
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($objects = null, $as = "paginatedObjects", $limitsAs = "limits", $paginationAs = "pagination") {
		$this->query = $objects->getQuery();
		
		$this->settings = $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Admin.Pagination");
		$this->request = $this->controllerContext->getRequest();
		
		$this->total = $this->query->count();
		$limits = $this->handleLimits();
		$pagination = $this->handlePagination();
		
		$this->templateVariableContainer->add($limitsAs, $limits);
		$this->templateVariableContainer->add($paginationAs, $pagination);
		$this->templateVariableContainer->add($as, $this->query->execute());
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove($limitsAs);
		$this->templateVariableContainer->remove($paginationAs);
		$this->templateVariableContainer->remove($as);
		
		return $content;
	}
	
	public function handleLimits(){
		
		$limits = array();
		foreach ($this->settings["Limits"] as $limit) {
			$limits[$limit] = false;
		}
		
		if($this->request->hasArgument("limit"))
			$this->limit = $this->request->getArgument("limit");
		else
			$this->limit = $this->settings["Default"];
		
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
		
		if(count($limits) == 1)
			$limits = array();
		
		$this->query->setLimit($this->limit);
		
		return $limits;
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
		$this->query->setOffset($offset);
		$pagination = array("offset" => $offset);
		
		if(count($pages) > 1){
			$pagination["currentPage"] = $currentPage;
		
			if($currentPage < count($pages))
				$pagination["nextPage"] = $currentPage + 1;
			
			if($currentPage > 1)
				$pagination["prevPage"] = $currentPage - 1;
			
			if(count($pages) > $this->settings["MaxPages"]){
				$max = $this->settings["MaxPages"];
				$start = $currentPage - ( ($max + ($max % 2) ) / 2);
				$start = $start > 0 ? $start : 0;
				$start = $start > 0 ? $start : 0;
				$start = $start + $max > count($pages) ? count($pages) - $max : $start;
				$pages = array_slice($pages, $start, $max);
			}
			
			$pagination["pages"] = $pages;
		}
		return $pagination;
	}
}

?>