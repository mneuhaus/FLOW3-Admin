<?php

namespace F3\Admin\Domain;

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

/**
 * Abstract
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Model {
	function getModelName(){
		$class = get_class($this);
		$parts = explode("\\",$class);
		$tmp = array_pop($parts);
		$nameParts = explode("_AOPProxy",$tmp);
		return array_shift($nameParts);
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
				echo "trying to call".$name."<br />";
				break;
		}
	}
	
	public function _set($name,$value){
		$property = strtolower(substr($name,3));
		if(!property_exists(get_class($this),$property))
			throw new \Exception('The Property '.$property.' you are trying to set isn\'t defined in this class '.get_class($this).".");
#		echo $name." "."<br />";
		/*
		if ($this->posts instanceof \F3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->posts->_loadRealInstance();
		}
		$this->removePosts($this->posts);
		foreach($posts as $post)
			$this->addPost($post);
		*/
		$this->$property = $value;
	}
	
	public function _get($name){
		$property = strtolower(substr($name,3));
		if(!property_exists(get_class($this),$property))
			throw new \Exception('The Property '.$property.' you are trying to get isn\'t defined in this class.');
		if ($this->$property instanceof \F3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->$property->_loadRealInstance();
		}
		if(is_object($this->$property))
			return clone $this->$property;
		else
			return $this->$property;
	}
	
	public function _add($name,$value){
		$model = $this->getModelName();
		$property = strtolower(substr($name,3));
		$pluralized = \F3\Admin\Service\Inflect::pluralize($property);

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
		$property = strtolower(substr($name,3));
		$pluralized = \F3\Admin\Service\Inflect::pluralize($property);
		if ($this->$pluralized instanceof \F3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->$pluralized->_loadRealInstance();
		}
		return $this->$pluralized->contains($value);
	}
	
	public function _remove($name,$value){
		$property = strtolower(substr($name,3));
		if(!property_exists(get_class($this),$property))
			throw new \Exception('The Property '.$property.' you are trying to set isn\'t defined in this class '.get_class($this).".");
		$pluralized = \F3\Admin\Service\Inflect::pluralize($property);

		if ($this->$pluralized instanceof \F3\FLOW3\Persistence\LazyLoadingProxy) {
			$this->$pluralized->_loadRealInstance();
		}

		$this->$pluralized->detach($value);
#		$post->clearBlog();
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
				$pluralized = \F3\Admin\Service\Inflect::pluralize($property);
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
		$reflectionService = $this->objectManager->getObject("F3\FLOW3\Reflection\ReflectionService");
		$class = get_class($this);
		$properties = $reflectionService->getClassPropertyNames($class);
		$identity = array();
		$title = array();
		$goodGuess = null;
		$usualSuspects = array("title","name");
		foreach($properties as $property){
			$tags = $reflectionService->getPropertyTagsValues($class,$property);
			if(in_array("title",array_keys($tags))){
				$title[] = \F3\FLOW3\Reflection\ObjectAccess::getProperty($this,$property);
			}
			
			if(in_array("identity",array_keys($tags))){
				$identity[] = \F3\FLOW3\Reflection\ObjectAccess::getProperty($this,$property);
			}
		
			if(in_array($property,$usualSuspects) && $goodGuess === null){
					$goodGuess = \F3\FLOW3\Reflection\ObjectAccess::getProperty($this,$property);
			}
		}
		
		if(count($title)>0)
			return implode(", ",$title);
		if(count($identity)>0)
			return implode(", ",$identity);
		if($goodGuess !== null)
			return $goodGuess;
	}
}

?>