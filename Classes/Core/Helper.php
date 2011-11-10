<?php

namespace Admin\Core;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Helper class to encapsulate various functions
 *
 * @package default
 * @author Marc Neuhaus
 */
class Helper {
	/**
	 * @var TYPO3\FLOW3\Cache\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;
	
	/**
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;
	
	/**
	 * Checks if the Variable is iteratable
	 *
	 * @param mixed $mixed $variable to check
	 * @return boolean
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	static function isIteratable($mixed){
		if(is_array($mixed))
			return true;

		if(is_object($mixed)){
			
			if($mixed instanceof \ArrayAccess)
				return true;

			if($mixed instanceof \SplObjectStorage)
				return true;

			if($mixed instanceof \Iterator)
				return true;
			
			if($mixed instanceof \Doctrine\ODM\MongoDB\PersistentCollection)
				return true;

			if($mixed instanceof \Doctrine\ODM\MongoDB\MongoCursor)
				return true;
		}
		
		return false;
	}
	
	/**
	 * return the Adapter responsible for the being
	 *
	 * @return $groups Array
	 * @author Marc Neuhaus
	 */
	public function getAdapterByBeing($being){
		$this->adapters = $this->getAdapters();
		
		$cache = $this->cacheManager->getCache('Admin_Cache');
		$identifier = "AdaptersByBeing-".sha1($being);
		
		if(!$cache->has($identifier)){
			$adaptersByBeings = array();
			foreach ($this->adapters as $adapter) {
				$adapters[$adapter] = $this->objectManager->get($adapter);
				foreach ($adapters[$adapter]->getGroups() as $group => $beings) {
					foreach ($beings as $conf) {
						$adaptersByBeings[$being] = $adapter;
					}
				}
			}
			
			$cache->set($identifier,$adaptersByBeings);
		}else{
			$adaptersByBeings = $cache->get($identifier);
		}
		
		return $adaptersByBeings[$being];
	}
	
	/**
	 * Returns all active adapters
	 *
	 * @return $adapters
	 * @author Marc Neuhaus
	 */
	public function getAdapters(){
		$settings = $this->getSettings();
		$adapters = array();
		foreach ($settings["Adapters"] as $adapter => $active) {
			if($active == "active"){
				$adapters[] = $adapter;
			}
		}
		return $adapters;
	}
	
	/**
	 * get the group which the being belongs to
	 *
	 * @param string $being 
	 * @return $group string
	 * @author Marc Neuhaus
	 */
	public function getGroupByBeing($being){
		$this->adapters = $this->getAdapters();
		foreach ($this->adapters as $adapter) {
			if(class_exists($adapter, false)){
				$adapters[$adapter] = $this->objectManager->get($adapter);
				foreach ($adapters[$adapter]->getGroups() as $group => $beings) {
					foreach ($beings as $beingName => $conf) {
						if($being == $beingName)
							return $group;
					}
				}
			}
		}
	}
	
	/**
	 * returns all active groups
	 *
	 * @return $groups Array
	 * @author Marc Neuhaus
	 */
	public function getGroups(){
		$this->adapters = $this->getAdapters();

		$cache = $this->cacheManager->getCache('Admin_Cache');
		$identifier = "Groups-".sha1(implode("-",$this->adapters));

		if(!$cache->has($identifier) || true){
			$groups = array();
			$adapters = array();
			foreach ($this->adapters as $adapter) {
				$adapters[$adapter] = $this->objectManager->get($adapter);
				foreach ($adapters[$adapter]->getGroups() as $group => $beings) {
					foreach ($beings as $conf) {
						$being = $conf["being"];
						$conf["adapter"] = $adapter;
						$groups[$group]["beings"][$being] = $conf;
					}
				}
			}

			$cache->set($identifier,$groups);
		}else{
			$groups = $cache->get($identifier);
		}

		return $groups;
	}
	
	
	/**
	 * Returns all Properties of a Specified Model
	 *
	 * @param $model String Name of the Model
	 * @return $properties Array of Model Properties
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getModelProperties($model){
		$tmpProperties = $this->reflectionService->getClassPropertyNames($model);
		foreach ($tmpProperties as $property) {
			$properties[$property] = $this->reflectionService->getPropertyTagsValues($model,$property);
			if(!in_array("var",array_keys($properties[$property]))) continue;
			$properties[$property]["identity"] = in_array("identity",array_keys($properties[$property])) ? "true" : "false";
		}
		unset($tmpProperties);
		return $properties;
	}
	
	/**
	 *
	 * @param $model String Name of the Model with Namespace
	 * @return $name String Model Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	static public function getPackageByClassName($model){
		preg_match("/\\\([^\\\\]+)/",$model,$match);
		if(count($match)>0)
			return $match[1];
	}
	
	/**
	 * returns a template Path by checking configured fallbacks
	 *
	 * @param string $patterns 
	 * @param string $replacements 
	 * @return $path String
	 * @author Marc Neuhaus
	 */
	public function getPathByPatternFallbacks($patterns, $replacements){
		if(is_string($patterns)){
			$paths = explode(".",$patterns);
			$patterns = $this->getSettings();
			$patterns = $patterns["Fallbacks"];
			foreach ($paths as $path) {
				$patterns = $patterns[$path];
			}
		}
		
		foreach($patterns as $pattern){
			$pattern = str_replace(array_keys($replacements),array_values($replacements),$pattern);
			$tried[] = $pattern;
			if(file_exists($pattern)){
				return $pattern;
			}
		}
		
		throw new \Exception('Could not find any Matching Path.');
	}
	
	/**
	 * Returns the Settings of that namespace and caches it
	 *
	 * @param $namespace String Namespace
	 * @return $settings Array of settings
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getSettings($namespace = "Admin"){
		if(!isset($this->cache["settings"]) || !isset($this->cache["settings"][$namespace])){
			$this->cache["settings"][$namespace] = $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $namespace);
		}
		return $this->cache["settings"][$namespace];
	}
	
	/**
	 * returns the shortname representation of the class
	 *
	 * @package default
	 * @author Marc Neuhaus
	 */
	static function getShortName($class){
		if(is_object($class))
			$class = get_class($class);
		
		if(class_exists($class, false)){
			$parts = explode("\\", $class);
			return array_pop($parts);
		}
		
		return $class;
	}

	/**
	 * Returns the Repository for a Model based on the Class name
	 *
	 * @param $model String Name of the Model
	 * @return $repository String Repository Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	static public function getModelRepository($model){
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		if(substr($repository,0,1) == "\\"){
			$repository = substr($repository,1);
		}
		return $repository;
	}
}

?>