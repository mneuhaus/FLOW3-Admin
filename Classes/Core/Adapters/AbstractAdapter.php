<?php

namespace Admin\Core\Adapters;

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
 * abstract base class for the Adapters
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractAdapter implements \Admin\Core\Adapters\AdapterInterface {
	/**
	 * @var TYPO3\FLOW3\Cache\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;
	
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;
	
	/**
	 * Holds the Converters
	 * @var array
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	protected $objectConverters = array();
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $packageManager;
	
	/**
	 * @var TYPO3\FLOW3\Property\PropertyMapper
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $propertyMapper;
	
	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;

	/**
	 * Initialize the Adapter
	 *
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function init() {
		$this->initializeConverters();
	}
	
	/**
	 * apply filters
	 *
	 * @param string $beings 
	 * @param string $filters 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function applyFilters($beings, $filters){
		$filtered = array();
		foreach($beings as $being){
			$matches = true;
			foreach($filters as $filter => $value){
				if($value != "_all_"){
					if(strval($being->getValue($filter)) != $value)
						$matches = false;
				}
			}
			if($matches)
				$filtered[] = $being;
		}
		return $filtered;
	}
	
	/**
	 * Gets the Processed Being
	 *
	 * @param string $being Name of Class of the Being
	 * @param string $id Identifier of the Being
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getBeing($being, $id = null) {
		$this->conf = $this->getConfiguration($being);
		
		$b = $this->objectManager->create("Admin\Core\Being",$this);
		$b->setClass($being);
		if($id !== null){
			$b->setObject($this->getObject($being, $id));
			$b->setId($id);
		}
		$properties = $this->getProperties($being);
		$b->setProperties($properties);
		$b->setSets($this->getSets(array_keys($properties)));
		return $b;
	}
	
	/**
	 * Gets multiple Processed Beings
	 *
	 * @param string $being Name of Class of the Being
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getBeings($being, $filters = null) {
		$this->conf = $this->getConfiguration($being);
		
		$objects = $this->getObjects($being);
		$beings = array ();
		if(!empty($objects)){
			foreach($objects as $object) {
				$b = $this->getBeing($being,$this->getId($object));
				$beings[] = $b;
			}
		}
		
		if($filters != null)
			$beings = $this->applyFilters($beings,$filters);
		
		return $beings;
	}
	
	public function getClass(){
		return "\\" . get_class($this);
	}
	
	/**
	 * returns classes that are taged with all of the specified tags
	 *
	 * @param string $tags 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function getClassesTaggedWith($tags){
		$cache = $this->cacheManager->getCache('Admin_ImplementationCache');
		$identifier = "ClassesTaggedWith-".implode("_",$tags);
		
		if(!$cache->has($identifier)){
			$classes = array();
			
			$activePackages = $this->packageManager->getActivePackages();
			foreach($activePackages as $packageName => $package) {
				if($packageName == "Doctrine") continue;
				foreach($package->getClassFiles() as $class => $file) {
					$classTags = $this->reflectionService->getClassTagsValues($class);
					
					if(isset($this->settings["Beings"][$class]))
						$classTags = array_merge($classTags,$this->settings["Beings"][$class]);
					$tagged = true;
					
					foreach($tags as $tag){
						if(!isset($classTags[$tag])) $tagged = false;
					}
					
					if($tagged)
						$classes[$class] = $packageName;
				}
			}
			
			$cache->set($identifier,$classes);
		}else{
			$classes = $cache->get($identifier);
		}
		return $classes;
	}
	
	/**
	 * Tries to get most of the Configuration automatically from most of the
	 * Sources like Class and YAML
	 *
	 * @param string $being Name of Class of the Being
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getConfiguration($being) {
		$cache = $this->cacheManager->getCache('Admin_ConfigurationCache');
		$identifier = str_replace("\\","_",$being)."-getConfiguration";
		
		if(!$cache->has($identifier) || true){
			$configuration = array();
			
			if(isset($this->settings["ConfigurationProvider"])){
				
				$configurationProviders = $this->settings["ConfigurationProvider"];
				foreach($configurationProviders as $configurationProviderClass){
					$configurationProvider = $this->objectManager->get($configurationProviderClass);
					$configurationProvider->injectAdapter($this);
					$configuration = array_merge_recursive($configuration,$configurationProvider->get($being));
				}
			}
			
			$configuration = $this->postProcessConfiguration($configuration);
			
			$cache->set($identifier,$configuration);
		}else{
			$configuration = $cache->get($identifier);
		}

		return $configuration;
	}
	
	public function getFilter($being,$selected = array()){
		$beings = $this->getBeings($being);
		$filters = array();
		foreach($beings as $being){
			$properties = $being->getProperties();
			foreach($properties as $property){
				if($property->isFilter()){
					if(!isset($filters[$property->getName()]))
						$filters[$property->getName()] = $this->objectManager->get("Admin\Core\Filter");

					if(isset($selected[$property->getName()]) && $selected[$property->getName()] == $property->getString()){
						$property->setSelected(true);
					}
					#$string = $property->getString();
					#if(!empty($string))
						$filters[$property->getName()]->addProperty($property);
				}
			}
		}
		return $filters;
	}
	
	/**
	 * returns the sanitized name of the Being
	 *
	 * @param string $being 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function getName($being) {
		$parts = explode("\\", $being);
		return str_replace("_AOPProxy_Development", "", end($parts));
	}
	
	/**
	 * Resolves Beings to Usable Options for Select Form Elements
	 *
	 * @param array $beings Array of the Beings
	 * @param array $selected Array of the Selected Keys
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getOptions($being, $selected = array()) {
		$options = array();
		if( is_string($being) )
			$beings = $this->getBeings($being);

		if( ! is_array($selected) )
			$selected = explode(",", $selected);

		if( empty($beings) )
			return array ();

		foreach($beings as $being) {
			$being->setSelected(in_array($being->getId(), $selected));
			$options [] = $being;
		}
		return $options;
	}
	
	/**
	 * Compiles the Properties of a Being
	 *
	 * @param string $being Name of Class of the Being
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getProperties($being){
		$configuration = $this->getConfiguration($being);
		
		$properties = array();
		foreach($configuration ["properties"] as $property => $conf) {
			if( $this->shouldBeIgnored($conf) ) continue;
			
			$p = $this->objectManager->create("Admin\Core\Property",$this);
			
			$p->setName($property);
			$p->setConf($conf);
			
			$properties[$property] = $p;
		}
		
		return $properties;
	}
	
	/**
	 * Resolves the Sets for a Being
	 *
	 * @param array $properties All properties from the Being
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getSets($properties) {
		$sets = array();
		if( !empty($this->conf) && isset($this->conf["class"]["annotations"]["set"]) ) {
			foreach($this->conf["class"]["annotations"]["set"] as $set) {
				$sets[$set["title"]] = explode(",", str_replace(" ", "", $set["properties"]));
			}
		}
		if( empty($sets) )
			$sets["General"] = $properties;
		
		return $sets;
	}
	
	/**
	 * returns the specified property of the mixed variable
	 *
	 * @param string $property 
	 * @param string $mixed 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function getValue($property, $mixed){
		$value = null;
		try {
			if(is_object($mixed) || is_array($mixed))
				$value = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($mixed, $property);
		} catch(\TYPO3\FLOW3\Reflection\Exception\PropertyNotAccessibleException $e) {}
		return $value;
	}
	
	/**
	 * Resolve the Type of Widget based on the Settings Configuration
	 *
	 * First step tries to Find a exact Match
	 * Second Step Tries every found pattern in the Settings as RegEx
	 * Third Step returns the Default if set
	 * Fourth Step simply Returns the Raw Value
	 *
	 * @param string $raw	 Raw Input Value to Search for in the Settings
	 * @param string $default If everything Fails this will be returned instead of the Raw Value
	 * @param string $path	Path in the Settings Array to Search for the Widget
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getWidget($raw, $default = null, $path = "Widgets.Mapping") {
		$cache = $this->cacheManager->getCache('Admin_Cache');
		$identifier = "Widget-".sha1($path.$raw.$default.__CLASS__);
		
		if(!$cache->has($identifier) || true){
			$widget = null;

			$mappings = \TYPO3\FLOW3\Reflection\ObjectAccess::getPropertyPath($this->settings, $path);
			if( ! empty($mappings) ) {
				
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
							$widget = $widget;
							break;
						}
					}
				}
			}

			if( $widget === null && $default !== null )
				$widget = $default;

			if($widget === null)
				$widget = $raw;

			$cache->set($identifier,$widget);
		}else{
			$widget = $cache->get($identifier);
		}

		return $widget;
	}
	
	/**
	 * Initializes the Datatype Converters which are later used in convertData
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function initializeConverters(){
		$cache = $this->cacheManager->getCache('Admin_ImplementationCache');

		$identifier = "ConverterInterface";
		if(!$cache->has($identifier)){
			$objectConverters = array();
			foreach($this->reflectionService->getAllImplementationClassNamesForInterface('Admin\Converter\ConverterInterface') as $objectConverterClassName) {
				$objectConverter = $this->objectManager->get($objectConverterClassName);
				foreach ($this->objectManager->get($objectConverterClassName)->getSupportedTypes() as $supportedType) {
					$objectConverters[$supportedType] = $objectConverterClassName;
				}
			}

			$cache->set($identifier,$objectConverters);
		}else{
			$objectConverters = $cache->get($identifier);
		}

		foreach($objectConverters as $supportedType => $objectConverterClassName){
			$objectConverters[$supportedType] = $this->objectManager->get($objectConverterClassName);
		}

		$this->objectConverters = $objectConverters;
	}
	
	/**
	 * PostProcesses the Configuration
	 * 
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function postProcessConfiguration($configuration){
		foreach($configuration["properties"] as $property => $c){
			if(array_key_exists("widget",$c))
				$configuration["properties"][$property]["widget"] = $c["widget"];
			else
				$configuration["properties"][$property]["widget"] = $this->getWidget($c["type"],"TextField");
			if(isset($c["optionsProvider"]) && is_array($c["optionsProvider"]))
				$configuration["properties"][$property]["optionsProvider"] = array_pop($c["optionsProvider"]);
		}
		return $configuration;
	}
	
	/**
	 * set filters
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function setFilter($filters){
		$this->filters = $filters;
	}
	
	/**
	 * checks the conf if the element should be ignored
	 *
	 * @param string $conf 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function shouldBeIgnored($conf){
		if(!isset($conf["ignore"]))
			return false;

		if($conf["ignore"] == "true")
			return true;

		$actions = explode(",",$conf["ignore"]);
		$action = \Admin\Core\Register::get("action");
		return in_array($action,$actions);

		return false;
	}
}

?>