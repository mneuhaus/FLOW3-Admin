<?php
namespace Admin\Adapters;

/*                                                                        *
 * This script belongs to the FLOW3 package "Contacts".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Adapter for the Doctrine engine
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * 
 */
class DoctrineAdapter extends \Admin\Core\Adapters\AbstractAdapter {
	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $persistenceManager;
	
	public function applyLimit($limit){
		$this->query->setLimit($limit);
	}
	
	public function applyOffset($offset){
		$this->query->setOffset($offset);
	}
	
	public function applyOrderings($property, $direction = null){
		if(is_null($direction)){
			$direction = \TYPO3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING;
		}
		
		$this->query->setOrderings(array(
			$property => $direction
		));
	}
	
	public function getName($being) {
		return ucfirst($being);
	}
	
	public function init() {
		$this->settings = $this->helper->getSettings("Doctrine");
		parent::init();
		$this->fmc = $this->objectManager->get('TYPO3\FLOW3\MVC\FlashMessageContainer');
	}
	
	public function initQuery($being){
		$repository = str_replace("Domain\\Model", "Domain\\Repository", $being) . "Repository";
		$repository = $this->getRepositoryForModel($being);
		if(\class_exists($repository)){
			$repositoryObject = $this->objectManager->get($repository);
			$this->query = $repositoryObject->createQuery();
		}
	}
	
	public function postProcessConfiguration($configuration) {
		foreach($configuration ["properties"] as $property => $conf) {
			$type = $configuration["properties"][$property]["var"];
			
			$configuration["properties"][$property]["type"] = $type;
			preg_match("/<(.+)>/", $configuration ["properties"] [$property] ["type"], $matches);
			if(!empty($matches)){
				$configuration["properties"][$property]["being"] = ltrim($matches[1],"\\");
				$configuration["properties"][$property]["mode"] = \Admin\Core\Property::INLINE_MULTIPLE_MODE;
			}
			
			if(class_exists($type)){
				$reflectClass = new \TYPO3\FLOW3\Reflection\ClassReflection($type);
				if($reflectClass->isTaggedWith("entity")){
					$configuration ["properties"] [$property] ["being"] = ltrim($type,"\\");
					$configuration["properties"][$property]["mode"] = \Admin\Core\Property::INLINE_SINGLE_MODE;
				}
			}
			
			if(isset($configuration["properties"][$property]["being"])){
				$repository = \Admin\Core\Helper::getModelRepository($configuration["properties"][$property]["being"]);
				if(!class_exists($repository) && $configuration["properties"][$property]["being"] !== "TYPO3\FLOW3\Resource\Resource"){
					$configuration["properties"][$property]["inline"] = true;
				}
			}
		}
		$configuration = parent::postProcessConfiguration($configuration);
		return $configuration;
	}

	public function getGroups() {
		$this->init();
		$groups = array();
		$classes = $this->getClassesTaggedWith(array("active"));
		foreach ($this->settings["Beings"] as $being => $conf) {
			if(isset($conf["active"]) && $conf["active"] == true){
				if(isset($conf["group"]))
					$classes[$being] = $conf["group"];
				else
					$classes[$being] = $this->objectManager->getPackageKeyByObjectName($being);
			}
		}
		
		foreach($classes as $class => $packageName) {
			$tags = $this->reflectionService->getClassTagsValues($class);
			$repository = $this->getRepositoryForModel($class);
			
			if(class_exists($repository)){
				$group = $packageName;
				
				if(isset($tags["group"]))
					$group = current($tags["group"]);
				
				$groups[$group][] = array("being" => $class, "name" => \Admin\Core\Helper::getShortName($class));
			}
		}
		return $groups;
	}

	public function getObject($being, $id) {
		if( class_exists($being) ) {
			if( $id == null ){
				return $this->objectManager->create($being);
			}else{
				return $this->persistenceManager->getObjectByIdentifier($id, $being);
			}
		}
		return null;
	}

	public function getObjects($being) {
		$configuration = $this->getConfiguration($being);
		$objects = array();
		if(!isset($this->query))
			$this->initQuery($being);
		if(isset($configuration["class"]["admin\annotations\orderby"])){
			$this->query->setOrderings(array(
				current($configuration["class"]["admin\annotations\orderby"]) => 'ASC'
			));
		}
		$objects = $this->query->execute();
		return $objects;
	}

	public function getId($object) {
		if(is_object($object)){
			return $this->persistenceManager->getIdentifierByObject($object);
		}
		return null;
	}
	
	public function getRepositoryForModel($model){
		if(isset($this->settings["Beings"][$model]) && $this->settings["Beings"][$model]["repository"])
			$repository = $this->settings["Beings"][$model]["repository"];
		else
			$repository = \Admin\Core\Helper::getModelRepository($model);
		
		return $repository;
	}
	
	public function getTotal($being){
		return $this->query->count();
	}
	
	
	public function createObject($being, $data) {
		$configuration = $this->getConfiguration($being);
		$result = $this->propertyMapper->convert($data, $being, \Admin\Core\PropertyMappingConfiguration::getConfiguration());
		
		if(is_a($result, $being)){
			$repository = $this->objectManager->get($this->getRepositoryForModel($being));
			$repository->add($result);
			$this->persistenceManager->persistAll();
		}
		return $result;
	}

	public function updateObject($being, $id, $data) {
		$configuration = $this->getConfiguration($being);
		$data["__identity"] = $id;
		$result = $this->propertyMapper->convert($data, $being, \Admin\Core\PropertyMappingConfiguration::getConfiguration());
		
		if(is_a($result, $being)){
			$repository = $this->objectManager->get($this->getRepositoryForModel($being));
			$repository->add($result);
			$this->persistenceManager->persistAll();
		}
		return $result;
	}

	public function deleteObject($being, $id) {
		$object = $this->persistenceManager->getObjectByIdentifier($id, $being);
		if( $object == null ) return;
		$repositoryObject = $this->objectManager->get($this->getRepositoryForModel($being));
		$repositoryObject->remove($object);
		$this->persistenceManager->persistAll();
	}
	
	## Conversion Functions
	

	public function beingsToIdentifiers($beings) {
		$identifiers = array();
		if( is_array($beings) ) {
			foreach($beings as $key => $being) {
				$identifiers [] = $this->getId($being);
			}
		}
		return implode(",", $identifiers);
	}

	public function identifiersToBeings($identifiers, $conf, $property) {
		preg_match("/\\\\Beings\\\\([A-Za-z]+)$/", $conf ["type"], $matches);
		$being = $matches [1];
		$identifiers = explode(",", $identifiers);
		$beings = array();
		foreach($identifiers as $identifier) {
			$beings [] = $this->getObject($being, $identifier);
		}
		return $beings;
	}

	public function identifierToBeing($identifier, $conf, $property) {
		preg_match("/\\\\Being\\\\([A-Za-z]+)$/", $conf ["type"], $matches);
		$being = $matches [1];
		return $this->getObject($being, $identifier);
	}
}

?>