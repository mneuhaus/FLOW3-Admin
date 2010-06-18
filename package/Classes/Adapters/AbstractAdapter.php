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

    /**
     * Holds the Converters
     * @var array
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    protected $objectConverters = array();

    /**
     * Initialize the Adapter
     *
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function init() {
        $this->initializeConverters();
	}

    /**
     * Initializes the Datatype Converters which are later used in convertData
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function initializeConverters(){
        $objectConverters = array();
        foreach($this->reflectionService->getAllImplementationClassNamesForInterface('F3\FLOW3\Property\ObjectConverterInterface') as $objectConverterClassName) {
            $objectConverter = $this->objectManager->get($objectConverterClassName);
            foreach ($this->objectManager->get($objectConverterClassName)->getSupportedTypes() as $supportedType) {
                $objectConverters[$supportedType] = $objectConverter;
            }
        }
        $this->objectConverters = $objectConverters;
    }

    public function getClass(){
        return ltrim(get_class($this),"\\");
    }

	public function getName($being) {
		$parts = explode("\\", $being);
		return str_replace("_AOPProxy_Development", "", end($parts));
	}

	public function getLabel($property) {
		return ucfirst($property);
	}

    /**
     * Tries to get most of the Configuration automatically from most of the
     * Sources like Class and YAML
     *
     * @param string $being Name of Class of the Being
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getConfiguration($being) {
		$configuration = array ();
		$configuration = array_merge($configuration,$this->getClassAnnotationConfiguration($being));
		$configuration = array_merge($configuration,$this->getYamlConfiguration($being));
		return $configuration;
	}

    /**
     * Tries to get the Configuration from the Class if it Exists
     *
     * @param string $being Name of Class of the Being
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function getClassAnnotationConfiguration($being){
		$configuration = array ();
		if( class_exists($being) ) {
			$configuration = array (
                "class" => $this->reflectionService->getClassTagsValues($being),
                "properties" => $this->helper->getModelProperties($being)
            );

            foreach($configuration["properties"] as $property => $conf) {
                // Injected Properties shouldn't be managed
                if( array_key_exists("inject", $conf) || array_key_exists("ignore", $conf) )
                    $configuration["properties"][$property]["ignore"] = true;

                foreach($conf as $key => $value){
                    if(is_array($value) && empty($value)){
                        $configuration["properties"][$property][$key] = true;
                    }

                    if(is_array($value) && count($value) == 1){
                        while(is_array($value))
                            $value = current($value);
                        $configuration["properties"][$property][$key] = $value;
                    }
                }
            }
        }
        return $configuration;
    }

    /**
     * Tries to get the Configuration from the Settings.yaml if
     * it Contains Configuration about the being.
     *
     * @param string $being Name of Class of the Being
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function getYamlConfiguration($being){
        $configuration = array();
        
        if(isset($this->settings["Beings"]))
            $configuration = $this->settings["Beings"];

        return $configuration;
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
        }
        return $configuration;
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
        
        $b = $this->objectManager->create("F3\Admin\Core\Being",$this);
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

            $p = $this->objectManager->create("F3\Admin\Core\Property",$this);

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
		$sets = array ();
		if( !empty($this->conf) && isset($this->conf["set"]) ) {
			foreach($this->conf["set"] as $set) {
				preg_match("/(.*)\(([A-Za-z0-9, ]+)\)/", $set, $matches);
				if( ! isset($matches [2]) ) continue;

				$setName = isset($matches [1]) ? $matches [1] : "";
				$fields = str_replace(" ", "", $matches [2]);

				$sets [$setName] = explode(",", $fields);
				unset($matches);
			}
		}
		if( empty($sets) )
			$sets ["General"] = $properties;
        
		return $sets;
	}

    /**
     * Resolve the Type of Widget based on the Settings Configuration
     *
     * First step tries to Find a exact Match
     * Second Step Tries every found pattern in the Settings as RegEx
     * Third Step returns the Default if set
     * Fourth Step simply Returns the Raw Value
     *
     * @param string $raw     Raw Input Value to Search for in the Settings
     * @param string $default If everything Fails this will be returned instead of the Raw Value
     * @param string $path    Path in the Settings Array to Search for the Widget
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getWidget($raw, $default = null, $path = "Widgets.Mapping") {
		$mappings = \F3\FLOW3\Reflection\ObjectAccess::getPropertyPath($this->settings, $path);
        
		if( ! empty($mappings) ) {
			if( isset($mappings[$raw]) ) {
				return $mappings[$raw];
			}

			if( isset($mappings[strtolower($raw)]) ) {
				return $mappings[$raw];
			}

			if( isset($mappings[ucfirst($raw)]) ) {
				return $mappings[$raw];
			}

			foreach($mappings as $pattern => $widget) {
				if( preg_match("/" . $pattern . "/", $raw) > 0 ) {
					return $widget;
				}
			}
		}
		if( $default !== null )
			return $default;

		return $raw;
	}

    public function getValue($property, $mixed){
        $value = null;
        try {
            if(is_object($mixed) || is_array($mixed))
                $value = \F3\FLOW3\Reflection\ObjectAccess::getProperty($mixed, $property);
        } catch(\F3\FLOW3\Reflection\Exception\PropertyNotAccessibleException $e) {}
        return $value;
    }

    /**
     * Resolves Beings to Usable Options for Select Form Elements
     *
     * @param array $beings Array of the Beings
     * @param array $selected Array of the Selected Keys
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getOptions($being, $selected = array()) {
        #$options = array ("" => "" );
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
     * Transforms an Array to a Specific Target Type/Object
     *
     * @param string $being Class/Name of the Being
     * @param array $data
     * @param object $target Specific Object to Map the Data to
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function transformToObject($being, $data, $target = null) {
		$data = $this->cleanUpItem($data);
		$data = $this->convertData($data,$being);

		$arg = $this->objectManager->get("F3\Admin\Core\Argument", "item", $being);

        if( $target !== null )
			$arg->setTarget($target);

        $validator = $this->helper->getModelValidator($being);
        if( is_object($validator) )
                $arg->setValidator($validator);
        
        $arg->setValue($data);

        $targetObject = $arg->getValue();

		$validationErrors = $arg->getValidator()->getErrors();

		$errors = array ();
		if( count($validationErrors) > 0 ) {
			foreach($validationErrors as $propertyError) {
				$errors [$propertyError->getPropertyName()] = array ();
				foreach($propertyError->getErrors() as $error) {
					$errors [$propertyError->getPropertyName()] [] = $error->getMessage();
				}
			}
		}

		return array ("errors" => $errors, "object" => $targetObject );
	}

    /**
     * Removes Empty/Dead Data
     *
     * @param array $item
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function cleanUpItem($item) {
		foreach($item as $key => $value) {
			if( is_array($value) ) {
				$item [$key] = $this->cleanUpItem($value);
			}
			if( is_object($value) && ! empty($value->FLOW3_Persistence_Entity_UUID) ) {
				$item [$key] = $value->FLOW3_Persistence_Entity_UUID;
			}
			if( empty($item [$key]) && $item [$key] !== false && $item [$key] !== 0 ) {
				unset($item [$key]);
			}
		}
		return $item;
	}

    /**
     * Converts Raw Data to the Corresponding Objects using the ObjectConverters
     *
     * @param array $data
     * @param string $being Class of the Being
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function convertData($data,$being){
        $c = $this->getConfiguration($being);
        foreach($data as $property => $value){
            if(isset($c["properties"][$property])){
                $targetType = $c["properties"][$property]["type"];
                if(isset($this->objectConverters[$targetType]) && !is_object($value)){
                    $conversionResult = $this->objectConverters[$targetType]->convertFrom($value, $c["properties"][$property], $this);
                    if (! $conversionResult instanceof \F3\FLOW3\Error\Error) {
                        $data[$property] = $value = $conversionResult;
                    }
                }

                if(isset($c["properties"][$property]["being"])){
                    $targetBeing = $c["properties"][$property]["being"];
                    if(isset($this->objectConverters[$targetBeing]) && !is_object($value)){
                        $conversionResult = $this->objectConverters[$targetBeing]->convertFrom($value, $c["properties"][$property], $this);
                        if (! $conversionResult instanceof \F3\FLOW3\Error\Error) {
                            $data[$property] = $value = $conversionResult;
                        }
                    }
                }
            }
            
            if(\F3\Admin\Core\Helper::isIteratable($value) && isset($c["properties"][$property]["being"])){
                $data[$property] = $this->convertData($value,$c["properties"][$property]["being"]);
            }elseif(\F3\Admin\Core\Helper::isIteratable($value)){
                $data[$property] = $this->convertData($value,$being);
            }
        }
        return $data;
    }

    public function getFilter($being,$selected = array()){
        $beings = $this->getBeings($being);
        $filters = array();
        foreach($beings as $being){
            $properties = $being->getProperties();
            foreach($properties as $property){
                if($property->isFilter()){
                    if(!isset($filters[$property->getName()]))
                        $filters[$property->getName()] = $this->objectManager->get("F3\Admin\Core\Filter");

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

    public function setFilter($filters){
        $this->filters = $filters;
    }

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

    public function shouldBeIgnored($conf){
        if(!isset($conf["ignore"]))
            return false;
        
        if($conf["ignore"] == "true")
            return true;

        $actions = explode(",",$conf["ignore"]);
        $action = \F3\Admin\Register::get("action");
        return in_array($action,$actions);

        return false;
    }
}

?>