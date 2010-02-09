<?php
 
namespace F3\Admin\Widgets;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * @api
 * @scope prototype
 */
class SingleRelationWidget extends \F3\Admin\Widgets\AbstractWidget{
    /**
	 *
	 * @param string $name
	 * @param object $object
	 * @param object $tags
	 * @return string "Form"-Tag.
	 * @api
	 */
    public function render($name,$object,$objectName,$tags) {
        $getter = "get".ucfirst($name);
		if(!is_callable(array($object,$getter))) return array("widget"=>"method not found");
        $value = call_user_func(array($object,$getter));
        $modelClass = $tags["var"][0];
        $repositoryClass = $this->getModelRepository($modelClass);
        if(class_exists($repositoryClass)){
            $this->view->assign("name",$name);
            $this->view->assign("object",$object);
            $this->view->assign("objectname",$objectName);
            $this->view->assign("value",$value);

            $repository = $this->objectManager->getObject($repositoryClass);
            $objects = $repository->findAll();

			$selectedUuid = $this->getSelectedUuid($name,$object);

            $options = array();
            foreach($objects as $option){
                $title = $this->utilities->toString($option);
                $uuid = $this->persistenceManager->getIdentifierByObject($option);
                $options[] = array(
                    "value"=> $uuid,
                    "name" => $title,
                    "selected" => ($selectedUuid == $uuid)
                );
            }
            $this->view->assign("options",$options);
			
			$this->view->assign("model",$modelClass);

            return array("widget" => $this->view->render());
        }else{
            return array(
				"property_errors" => "Couldn't find a appropriate Repository for the Model ".$modelClass,
				"widget" =>""
			);
        }
	}

	public function getSelectedUuid($property, $mainObject){
		$method = "get".ucfirst($property);
		$uuids = array();
		if(is_callable(array($mainObject,$method))){
            $object = call_user_func(array($mainObject,$method));
			if(!is_object($object))
				return false;
           	$uuid = $this->persistenceManager->getIdentifierByObject($object);
            return $uuid;
		}
	}
}

?>
