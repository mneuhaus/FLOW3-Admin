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
	 * @var \F3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $packageManager;
	
	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 * @author Marc Neuhaus
	 * @inject
	 */
	protected $reflection;
	
	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;
	
	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $persistenceManager;
	
	public function getGroups(){
		$activePackages = $this->packageManager->getActivePackages();
		$groups = array();
		$settings = $this->helper->getSettings();
		foreach ($activePackages as $packageName => $package) {
			foreach ($package->getClassFiles() as $class => $file) {
				if(strpos($class,"\Model\\")>0){
					$tags = $this->reflection->getClassTagsValues($class);
					$parts = explode('\\',$class);
					$name = end($parts);
					$repository = $this->helper->getModelRepository($class);
					if( ( in_array("autoadmin",array_keys($tags)) || in_array("\\".$class,$settings["Models"]) )
						&& class_exists($repository)){
						$groups[$packageName][] = array(
							"being" => $class,
							"name"	=> $name
						);
					}
				}
			}
		}
		return $groups;
	}
	
	public function getAttributeSets($being, $id = null, $level = 0){
		$being = ltrim($being,"\\");
		$model = $this->objectManager->create($being);
		
		$properties = $this->helper->getModelProperties($being);
		$tags = $this->reflection->getClassTagsValues($being);
		$sets = array();
		if(isset($tags["set"])){
			foreach ($tags["set"] as $set) {
				preg_match("/(.*)\(([a-z,]+)\)/",$set,$matches);
				if(!isset($matches[2])) continue;
				
				$setName = isset($matches[1]) ? $matches[1] : "General";
				
				$attributes = explode(",",$matches[2]);
				
#				$propertyErrors = array();
#				foreach ($this->utilities->getErrorsForProperty($property,$errors) as $error) {
#					$propertyErrors[] = $error->getMessage();
#				}
				foreach ($attributes as $attribute) {
					if(!isset($properties[$attribute])) continue;
					$type = $this->getWidgetType($properties[$attribute]["var"][0],"TextField");
					
					$inline = array();
					if(array_key_exists("inline",$properties[$attribute]) && $level < 1){
						if($type == "SingleRelation"){
							$inline = $this->getAttributeSets($properties[$attribute]["var"][0], $id, $level+1);
						}
					}
					
					$sets[$setName][] = array(
						"label" 	=> ucfirst($attribute),
						"name" 		=> $attribute,
						"error" 	=> "",
						"widget" 	=> $type,
						"inline"	=> $inline
					);
					
				}
			}
		}else{
			foreach ($properties as $attribute => $property) {
				if(!isset($properties[$attribute])) continue;
				$type = $this->getWidgetType($properties[$attribute]["var"][0],"TextField");
				
				$inline = array();
				if(array_key_exists("inline",$properties[$attribute]) && $level < 1){
					if($type == "SingleRelation"){
						$inline = $this->getAttributeSets($properties[$attribute]["var"][0], $id, $level+1);
					}
				}
				$sets["General"][] = array(
					"label" 	=> ucfirst($attribute),
					"name" 		=> $attribute,
					"error" 	=> "",
					"widget" 	=> $type,
					"inline"	=> $inline
				);
				
			}
		}
		
		return $sets;
	}
	
	
	public function createObject($being, $data){
		$result = $this->transformToObject($being,$data);
		$repository = $this->objectManager->getObject(str_replace("Domain\Model","Domain\Repository",$being) . "Repository");
		$repository->add($result["object"]);
		return $result["errors"];
	}
	
	public function getObjects($being){
		$repository = str_replace("Domain\Model","Domain\Repository",$being) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);
		
		$properties = $this->helper->getModelProperties($being);
		
		$query = $repositoryObject->createQuery();
		$rawObjects = $query->execute();
		
		$objects = array();
		foreach ($rawObjects as $object) {
			foreach ($properties as $property => $meta) {
				$objects[$this->persistenceManager->getIdentifierByObject($object)][$property] = array(
					"label" => ucfirst($property),
					"name"	=> $property,
					"type"	=> $this->getWidgetType($meta["var"][0],"TextField"),
					"value" => \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property)
				);
			}
		}
		return $objects;
	}
	
	public function getObject($being,$id){
		$properties = $this->helper->getModelProperties($being);
		$rawObject = $this->persistenceManager->getObjectByIdentifier($id);
		$object = array();
		foreach ($properties as $property => $meta) {
			$object["meta"] = array(
				"id" => $id,
				"name" => $rawObject->__toString()
			);
			$object["properties"][$property] = array(
				"label" => ucfirst($property),
				"name"	=> $property,
				"type"	=> $this->getWidgetType($meta["var"][0],"TextField"),
				"value" => \F3\FLOW3\Reflection\ObjectAccess::getProperty($rawObject,$property)
			);
		}
		
		return $object;
	}
	
	public function deleteObject($being,$id){
		$object = $this->persistenceManager->getObjectByIdentifier($id);
		
		if($object == null) return;
		
		$repository = str_replace("Domain\Model","Domain\Repository",$being) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);
		
		$repositoryObject->remove($object);
		$this->persistenceManager->persistAll();
	}
	
	public function updateObject($being, $data){
		$result = $this->transformToObject($being,$data);
		$repository = $this->objectManager->getObject(str_replace("Domain\Model","Domain\Repository",$being) . "Repository");
		$repository->update($result["object"]);
		return $result["errors"];
	}
	
	## Helper Functions from here on
	
	public function transformToObject($being,$data){
#		$item = $this->convertArray($this->request->getArgument("item"),$being);
		$data = $this->cleanUpItem($data);

		$arg = $this->objectManager->get("F3\Admin\Argument","item",$being);
		$arg->setValidator($this->helper->getModelValidator($being));
		$arg->setValue($data);

		$targetObject = $arg->getValue();

		$validationErrors = $arg->getValidator()->getErrors();
		
		$errors = array();
		if(count($validationErrors)>0){
			foreach ($validationErrors as $propertyError) {
				$errors[$propertyError->getPropertyName()] = array();
				foreach ($propertyError->getErrors() as $error) {
					$errors[$propertyError->getPropertyName()][] = $error->getMessage();
				}
			}	
			return $errors;
		}
		
		return array(
			"errors" => $errors,
			"object" => $targetObject
		);
	}
	
	public function getWidgetType($raw,$default = null){
		$settings = $this->helper->getSettings("PHPCR");
		$mappings = $settings["Widgets"]["Mapping"];
		
		if(isset($mappings[$raw])){
			return $mappings[$raw];
		}
		
		foreach ($mappings as $pattern => $widget) {
			if(preg_match("/".$pattern."/",$raw) > 0){
				return $widget;
			}
		}
		
		if($default !== null)
			return $default;
		
		return $raw;
    }
	
	public function cleanUpItem($item){
		foreach ($item as $key => $value) {
			if(is_array($value)){
				$item[$key] = $this->cleanUpItem($value);
			}
			if(empty($item[$key])){
				unset($item[$key]);
			}
		}
		return $item;
	}
}

?>