<?php
 
namespace F3\Admin;

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

/**
 * @api
 * @scope singleton
 */
class Helper{
	/**
	 * Reflection service
	 * @var F3\FLOW3\Reflection\ReflectionService
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	private $reflection;
	
	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;
	
	/**
	 * @var \F3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $packageManager;

	/**
	 * @var \F3\FLOW3\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 *
	 * @var \F3\FLOW3\Validation\ValidatorResolver
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 **/
	protected $ValidatorResolver;

	/**
	 * Removes escapings from a given argument string.
	 *
	 * This method is meant as a helper for regular expression results.
	 *
	 * @param string &$quotedValue Value to unquote
	 * @return string Unquoted value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function unquoteString(&$quotedValue) {
		switch ($quotedValue[0]) {
			case '"':
				$quotedValue = str_replace('\"', '"', trim($quotedValue, '"'));
			break;
			case '\'':
				$quotedValue = str_replace('\\\'', '\'', trim($quotedValue, '\''));
			break;
		}
		$quotedValue = str_replace('\\\\', '\\', $quotedValue);
	}

	/**
	 * Returns the Repository for a Model based on the Class name
	 *
	 * @param $model String Name of the Model
	 * @return $repository String Repository Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function getModelRepository($model){
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		if(substr($repository,0,1) == "\\"){
			$repository = substr($repository,1);
		}
		return $repository;
	}

	/**
	 * Returns the Name of the Model without Namespace
	 *
	 * @param $model String Name of the Model with Namespace
	 * @return $name String Model Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getObjectNameByClassName($model){
		$parts = explode("\\",$model);
		return str_replace("_AOPProxy_Development","",end($parts));
	}
	
	/**
	 *
	 * @param $model String Name of the Model with Namespace
	 * @return $name String Model Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getPackageByClassName($model){
		preg_match("/F3\\\\([^\\\\]+)/",$model,$match);
		if(count($match)>0)
        	return $match[1];
	}

	/**
	 * Returns the Main Type of the Model Property
	 * 
	 * For example: SplObjectStorage<DateTime> -> SplObjectStorage
	 *
	 * @param $raw String raw Type
	 * @return $name String Type
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function getWidgetType($raw){
		$settings = $this->getSettings();
		$mappings = $settings["Widgets"]["Mapping"];
		
		if(isset($mappings[$raw])){
			return $mappings[$raw];
		}
		
		foreach ($mappings as $pattern => $widget) {
			if(preg_match("/".$pattern."/",$raw) > 0){
				return $widget;
			}
		}
		
		return $raw;
    }

	/**
	 * Returns the Sub Type of the Model Property
	 * 
	 * For example: SplObjectStorage<DateTime> -> DateTime
	 *
	 * @param $raw String raw Type
	 * @return $name String Type
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function getElementType($raw){
		preg_match("/<(.*?)>/",$raw,$match);
		if(count($match)>0)
        	return $match[1];
		return false;
    }

	/**
	 * Checks if the Class is a manageble Entity
	 *
	 * @param $checkClass String Name of the Class
	 * @return $isEntity Boolean
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function isEntity($checkClass){
		if(substr($checkClass,0,1) == "\\") $checkClass = substr($checkClass,1);
		if(!isset($this->cached["entities"])){
			$activePackages = $this->packageManager->getActivePackages();
			$this->cached["entities"] = array();
			foreach ($activePackages as $packageName => $package) {
				foreach ($package->getClassFiles() as $class => $file) {
					if(strpos($class,"\Model\\")>0){
						$tags = $this->reflection->getClassTagsValues($class);
						$parts = explode('\\',$class);
						$name = end($parts);
						if(in_array("autoadmin",array_keys($tags))){
							$this->cached["entities"][$class] = $name;
						}
					}
				}
			}
		}
		return isset($this->cached["entities"][$checkClass]) ? $this->cached["entities"][$checkClass] : false;
	}

	/**
	 * Returns the Class of the Widget for a property Type
	 *
	 * @param $type String Type of the Property
	 * @return $widgetClass String Class of the Widget
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function getWidgetClass($type){
        $widgetWidgetType = $type = $this->getWidgetType($type);
        return str_replace("@type",$widgetWidgetType,"F3\Admin\Widgets\@typeWidget");
    }
	
	/**
	 * Returns all Models wich are enabled through the @autoadmin tag
	 *
	 * @return $packages Array of the Models grouped by Packages
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getEnabledActions()
	{
		if(!isset($this->cached["actions"])){
			$activePackages = $this->packageManager->getActivePackages();
			$this->cached["actions"] = array();
			foreach ($activePackages as $packageName => $package) {
				foreach ($package->getClassFiles() as $class => $file) {
					if(strpos($class,"\Controller\\")>0){
						$methods= $this->reflection->getClassMethodNames($class);
						foreach($methods as $method){
							$tags = $this->reflection->getMethodTagsValues($class,$method);
							if(in_array("autoadmin",array_keys($tags))){
								$this->cached["actions"][$packageName][] = array(
									"class" => $class,
									"name"	=> $method
								);
							}
						}
					}
				}
			}
		}
		return $this->cached["actions"];
	}
	
	/**
	 * Returns a Validator for the given Model
	 *
	 * @param $model String Class of the Model
	 * @return $validator GenericObjectValidator
	 * @author Marc Neuhaus
	 **/
	public function getModelValidator($model){
		$objectValidator = $this->ValidatorResolver->createValidator('F3\FLOW3\Validation\Validator\GenericObjectValidator');
		if (class_exists($model)) {
			$validatorCount = 0;

			foreach ($this->reflection->getClassPropertyNames($model) as $classPropertyName) {
				$classPropertyTagsValues = $this->reflection->getPropertyTagsValues($model, $classPropertyName);
				if (!isset($classPropertyTagsValues['validate'])) continue;

				foreach ($classPropertyTagsValues['validate'] as $validateValue) {
					$parsedAnnotation = $this->parseValidatorAnnotation($validateValue);
					foreach ($parsedAnnotation['validators'] as $validatorConfiguration) {
						$newValidator = $this->ValidatorResolver->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
						if ($newValidator === NULL) {
							throw new \F3\FLOW3\Validation\Exception\NoSuchValidatorException('Invalid validate annotation in ' . $model . '::' . $classPropertyName . ': Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".', 1241098027);
						}
						$objectValidator->addPropertyValidator($classPropertyName, $newValidator);
						$validatorCount ++;
					}
				}
			}
		}
		return $objectValidator;
	}
	
