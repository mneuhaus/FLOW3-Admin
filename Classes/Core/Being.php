<?php

namespace Admin\Core;

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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * represents a being used primarily for views
 * 
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("prototype")
 */
class Being{
	
	/**
	 * @var \Admin\Core\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;

	/**
	 * This Beings ID
	 *
	 * @var string
	 */
	public $id = null;
	
	/**
	 * The raw Object of this Being
	 *
	 * @var mixed
	 */
	public $object;
	
	/**
	 * ClassName of this Being
	 *
	 * @var string
	 */
	public $class;
	
	/**
	 * Responsible Adapter
	 *
	 * @var mixed
	 */
	public $adapter;
	
	/**
	 * Name of this Being
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 * Documents if this Being is selected in an relation Widget
	 *
	 * @var boolean
	 */
	public $selected = false;
	
	/**
	 * Beings Properties
	 *
	 * @var array()
	 */
	public $properties = array();
	
	/**
	 * Errors of this Being
	 *
	 * @var array
	 */
	protected $errors;
	
	/**
	 * Hidden Properties to be rendered
	 *
	 * @var string
	 */
	public $hiddenProperties = array();
	
	/**
	 * Configured Sets for this Being
	 *
	 * @var string
	 */
	protected $set = null;
	
	/**
	 * current Prefix for an input name
	 *
	 * @var string
	 */
	public $prefix = "item";

	public function __construct($adapter){
		$this->adapter = $adapter;
	}

	public function __toString(){
		if(is_object($this->object) && is_callable(array($this->object,"__toString")))
			return strval($this->object->__toString());
		return get_class($this->object);
	}
	
	public function addHiddenProperty($name, $value){
		$this->hiddenProperties = array_merge($this->hiddenProperties, array($name => $value));
	}
	
	public function getArguments(){
		return $this->object->getArguments();
	}
	
	public function getErrors($property = null) {
		return array();
		
		if($property == null)
			return $this->errors;
		else
			return isset($this->errors[$property]) ? $this->errors[$property] : array();
	}
	
	public function setErrors($errors) {
		$this->errors = $errors;
	}
	
	public function getSets(){
		$sets = array();
		if(is_array($this->set)){
			foreach($this->set as $set){
				$properties = explode(",", str_replace(", ",  ",", $set->properties));
				foreach($properties as $property){
					if(!isset($this->properties[$property])) continue;
					$sets[$set->title][$property] = $this->properties[$property];
				}
			}
		}else{
			foreach ($this->properties as $key => $value) {
				$sets[""][$key] = $value;
			}
		}
		return $sets;
	}
	
	public function getShortName() {
		$class = $this->name;
		if(is_object($class))
			$class = get_class($class);
	
		$parts = explode("\\", $class);
		return array_pop($parts);
	}
	
	public function getTemplate(){
		$b = $this->adapter->getBeing($this->class);
		$b->prefix = $this->parentProperty->getPrefix("{counter}");
		return $b;
	}
	
	public function getValue($property){
		return $this->adapter->getValue($property, $this->object);
	}
	
	public function setClass($class){
		$this->class = $class;
		
		$configuration = $this->configurationManager->getClassConfiguration($class);
		
		foreach ($configuration as $key => $values) {
			switch ($key) {
				case 'properties':
					foreach ($values as $property => $value) {
						if($this->shouldBeIgnored($value)) continue;
						$p = new \Admin\Core\Property($property, $this);
						$p->setParent($this);
						$p->setConfiguration($value);
						$this->properties[$property] = $p;
					}
					break;
				
				default:
					$this->$key = $values;
					break;
			}
		}
	}
	
	public function setObject($object) {
		$this->object = $object;
		$this->id = $this->adapter->getId($object);
	}
	
	/**
	 * checks the conf if the element should be ignored
	 *
	 * @param string $conf 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function shouldBeIgnored($annotations){
		if(isset($annotations["inject"])) return true;
		
		if(!isset($annotations["ignore"])){
			return false;
		}else{
			$ignore = current($annotations["ignore"]);
			if(empty($ignore->views)){
				return true;
			}else{
				$actions = explode(",", $ignore->views);
				$action = \Admin\Core\API::get("action");
				return in_array($action, $actions);
			}
		}
		
		return false;
	}
}

?>