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
	
	public function init(){
		$this->settings = $this->helper->getSettings("PHPCR");
	}
	
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
		$objectArray = $this->getBeing($being,$id);
		
		$properties = $this->helper->getModelProperties($being);
		$tags = $this->reflection->getClassTagsValues($being);
		
		# Compile all Attributes Informations
		$attributes = array();
		foreach ($properties as $attribute => $conf) {
			if(!isset($properties[$attribute])) continue;
			$type = $this->getSetting($properties[$attribute]["var"][0],"TextField");
			$value = $objectArray["properties"][$attribute]["value"];
			
			$options = array();
			if($type == "SingleRelation" || $type == "MultipleRelation"){
				if($type == "MultipleRelation"){
			        preg_match("/<(.+)>/",$properties[$attribute]["var"][0],$matches);
			        $repository = $matches[1];
				}elseif($type == "SingleRelation"){
					$repository = $properties[$attribute]["var"][0];
				}
				$options = $this->getOptions($repository,$value);
			}
			
			$inline = array();
			if(array_key_exists("inline",$properties[$attribute]) && $level < 1){
				if($type == "SingleRelation"){
					$inline = $this->getAttributeSets($properties[$attribute]["var"][0], $id, $level+1);
				}
			}
			
			$attributes[$attribute] = array(
				"label" 	=> ucfirst($attribute),
				"name" 		=> $attribute,
				"error" 	=> "",
				"type"	 	=> $type,
				"inline"	=> $inline,
				"options" 	=> $options,
				"value" 	=> $value
			);
		}
		
		# Sort it into Sets
		$sets = array();
		if(isset($tags["set"])){
			foreach ($tags["set"] as $set) {
				preg_match("/(.*)\(([a-z,]+)\)/",$set,$matches);
				if(!isset($matches[2])) continue;
				
				$setName = isset($matches[1]) ? $matches[1] : "General";
				
				$setAttributes = array_intersect_key($attributes, array_flip(explode(",",$matches[2])));
				if(count($setAttributes)>0)
					$sets[$setName] = $setAttributes;
			}
		}else{
			$sets["General"] = array_values($attributes);
		}
		
		return $sets;
	}
	
	public function getBeings($being){
		$repository = str_replace("Domain\Model","Domain\Repository",$being) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);
		
		$properties = $this->helper->getModelProperties($being);
		
		$query = $repositoryObject->createQuery();
		$rawObjects = $query->execute();
		
		$objects = array();
		foreach ($rawObjects as $object) {
			$tmp = array();
			foreach ($properties as $property => $meta) {
				$tmp["meta"] = array(
					"id" => $this->persistenceManager->getIdentifierByObject($object),
					"name" => $object->__toString()
				);
				$tmp["properties"][$property] = array(
					"label" => ucfirst($property),
					"name"	=> $property,
					"type"	=> $this->getSetting($meta["var"][0],"TextField"),
					"value" => $this->convertValue(\F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property),$meta["var"][0])
				);
			}
			$objects[] = $tmp;
		}
		return $objects;
	}
	
	public function getBeing($being,$id = null){
		$properties = $this->helper->getModelProperties($being);
		
		if($id == null)
			$object = $this->objectManager->create($being);
		else
			$object = $this->persistenceManager->getObjectByIdentifier($id);
		
		$array = array();
		foreach ($properties as $property => $meta) {
			$array["meta"] = array(
				"id" => $id,
				"name" => $object->__toString()
			);
			$array["properties"][$property] = array(
				"label" => ucfirst($property),
				"name"	=> $property,
				"type"	=> $this->getSetting($meta["var"][0],"TextField"),
				"value" => $this->convertValue(\F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property),$meta["var"][0])
			);
		}
		
		return $array;
	}
	
	public function deleteObject($being,$id){
		$object = $this->persistenceManager->getObjectByIdentifier($id);
		
		if($object == null) return;
		
		$repository = str_replace("Domain\Model","Domain\Repository",$being) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);
		
		$repositoryObject->remove($object);
		$this->persistenceManager->persistAll();
	}

	public function createObject($being, $data){
		$properties = $this->helper->getModelProperties($being);
		$data = $this->convertValues($data,$properties);
		$result = $this->transformToObject($being,$data);
		$repository = $this->objectManager->getObject(str_replace("Domain\Model","Domain\Repository",$being) . "Repository");
		$repository->add($result["object"]);
		return $result["errors"];
	}
	
	public function updateObject($being, $id, $data){
		$properties = $this->helper->getModelProperties($being);
		$data = $this->convertValues($data,$properties);
		$data["__identity"] = $id;
		$result = $this->transformToObject($being,$data);
		$repository = $this->objectManager->getObject(str_replace("Domain\Model","Domain\Repository",$being) . "Repository");
		$repository->update($result["object"]);
		return $result["errors"];
	}
	
	## Helper Functions from here on
	
	public function getOptions($repository,$values = array()){
		$selected = array();
		if(count($values)>0){
			foreach ($values as $value) {
				$selected[] = $value["id"];
			}
		}
		
		$options = array();
		$repository = $this->objectManager->getObject($this->helper->getModelRepository($repository));
		$objects = $repository->findAll();
		foreach ($objects as $object) {
			$uuid = $this->persistenceManager->getIdentifierByObject($object);
			$options[] = array(
				"id" => $uuid,
				"name" => $object->__toString(),
				"selected" => in_array($uuid,$selected)
			);
		}
		
		return $options;
	}
}

?>