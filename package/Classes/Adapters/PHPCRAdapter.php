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
		parent::init();
		
		if(\F3\Admin\register::has("being")){
			$this->repository = str_replace("Domain\Model","Domain\Repository",$this->being) . "Repository";
			$this->repositoryObject = $this->objectManager->getObject($this->repository);
			$this->query = $this->repositoryObject->createQuery();
		}
	}
	
	public function setSorting($property,$direction = "asc"){
		if(strtoupper($direction) == "ASC")
			$direction = \F3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING;
		else
			$direction = \F3\FLOW3\Persistence\QueryInterface::ORDER_DESCENDING;
		
		$this->query->setOrderings(array($property => $direction));
	}
	
	public function setLimit($limit){
		$this->query->setLimit(intval($limit));
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
		$configuration = $this->getConfiguration($being);
		$data = $this->convertValues($data,$configuration["properties"]);
		$result = $this->transformToObject($being,$data);
		$repository = $this->objectManager->getObject(str_replace("Domain\Model","Domain\Repository",$being) . "Repository");
		$repository->add($result["object"]);
		$this->persistenceManager->persistAll();
		return $result["errors"];
	}
	
	public function updateObject($being, $id, $data){
		$configuration = $this->getConfiguration($being);
		$data = $this->convertValues($data,$configuration["properties"]);
		$data["__identity"] = $id;
		$result = $this->transformToObject($being,$data);
		$repository = $this->objectManager->getObject(str_replace("Domain\Model","Domain\Repository",$being) . "Repository");
		$repository->update($result["object"]);
		$this->persistenceManager->persistAll();
		return $result["errors"];
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
		$object = $this->getObject($being, $id);
		$configuration = $this->getConfiguration($being);
		
		$properties = array();
		foreach ($configuration["properties"] as $property => $conf) {
			if(!isset($configuration["properties"][$property])) continue;
			if(array_key_exists("ignore",$conf)) continue;
			
			$value = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
			
			$options = array();
			$inline = array();
			if(($conf["widget"] == "SingleRelation" || $conf["widget"] == "MultipleRelation") && !array_key_exists("inline",$conf) && $level < 1){
				$this->handleRelation($conf["widget"],$conf,$value);
			}else if(array_key_exists("inline",$conf) && $level < 1){
				if($conf["widget"] == "SingleRelation"){
					$inline = $this->getAttributeSets($conf["type"], $id, $level+1);
				}
			}
			
			$properties[$property] = array(
				"label" 	=> $this->getLabel($conf,$property),
				"name" 		=> $property,
				"error" 	=> "",
				"type"	 	=> $conf["widget"],
				"inline"	=> $inline,
				"options" 	=> $options,
				"value" 	=> $this->convertValue($value,$conf["widget"])
			);
		}
		
		# Sort it into Sets
		$sets = $this->groupPropertiesIntoSets($properties);
		
		return $sets;
	}
	
	public function handleRelation($type,$conf,$value){
		if($type == "MultipleRelation"){
			preg_match("/<(.+)>/",$conf["var"][0],$matches);
			$repository = $matches[1];
		}else if($type == "SingleRelation"){
			$repository = $conf["var"][0];
		}
		
		if(class_exists("\\".$this->helper->getModelRepository($repository))){
			$repository = $this->objectManager->getObject($this->helper->getModelRepository($repository));
			/*
			print_r($properties[$attribute]);
			if(array_key_exists("filter",$properties[$attribute])){
				$this->helper->stringToConstraint($properties[$attribute]["filter"][0],$being);
				#$query = $repository->createQuery();
				#$query->equals
			}
			*/
			$objects = $repository->findAll();
		}else{
			$objects = $value;
		}
		return $this->getOptions($objects,$value);
	}
	
	public function getBeing($being,$id = null){
		$configuration = $this->getConfiguration($being);
		$object = $this->getObject($being, $id);
		
		$array = array(
			"meta" => array(
				"id" => $id,
				"name" => $this->toString($object)
			)
		);
		foreach ($configuration["properties"] as $property => $conf) {
			if(array_key_exists("ignore",$conf)) continue;
			
			$value = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
			$array["properties"][$property] = array(
				"label" => $this->getLabel($conf,$property),
				"name"	=> $property,
				"type"	=> $this->getSetting($conf["var"][0],"TextField"),
				"value" => $this->convertValue($value,$conf["var"][0]),
				"conf" 	=> $conf
			);
			$array["object"] = $object;
		}
		
		return $array;
	}
	
	public function getBeings($being){
		$rawObjects = $this->query->execute();
		
		$objects = array();
		foreach ($rawObjects as $object) {
			$id = $this->persistenceManager->getIdentifierByObject($object);
			$array = $this->getBeing($being,$id);
			if(!empty($array))
				$objects[] = $array;
		}
		return $objects;
	}
	
	public function getObject($being, $id = null){
		if($id == null)
			$object = $this->objectManager->create($being);
		else
			$object = $this->persistenceManager->getObjectByIdentifier($id);
		return $object;
	}
	
	public function getConfiguration($being){
		$configuration = parent::getConfiguration($being);
		
		foreach($configuration["properties"] as $property => $conf){
			if( array_key_exists("inject",$conf) ||
				array_key_exists("ignore",$conf) 	){
				$configuration["properties"][$property]["ignore"] = true;
			}
			
			if(array_key_exists("widget",$conf))
				$configuration["properties"][$property]["widget"] = $conf["widget"][0];
			else
				$configuration["properties"][$property]["widget"] = $this->getSetting($conf["var"][0],"TextField");
			
			$configuration["properties"][$property]["type"] = $conf["var"][0];
		}
		
		return $configuration;
	}
	
	## Helper Functions from here on
	
	public function getOptions($objects,$values = array()){
		if(empty($objects)) return array();
		
		$selected = array();
		if(count($values)>0){
			foreach ($values as $value) {
				$selected[] = $value["id"];
			}
		}
		
		$options = array();
		foreach ($objects as $object) {
			$uuid = $this->persistenceManager->getIdentifierByObject($object);
			$options[] = array(
				"id" => $uuid,
				"name" => $this->toString($object),
				"selected" => in_array($uuid,$selected)
			);
		}
		
		return $options;
	}
}

?>