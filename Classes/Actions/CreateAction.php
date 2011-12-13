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
 * Action to create a new Being
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("prototype")
 */
class CreateAction extends \Admin\Core\Actions\AbstractAction {

	/**
	 * Function to Check if this Requested Action is supported
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function canHandle($being, $action = null, $id = false) {
		return ($action == "list" && !$id);
	}

	/**
	 * The Name of this Action
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function __toString(){
		return "New";
	}
	
	public function getShortcut(){
		return "n";
	}
	
	/**
	 * Create objects
	 *
	 * @param string $being
	 * @param array $ids
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function execute($being, $ids = null) {
		$object = $this->adapter->getBeing($being);
		
		if( $this->request->hasArgument("create") ) {
			$result = $this->adapter->createObject($being, $this->request->getArgument("item"));
			
			if( is_a($result, $being) ) {
				$this->controller->addLog();
				$arguments = array("being" => \Admin\Core\API::get("classShortNames", $being));
				$this->controller->redirect('list', NULL, NULL, $arguments);
			}else {
				$object->setErrors($result);
				$object->setObject($this->request->getArgument("item"));
			}
		}

		$this->view->assign("being", $object);
	}

}
?>