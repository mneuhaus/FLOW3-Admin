<?php

namespace F3\Admin\Actions;

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

/**
 * Abstract validator
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class ListAction extends AbstractAction {
    /**
     * Function to Check if this Requested Action is supported
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function canHandle($being, $action = null, $id = false){
        return false;
    }

    /**
     * The Name of this Action
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function __toString(){
        return "List";
    }

    public function getClass(){
        return "";
    }

    public function getAction(){
        return "list";
    }

    /**
     * Delete objects
     *
     * @param string $being
     * @param array $ids
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function execute($being, $ids = null){
        $actions = $this->controller->getActions("bulk", $being, true);
        $this->view->assign("actions",$actions);

        if($this->request->hasArgument("bulk")){
            $bulkAction = $this->request->getArgument("bulkAction");
            if(isset($actions[$bulkAction])){
                $action = $actions[$bulkAction];
                $items = $this->request->getArgument("bulkItems");
                $action->execute($being,$items);
            }
			$arguments = array( "being" => $being , "adapter" => get_class($this->adapter));
			$this->controller->redirect("list",NULL,NULL,$arguments);
        }

        if($this->request->hasArgument("filter")){
            $filters = $this->request->getArgument("filters");
            $beings = $this->adapter->getBeings($being,$filters);
            $this->view->assign("filters", $this->adapter->getFilter($being,$filters));
        }else{
            $beings = $this->adapter->getBeings($being);
            $this->view->assign("filters", $this->adapter->getFilter($being));
        }

		$this->view->assign("objects",$beings);

		// Redirect to creating a new Object if there aren't any (Clean Slate)
		if(count($beings) < 1){
			$arguments = array( "being" => $being , "adapter" => get_class($this->adapter));
			$this->controller->redirect("create",NULL,NULL,	$arguments);
		}

        $listActions = $this->controller->getActions("list", $being, true);
		$this->view->assign('listActions',$listActions);
    }
}

?>