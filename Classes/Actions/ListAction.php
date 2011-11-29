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
		$this->view->assign('className', $being);

		$this->settings = $this->getSettings();
		
		$this->handleBulkActions();
		
		$this->adapter->initQuery($being);
		$results = $this->adapter->getQuery()->execute();
		$this->view->assign("objects", $results);
		
		// Redirect to creating a new Object if there aren't any (Clean Slate)
		if( $results->count() < 1 ) {
			$arguments = array("being" => \Admin\Core\API::get("classShortNames", $being));
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
				
				$this->controller->setTemplate($action->getAction());
				
				if($action->getAction() !== $bulkAction)
					$action = $this->controller->getActionByShortName($action->getAction() . "Action");
				
				$action->execute($this->being, $this->request->getArgument("bulkItems"));
			}
		}
	}
}
?>