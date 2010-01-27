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
class SplObjectStorageWidget extends \F3\Admin\Widgets\AbstractWidget{
    /**
	 *
	 * @param string $name
	 * @param object $object
	 * @param object $tags
	 * @return string "Form"-Tag.
	 * @api
	 */
    public function render($name,$object,$objectName,$tags) {
		$value = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$name);
		
        preg_match("/<(.+)>/",$tags["var"][0],$matches);
        $modelClass = $matches[1];
        $repositoryClass = $this->utilities->getModelRepository($modelClass);
        if(class_exists($repositoryClass)){
            $this->view->assign("name",$name);
            $this->view->assign("object",$object);
            $this->view->assign("objectname",$objectName);
            $this->view->assign("value",$value);

            $repository = $this->objectManager->getObject($repositoryClass);
            $objects = $repository->findAll();

			$uuids = $this->getObjectUuidsByProperty($name,$object);

            $options = array();
            foreach($objects as $option){
                $title = $option->__toString();
                $uuid = $this->persistenceManager->getIdentifierByObject($option);
                $options[] = array(
                    "value"=> $uuid,
                    "name" => $title,
                    "selected" => in_array($uuid,$uuids)
                );
            }

            $this->view->assign("options",$options);
			
			if(isset($tags["inline"])){
				$widget = array(
					"model" => $modelClass,
					"object" => $this->objectFactory->create(substr($modelClass,1)),
					"errors" => array()
				);
				$this->view->assign("widget",$widget);
			}

            return array("widget" => $this->view->render());
        }else{
            return array(
				"property_errors" => "Couldn't find a appropriate Repository for the Model ".$modelClass,
				"widget" =>""
			);
        }
	}

	public function getObjectUuidsByProperty($property, $mainObject){
		$uuids = array();
		if($objects = \F3\FLOW3\Reflection\ObjectAccess::getProperty($mainObject, $property)){
			foreach($objects as $object){
				$uuids[] = $this->persistenceManager->getIdentifierByObject($object);
			}
		}
		return $uuids;
	}
}

?>
