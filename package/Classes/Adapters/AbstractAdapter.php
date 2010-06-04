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
abstract class AbstractAdapter implements AdapterInterface {
	/**
	 * @var \F3\Admin\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $helper;
	
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
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $reflectionService;
	
	public function init(){
		$this->session = \F3\Admin\register::get("session");
		
		if(\F3\Admin\register::has("being")){
			$this->being = \F3\Admin\register::get("being");
			$this->conf = $this->getConfiguration($this->being);
		}
		
		if(!empty($this->being) && $this->session->hasKey($this->being))
			$this->userSettings = $this->session->getData($this->being);
		else
			$this->userSettings = array();
	}
	
	public function __destruct(){
		#$this->session->setData($this->being,$this->userSettings);
	}
	
	public function restoreSorting(){
		if(empty($this->userSettings["sorting"]) && array_key_exists("sort",$this->conf["class"])){
			$parts = explode(" ",$this->conf["class"]["sort"][0]);
			$this->userSettings["property"] = array(
				"property" => $parts[0],
				"direction" => $parts[1]
			);
		}
		$this->setSorting($this->userSettings["sorting"]["property"],$this->userSettings["sorting"]["direction"]);
	}
	
	public function setSorting($property,$direction = "asc"){}
	
	public function restoreLimit(){
		if(empty($this->userSettings["limit"]) && array_key_exists("limit",$this->conf["class"])){
			$this->userSettings["limit"] = $this->conf["class"]["limit"][0];
		}
		$this->setLimit($this->userSettings["limit"]);
	}
	
	public function setLimit($limit){}
	
	public function restoreOffset(){
		if(empty($this->userSettings["offset"]) && array_key_exists("offset",$this->conf["class"])){
			$this->userSettings["offset"] = $this->conf["class"]["offset"][0];
		}
		$this->setOffset($this->userSettings["offset"]);
	}
	
	public function setOffset($offset){}
	
	public function groupPropertiesIntoSets($attributes){
		$sets = array();
		if(!empty($this->conf) && isset($this->conf["class"]["set"])){
			foreach ($this->conf["class"]["set"] as $set) {
				preg_match("/(.*)\(([a-z, ]+)\)/",$set,$matches);
				if(!isset($matches[2])) continue;
				
				$setName = isset($matches[1]) ? $matches[1] : "General";
				$fields = str_replace(" ","",$matches[2]);
				
				$setAttributes = array_intersect_key($attributes, array_flip(explode(",",$fields)));
				if(count($setAttributes)>0)
					$sets[$setName] = $setAttributes;
			}
		}
		if(empty($sets))
			$sets["General"] = array_values($attributes);
		
		return $sets;
	}
	
	public function getConfiguration($being){
		$configuration = array(
			"class" => $this->reflection->getClassTagsValues($being),
			"properties" => $this->helper->getModelProperties($being)
		);
		
		return $configuration;
	}
	
	public function transformToObject($being,$data,$target=null,$propertyMapper = null){
		$data = $this->cleanUpItem($data);

		$arg = $this->objectManager->get("F3\Admin\Argument","item",$being);
		if($propertyMapper !== null)
			$arg->replacePropertyMapper($propertyMapper);
		if($target !== null)
			$arg->setTarget($target);
		$validator = $this->helper->getModelValidator($being);
		if(is_object($validator))
			$arg->setValidator($validator);
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
		}
		
		return array(
			"errors" => $errors,
			"object" => $targetObject
		);
	}
	
	public function cleanUpItem($item){
		foreach ($item as $key => $value) {
			if(is_array($value)){
				$item[$key] = $this->cleanUpItem($value);
			}
			if(is_object($value) && !empty($value->FLOW3_Persistence_Entity_UUID)){
				$item[$key] = $value->FLOW3_Persistence_Entity_UUID;
			}
			if(empty($item[$key]) && $item[$key] !== false){
				unset($item[$key]);
			}
		}
		return $item;
	}
	
	public function getName($being){
		$parts = explode("\\",$being);
		return str_replace("_AOPProxy_Development","",end($parts));
	}
	
	public function getLabel($conf,$property){
		if(array_key_exists("label",$conf) && is_array($conf["label"]))
			return $conf["label"][0];
			
		return ucfirst($property);
	}

	public function getSetting($raw,$default = null,$path = "Widgets.Mapping"){
		$mappings = \F3\FLOW3\Reflection\ObjectAccess::getPropertyPath($this->settings,$path);

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

	public function convertValues($values,$properties){
		$values = $this->cleanUpItem($values);
		foreach ($values as $property => $value) {
			$values[$property] = $this->convertValue($value,$properties[$property]["var"][0],"storage",$properties[$property]);
		}
		return $values;
	}

	public function convertValue($value,$type,$target="presentation",$conf = array()){
		$widgetType = $this->getSetting($type,"TextField");
		#echo "<br />".$type." (".$target.")";
		#\F3\dump(array(
		#	"value" => $value,
		#	"type" => $type,
		#	"target" => $target,
		#	"conf" => $conf
		#));
		if($target == "presentation"){
			switch ($type) {
				case 'string':
					return strval($value);
				case 'integer':
					return intval($value);
				case 'float':
					return floatval($value);
				case 'boolean':
					return $value ? "true" : "false";

				default:
					$callback = $this->getCallback($this->getSetting($type,null,"Conversions.Presentation"),$conf);
					if(!empty($callback))
						return call_user_func($callback,$value,$conf);
					return $value;
					break;
			}
		}else{
			switch ($type) {
				case 'string':
					return strval($value);
				case 'integer':
					return intval($value);
				case 'float':
					return floatval($value);
				case 'boolean':
					return $value == "true" ? true : false;

				default:
					$callback = $this->getCallback($this->getSetting($type,null,"Conversions.Storage"),$conf);
					if(!empty($callback))
						return call_user_func($callback,$value,$conf);
					return $value;
					break;
			}
		}
	}

	public function getCallback($raw){
		$callback = null;

		if(function_exists($raw)){
			$callback = $raw;
		}elseif(stristr($raw,"::")){
			$callback = $raw;
		}elseif(stristr($raw,"->")){
			$parts = explode("->",$raw);

			if($parts[0] == __CLASS__ || $parts[0] == "self" || $parts[0] == "this"){
				$callback = array(
					$this,
					$parts[1]
				);
			}elseif(class_exists($parts[0])){
				$callback = array(
					$this->objectManager->getObject($parts[0]),
					$parts[1]
				);
			}
		}
		
		return $callback;
	}
	
	public function toString($object){
		if(is_callable(array($object,"__toString")))
			return $object->__toString();
			
		$class = get_class($object);
		$properties = $this->reflectionService->getClassPropertyNames($class);
		$identity = array();
		$title = array();
		$goodGuess = null;
		$usualSuspects = array("title","name");
		foreach($properties as $property){
			$tags = $this->reflectionService->getPropertyTagsValues($class,$property);
			if(in_array("title",array_keys($tags))){
				$title[] = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
			}
			
			if(in_array("identity",array_keys($tags))){
				$value = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
				if(is_string($value) || is_integer($value))
					$identity[] = $value;
			}
		
			if(in_array($property,$usualSuspects) && $goodGuess === null){
				$goodGuess = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
			}
		}
		
		if(count($title)>0)
			return implode(", ",$title);
		if(count($identity)>0)
			return implode(", ",$identity);
		if($goodGuess !== null)
			return $goodGuess;
	}
	
	public function getOptions($beings,$selected = array()){
		if(is_string($beings)) $beings = $this->getBeings($beings);
		if(empty($beings)) return array();
		
		$options = array(""=>"");
		foreach ($beings as $being) {
			$options[] = array(
				"id" => $being["meta"]["id"],
				"name" => $being["meta"]["name"],
				"selected" => in_array($being["meta"]["id"],$selected)
			);
		}
		
		return $options;
	}
	
	
	## Conversion Functions
	public function dateTimeToString($datetime,$conf){
		if(is_object($datetime)){
			$format = array_key_exists("format",$conf) ? $conf["format"][0] : "H:i:s d.m.Y";
			$string = date($format,$datetime->getTimestamp());
			return $string;
		}
		return $datetime;
	}
	
	public function stringToDateTime($string){
		if(is_object($string) && get_class($string) == "DateTime")
			return $string;
		if(!empty($string)){
			$datetime = new \DateTime($string);
			return $datetime;
		}
		return null;
	}
	
	public function identifierToModel($identifier){
		if(!empty($identifier)){
			return $this->persistenceManager->getObjectByIdentifier($identifier);
		}
	}
	
	public function modelToIdentifier($model){
		if(is_object($model)){
			return array(array(
				"id" => $this->persistenceManager->getIdentifierByObject($model),
				"name" => $this->toString($model)
			));
		}
	}
	
	public function identifiersToSplObjectStorage($identifiers){
		$spl = new \SplObjectStorage();
		foreach ($identifiers as $identifier) {
			$spl->attach($this->identifierToModel($identifier));
		}
		return $spl;
	}
	
	public function splObjectStorageToIdentifiers($spl){
		$identifiers = array();
		if(count($spl)>0){
			foreach ($spl as $model) {
				$identifiers[] = current($this->modelToIdentifier($model));
			}
		}
		return $identifiers;
	}
}

?>