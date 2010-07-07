<?php

namespace F3\Admin\Adapters;

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
 * @prototype
 */
class PHPCRAdapter extends AbstractAdapter {
	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $persistenceManager;

    public $debuglog = array();

    public function init() {
		$this->settings = $this->helper->getSettings("PHPCR");
		parent::init();
    }

    public function createObject($being, $data) {
		$configuration = $this->getConfiguration($being);
		$result = $this->transformToObject($being, $data);
        if(empty($result["errors"])){
            $repository = $this->objectManager->getObject(str_replace("Domain\\Model", "Domain\\Repository", $being) . "Repository");
            $repository->add($result["object"]);
            $this->persistenceManager->persistAll();
        }
		return $result;
    }

    public function deleteObject($being, $id) {
		$object = $this->persistenceManager->getObjectByIdentifier($id);
		if( $object == null ) return;
		$repository = str_replace("Domain\\Model", "Domain\\Repository", $being) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);
		$repositoryObject->remove($object);
		$this->persistenceManager->persistAll();
    }

    public function getGroups() {
        $this->init();
		$groups = array ();
        $classes = $this->getClassesTaggedWith(array("entity","autoadmin"));
        foreach($classes as $class => $packageName) {
            $tags = $this->reflectionService->getClassTagsValues($class);
            $repository = \F3\Admin\Core\Helper::getModelRepository($class);
            if(class_exists($repository)){
                
                $group = $packageName;
                if(isset($tags["group"]))
                    $group = current($tags["group"]);
                
                $groups[$group][] = array("being" => $class, "name" => \F3\Admin\Core\Helper::getShortName($class));
            }
        }
		return $groups;
    }

    public function getId($object) {
        if(is_object($object)){
            return $this->persistenceManager->getIdentifierByObject($object);
        }
        return null;
    }

    public function getObject($being, $id = null) {
		if( class_exists($being) ) {
			if( $id == null ){
				return $this->objectManager->create($being);
            }else{
				return $this->persistenceManager->getObjectByIdentifier($id);
            }
		}
		return null;
    }

    public function getObjects($being) {
        $repository = str_replace("Domain\\Model", "Domain\\Repository", $being) . "Repository";
        $objects = array();
        if(\class_exists($repository)){
            $repositoryObject = $this->objectManager->getObject($repository);
            $query = $repositoryObject->createQuery();
            $objects = $query->execute();
        }else{
            #$objects = array($this->getObject($being));
        }
		return $objects;
    }

    public function updateObject($being, $id, $data) {
		$configuration = $this->getConfiguration($being);
        $data["__identity"] = $id;
		$result = $this->transformToObject($being, $data);
        if(empty($result["errors"])){
            $repository = $this->objectManager->getObject(str_replace("Domain\\Model", "Domain\\Repository", $being) . "Repository");
            $repository->add($result["object"]);
            $this->persistenceManager->persistAll();
        }
		return $result;
    }
	
	public function postProcessConfiguration($configuration) {
		foreach($configuration ["properties"] as $property => $conf) {
			$type = $configuration["properties"][$property]["var"];

			$configuration ["properties"] [$property] ["type"] = $type;

			preg_match("/<(.+)>/", $configuration ["properties"] [$property] ["type"], $matches);
			if(!empty($matches)){
				$configuration["properties"][$property]["being"] = ltrim($matches[1],"\\");
				$configuration["properties"][$property]["mode"] = \F3\Admin\Core\Property::INLINE_MULTIPLE_MODE;
			}

			if(class_exists($type)){
				$reflectClass = new \F3\FLOW3\Reflection\ClassReflection($type);
				if($reflectClass->isTaggedWith("entity")){
					$configuration ["properties"] [$property] ["being"] = ltrim($type,"\\");
					$configuration["properties"][$property]["mode"] = \F3\Admin\Core\Property::INLINE_SINGLE_MODE;
				}
			}

			if(isset($configuration["properties"][$property]["being"])){
				$repository = \F3\Admin\Core\Helper::getModelRepository($configuration["properties"][$property]["being"]);
				if(!class_exists($repository)){
					$configuration["properties"][$property]["inline"] = true;
				}
			}
		}
		$configuration = parent::postProcessConfiguration($configuration);
		return $configuration;
	}

}

?>