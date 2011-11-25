<?php

namespace Admin\Core;

/*																		*
 * This script belongs to the FLOW3 package "Fluid".					  *
 *																		*
 * It is free software; you can redistribute it and/or modify it under	*
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.											 *
 *																		*
 * This script is distributed in the hope that it will be useful, but	 *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-	*
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser	   *
 * General Public License for more details.							   *
 *																		*
 * You should have received a copy of the GNU Lesser General Public	   *
 * License along with the script.										 *
 * If not, see http://www.gnu.org/licenses/lgpl.html					  *
 *																		*
 * The TYPO3 project - inspiring people to share!						 *
 *																		*/

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * represents a beings property
 * 
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("prototype")
 */
class Property{
	const INLINE_SINGLE_MODE = "single";
	const INLINE_MULTIPLE_MODE = "multiple";
	
	/**
	 * @var \Admin\Core\Helper
	 * @FLOW3\Inject
	 */
	protected $helper;

	
	public $adapter;

	public $being = null;
	
	/**
	 * Label for the Property
	 *
	 * @var string
	 */
	public $label;
	
	/**
	 * Name of this Property
	 *
	 * @var string
	 */
	public $name;
	
	protected $type = "string";
	protected $widget;
	protected $options;
	protected $configuration;
	public $parent;
	public $mode = "single";
	protected $children = array();
	protected $counter = 0;
	protected $value = null;
	protected $filter = false;
	protected $selected = false;

	public function  __construct($name, $being) {
		$this->name = $name;
		$this->adapter = $being->adapter;
	}
	
	public function getInputName(){
		if($this->mode == self::INLINE_MULTIPLE_MODE)
			return $this->parent->prefix."[".$this->name."][]";
		
		return $this->parent->prefix."[".$this->name."]";
	}
	
	public function getPrefix($counter = null){
		$counter = is_null($counter) ? $this->counter : $counter;
		if($this->mode == self::INLINE_MULTIPLE_MODE)
			return $this->parent->prefix."[".$this->name."][".$counter."]";
		
		return $this->parent->prefix."[".$this->name."]";
	}
	
	public function getString(){
		return $this->value->__toString();
	}
	
	public function getValue(){
		return $this->parent->getValue($this->name);
	}
	
	public function getWidget() {
		$raw = $this->type;
		
		$widget = null;
		$default = "Textfield";
		
		$mappings = $this->helper->getSettings("Admin.Mapping.Widgets");
		
		if( ! empty($mappings) ) {
			if(isset($this->widget))
				$widget = $this->widget->name;
				
			if(isset($this->editor))
				$widget = "Textarea";
			
			if( $widget === null && isset($mappings[$raw]) ) {
				$widget = $mappings[$raw];
			}
			
			if( $widget === null && isset($mappings[strtolower($raw)]) ) {
				$widget = $mappings[$raw];
			}
			
			if( $widget === null && isset($mappings[ucfirst($raw)]) ) {
				$widget = $mappings[$raw];
			}
			
			if( $widget === null){
				foreach($mappings as $pattern => $widget) {
					if( preg_match("/" . $pattern . "/", $raw) > 0 ) {
						break;
					}
				}
			}
		}
		
		if( $widget === null && $default !== null )
			$widget = $default;
		
		if($widget === null)
			$widget = $raw;
		
		return $widget;
	}
	
	public function setConfiguration($configuration){
		$this->configuration = $configuration;
		
		$this->value = new \Admin\Core\Value($this, $this->adapter);
		
		$this->label = ucfirst($this->name);
		$this->variant = new \Admin\Annotations\Variant();
		
		foreach ($configuration as $key => $values) {
			switch ($key) {
				case 'var':
					$this->$key = current($values);
					$this->type = current($values);
					
					preg_match("/<(.+)>/", $this->$key, $matches);
					if(!empty($matches)){
						$this->type = ltrim($matches[1],"\\");
						$this->being = ltrim($matches[1],"\\");
					}else{
						$this->type = current($values);
						$this->being = current($values);
					}
					
					break;
					
				case 'onetomany':
				case 'manytomany':
					#$objects = $this->adapter->getValue($this->name, $this->parent->object);
					$this->mode = self::INLINE_MULTIPLE_MODE;
					$this->$key = $values;
					break;
				
				case 'manytoone':
				case 'onetoone':
					$this->mode = self::INLINE_SINGLE_MODE;
					$this->$key = $values;
					break;
					
				default:
					if(is_array($values) && count($values) == 1){
						$this->$key = current($values);
					}else{
						$this->$key = $values;
					}
					break;
			}
		}
	}
	
	
	
	
	public function getOptions() {
		$options = array();
		
		if(isset($this->optionsProvider)){
			$provider = new $this->optionsProvider->name;
			$provider->setProperty($this);
			$options = $provider->getOptions();
		}
		return $options;
	}

	public function getConfiguration() {
		return $this->configuration;
	}

	public function getChildren() {
		if($this->inline && empty($this->children)){
			$values = $this->getValue();
			$beings = array();
			$amountOfInlines = 0;
			if(\Admin\Core\Helper::isIteratable($values)){
				foreach($values as $value){
					if(is_object($value)){
						$id = $this->adapter->getId($value);
						$being = $this->createBeing($this->being, $id);
						$beings[] = $being;
					}
					$this->counter++;
				}
			}elseif(!empty($values) && is_object($values)){
				$id = $this->adapter->getId($values);
				$being = $this->createBeing($this->being, $id);
				$beings[] = $being;
				$this->counter++;
			}else{
				$amountOfInlines = 1;
			}
			
			if($this->mode == self::INLINE_MULTIPLE_MODE)
				$amountOfInlines = 1;
			
			for ($index = 0; $index < $amountOfInlines; $index++) {
				$being = $this->createBeing($this->being);
				if($this->mode == self::INLINE_MULTIPLE_MODE)
					$being->unusedClass = "inline-unused";
				$beings[] = $being;
				$this->counter++;
			}
			
			foreach ($beings as $key => $being) {
				$beings[$key]->parentProperty = $this;
			}
			
			$this->children = $beings;
		}
		return $this->children;
	}
	
	public function getChild(){
		return current($this->getChildren());
	}

	public function createBeing($being, $id = null){
		$b = $this->adapter->getBeing($this->being, $id);
		$b->prefix = $this->getPrefix();
		
		if(!empty($id)){
#			$identity = array( $this->getPrefix() . "[__identity]" => $id );
			$b->addHiddenProperty($this->getPrefix() . "[__identity]",  $id);
		}

		return $b;
	}

	public function setParent($parent){
		$this->parent = $parent;
	}

	protected function getParent(){
		return $this->parent;
	}

	public function getIds(){
		return $this->value->getIds();
	}
	
	public function isFilter(){
		return false;
	}
	
	public function getError(){
		if(is_object($this->getParent()))
			return $this->getParent()->getErrors($this->name);
	}
	
	public function isMultiple(){
		return $this->mode == self::INLINE_MULTIPLE_MODE;
	}
}

?>