	/**
	 * Find errors for a specific property in the given errors array
	 *
	 * @param string $propertyName The property name to look up
	 * @param array $errors An array of F3\FLOW3\Error\Error objects
	 * @return array An array of errors for $propertyName
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getErrorsForProperty($propertyName, $errors) {
		if(is_array($errors)){
			foreach ($errors as $error) {
				if ($error instanceof \F3\FLOW3\Validation\PropertyError) {
					if ($error->getPropertyName() === $propertyName) {
						return $error->getErrors();
					}
				}
			}
		}
		return array();
	}
	
	/**
	 * Returns all Properties of a Specified Model
	 *
	 * œparam $model String Name of the Model
	 * @return $properties Array of Model Properties
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getModelProperties($model){
		$tmpProperties = $this->reflection->getClassPropertyNames($model);
		foreach ($tmpProperties as $property) {
			$properties[$property] = $this->reflection->getPropertyTagsValues($model,$property);
			if(!in_array("var",array_keys($properties[$property]))) continue;
			$properties[$property]["identity"] = in_array("identity",array_keys($properties[$property])) ? "true" : "false";
		}
		unset($tmpProperties);
		return $properties;
	}
	
	public function getFilters($properties,$objects,$active){
		$filters = array();
		foreach ($properties as $property => $tags) {
			if(isset($tags["adminfilter"])){
				$filters[$property] = array();
				foreach ($objects as $object) {
					$value = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
					
					$filters[$property][$value] = isset($active[$property]) && $active[$property]==$value ? true : false;
				}
			}
		}
		return $filters;
	}
	
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
			if(file_exists($pattern)){
				return $pattern;
			}
		}
	}
	
	public function getSettings($namespace = "Admin"){
		if(!isset($this->cache["settings"])){
			$this->cache["settings"] = $this->configurationManager->getConfiguration(\F3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $namespace);
		}
		return $this->cache["settings"];
	}
	
	public function toString($object){
		if(is_callable(array($object,"__toString"))){
			return $object->__toString();
		}
		
		$class = get_class($object);
		$properties = $this->reflection->getClassPropertyNames($class);
		$identity = array();
		$title = array();
		$goodGuess = null;
		$usualSuspects = array("title","name");
		foreach($properties as $property){
			$tags = $this->reflection->getPropertyTagsValues($class,$property);
			
			if(in_array("title",array_keys($tags))){
				$title[] = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
			}
			if(in_array("identity",array_keys($tags))){
				$identity[] = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
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
			
		return "Can't provide useful String representation";
	}
	
	public function arrayToObject($array){
		$object = new \StdClass;
		foreach ($array as $key => $value) {
			$object->$key = $value;
		}
		return $object;
	}
	
	public function groupArrayByKeys($array)
	{
		$keys = array();
		foreach ($array as $sub) {
			if(is_array($sub)){
				foreach ($sub as $key => $value) {
					if(!array_key_exists($key,$keys)){
						$keys[$key] = 1;
					}else{
						$keys[$key]++;
					}
				}
			}
		}
		if(!empty($keys) && max($keys) == min($keys)){
			$newArray = array();
			$tmp = array();
			foreach ($array as $sub) {
				if(is_array($sub)){
					foreach ($sub as $key => $value) {
						if(array_key_exists($key,$tmp)){
							$newArray[] = $tmp;
							$tmp = array();
						}
						$tmp[$key] = $value;
					}
				}
			}	
			$newArray[] = $tmp;
			return $newArray;
		}else{
			return $array;
		}
	}
	
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
	
	public function getGroups(){
		$this->adapters = $this->getAdapters();
		$groups = array();
		foreach ($this->adapters as $adapter) {
			if(class_exists($adapter)){
				$adapters[$adapter] = $this->objectManager->getObject($adapter);
				foreach ($adapters[$adapter]->getGroups() as $group => $beings) {
					foreach ($beings as $being => $conf) {
						$conf["adapter"] = $adapter;
						$groups[$group][$being] = $conf;
					}
				}
			}
		}
		return $groups;
	}
	
	public function getGroupByBeing($being){
		$this->adapters = $this->getAdapters();
		foreach ($this->adapters as $adapter) {
			if(class_exists($adapter)){
				$adapters[$adapter] = $this->objectManager->getObject($adapter);
				foreach ($adapters[$adapter]->getGroups() as $group => $beings) {
					foreach ($beings as $beingName => $conf) {
						if($being == $beingName)
							return $group;
					}
				}
			}
		}
	}
}

?>
