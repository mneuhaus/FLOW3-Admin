<?php

namespace Admin\Core\Domain;

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
 * Magic basemodel
 * Provides the following features
 *    - magic getters and setters for all properties
 *    - intelligent __toString method
 *    - toArray and fromArray functions
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * 
 * @FLOW3\Entity
 * @ORM\InheritanceType("JOINED")
 */
abstract class Magic {
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	public function getArguments(){
		return array(
			"id" => $this->getIdentity(),
			"being" => $this->getClassName(),
			"adapter" => \Admin\Core\API::get("adapter")
		);
	}
	
	public function getClassName(){
		$class = get_class($this);
		$nameParts = explode("_AOPProxy",$class);
		return array_shift($nameParts);
	}
	
	function getModelName(){
		$class = get_class($this);
		$parts = explode("\\",$class);
		$tmp = array_pop($parts);
		$nameParts = explode("_AOPProxy",$tmp);
		return array_shift($nameParts);
	}
	
	function getIdentity(){
		$persistenceManager = $this->objectManager->get("TYPO3\FLOW3\Persistence\PersistenceManagerInterface");
		return $persistenceManager->getIdentifierByObject($this);
	}

	function __call($name,$arguments) {
		if(count($arguments)>0){
			$value = $arguments[0];
		}
        
		switch (true) {
			// Magic Setter Function: setProperty($value) sets $this->property = $value;
			case substr($name,0,3) == "set":
				$this->_set($name,$value);
				break;

			// Magic Setter Function: getProperty() return $this->property
			case substr($name,0,3) == "get":
				return $this->_get($name);
				break;

			// Magic Setter Function: getProperty() return $this->property
			case substr($name,0,3) == "add":
				$this->_add($name,$value);
				break;

			case substr($name,0,3) == "has":	
				return $this->_has($name,$value);
				break;

			case substr($name,0,6) == "remove":	
				return $this->_remove($name,$value);
				break;
				
			default:
#				echo "trying to call".$name."<br />";
				break;
		}
	}
	
	public function _set($name,$value){
		$property = $this->getPropertyName(substr($name,3));
		if($property === false)
			throw new \Exception('The Property '.$property.' you are trying to set isn\'t defined in this class '.get_class($this).".");
#		echo $name." "."<br />";
		/*
		if ($this->posts instanceof \TYPO3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->posts->_loadRealInstance();
		}
		$this->removePosts($this->posts);
		foreach($posts as $post)
			$this->addPost($post);
		*/
		$this->$property = $value;
	}
	
	public function _get($name){
        $property = $this->getPropertyName(substr($name,3));
		if($property === false)
			throw new \Exception('The Property '.$property.' you are trying to get isn\'t defined in this class.');
		if ($this->$property instanceof \TYPO3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->$property->_loadRealInstance();
		}
		return $this->$property;
	}
	
	public function _add($name,$value){
		$model = $this->getModelName();
		$property = $this->getPropertyName(substr($name,3));
		$pluralized = \Admin\Service\Inflect::pluralize($property);

		if(!property_exists(get_class($this),$pluralized))
			throw new \Exception('The Property '.$property.' you are trying to add isn\'t defined in this class.');
		$this->$pluralized->attach($value);

		if(is_object($value)){
			$setter = "set".$model;
			$adder = "add".$model;
			$checker = "has".$model;
			if(method_exists($value,"method_exists")){
				if($value->method_exists($setter)){
					$value->$setter($this);
				}elseif($value->method_exists($adder)){
					if(!$value->$checker($this))
						$value->$adder($this);
				}
			}
		}
	}
	
	public function _has($name,$value){
		$property = $this->getPropertyName(substr($name,3));
		$pluralized = \Admin\Service\Inflect::pluralize($property);
		if ($this->$pluralized instanceof \TYPO3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->$pluralized->_loadRealInstance();
		}
		return $this->$pluralized->contains($value);
	}
	
	public function _remove($name,$value){
		$property = $this->getPropertyName(substr($name,3));
		if(!property_exists(get_class($this),$property))
			throw new \Exception('The Property '.$property.' you are trying to set isn\'t defined in this class '.get_class($this).".");
		$pluralized = \Admin\Service\Inflect::pluralize($property);

		if ($this->$pluralized instanceof \TYPO3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->$pluralized->_loadRealInstance();
		}

		$this->$pluralized->detach($value);
	}
	
	function method_exists($method){
		$model = $this->getModelName();
		switch (true) {
			case substr($method,0,3) == "set":
				$property = strtolower(substr($method,3));
				if(property_exists(get_class($this),$property))
					return true;
				break;
			case substr($method,0,3) == "get":
				$property = strtolower(substr($method,3));
				if(property_exists(get_class($this),$property))
					return true;
				break;
			case substr($method,0,3) == "add":
				$property = strtolower(substr($method,3));
				$pluralized = \Admin\Service\Inflect::pluralize($property);
				if(property_exists(get_class($this),$pluralized))
					return true;
				break;
			case substr($method,0,3) == "has":
				break;
			default:
				return false;
				break;
		}
	}
	
	public function __toString(){
		$reflectionService = $this->objectManager->get("TYPO3\FLOW3\Reflection\ReflectionService");
		$class = get_parent_class(get_class($this));
		$properties = $reflectionService->getClassPropertyNames($class);
		$identity = array();
		$title = array();
		$goodGuess = null;
		$usualSuspects = array("title","name");
		foreach($properties as $property){
			$tags = $reflectionService->getPropertyTagsValues($class,$property);
			if(in_array("title",array_keys($tags))){
				$title[] = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($this,$property);
			}
			
			if(in_array("identity",array_keys($tags))){
				$value = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($this,$property);
				if(!is_object($value))
					$identity[] = $value;
			}
		
			if(in_array($property,$usualSuspects) && $goodGuess === null){
				$value = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($this,$property);
				if(!is_object($value))
					$goodGuess[] = $value;
			}
		}
		
		if(count($title)>0)
			return implode(", ",$title);
		if(count($identity)>0)
			return implode(", ",$identity);
		if($goodGuess !== null)
			return $goodGuess;
		
		return "";
	}

	public function getPropertyName($property = null){
		$properties = get_class_vars(get_class($this));
		foreach($properties as $p => $value){
			if(strtolower($property) == strtolower($p)){
				return $p;
			}
		}
		return false;
	}

	public function toArray(){
		$array = array();
		$properties = get_class_vars(get_class($this));
		foreach($properties as $property => $value){
			$value = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($this,$property);
			if(is_object($value) && is_callable(array($value,"__toString"))){
				$array[$property] = strval($value);
			}else{
				$array[$property] = $value;
			}
		}
		return $array;
	}

    public function fromArray($array){
		foreach($array as $property => $value){
			$this->$property = $value;
		}
	}
}

?>