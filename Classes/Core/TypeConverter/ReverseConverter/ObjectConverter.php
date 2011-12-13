<?php
namespace Admin\Core\TypeConverter\ReverseConverter;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @api
 * @FLOW3\Scope("singleton")
 */
class ObjectConverter extends \TYPO3\FLOW3\Property\TypeConverter\AbstractTypeConverter {
	
	/**
	 * @var \Admin\Core\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;
	
	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;
	
	/**
	 * @var array<string>
	 */
	protected $sourceTypes = array('object');

	/**
	 * @var string
	 */
	protected $targetType = 'string';

	/**
	 * @var integer
	 */
	protected $priority = 10;
	
	/**
	 * @var integer
	 */
	protected $nestingLevel = 0;
	
	/**
	 * This implementation always returns TRUE for this method.
	 *
	 * @param mixed $source the source data
	 * @param string $targetType the type to convert to.
	 * @return boolean TRUE if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
	 * @api
	 */
	public function canConvertFrom($source, $targetType) {
		return true;
	}
	
	/**
	 * Actually convert from $source to $targetType, by doing a typecast.
	 *
	 * @param string $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration
	 * @return float
	 * @api
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		if(method_exists($source, "__toString"))
			return strval($source);
		
		if($string = $this->getStringByAnnotation($source))
			return $string;
			
		if($string = $this->getStringByGuessing($source))
			return $string;
		
		if($string = $this->getStringByProperties($source))
			return $string;
		
		
		if($this->nestingLevel > 0)
			return null;
		
		return sprintf("Object (%s)", \Admin\Core\Helper::getShortName(get_class($this)));
	}
	
	public function getProperties($source){
		if($source instanceof \Doctrine\ORM\Proxy\Proxy) 
			$class = get_parent_class($source);
		else
			$class = get_class($source);
		$schema = $this->reflectionService->getClassSchema($class);
		if(is_object($schema)){
			$properties = $schema->getProperties();
		}else{
			$properties = array_flip($this->reflectionService->getClassPropertyNames($class));
		}
		return $properties;
	}
	
	public function getStringByAnnotation($source){
		$configuration = $this->configurationManager->getClassConfiguration(get_class($source));
		
		$title = array();
		foreach($configuration["properties"] as $property => $meta){
			if(in_array("title",array_keys($meta))){
				$title[] = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($source, $property);
			}
		}
		
		if(count($title)>0)
			return implode(", ",$title);
		
		return false;
	}
	
	public function getStringByGuessing($source){
		$configuration = $this->configurationManager->getClassConfiguration(get_class($source));
		
		$goodGuess = array();
		$usualSuspects = array("title", "name");
		foreach($configuration["properties"] as $property => $meta){
			if(in_array($property, $usualSuspects)){
				if(\TYPO3\FLOW3\Reflection\ObjectAccess::isPropertyGettable($source, $property)){
					$value = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($source, $property);
					
					if(is_object($value) && $this->nestingLevel < 3){
						$this->nestingLevel++;
						$value = $this->convertFrom($value, "string");
						$this->nestingLevel--;
					}
				
					if(!is_object($value) && !is_null($value))
						$goodGuess[] = $value;
				}
			}
		}
		
		if(count($goodGuess) > 0)
			return implode(", ", $goodGuess);
		
		return false;
	}
	
	public function getStringByProperties($source){
		$properties = $this->getProperties($source);
		
		$strings = array();
		$count = 0;
		foreach ($properties as $key => $meta) {
			if($count > 3) break;
			if($key !== "FLOW3_Persistence_Identifier"){
				if(\TYPO3\FLOW3\Reflection\ObjectAccess::isPropertyGettable($source, $key)){
					$value = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($source, $key);
					if(is_string($value)){
						$strings[] = $value;
						$count++;
					}elseif(is_object($value) && $this->nestingLevel < 3){
						$this->nestingLevel++;
						$string = $this->convertFrom($value, "string");
						if(!is_null($string)){
							$strings[] = $string;
							$count++;
						}
						$this->nestingLevel--;
					}
				}
			}
		}
		if(!empty($strings))
			return implode(", ", $strings);
		
		return false;
	}
	
}
?>