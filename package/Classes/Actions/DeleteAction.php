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
class DeleteAction extends AbstractAction {
    /**
     * Function to Check if this Requested Action is supported
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function canHandle($being, $action = null, $id = false){
        return $id;
    }
    
    public function getClass(){
        return "ui-icon ui-button-b16-delete";
    }

    public function getAction(){
        return "confirm";
    }

    /**
     * Delete objects
     *
     * @param string $being
     * @param array $ids
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function execute($being, $ids = null){
        if(is_array($ids)){
            foreach($ids as $id){
                $this->adapter->deleteObject($being, $id);
            }
        }else{
            if($this->request->hasArgument("confirm")){
                $this->adapter->deleteObject($being,$ids);

                $arguments = array("adapter"=>get_class($this->adapter),"being"=>$being);
                $this->controller->redirect('list',NULL,NULL,$arguments);
            }else{
                $this->controller->redirect('confirm',NULL,NULL,$this->request->getArguments());
            }
        }
    }
}

?>